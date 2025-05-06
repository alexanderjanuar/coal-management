<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxReport extends Model
{
    use HasFactory;

    public function client(){
        return $this->belongsTo(Client::class);
    }

    public function invoices(){
        return $this->hasMany(Invoice::class);
    }

    public function incomeTaxs(){
        return $this->hasMany(IncomeTax::class);
    }

    public function bupots(){
        return $this->hasMany(Bupot::class);
    }
}
