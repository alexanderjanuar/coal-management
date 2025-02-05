<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SopTask extends Model
{
    use HasFactory;

    public function sopStep()
    {
        return $this->belongsTo(Sop::class);
    }
}
