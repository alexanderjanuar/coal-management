<?php

use App\Models\ChatThread;
use App\Services\ChatService;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.thread.{threadId}', function ($user, int $threadId): bool {
    $thread = ChatThread::find($threadId);

    if (!$thread) {
        return false;
    }

    try {
        app(ChatService::class)->authorizeThreadAccess($user, $thread);

        return true;
    } catch (Throwable) {
        return false;
    }
});
