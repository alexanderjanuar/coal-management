<div class="space-y-4">
    <!-- Comment Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-700">
        <form wire:submit="createComment">
            {{ $this->commentForm }}

            <div class="flex justify-end mt-2">
                <x-filament::button type="submit">
                    Kirim Komentar
                </x-filament::button>
            </div>
        </form>
    </div>

    <!-- Existing Comments -->
    <div class="space-y-4">
        @foreach($comments as $comment)
        <div class="space-y-3">
            <div class="flex gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center ring-2 ring-white dark:ring-gray-800">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                            {{ substr($comment->user->name ?? 'U', 0, 1) }}
                        </span>
                    </div>
                </div>
                <div class="flex-1 min-w-0 space-y-3">
                    <div class="bg-white dark:bg-gray-800 rounded-lg px-4 py-3 shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $comment->user->name ?? 'Unknown User' }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $comment->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <div class="prose prose-sm max-w-none mt-2 text-gray-700 dark:text-gray-300">
                            {!! $comment->content !!}
                        </div>

                        <div class="mt-3 flex items-center gap-4 border-t border-gray-100 dark:border-gray-700 pt-2">
                            <button wire:click="toggleReplyForm({{ $comment->id }})" type="button"
                                class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                                <x-heroicon-s-arrow-uturn-left class="w-4 h-4" />
                                Balas
                            </button>
                        </div>
                    </div>

                    @if($showReplyForm === $comment->id)
                    <div class="ml-4">
                        <form wire:submit="replyToComment({{ $comment->id }})">
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
                                {{ $this->replyForm }}

                                <div class="flex justify-end gap-2 mt-2">
                                    <x-filament::button type="button" color="gray" wire:click="toggleReplyForm(null)">
                                        Batal
                                    </x-filament::button>
                                    <x-filament::button type="submit">
                                        Kirim Balasan
                                    </x-filament::button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @endif

                    @if($comment->replies->count() > 0)
                    <div class="ml-6 space-y-3 border-l-2 border-gray-100 dark:border-gray-700 pl-4">
                        @foreach($comment->replies as $reply)
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center ring-2 ring-white dark:ring-gray-800">
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                        {{ substr($reply->user->name ?? 'U', 0, 1) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg px-3 py-2 shadow-sm border border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $reply->user->name ?? 'Unknown User' }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $reply->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    <div class="prose prose-sm max-w-none mt-1 text-gray-700 dark:text-gray-300">
                                        {!! $reply->content !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
