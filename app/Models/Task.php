<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    public function progress(){
        return $this->belongsTo(Progress::class);
    }

    public function documents(){
        return $this->hasMany(Document::class);
    }
}
