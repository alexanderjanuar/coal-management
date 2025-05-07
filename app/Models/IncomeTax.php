<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeTax extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_report_id',
        'employee_id',
        'created_by'
    ];

    public function taxreport()
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
}
