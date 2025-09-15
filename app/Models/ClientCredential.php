<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ClientCredential extends Model
{
    use HasFactory, LogsActivity;


    /**
     * Relationship ke Client (one-to-one melalui foreign key di clients table)
     */
    public function client(): HasOne
    {
        return $this->hasOne(Client::class, 'credential_id');
    }

    /**
     * Check apakah credential aktif
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Scope untuk credential yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope berdasarkan tipe credential
     */
    public function scopeByType($query, $type)
    {
        return $query->where('credential_type', $type);
    }

    /**
     * Update last used timestamp
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Activity Log Configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'core_tax_user_id',
                'djp_account', 
                'email',
                'credential_type',
                'is_active'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $clientName = $this->client?->name ?? 'Klien Tidak Diketahui';
                $userName = auth()->user()?->name ?? 'System';
                
                return match($eventName) {
                    'created' => "[{$clientName}] 🔐 KREDENSIAL BARU: {$this->formatted_credentials} | Dibuat oleh: {$userName}",
                    'updated' => match($this->is_active) {
                        true => "[{$clientName}] ✅ KREDENSIAL DIAKTIFKAN: {$this->formatted_credentials} | Oleh: {$userName}",
                        false => "[{$clientName}] ❌ KREDENSIAL DINONAKTIFKAN: {$this->formatted_credentials} | Oleh: {$userName}",
                        default => "[{$clientName}] 📝 KREDENSIAL DIPERBARUI: {$this->formatted_credentials} | Oleh: {$userName}"
                    },
                    'deleted' => "[{$clientName}] 🗑️ KREDENSIAL DIHAPUS: {$this->formatted_credentials} | Oleh: {$userName}",
                    default => "[{$clientName}] Kredensial telah {$eventName} oleh {$userName}"
                };
            });
    }

    /**
     * Disable credential
     */
    public function disable(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Enable credential
     */
    public function enable(): void
    {
        $this->update(['is_active' => true]);
    }
}