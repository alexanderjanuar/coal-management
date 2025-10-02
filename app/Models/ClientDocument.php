<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientDocument extends Model
{
    use HasFactory;

    // Existing fillable fields (sesuai dengan struktur table yang ada)
    protected $fillable = [
        'client_id',
        'user_id', 
        'file_path',
        'original_filename',
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
    
    /**
     * Relationship to SOP template
     */
    public function sopLegalDocument(): BelongsTo
    {
        return $this->belongsTo(SopLegalDocument::class);
    }

    // Activity logging hanya untuk upload (created event)
    protected static function booted()
    {
        static::created(function ($clientDocument) {
            $filename = $clientDocument->original_filename ?? basename($clientDocument->file_path);
            $clientName = $clientDocument->client->name;
            
            // Determine if this is a legal document
            $isLegalDocument = $clientDocument->isLegalDocument();
            
            if ($isLegalDocument) {
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
    }

    // Essential helper: Check if legal document
    public function isLegalDocument(): bool
    {
        $filename = strtolower($this->original_filename ?? '');
        $filepath = strtolower($this->file_path ?? '');
        
        // Check by file path (if stored in legal folder)
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

    // Essential accessor: Get filename
    public function getFileNameAttribute(): string
    {
        return $this->original_filename ?? basename($this->file_path ?? 'Unknown');
    }

    // Essential accessor: Get file URL
    public function getFileUrlAttribute(): ?string
    {
        if ($this->file_path && \Storage::disk('public')->exists($this->file_path)) {
            return \Storage::disk('public')->url($this->file_path);
        }
        return null;
    }

    // Essential static method: Upload helper
    public static function uploadForClient(Client $client, $file): self
    {
        // Generate filename
        $originalName = $file->getClientOriginalName();
        $filename = time() . '_' . $originalName;
        
        // Determine storage path
        $tempDocument = new self(['original_filename' => $originalName]);
        $isLegal = $tempDocument->isLegalDocument();
        
        $storagePath = $isLegal 
            ? $client->getLegalFolderPath() 
            : $client->getFolderPath();
        
        // Store file
        $filePath = $file->storeAs($storagePath, $filename, 'public');
        
        // Create document record
        return self::create([
            'client_id' => $client->id,
            'user_id' => auth()->id(),
            'file_path' => $filePath,
            'original_filename' => $originalName,
        ]);
    }

    // Essential scope: Legal documents
    public function scopeLegalDocuments($query)
    {
        return $query->where(function($q) {
            $q->where('file_path', 'like', '%/legal/%')
              ->orWhere('original_filename', 'like', '%legal%')
              ->orWhere('original_filename', 'like', '%kontrak%')
              ->orWhere('original_filename', 'like', '%akta%')
              ->orWhere('original_filename', 'like', '%perjanjian%')
              ->orWhere('original_filename', 'like', '%contract%')
              ->orWhere('original_filename', 'like', '%agreement%');
        });
    }
}