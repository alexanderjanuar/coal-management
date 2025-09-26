{{-- resources/views/livewire/daily-task/daily-task-filter-component.blade.php --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 
            shadow-sm dark:shadow-lg dark:shadow-gray-900/20 p-3 sm:p-4" x-data="{ 
        showFilters: false,
        activeTab: 'main',
        showMobileMenu: false
     }">

    {{-- Main Filter Bar --}}
    <div class="space-y-3 lg:space-y-0 lg:flex lg:items-center lg:gap-3">

        {{-- Search Input - Full width on mobile --}}
        <div class="w-full lg:flex-1 lg:min-w-[400px]">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input type="text" wire:model.live.debounce.750ms="filterData.search" placeholder="Cari task..." class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg 
                           focus:ring-2 focus:ring-primary-500 focus:border-primary-500 
                           dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 
                           transition-all duration-200 text-sm">
            </div>
        </div>

        {{-- Desktop Controls --}}
        <div class="hidden lg:flex lg:items-center lg:gap-2">
            {{-- View Mode Toggle --}}
            <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                <button type="button" wire:click="$set('filterData.view_mode', 'list')" class="px-3 py-1.5 rounded-md text-sm font-medium transition-all duration-200
                           {{ ($filterData['view_mode'] ?? 'list') === 'list' 
                               ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 shadow-sm' 
                               : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                </button>
                <button type="button" wire:click="$set('filterData.view_mode', 'kanban')" class="px-3 py-1.5 rounded-md text-sm font-medium transition-all duration-200
                           {{ ($filterData['view_mode'] ?? 'list') === 'kanban' 
                               ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 shadow-sm' 
                               : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                </button>
            </div>

            {{-- Group By Quick Select --}}
            <select wire:model.live="filterData.group_by" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm
                       focus:ring-2 focus:ring-primary-500 focus:border-primary-500 
                       dark:bg-gray-700 dark:text-gray-100 transition-all duration-200 min-w-[130px]">
                <option value="none">No Group</option>
                <option value="status">By Status</option>
                <option value="priority">By Priority</option>
                <option value="project">By Project</option>
                <option value="assignee">By Assignee</option>
                <option value="date">By Date</option>
            </select>

            {{-- Filter Button --}}
            <button @click="showFilters = !showFilters" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 dark:bg-gray-700 
                       hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 
                       rounded-lg transition-all duration-200 text-sm font-medium whitespace-nowrap
                       {{ !empty($activeFilters) ? 'ring-2 ring-primary-500 dark:ring-primary-400' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                </svg>
                <span class="hidden sm:inline">Filter</span>
                @if(!empty($activeFilters))
                <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold 
                           text-white bg-primary-500 dark:bg-primary-600 rounded-full">
                    {{ count($activeFilters) }}
                </span>
                @endif
            </button>

            {{-- Total Tasks Badge --}}
            <div class="inline-flex items-center gap-2 px-3 py-2 bg-primary-50 dark:bg-primary-900/30 
                        text-primary-700 dark:text-primary-300 rounded-lg text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <span>{{ number_format($totalTasks) }}</span>
            </div>

            {{-- Reset Button --}}
            @if(!empty($activeFilters))
            <button wire:click="resetFilters" class="inline-flex items-center gap-1.5 px-3 py-2 text-gray-500 dark:text-gray-400 
                       hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 
                       rounded-lg transition-all duration-200 text-sm font-medium" title="Reset semua filter">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span class="hidden xl:inline">Reset</span>
            </button>
            @endif
        </div>

        {{-- Mobile Menu Button --}}
        <div class="lg:hidden">
            <button @click="showMobileMenu = !showMobileMenu" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 
                       bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 
                       text-gray-700 dark:text-gray-300 rounded-lg transition-all duration-200 text-sm font-medium
                       {{ !empty($activeFilters) ? 'ring-2 ring-primary-500 dark:ring-primary-400' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m0 6h9.75m-9.75 0a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 12H7.5m0 6h9.75m-9.75 0a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 18H7.5" />
                </svg>
                <span>Opsi & Filter</span>
                @if(!empty($activeFilters))
                <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold 
                           text-white bg-primary-500 dark:bg-primary-600 rounded-full">
                    {{ count($activeFilters) }}
                </span>
                @endif
                <svg class="w-4 h-4 ml-auto transition-transform duration-200"
                    :class="showMobileMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile Controls Panel --}}
    <div x-show="showMobileMenu" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-2" class="mt-3 space-y-3 lg:hidden">

        {{-- Mobile Stats --}}
        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="flex items-center gap-2 text-primary-700 dark:text-primary-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <span class="text-sm font-medium">Total Tasks: {{ number_format($totalTasks) }}</span>
            </div>
            @if(!empty($activeFilters))
            <button wire:click="resetFilters" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-red-600 
                       dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md 
                       transition-all duration-200">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Reset
            </button>
            @endif
        </div>

        {{-- Mobile View Mode Toggle --}}
        <div class="space-y-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tampilan</label>
            <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                <button type="button" wire:click="$set('filterData.view_mode', 'list')" class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-all duration-200
                           {{ ($filterData['view_mode'] ?? 'list') === 'list' 
                               ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 shadow-sm' 
                               : 'text-gray-600 dark:text-gray-400' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    List
                </button>
                <button type="button" wire:click="$set('filterData.view_mode', 'kanban')" class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-all duration-200
                           {{ ($filterData['view_mode'] ?? 'list') === 'kanban' 
                               ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 shadow-sm' 
                               : 'text-gray-600 dark:text-gray-400' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                    Kanban
                </button>
            </div>
        </div>

        {{-- Mobile Group By --}}
        <div class="space-y-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Grup</label>
            <select wire:model.live="filterData.group_by" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm
                       focus:ring-2 focus:ring-primary-500 focus:border-primary-500 
                       dark:bg-gray-700 dark:text-gray-100 transition-all duration-200">
                <option value="none">Tidak ada pengelompokan</option>
                <option value="status">Berdasarkan Status</option>
                <option value="priority">Berdasarkan Prioritas</option>
                <option value="project">Berdasarkan Proyek</option>
                <option value="assignee">Berdasarkan Penanggung Jawab</option>
                <option value="date">Berdasarkan Tanggal</option>
            </select>
        </div>

        {{-- Mobile Filter Button --}}
        <button @click="showFilters = !showFilters; showMobileMenu = false" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 
                   bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 
                   text-white rounded-lg text-sm font-medium transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
            </svg>
            <span>Filter Lanjutan</span>
            @if(!empty($activeFilters))
            <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold 
                       text-primary-600 bg-white rounded-full">
                {{ count($activeFilters) }}
            </span>
            @endif
        </button>
    </div>

    {{-- Active Filters Chips --}}
    @if(!empty($activeFilters))
    <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 w-full sm:w-auto">Filter Aktif:</span>
        <div class="flex flex-wrap items-center gap-2">
            @foreach($activeFilters as $filter)
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white dark:bg-gray-700 
                        border border-gray-200 dark:border-gray-600 rounded-md text-xs group
                        hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200">
                <svg class="w-3 h-3 {{ match($filter['color']) {
                        'primary' => 'text-primary-500 dark:text-primary-400',
                        'success' => 'text-green-500 dark:text-green-400',
                        'warning' => 'text-orange-500 dark:text-orange-400',
                        'danger' => 'text-red-500 dark:text-red-400',
                        'info' => 'text-blue-500 dark:text-blue-400',
                        default => 'text-gray-500 dark:text-gray-400'
                    } }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $filter['label'] }}:</span>
                <span class="text-gray-600 dark:text-gray-400 max-w-[80px] sm:max-w-none truncate">
                    {{ Str::limit($filter['value'], 15) }}
                </span>
                <button wire:click="removeFilter('{{ $filter['type'] }}')" class="ml-1 opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 
                           transition-all duration-200 p-0.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Expandable Filter Panel --}}
    <div x-show="showFilters" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-2"
        class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">

        {{-- Filter Tabs --}}
        <div class="flex items-center gap-1 mb-4 bg-gray-100 dark:bg-gray-700 rounded-lg p-1 
                    overflow-x-auto scrollbar-hide">
            <button @click="activeTab = 'main'" class="px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 whitespace-nowrap
                       flex items-center gap-2 min-w-fit" :class="activeTab === 'main' 
                    ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 shadow-sm' 
                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m0 6h9.75m-9.75 0a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 12H7.5m0 6h9.75m-9.75 0a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 18H7.5" />
                </svg>
                <span>Utama</span>
            </button>
            <button @click="activeTab = 'advanced'" class="px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 whitespace-nowrap
                       flex items-center gap-2 min-w-fit" :class="activeTab === 'advanced' 
                    ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 shadow-sm' 
                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                <span>Lanjutan</span>
            </button>
        </div>

        {{-- Main Filters Tab --}}
        <div x-show="activeTab === 'main'" class="space-y-4">
            {{ $this->filterForm }}
        </div>

        {{-- Advanced Filters Tab --}}
        <div x-show="activeTab === 'advanced'" class="space-y-4">
            <div class="text-sm text-gray-600 dark:text-gray-400 mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Filter Lanjutan</h4>
                        <p class="text-sm">Filter lanjutan akan ditampilkan di sini. Saat ini, semua filter yang
                            tersedia dapat diakses melalui tab "Utama".</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Actions --}}
        <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-4 border-t border-gray-200 dark:border-gray-700 mt-6">
            <div class="text-sm text-gray-500 dark:text-gray-400 order-2 sm:order-1">
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ count($activeFilters) }} filter aktif
                </span>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 order-1 sm:order-2">
                <button wire:click="resetFilters" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 
                           hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 
                           dark:hover:bg-gray-700 rounded-lg transition-all duration-200 
                           border border-gray-300 dark:border-gray-600">
                    Reset Semua
                </button>
                <button @click="showFilters = false" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 
                           dark:hover:bg-primary-600 text-white rounded-lg text-sm font-medium 
                           transition-all duration-200 shadow-sm hover:shadow-md">
                    Terapkan Filter
                </button>
            </div>
        </div>
    </div>

    <style>
        /* Custom scrollbar for horizontal overflow */
        .scrollbar-hide {
            -ms-overflow-style: none;
            /* Internet Explorer 10+ */
            scrollbar-width: none;
            /* Firefox */
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
            /* Safari and Chrome */
        }

        /* Responsive breakpoint utilities */
        @media (max-width: 640px) {
            .truncate-mobile {
                max-width: 100px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
        }

        /* Loading animation for filter chips */
        .filter-chip {
            animation: slideIn 0.2s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Focus states for better accessibility */
        .focus-ring {
            @apply focus: ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 focus:outline-none;
        }

        /* Mobile-first responsive grid for filter form */
        .filter-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        @media (min-width: 640px) {
            .filter-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .filter-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</div>