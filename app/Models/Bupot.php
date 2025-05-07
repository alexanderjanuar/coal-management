<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bupot extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_report_id',
        'tax_period',
        'npwp',
        'company_name',
        'bupot_type',
        'notes',
        'dpp',
        'pph_type',
        'bupot_amount',
        'file_path',
        'created_by'
    ];

    public function taxreport()
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
}
