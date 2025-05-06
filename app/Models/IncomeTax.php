<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeTax extends Model
{
    use HasFactory;

    public function taxreport()
    {
        return $this->belongsTo(TaxReport::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
