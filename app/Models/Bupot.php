<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bupot extends Model
{
    use HasFactory;

    public function taxreport()
    {
        return $this->belongsTo(TaxReport::class);
    }
}
