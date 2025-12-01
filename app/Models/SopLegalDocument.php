<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SopLegalDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'client_type',
        'is_required',
        'category',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    // Relationship ke uploaded documents
    public function clientDocuments(): HasMany
    {
        return $this->hasMany(ClientDocument::class);
    }

    // Scope untuk filter by client type
    public function scopeForClientType($query, ?string $clientType)
    {
        // If client type is null, return all documents marked as 'Both' or empty result
        if ($clientType === null) {
            return $query->where('client_type', 'Both');
        }
        
        return $query->where(function ($q) use ($clientType) {
            $q->where('client_type', $clientType)
              ->orWhere('client_type', 'Both');
        });
    }

    // Scope untuk dokumen aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope untuk dokumen wajib
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    // Scope by category
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // Check if this SOP document is uploaded for specific client
    public function isUploadedForClient(Client $client): bool
    {
        return $this->clientDocuments()
                    ->where('client_id', $client->id)
                    ->exists();
    }

    // Check if applicable for client type
    public function isApplicableFor(?string $clientType): bool
    {
        if ($clientType === null) {
            return $this->client_type === 'Both';
        }
        
        return $this->client_type === $clientType || $this->client_type === 'Both';
    }
}