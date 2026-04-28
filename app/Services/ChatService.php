<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\ChatThread;
use App\Models\Client;
use App\Models\TaxReport;
use App\Models\User;
use App\Models\UserClient;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChatService
{
    public const THREAD_TYPES = [
        'general',
        'project',
        'tax_report',
        'client_support',
    ];

    public function accessibleClients(User $user): EloquentCollection
    {
        if ($user->hasRole('super-admin')) {
            return Client::query()
                ->select(['id', 'name', 'logo', 'status'])
                ->orderBy('name')
                ->get();
        }

        return Client::query()
            ->select(['clients.id', 'clients.name', 'clients.logo', 'clients.status'])
            ->whereHas('userClients', fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('name')
            ->get();
    }

    public function threadsForClient(User $user, int|Client $client): EloquentCollection
    {
        $client = $this->resolveClient($client);
        $this->authorizeClientAccess($user, $client);

        return ChatThread::query()
            ->forUser($user)
            ->where('client_id', $client->id)
            ->with([
                'client:id,name,logo',
                'latestMessage.user:id,name,email,avatar_url',
                'participants.user:id,name,email,avatar_url',
            ])
            ->orderByDesc(DB::raw('COALESCE(latest_message_at, created_at)'))
            ->get();
    }

    public function threadsForInbox(User $user): EloquentCollection
    {
        return ChatThread::query()
            ->when(!$user->hasRole('super-admin'), function ($query) use ($user) {
                $query->where(function ($threadQuery) use ($user) {
                    $threadQuery
                        ->whereHas('participants', fn ($participantQuery) => $participantQuery->where('user_id', $user->id))
                        ->orWhereHas('client.userClients', fn ($clientQuery) => $clientQuery->where('user_id', $user->id));
                });
            })
            ->with([
                'client:id,name,logo',
                'latestMessage.user:id,name,email,avatar_url',
                'participants.user:id,name,email,avatar_url',
            ])
            ->orderByDesc(DB::raw('COALESCE(latest_message_at, created_at)'))
            ->get();
    }

    public function createThread(
        User $creator,
        int|Client $client,
        ?string $title = null,
        string $type = 'client_support',
        ?int $projectId = null,
        ?int $taxReportId = null,
        array $participantIds = [],
    ): ChatThread {
        $client = $this->resolveClient($client);
        $this->authorizeClientAccess($creator, $client);
        $this->validateThreadContext($client, $type, $projectId, $taxReportId);

        return DB::transaction(function () use ($creator, $client, $title, $type, $projectId, $taxReportId, $participantIds): ChatThread {
            $thread = ChatThread::create([
                'client_id' => $client->id,
                'project_id' => $projectId,
                'tax_report_id' => $taxReportId,
                'created_by_id' => $creator->id,
                'title' => $title ?: 'Percakapan ' . $client->name,
                'type' => $type,
            ]);

            $this->syncParticipants($thread, collect($participantIds)->push($creator->id));

            return $thread->load([
                'client:id,name,logo',
                'latestMessage.user:id,name,email,avatar_url',
                'participants.user:id,name,email,avatar_url',
            ]);
        });
    }

    public function sendMessage(User $sender, int|ChatThread $thread, ?string $body, array $attachments = []): ChatMessage
    {
        $thread = $this->resolveThread($thread);
        $this->authorizeThreadAccess($sender, $thread);
        $this->ensureParticipant($thread, $sender);

        $body = trim((string) $body);
        if ($body === '' && empty($attachments)) {
            throw ValidationException::withMessages([
                'messageBody' => 'Tulis pesan atau lampirkan file terlebih dahulu.',
            ]);
        }

        return ChatMessage::create([
            'chat_thread_id' => $thread->id,
            'user_id' => $sender->id,
            'body' => $body === '' ? null : $body,
            'attachments' => $attachments ?: null,
        ])->load('user:id,name,email,avatar_url');
    }

    public function markAsRead(User $user, int|ChatThread $thread): void
    {
        $thread = $this->resolveThread($thread);
        $this->authorizeThreadAccess($user, $thread);

        ChatParticipant::query()
            ->where('chat_thread_id', $thread->id)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);
    }

    public function authorizeClientAccess(User $user, int|Client $client): void
    {
        $client = $this->resolveClient($client);

        if ($user->hasRole('super-admin')) {
            return;
        }

        $hasAccess = UserClient::query()
            ->where('user_id', $user->id)
            ->where('client_id', $client->id)
            ->exists();

        if (!$hasAccess) {
            throw new AuthorizationException('Anda tidak memiliki akses ke klien ini.');
        }
    }

    public function authorizeThreadAccess(User $user, int|ChatThread $thread): void
    {
        $thread = $this->resolveThread($thread);

        if ($user->hasRole('super-admin')) {
            return;
        }

        if ($thread->hasParticipant($user)) {
            return;
        }

        $hasClientAccess = !$user->hasRole('client')
            && UserClient::query()
                ->where('user_id', $user->id)
                ->where('client_id', $thread->client_id)
                ->exists();

        if ($hasClientAccess) {
            return;
        }

        throw new AuthorizationException('Anda bukan peserta percakapan ini.');
    }

    private function syncParticipants(ChatThread $thread, Collection $participantIds): void
    {
        $assignedUserIds = UserClient::query()
            ->where('client_id', $thread->client_id)
            ->pluck('user_id');

        $assignedUserIds
            ->merge($participantIds)
            ->filter()
            ->unique()
            ->each(function (int $userId) use ($thread): void {
                ChatParticipant::firstOrCreate([
                    'chat_thread_id' => $thread->id,
                    'user_id' => $userId,
                ]);
            });
    }

    private function ensureParticipant(ChatThread $thread, User $user): void
    {
        ChatParticipant::firstOrCreate([
            'chat_thread_id' => $thread->id,
            'user_id' => $user->id,
        ]);
    }

    private function validateThreadContext(Client $client, string $type, ?int $projectId, ?int $taxReportId): void
    {
        if (!in_array($type, self::THREAD_TYPES, true)) {
            throw ValidationException::withMessages([
                'threadType' => 'Tipe percakapan tidak valid.',
            ]);
        }

        if ($projectId && !$client->projects()->whereKey($projectId)->exists()) {
            throw ValidationException::withMessages([
                'projectId' => 'Proyek tidak terhubung dengan klien ini.',
            ]);
        }

        if ($taxReportId && !TaxReport::query()->whereKey($taxReportId)->where('client_id', $client->id)->exists()) {
            throw ValidationException::withMessages([
                'taxReportId' => 'Laporan pajak tidak terhubung dengan klien ini.',
            ]);
        }
    }

    private function resolveClient(int|Client $client): Client
    {
        return $client instanceof Client ? $client : Client::findOrFail($client);
    }

    private function resolveThread(int|ChatThread $thread): ChatThread
    {
        return $thread instanceof ChatThread ? $thread : ChatThread::findOrFail($thread);
    }
}
