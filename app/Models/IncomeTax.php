<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class IncomeTax extends Model
{
    use HasFactory, LogsActivity;

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