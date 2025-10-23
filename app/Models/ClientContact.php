<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'position',
        'email',
        'phone',
        'mobile',
        'type',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationship
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('type', 'primary');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Accessors
    public function getFullContactAttribute(): string
    {
        $contact = [];
        
        if ($this->email) {
            $contact[] = $this->email;
        }
        
        if ($this->phone) {
            $contact[] = $this->phone;
        }
        
        if ($this->mobile) {
            $contact[] = $this->mobile;
        }
        
        return implode(' | ', $contact);
    }

    public function getDisplayNameAttribute(): string
    {
        $display = $this->name;
        
        if ($this->position) {
            $display .= " ({$this->position})";
        }
        
        return $display;
    }
}