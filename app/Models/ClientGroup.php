<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientGroup extends Model
{
    protected $fillable = [
        'name',
        'logo',
        'address',
        'contact_name',
        'contact_email',
        'contact_phone',
        'status',
        'notes',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'group_id');
    }

    public function activeClients(): HasMany
    {
        return $this->hasMany(Client::class, 'group_id')->where('status', 'Active');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
