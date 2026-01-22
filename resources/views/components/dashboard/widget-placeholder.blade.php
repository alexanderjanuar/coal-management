@props([
    'title' => 'Widget',
    'description' => 'Widget description',
    'icon' => 'heroicon-o-square-3-stack-3d',
    'color' => 'gray'
])

<div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="flex items-center gap-x-3">
        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-{{ $color }}-50 dark:bg-{{ $color }}-500/10">
            <x-filament::icon 
                :icon="$icon"
                class="h-6 w-6 text-{{ $color }}-600 dark:text-{{ $color }}-400"
            />
        </div>
        
        <div class="flex-1">
            <div class="flex items-center gap-x-2">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ $title }}
                </h3>
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                {{ $description }}
            </p>
        </div>
    </div>

    <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
        <div class="flex items-center justify-center h-20">
            <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                    Ready for your widget
                </p>
            </div>
        </div>
    </div>

    {{-- Optional slot for custom content --}}
    {{ $slot }}
</div>