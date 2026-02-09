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
                            ? 'border-gray-900 text-gray-900 dark:border-gray-100 dark:text-gray-100' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="group inline-flex flex-shrink-0 items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors whitespace-nowrap">
                        <x-heroicon-o-document-text class="h-5 w-5" />
                        <span class="hidden sm:inline">Daftar Pajak</span>
                        <span class="sm:hidden">Pajak</span>
                        <span :class="activeTab === 'daftar-pajak' 
                                ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' 
                                : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'"
                            class="ml-2 rounded-full px-2.5 py-0.5 text-xs font-medium">
                            {{ $fakturMasukCount + $fakturKeluarCount }}
                        </span>
                    </button>

                    <button @click="activeTab = 'kalkulasi'"
                        :class="activeTab === 'kalkulasi' 
                            ? 'border-gray-900 text-gray-900 dark:border-gray-100 dark:text-gray-100' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="inline-flex flex-shrink-0 items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors whitespace-nowrap">
                        <x-heroicon-o-calculator class="h-5 w-5" />
                        <span>Kalkulasi</span>
                    </button>

                    <button @click="activeTab = 'yearly-summary'"
                        :class="activeTab === 'yearly-summary' 
                            ? 'border-gray-900 text-gray-900 dark:border-gray-100 dark:text-gray-100' 
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
                @livewire('client.panel.tax-report.components.invoice-table', ['taxReportId' => $taxReportId],
                key('invoice-table-' . $taxReportId . time()))
            </div>

            {{-- Kalkulasi Tab - Ultra Clean Design with Headers & Tooltips --}}
            <div x-show="activeTab === 'kalkulasi'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-cloak>

                {{-- Section 1: Ringkasan Utama --}}
                <div class="mb-6">
                    <div class="mb-4 flex items-center gap-2">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Ringkasan Utama</h2>
                        <div class="group relative">
                            <svg class="h-4 w-4 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div
                                class="invisible group-hover:visible absolute left-0 top-6 z-10 w-64 rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg dark:bg-gray-700">
                                Ringkasan perhitungan PPN untuk periode ini, menampilkan total PPN Masuk, PPN Keluar,
                                dan status akhir
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:gap-6 md:grid-cols-2 xl:grid-cols-4">

                        {{-- Card 1: PPN Masuk --}}
                        <div
                            class="rounded-xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-700">
                            <div class="flex items-start justify-between mb-3">
                                <span class="text-sm text-gray-600 dark:text-gray-400">1</span>
                                <div class="group relative">
                                    <svg class="h-4 w-4 text-gray-400 cursor-help" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div
                                        class="invisible group-hover:visible absolute right-0 top-6 z-10 w-48 rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg dark:bg-gray-700">
                                        Total PPN yang dibayarkan saat pembelian/input (dapat dikreditkan)
                                    </div>
                                </div>
                            </div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">PPN Masuk</h3>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                                Rp {{ number_format($ppnMasuk, 0, ',', '.') }}
                            </p>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    {{ $fakturMasukCount }} Faktur
                                </span>
                            </div>
                        </div>

                        {{-- Card 2: PPN Keluar --}}
                        <div
                            class="rounded-xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-700">
                            <div class="flex items-start justify-between mb-3">
                                <span class="text-sm text-gray-600 dark:text-gray-400">2</span>
                                <div class="group relative">
                                    <svg class="h-4 w-4 text-gray-400 cursor-help" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div
                                        class="invisible group-hover:visible absolute right-0 top-6 z-10 w-48 rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg dark:bg-gray-700">
                                        Total PPN yang dipungut dari penjualan/output (wajib disetor)
                                    </div>
                                </div>
                            </div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">PPN Keluar</h3>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                                Rp {{ number_format($ppnKeluar, 0, ',', '.') }}
                            </p>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                    {{ $fakturKeluarCount }} Faktur
                                </span>
                            </div>
                        </div>

                        {{-- Card 3: Selisih --}}
                        <div
                            class="rounded-xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-700">
                            <div class="flex items-start justify-between mb-3">
                                <span class="text-sm text-gray-600 dark:text-gray-400">3</span>
                                <div class="group relative">
                                    <svg class="h-4 w-4 text-gray-400 cursor-help" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div
                                        class="invisible group-hover:visible absolute right-0 top-6 z-10 w-48 rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg dark:bg-gray-700">
                                        Selisih antara PPN Keluar dan PPN Masuk sebelum kompensasi
                                    </div>
                                </div>
                            </div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Selisih</h3>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                                Rp {{ number_format(abs($ppnKeluar - $ppnMasuk), 0, ',', '.') }}
                            </p>
                            <div class="flex items-center gap-2">
                                @if($ppnKeluar > $ppnMasuk)
                                <span
                                    class="inline-flex items-center gap-1 text-xs text-orange-600 dark:text-orange-400">
                                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                                    Kurang Bayar
                                </span>
                                @elseif($ppnKeluar < $ppnMasuk) <span
                                    class="inline-flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    Lebih Bayar
                                    </span>
                                    @else
                                    <span
                                        class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                        <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                                        Nihil
                                    </span>
                                    @endif
                            </div>
                        </div>

                        {{-- Card 4: Saldo Akhir --}}
                        <div
                            class="rounded-xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-700">
                            <div class="flex items-start justify-between mb-3">
                                <span class="text-sm text-gray-600 dark:text-gray-400">✓</span>
                                <div class="group relative">
                                    <svg class="h-4 w-4 text-gray-400 cursor-help" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div
                                        class="invisible group-hover:visible absolute right-0 top-6 z-10 w-48 rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg dark:bg-gray-700">
                                        Saldo akhir setelah diperhitungkan dengan kompensasi (jika ada)
                                    </div>
                                </div>
                            </div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Saldo Akhir</h3>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                                Rp {{ number_format(abs($saldoFinal), 0, ',', '.') }}
                            </p>
                            <div class="flex items-center gap-2">
                                @if($statusFinal === 'Kurang Bayar')
                                <span
                                    class="inline-flex items-center gap-1 text-xs text-orange-600 dark:text-orange-400">
                                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                                    Kurang Bayar
                                </span>
                                @elseif($statusFinal === 'Lebih Bayar')
                                <span class="inline-flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    Lebih Bayar
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                    <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                                    Nihil
                                </span>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Section 2: Detail Perhitungan --}}
                <div class="mb-6">
                    <div class="mb-4 flex items-center gap-2">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Detail Perhitungan</h2>
                        <div class="group relative">
                            <svg class="h-4 w-4 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div
                                class="invisible group-hover:visible absolute left-0 top-6 z-10 w-64 rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg dark:bg-gray-700">
                                Rincian lengkap perhitungan PPN, peredaran bruto, dan ringkasan faktur
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:gap-6 md:grid-cols-2 xl:grid-cols-3">

                        {{-- Detail Card 1: Calculation Breakdown --}}
                        <div
                            class="rounded-xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Perhitungan PPN</h3>
                                <div class="group relative">
                                    <svg class="h-4 w-4 text-gray-400 cursor-help" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div
                                        class="invisible group-hover:visible absolute right-0 top-6 z-10 w-48 rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg dark:bg-gray-700">
                                        Formula: PPN Keluar - PPN Masuk = Selisih
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">PPN Keluar</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        Rp {{ number_format($ppnKeluar, 0, ',', '.') }}
                                    </span>
                                </div>

                                <div
                                    class="flex items-center justify-between text-sm border-t border-gray-100 pt-3 dark:border-gray-700">
                                    <span class="text-gray-600 dark:text-gray-400">PPN Masuk</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        Rp {{ number_format($ppnMasuk, 0, ',', '.') }}
                                    </span>
                                </div>

                                <div
                                    class="flex items-center justify-between text-sm border-t border-gray-200 pt-3 font-semibold dark:border-gray-600">
                                    <span class="text-gray-900 dark:text-white">Selisih</span>
                                    <span class="text-gray-900 dark:text-white">
                                        Rp {{ number_format(abs($ppnKeluar - $ppnMasuk), 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Detail Card 2: Peredaran Bruto (DPP only) --}}
                        <div
                            class="rounded-xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Peredaran Bruto</h3>
                                <div class="group relative">
                                    <svg class="h-4 w-4 text-gray-400 cursor-help" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div
                                        class="invisible group-hover:visible absolute right-0 top-6 z-10 w-48 rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg dark:bg-gray-700">
                                        Total nilai DPP (Dasar Pengenaan Pajak) dari seluruh transaksi
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Total DPP</span>
                                        <div class="group/dpp relative">
                                            <svg class="h-3.5 w-3.5 text-gray-400 cursor-help" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <div
                                                class="invisible group-hover/dpp:visible absolute left-0 top-6 z-10 w-48 rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg dark:bg-gray-700">
                                                Dasar Pengenaan Pajak dari Faktur Keluar
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-2xl font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($totalDpp, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Detail Card 3: Faktur Summary --}}
                        <div
                            class="rounded-xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Ringkasan Faktur</h3>
                                <div class="group relative">
                                    <svg class="h-4 w-4 text-gray-400 cursor-help" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div
                                        class="invisible group-hover:visible absolute right-0 top-6 z-10 w-48 rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg dark:bg-gray-700">
                                        Jumlah total faktur yang dihitung dalam periode ini
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Faktur Masuk</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ $fakturMasukCount }}
                                        @if($fakturMasukExcludedCount > 0)
                                        <span class="text-xs text-orange-600">({{ $fakturMasukExcludedCount }}
                                            dikecualikan)</span>
                                        @endif
                                    </span>
                                </div>

                                <div
                                    class="flex items-center justify-between text-sm border-t border-gray-100 pt-3 dark:border-gray-700">
                                    <span class="text-gray-600 dark:text-gray-400">Faktur Keluar</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ $fakturKeluarCount }}
                                        @if($fakturKeluarExcludedCount > 0)
                                        <span class="text-xs text-orange-600">({{ $fakturKeluarExcludedCount }}
                                            dikecualikan)</span>
                                        @endif
                                    </span>
                                </div>

                                <div
                                    class="flex items-center justify-between text-sm border-t border-gray-200 pt-3 font-semibold dark:border-gray-600">
                                    <span class="text-gray-900 dark:text-white">Total Faktur</span>
                                    <span class="text-gray-900 dark:text-white">
                                        {{ $fakturMasukCount + $fakturKeluarCount }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Section 3: Kompensasi (if exists) --}}
                @if($kompensasiDiterima > 0 || $kompensasiTersedia > 0 || $kompensasiTerpakai > 0)
                <div class="mb-6">
                    <div class="mb-4 flex items-center gap-2">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Detail Kompensasi</h2>
                        <div class="group relative">
                            <svg class="h-4 w-4 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div
                                class="invisible group-hover:visible absolute left-0 top-6 z-10 w-64 rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg dark:bg-gray-700">
                                Informasi kompensasi lebih bayar yang digunakan atau tersedia untuk periode mendatang
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:gap-6 md:grid-cols-2 xl:grid-cols-3">
                        {{-- Kompensasi Diterima --}}
                        @if($kompensasiDiterima > 0)
                        <div
                            class="rounded-xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Kompensasi Diterima</h4>
                                <div class="group relative">
                                    <svg class="h-4 w-4 text-gray-400 cursor-help" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div
                                        class="invisible group-hover:visible absolute right-0 top-6 z-10 w-48 rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg dark:bg-gray-700">
                                        Kompensasi lebih bayar dari periode sebelumnya yang digunakan
                                    </div>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                                Rp {{ number_format($kompensasiDiterima, 0, ',', '.') }}
                            </p>
                            <span class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                                Dari periode sebelumnya
                            </span>
                        </div>
                        @endif

                        {{-- Kompensasi Tersedia --}}
                        @if($kompensasiTersedia > 0)
                        <div
                            class="rounded-xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Kompensasi Tersedia</h4>
                                <div class="group relative">
                                    <svg class="h-4 w-4 text-gray-400 cursor-help" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div
                                        class="invisible group-hover:visible absolute right-0 top-6 z-10 w-48 rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg dark:bg-gray-700">
                                        Sisa kompensasi yang dapat digunakan untuk periode berikutnya
                                    </div>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                                Rp {{ number_format($kompensasiTersedia, 0, ',', '.') }}
                            </p>
                            <span class="inline-flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                Dapat digunakan
                            </span>
                        </div>
                        @endif

                        {{-- Kompensasi Terpakai --}}
                        @if($kompensasiTerpakai > 0)
                        <div
                            class="rounded-xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Kompensasi Terpakai</h4>
                                <div class="group relative">
                                    <svg class="h-4 w-4 text-gray-400 cursor-help" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div
                                        class="invisible group-hover:visible absolute right-0 top-6 z-10 w-48 rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg dark:bg-gray-700">
                                        Kompensasi yang sudah digunakan di periode berikutnya
                                    </div>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                                Rp {{ number_format($kompensasiTerpakai, 0, ',', '.') }}
                            </p>
                            <span class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                                Sudah digunakan
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Info Note --}}
                <div
                    class="rounded-xl bg-gray-50 p-4 border border-gray-100 dark:bg-gray-800/50 dark:border-gray-700/50">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Aturan Perhitungan
                            </h4>
                            <p class="text-xs text-gray-700 dark:text-gray-400 leading-relaxed">
                                Faktur dengan kode 02, 03, 07, 08 dikecualikan • Hitung revisi terbaru • Masuk yang
                                business-related • Peredaran Bruto berdasarkan DPP
                            </p>
                        </div>
                    </div>
                </div>

            </div>


        {{-- Yearly Summary Tab --}}
        <div x-show="activeTab === 'yearly-summary'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-cloak>
            @livewire('client.panel.tax-report.yearly-summary', ['taxReportId' => $taxReportId, 'clientId' => $taxReport->client_id], key('yearly-summary-' . $taxReportId . time()))
        </div>

    </div>

</div>
</div>