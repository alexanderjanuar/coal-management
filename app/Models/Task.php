<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Task extends Model
{
    use HasFactory;

    use LogsActivity;

    protected $fillable = ['project_step_id', 'title', 'description', 'status', 'requires_document'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'description', 'status', 'requires_document'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $prefix = match($eventName) {
                    'created' => 'New task was created:',  
                    'updated' => 'Task was modified:',
                    'deleted' => 'Task was removed:',
                    default => "Task was {$eventName}:"
                };
                return "{$prefix} {$this->title}";
            })
            ->logFillable();
    }

    public function projectStep()
    {
        return $this->belongsTo(ProjectStep::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
