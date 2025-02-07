<div class="w-full">
    <!-- Task Header - Made more compact for mobile -->
    <div class="mb-4 sm:mb-6">
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between bg-gray-50 rounded-xl p-3 sm:p-4 gap-3 sm:gap-4">
            <div class="flex items-start gap-3 sm:gap-4">
                <!-- Status Icon -->
                <div @class([ 'w-8 h-8 sm:w-10 sm:h-10 rounded-lg flex items-center justify-center flex-shrink-0'
                    , 'bg-success-100 text-success-600'=> $task->status === 'completed',
                    'bg-warning-100 text-warning-600' => $task->status === 'in_progress',
                    'bg-danger-100 text-danger-600' => $task->status === 'blocked',
                    'bg-gray-100 text-gray-600' => $task->status === 'pending',
                    ])>
                    @switch($task->status)
                    @case('completed')
                    <x-heroicon-o-check-circle class="w-4 h-4 sm:w-5 sm:h-5" />
                    @break
                    @case('in_progress')
                    <x-heroicon-o-arrow-path class="w-4 h-4 sm:w-5 sm:h-5 animate-spin-slow" />
                    @break
                    @case('blocked')
                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 sm:w-5 sm:h-5" />
                    @break
                    @default
                    <x-heroicon-o-clock class="w-4 h-4 sm:w-5 sm:h-5" />
                    @endswitch
                </div>

                <div class="min-w-0 flex-1">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 truncate">{{ $task->title }}</h3>
                    @if($task->description)
                    <p class="text-xs sm:text-sm text-gray-500 line-clamp-2 sm:line-clamp-none">{!!
                        str($task->description)->sanitizeHtml() !!}
                    </p>
                    @endif
                </div>
            </div>

            <!-- Status Badge - Adjusted for mobile -->
            <div class="ml-11 sm:ml-0">
                <button x-on:click="$dispatch('close-modal', { id: 'task-modal-{{ $task->id }}' })" type="button"
                    class="flex-shrink-0 rounded-lg p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors duration-200">
                    <span class="sr-only">Close</span>
                    <x-heroicon-m-x-mark class="w-5 h-5" />
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Stats Bar - Made scrollable on mobile -->
    <div class="overflow-x-auto -mx-4 sm:mx-0 mb-4 sm:mb-6">
        <div class="flex items-center gap-4 sm:gap-6 px-4 py-2 bg-gray-50 rounded-lg min-w-max sm:min-w-0">
            <div class="flex items-center gap-2">
                <x-heroicon-o-chat-bubble-left-right class="w-4 h-4 text-gray-400" />
                <span class="text-xs sm:text-sm text-gray-600 whitespace-nowrap">
                    {{ $comments->count() }} {{ Str::plural('Comment', $comments->count()) }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                <span class="text-xs sm:text-sm text-gray-600 whitespace-nowrap">
                    {{ $comments->first()?->created_at?->diffForHumans() ?? 'No activity' }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <x-heroicon-o-users class="w-4 h-4 text-gray-400" />
                <span class="text-xs sm:text-sm text-gray-600 whitespace-nowrap">
                    {{ $comments->unique('user_id')->count() }} {{ Str::plural('Participant',
                    $comments->unique('user_id')->count()) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="space-y-4 sm:space-y-6">
        <!-- Comment Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
            <form wire:submit="createComment" class="p-3 sm:p-4 space-y-3">
                {{ $this->commentForm }}
                <div class="flex justify-end">
                    <x-filament::button type="submit" size="sm" class="w-full sm:w-auto">
                        Post Comment
                    </x-filament::button>
                </div>
            </form>
        </div>

        <!-- Comments List -->
        <div class="space-y-4">
            @forelse($comments as $comment)
            <!-- Comment Container -->
            <div class="space-y-3">
                <!-- Parent Comment -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                    <div class="p-3 sm:p-4">
                        <div class="flex items-start gap-3">
                            <!-- User Avatar -->
                            <div class="flex-shrink-0">
                                <div
                                    class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-primary-50 flex items-center justify-center ring-2 ring-white">
                                    <span class="text-primary-700 font-semibold text-sm">
                                        {{ substr($comment->user->name ?? 'U', 0, 1) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Comment Content -->
                            <div class="flex-grow min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-medium text-sm sm:text-base truncate">{{ $comment->user->name
                                        }}</span>
                                    <span class="text-gray-500 text-xs sm:text-sm">·</span>
                                    <span class="text-gray-500 text-xs sm:text-sm">{{
                                        $comment->created_at->diffForHumans() }}</span>
                                    @if($comment->user_id === auth()->id())
                                    <span
                                        class="text-[10px] sm:text-xs bg-primary-50 text-primary-700 px-1.5 sm:px-2 py-0.5 rounded-full">You</span>
                                    @endif
                                </div>

                                <div class="mt-2 prose prose-sm max-w-none text-gray-700">
                                    {!! $comment->content !!}
                                </div>

                                <!-- Comment Actions -->
                                <div class="mt-3 flex items-center gap-4 pt-2 border-t border-gray-50">
                                    <button wire:click="toggleReplyForm({{ $comment->id }})"
                                        class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-900">
                                        <x-heroicon-o-arrow-uturn-left class="w-4 h-4" />
                                        Reply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reply Form -->
                    @if($showReplyForm === $comment->id)
                    <div class="border-t border-gray-100 bg-gray-50 p-3 sm:p-4 rounded-b-lg">
                        <form wire:submit="replyToComment({{ $comment->id }})">
                            {{ $this->replyForm }}
                            <div class="flex justify-end gap-2 mt-3">
                                <x-filament::button type="button" color="gray" size="sm"
                                    wire:click="toggleReplyForm(null)">
                                    Cancel
                                </x-filament::button>
                                <x-filament::button type="submit" size="sm">
                                    Post Reply
                                </x-filament::button>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>

                <!-- Replies -->
                @if($comment->replies->count() > 0)
                <div class="ml-6 sm:ml-12 space-y-3">
                    @foreach($comment->replies as $reply)
                    <div class="bg-gray-50 rounded-lg shadow-sm border border-gray-100">
                        <div class="p-3 sm:p-4">
                            <div class="flex items-start gap-3">
                                <!-- Reply User Avatar -->
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-white flex items-center justify-center ring-2 ring-gray-100">
                                        <span class="text-gray-700 font-semibold text-xs sm:text-sm">
                                            {{ substr($reply->user->name ?? 'U', 0, 1) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Reply Content -->
                                <div class="flex-grow min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-medium text-sm truncate">{{ $reply->user->name }}</span>
                                        <span class="text-gray-500 text-xs">·</span>
                                        <span class="text-gray-500 text-xs">{{ $reply->created_at->diffForHumans()
                                            }}</span>
                                        @if($reply->user_id === auth()->id())
                                        <span
                                            class="text-[10px] bg-white text-primary-700 px-1.5 py-0.5 rounded-full">You</span>
                                        @endif
                                    </div>

                                    <div class="mt-1.5 prose prose-sm max-w-none text-gray-700">
                                        {!! $reply->content !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @empty
            <div class="text-center py-8 bg-gray-50 rounded-lg border border-gray-100">
                <div class="flex flex-col items-center gap-2">
                    <x-heroicon-o-chat-bubble-left-right class="w-8 h-8 text-gray-400" />
                    <p class="text-sm sm:text-base text-gray-600">No comments yet</p>
                    <p class="text-xs text-gray-500">Be the first to share your thoughts</p>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>