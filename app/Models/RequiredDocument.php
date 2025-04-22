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
                $clientName = $this->projectStep->project->client->name ?? 'Klien';
                $projectName = $this->projectStep->project->name ?? 'Proyek';
                $stepName = $this->projectStep->name ?? 'Tahap';
                
                return match($eventName) {
                    'created' => "[{$clientName}] 📋 Persyaratan dokumen baru ditambahkan: {$this->name} pada {$stepName} ({$projectName})",
                    'updated' => match($this->status) {
                        'approved' => "[{$clientName}] ✅ Semua dokumen {$this->name} telah disetujui",
                        'pending_review' => "[{$clientName}] 👁️ Dokumen {$this->name} menunggu peninjauan",
                        'uploaded' => "[{$clientName}] 📤 Dokumen baru diunggah untuk {$this->name}",
                        'rejected' => "[{$clientName}] ❌ Beberapa dokumen {$this->name} ditolak",
                        'draft' => "[{$clientName}] 📝 Dokumen {$this->name} masih draft",
                        default => "[{$clientName}] 🔄 Persyaratan dokumen diperbarui: {$this->name}"
                    },
                    'deleted' => "[{$clientName}] 🗑️ Persyaratan dokumen dihapus: {$this->name} dari {$stepName}",
                    default => "[{$clientName}] ℹ️ Persyaratan dokumen {$this->name} telah di{$eventName}"
                };
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
