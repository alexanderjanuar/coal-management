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
    ];

    protected $casts = [
        'expired_at' => 'date',
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

    public function scopeValid($query)
    {
        return $query->where('status', 'valid');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
                    ->orWhere(function($q) {
                        $q->whereNotNull('expired_at')
                          ->where('expired_at', '<', now());
                    });
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

    public function getStatusBadgeAttribute(): array
    {
        if ($this->is_expired) {
            return [
                'class' => 'bg-red-100 text-red-800',
                'text' => 'Expired'
            ];
        }

        return match($this->status) {
            'valid' => [
                'class' => 'bg-green-100 text-green-800',
                'text' => 'Valid'
            ],
            'pending' => [
                'class' => 'bg-yellow-100 text-yellow-800',
                'text' => 'Pending'
            ],
            'rejected' => [
                'class' => 'bg-red-100 text-red-800',
                'text' => 'Ditolak'
            ],
            default => [
                'class' => 'bg-gray-100 text-gray-800',
                'text' => 'Unknown'
            ]
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

    public function updateExpiryStatus(): void
    {
        if ($this->expired_at && $this->expired_at->isPast() && $this->status === 'valid') {
            $this->update(['status' => 'expired']);
        }
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
            'status' => 'valid',
        ], $data));
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
    }
}