<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\Trackable;

class Project extends Model
{
    use HasFactory;

    use Trackable;
    
    use LogsActivity;

    protected $fillable = ['client_id', 'name', 'description', 'status'];

    protected $casts = [
        'due_date' => 'date',
        'deliverable_files' => 'array', // Make sure this is present
    ];

    // Auto-logging events
    protected static function booted()
    {
        static::created(function ($project) {
            $project->logActivity(
                'project_created',
                "Proyek '{$project->name}' telah dibuat untuk klien {$project->client->name}"
            );
        });

        static::updated(function ($project) {
            if ($project->wasChanged('status')) {
                $project->logActivity(
                    'project_status_changed',
                    "Status proyek '{$project->name}' diubah dari {$project->getOriginal('status')} menjadi {$project->status}"
                );
            }
            
            if ($project->wasChanged('priority')) {
                $project->logActivity(
                    'project_priority_changed',
                    "Prioritas proyek '{$project->name}' diubah dari {$project->getOriginal('priority')} menjadi {$project->priority}"
                );
            }
        });

        static::deleted(function ($project) {
            $project->logActivity(
                'project_deleted',
                "Proyek '{$project->name}' telah dihapus"
            );
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'priority', 'type', 'due_date', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $clientName = $this->client->name ?? 'Klien';
                
                return match($eventName) {
                    'created' => "[{$clientName}] 📂 PROYEK BARU: {$this->name} | Prioritas: {$this->priority}",
                    'updated' => match($this->status) {
                        'completed' => "[{$clientName}] ✅ PROYEK SELESAI: {$this->name}",
                        'in_progress' => "[{$clientName}] ⚡ PROYEK AKTIF: {$this->name}",
                        'on_hold' => "[{$clientName}] ⏸️ PROYEK DITUNDA: {$this->name}",
                        'canceled' => "[{$clientName}] ❌ PROYEK DIBATALKAN: {$this->name}",
                        default => "[{$clientName}] Proyek {$this->name} diperbarui"
                    },
                    'deleted' => "[{$clientName}] 🗑️ PROYEK DIHAPUS: {$this->name}",
                    default => "[{$clientName}] Proyek {$this->name} telah di{$eventName}"
                };
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

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_projects')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function userProject(): HasMany
    {
        return $this->hasMany(UserProject::class);
    }

    public function sop(): BelongsTo
    {
        return $this->belongsTo(Sop::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function userProjects()
    {
        return $this->hasMany(UserProject::class);
    }

    public function teamMembers()
    {
        return $this->belongsToMany(User::class, 'user_projects')
                    ->withPivot('role', 'specializations', 'assigned_date')
                    ->withTimestamps();
    }

    /**
     * Get formatted deliverable files with full URLs
     */
    public function getDeliverableFilesWithUrlsAttribute(): array
    {
        if (!$this->deliverable_files) {
            return [];
        }
        
        return collect($this->deliverable_files)->map(function($file) {
            return [
                'name' => $file['name'] ?? basename($file['path'] ?? ''),
                'path' => $file['path'] ?? null,
                'url' => isset($file['path']) ? \Storage::disk('public')->url($file['path']) : null,
                'size' => $file['size'] ?? null,
                'type' => $file['type'] ?? null,
                'uploaded_at' => $file['uploaded_at'] ?? null,
            ];
        })->toArray();
    }

    /**
     * Get client-friendly status label
     */
    public function getClientStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Belum Dimulai',
            'analysis' => 'Sedang Dianalisis',
            'in_progress' => 'Sedang Dikerjakan',
            'review' => 'Dalam Review',
            'completed' => 'Selesai',
            'completed (Not Payed Yet)' => 'Selesai',
            'on_hold' => 'Ditunda',
            'canceled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get status badge color for client view
     */
    public function getClientStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'analysis' => 'purple',
            'in_progress' => 'blue',
            'review' => 'yellow',
            'completed', 'completed (Not Payed Yet)' => 'green',
            'on_hold', 'canceled' => 'red',
            default => 'gray',
        };
    }

    /**
     * Check if project is active (not completed or canceled)
     */
    public function isActive(): bool
    {
        return !in_array($this->status, ['completed', 'completed (Not Payed Yet)', 'canceled']);
    }

    /**
     * Get days until due date
     */
    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->due_date) {
            return null;
        }
        
        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Check if project has deliverables
     */
    public function hasDeliverables(): bool
    {
        return !empty($this->deliverable_files);
    }

    /**
     * Get deliverables count
     */
    public function getDeliverablesCountAttribute(): int
    {
        return count($this->deliverable_files ?? []);
    }

}
