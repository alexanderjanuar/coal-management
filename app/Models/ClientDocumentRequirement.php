<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ClientDocumentRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'created_by',
        'name',
        'description',
        'category',
        'is_required',
        'status',
        'due_date',
        'admin_notes',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'due_date' => 'date',
    ];

    /**
     * Relationships
     */
    
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * All documents uploaded for this requirement
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ClientDocument::class, 'requirement_id');
    }

    /**
     * Scopes
     */
    
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeFulfilled(Builder $query): Builder
    {
        return $query->where('status', 'fulfilled');
    }

    public function scopeWaived(Builder $query): Builder
    {
        return $query->where('status', 'waived');
    }

    public function scopeRequired(Builder $query): Builder
    {
        return $query->where('is_required', true);
    }

    public function scopeOptional(Builder $query): Builder
    {
        return $query->where('is_required', false);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'pending')
                     ->whereNotNull('due_date')
                     ->where('due_date', '<', now());
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Status Management Methods
     */
    
    public function markAsFulfilled(): bool
    {
        return $this->update(['status' => 'fulfilled']);
    }

    public function markAsPending(): bool
    {
        return $this->update(['status' => 'pending']);
    }

    public function waive(string $reason = null): bool
    {
        return $this->update([
            'status' => 'waived',
            'admin_notes' => $reason ?? $this->admin_notes,
        ]);
    }

    /**
     * Check Methods
     */
    
    public function isFulfilled(): bool
    {
        return $this->status === 'fulfilled' || $this->hasValidDocument();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isWaived(): bool
    {
        return $this->status === 'waived';
    }

    public function isOverdue(): bool
    {
        return $this->isPending() && 
               $this->due_date && 
               $this->due_date->isPast();
    }

    public function hasValidDocument(): bool
    {
        return $this->documents()
            ->where('status', 'valid')
            ->whereNotNull('file_path')
            ->exists();
    }

    public function hasAnyDocument(): bool
    {
        return $this->documents()
            ->whereNotNull('file_path')
            ->exists();
    }

    /**
     * Get Documents
     */
    
    public function getLatestDocument()
    {
        return $this->documents()
            ->whereNotNull('file_path')
            ->latest()
            ->first();
    }

    public function getValidDocument()
    {
        return $this->documents()
            ->where('status', 'valid')
            ->whereNotNull('file_path')
            ->latest()
            ->first();
    }

    public function getPendingDocument()
    {
        return $this->documents()
            ->where('status', 'pending_review')
            ->whereNotNull('file_path')
            ->latest()
            ->first();
    }

    public function getRejectedDocuments()
    {
        return $this->documents()
            ->where('status', 'rejected')
            ->whereNotNull('file_path')
            ->latest()
            ->get();
    }

    /**
     * Accessors
     */
    
    public function getStatusBadgeAttribute(): array
    {
        if ($this->isOverdue()) {
            return [
                'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                'icon' => 'heroicon-o-exclamation-triangle',
                'text' => 'Terlambat',
            ];
        }

        return match($this->status) {
            'fulfilled' => [
                'class' => 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
                'icon' => 'heroicon-o-check-circle',
                'text' => 'Terpenuhi',
            ],
            'pending' => [
                'class' => 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400',
                'icon' => 'heroicon-o-clock',
                'text' => 'Menunggu',
            ],
            'waived' => [
                'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                'icon' => 'heroicon-o-minus-circle',
                'text' => 'Dikecualikan',
            ],
            default => [
                'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                'icon' => 'heroicon-o-question-mark-circle',
                'text' => 'Unknown',
            ],
        };
    }

    public function getCategoryBadgeAttribute(): array
    {
        return match($this->category) {
            'legal' => [
                'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                'text' => 'Legal',
            ],
            'financial' => [
                'class' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                'text' => 'Keuangan',
            ],
            'operational' => [
                'class' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                'text' => 'Operasional',
            ],
            'compliance' => [
                'class' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                'text' => 'Kepatuhan',
            ],
            default => [
                'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                'text' => 'Lainnya',
            ],
        };
    }

    public function getFormattedDueDateAttribute(): ?string
    {
        if (!$this->due_date) {
            return null;
        }

        return $this->due_date->format('d M Y');
    }

    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->due_date) {
            return null;
        }

        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Boot method - Auto actions
     */
    protected static function booted()
    {
        // Log when requirement is created
        static::created(function ($requirement) {
            activity()
                ->performedOn($requirement)
                ->causedBy(auth()->user())
                ->withProperties([
                    'client_id' => $requirement->client_id,
                    'requirement_name' => $requirement->name,
                    'category' => $requirement->category,
                ])
                ->log("Requirement '{$requirement->name}' created for {$requirement->client->name}");
        });

        // Log status changes
        static::updated(function ($requirement) {
            if ($requirement->wasChanged('status')) {
                $oldStatus = $requirement->getOriginal('status');
                $newStatus = $requirement->status;
                
                activity()
                    ->performedOn($requirement)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                    ])
                    ->log("Requirement '{$requirement->name}' status changed from {$oldStatus} to {$newStatus}");
            }
        });
    }
}