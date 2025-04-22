<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SubmittedDocument extends Model
{
    use HasFactory;

    use LogsActivity;

    protected $fillable = ['required_document_id', 'user_id', 'file_path', 'status', 'rejection_reason'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['file_path', 'rejection_reason', 'status', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $docName = basename($this->file_path) ?? 'Dokumen';
                $userName = $this->user->name ?? 'Pengguna';
                $clientName = $this->requiredDocument->projectStep->project->client->name ?? 'Klien';
                $projectName = $this->requiredDocument->projectStep->project->name ?? 'Proyek';
                
                return match ($eventName) {
                    'created' => "[{$clientName}] 📤 {$userName} telah mengunggah \"{$docName}\" untuk {$projectName}",
                    'updated' => match ($this->status) {
                        'approved' => "[{$clientName}] ✅ Dokumen \"{$docName}\" untuk {$projectName} telah DISETUJUI",
                        'rejected' => "[{$clientName}] ❌ Dokumen \"{$docName}\" untuk {$projectName} DITOLAK. Alasan: {$this->rejection_reason}",
                        'pending_review' => "[{$clientName}] 👁️ Dokumen \"{$docName}\" untuk {$projectName} sedang DIPERIKSA", 
                        'draft' => "[{$clientName}] 📝 Dokumen \"{$docName}\" untuk {$projectName} masih DRAFT",
                        default => "[{$clientName}] 🔄 Dokumen \"{$docName}\" untuk {$projectName} telah diperbarui"
                    },
                    'deleted' => "[{$clientName}] 🗑️ {$userName} telah menghapus \"{$docName}\" dari {$projectName}",
                    default => "[{$clientName}] ℹ️ \"{$docName}\" untuk {$projectName} telah di{$eventName}"
                };
            });
    }

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
}
