<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientAffiliate extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'company_name',
        'relationship_type',
        'ownership_percentage',
        'npwp',
        'affiliated_client_id',
        'notes',
        'status',
    ];

    protected $casts = [
        'ownership_percentage' => 'decimal:2',
    ];

    /**
     * Relationship ke client utama
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relationship ke client afiliasi (jika afiliasi juga adalah client)
     */
    public function affiliatedClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'affiliated_client_id');
    }

    /**
     * Scope untuk afiliasi yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope berdasarkan tipe hubungan
     */
    public function scopeByRelationshipType($query, string $type)
    {
        return $query->where('relationship_type', $type);
    }

    /**
     * Check apakah afiliasi aktif
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Format ownership percentage untuk display
     */
    public function getFormattedOwnershipAttribute(): string
    {
        return $this->ownership_percentage 
            ? number_format($this->ownership_percentage, 0) . '%' 
            : '-';
    }

    /**
     * Format NPWP untuk display
     */
    public function getFormattedNpwpAttribute(): string
    {
        if (!$this->npwp) {
            return '-';
        }
        
        // Format: XX.XXX.XXX.X-XXX.XXX
        return preg_replace(
            '/(\d{2})(\d{3})(\d{3})(\d{1})(\d{3})(\d{3})/',
            '$1.$2.$3.$4-$5.$6',
            $this->npwp
        );
    }

    /**
     * Get relationship type options untuk form
     */
    public static function getRelationshipTypes(): array
    {
        return [
            'Anak Perusahaan' => 'Anak Perusahaan',
            'Afiliasi' => 'Afiliasi',
            'Perusahaan Induk' => 'Perusahaan Induk',
            'Sister Company' => 'Sister Company',
            'Joint Venture' => 'Joint Venture',
            'Lainnya' => 'Lainnya',
        ];
    }

    /**
     * Get badge color berdasarkan relationship type
     */
    public function getRelationshipBadgeColor(): string
    {
        return match($this->relationship_type) {
            'Anak Perusahaan' => 'success',
            'Perusahaan Induk' => 'primary',
            'Afiliasi' => 'info',
            'Sister Company' => 'warning',
            'Joint Venture' => 'purple',
            default => 'gray',
        };
    }

    /**
     * Get badge color untuk status
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'active' => 'success',
            'inactive' => 'danger',
            default => 'gray',
        };
    }
}