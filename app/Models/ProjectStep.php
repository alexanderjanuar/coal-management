<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProjectStep extends Model
{
    use HasFactory;
    
    use LogsActivity;

    protected $fillable = ['project_id', 'name', 'order', 'description', 'status'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'order', 'description', 'priority', 'start_date', 'due_date', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $prefix = match($eventName) {
                    'created' => 'New project step was created:',
                    'updated' => 'Project step was modified:',
                    'deleted' => 'Project step was removed:',
                    default => "Project step was {$eventName}:"
                };
                return "{$prefix} {$this->name}";
            })
            ->logFillable();
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function requiredDocuments()
    {
        return $this->hasMany(RequiredDocument::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
