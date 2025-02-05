<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SopStep extends Model
{
    use HasFactory;

    public function sop()
    {
        return $this->belongsTo(SOP::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(SOPTask::class, 'sop_step_id');
    }

    public function requiredDocuments(): HasMany
    {
        return $this->hasMany(SOPRequiredDocument::class, 'sop_step_id');
    }
}
