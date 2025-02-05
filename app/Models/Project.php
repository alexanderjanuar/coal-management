<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'name', 'description', 'status'];

    protected $casts = [
        'due_date' => 'date'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function steps()
    {
        return $this->hasMany(ProjectStep::class);
    }

    public function userProject(): HasMany
    {
        return $this->hasMany(UserProject::class);
    }

    public function sop(): BelongsTo
    {
        return $this->belongsTo(SOP::class);
    }
}
