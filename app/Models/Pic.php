<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pic extends Model
{
    use HasFactory;

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Get active clients only
     */
    public function activeClients(): HasMany
    {
        return $this->hasMany(Client::class)->where('status', 'Active');
    }

    /**
     * Check if PIC is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Scope to get only active PICs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
