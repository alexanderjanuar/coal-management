<div
    x-data="{
        activeThreadId: @js($activeThread?->id),
        subscribedThreadId: null,
        init() {
            this.subscribe(this.activeThreadId);

            Livewire.on('chat-thread-selected', (event) => {
                const threadId = event.threadId ?? event[0]?.threadId ?? null;
                this.subscribe(threadId);
                this.scrollToBottom();
            });

            Livewire.on('chat-message-posted', () => this.scrollToBottom());

            this.$nextTick(() => this.scrollToBottom());
        },
        subscribe(threadId) {
            if (!window.Echo || !threadId || this.subscribedThreadId === threadId) {
                return;
            }

            if (this.subscribedThreadId) {
                window.Echo.leave(`chat.thread.${this.subscribedThreadId}`);
            }

            this.activeThreadId = threadId;
            this.subscribedThreadId = threadId;

            window.Echo.private(`chat.thread.${threadId}`)
                .listen('.message.sent', (payload) => {
                    $wire.handleBroadcastedMessage(payload?.message?.id ?? null);
                    this.scrollToBottom();
                });
        },
        scrollToBottom() {
            this.$nextTick(() => {
                const list = this.$refs.messages;
                if (list) {
                    list.scrollTop = list.scrollHeight;
                }
            });
        }
    }"
    class="space-y-4"
