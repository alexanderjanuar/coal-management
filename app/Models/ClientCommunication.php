<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientCommunication extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'title',
        'description',
        'type',
        'communication_date',
        'communication_time',
        'notes',
        'attachments',
    ];

    protected $casts = [
        'communication_date' => 'date',
        'attachments' => 'array',
    ];

    /**
     * Get attachments as array, always return array even if null
     */
    public function getAttachmentsAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }
        
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        return is_array($value) ? $value : [];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the icon for the communication type
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'meeting' => 'heroicon-o-calendar',
            'email' => 'heroicon-o-envelope',
            'phone' => 'heroicon-o-phone',
            'video_call' => 'heroicon-o-video-camera',
            default => 'heroicon-o-chat-bubble-left-right',
        };
    }

    /**
     * Get the label for the communication type
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'meeting' => 'Meeting',
            'email' => 'Email',
            'phone' => 'Telepon',
            'video_call' => 'Video Call',
            default => 'Lainnya',
        };
    }
}