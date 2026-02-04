{{-- resources/views/livewire/daily-task/daily-task-filter-component.blade.php --}}
<div class="space-y-4" x-data="{
    showAdvancedFilters: false,
    viewMode: @entangle('filterData.view_mode').live,
    groupBy: 'status',

    toggleViewMode(mode) {
        this.viewMode = mode;
    }
}">

    {{-- Header Row: Search + Controls - Responsive --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
        {{-- Search Section - Takes remaining space on desktop, full width on mobile --}}
        <div class="flex-1 order-1">
            <div class="relative group">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400
                            group-focus-within:text-blue-500 transition-colors duration-200"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" wire:model.live.debounce.500ms="filterData.search"
                    placeholder="Cari berdasarkan nama task, deskripsi, atau proyek..."
                    class="w-full pl-10 pr-10 py-3 border border-gray-300 dark:border-gray-600 rounded-xl
                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                           dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400
                           transition-all duration-200 text-sm font-medium
                           hover:border-gray-400 dark:hover:border-gray-500">
                <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                    @if(!empty($filterData['search']))
                    <button wire:click="$set('filterData.search', '')"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300
                               transition-all duration-200 hover:scale-110 active:scale-95
                               p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Controls - Responsive layout --}}
        <div class="flex items-center justify-between sm:justify-end gap-2 sm:gap-3 flex-shrink-0 order-2">
            {{-- Left side on mobile, right side on desktop --}}
            <div class="flex items-center gap-2">
                {{-- Enhanced View Mode Toggle - Hidden on small screens, shown on md+ --}}
                <div class="hidden md:flex items-center bg-gradient-to-br from-gray-100 to-gray-50
                            dark:from-gray-700 dark:to-gray-800 rounded-xl p-1.5 shadow-sm border
                            border-gray-200 dark:border-gray-600">
                    <button type="button"
                        @click="toggleViewMode('kanban')"
                        class="relative px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300 ease-out
                               {{ ($filterData['view_mode'] ?? 'kanban') === 'kanban'
                                   ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow-md scale-105'
                                   : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                        <svg class="w-4 h-4 transition-transform duration-300
                                    {{ ($filterData['view_mode'] ?? 'kanban') === 'kanban' ? 'scale-110' : '' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                        @if(($filterData['view_mode'] ?? 'kanban') === 'kanban')
                        <span class="absolute inset-0 rounded-lg bg-blue-500/10 animate-ping-slow"></span>
                        @endif
                    </button>
                    <button type="button"
                        @click="toggleViewMode('list')"
                        class="relative px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300 ease-out
                               {{ ($filterData['view_mode'] ?? 'kanban') === 'list'
                                   ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow-md scale-105'
                                   : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                        <svg class="w-4 h-4 transition-transform duration-300
                                    {{ ($filterData['view_mode'] ?? 'kanban') === 'list' ? 'scale-110' : '' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        @if(($filterData['view_mode'] ?? 'kanban') === 'list')
                        <span class="absolute inset-0 rounded-lg bg-blue-500/10 animate-ping-slow"></span>
                        @endif
                    </button>
                </div>

                {{-- Advanced Filter Toggle - Enhanced Design --}}
                <button @click="showAdvancedFilters = !showAdvancedFilters"
                    class="group inline-flex items-center gap-2 px-3 sm:px-4 py-2.5
                           border-2 transition-all duration-300 ease-out
                           rounded-xl hover:scale-105 active:scale-95
                           text-sm font-medium whitespace-nowrap
                           {{ !empty($activeFilters)
                               ? 'border-blue-400 dark:border-blue-500 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/20 text-blue-700 dark:text-blue-300 shadow-md shadow-blue-500/20'
                               : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500' }}">
                    <svg class="w-4 h-4 transition-transform duration-300 {{ !empty($activeFilters) ? 'text-blue-600 dark:text-blue-400' : '' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         :class="{ 'rotate-180': showAdvancedFilters }">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                    </svg>
                    <span class="hidden sm:inline">Filter</span>
                    @if(!empty($activeFilters))
                    <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold
                                 text-white bg-gradient-to-br from-blue-500 to-blue-600 rounded-full
                                 shadow-sm animate-pulse-subtle">
                        {{ count($activeFilters) }}
                    </span>
                    @endif
                </button>
            </div>

            {{-- Right side - Results Count - Enhanced Design --}}
            <div class="flex items-center gap-2 px-2 sm:px-3 py-1.5 sm:py-2
                        bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900
                        border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="hidden sm:inline">{{ number_format($totalTasks) }} task{{ $totalTasks !== 1 ? 's' : '' }}</span>
                    <span class="sm:hidden">{{ number_format($totalTasks) }}</span>
                </span>
            </div>
        </div>
    </div>

    {{-- Mobile View Mode Toggle - Enhanced with smooth animations --}}
    <div class="md:hidden" x-data="{ isMobileToggleVisible: true }">
        <div class="flex items-center justify-center" x-show="isMobileToggleVisible"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center bg-gradient-to-br from-gray-100 to-gray-50
                        dark:from-gray-700 dark:to-gray-800 rounded-xl p-1.5 w-full max-w-xs
                        shadow-md border border-gray-200 dark:border-gray-600">
                <button type="button"
                    @click="toggleViewMode('kanban')"
                    class="relative flex-1 flex items-center justify-center gap-2 px-3 py-2.5
                           rounded-lg text-sm font-medium transition-all duration-300 ease-out
                           {{ ($filterData['view_mode'] ?? 'kanban') === 'kanban'
                               ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow-lg scale-105'
                               : 'text-gray-600 dark:text-gray-400' }}">
                    <svg class="w-4 h-4 transition-transform duration-300
                                {{ ($filterData['view_mode'] ?? 'kanban') === 'kanban' ? 'scale-110' : '' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    <span>Board</span>
                    @if(($filterData['view_mode'] ?? 'kanban') === 'kanban')
                    <span class="absolute inset-0 rounded-lg bg-blue-500/10 animate-ping-slow"></span>
                    @endif
                </button>
                <button type="button"
                    @click="toggleViewMode('list')"
                    class="relative flex-1 flex items-center justify-center gap-2 px-3 py-2.5
                           rounded-lg text-sm font-medium transition-all duration-300 ease-out
                           {{ ($filterData['view_mode'] ?? 'kanban') === 'list'
                               ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow-lg scale-105'
                               : 'text-gray-600 dark:text-gray-400' }}">
                    <svg class="w-4 h-4 transition-transform duration-300
                                {{ ($filterData['view_mode'] ?? 'kanban') === 'list' ? 'scale-110' : '' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <span>List</span>
                    @if(($filterData['view_mode'] ?? 'kanban') === 'list')
                    <span class="absolute inset-0 rounded-lg bg-blue-500/10 animate-ping-slow"></span>
                    @endif
                </button>
            </div>
        </div>
    </div>

    {{-- Active Filters - Clean & Simple --}}
    @if(!empty($activeFilters))
    <div class="flex flex-wrap items-center gap-2 p-3
                bg-gray-50 dark:bg-gray-800/50
                border border-gray-200 dark:border-gray-700
                rounded-lg">

        {{-- Filter count --}}
        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">
            {{ count($activeFilters) }} filter:
        </span>

        {{-- Filter chips --}}
        @foreach($activeFilters as $filter)
        <div class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1
                    bg-white dark:bg-gray-700
                    border border-gray-300 dark:border-gray-600
                    rounded-md
                    text-xs">
            <span class="text-gray-700 dark:text-gray-300">
                {{ $filter['label'] }}:
            </span>
            <span class="font-medium text-gray-900 dark:text-white">
                {{ $filter['value'] }}
            </span>
            <button wire:click="removeFilter('{{ $filter['type'] }}')"
                    class="ml-0.5 p-0.5 text-gray-400 hover:text-red-500 rounded transition-colors">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        @endforeach

        {{-- Reset button --}}
        <button wire:click="resetFilters"
                class="ml-auto text-xs font-medium text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors">
            Reset semua
        </button>
    </div>
    @endif

    {{-- Advanced Filters Panel --}}
    <div x-show="showAdvancedFilters"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-4"
         class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700
                rounded-xl shadow-lg overflow-hidden">

        <div class="p-4 sm:p-5">
            {{-- Header with gradient --}}
            <div class="mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-md">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Filter Lanjutan</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                                Atur filter tambahan untuk hasil yang lebih spesifik
                            </p>
                        </div>
                    </div>
                    <button @click="showAdvancedFilters = false"
                        class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100
                               dark:hover:text-gray-200 dark:hover:bg-gray-700
                               transition-all duration-200 hover:scale-110 active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Filter form --}}
            <div class="space-y-4">
                {{ $this->filterForm }}
            </div>

            {{-- Enhanced Actions Footer --}}
            <div class="flex items-center justify-between pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                    <span class="font-medium">{{ count($activeFilters) }} filter diterapkan</span>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="resetFilters"
                        class="group flex items-center gap-2 px-4 py-2 text-sm font-medium
                               text-gray-700 dark:text-gray-300
                               hover:text-gray-900 dark:hover:text-gray-100
                               bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600
                               rounded-lg transition-all duration-200
                               border border-gray-300 dark:border-gray-600
                               hover:scale-105 active:scale-95">
                        <svg class="w-4 h-4 transition-transform duration-200 group-hover:rotate-180"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset
                    </button>
                    <button @click="showAdvancedFilters = false"
                        class="flex items-center gap-2 px-5 py-2
                               bg-gradient-to-br from-blue-600 to-blue-700
                               hover:from-blue-700 hover:to-blue-800
                               text-white rounded-lg text-sm font-semibold
                               shadow-md hover:shadow-lg
                               transition-all duration-200
                               hover:scale-105 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 13l4 4L19 7" />
                        </svg>
                        Terapkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        /* Enhanced smooth animations */
        @keyframes ping-slow {
            0%, 100% {
                opacity: 0;
                transform: scale(1);
            }
            50% {
                opacity: 0.3;
                transform: scale(1.1);
            }
        }

        @keyframes pulse-subtle {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.9;
                transform: scale(1.05);
            }
        }

        @keyframes slideInFromLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-ping-slow {
            animation: ping-slow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .animate-pulse-subtle {
            animation: pulse-subtle 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Smooth transitions for all interactive elements */
        button, select, input {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Enhanced focus states */
        button:focus-visible,
        select:focus-visible,
        input:focus-visible {
            outline: none;
            ring: 2px solid #3B82F6;
            ring-offset: 2px;
        }

        /* Smooth hover effects */
        .hover-lift {
            transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        /* Dark mode enhancements */
        @media (prefers-color-scheme: dark) {
            .hover-lift:hover {
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            }
        }

        /* Responsive optimizations */
        @media (max-width: 640px) {
            .mobile-stack {
                flex-direction: column;
                gap: 0.75rem;
            }
        }

        /* Custom scrollbar */
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            @apply bg-gray-100 dark:bg-gray-800 rounded-lg;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            @apply bg-gray-300 dark:bg-gray-600 rounded-lg;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            @apply bg-gray-400 dark:bg-gray-500;
        }
    </style>
    @endpush
</div>
