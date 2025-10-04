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

    // Basic Relationships
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

    // Relationship to comments
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

    // Activity Log Configuration with Custom Descriptions
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
                'project_id'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $userName = auth()->user()?->name ?? 'System';
                
                return match($eventName) {
                    'created' => "membuat task baru dengan judul \"{$this->title}\"",
                    'updated' => $this->getUpdateDescription(),
                    'deleted' => "menghapus task \"{$this->title}\"",
                    default => "melakukan aksi {$eventName} pada task"
                };
            });
    }

    /**
     * Generate detailed update description based on what changed
     */
    protected function getUpdateDescription(): string
    {
        $changes = [];
        
        if ($this->wasChanged('title')) {
            $old = $this->getOriginal('title');
            $new = $this->title;
            $changes[] = "mengubah judul dari \"{$old}\" menjadi \"{$new}\"";
        }
        
        if ($this->wasChanged('status')) {
            $statusLabels = [
                'pending' => 'Tertunda',
                'in_progress' => 'Sedang Dikerjakan',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan'
            ];
            $old = $statusLabels[$this->getOriginal('status')] ?? $this->getOriginal('status');
            $new = $statusLabels[$this->status] ?? $this->status;
            $changes[] = "mengubah status dari \"{$old}\" menjadi \"{$new}\"";
        }
        
        if ($this->wasChanged('priority')) {
            $priorityLabels = [
                'low' => 'Rendah',
                'normal' => 'Normal',
                'high' => 'Tinggi',
                'urgent' => 'Mendesak'
            ];
            $old = $priorityLabels[$this->getOriginal('priority')] ?? $this->getOriginal('priority');
            $new = $priorityLabels[$this->priority] ?? $this->priority;
            $changes[] = "mengubah prioritas dari \"{$old}\" menjadi \"{$new}\"";
        }
        
        if ($this->wasChanged('task_date')) {
            $old = \Carbon\Carbon::parse($this->getOriginal('task_date'))->format('d M Y');
            $new = $this->task_date->format('d M Y');
            $changes[] = "mengubah tanggal deadline dari {$old} menjadi {$new}";
        }
        
        if ($this->wasChanged('start_task_date')) {
            if ($this->getOriginal('start_task_date')) {
                $old = \Carbon\Carbon::parse($this->getOriginal('start_task_date'))->format('d M Y');
                $new = $this->start_task_date->format('d M Y');
                $changes[] = "mengubah tanggal mulai dari {$old} menjadi {$new}";
            } else {
                $new = $this->start_task_date->format('d M Y');
                $changes[] = "memulai task pada tanggal {$new}";
            }
        }
        
        if ($this->wasChanged('description')) {
            $changes[] = "memperbarui deskripsi task";
        }
        
        if ($this->wasChanged('project_id')) {
            $oldProject = $this->getOriginal('project_id') ? \App\Models\Project::find($this->getOriginal('project_id'))?->name : 'Tanpa Project';
            $newProject = $this->project?->name ?? 'Tanpa Project';
            $changes[] = "memindahkan task dari project \"{$oldProject}\" ke \"{$newProject}\"";
        }
        
        return !empty($changes) ? implode(', dan ', $changes) : 'memperbarui task';
    }

    // Helper Methods
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function markAsInProgress(): void
    {
        $this->update(['status' => 'in_progress']);
        
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

    public function hasStarted(): bool
    {
        return !is_null($this->start_task_date);
    }
}