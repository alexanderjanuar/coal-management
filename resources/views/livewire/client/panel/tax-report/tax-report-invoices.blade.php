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

            {{-- Kalkulasi Tab - Redesigned with Monochrome Colors --}}
            <div x-show="activeTab === 'kalkulasi'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-cloak>
                
                <div class="space-y-6">
                    {{-- Summary Section --}}
                    <div class="grid gap-6 md:grid-cols-2">
                        {{-- PPN Calculation Card --}}
                        <div class="rounded-lg border border-gray-300 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
                            <h3 class="mb-6 text-lg font-semibold text-gray-900 dark:text-white">Perhitungan PPN</h3>
                            
                            <div class="space-y-4">
                                {{-- PPN Keluar --}}
                                <div class="flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-700">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">PPN Keluar</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $fakturKeluarCount }} faktur</p>
                                    </div>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($ppnKeluar, 0, ',', '.') }}
                                    </p>
                                </div>

                                {{-- PPN Masuk --}}
                                <div class="flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-700">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">PPN Masuk</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $fakturMasukCount }} faktur</p>
                                    </div>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($ppnMasuk, 0, ',', '.') }}
                                    </p>
                                </div>

                                {{-- Kompensasi Diterima (if exists) --}}
                                @if($kompensasiDiterima > 0)
                                <div class="flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-700">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Kompensasi Diterima</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Dari periode sebelumnya</p>
                                    </div>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($kompensasiDiterima, 0, ',', '.') }}
                                    </p>
                                </div>
                                @endif

                                {{-- Saldo Final --}}
                                <div class="rounded-lg bg-gray-100 p-4 dark:bg-gray-800">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Saldo Final</p>
                                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                                @if($saldoFinal > 0)
                                                    Kurang Bayar
                                                @elseif($saldoFinal < 0)
                                                    Lebih Bayar
                                                @else
                                                    Nihil
                                                @endif
                                            </p>
                                        </div>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                            Rp {{ number_format(abs($saldoFinal), 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Peredaran Bruto Card --}}
                        <div class="rounded-lg border border-gray-300 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
                            <h3 class="mb-6 text-lg font-semibold text-gray-900 dark:text-white">Peredaran Bruto</h3>
                            
                            <div class="space-y-4">
                                {{-- DPP --}}
                                <div class="flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-700">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">DPP (Dasar Pengenaan Pajak)</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($totalDpp, 0, ',', '.') }}
                                    </p>
                                </div>

                                {{-- DPP Nilai Lainnya --}}
                                <div class="flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-700">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">DPP Nilai Lainnya</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($totalDppNilaiLainnya, 0, ',', '.') }}
                                    </p>
                                </div>

                                {{-- Total Peredaran --}}
                                <div class="rounded-lg bg-gray-100 p-4 dark:bg-gray-800">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Total Peredaran Bruto</p>
                                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Periode ini</p>
                                        </div>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                            Rp {{ number_format($peredaranBruto, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kompensasi Section (if exists) --}}
                    @if($kompensasiDiterima > 0 || $kompensasiTersedia > 0 || $kompensasiTerpakai > 0)
                    <div class="rounded-lg border border-gray-300 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Detail Kompensasi</h3>
                        
                        <div class="grid gap-4 sm:grid-cols-3">
                            @if($kompensasiDiterima > 0)
                            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Diterima</p>
                                <p class="mt-2 text-xl font-bold text-gray-900 dark:text-white">
                                    Rp {{ number_format($kompensasiDiterima, 0, ',', '.') }}
                                </p>
                            </div>
                            @endif

                            @if($kompensasiTersedia > 0)
                            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Tersedia</p>
                                <p class="mt-2 text-xl font-bold text-gray-900 dark:text-white">
                                    Rp {{ number_format($kompensasiTersedia, 0, ',', '.') }}
                                </p>
                            </div>
                            @endif

                            @if($kompensasiTerpakai > 0)
                            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Terpakai</p>
                                <p class="mt-2 text-xl font-bold text-gray-900 dark:text-white">
                                    Rp {{ number_format($kompensasiTerpakai, 0, ',', '.') }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Excluded Invoices Info --}}
                    @if($fakturMasukExcludedCount > 0 || $fakturKeluarExcludedCount > 0)
                    <div class="rounded-lg border border-gray-300 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-information-circle class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Faktur Dikecualikan</h4>
                                <div class="mt-2 grid gap-2 text-sm text-gray-600 dark:text-gray-400 sm:grid-cols-2">
                                    @if($fakturMasukExcludedCount > 0)
                                    <div class="flex items-center justify-between">
                                        <span>Faktur Masuk:</span>
                                        <span class="font-semibold">{{ $fakturMasukExcludedCount }} faktur</span>
                                    </div>
                                    @endif
                                    @if($fakturKeluarExcludedCount > 0)
                                    <div class="flex items-center justify-between">
                                        <span>Faktur Keluar:</span>
                                        <span class="font-semibold">{{ $fakturKeluarExcludedCount }} faktur</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Calculation Rules --}}
                    <div class="rounded-lg border border-gray-300 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
                        <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Aturan Kalkulasi</h3>
                        <ul class="grid gap-3 text-sm text-gray-600 dark:text-gray-400 sm:grid-cols-2">
                            <li class="flex items-start gap-2">
                                <x-heroicon-m-check-circle class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-600 dark:text-gray-400" />
                                <span>Faktur dengan kode 02, 03, 07, 08 dikecualikan dari perhitungan</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <x-heroicon-m-check-circle class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-600 dark:text-gray-400" />
                                <span>Hanya revisi terbaru yang dihitung</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <x-heroicon-m-check-circle class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-600 dark:text-gray-400" />
                                <span>Faktur masuk yang business-related saja</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <x-heroicon-m-check-circle class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-600 dark:text-gray-400" />
                                <span>Peredaran Bruto = DPP + DPP Nilai Lainnya</span>
                            </li>
                        </ul>
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