<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Sop extends Model
{
    use HasFactory;

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(SopStep::class, 'sop_id')->orderBy('order');
    }
}
