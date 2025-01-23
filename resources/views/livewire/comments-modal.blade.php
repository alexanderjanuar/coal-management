<div class="w-full">
    <!-- Task Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between bg-gray-50 rounded-xl p-4">
            <div class="flex items-center gap-4">
                <!-- Status Icon -->
                <div @class([ 'w-10 h-10 rounded-lg flex items-center justify-center'
                    , 'bg-success-100 text-success-600'=> $task->status === 'completed',
                    'bg-warning-100 text-warning-600' => $task->status === 'in_progress',
                    'bg-danger-100 text-danger-600' => $task->status === 'blocked',
                    'bg-gray-100 text-gray-600' => $task->status === 'pending',
                    ])>
                    @switch($task->status)
                    @case('completed')
                    <x-heroicon-o-check-circle class="w-5 h-5" />
                    @break
                    @case('in_progress')
                    <x-heroicon-o-arrow-path class="w-5 h-5 animate-spin-slow" />
                    @break
                    @case('blocked')
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                    @break
                    @default
                    <x-heroicon-o-clock class="w-5 h-5" />
                    @endswitch
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $task->title }}</h3>
                    @if($task->description)
                    <p class="text-sm text-gray-500">{{ $task->description }}</p>
                    @endif
                </div>
            </div>

            <x-filament::badge :color="match ($task->status) {
                    'completed' => 'success',
                    'in_progress' => 'warning',
                    'blocked' => 'danger',
                    default => 'secondary',
                }">
                {{ ucwords(str_replace('_', ' ', $task->status)) }}
            </x-filament::badge>
        </div>
    </div>

    <!-- Quick Stats Bar -->
    <div class="flex items-center gap-6 px-4 py-2 bg-gray-50 rounded-lg mb-6">
        <div class="flex items-center gap-2">
            <x-heroicon-o-chat-bubble-left-right class="w-4 h-4 text-gray-400" />
            <span class="text-sm text-gray-600">{{ $comments->count() }} {{ Str::plural('Comment', $comments->count())
                }}</span>
        </div>
        <div class="flex items-center gap-2">
            <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
            <span class="text-sm text-gray-600">{{ $comments->first()?->created_at?->diffForHumans() ?? 'No activity'
                }}</span>
        </div>
        <div class="flex items-center gap-2">
            <x-heroicon-o-users class="w-4 h-4 text-gray-400" />
            <span class="text-sm text-gray-600">{{ $comments->unique('user_id')->count() }} {{
                Str::plural('Participant', $comments->unique('user_id')->count()) }}</span>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="space-y-6">
        <!-- Comments List -->
        <div class="w-full space-y-4">
            <!-- Comment Form -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <form wire:submit="addComment" class="space-y-4">
                    {{ $this->form }}

                    <div class="flex justify-end">
                        <x-filament::button type="submit" size="sm">
                            Post Comment
                        </x-filament::button>
                    </div>
                </form>
            </div>

            <!-- Comments List -->
            <div class="space-y-4">
                @forelse($comments as $comment)
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="flex items-start gap-3">
                        <!-- User Avatar -->
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full bg-primary-50 flex items-center justify-center">
                                <span class="text-primary-700 font-semibold text-sm">
                                    {{ substr($comment->user->name ?? 'U', 0, 1) }}
                                </span>
                            </div>
                        </div>

                        <!-- Comment Content -->
                        <div class="flex-grow">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">{{ $comment->user->name }}</span>
                                <span class="text-gray-500 text-sm">·</span>
                                <span class="text-gray-500 text-sm">{{ $comment->created_at->diffForHumans() }}</span>
                                @if($comment->user_id === auth()->id())
                                <span class="text-xs bg-primary-50 text-primary-700 px-2 py-0.5 rounded-full">You</span>
                                @endif
                            </div>

                            <div class="mt-1 prose prose-sm">
                                {!! $comment->content !!}
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 bg-gray-50 rounded-lg">
                    <x-heroicon-o-chat-bubble-left-right class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                    <p class="text-gray-600">No comments yet</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>