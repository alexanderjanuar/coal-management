<div class="" x-data="{ activeTab: 'daftar-pajak' }">
    <div class="px-2 py-4 sm:px-4 lg:px-6">

        {{-- Header Section --}}
        <div class="mb-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white sm:text-2xl">Tax Report Detail</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Periode {{ $taxReport->month }} • {{ $taxReport->client->name }}
                    </p>
                </div>

                {{-- Quick Status Badge - Enhanced & More Noticeable --}}
                <div class="flex items-center gap-3">
                    @if($statusFinal === 'Kurang Bayar')
                    <div
                        class="rounded-xl bg-gradient-to-br from-orange-50 to-orange-100 px-5 py-3 shadow-sm ring-1 ring-orange-200 dark:from-orange-500/10 dark:to-orange-600/10 dark:ring-orange-500/20">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-500 shadow-lg">
                                <x-filament::icon icon="heroicon-m-arrow-trending-up" class="h-5 w-5 text-white" />
                            </div>
                            <div>
                                <span class="block text-xs font-medium text-orange-600 dark:text-orange-400">Status
                                    Pajak</span>
                                <span class="block text-sm font-bold text-orange-700 dark:text-orange-300">
                                    {{ $statusFinal }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @elseif($statusFinal === 'Lebih Bayar')
                    <div
                        class="rounded-xl bg-gradient-to-br from-green-50 to-green-100 px-5 py-3 shadow-sm ring-1 ring-green-200 dark:from-green-500/10 dark:to-green-600/10 dark:ring-green-500/20">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-500 shadow-lg">
                                <x-filament::icon icon="heroicon-m-arrow-trending-down" class="h-5 w-5 text-white" />
                            </div>
                            <div>
                                <span class="block text-xs font-medium text-green-600 dark:text-green-400">Status
                                    Pajak</span>
                                <span class="block text-sm font-bold text-green-700 dark:text-green-300">
                                    {{ $statusFinal }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @else
                    <div
                        class="rounded-xl bg-gradient-to-br from-gray-50 to-gray-100 px-5 py-3 shadow-sm ring-1 ring-gray-200 dark:from-gray-800 dark:to-gray-900 dark:ring-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-400 shadow-lg">
                                <x-filament::icon icon="heroicon-m-check" class="h-5 w-5 text-white" />
                            </div>
                            <div>
                                <span class="block text-xs font-medium text-gray-600 dark:text-gray-400">Status
                                    Pajak</span>
                                <span class="block text-sm font-bold text-gray-700 dark:text-gray-300">
                                    Nihil
                                </span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tab Navigation - Responsive with horizontal scroll on mobile --}}
        <div class="mb-4">
            <div class="border-b border-gray-200 dark:border-gray-800">
                <nav class="-mb-px flex space-x-4 overflow-x-auto pb-px sm:space-x-8" aria-label="Tabs">
                    <button @click="activeTab = 'daftar-pajak'"
                        :class="activeTab === 'daftar-pajak' 
                            ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="group inline-flex flex-shrink-0 items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors whitespace-nowrap">
                        <x-filament::icon icon="heroicon-o-document-text" class="h-5 w-5" />
                        <span class="hidden sm:inline">Daftar Pajak</span>
                        <span class="sm:hidden">Pajak</span>
                        <span :class="activeTab === 'daftar-pajak' 
                                ? 'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400' 
                                : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'"
                            class="ml-2 rounded-full px-2.5 py-0.5 text-xs font-medium">
                            {{ $fakturMasukCount + $fakturKeluarCount }}
                        </span>
                    </button>

                    <button @click="activeTab = 'kalkulasi'"
                        :class="activeTab === 'kalkulasi' 
                            ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="inline-flex flex-shrink-0 items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors whitespace-nowrap">
                        <x-filament::icon icon="heroicon-o-calculator" class="h-5 w-5" />
                        <span>Kalkulasi</span>
                    </button>

                    <button @click="activeTab = 'kompensasi'"
                        :class="activeTab === 'kompensasi' 
                            ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="inline-flex flex-shrink-0 items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors whitespace-nowrap">
                        <x-filament::icon icon="heroicon-o-arrow-path-rounded-square" class="h-5 w-5" />
                        <span>Kompensasi</span>
                        @if($kompensasiTersedia > 0)
                        <span
                            class="ml-2 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-500/20 dark:text-blue-400">
                            Tersedia
                        </span>
                        @endif
                    </button>

                    <button @click="activeTab = 'yearly-summary'"
                        :class="activeTab === 'yearly-summary' 
                            ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="inline-flex flex-shrink-0 items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors whitespace-nowrap">
                        <x-filament::icon icon="heroicon-o-chart-bar-square" class="h-5 w-5" />
                        <span class="hidden sm:inline">Yearly Summary</span>
                        <span class="sm:hidden">Yearly</span>
                    </button>

                    <button @click="activeTab = 'catatan'"
                        :class="activeTab === 'catatan' 
                            ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="inline-flex flex-shrink-0 items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors whitespace-nowrap">
                        <x-filament::icon icon="heroicon-o-pencil-square" class="h-5 w-5" />
                        <span>Catatan</span>
                        @if(!empty($existingNotes))
                        <span
                            class="ml-2 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-600 dark:bg-green-500/20 dark:text-green-400">
                            {{ count($existingNotes) }}
                        </span>
                        @endif
                    </button>

                    <button @click="activeTab = 'riwayat'"
                        :class="activeTab === 'riwayat' 
                            ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="inline-flex flex-shrink-0 items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors whitespace-nowrap">
                        <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5" />
                        <span>Riwayat</span>
                    </button>
                </nav>
            </div>
        </div>

        {{-- Tab Content --}}
        <div class="space-y-6">

            {{-- Daftar Pajak Tab --}}
            <div x-show="activeTab === 'daftar-pajak'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-cloak>
                @livewire('tax-report.components.invoice-table', ['taxReportId' => $taxReportId], key('invoice-table-' .
                $taxReportId))
            </div>

            {{-- Kalkulasi Tab --}}
            <div x-show="activeTab === 'kalkulasi'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-cloak>

                {{-- Summary Cards Row - Responsive Grid --}}
                <div class="grid gap-4 sm:gap-6 md:grid-cols-2 xl:grid-cols-3">
                    {{-- Card: PPN Masuk --}}
                    <div class="xl:col-span-1">
                        <div
                            class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                            <div class="p-4 sm:p-6">
                                <div class="mb-4 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">PPN Masuk</h3>
                                    <div class="rounded-lg bg-emerald-50 p-2 dark:bg-emerald-500/10">
                                        <x-filament::icon icon="heroicon-o-arrow-down-circle"
                                            class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                                        <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white sm:text-2xl">
                                            Rp {{ number_format($ppnMasuk, 0, ',', '.') }}
                                        </p>
                                    </div>

                                    <div
                                        class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800/50">
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Jumlah Faktur</span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                            $fakturMasukCount }}</span>
                                    </div>

                                    @if($fakturMasukExcludedCount > 0)
                                    <div
                                        class="flex items-center justify-between rounded-lg bg-orange-50 px-3 py-2 dark:bg-orange-500/10">
                                        <span class="text-xs text-orange-700 dark:text-orange-400">Dikecualikan</span>
                                        <span class="text-sm font-semibold text-orange-700 dark:text-orange-400">{{
                                            $fakturMasukExcludedCount }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card: PPN Keluar --}}
                    <div class="xl:col-span-1">
                        <div
                            class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                            <div class="p-4 sm:p-6">
                                <div class="mb-4 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">PPN Keluar</h3>
                                    <div class="rounded-lg bg-rose-50 p-2 dark:bg-rose-500/10">
                                        <x-filament::icon icon="heroicon-o-arrow-up-circle"
                                            class="h-5 w-5 text-rose-600 dark:text-rose-400" />
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                                        <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white sm:text-2xl">
                                            Rp {{ number_format($ppnKeluar, 0, ',', '.') }}
                                        </p>
                                    </div>

                                    <div
                                        class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800/50">
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Jumlah Faktur</span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                            $fakturKeluarCount }}</span>
                                    </div>

                                    @if($fakturKeluarExcludedCount > 0)
                                    <div
                                        class="flex items-center justify-between rounded-lg bg-orange-50 px-3 py-2 dark:bg-orange-500/10">
                                        <span class="text-xs text-orange-700 dark:text-orange-400">Dikecualikan</span>
                                        <span class="text-sm font-semibold text-orange-700 dark:text-orange-400">{{
                                            $fakturKeluarExcludedCount }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Saldo Final - Enhanced Status Badge --}}
                    <div class="xl:col-span-1 md:col-span-2 xl:col-span-1">
                        <div
                            class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                            <div class="p-4 sm:p-6">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Saldo Final</p>
                                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl">
                                            Rp {{ number_format(abs($saldoFinal), 0, ',', '.') }}
                                        </p>
                                        <div class="mt-3">
                                            @if($saldoFinal > 0)
                                            <div
                                                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-4 py-2 shadow-lg">
                                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                                </svg>
                                                <span class="text-sm font-bold text-white">Kurang Bayar</span>
                                            </div>
                                            @elseif($saldoFinal < 0) <div
                                                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-green-500 to-green-600 px-4 py-2 shadow-lg">
                                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                                </svg>
                                                <span class="text-sm font-bold text-white">Lebih Bayar</span>
                                        </div>
                                        @else
                                        <div
                                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-gray-400 to-gray-500 px-4 py-2 shadow-lg">
                                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span class="text-sm font-bold text-white">Nihil</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-4 sm:mt-0 sm:ml-4">
                                    <div class="rounded-full bg-blue-50 p-3 dark:bg-blue-500/10">
                                        <x-filament::icon icon="heroicon-o-banknotes"
                                            class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Calculation Details - Responsive Grid --}}
            <div class="mt-4 grid gap-4 sm:mt-6 sm:gap-6 md:grid-cols-2 xl:grid-cols-3">

                {{-- Selisih PPN Card --}}
                <div
                    class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10 sm:p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Selisih PPN</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Sebelum kompensasi</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">PPN Keluar</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white sm:text-base">
                                Rp {{ number_format($ppnKeluar, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-center">
                            <div class="h-px w-full bg-gray-200 dark:bg-gray-700"></div>
                            <span class="px-3 text-gray-400">−</span>
                            <div class="h-px w-full bg-gray-200 dark:bg-gray-700"></div>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">PPN Masuk</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white sm:text-base">
                                Rp {{ number_format($ppnMasuk, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="border-t-2 border-gray-200 pt-4 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">Selisih</span>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-blue-600 dark:text-blue-400 sm:text-xl">
                                        Rp {{ number_format(abs($ppnKeluar - $ppnMasuk), 0, ',', '.') }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        @if($ppnKeluar > $ppnMasuk)
                                        Kurang Bayar
                                        @elseif($ppnKeluar < $ppnMasuk) Lebih Bayar @else Nihil @endif </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Peredaran Bruto Card --}}
                <div
                    class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10 sm:p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Peredaran Bruto</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total omzet periode ini</p>
                        </div>
                        <div class="rounded-full bg-blue-50 p-2 dark:bg-blue-500/10">
                            <x-filament::icon icon="heroicon-o-chart-bar"
                                class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">DPP</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white sm:text-base">
                                Rp {{ number_format($totalDpp, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-center">
                            <div class="h-px w-full bg-gray-200 dark:bg-gray-700"></div>
                            <span class="px-3 text-gray-400">+</span>
                            <div class="h-px w-full bg-gray-200 dark:bg-gray-700"></div>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">DPP Nilai Lainnya</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white sm:text-base">
                                Rp {{ number_format($totalDppNilaiLainnya, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="border-t-2 border-gray-200 pt-4 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">Total Peredaran</span>
                                <p class="text-lg font-bold text-blue-600 dark:text-blue-400 sm:text-xl">
                                    Rp {{ number_format($peredaranBruto, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Faktur Summary Card --}}
                <div
                    class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10 sm:p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Total Faktur</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Ringkasan faktur periode ini</p>
                        </div>
                        <div class="rounded-full bg-indigo-50 p-2 dark:bg-indigo-500/10">
                            <x-filament::icon icon="heroicon-o-document-duplicate"
                                class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div
                            class="flex items-center justify-between rounded-lg bg-emerald-50 px-3 py-2.5 dark:bg-emerald-500/10">
                            <span class="text-sm font-medium text-emerald-900 dark:text-emerald-300">Faktur Masuk</span>
                            <span class="text-lg font-bold text-emerald-900 dark:text-emerald-200">
                                {{ $fakturMasukCount }}
                            </span>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-lg bg-rose-50 px-3 py-2.5 dark:bg-rose-500/10">
                            <span class="text-sm font-medium text-rose-900 dark:text-rose-300">Faktur Keluar</span>
                            <span class="text-lg font-bold text-rose-900 dark:text-rose-200">
                                {{ $fakturKeluarCount }}
                            </span>
                        </div>

                        @if($fakturMasukExcludedCount > 0 || $fakturKeluarExcludedCount > 0)
                        <div class="border-t-2 border-gray-200 pt-4 dark:border-gray-700">
                            <div class="space-y-2">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Dikecualikan:</p>
                                @if($fakturMasukExcludedCount > 0)
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Masuk</span>
                                    <span class="text-sm font-semibold text-orange-700 dark:text-orange-400">
                                        {{ $fakturMasukExcludedCount }}
                                    </span>
                                </div>
                                @endif
                                @if($fakturKeluarExcludedCount > 0)
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Keluar</span>
                                    <span class="text-sm font-semibold text-orange-700 dark:text-orange-400">
                                        {{ $fakturKeluarExcludedCount }}
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="border-t-2 border-gray-200 pt-4 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">Total</span>
                                <p class="text-lg font-bold text-blue-600 dark:text-blue-400 sm:text-xl">
                                    {{ $fakturMasukCount + $fakturKeluarCount }}
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Kompensasi Section (if exists) --}}
            @if($kompensasiDiterima > 0 || $kompensasiTersedia > 0 || $kompensasiTerpakai > 0)
            <div class="mt-4 rounded-2xl bg-blue-50 p-4 dark:bg-blue-500/5 sm:mt-6 sm:p-6">
                <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Detail Kompensasi</h3>

                <div class="grid gap-3 sm:gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @if($kompensasiDiterima > 0)
                    <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-gray-900">
                        <div class="mb-2 flex items-center gap-2">
                            <div class="rounded-lg bg-blue-50 p-2 dark:bg-blue-500/10">
                                <x-filament::icon icon="heroicon-o-arrow-down-tray"
                                    class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            <span
                                class="text-xs font-medium uppercase tracking-wide text-gray-600 dark:text-gray-400">Diterima</span>
                        </div>
                        <p class="text-lg font-bold text-gray-900 dark:text-white sm:text-xl">
                            Rp {{ number_format($kompensasiDiterima, 0, ',', '.') }}
                        </p>
                    </div>
                    @endif

                    @if($kompensasiTersedia > 0)
                    <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-gray-900">
                        <div class="mb-2 flex items-center gap-2">
                            <div class="rounded-lg bg-green-50 p-2 dark:bg-green-500/10">
                                <x-filament::icon icon="heroicon-o-archive-box"
                                    class="h-4 w-4 text-green-600 dark:text-green-400" />
                            </div>
                            <span
                                class="text-xs font-medium uppercase tracking-wide text-gray-600 dark:text-gray-400">Tersedia</span>
                        </div>
                        <p class="text-lg font-bold text-gray-900 dark:text-white sm:text-xl">
                            Rp {{ number_format($kompensasiTersedia, 0, ',', '.') }}
                        </p>
                    </div>
                    @endif

                    @if($kompensasiTerpakai > 0)
                    <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-gray-900">
                        <div class="mb-2 flex items-center gap-2">
                            <div class="rounded-lg bg-gray-50 p-2 dark:bg-gray-800">
                                <x-filament::icon icon="heroicon-o-check-circle"
                                    class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                            </div>
                            <span
                                class="text-xs font-medium uppercase tracking-wide text-gray-600 dark:text-gray-400">Terpakai</span>
                        </div>
                        <p class="text-lg font-bold text-gray-900 dark:text-white sm:text-xl">
                            Rp {{ number_format($kompensasiTerpakai, 0, ',', '.') }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Info Box - Responsive --}}
            <div class="mt-4 rounded-2xl bg-gray-50 p-4 dark:bg-gray-900/50 sm:mt-6 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:gap-4">
                    <div class="flex-shrink-0">
                        <div class="rounded-full bg-blue-100 p-2 dark:bg-blue-500/20">
                            <x-filament::icon icon="heroicon-o-information-circle"
                                class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Aturan Kalkulasi</h4>
                        <ul class="mt-2 grid gap-2 text-sm text-gray-600 dark:text-gray-400 sm:grid-cols-2">
                            <li class="flex items-start gap-2">
                                <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-blue-600" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Faktur 02, 03, 07, 08 dikecualikan</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-blue-600" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Hitung revisi terbaru saja</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-blue-600" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Masuk yang business-related</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-blue-600" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Peredaran Bruto = DPP + DPP NL</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kompensasi Tab --}}
        <div x-show="activeTab === 'kompensasi'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-cloak>
            @livewire('tax-report.components.tax-report-kompensasi', ['taxReportId' => $taxReportId])
        </div>

        {{-- Yearly Summary Tab --}}
        <div x-show="activeTab === 'yearly-summary'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-cloak>
            @livewire('tax-report.components.yearly-summary', ['taxReportId' => $taxReportId, 'clientId' =>
            $taxReport->client_id])
        </div>

        {{-- Catatan Tab - Now Functional --}}
        <div x-show="activeTab === 'catatan'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-cloak>
            <div>
                <div
                    class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10 sm:p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Catatan PPN</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Catatan untuk perhitungan PPN periode
                            {{ $taxReport->month }}</p>
                    </div>

                    <form wire:submit="saveNote" class="space-y-6">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Tambah Catatan Baru
                            </label>
                            <div class="relative">
                                <textarea wire:model="newNote" rows="4"
                                    class="w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white @error('newNote') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                    placeholder="Tulis catatan untuk periode ini... (minimal 3 karakter, maksimal 1000 karakter)"></textarea>

                                {{-- Character counter --}}
                                <div class="absolute bottom-2 right-3 text-xs text-gray-400">
                                    <span x-text="$wire.newNote.length"></span>/1000
                                </div>
                            </div>

                            @error('newNote')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="submit" wire:loading.attr="disabled" wire:target="saveNote"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-blue-500 dark:hover:bg-blue-600">
                                <x-filament::icon icon="heroicon-m-check" class="h-4 w-4" wire:loading.remove.delay
                                    wire:target="saveNote" />
                                <x-filament::icon icon="heroicon-m-arrow-path" class="h-4 w-4 animate-spin"
                                    wire:loading.delay wire:target="saveNote" />
                                <span wire:loading.remove.delay wire:target="saveNote">Simpan Catatan</span>
                                <span wire:loading.delay wire:target="saveNote">Menyimpan...</span>
                            </button>

                            @if(!empty($newNote))
                            <button type="button" wire:click="$set('newNote', '')"
                                class="inline-flex items-center gap-2 rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                <x-filament::icon icon="heroicon-m-x-mark" class="h-4 w-4" />
                                Batal
                            </button>
                            @endif
                        </div>
                    </form>

                    {{-- Existing Notes Section --}}
                    <div class="mt-8 space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                Catatan Sebelumnya
                                @if(!empty($existingNotes))
                                <span
                                    class="ml-2 inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ count($existingNotes) }}
                                </span>
                                @endif
                            </h4>
                        </div>

                        @if(!empty($existingNotes))
                        <div class="space-y-4 max-h-96 overflow-y-auto">
                            @foreach($existingNotes as $index => $note)
                            <div
                                class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-2">
                                            <div class="flex-shrink-0">
                                                <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                                            </div>
                                            <time class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                {{ \Carbon\Carbon::parse($note['created_at'])->format('d M Y, H:i') }}
                                            </time>
                                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                                ({{ \Carbon\Carbon::parse($note['created_at'])->diffForHumans() }})
                                            </span>
                                        </div>
                                        <div class="prose prose-sm max-w-none dark:prose-invert">
                                            <p
                                                class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line break-words">
                                                {{ $note['content'] }}</p>
                                        </div>
                                    </div>

                                    {{-- Delete button --}}
                                    <div class="flex-shrink-0">
                                        <button wire:click="deleteNote({{ $index }})"
                                            wire:confirm="Apakah Anda yakin ingin menghapus catatan ini?"
                                            class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-red-100 text-red-600 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40"
                                            title="Hapus catatan">
                                            <x-filament::icon icon="heroicon-m-trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="rounded-xl bg-gray-50 p-8 dark:bg-gray-800/50">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="rounded-full bg-gray-100 p-3 dark:bg-gray-800">
                                    <x-filament::icon icon="heroicon-o-document-text" class="h-6 w-6 text-gray-400" />
                                </div>
                                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada catatan untuk periode ini
                                </p>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                    Tambahkan catatan untuk mencatat informasi penting tentang perhitungan PPN
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Riwayat Tab --}}
        <div x-show="activeTab === 'riwayat'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-cloak>
            <div>
                <div
                    class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10 sm:p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Aktivitas</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Log aktivitas dan perubahan</p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex gap-4">
                            <div class="relative flex flex-col items-center">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-white">
                                    <x-filament::icon icon="heroicon-m-document-plus" class="h-5 w-5" />
                                </div>
                                <div class="mt-2 h-full w-0.5 bg-gray-200 dark:bg-gray-700"></div>
                            </div>
                            <div class="flex-1 rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                            Tax Report Dibuat
                                        </h4>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            Tax report untuk periode ini telah dibuat
                                        </p>
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Baru saja</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-center py-12">
                            <div class="text-center">
                                <div class="mx-auto rounded-full bg-gray-100 p-3 dark:bg-gray-800">
                                    <x-filament::icon icon="heroicon-o-clock" class="h-6 w-6 text-gray-400" />
                                </div>
                                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                                    Riwayat aktivitas akan muncul di sini
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</div>