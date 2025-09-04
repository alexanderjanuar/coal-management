<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AccountRepresentative extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'phone_number', 
        'email',
        'KPP',
        'notes',
        'status'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get all clients assigned to this Account Representative
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'ar_id');
    }

    /**
     * Get active clients only
     */
    public function activeClients(): HasMany
    {
        return $this->hasMany(Client::class, 'ar_id')->where('status', 'Active');
    }

    /**
     * Check if AR is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Scope to get only active Account Representatives
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get count of clients handled by this AR
     */
    public function getClientCountAttribute(): int
    {
        return $this->clients()->count();
    }

    /**
     * Get count of active clients handled by this AR
     */
    public function getActiveClientCountAttribute(): int
    {
        return $this->activeClients()->count();
    }

    /**
     * Get formatted phone number for display
     */
    public function getFormattedPhoneAttribute(): string
    {
        return $this->phone_number ? $this->phone_number : 'Tidak tersedia';
    }

    /**
     * Activity Log Configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'phone_number', 'email', 'office_location', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                return match($eventName) {
                    'created' => "Account Representative baru ditambahkan: {$this->name}",
                    'updated' => match($this->status) {
                        'inactive' => "Account Representative {$this->name} dinonaktifkan",
                        'active' => "Account Representative {$this->name} diaktifkan",
                        default => "Data Account Representative {$this->name} diperbarui"
                    },
                    'deleted' => "Account Representative {$this->name} dihapus",
                    default => "Account Representative {$this->name} telah di{$eventName}"
                };
            })
            ->logFillable();
    }

    /**
     * Get full contact info for display
     */
    public function getFullContactAttribute(): string
    {
        $contact = $this->name;
        
        if ($this->phone_number) {
            $contact .= ' | ' . $this->phone_number;
        }
        
        if ($this->email) {
            $contact .= ' | ' . $this->email;
        }
        
        return $contact;
    }
}