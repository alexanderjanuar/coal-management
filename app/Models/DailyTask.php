<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DailyTask extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'title',
        'description',
        'project_id',
        'created_by',
        'priority',
        'status',
        'task_date',
        'start_task_date',
    ];

    protected $casts = [
        'task_date' => 'date',
        'start_task_date' => 'date',
    ];

    // Basic Relationships (we'll add more as we create other models)
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Assignment relationships
    public function assignments(): HasMany
    {
        return $this->hasMany(DailyTaskAssignment::class);
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'daily_task_assignments')
            ->withTimestamps();
    }

    // Subtask relationships
    public function subtasks(): HasMany
    {
        return $this->hasMany(DailyTaskSubtask::class);
    }

    // Relationship to comments (using your existing Comment model)
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    // Useful Scopes
    public function scopeForDate($query, $date)
    {
        return $query->where('task_date', $date);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('assignments', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function scopeToday($query)
    {
        return $query->where('task_date', today());
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function getProgressPercentageAttribute()
    {
        $totalSubtasks = $this->subtasks()->count();
        if ($totalSubtasks === 0) {
            return $this->status === 'completed' ? 100 : 0;
        }

        $completedSubtasks = $this->subtasks()->completed()->count();
        return round(($completedSubtasks / $totalSubtasks) * 100);
    }

    // Activity Log Configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title',
                'description', 
                'priority',
                'status',
                'task_date',
                'start_task_date',
                'project.name'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Helper Methods
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function markAsInProgress(): void
    {
        $this->update(['status' => 'in_progress']);
        
        // Set start_task_date to today if not already set
        if (!$this->start_task_date) {
            $this->update(['start_task_date' => today()]);
        }
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function assignToUser(User $user): void
    {
        $this->assignments()->firstOrCreate(['user_id' => $user->id]);
    }

    public function unassignUser(User $user): void
    {
        $this->assignments()->where('user_id', $user->id)->delete();
    }

    public function isAssignedTo(User $user): bool
    {
        return $this->assignments()->where('user_id', $user->id)->exists();
    }

    public function addSubtask(string $title): DailyTaskSubtask
    {
        return $this->subtasks()->create([
            'title' => $title,
        ]);
    }

    public function getCompletedSubtasksCount(): int
    {
        return $this->subtasks()->completed()->count();
    }

    public function getTotalSubtasksCount(): int
    {
        return $this->subtasks()->count();
    }

    // New helper methods for start task date
    public function hasStarted(): bool
    {
        return !is_null($this->start_task_date);
    }
}