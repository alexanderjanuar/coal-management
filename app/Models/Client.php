<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'logo'];

    public function projects()
    {
        return $this->hasMany(Project::class);
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


    public function userClients()
    {
        return $this->hasMany(UserClient::class);
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
}
