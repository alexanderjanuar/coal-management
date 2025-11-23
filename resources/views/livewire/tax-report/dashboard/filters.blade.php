{{-- resources/views/livewire/tax-report/dashboard/filters.blade.php --}}
<div class="relative">
    <!-- Main Filter Bar - Responsive -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between p-4 sm:p-6 gap-4 lg:gap-0">
        <!-- Left Side: Labels -->
        <div class="flex flex-col">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Dashboard Laporan Pajak</h2>
            <p class="text-sm sm:text-base lg:text-xl text-gray-500 dark:text-gray-400 mt-0.5">
                Pantau dan kelola laporan pajak klien Anda
            </p>
        </div>

        <!-- Right Side: Filter Controls - Responsive -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3">
            <!-- Period/Date Picker Button -->
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
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Pilih Periode</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pilih periode laporan pajak
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
                            <div wire:click="setDateRange('this_quarter')" @click="showDateFilter = false"
                                class="flex items-center justify-between p-2.5 sm:p-3 hover:bg-blue-50 dark:hover:bg-blue-900/10 rounded-lg cursor-pointer group transition-all duration-150 @if(isset($data['date_range']) && $data['date_range'] === 'this_quarter') bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 @endif">
                                <span
                                    class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400">Quarter
                                    Ini</span>
                                @if(isset($data['date_range']) && $data['date_range'] === 'this_quarter')
                                <x-heroicon-s-check class="w-4 h-4 text-blue-500 flex-shrink-0" />
                                @endif
                            </div>
                            <div wire:click="setDateRange('last_quarter')" @click="showDateFilter = false"
                                class="flex items-center justify-between p-2.5 sm:p-3 hover:bg-blue-50 dark:hover:bg-blue-900/10 rounded-lg cursor-pointer group transition-all duration-150 @if(isset($data['date_range']) && $data['date_range'] === 'last_quarter') bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 @endif">
                                <span
                                    class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400">Quarter
                                    Lalu</span>
                                @if(isset($data['date_range']) && $data['date_range'] === 'last_quarter')
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
                            <div wire:click="setDateRange('last_year')" @click="showDateFilter = false"
                                class="flex items-center justify-between p-2.5 sm:p-3 hover:bg-blue-50 dark:hover:bg-blue-900/10 rounded-lg cursor-pointer group transition-all duration-150 @if(isset($data['date_range']) && $data['date_range'] === 'last_year') bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 @endif">
                                <span
                                    class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400">Tahun
                                    Lalu</span>
                                @if(isset($data['date_range']) && $data['date_range'] === 'last_year')
                                <x-heroicon-s-check class="w-4 h-4 text-blue-500 flex-shrink-0" />
                                @endif
                            </div>
                            <div
                                class="flex items-center justify-center p-2.5 sm:p-3 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/10 dark:to-pink-900/10 rounded-lg border border-dashed border-purple-300 dark:border-purple-600">
                                <span
                                    class="text-xs sm:text-sm text-purple-600 dark:text-purple-400 font-medium">Periode
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
                                    class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Dari</label>
                                <div class="relative">
                                    <input type="month" wire:model.live="data.from"
                                        class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Sampai</label>
                                <div class="relative">
                                    <input type="month" wire:model.live="data.to"
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
                    @php
                    $activeFilters = 0;
                    if(isset($data['client_id']) && $data['client_id']) $activeFilters++;
                    if(isset($data['tax_type']) && $data['tax_type']) $activeFilters++;
                    if(isset($data['report_status']) && $data['report_status']) $activeFilters++;
                    if(isset($data['payment_status']) && $data['payment_status']) $activeFilters++;
                    @endphp
                    @if($activeFilters > 0)
                    <span
                        class="inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-gradient-to-r from-purple-500 to-pink-500 rounded-full animate-pulse flex-shrink-0">
                        {{ $activeFilters }}
                    </span>
                    @endif
                    <x-heroicon-o-chevron-down
                        class="w-3 h-3 text-gray-400 group-hover:text-gray-600 transition-all duration-200 flex-shrink-0"
                        x-bind:class="showFilters ? 'rotate-180' : ''" />
                </button>

                <!-- Filter Dropdown - Responsive -->
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
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Saring data laporan pajak
                                </p>
                            </div>
                            <div class="p-1.5 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                                <x-heroicon-o-funnel class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                            </div>
                        </div>
                    </div>

                    <div class="p-3 sm:p-4 space-y-4">
                        <!-- Client Filter -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label
                                    class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Klien</label>
                                @if(isset($data['client_id']) && $data['client_id'])
                                <span class="text-xs text-purple-500 font-medium">Aktif</span>
                                @endif
                            </div>
                            <div class="relative">
                                <select wire:model.live="data.client_id"
                                    class="w-full pl-10 pr-4 py-3 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 appearance-none">
                                    <option value="">Semua Klien</option>
                                    @foreach($this->getClientOptions() as $value => $label)
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

                        <!-- Tax Type Filter -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label
                                    class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Jenis
                                    Pajak</label>
                                @if(isset($data['tax_type']) && $data['tax_type'])
                                <span class="text-xs text-purple-500 font-medium">Aktif</span>
                                @endif
                            </div>
                            <div class="relative">
                                <select wire:model.live="data.tax_type"
                                    class="w-full pl-10 pr-4 py-3 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 appearance-none">
                                    <option value="">Semua Jenis</option>
                                    <option value="ppn">PPN</option>
                                    <option value="pph">PPh</option>
                                    <option value="bupot">Bupot</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <x-heroicon-o-document-text class="w-4 h-4 text-gray-400" />
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400" />
                                </div>
                            </div>
                        </div>

                        <!-- Report Status Filter -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label
                                    class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Status
                                    Laporan</label>
                                @if(isset($data['report_status']) && $data['report_status'])
                                <span class="text-xs text-purple-500 font-medium">Aktif</span>
                                @endif
                            </div>
                            <div class="relative">
                                <select wire:model.live="data.report_status"
                                    class="w-full pl-10 pr-4 py-3 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 appearance-none">
                                    <option value="">Semua Status</option>
                                    <option value="Belum Lapor">Belum Lapor</option>
                                    <option value="Sudah Lapor">Sudah Lapor</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <x-heroicon-o-clipboard-document-check class="w-4 h-4 text-gray-400" />
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400" />
                                </div>
                            </div>
                        </div>

                        <!-- Payment Status Filter -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label
                                    class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Status
                                    Pembayaran</label>
                                @if(isset($data['payment_status']) && $data['payment_status'])
                                <span class="text-xs text-purple-500 font-medium">Aktif</span>
                                @endif
                            </div>
                            <div class="relative">
                                <select wire:model.live="data.payment_status"
                                    class="w-full pl-10 pr-4 py-3 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 appearance-none">
                                    <option value="">Semua Status</option>
                                    <option value="Lebih Bayar">Lebih Bayar</option>
                                    <option value="Kurang Bayar">Kurang Bayar</option>
                                    <option value="Nihil">Nihil</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <x-heroicon-o-banknotes class="w-4 h-4 text-gray-400" />
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
                        @if($activeFilters > 0)
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
                            @if(isset($data['client_id']) && $data['client_id'])
                            @php
                            $clientName = \App\Models\Client::find($data['client_id'])?->name ?? 'Client';
                            @endphp
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium bg-gradient-to-r from-blue-100 to-purple-100 text-purple-700 dark:from-blue-900/20 dark:to-purple-900/20 dark:text-purple-300 rounded-lg border border-purple-200 dark:border-purple-700">
                                <x-heroicon-s-building-office class="w-3 h-3 flex-shrink-0" />
                                <span class="truncate max-w-[120px]">{{ $clientName }}</span>
                            </span>
                            @endif
                            @if(isset($data['tax_type']) && $data['tax_type'])
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium bg-gradient-to-r from-green-100 to-teal-100 text-teal-700 dark:from-green-900/20 dark:to-teal-900/20 dark:text-teal-300 rounded-lg border border-teal-200 dark:border-teal-700">
                                <x-heroicon-s-document-text class="w-3 h-3 flex-shrink-0" />
                                <span class="truncate max-w-[120px]">{{ strtoupper($data['tax_type']) }}</span>
                            </span>
                            @endif
                            @if(isset($data['report_status']) && $data['report_status'])
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium bg-gradient-to-r from-amber-100 to-orange-100 text-orange-700 dark:from-amber-900/20 dark:to-orange-900/20 dark:text-orange-300 rounded-lg border border-orange-200 dark:border-orange-700">
                                <x-heroicon-s-clipboard-document-check class="w-3 h-3 flex-shrink-0" />
                                <span class="truncate max-w-[120px]">{{ $data['report_status'] }}</span>
                            </span>
                            @endif
                            @if(isset($data['payment_status']) && $data['payment_status'])
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium bg-gradient-to-r from-pink-100 to-rose-100 text-rose-700 dark:from-pink-900/20 dark:to-rose-900/20 dark:text-rose-300 rounded-lg border border-rose-200 dark:border-rose-700">
                                <x-heroicon-s-banknotes class="w-3 h-3 flex-shrink-0" />
                                <span class="truncate max-w-[120px]">{{ $data['payment_status'] }}</span>
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