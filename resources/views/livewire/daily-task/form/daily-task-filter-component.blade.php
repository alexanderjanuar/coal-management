{{-- resources/views/livewire/daily-task/daily-task-filter-component.blade.php --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 
            shadow-sm dark:shadow-lg dark:shadow-gray-900/20 p-4"
     x-data="{ 
        showFilters: false,
        activeTab: 'main'
     }">
    
    {{-- Main Filter Bar --}}
    <div class="flex items-center gap-3 flex-wrap">
        {{-- Search Input --}}
        <div class="flex-1 min-w-[300px]">
            <div class="relative">
                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input type="text" wire:model.live.debounce.750ms="filterData.search"
                    placeholder="Cari task..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg 
                           focus:ring-2 focus:ring-primary-500 focus:border-primary-500 
                           dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 
                           transition-all duration-200 text-sm">
            </div>
        </div>

        {{-- Quick Filter Buttons --}}
        <div class="flex items-center gap-2">
            {{-- View Mode Toggle --}}
            <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                <button type="button" 
                    wire:click="$set('filterData.view_mode', 'list')"
                    class="px-3 py-1.5 rounded-md text-sm font-medium transition-all duration-200
                           {{ ($filterData['view_mode'] ?? 'list') === 'list' 
                               ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 shadow-sm' 
                               : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    <x-heroicon-o-list-bullet class="w-4 h-4" />
                </button>
                <button type="button" 
                    wire:click="$set('filterData.view_mode', 'kanban')"
                    class="px-3 py-1.5 rounded-md text-sm font-medium transition-all duration-200
                           {{ ($filterData['view_mode'] ?? 'list') === 'kanban' 
                               ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 shadow-sm' 
                               : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    <x-heroicon-o-squares-2x2 class="w-4 h-4" />
                </button>
            </div>

            {{-- Group By Quick Select --}}
            <select wire:model.live="filterData.group_by"
                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm
                       focus:ring-2 focus:ring-primary-500 focus:border-primary-500 
                       dark:bg-gray-700 dark:text-gray-100 transition-all duration-200">
                <option value="none">No Group</option>
                <option value="status">By Status</option>
                <option value="priority">By Priority</option>
                <option value="project">By Project</option>
                <option value="assignee">By Assignee</option>
                <option value="date">By Date</option>
            </select>

            {{-- Filter Button --}}
            <button @click="showFilters = !showFilters"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 dark:bg-gray-700 
                       hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 
                       rounded-lg transition-all duration-200 text-sm font-medium
                       {{ !empty($activeFilters) ? 'ring-2 ring-primary-500 dark:ring-primary-400' : '' }}">
                <x-heroicon-o-funnel class="w-4 h-4" />
                <span>Filter</span>
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
                <x-heroicon-o-document-text class="w-4 h-4" />
                <span>{{ number_format($totalTasks) }}</span>
            </div>

            {{-- Reset Button --}}
            @if(!empty($activeFilters))
            <button wire:click="resetFilters"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-gray-500 dark:text-gray-400 
                       hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 
                       rounded-lg transition-all duration-200 text-sm font-medium"
                title="Reset semua filter">
                <x-heroicon-o-x-mark class="w-4 h-4" />
                <span class="hidden sm:inline">Reset</span>
            </button>
            @endif
        </div>
    </div>

    {{-- Active Filters Chips --}}
    @if(!empty($activeFilters))
    <div class="flex items-center gap-2 mt-3 flex-wrap">
        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Aktif:</span>
        @foreach($activeFilters as $filter)
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white dark:bg-gray-700 
                    border border-gray-200 dark:border-gray-600 rounded-md text-xs group
                    hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200">
            <x-dynamic-component :component="$filter['icon']" 
                class="w-3 h-3 {{ match($filter['color']) {
                    'primary' => 'text-primary-500 dark:text-primary-400',
                    'success' => 'text-green-500 dark:text-green-400',
                    'warning' => 'text-orange-500 dark:text-orange-400',
                    'danger' => 'text-red-500 dark:text-red-400',
                    'info' => 'text-blue-500 dark:text-blue-400',
                    default => 'text-gray-500 dark:text-gray-400'
                } }}" />
            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $filter['label'] }}:</span>
            <span class="text-gray-600 dark:text-gray-400">{{ Str::limit($filter['value'], 15) }}</span>
            <button wire:click="removeFilter('{{ $filter['type'] }}')"
                class="ml-1 opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 
                       transition-all duration-200">
                <x-heroicon-o-x-mark class="w-3 h-3" />
            </button>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Expandable Filter Panel --}}
    <div x-show="showFilters" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-2"
         class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">

        {{-- Filter Tabs --}}
        <div class="flex items-center gap-1 mb-4 bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
            <button @click="activeTab = 'main'"
                class="px-4 py-2 text-sm font-medium rounded-md transition-all duration-200"
                :class="activeTab === 'main' 
                    ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 shadow-sm' 
                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'">
                <x-heroicon-o-adjustments-horizontal class="w-4 h-4 inline mr-2" />
                Utama
            </button>
            <button @click="activeTab = 'advanced'"
                class="px-4 py-2 text-sm font-medium rounded-md transition-all duration-200"
                :class="activeTab === 'advanced' 
                    ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 shadow-sm' 
                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'">
                <x-heroicon-o-cog-6-tooth class="w-4 h-4 inline mr-2" />
                Lanjutan
            </button>
        </div>

        {{-- Main Filters Tab --}}
        <div x-show="activeTab === 'main'" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Date Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tanggal Spesifik
                    </label>
                    {{ $this->filterForm->getComponent('date') }}
                </div>

                {{-- Date Range Start --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Dari Tanggal
                    </label>
                    {{ $this->filterForm->getComponent('date_start') }}
                </div>

                {{-- Date Range End --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Sampai Tanggal
                    </label>
                    {{ $this->filterForm->getComponent('date_end') }}
                </div>
            </div>

            {{-- Status & Priority --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Status
                    </label>
                    {{ $this->filterForm->getComponent('status') }}
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Prioritas
                    </label>
                    {{ $this->filterForm->getComponent('priority') }}
                </div>
            </div>
        </div>

        {{-- Advanced Filters Tab --}}
        <div x-show="activeTab === 'advanced'" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Project
                    </label>
                    {{ $this->filterForm->getComponent('project') }}
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Assignee
                    </label>
                    {{ $this->filterForm->getComponent('assignee') }}
                </div>
            </div>
        </div>

        {{-- Filter Actions --}}
        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700 mt-6">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ count($activeFilters) }} filter aktif
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="resetFilters"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 
                           hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 
                           dark:hover:bg-gray-700 rounded-lg transition-all duration-200">
                    Reset Semua
                </button>
                <button @click="showFilters = false"
                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 
                           dark:hover:bg-primary-600 text-white rounded-lg text-sm font-medium 
                           transition-all duration-200">
                    Terapkan Filter
                </button>
            </div>
        </div>
    </div>

    {{-- Loading State --}}
    <div wire:loading.delay class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm 
                                   rounded-xl flex items-center justify-center z-10">
        <div class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg 
                    border border-gray-200 dark:border-gray-700">
            <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin text-primary-600 dark:text-primary-400" />
            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Memuat...</span>
        </div>
    </div>
</div>