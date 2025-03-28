<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class RequiredDocument extends Model
{
    use HasFactory;

    use LogsActivity;

    protected $fillable = ['project_step_id', 'name', 'description', 'is_required'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status', 'description', 'is_required'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $prefix = match($eventName) {
                    'created' => 'New document requirement was added:',
                    'updated' => 'Document requirement was updated:',
                    'deleted' => 'Document requirement was removed:',
                    default => "Document requirement was {$eventName}:"
                };
                return "{$prefix} {$this->name}";
            })
            ->logFillable();
    }

    public function projectStep()
    {
        return $this->belongsTo(ProjectStep::class);
    }

    public function submittedDocuments()
    {
        return $this->hasMany(SubmittedDocument::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
