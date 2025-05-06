<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
 
class Invoice extends Model
{
    use HasFactory;

    public function taxReport()
    {
        return $this->belongsTo(TaxReport::class);
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
