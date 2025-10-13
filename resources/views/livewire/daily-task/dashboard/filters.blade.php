{{-- resources/views/livewire/daily-task/dashboard/filters.blade.php --}}
<div class="relative">
    <!-- Main Filter Bar - Responsive -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between p-4 sm:p-6 gap-4 lg:gap-0">
        <!-- Left Side: Labels -->
        <div class="flex flex-col">
            <h2 class="text-xl sm:text-2xl font-bold text-black dark:text-gray-100">Dashboard Tugas Harian</h2>
            <p class="text-sm sm:text-base lg:text-xl text-gray-500 dark:text-gray-400 mt-0.5">
                Kelola dan pantau tugas harian tim Anda dengan efisien
            </p>
        </div>

        <!-- Right Side: Filter Controls - Responsive -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3">
            <!-- Date Picker Button -->
            <div class="relative" x-data="{ showDateFilter: false }">
                <button type="button" @click="showDateFilter = !showDateFilter"
                    class="group flex items-center justify-center sm:justify-start gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 transition-all duration-200 shadow-sm hover:shadow-md w-full sm:w-auto">
                    <x-heroicon-o-calendar
                        class="w-4 h-4 text-blue-500 group-hover:text-blue-600 transition-colors flex-shrink-0" />
                    <span class="truncate">{{ $this->getDisplayDate() }}</span>
                    <x-heroicon-o-chevron-down
                        class="w-3 h-3 text-gray-400 group-hover:text-gray-600 transition-all duration-200 flex-shrink-0"
                        x-bind:class="showDateFilter ? 'rotate-180' : ''" />
                </button>

                <!-- Date Filter Dropdown - Responsive -->
                <div x-show="showDateFilter" x-cloak x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                    class="absolute left-0 sm:right-0 sm:left-auto top-full mt-3 z-50 w-[calc(100vw-2rem)] sm:w-96 max-w-md bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-600 shadow-xl backdrop-blur-sm"
                    @click.away="showDateFilter = false">

                    <!-- Header -->
                    <div class="p-3 sm:p-4 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Pilih Rentang Tanggal
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pilih periode untuk data Anda
                                </p>
                            </div>
                            <div class="p-1.5 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <x-heroicon-o-calendar class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Date Range Options -->
                    <div class="p-2">
                        <div class="grid grid-cols-2 gap-1">
                            <div wire:click="setDateRange('today')" @click="showDateFilter = false"
                                class="flex items-center justify-between p-2.5 sm:p-3 hover:bg-blue-50 dark:hover:bg-blue-900/10 rounded-lg cursor-pointer group transition-all duration-150 @if(isset($data['date_range']) && $data['date_range'] === 'today') bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 @endif">
                                <span
                                    class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400">Hari
                                    Ini</span>
                                @if(isset($data['date_range']) && $data['date_range'] === 'today')
                                <x-heroicon-s-check class="w-4 h-4 text-blue-500 flex-shrink-0" />
                                @endif
                            </div>
                            <div wire:click="setDateRange('yesterday')" @click="showDateFilter = false"
                                class="flex items-center justify-between p-2.5 sm:p-3 hover:bg-blue-50 dark:hover:bg-blue-900/10 rounded-lg cursor-pointer group transition-all duration-150 @if(isset($data['date_range']) && $data['date_range'] === 'yesterday') bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 @endif">
                                <span
                                    class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400">Kemarin</span>
                                @if(isset($data['date_range']) && $data['date_range'] === 'yesterday')
                                <x-heroicon-s-check class="w-4 h-4 text-blue-500 flex-shrink-0" />
                                @endif
                            </div>
                            <div wire:click="setDateRange('this_week')" @click="showDateFilter = false"
                                class="flex items-center justify-between p-2.5 sm:p-3 hover:bg-blue-50 dark:hover:bg-blue-900/10 rounded-lg cursor-pointer group transition-all duration-150 @if(isset($data['date_range']) && $data['date_range'] === 'this_week') bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 @endif">
                                <span
                                    class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400">Minggu
                                    Ini</span>
                                @if(isset($data['date_range']) && $data['date_range'] === 'this_week')
                                <x-heroicon-s-check class="w-4 h-4 text-blue-500 flex-shrink-0" />
                                @endif
                            </div>
                            <div wire:click="setDateRange('last_week')" @click="showDateFilter = false"
                                class="flex items-center justify-between p-2.5 sm:p-3 hover:bg-blue-50 dark:hover:bg-blue-900/10 rounded-lg cursor-pointer group transition-all duration-150 @if(isset($data['date_range']) && $data['date_range'] === 'last_week') bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 @endif">
                                <span
                                    class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400">Minggu
                                    Lalu</span>
                                @if(isset($data['date_range']) && $data['date_range'] === 'last_week')
                                <x-heroicon-s-check class="w-4 h-4 text-blue-500 flex-shrink-0" />
                                @endif
                            </div>
                            <div wire:click="setDateRange('this_month')" @click="showDateFilter = false"
                                class="flex items-center justify-between p-2.5 sm:p-3 hover:bg-blue-50 dark:hover:bg-blue-900/10 rounded-lg cursor-pointer group transition-all duration-150 @if(isset($data['date_range']) && $data['date_range'] === 'this_month') bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 @endif">
                                <span
                                    class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400">Bulan
                                    Ini</span>
                                @if(isset($data['date_range']) && $data['date_range'] === 'this_month')
                                <x-heroicon-s-check class="w-4 h-4 text-blue-500 flex-shrink-0" />
                                @endif
                            </div>
                            <div wire:click="setDateRange('last_month')" @click="showDateFilter = false"
                                class="flex items-center justify-between p-2.5 sm:p-3 hover:bg-blue-50 dark:hover:bg-blue-900/10 rounded-lg cursor-pointer group transition-all duration-150 @if(isset($data['date_range']) && $data['date_range'] === 'last_month') bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 @endif">
                                <span
                                    class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400">Bulan
                                    Lalu</span>
                                @if(isset($data['date_range']) && $data['date_range'] === 'last_month')
                                <x-heroicon-s-check class="w-4 h-4 text-blue-500 flex-shrink-0" />
                                @endif
                            </div>
                            <div wire:click="setDateRange('this_year')" @click="showDateFilter = false"
                                class="flex items-center justify-between p-2.5 sm:p-3 hover:bg-blue-50 dark:hover:bg-blue-900/10 rounded-lg cursor-pointer group transition-all duration-150 @if(isset($data['date_range']) && $data['date_range'] === 'this_year') bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 @endif">
                                <span
                                    class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400">Tahun
                                    Ini</span>
                                @if(isset($data['date_range']) && $data['date_range'] === 'this_year')
                                <x-heroicon-s-check class="w-4 h-4 text-blue-500 flex-shrink-0" />
                                @endif
                            </div>
                            <div
                                class="flex items-center justify-center p-2.5 sm:p-3 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/10 dark:to-pink-900/10 rounded-lg border border-dashed border-purple-300 dark:border-purple-600">
                                <span
                                    class="text-xs sm:text-sm text-purple-600 dark:text-purple-400 font-medium">Rentang
                                    Kustom ↓</span>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Date Range -->
                    <div
                        class="p-3 sm:p-4 bg-gray-50 dark:bg-gray-700/50 rounded-b-2xl border-t border-gray-100 dark:border-gray-600">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-2">
                                <label
                                    class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Dari
                                    Tanggal</label>
                                <div class="relative">
                                    <input type="date" wire:model.live="data.from"
                                        class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Sampai
                                    Tanggal</label>
                                <div class="relative">
                                    <input type="date" wire:model.live="data.to"
                                        class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Button -->
            <div class="relative" x-data="{ showFilters: false }">
                <button type="button" @click="showFilters = !showFilters"
                    class="group flex items-center justify-center sm:justify-start gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 transition-all duration-200 shadow-sm hover:shadow-md w-full sm:w-auto">
                    <x-heroicon-o-funnel
                        class="w-4 h-4 text-purple-500 group-hover:text-purple-600 transition-colors flex-shrink-0" />
                    <span>Filter</span>
                    @if((isset($data['department']) && $data['department']) || (isset($data['position']) &&
                    $data['position']))
                    <span
                        class="inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-gradient-to-r from-purple-500 to-pink-500 rounded-full animate-pulse flex-shrink-0">
                        {{ (isset($data['department']) && $data['department'] ? 1 : 0) + (isset($data['position']) &&
                        $data['position'] ? 1 : 0) }}
                    </span>
                    @endif
                    <x-heroicon-o-chevron-down
                        class="w-3 h-3 text-gray-400 group-hover:text-gray-600 transition-all duration-200 flex-shrink-0"
                        x-bind:class="showFilters ? 'rotate-180' : ''" />
                </button>

                <!-- Department & Position Filter Dropdown - Responsive -->
                <div x-show="showFilters" x-cloak x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                    class="absolute left-0 sm:right-0 sm:left-auto top-full mt-3 z-50 w-[calc(100vw-2rem)] sm:w-96 max-w-md bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-600 shadow-xl backdrop-blur-sm"
                    @click.away="showFilters = false">

                    <!-- Header -->
                    <div class="p-3 sm:p-4 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Opsi Filter</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Saring tampilan tugas Anda
                                </p>
                            </div>
                            <div class="p-1.5 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                                <x-heroicon-o-funnel class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                            </div>
                        </div>
                    </div>

                    <div class="p-3 sm:p-4 space-y-4">
                        <!-- Department Filter -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label
                                    class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Department</label>
                                @if(isset($data['department']) && $data['department'])
                                <span class="text-xs text-purple-500 font-medium">Aktif</span>
                                @endif
                            </div>
                            <div class="relative">
                                <select wire:model.live="data.department"
                                    class="w-full pl-10 pr-4 py-3 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 appearance-none">
                                    <option value="">Semua Department</option>
                                    @foreach($this->getDepartmentOptions() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <x-heroicon-o-building-office class="w-4 h-4 text-gray-400" />
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400" />
                                </div>
                            </div>
                        </div>

                        <!-- Position Filter -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label
                                    class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Posisi</label>
                                @if(isset($data['position']) && $data['position'])
                                <span class="text-xs text-purple-500 font-medium">Aktif</span>
                                @endif
                            </div>
                            <div class="relative">
                                <select wire:model.live="data.position"
                                    class="w-full pl-10 pr-4 py-3 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 appearance-none">
                                    <option value="">Semua Posisi</option>
                                    @foreach($this->getPositionOptions() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <x-heroicon-o-user-group class="w-4 h-4 text-gray-400" />
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Filters & Actions -->
                    <div
                        class="p-3 sm:p-4 bg-gray-50 dark:bg-gray-700/50 rounded-b-2xl border-t border-gray-100 dark:border-gray-600">
                        @if((isset($data['department']) && $data['department']) || (isset($data['position']) &&
                        $data['position']))
                        <div class="flex items-center justify-between mb-3">
                            <span
                                class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Filter
                                Aktif</span>
                            <button wire:click="resetFilters" @click="showFilters = false"
                                class="text-xs text-red-500 hover:text-red-600 font-medium transition-colors">
                                Hapus Semua
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-2 mb-3">
                            @if(isset($data['department']) && $data['department'])
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium bg-gradient-to-r from-blue-100 to-purple-100 text-purple-700 dark:from-blue-900/20 dark:to-purple-900/20 dark:text-purple-300 rounded-lg border border-purple-200 dark:border-purple-700">
                                <x-heroicon-s-building-office class="w-3 h-3 flex-shrink-0" />
                                <span class="truncate max-w-[120px]">{{ $data['department'] }}</span>
                            </span>
                            @endif
                            @if(isset($data['position']) && $data['position'])
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium bg-gradient-to-r from-green-100 to-teal-100 text-teal-700 dark:from-green-900/20 dark:to-teal-900/20 dark:text-teal-300 rounded-lg border border-teal-200 dark:border-teal-700">
                                <x-heroicon-s-user-group class="w-3 h-3 flex-shrink-0" />
                                <span class="truncate max-w-[120px]">{{ $data['position'] }}</span>
                            </span>
                            @endif
                        </div>
                        @endif
                        <button wire:click="resetFilters" @click="showFilters = false"
                            class="w-full px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg transition-all duration-200 shadow-sm hover:shadow">
                            <div class="flex items-center justify-center gap-2">
                                <x-heroicon-o-arrow-path class="w-4 h-4" />
                                Reset Semua Filter
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Add Task Button -->
            <a href="/daily-task-list" wire:navigate
                class="group flex items-center justify-center gap-2 px-4 sm:px-6 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 w-full sm:w-auto">
                <x-heroicon-o-plus
                    class="w-4 h-4 group-hover:rotate-90 transition-transform duration-200 flex-shrink-0" />
                <span>Tambah Tugas</span>
            </a>
        </div>
    </div>

    <!-- Hidden form for Livewire state management -->
    <div style="display: none;">
        {{ $this->form }}
    </div>

    <style>
        /* Custom animations */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .dropdown-enter {
            animation: slideInDown 0.3s ease-out;
        }

        /* Ensure x-cloak works */
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>