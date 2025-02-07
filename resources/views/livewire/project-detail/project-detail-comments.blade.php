<div class="space-y-4">
    <!-- Comment Form at the top -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <form wire:submit="createComment">
            {{ $this->commentForm }}

            <div class="flex justify-end mt-2">
                <x-filament::button type="submit">
                    Post Comment
                </x-filament::button>
            </div>
        </form>
    </div>

    <!-- Existing Comments -->
    <div class="space-y-4">
        @foreach($comments as $comment)
        <div class="space-y-3">
            <!-- Parent Comment -->
            <div class="flex gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center ring-2 ring-white">
                        <span class="text-sm font-medium text-gray-600">
                            {{ substr($comment->user->name ?? 'U', 0, 1) }}
                        </span>
                    </div>
                </div>
                <div class="flex-1 min-w-0 space-y-3">
                    <!-- Comment Content -->
                    <div class="bg-white rounded-lg px-4 py-3 shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-900">
                                    {{ $comment->user->name ?? 'Unknown User' }}
                                </span>
                                <span class="text-xs text-gray-500">•</span>
                                <span class="text-xs text-gray-500">
                                    {{ $comment->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        <div class="prose prose-sm max-w-none mt-2 text-gray-700">
                            {!! $comment->content !!}
                        </div>

                        <!-- Reply Button -->
                        <div class="mt-3 flex items-center gap-4 border-t border-gray-100 pt-2">
                            <button wire:click="toggleReplyForm({{ $comment->id }})" type="button"
                                class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M7.707 3.293a1 1 0 010 1.414L5.414 7H11a7 7 0 017 7v2a1 1 0 11-2 0v-2a5 5 0 00-5-5H5.414l2.293 2.293a1 1 0 11-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                Reply
                            </button>
                        </div>
                    </div>

                    <!-- Reply Form -->
                    @if($showReplyForm === $comment->id)
                    <div class="ml-4">
                        <form wire:submit="replyToComment({{ $comment->id }})">
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                {{ $this->replyForm }}

                                <div class="flex justify-end gap-2 mt-2">
                                    <x-filament::button type="button" color="gray" wire:click="toggleReplyForm(null)">
                                        Cancel
                                    </x-filament::button>
                                    <x-filament::button type="submit">
                                        Post Reply
                                    </x-filament::button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @endif

                    <!-- Replies -->
                    @if($comment->replies->count() > 0)
                    <div class="ml-6 space-y-3 border-l-2 border-gray-100 pl-4">
                        @foreach($comment->replies as $reply)
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center ring-2 ring-white">
                                    <span class="text-xs font-medium text-gray-600">
                                        {{ substr($reply->user->name ?? 'U', 0, 1) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="bg-gray-50 rounded-lg px-3 py-2 shadow-sm border border-gray-100">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-gray-900">
                                                {{ $reply->user->name ?? 'Unknown User' }}
                                            </span>
                                            <span class="text-xs text-gray-500">•</span>
                                            <span class="text-xs text-gray-500">
                                                {{ $reply->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="prose prose-sm max-w-none mt-1 text-gray-700">
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