<?php

namespace App\Models;

use App\Traits\Trackable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class IncomeTax extends Model
{
    use HasFactory, LogsActivity,Trackable;

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
        });

        static::updated(function ($incomeTax) {
            $employeeName = $incomeTax->employee->name ?? 'Karyawan';
            
            if ($incomeTax->wasChanged('ter_amount') || $incomeTax->wasChanged('pph_21_amount')) {
                $incomeTax->logActivity(
                    'income_tax_amount_updated',
                    "Jumlah PPh 21 untuk {$employeeName} telah diperbarui"
                );
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
        });
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
                    'updated' => "[{$clientName}] 📄 DIPERBARUI: PPh {$employeeName} - {$taxPeriod} | Diperbarui oleh: {$userName}",
                    'deleted' => "[{$clientName}] 🗑️ DIHAPUS: PPh {$employeeName} - {$taxPeriod} | Dihapus oleh: {$userName}",
                    default => "[{$clientName}] PPh {$employeeName} - {$taxPeriod} telah {$eventName} oleh {$userName}"
                };
            });
    }
}