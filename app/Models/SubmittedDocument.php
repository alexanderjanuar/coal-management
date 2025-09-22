<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\Trackable; // Import trait

class SubmittedDocument extends Model
{
    use HasFactory, LogsActivity, Trackable; // Tambahkan Trackable

    protected $fillable = [
        'required_document_id', 
        'user_id', 
        'file_path', 
        'status', 
        'rejection_reason',
        'notes'
    ];

    // Relationships
    public function requiredDocument()
    {
        return $this->belongsTo(RequiredDocument::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    // Helper untuk mendapatkan client dari relationship chain
    public function getClientAttribute()
    {
        return $this->requiredDocument?->projectStep?->project?->client;
    }

    // Helper untuk mendapatkan project dari relationship chain
    public function getProjectAttribute()
    {
        return $this->requiredDocument?->projectStep?->project;
    }

    // Auto-logging events
    protected static function booted()
    {
        static::created(function ($submittedDocument) {
            $docName = basename($submittedDocument->file_path) ?? 'Dokumen';
            $userName = $submittedDocument->user->name ?? 'Pengguna';
            $clientName = $submittedDocument->getClientName();
            $projectName = $submittedDocument->getProjectName();
            $reqDocName = $submittedDocument->requiredDocument?->name ?? 'Dokumen';
            
            // Create activity dengan client_id dan project_id yang benar
            UserActivity::log([
                'action' => 'document_submitted',
                'description' => "Dokumen '{$docName}' untuk persyaratan '{$reqDocName}' telah diunggah untuk {$clientName} - {$projectName} oleh {$userName}",
                'actionable_type' => SubmittedDocument::class,
                'actionable_id' => $submittedDocument->id,
                'client_id' => $submittedDocument->getSubmittedDocumentClientId(),
                'project_id' => $submittedDocument->getSubmittedDocumentProjectId(),
            ]);
        });

        static::updated(function ($submittedDocument) {
            $docName = basename($submittedDocument->file_path) ?? 'Dokumen';
            $clientName = $submittedDocument->getClientName();
            $projectName = $submittedDocument->getProjectName();
            $reqDocName = $submittedDocument->requiredDocument?->name ?? 'Dokumen';
            $userName = auth()->user()?->name ?? 'System';
            
            if ($submittedDocument->wasChanged('status')) {
                $status = $submittedDocument->status;
                
                $description = match($status) {
                    'approved' => "Dokumen '{$docName}' untuk persyaratan '{$reqDocName}' telah DISETUJUI - {$clientName} ({$projectName}) oleh {$userName}",
                    'rejected' => "Dokumen '{$docName}' untuk persyaratan '{$reqDocName}' telah DITOLAK - {$clientName} ({$projectName}). Alasan: {$submittedDocument->rejection_reason} oleh {$userName}",
                    'pending_review' => "Dokumen '{$docName}' untuk persyaratan '{$reqDocName}' sedang DIPERIKSA - {$clientName} ({$projectName}) oleh {$userName}",
                    'uploaded' => "Dokumen '{$docName}' untuk persyaratan '{$reqDocName}' telah diunggah ulang - {$clientName} ({$projectName}) oleh {$userName}",
                    default => "Status dokumen '{$docName}' untuk persyaratan '{$reqDocName}' diubah menjadi {$status} - {$clientName} ({$projectName}) oleh {$userName}"
                };
                
                UserActivity::log([
                    'action' => 'document_status_changed',
                    'description' => $description,
                    'actionable_type' => SubmittedDocument::class,
                    'actionable_id' => $submittedDocument->id,
                    'client_id' => $submittedDocument->getSubmittedDocumentClientId(),
                    'project_id' => $submittedDocument->getSubmittedDocumentProjectId(),
                ]);
            }

            if ($submittedDocument->wasChanged('notes') && !empty($submittedDocument->notes)) {
                UserActivity::log([
                    'action' => 'document_notes_added',
                    'description' => "Catatan ditambahkan pada dokumen '{$docName}' untuk persyaratan '{$reqDocName}' - {$clientName} ({$projectName}) oleh {$userName}",
                    'actionable_type' => SubmittedDocument::class,
                    'actionable_id' => $submittedDocument->id,
                    'client_id' => $submittedDocument->getSubmittedDocumentClientId(),
                    'project_id' => $submittedDocument->getSubmittedDocumentProjectId(),
                ]);
            }
        });

        static::deleted(function ($submittedDocument) {
            $docName = basename($submittedDocument->file_path) ?? 'Dokumen';
            $clientName = $submittedDocument->getClientName();
            $projectName = $submittedDocument->getProjectName();
            $reqDocName = $submittedDocument->requiredDocument?->name ?? 'Dokumen';
            $userName = auth()->user()?->name ?? 'System';
            
            UserActivity::log([
                'action' => 'document_deleted',
                'description' => "Dokumen '{$docName}' untuk persyaratan '{$reqDocName}' telah dihapus - {$clientName} ({$projectName}) oleh {$userName}",
                'actionable_type' => SubmittedDocument::class,
                'actionable_id' => $submittedDocument->id,
                'client_id' => $submittedDocument->getSubmittedDocumentClientId(),
                'project_id' => $submittedDocument->getSubmittedDocumentProjectId(),
            ]);
        });
    }

    // Helper methods untuk mendapatkan client dan project name
    public function getClientName(): string
    {
        // Coba dari relationship chain
        if ($this->requiredDocument?->projectStep?->project?->client?->name) {
            return $this->requiredDocument->projectStep->project->client->name;
        }
        
        // Fallback ke "Klien"
        return 'Klien';
    }

    public function getProjectName(): string
    {
        // Coba dari relationship chain
        if ($this->requiredDocument?->projectStep?->project?->name) {
            return $this->requiredDocument->projectStep->project->name;
        }
        
        // Fallback ke "Proyek"
        return 'Proyek';
    }

    public function getSubmittedDocumentClientId(): ?int
    {
        return $this->requiredDocument?->projectStep?->project?->client?->id;
    }

    public function getSubmittedDocumentProjectId(): ?int
    {
        return $this->requiredDocument?->projectStep?->project?->id;
    }

    // Custom methods untuk specific actions
    public function approve(string $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'notes' => $notes
        ]);
    }

