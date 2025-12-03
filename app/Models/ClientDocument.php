<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ClientDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id', 
        'sop_legal_document_id',
        'file_path',
        'original_filename',
        'document_number',
        'expired_at',
        'document_category',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'expired_at' => 'date',
        'reviewed_at' => 'datetime',
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function sopLegalDocument(): BelongsTo
    {
        return $this->belongsTo(SopLegalDocument::class);
    }
    
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(ClientDocumentRequirement::class, 'requirement_id');
    }

    // Scopes
    public function scopeLegalDocuments($query)
    {
        return $query->where(function($q) {
            $q->whereNotNull('sop_legal_document_id')
              ->orWhere('file_path', 'like', '%/legal/%')
              ->orWhere('original_filename', 'like', '%legal%')
              ->orWhere('original_filename', 'like', '%kontrak%')
              ->orWhere('original_filename', 'like', '%akta%')
              ->orWhere('original_filename', 'like', '%perjanjian%')
              ->orWhere('original_filename', 'like', '%contract%')
              ->orWhere('original_filename', 'like', '%agreement%');
        });
    }


    /**
     * ✅ ADD THIS - Documents fulfilling requirements
     */
    public function scopeRequirementDocuments($query)
    {
        return $query->whereNotNull('requirement_id');
    }

    /**
     * ✅ ADD THIS - Ad-hoc additional documents (not linked to requirements)
     */
    public function scopeAdditionalDocuments($query)
    {
        return $query->whereNull('sop_legal_document_id')
                     ->whereNull('requirement_id');
    }


    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
                    ->orWhere(function($q) {
                        $q->whereNotNull('expired_at')
                          ->where('expired_at', '<', now());
                    });
    }


    public function scopeValid($query)
    {
        return $query->where('status', 'valid');
    }

    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    public function scopeUploaded($query)
    {
        return $query->where('is_template', false)
                    ->whereNotNull('file_path');
    }


    // Add new static method
    public static function createRequiredDocument(Client $client, string $filename, ?string $description = null): self
    {
        return self::create([
            'client_id' => $client->id,
            'original_filename' => $filename,
            'description' => $description,
            'is_template' => true,
            'status' => 'required',
            'file_path' => null,
            'user_id' => auth()->id(),
        ]);
    }

    // Check if this is a requirement/template
    public function isTemplate(): bool
    {
        return $this->is_template;
    }

    // Check if template has been fulfilled
    public function isFulfilled(): bool
    {
        if (!$this->is_template) {
            return false;
        }
        
        return $this->client->clientDocuments()
                    ->where('is_template', false)
                    ->whereNotNull('file_path')
                    ->where('original_filename', 'LIKE', '%' . $this->original_filename . '%')
                    ->exists();
    }
  
    public function scopeRequired($query)
    {
        return $query->where('status', 'required');
    }
    
    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }
    
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    
    public function scopeNotUploaded($query)
    {
        return $query->whereNull('file_path');
    }

    // Accessors & Mutators
    public function getFileNameAttribute(): string
    {
        return $this->original_filename ?? basename($this->file_path ?? 'Unknown');
    }

    public function getFileUrlAttribute(): ?string
    {
        if ($this->file_path && \Storage::disk('public')->exists($this->file_path)) {
            return \Storage::disk('public')->url($this->file_path);
        }
        return null;
    }

    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expired_at) {
            return false;
        }
        
        return $this->expired_at->isPast();
    }
    
    public function getIsUploadedAttribute(): bool
    {
        return !is_null($this->file_path);
    }
    
    public function getIsRequiredAttribute(): bool
    {
        return $this->status === 'required';
    }
    
    public function getIsPendingReviewAttribute(): bool
    {
        return $this->status === 'pending_review';
    }
    
    public function getIsValidAttribute(): bool
    {
        return $this->status === 'valid';
    }
    
    public function getIsRejectedAttribute(): bool
    {
        return $this->status === 'rejected';
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'required' => [
                'class' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                'text' => 'Belum Upload',
                'icon' => 'heroicon-o-clock'
            ],
            'pending_review' => [
                'class' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                'text' => 'Menunggu Review',
                'icon' => 'heroicon-o-eye'
            ],
            'valid' => [
                'class' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                'text' => 'Valid',
                'icon' => 'heroicon-o-check-circle'
            ],
            'expired' => [
                'class' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                'text' => 'Expired',
                'icon' => 'heroicon-o-exclamation-circle'
            ],
            'rejected' => [
                'class' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                'text' => 'Ditolak',
                'icon' => 'heroicon-o-x-circle'
            ],
            default => [
                'class' => 'bg-gray-100 text-gray-800',
                'text' => 'Unknown',
                'icon' => 'heroicon-o-question-mark-circle'
            ]
        };
    }
    
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'required' => 'gray',
            'pending_review' => 'warning',
            'valid' => 'success',
            'expired' => 'danger',
            'rejected' => 'danger',
            default => 'gray'
        };
    }
    
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'required' => 'Belum Upload',
            'pending_review' => 'Menunggu Review',
            'valid' => 'Valid',
            'expired' => 'Expired',
            'rejected' => 'Ditolak',
            default => 'Unknown'
        };
    }

    // Helper Methods
    public function isLegalDocument(): bool
    {
        // Check if linked to SOP
        if ($this->sop_legal_document_id) {
            return true;
        }

        $filename = strtolower($this->original_filename ?? '');
        $filepath = strtolower($this->file_path ?? '');
        
        // Check by file path
        if (str_contains($filepath, '/legal/')) {
            return true;
        }
        
        // Check by filename keywords
        $legalKeywords = ['legal', 'kontrak', 'akta', 'perjanjian', 'contract', 'agreement'];
        
        foreach ($legalKeywords as $keyword) {
            if (str_contains($filename, $keyword)) {
                return true;
            }
        }
        
        return false;
    }

    public function checkAndUpdateExpiryStatus(): void
    {
        if ($this->expired_at && $this->expired_at->isPast() && $this->status === 'valid') {
            $this->update([
                'status' => 'expired',
                'admin_notes' => ($this->admin_notes ? $this->admin_notes . "\n\n" : '') 
                    . 'Dokumen expired pada: ' . $this->expired_at->format('d M Y')
            ]);
        }
    }
    
    /**
     * Mark document as approved (admin action)
     */
    public function approve(?string $notes = null): bool
    {
        return $this->update([
            'status' => 'valid',
            'admin_notes' => $notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
    }
    
    /**
     * Mark document as rejected (admin action)
     */
    public function reject(string $reason): bool
    {
        return $this->update([
            'status' => 'rejected',
            'admin_notes' => $reason,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
    }

    // Static Methods
    public static function uploadForClient(Client $client, $file, array $data = []): self
    {
        // Generate filename
        $originalName = $file->getClientOriginalName();
        $filename = time() . '_' . $originalName;
        
        // Determine storage path
        $tempDocument = new self(['original_filename' => $originalName]);
        $isLegal = $tempDocument->isLegalDocument() || isset($data['sop_legal_document_id']);
        
        $storagePath = $isLegal 
            ? $client->getLegalFolderPath() 
            : $client->getFolderPath();
        
        // Store file
        $filePath = $file->storeAs($storagePath, $filename, 'public');
        
        // Create document record
        return self::create(array_merge([
            'client_id' => $client->id,
            'user_id' => auth()->id(),
            'file_path' => $filePath,
            'original_filename' => $originalName,
            'status' => 'pending_review',
        ], $data));
    }
    
    /**
     * Create document requirement without file (to be uploaded by client later)
     */
    public static function createRequirement(Client $client, array $data): self
    {
        return self::create(array_merge([
            'client_id' => $client->id,
            'status' => 'required',
        ], $data));
    }


    /**
     * ✅ ADD THIS - Check if this fulfills a requirement
     */
    public function isRequirementDocument(): bool
    {
        return !is_null($this->requirement_id);
    }

    /**
     * ✅ ADD THIS - Check if this is ad-hoc additional document
     */
    public function isAdditionalDocument(): bool
    {
        return is_null($this->sop_legal_document_id) && 
               is_null($this->requirement_id);
    }

    public function updateExpiryStatus(): void
    {
        if ($this->expired_at && $this->expired_at->isPast() && $this->status === 'valid') {
            $this->update(['status' => 'expired']);
        }
    }

    // Event Handlers
    protected static function booted()
    {
        static::created(function ($clientDocument) {
            $filename = $clientDocument->original_filename ?? basename($clientDocument->file_path);
            
            if ($clientDocument->isLegalDocument()) {
                UserActivity::logLegalDocumentUpload(
                    $clientDocument->client, 
                    $filename, 
                    $clientDocument
                );
            } elseif ($clientDocument->isRequirementDocument()) {
                // ✅ ADD THIS - Log requirement document upload
                UserActivity::log([
                    'action' => 'requirement_document_uploaded',
                    'description' => "Dokumen '{$filename}' diunggah untuk persyaratan '{$clientDocument->requirement->name}' oleh " . auth()->user()?->name,
                    'actionable_type' => ClientDocument::class,
                    'actionable_id' => $clientDocument->id,
                    'client_id' => $clientDocument->client_id,
                ]);
            } else {
                UserActivity::logClientDocumentUpload(
                    $clientDocument->client, 
                    $filename
                );
            }
        });

        static::updating(function ($clientDocument) {
            // Auto-update status if expired
            $clientDocument->updateExpiryStatus();
        });

        // ✅ ADD THIS - Auto-update requirement status when document status changes
        static::updated(function ($clientDocument) {
            if ($clientDocument->wasChanged('status') && $clientDocument->requirement_id) {
                $requirement = $clientDocument->requirement;
                
                // If document is approved, mark requirement as fulfilled
                if ($clientDocument->status === 'valid') {
                    $requirement->markAsFulfilled();
                }
                // If document was rejected and no other valid docs, mark as pending
                elseif ($clientDocument->status === 'rejected') {
                    if (!$requirement->hasValidDocument()) {
                        $requirement->markAsPending();
                    }
                }
            }
        });

        // ✅ ADD THIS - Handle requirement status when document is deleted
        static::deleted(function ($clientDocument) {
            if ($clientDocument->requirement_id) {
                $requirement = $clientDocument->requirement;
                
                // If this was the only valid document, mark requirement as pending
                if (!$requirement->hasValidDocument()) {
                    $requirement->markAsPending();
                }
            }
        });
    }
}