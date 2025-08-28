<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyTaskSubtask extends Model
{
    use HasFactory;

    use HasFactory;

    protected $fillable = [
        'daily_task_id',
        'title',
        'status',
    ];

    protected $casts = [
        //
    ];
    

    // Relationships
    public function dailyTask(): BelongsTo
    {
        return $this->belongsTo(DailyTask::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('id'); // Order by creation time instead
    }

    // Helper Methods
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);

        // Check if all subtasks are completed to update parent task
        $this->checkParentTaskCompletion();
    }

    public function markAsInProgress(): void
    {
        $this->update(['status' => 'in_progress']);
        
        // Update parent task status if needed
        if ($this->dailyTask->status === 'pending') {
            $this->dailyTask->markAsInProgress();
        }
    }

    public function markAsPending(): void
    {
        $this->update(['status' => 'pending']);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    // Accessors
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsInProgressAttribute(): bool
    {
        return $this->status === 'in_progress';
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    // Private helper method
    private function checkParentTaskCompletion(): void
    {
        $allSubtasksCompleted = $this->dailyTask
            ->subtasks()
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->count() === 0;

        if ($allSubtasksCompleted && $this->dailyTask->status !== 'completed') {
            $this->dailyTask->markAsCompleted();
        }
    }
}
