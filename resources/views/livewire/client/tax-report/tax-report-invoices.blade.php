<div class="" x-data="{ activeTab: 'daftar-pajak' }">
    <div class="px-2 py-4 sm:px-4 lg:px-6">
        {{-- Header Section --}}
        <div class="mb-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white sm:text-2xl">Detail Laporan PPN</h1>
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
                                <x-heroicon-m-arrow-trending-up class="h-5 w-5 text-white" />
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
                                <x-heroicon-m-arrow-trending-down class="h-5 w-5 text-white" />
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
                                <x-heroicon-m-check class="h-5 w-5 text-white" />
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
                            ? 'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="group inline-flex flex-shrink-0 items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors whitespace-nowrap">
                        <x-heroicon-o-document-text class="h-5 w-5" />
                        <span class="hidden sm:inline">Daftar Pajak</span>
                        <span class="sm:hidden">Pajak</span>
                        <span :class="activeTab === 'daftar-pajak' 
                                ? 'bg-primary-100 text-primary-600 dark:bg-primary-500/20 dark:text-primary-400' 
                                : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'"
                            class="ml-2 rounded-full px-2.5 py-0.5 text-xs font-medium">
                            {{ $fakturMasukCount + $fakturKeluarCount }}
                        </span>
                    </button>

                    <button @click="activeTab = 'kalkulasi'"
                        :class="activeTab === 'kalkulasi' 
                            ? 'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="inline-flex flex-shrink-0 items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors whitespace-nowrap">
                        <x-heroicon-o-calculator class="h-5 w-5" />
                        <span>Kalkulasi</span>
                    </button>

                    <button @click="activeTab = 'kompensasi'"
                        :class="activeTab === 'kompensasi' 
                            ? 'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="inline-flex flex-shrink-0 items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors whitespace-nowrap">
                        <x-heroicon-o-arrow-path-rounded-square class="h-5 w-5" />
                        <span>Kompensasi</span>
                        @if($kompensasiTersedia > 0)
                        <span
                            class="ml-2 rounded-full bg-primary-100 px-2.5 py-0.5 text-xs font-medium text-primary-600 dark:bg-primary-500/20 dark:text-primary-400">
                            Tersedia
                        </span>
                        @endif
                    </button>

                    <button @click="activeTab = 'yearly-summary'"
                        :class="activeTab === 'yearly-summary' 
                            ? 'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="inline-flex flex-shrink-0 items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors whitespace-nowrap">
                        <x-heroicon-o-chart-bar-square class="h-5 w-5" />
                        <span class="hidden sm:inline">Yearly Summary</span>
                        <span class="sm:hidden">Yearly</span>
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
                @livewire('client.tax-report.components.invoice-table', ['taxReportId' => $taxReportId],
                key('invoice-table-' . $taxReportId . time()))
            </div>

            {{-- Kalkulasi Tab --}}
            <div x-show="activeTab === 'kalkulasi'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-cloak>

                {{-- Top Row: Main Summary Cards --}}
                <div class="grid gap-4 sm:gap-6 md:grid-cols-3">
                    {{-- Card: PPN Masuk --}}
                    <div>
                        <div
                            class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                            <div class="p-6">
                                <div class="mb-4 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">PPN Masuk</h3>
                                    <div class="rounded-lg bg-emerald-50 p-2 dark:bg-emerald-500/10">
                                        <x-heroicon-o-arrow-down-circle
                                            class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                                        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
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
                    <div>
                        <div
                            class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                            <div class="p-6">
                                <div class="mb-4 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">PPN Keluar</h3>
                                    <div class="rounded-lg bg-rose-50 p-2 dark:bg-rose-500/10">
                                        <x-heroicon-o-arrow-up-circle
                                            class="h-5 w-5 text-rose-600 dark:text-rose-400" />
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                                        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
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

                    {{-- Card: Saldo Final --}}
                    <div>
                        <div
                            class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                            <div class="p-6">
                                <div class="mb-4 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Saldo Final</h3>
                                    <div class="rounded-full bg-primary-50 p-2 dark:bg-primary-500/10">
                                        <x-heroicon-o-banknotes
                                            class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                            Rp {{ number_format(abs($saldoFinal), 0, ',', '.') }}
                                        </p>
                                    </div>

                                    @if($saldoFinal > 0)
                                    <div
                                        class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-3 py-2 shadow-sm">
                                        <x-heroicon-m-arrow-trending-up class="h-4 w-4 text-white" />
                                        <span class="text-sm font-semibold text-white">Kurang Bayar</span>
                                    </div>
                                    @elseif($saldoFinal < 0) <div
                                        class="inline-flex items-center gap-2 rounded-lg bg-green-500 px-3 py-2 shadow-sm">
                                        <x-heroicon-m-arrow-trending-down class="h-4 w-4 text-white" />
                                        <span class="text-sm font-semibold text-white">Lebih Bayar</span>
                                </div>
                                @else
                                <div class="inline-flex items-center gap-2 rounded-lg bg-gray-400 px-3 py-2 shadow-sm">
                                    <x-heroicon-m-check class="h-4 w-4 text-white" />
                                    <span class="text-sm font-semibold text-white">Nihil</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Second Row: Calculation Details --}}
            <div class="mt-6 grid gap-4 sm:gap-6 md:grid-cols-2 lg:grid-cols-3">

                {{-- Selisih PPN Card --}}
                <div
                    class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Selisih PPN</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Sebelum kompensasi</p>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">PPN Keluar</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                Rp {{ number_format($ppnKeluar, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
                            <span class="text-xs text-gray-400">−</span>
                            <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">PPN Masuk</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                Rp {{ number_format($ppnMasuk, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="border-t-2 border-gray-200 pt-3 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Selisih</p>
                            <p class="text-lg font-bold text-primary-600 dark:text-primary-400">
                                Rp {{ number_format(abs($ppnKeluar - $ppnMasuk), 0, ',', '.') }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                @if($ppnKeluar > $ppnMasuk)
                                Kurang Bayar
                                @elseif($ppnKeluar < $ppnMasuk) Lebih Bayar @else Nihil @endif </p>
                        </div>
                    </div>
                </div>

                {{-- Peredaran Bruto Card --}}
                <div
                    class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Peredaran Bruto</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total omzet periode ini</p>
                        </div>
                        <div class="rounded-lg bg-primary-50 p-1.5 dark:bg-primary-500/10">
                            <x-heroicon-o-chart-bar class="h-4 w-4 text-primary-600 dark:text-primary-400" />
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">DPP</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                Rp {{ number_format($totalDpp, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
                            <span class="text-xs text-gray-400">+</span>
                            <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">DPP Nilai Lainnya</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                Rp {{ number_format($totalDppNilaiLainnya, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="border-t-2 border-gray-200 pt-3 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Peredaran</p>
                            <p class="text-lg font-bold text-primary-600 dark:text-primary-400">
                                Rp {{ number_format($peredaranBruto, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Total Faktur Card --}}
                <div
                    class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Total Faktur</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Ringkasan faktur periode ini</p>
                        </div>
                        <div class="rounded-lg bg-indigo-50 p-1.5 dark:bg-indigo-500/10">
                            <x-heroicon-o-document-duplicate class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div
                            class="flex items-center justify-between rounded-lg bg-emerald-50 px-3 py-2 dark:bg-emerald-500/10">
                            <span class="text-xs font-medium text-emerald-900 dark:text-emerald-300">Faktur Masuk</span>
                            <span class="text-base font-bold text-emerald-900 dark:text-emerald-200">
                                {{ $fakturMasukCount }}
                            </span>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-lg bg-rose-50 px-3 py-2 dark:bg-rose-500/10">
                            <span class="text-xs font-medium text-rose-900 dark:text-rose-300">Faktur Keluar</span>
                            <span class="text-base font-bold text-rose-900 dark:text-rose-200">
                                {{ $fakturKeluarCount }}
                            </span>
                        </div>

                        @if($fakturMasukExcludedCount > 0 || $fakturKeluarExcludedCount > 0)
                        <div class="border-t border-gray-200 pt-2 dark:border-gray-700">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Dikecualikan:</p>
                            <div class="space-y-1">
                                @if($fakturMasukExcludedCount > 0)
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Masuk</span>
                                    <span class="text-xs font-semibold text-orange-700 dark:text-orange-400">
                                        {{ $fakturMasukExcludedCount }}
                                    </span>
                                </div>
                                @endif
                                @if($fakturKeluarExcludedCount > 0)
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Keluar</span>
                                    <span class="text-xs font-semibold text-orange-700 dark:text-orange-400">
                                        {{ $fakturKeluarExcludedCount }}
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="border-t border-gray-200 pt-2 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-900 dark:text-white">Total</span>
                                <p class="text-lg font-bold text-primary-600 dark:text-primary-400">
                                    {{ $fakturMasukCount + $fakturKeluarCount }}
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Third Row: Aturan Kalkulasi --}}
            <div class="mt-6">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="rounded-lg bg-primary-50 p-2 dark:bg-primary-500/10">
                            <x-heroicon-o-information-circle class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Aturan Kalkulasi</h3>
                        <ul class="grid gap-2 text-sm text-gray-600 dark:text-gray-400 sm:grid-cols-2 lg:grid-cols-4">
                            <li class="flex items-start gap-2">
                                <x-heroicon-m-check-circle
                                    class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary-600 dark:text-primary-400" />
                                <span>Faktur 02, 03, 07, 08 dikecualikan</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <x-heroicon-m-check-circle
                                    class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary-600 dark:text-primary-400" />
                                <span>Hitung revisi terbaru saja</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <x-heroicon-m-check-circle
                                    class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary-600 dark:text-primary-400" />
                                <span>Masuk yang business-related</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <x-heroicon-m-check-circle
                                    class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary-600 dark:text-primary-400" />
                                <span>Peredaran Bruto = DPP + DPP NL</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Kompensasi Section (if exists) --}}
            @if($kompensasiDiterima > 0 || $kompensasiTersedia > 0 || $kompensasiTerpakai > 0)
            <div class="mt-6 rounded-2xl bg-primary-50 p-4 dark:bg-primary-500/5 sm:p-6">
                <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Detail Kompensasi</h3>

                <div class="grid gap-3 sm:gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @if($kompensasiDiterima > 0)
                    <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-gray-900">
                        <div class="mb-2 flex items-center gap-2">
                            <div class="rounded-lg bg-primary-50 p-2 dark:bg-primary-500/10">
                                <x-heroicon-o-arrow-down-tray class="h-4 w-4 text-primary-600 dark:text-primary-400" />
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
                                <x-heroicon-o-archive-box class="h-4 w-4 text-green-600 dark:text-green-400" />
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
                                <x-heroicon-o-check-circle class="h-4 w-4 text-gray-600 dark:text-gray-400" />
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
        </div>

        {{-- Kompensasi Tab --}}
        <div x-show="activeTab === 'kompensasi'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-cloak>
            {{-- COMMENTED: Kompensasi Component will be created later --}}
            {{-- @livewire('client.tax-report.tax-report-kompensasi', ['taxReportId' => $taxReportId]) --}}

            <div
                class="rounded-xl border border-gray-200 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-800">
                <div class="mx-auto max-w-md">
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                        <x-heroicon-o-arrow-path-rounded-square class="h-8 w-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Kompensasi</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Detail kompensasi akan ditampilkan di sini
                    </p>
                </div>
            </div>
        </div>

        {{-- Yearly Summary Tab --}}
        <div x-show="activeTab === 'yearly-summary'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-cloak>
            {{-- COMMENTED: Yearly Summary Component will be created later --}}
            {{-- @livewire('client.tax-report.yearly-summary', ['taxReportId' => $taxReportId, 'clientId' =>
            $taxReport->client_id]) --}}

            <div
                class="rounded-xl border border-gray-200 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-800">
                <div class="mx-auto max-w-md">
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                        <x-heroicon-o-chart-bar-square class="h-8 w-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Yearly Summary</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Ringkasan tahunan akan ditampilkan di sini
                    </p>
                </div>
            </div>
        </div>

    </div>

</div>
</div>