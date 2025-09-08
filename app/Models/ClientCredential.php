<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ClientCredential extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'client_id',
        'core_tax_user_id',
        'core_tax_password',
        'email',
        'email_password',
        'credential_type',
        'notes',
        'last_used_at',
        'is_active'
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'core_tax_password',
        'email_password',
    ];

    /**
     * Relationship dengan Client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Scope untuk credential yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk tipe credential tertentu
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('credential_type', $type);
    }

    /**
     * Scope untuk Core Tax credentials
     */
    public function scopeCoreTax($query)
    {
        return $query->where('credential_type', 'core_tax')
                    ->orWhere(function($q) {
                        $q->where('credential_type', 'general')
                          ->whereNotNull('core_tax_user_id');
                    });
    }

    /**
     * Scope untuk Email credentials
     */
    public function scopeEmail($query)
    {
        return $query->where('credential_type', 'email')
                    ->orWhere(function($q) {
                        $q->where('credential_type', 'general')
                          ->whereNotNull('email');
                    });
    }


    /**
     * Method untuk mendapatkan password asli (hanya untuk kebutuhan sistem)
     */
    public function getRawCoreTaxPassword(): ?string
    {
        return $this->getOriginal('core_tax_password');
    }

    public function getRawEmailPassword(): ?string
    {
        return $this->getOriginal('email_password');
    }

    /**
     * Check apakah credential lengkap
     */
    public function isComplete(): bool
    {
        return match($this->credential_type) {
            'core_tax' => !empty($this->core_tax_user_id) && !empty($this->core_tax_password),
            'email' => !empty($this->email) && !empty($this->email_password),
            'general' => $this->hasCoreTaxCredentials() || $this->hasEmailCredentials(),
            default => false,
        };
    }

    /**
     * Check apakah ada credential Core Tax
     */
    public function hasCoreTaxCredentials(): bool
    {
        return !empty($this->core_tax_user_id) && !empty($this->getRawCoreTaxPassword());
    }

    /**
     * Check apakah ada credential Email
     */
    public function hasEmailCredentials(): bool
    {
        return !empty($this->email) && !empty($this->getRawEmailPassword());
    }

    /**
     * Update last used timestamp
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Deactivate credential
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Activate credential
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Get credential status untuk display
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'Tidak Aktif';
        }

        if (!$this->isComplete()) {
            return 'Belum Lengkap';
        }

        return 'Aktif';
    }

    /**
     * Get credential status color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'Aktif' => 'success',
            'Belum Lengkap' => 'warning',
            'Tidak Aktif' => 'danger',
            default => 'gray'
        };
    }

    /**
     * Activity Log Configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'client_id',
                'core_tax_user_id',
                'email',
                'credential_type',
                'is_active'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $clientName = $this->client?->name ?? 'Klien';
                $credType = ucfirst(str_replace('_', ' ', $this->credential_type));
                
                return match($eventName) {
                    'created' => "[{$clientName}] 🔑 Credential {$credType} baru ditambahkan",
                    'updated' => "[{$clientName}] 🔧 Credential {$credType} diperbarui",
                    'deleted' => "[{$clientName}] 🗑️ Credential {$credType} dihapus",
                    default => "[{$clientName}] Credential {$credType} {$eventName}"
                };
            });
    }
}