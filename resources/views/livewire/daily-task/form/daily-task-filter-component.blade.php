{{-- resources/views/livewire/daily-task/daily-task-filter-component.blade.php --}}
<div class="space-y-4" x-data="{ 
    showAdvancedFilters: false,
    viewMode: 'list',
    groupBy: 'status'
}">

    {{-- Header Row: Search + Controls - Responsive --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
        {{-- Search Section - Takes remaining space on desktop, full width on mobile --}}
        <div class="flex-1 order-1">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" wire:model.live.debounce.500ms="filterData.search"
                    placeholder="Cari berdasarkan nama task, deskripsi, atau proyek..." class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl
                              focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                              dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400
                              transition-all duration-200 text-sm font-medium">
                <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                    @if(!empty($filterData['search']))
                    <button wire:click="$set('filterData.search', '')"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
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
                {{-- View Mode Toggle - Hidden on small screens, shown on md+ --}}
                <div class="hidden md:flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                    <button type="button" wire:click="$set('filterData.view_mode', 'list')"
                        class="px-2 lg:px-3 py-2 rounded-md text-sm font-medium transition-all duration-200
                                   {{ ($filterData['view_mode'] ?? 'list') === 'list' 
                                       ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 shadow-sm' 
                                       : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                    </button>
                    <button type="button" wire:click="$set('filterData.view_mode', 'kanban')"
                        class="px-2 lg:px-3 py-2 rounded-md text-sm font-medium transition-all duration-200
                                   {{ ($filterData['view_mode'] ?? 'list') === 'kanban' 
                                       ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 shadow-sm' 
                                       : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                    </button>
                </div>

                {{-- Advanced Filter Toggle - Responsive text --}}
                <button @click="showAdvancedFilters = !showAdvancedFilters" class="inline-flex items-center gap-2 px-3 sm:px-4 py-2.5 border border-gray-300 dark:border-gray-600
                               rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200
                               text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap
                               {{ !empty($activeFilters) ? 'ring-2 ring-blue-500 border-blue-300' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                    </svg>
                    <span class="hidden sm:inline">Filter</span>
                    @if(!empty($activeFilters))
                    <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold 
                                     text-white bg-blue-500 rounded-full">
                        {{ count($activeFilters) }}
                    </span>
                    @endif
                </button>
            </div>

            {{-- Right side - Results Count --}}
            <div class="flex items-center gap-2 px-2 sm:px-3 py-1.5 sm:py-2 bg-gray-50 dark:bg-gray-800 rounded-md">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    <span class="hidden sm:inline">{{ number_format($totalTasks) }} task</span>
                    <span class="sm:hidden">{{ number_format($totalTasks) }}</span>
                </span>
            </div>
        </div>
    </div>

    {{-- Mobile View Mode Toggle - Only show on small screens --}}
    <div class="md:hidden">
        <div class="flex items-center justify-center">
            <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-1 w-full max-w-xs">
                <button type="button" wire:click="$set('filterData.view_mode', 'list')" class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-all duration-200
                               {{ ($filterData['view_mode'] ?? 'list') === 'list' 
                                   ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 shadow-sm' 
                                   : 'text-gray-600 dark:text-gray-400' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <span>List</span>
                </button>
                <button type="button" wire:click="$set('filterData.view_mode', 'kanban')" class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-all duration-200
                               {{ ($filterData['view_mode'] ?? 'list') === 'kanban' 
                                   ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 shadow-sm' 
                                   : 'text-gray-600 dark:text-gray-400' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    <span>Board</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Quick Filters Row - Responsive --}}
    <div class="flex flex-col lg:flex-row lg:items-center gap-3 lg:gap-6">
        {{-- Quick Date Filters --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Tanggal:</span>
            <div class="flex flex-wrap items-center gap-1">
                <button wire:click="setDateFilter('today')"
                    class="px-2 sm:px-3 py-1 sm:py-1.5 text-xs font-medium rounded-md transition-all duration-200
                               {{ ($filterData['date_preset'] ?? '') === 'today' 
                                   ? 'bg-blue-100 text-blue-700 border border-blue-200' 
                                   : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                    Hari Ini
                </button>
                <button wire:click="setDateFilter('tomorrow')"
                    class="px-2 sm:px-3 py-1 sm:py-1.5 text-xs font-medium rounded-md transition-all duration-200
                               {{ ($filterData['date_preset'] ?? '') === 'tomorrow' 
                                   ? 'bg-blue-100 text-blue-700 border border-blue-200' 
                                   : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                    Besok
                </button>
                <button wire:click="setDateFilter('this_week')"
                    class="px-2 sm:px-3 py-1 sm:py-1.5 text-xs font-medium rounded-md transition-all duration-200
                               {{ ($filterData['date_preset'] ?? '') === 'this_week' 
                                   ? 'bg-blue-100 text-blue-700 border border-blue-200' 
                                   : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                    <span class="hidden sm:inline">Minggu Ini</span>
                    <span class="sm:hidden">Minggu</span>
                </button>
                <button wire:click="setDateFilter('overdue')"
                    class="px-2 sm:px-3 py-1 sm:py-1.5 text-xs font-medium rounded-md transition-all duration-200
                               {{ ($filterData['date_preset'] ?? '') === 'overdue' 
                                   ? 'bg-red-100 text-red-700 border border-red-200' 
                                   : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                    Terlambat
                </button>
            </div>
        </div>

        {{-- Quick Status Filters --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Status:</span>
            <div class="flex flex-wrap items-center gap-1">
                <button wire:click="toggleQuickFilter('status', 'pending')"
                    class="px-2 sm:px-3 py-1 sm:py-1.5 text-xs font-medium rounded-md transition-all duration-200
                               {{ in_array('pending', $filterData['status'] ?? []) 
                                   ? 'bg-yellow-100 text-yellow-700 border border-yellow-200' 
                                   : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                    Pending
                </button>
                <button wire:click="toggleQuickFilter('status', 'in_progress')"
                    class="px-2 sm:px-3 py-1 sm:py-1.5 text-xs font-medium rounded-md transition-all duration-200
                               {{ in_array('in_progress', $filterData['status'] ?? []) 
                                   ? 'bg-blue-100 text-blue-700 border border-blue-200' 
                                   : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                    <span class="hidden sm:inline">In Progress</span>
                    <span class="sm:hidden">Progress</span>
                </button>
                <button wire:click="toggleQuickFilter('status', 'completed')"
                    class="px-2 sm:px-3 py-1 sm:py-1.5 text-xs font-medium rounded-md transition-all duration-200
                               {{ in_array('completed', $filterData['status'] ?? []) 
                                   ? 'bg-green-100 text-green-700 border border-green-200' 
                                   : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                    <span class="hidden sm:inline">Completed</span>
                    <span class="sm:hidden">Done</span>
                </button>
            </div>
        </div>

        {{-- Group By - Right aligned on desktop --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 lg:ml-auto">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Grup:</span>
            <select wire:model.live="filterData.group_by" class="px-2 sm:px-3 py-1 sm:py-1.5 text-xs border border-gray-300 dark:border-gray-600 rounded-md
                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                           dark:bg-gray-700 dark:text-gray-100 transition-all duration-200 min-w-0">
                <option value="none">Tanpa Grup</option>
                <option value="status">Status</option>
                <option value="priority">Prioritas</option>
                <option value="project">Proyek</option>
                <option value="assignee">PIC</option>
                <option value="date">Tanggal</option>
            </select>
        </div>
    </div>

    {{-- Active Filters --}}
    @if(!empty($activeFilters))
    <div class="flex flex-wrap items-center gap-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
        <span class="text-sm font-medium text-blue-700 dark:text-blue-300">Filter Aktif:</span>
        @foreach($activeFilters as $filter)
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white dark:bg-gray-700 
                            border border-blue-200 dark:border-blue-700 rounded-md text-xs group
                            hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-all duration-200">
            <span class="font-medium text-blue-700 dark:text-blue-300">{{ $filter['label'] }}:</span>
            <span class="text-blue-600 dark:text-blue-400 max-w-[120px] truncate">
                {{ $filter['value'] }}
            </span>
            <button wire:click="removeFilter('{{ $filter['type'] }}')" class="ml-1 opacity-0 group-hover:opacity-100 text-blue-400 hover:text-red-500 
                                   transition-all duration-200 p-0.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        @endforeach
        <button wire:click="resetFilters" class="ml-2 px-3 py-1 text-xs font-medium text-red-600 hover:text-red-700
                           hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-all duration-200">
            Reset Semua
        </button>
    </div>
    @endif

    {{-- Advanced Filters Panel --}}
    <div x-show="showAdvancedFilters" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-2"
        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">

        <div class="mb-3">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Filter Lanjutan</h3>
            <p class="text-xs text-gray-600 dark:text-gray-400">Atur filter tambahan untuk hasil yang lebih spesifik</p>
        </div>

        {{ $this->filterForm }}

        {{-- Advanced Filter Actions --}}
        <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-700 mt-4">
            <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ count($activeFilters) }} filter diterapkan
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="resetFilters" class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 
                               hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 
                               dark:hover:bg-gray-700 rounded-md transition-all duration-200 
                               border border-gray-300 dark:border-gray-600">
                    Reset
                </button>
                <button @click="showAdvancedFilters = false" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 
                               dark:hover:bg-blue-600 text-white rounded-md text-xs font-medium 
                               transition-all duration-200">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <style>
        /* Custom styles for responsive behavior */
        @media (max-width: 640px) {
            .quick-filters-mobile {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-buttons-mobile {
                justify-content: flex-start;
                flex-wrap: wrap;
            }
        }

        /* Smooth transitions for collapsible elements */
        .filter-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Custom scrollbar for horizontal overflow */
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        /* Loading state for buttons */
        .filter-btn {
            transition: all 0.2s ease-in-out;
        }

        .filter-btn:hover {
            transform: translateY(-1px);
        }

        /* Focus states for accessibility */
        button:focus-visible,
        select:focus-visible,
        input:focus-visible {
            outline: none;
            ring: 2px solid #3B82F6;
            ring-offset: 2px;
        }

        /* Dark mode specific adjustments */
        @media (prefers-color-scheme: dark) {
            .filter-chip {
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            }
        }

        /* Responsive grid for advanced filters */
        .advanced-filter-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        @media (min-width: 640px) {
            .advanced-filter-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .advanced-filter-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        /* Animation for filter chips */
        .filter-chip {
            animation: slideInFromLeft 0.3s ease-out;
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

        /* Hover effects for interactive elements */
        .interactive-hover {
            transition: all 0.2s ease-in-out;
        }

        .interactive-hover:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Mobile-specific optimizations */
        @media (max-width: 480px) {
            .mobile-stack {
                flex-direction: column;
                gap: 0.75rem;
            }

            .mobile-full-width {
                width: 100%;
            }

            .mobile-text-sm {
                font-size: 0.75rem;
            }
        }

        /* Tablet optimizations */
        @media (min-width: 768px) and (max-width: 1023px) {
            .tablet-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</div>