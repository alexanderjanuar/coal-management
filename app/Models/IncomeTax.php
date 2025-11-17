<?php

namespace App\Models;

use App\Traits\Trackable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class IncomeTax extends Model
{
    use HasFactory, LogsActivity, Trackable;

    protected $fillable = [
        'tax_report_id',
        'employee_id',
        'ter_amount',
        'ter_category',
        'pph_21_amount',
        'file_path',
        'bukti_setor',
        'notes',
        'created_by'
    ];

    public function taxReport()
    {
        return $this->belongsTo(TaxReport::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted()
    {
        static::created(function ($incomeTax) {
            $employeeName = $incomeTax->employee->name ?? 'Karyawan';
            $incomeTax->logActivity(
                'income_tax_created',
                "PPh 21 untuk {$employeeName} telah dibuat (TER: Rp " . number_format($incomeTax->ter_amount, 0, ',', '.') . 
                ", PPh 21: Rp " . number_format($incomeTax->pph_21_amount, 0, ',', '.') . ")"
            );

            // AUTO-RECALCULATE: Update PPh summary when income tax created
            $incomeTax->recalculateTaxSummary();
        });

        static::updated(function ($incomeTax) {
            $employeeName = $incomeTax->employee->name ?? 'Karyawan';
            
            if ($incomeTax->wasChanged('ter_amount') || $incomeTax->wasChanged('pph_21_amount')) {
                $incomeTax->logActivity(
                    'income_tax_amount_updated',
                    "Jumlah PPh 21 untuk {$employeeName} telah diperbarui"
                );

                // AUTO-RECALCULATE: Update PPh summary when amount changed
                $incomeTax->recalculateTaxSummary();
            }

            if ($incomeTax->wasChanged('bukti_setor')) {
                $incomeTax->logActivity(
                    'income_tax_bukti_setor_uploaded',
                    "Bukti setor PPh 21 untuk {$employeeName} telah diunggah"
                );
            }
        });

        static::deleted(function ($incomeTax) {
            $employeeName = $incomeTax->employee->name ?? 'Karyawan';
            $incomeTax->logActivity(
                'income_tax_deleted',
                "PPh 21 untuk {$employeeName} telah dihapus"
            );

            // AUTO-RECALCULATE: Update PPh summary when income tax deleted
            $incomeTax->recalculateTaxSummary();
        });
    }

    /**
     * Recalculate PPh tax summary for this income tax's tax report
     */
    protected function recalculateTaxSummary()
    {
        try {
            if ($this->taxReport) {
                // Get or create PPh summary
                $pphSummary = $this->taxReport->taxCalculationSummaries()
                    ->firstOrCreate(
                        ['tax_type' => 'pph'],
                        [
                            'pajak_masuk' => 0,
                            'pajak_keluar' => 0,
                            'selisih' => 0,
                            'status' => 'Nihil',
                            'kompensasi_diterima' => 0,
                            'kompensasi_tersedia' => 0,
                            'kompensasi_terpakai' => 0,
                            'saldo_final' => 0,
                            'status_final' => 'Nihil',
                            'report_status' => 'Belum Lapor',
                        ]
                    );

                // Recalculate using TaxCalculationSummary's recalculate method
                $pphSummary->recalculate();
            }
        } catch (\Exception $e) {
            // Log error but don't break the income tax operation
            \Log::error('Failed to recalculate PPh summary for income tax ' . $this->id . ': ' . $e->getMessage());
        }
    }

    // Spatie ActivityLog (tetap ada untuk detailed audit)
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'tax_report_id',
                'employee_id',
                'ter_amount',
                'pph_21_amount'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $clientName = $this->taxreport?->client?->name ?? 'Klien';
                $employeeName = $this->employee?->name ?? 'Karyawan';
                $taxPeriod = $this->taxreport?->month ?? 'Periode';
                $userName = auth()->user()?->name ?? 'System';
                
                return match($eventName) {
                    'created' => "[{$clientName}] 💰 PPh BARU: {$employeeName} - {$taxPeriod} | Dibuat oleh: {$userName}",
                    'updated' => "[{$clientName}] 🔄 DIPERBARUI: PPh {$employeeName} - {$taxPeriod} | Diperbarui oleh: {$userName}",
                    'deleted' => "[{$clientName}] 🗑️ DIHAPUS: PPh {$employeeName} - {$taxPeriod} | Dihapus oleh: {$userName}",
                    default => "[{$clientName}] PPh {$employeeName} - {$taxPeriod} telah {$eventName} oleh {$userName}"
                };
            });
    }
}