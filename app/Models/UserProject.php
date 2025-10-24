<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProject extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'role',
        'specializations',
        'assigned_date',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'specializations' => 'array', // Cast ke array
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->user->name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->user->name, 0, 2));
    }

    public function getSpecializationsListAttribute(): string
    {
        if (!$this->specializations) {
            return '-';
        }
        
        if (is_array($this->specializations)) {
            return implode(', ', $this->specializations);
        }
        
        return $this->specializations;
    }
}