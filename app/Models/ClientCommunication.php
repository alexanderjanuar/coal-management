<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientCommunication extends Model
{
    protected $fillable = [
        'client_id',
        'user_id',
        'title',
        'description',
        'type',
        'communication_date',
        'communication_time',
        'notes',
    ];

    protected $casts = [
        'communication_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'meeting' => 'heroicon-o-users',
            'email' => 'heroicon-o-envelope',
            'phone' => 'heroicon-o-phone',
            'video_call' => 'heroicon-o-video-camera',
            default => 'heroicon-o-chat-bubble-left-right',
        };
    }

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

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'meeting' => 'blue',
            'email' => 'purple',
            'phone' => 'green',
            'video_call' => 'orange',
            default => 'gray',
        };
    }
}