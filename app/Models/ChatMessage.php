<?php

namespace App\Models;

use App\Events\ChatMessageSent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'chat_thread_id',
        'user_id',
        'body',
        'attachments',
        'metadata',
        'edited_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'metadata' => 'array',
        'edited_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (ChatMessage $message): void {
            $message->thread()->update([
                'latest_message_at' => $message->created_at,
            ]);

            event(new ChatMessageSent($message));
        });
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(ChatThread::class, 'chat_thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
