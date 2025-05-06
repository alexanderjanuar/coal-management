<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeIncomeTax extends Model
{
    use HasFactory;

    public function incomeTaxes()
    {
        return $this->belongsTo(IncomeTax::class);
    }

    

}
