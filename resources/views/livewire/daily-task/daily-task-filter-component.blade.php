{{-- resources/views/livewire/daily-task/daily-task-filter-component.blade.php --}}
<div class="bg-white dark:bg-gray-800 rounded-lg lg:rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm" 
     x-data="{ collapsed: @entangle('filtersCollapsed') }">
    <div class="p-4 lg:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div
                    class="w-6 h-6 lg:w-8 lg:h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-funnel class="w-3 h-3 lg:w-4 lg:h-4 text-gray-600 dark:text-gray-300" />
                </div>
                <div>
                    <h3 class="text-base lg:text-lg font-semibold text-gray-900 dark:text-gray-100">Filter & Kontrol</h3>
                    <p class="text-xs lg:text-sm text-gray-500 dark:text-gray-400 hidden sm:block">Sesuaikan tampilan task Anda</p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <x-filament::badge color="primary" size="lg" icon="heroicon-o-document-text">
                    <span class="hidden sm:inline">{{ number_format($totalTasks) }} tasks</span>
                    <span class="sm:hidden">{{ $totalTasks }}</span>
                </x-filament::badge>

                <x-filament::button wire:click="resetFilters" color="gray" size="sm" icon="heroicon-o-arrow-path"
                    outlined>
                    <span class="hidden sm:inline">Reset</span>
                    <span class="sm:hidden sr-only">Reset Filter</span>
                </x-filament::button>

                <button @click="collapsed = !collapsed; $wire.set('filtersCollapsed', collapsed)"
                    class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <x-heroicon-o-chevron-down
                        class="w-4 h-4 text-gray-500 dark:text-gray-400 transition-transform duration-200"
                        x-bind:class="{ 'rotate-180': !collapsed }" />
                </button>
            </div>
        </div>
    </div>

    <div x-show="!collapsed" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-2" class="p-4 lg:p-6">

        {{-- Active Filters Display --}}
        @if(!empty($activeFilters))
        <div
            class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-funnel class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                    <span class="text-sm font-medium text-blue-900 dark:text-blue-200">Filter Aktif</span>
                    <span
                        class="text-xs bg-blue-200 dark:bg-blue-800 text-blue-800 dark:text-blue-200 px-2 py-1 rounded-full">{{
                        count($activeFilters) }}</span>
                </div>
                <button wire:click="resetFilters"
                    class="text-xs text-blue-700 dark:text-blue-300 hover:text-blue-900 dark:hover:text-blue-100 font-medium hover:underline flex items-center gap-1">
                    <x-heroicon-o-x-mark class="w-3 h-3" />
                    Hapus Semua
                </button>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach($activeFilters as $filter)
                <div
                    class="inline-flex items-center gap-2 px-3 py-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm text-sm">
                    <x-dynamic-component :component="$filter['icon']" class="w-3 h-3 {{ match($filter['color']) {
                                'primary' => 'text-primary-600 dark:text-primary-400',
                                'success' => 'text-green-600 dark:text-green-400',
                                'warning' => 'text-orange-600 dark:text-orange-400',
                                'danger' => 'text-red-600 dark:text-red-400',
                                'info' => 'text-blue-600 dark:text-blue-400',
                                default => 'text-gray-600 dark:text-gray-400'
                            } }}" />
                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $filter['label'] }}:</span>
                    <span class="text-gray-600 dark:text-gray-400">
                        @if(isset($filter['count']) && $filter['count'] > 1)
                        {{ Str::limit($filter['value'], 20) }}
                        <span
                            class="text-xs bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-1.5 py-0.5 rounded-full ml-1">{{
                            $filter['count'] }}</span>
                        @else
                        {{ Str::limit($filter['value'], 30) }}
                        @endif
                    </span>
                    <button wire:click="removeFilter('{{ $filter['type'] }}')"
                        class="ml-1 text-gray-400 dark:text-gray-500 hover:text-red-500 dark:hover:text-red-400 transition-colors"
                        title="Hapus filter">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </button>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Filter Form --}}
        {{ $this->filterForm }}

        {{-- Loading State --}}
        <div wire:loading.delay class="flex items-center justify-center py-4">
            <div class="flex items-center text-primary-600 dark:text-primary-400">
                <x-heroicon-o-arrow-path class="w-5 h-5 animate-spin mr-2" />
                <span class="text-sm font-medium">Memuat tasks...</span>
            </div>
        </div>
    </div>
</div>