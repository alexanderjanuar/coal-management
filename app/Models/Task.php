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
                $projectName = $this->projectStep->project->name ?? 'Proyek';
                $clientName = $this->projectStep->project->client->name ?? 'Klien';
                $stepName = $this->projectStep->name ?? 'Tahap';
                
                return match($eventName) {
                    'created' => "[{$clientName}] ✚ TUGAS BARU: {$this->title} | Proyek: {$projectName} ({$stepName})",
                    'updated' => match($this->status) {
                        'completed' => "[{$clientName}] ✓ SELESAI: {$this->title} | Proyek: {$projectName} ({$stepName})",
                        'pending' => "[{$clientName}] ⌛ MENUNGGU: {$this->title} | Proyek: {$projectName} ({$stepName})",
                        'in_progress' => "[{$clientName}] ⚡ SEDANG DIKERJAKAN: {$this->title} | Proyek: {$projectName} ({$stepName})",
                        default => "[{$clientName}] Tugas diperbarui: {$this->title} | Proyek: {$projectName} ({$stepName})"
                    },
                    'deleted' => "[{$clientName}] 🗑 DIHAPUS: {$this->title} | Proyek: {$projectName} ({$stepName})",
                    default => "[{$clientName}] Tugas {$this->title} telah di{$eventName} | Proyek: {$projectName} ({$stepName})"
                };
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
