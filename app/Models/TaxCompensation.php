<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxCompensation extends Model
{
    use HasFactory;

    protected $table = 'tax_compensations';

    protected $fillable = [
        'source_tax_report_id',
        'target_tax_report_id',
        'amount_compensated',
        'notes'
    ];

    protected $casts = [
        'amount_compensated' => 'decimal:2',
    ];

    public function sourceTaxReport()
    {
        return $this->belongsTo(TaxReport::class, 'source_tax_report_id');
    }

    public function targetTaxReport()
    {
        return $this->belongsTo(TaxReport::class, 'target_tax_report_id');
    }

    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount_compensated, 0, ',', '.');
    }
}