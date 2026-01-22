@props([
    'title' => 'Example Widget',
    'value' => '0',
    'description' => 'Description here',
    'icon' => 'heroicon-o-chart-bar',
    'color' => 'primary',
    'trend' => null, // 'up', 'down', or null
    'trendValue' => null,
])

<div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 hover:shadow-md transition-shadow duration-200">
    <div class="flex items-start justify-between gap-x-3">
        <div class="flex-1">
            {{-- Header --}}
            <div class="flex items-center gap-x-2 mb-1">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-{{ $color }}-50 dark:bg-{{ $color }}-500/10">
                    <x-filament::icon 
                        :icon="$icon"
                        class="h-5 w-5 text-{{ $color }}-600 dark:text-{{ $color }}-400"
                    />
                </div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ $title }}
                </h3>
            </div>

            {{-- Main Value --}}
            <div class="mt-3">
                <p class="text-3xl font-semibold text-gray-900 dark:text-white">
                    {{ $value }}
                </p>
            </div>

            {{-- Description --}}
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                {{ $description }}
            </p>

            {{-- Trend Indicator (Optional) --}}
            @if($trend && $trendValue)
            <div class="mt-3 flex items-center gap-x-1.5">
                @if($trend === 'up')
                    <svg class="h-4 w-4 text-success-600 dark:text-success-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <span class="text-xs font-medium text-success-600 dark:text-success-400">
                        {{ $trendValue }}
                    </span>
                @elseif($trend === 'down')
                    <svg class="h-4 w-4 text-danger-600 dark:text-danger-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                    </svg>
                    <span class="text-xs font-medium text-danger-600 dark:text-danger-400">
                        {{ $trendValue }}
                    </span>
                @endif
                <span class="text-xs text-gray-400 dark:text-gray-500">vs last period</span>
            </div>
            @endif
        </div>

        {{-- Optional Action Button --}}
        @if(isset($action))
            <div class="shrink-0">
                {{ $action }}
            </div>
        @endif
    </div>

    {{-- Footer Slot (Optional) --}}
    @if(isset($footer))
        <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-3">
            {{ $footer }}
        </div>
    @endif

    {{-- Default Slot --}}
    {{ $slot }}
</div>