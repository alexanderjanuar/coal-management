<?php

namespace App\Models;

use App\Traits\Trackable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory,Trackable;

    protected $fillable = ['name', 'email', 'logo'];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function applicationCredentials(): HasMany
    {
        return $this->hasMany(ApplicationClient::class);
    }

    public function activeApplications(): HasMany
    {
        return $this->hasMany(ApplicationClient::class)->where('is_active', true);
    }   

    public function applications()
    {
        return $this->hasMany(ApplicationClient::class);
    }

    public function taxreports()
    {
        return $this->hasMany(TaxReport::class);
    }

    /**
     * Get the PIC that manages this client
     */
    public function pic(): BelongsTo
    {
        return $this->belongsTo(Pic::class);
    }

    public function accountRepresentative(): BelongsTo
    {
        return $this->belongsTo(AccountRepresentative::class, 'ar_id');
    }

    public function userClients()
    {
        return $this->hasMany(UserClient::class);
    }

    

    /**
     * Relationship ke credential utama
     */
    public function clientCredential(): BelongsTo
    {
        return $this->belongsTo(ClientCredential::class, 'credential_id');
    }

    public function clientDocuments(): HasMany
    {
        return $this->hasMany(ClientDocument::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }


    /**
     * Get client folder path
     */
    public function getFolderPath(): string
    {
        $sluggedName = Str::slug($this->name);
        return "clients/{$sluggedName}";
    }

    /**
     * Get legal documents folder path
     */
    public function getLegalFolderPath(): string
    {
        return $this->getFolderPath() . '/Legal';
    }

    /**
     * Clean up client folder when client is deleted
     */
    public function cleanupClientFolder(): void
    {
        $folderPath = $this->getFolderPath();
        
        try {
            // Delete all files in client folder recursively
            Storage::disk('public')->deleteDirectory($folderPath);
            
            \Log::info("Cleaned up client folder: {$folderPath}");
        } catch (\Exception $e) {
            \Log::error("Failed to cleanup client folder: {$folderPath}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create client folders if they don't exist
     */
    public function ensureFoldersExist(): void
    {
        $legalFolderPath = $this->getLegalFolderPath();
        
        if (!Storage::disk('public')->exists($legalFolderPath)) {
            Storage::disk('public')->makeDirectory($legalFolderPath);
        }
    }


    /**
     * Get SOP legal documents yang applicable untuk client ini
     */
    public function getApplicableSopDocuments()
    {
        return SopLegalDocument::forClientType($this->client_type)
                            ->active()
                            ->orderBy('category')
                            ->orderBy('order')
                            ->get();
    }

    /**
     * Get dokumen checklist dengan status upload
     * Return collection dengan info: SOP template + status upload
     */
    public function getLegalDocumentsChecklist()
    {
        // Ambil semua SOP dokumen yang applicable untuk tipe klien ini
        $sopDocuments = $this->getApplicableSopDocuments();
        
        // Map setiap SOP dengan status uploadnya
        return $sopDocuments->map(function ($sopDoc) {
            // Cari dokumen yang sudah diupload untuk SOP ini
            $uploadedDoc = $this->clientDocuments()
                            ->where('sop_legal_document_id', $sopDoc->id)
                            ->latest()
                            ->first();
            
            return [
                'sop_id' => $sopDoc->id,
                'name' => $sopDoc->name,
                'description' => $sopDoc->description,
                'category' => $sopDoc->category,
                'is_required' => $sopDoc->is_required,
                'is_uploaded' => $uploadedDoc !== null,
                'uploaded_document' => $uploadedDoc,
                'file_path' => $uploadedDoc?->file_path,
                'uploaded_at' => $uploadedDoc?->created_at,
                'uploaded_by' => $uploadedDoc?->user,
            ];
        });
    }

    /**
     * Get statistics dokumen legal
     */
    public function getLegalDocumentsStats()
    {
        $checklist = $this->getLegalDocumentsChecklist();
        
        $totalRequired = $checklist->where('is_required', true)->count();
        $uploadedRequired = $checklist->where('is_required', true)
                                    ->where('is_uploaded', true)
                                    ->count();
        
        return [
            'total_documents' => $checklist->count(),
            'total_required' => $totalRequired,
            'total_optional' => $checklist->where('is_required', false)->count(),
            'uploaded' => $checklist->where('is_uploaded', true)->count(),
            'not_uploaded' => $checklist->where('is_uploaded', false)->count(),
            'completion_percentage' => $totalRequired > 0 
                ? round(($uploadedRequired / $totalRequired) * 100, 2) 
                : 100,
        ];
    }

    /**
     * Get missing required documents
     */
    public function getMissingRequiredDocuments()
    {
        return $this->getLegalDocumentsChecklist()
                    ->where('is_required', true)
                    ->where('is_uploaded', false)
                    ->pluck('name');
    }

    /**
     * Check if all required documents are uploaded
     */
    public function hasAllRequiredDocuments(): bool
    {
        return $this->getMissingRequiredDocuments()->isEmpty();
    }
    
    protected static function booted()
    {
        static::created(function ($client) {
            $client->logActivity(
                'client_created',
                "Klien baru '{$client->name}' telah ditambahkan ke sistem"
            );
        });

        static::updated(function ($client) {
            if ($client->wasChanged('status')) {
                $status = $client->status;
                $client->logActivity(
                    'client_status_changed',
                    "Status klien '{$client->name}' diubah menjadi: {$status}"
                );
            }

            if ($client->wasChanged('pic_id')) {
                $picName = $client->pic?->name ?? 'Tidak ada';
                $client->logActivity(
                    'client_pic_changed',
                    "PIC klien '{$client->name}' diubah menjadi: {$picName}"
                );
            }

            if ($client->wasChanged('ar_id')) {
                $arName = $client->ar?->name ?? 'Tidak ada';
                $client->logActivity(
                    'client_ar_changed',
                    "Account Representative klien '{$client->name}' diubah menjadi: {$arName}"
                );
            }

            // Log contract changes
            $contractFields = ['ppn_contract', 'pph_contract', 'bupot_contract'];
            foreach ($contractFields as $field) {
                if ($client->wasChanged($field)) {
                    $status = $client->$field ? 'Aktif' : 'Tidak Aktif';
                    $contractType = str_replace('_contract', '', $field);
                    $client->logActivity(
                        'client_contract_changed',
                        "Kontrak {$contractType} klien '{$client->name}' diubah menjadi: {$status}"
                    );
                }
            }
        });

        static::deleted(function ($client) {
            $client->logActivity(
                'client_deleted',
                "Klien '{$client->name}' telah dihapus dari sistem"
            );
        });
    }

    // Custom method untuk document upload
    public function logDocumentUpload(string $filename, string $documentType = 'document')
    {
        $this->logActivity(
            'document_uploaded',
            "Dokumen '{$filename}' ({$documentType}) diunggah untuk klien '{$this->name}'"
        );
    }
}
