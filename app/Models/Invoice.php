<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_report_id',
        'invoice_number',
        'company_name',
        'npwp',
        'type',
        'dpp',
        'ppn',
        'nihil',
        'file_path',
        'notes',
        'created_by'
    ];

    public function taxReport()
    {
        return $this->belongsTo(TaxReport::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bupots()
    {
        return $this->hasOne(Bupot::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'tax_report_id',
                'amount'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $clientName = $this->taxReport?->client?->name ?? 'Klien';
                $invoiceNumber = $this->invoice_number ?? 'Tidak Diketahui';
                $invoiceType = $this->type ?? 'Umum';
                $userName = auth()->user()?->name ?? 'System';
                
                return match($eventName) {
                    'created' => "[{$clientName}] 📄 {$invoiceType} BARU: {$invoiceNumber} | Dibuat oleh: {$userName}",
                    'updated' => "[{$clientName}] 🔄 DIPERBARUI: {$invoiceType} {$invoiceNumber} | Diperbarui oleh: {$userName}",
                    'deleted' => "[{$clientName}] 🗑️ DIHAPUS: {$invoiceType} {$invoiceNumber} | Dihapus oleh: {$userName}",
                    default => "[{$clientName}] {$invoiceType} {$invoiceNumber} telah {$eventName} oleh {$userName}"
                };
            });
    }
}
