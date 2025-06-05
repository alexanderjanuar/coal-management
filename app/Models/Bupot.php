<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Bupot extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'tax_report_id',
        'invoice_id',
        'tax_period',
        'npwp',
        'company_name',
        'bupot_percentage',
        'bupot_type',
        'notes',
        'dpp',
        'pph_type',
        'bupot_amount',
        'file_path',
        'bukti_setor',
        'created_by'
    ];

    public function taxReport()
    {
        return $this->belongsTo(TaxReport::class);
    }

    public function invoice(){
        return $this->belongsTo(Invoice::class);
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
                'company_name',
                'bupot_type',
                'pph_type',
                'bupot_amount'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $clientName = $this->taxreport?->client?->name ?? 'Klien';
                $companyName = $this->company_name ?? 'Perusahaan';
                $bupotType = $this->bupot_type ?? 'Bupot';
                $pphType = $this->pph_type ?? '';
                $taxPeriod = $this->tax_period ?? $this->taxreport?->month ?? 'Periode';
                $userName = auth()->user()?->name ?? 'System';
                
                $typeDisplay = $pphType ? "{$bupotType} {$pphType}" : $bupotType;
                
                return match($eventName) {
                    'created' => "[{$clientName}] 📋 BUPOT BARU: {$typeDisplay} - {$companyName} ({$taxPeriod}) | Dibuat oleh: {$userName}",
                    'updated' => "[{$clientName}] 🔄 DIPERBARUI: {$typeDisplay} - {$companyName} ({$taxPeriod}) | Diperbarui oleh: {$userName}",
                    'deleted' => "[{$clientName}] 🗑️ DIHAPUS: {$typeDisplay} - {$companyName} ({$taxPeriod}) | Dihapus oleh: {$userName}",
                    default => "[{$clientName}] {$typeDisplay} - {$companyName} ({$taxPeriod}) telah {$eventName} oleh {$userName}"
                };
            });
    }
}