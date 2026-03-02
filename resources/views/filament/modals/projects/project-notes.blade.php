<div class="py-2">
    @if($notes->isEmpty())
        <div class="flex flex-col items-center justify-center py-12 text-center">
            <x-heroicon-o-chat-bubble-left-right class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" />
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No notes yet</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Add the first note using the "Add Note" action.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($notes as $note)
                @php
                    $typeConfig = match ($note->type) {
                        'important' => ['color' => 'amber', 'icon' => '⚠️', 'label' => 'Penting', 'ring' => 'ring-amber-200 dark:ring-amber-700', 'bg' => 'bg-amber-50 dark:bg-amber-900/20'],
                        'blocker' => ['color' => 'red', 'icon' => '🚫', 'label' => 'Penghambat', 'ring' => 'ring-red-200 dark:ring-red-700', 'bg' => 'bg-red-50 dark:bg-red-900/20'],
                        default => ['color' => 'gray', 'icon' => '💬', 'label' => 'Umum', 'ring' => 'ring-gray-200 dark:ring-gray-700', 'bg' => 'bg-white dark:bg-gray-800'],
                    };
                @endphp
                <div class="rounded-xl ring-1 {{ $typeConfig['ring'] }} {{ $typeConfig['bg'] }} p-4 shadow-sm">
                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-1 gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            {{-- Avatar --}}
                            <div
                                class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center text-primary-600 dark:text-primary-400 font-semibold text-sm shrink-0">
                                {{ strtoupper(substr($note->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $note->user->name ?? 'Unknown User' }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $note->created_at->diffForHumans() }} &middot;
                                    {{ $note->created_at->format('d M Y, H:i') }}
                                </p>
                            </div>
                        </div>
                        {{-- Type badge --}}
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1
                                                            @if($note->type === 'important') bg-amber-100 text-amber-800 ring-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:ring-amber-700
                                                            @elseif($note->type === 'blocker') bg-red-100 text-red-800 ring-red-200 dark:bg-red-900/30 dark:text-red-300 dark:ring-red-700
                                                            @else bg-gray-100 text-gray-600 ring-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600
                                                            @endif
                                                        ">
                            {{ $typeConfig['icon'] }} {{ $typeConfig['label'] }}
                        </span>
                    </div>
                    {{-- Content --}}
                    <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-normal">
                        {{ $note->content }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 text-center">
            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $notes->count() }}
                {{ Str::plural('note', $notes->count()) }} total
            </p>
        </div>
    @endif
</div>