<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyTaskAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_task_id',
        'user_id',
    ];

    protected $casts = [
        //
    ];

    // Relationships
    public function dailyTask(): BelongsTo
    {
        return $this->belongsTo(DailyTask::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper Methods - simplified for basic assignment
    public function unassign(): void
    {
        $this->delete();
    }

    // Accessors - simplified
    public function getIsAssignedAttribute(): bool
    {
        return true; // If record exists, user is assigned
    }
}