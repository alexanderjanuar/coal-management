<div class="space-y-4">
    <!-- Comment Form at the top -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <form wire:submit="createComment">
            {{ $this->form }}
            
            <div class="flex justify-end mt-2">
                <x-filament::button type="submit">
                    Post Comment
                </x-filament::button>
            </div>
        </form>
    </div>

    <!-- Existing Comments -->
    <div class="space-y-3">
        @foreach($comments as $comment)
        <div class="flex gap-3">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                    <span class="text-xs font-medium text-gray-600">
                        {{ substr($comment->user->name ?? 'U', 0, 1) }}
                    </span>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <div class="bg-white rounded-lg px-3 py-2 shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm font-medium text-gray-900">
                            {{ $comment->user->name ?? 'Unknown User' }}
                        </span>
                        <span class="text-xs text-gray-400">
                            {{ $comment->created_at->format('M d, Y H:i') }}
                        </span>
                    </div>
                    <div class="prose prose-sm max-w-none mt-1">
                        {!! $comment->content !!}
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>