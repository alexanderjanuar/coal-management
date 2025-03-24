<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Project extends Model
{
    use HasFactory;
    
    use LogsActivity;

    protected $fillable = ['client_id', 'name', 'description', 'status'];

    protected $casts = [
        'due_date' => 'date'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'priority', 'type', 'due_date', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $prefix = match($eventName) {
                    'created' => 'New project was created:',
                    'updated' => 'Project details were modified:',
                    'deleted' => 'Project was deleted:',
                    default => "Project was {$eventName}:"
                };
                return "{$prefix} {$this->name}";
            });
    }

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