>
    @if (!$hasClients)
        <div class="flex min-h-[420px] items-center justify-center rounded-lg border border-dashed border-gray-300 bg-white text-sm text-gray-500 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
            Belum ada klien yang tersedia.
        </div>
    @else
        <div class="grid min-h-[620px] overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-950 lg:grid-cols-[340px_minmax(0,1fr)]">
            <aside class="border-b border-gray-200 bg-gray-50/80 dark:border-gray-800 dark:bg-gray-900/70 lg:border-b-0 lg:border-r">
                <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-gray-950 dark:text-white">
                            {{ $showAllClients ? 'Semua Percakapan' : $selectedClient->name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $threads->count() }} percakapan</p>
                    </div>

                    <button
                        type="button"
                        wire:click="toggleNewThreadForm"
                        title="Percakapan baru"
                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:border-primary-300 hover:text-primary-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-primary-500 dark:hover:text-primary-400"
                    >
                        <x-heroicon-o-plus class="h-4 w-4" />
                    </button>
                </div>

                @if ($showNewThreadForm)
                    <form wire:submit.prevent="createThread" class="border-b border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-950">
                        <div class="space-y-2">
                            @if ($showAllClients)
                                <select
                                    wire:model.defer="newThreadClientId"
                                    class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                >
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            @endif

                            <div class="flex gap-2">
                            <input
                                type="text"
                                wire:model.defer="newThreadTitle"
                                placeholder="Judul percakapan"
                                class="min-w-0 flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                            <button
                                type="submit"
                                title="Buat percakapan"
                                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-primary-600 text-white transition hover:bg-primary-700"
                            >
                                <x-heroicon-o-check class="h-4 w-4" />
                            </button>
                            </div>
                        </div>
                        @error('newThreadClientId')
                            <p class="mt-2 text-xs text-danger-600">{{ $message }}</p>
                        @enderror
                        @error('newThreadTitle')
                            <p class="mt-2 text-xs text-danger-600">{{ $message }}</p>
                        @enderror
                    </form>
                @endif

                <div class="max-h-[540px] overflow-y-auto">
                    @forelse ($threads as $thread)
                        @php
                            $latestMessage = $thread->latestMessage;
                            $isActive = $activeThread?->id === $thread->id;
                        @endphp

                        <button
                            type="button"
                            wire:click="selectThread({{ $thread->id }})"
                            class="block w-full border-b border-gray-200 px-4 py-3 text-left transition dark:border-gray-800 {{ $isActive ? 'bg-white dark:bg-gray-950' : 'hover:bg-white/80 dark:hover:bg-gray-950/70' }}"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold {{ $isActive ? 'text-primary-700 dark:text-primary-300' : 'text-gray-900 dark:text-white' }}">
                                        {{ $thread->title ?: 'Percakapan' }}
                                    </p>
                                    @if ($showAllClients)
                                        <p class="mt-0.5 truncate text-[11px] font-medium uppercase tracking-wide text-primary-600 dark:text-primary-400">
                                            {{ $thread->client?->name ?? 'Klien' }}
                                        </p>
                                    @endif
                                    <p class="mt-1 line-clamp-2 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $latestMessage?->body ?: 'Belum ada pesan.' }}
                                    </p>
                                </div>
                                <span class="shrink-0 text-[11px] text-gray-400">
                                    {{ ($latestMessage?->created_at ?? $thread->created_at)?->format('H:i') }}
                                </span>
                            </div>
                        </button>
                    @empty
                        <div class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                            Belum ada percakapan.
                        </div>
                    @endforelse
                </div>
            </aside>

            <section class="flex min-h-[620px] flex-col bg-white dark:bg-gray-950">
                @if ($activeThread)
                    <header class="flex items-center justify-between gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                        <div class="min-w-0">
                            <h2 class="truncate text-base font-semibold text-gray-950 dark:text-white">{{ $activeThread->title }}</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @if ($showAllClients && $activeThread->client)
                                    {{ $activeThread->client->name }} ·
                                @endif
                                {{ $activeThread->participants->count() }} peserta
                            </p>
                        </div>

                        <span class="rounded-md bg-primary-50 px-2.5 py-1 text-xs font-medium text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                            {{ str_replace('_', ' ', $activeThread->type) }}
                        </span>
                    </header>

                    <div x-ref="messages" class="flex-1 space-y-3 overflow-y-auto bg-gray-50 px-5 py-5 dark:bg-gray-900/50">
                        @forelse ($messages as $message)
                            @php
                                $isOwn = $message->user_id === auth()->id();
                            @endphp

                            <div class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-[78%]">
                                    @unless ($isOwn)
                                        <p class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                                            {{ $message->user?->name ?? 'Pengguna' }}
                                        </p>
                                    @endunless

                                    <div class="rounded-lg px-4 py-2.5 text-sm shadow-sm {{ $isOwn ? 'bg-primary-600 text-white' : 'border border-gray-200 bg-white text-gray-800 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-100' }}">
                                        <p class="whitespace-pre-wrap break-words">{{ $message->body }}</p>
                                    </div>

                                    <p class="mt-1 text-right text-[11px] text-gray-400">
                                        {{ $message->created_at?->format('d M, H:i') }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="flex h-full items-center justify-center text-sm text-gray-500 dark:text-gray-400">
                                Belum ada pesan.
                            </div>
                        @endforelse
                    </div>

                    <form wire:submit.prevent="sendMessage" class="border-t border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950">
                        <div class="flex items-end gap-3">
                            <textarea
                                wire:model.defer="messageBody"
                                rows="2"
                                placeholder="Tulis pesan"
                                class="max-h-32 min-h-11 flex-1 resize-none rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            ></textarea>

                            <button
                                type="submit"
                                title="Kirim pesan"
                                wire:loading.attr="disabled"
                                wire:target="sendMessage"
                                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-primary-600 text-white shadow-sm transition hover:bg-primary-700 disabled:cursor-wait disabled:opacity-70"
                            >
                                <x-heroicon-o-paper-airplane class="h-5 w-5" />
                            </button>
                        </div>
                        @error('messageBody')
                            <p class="mt-2 text-xs text-danger-600">{{ $message }}</p>
                        @enderror
                    </form>
                @else
                    <div class="flex flex-1 flex-col items-center justify-center gap-3 px-6 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-300">
                            <x-heroicon-o-chat-bubble-left-right class="h-6 w-6" />
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Belum ada percakapan aktif</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ $showAllClients ? 'Buat percakapan baru untuk salah satu klien.' : 'Buat percakapan baru untuk klien ini.' }}
                            </p>
                        </div>
                        <button
                            type="button"
                            wire:click="toggleNewThreadForm"
                            class="inline-flex items-center gap-2 rounded-md bg-primary-600 px-3 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-primary-700"
                        >
                            <x-heroicon-o-plus class="h-4 w-4" />
                            <span>Percakapan baru</span>
                        </button>
                    </div>
                @endif
            </section>
        </div>
    @endif
</div>