    public function reject(string $reason, string $notes = null)
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'notes' => $notes
        ]);
    }

    public function markForReview()
    {
        $this->update(['status' => 'pending_review']);
    }

    // Spatie ActivityLog (tetap ada untuk detailed audit)
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['file_path', 'rejection_reason', 'status', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $docName = basename($this->file_path) ?? 'Dokumen';
                $userName = $this->user->name ?? 'Pengguna';
                $clientName = $this->client?->name ?? 'Klien';
                $projectName = $this->project?->name ?? 'Proyek';
                
                return match ($eventName) {
                    'created' => "[{$clientName}] 📤 {$userName} telah mengunggah \"{$docName}\" untuk {$projectName}",
                    'updated' => match ($this->status) {
                        'approved' => "[{$clientName}] ✅ Dokumen \"{$docName}\" untuk {$projectName} telah DISETUJUI",
                        'rejected' => "[{$clientName}] ❌ Dokumen \"{$docName}\" untuk {$projectName} DITOLAK. Alasan: {$this->rejection_reason}",
                        'pending_review' => "[{$clientName}] 👁️ Dokumen \"{$docName}\" untuk {$projectName} sedang DIPERIKSA", 
                        'uploaded' => "[{$clientName}] 📄 Dokumen \"{$docName}\" untuk {$projectName} telah diperbarui",
                        default => "[{$clientName}] 📄 Dokumen \"{$docName}\" untuk {$projectName} telah diperbarui"
                    },
                    'deleted' => "[{$clientName}] 🗑️ {$userName} telah menghapus \"{$docName}\" dari {$projectName}",
                    default => "[{$clientName}] ℹ️ \"{$docName}\" untuk {$projectName} telah di{$eventName}"
                };
            });
    }

    // Accessor helpers
    public function getFileNameAttribute(): string
    {
        return basename($this->file_path);
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'approved';
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->status === 'rejected';
    }

    public function getIsPendingReviewAttribute(): bool
    {
        return $this->status === 'pending_review';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'pending_review' => 'warning',
            'uploaded' => 'info',
            default => 'gray'
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            'approved' => '✅',
            'rejected' => '❌',
            'pending_review' => '👁️',
            'uploaded' => '📤',
            default => '📄'
        };
    }
}