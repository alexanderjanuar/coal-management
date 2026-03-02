<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'content',
        'type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the type label for display.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'general' => 'General',
            'important' => 'Important',
            'blocker' => 'Blocker',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get the badge color for the type.
     */
    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'general' => 'gray',
            'important' => 'warning',
            'blocker' => 'danger',
            default => 'gray',
        };
    }
}
