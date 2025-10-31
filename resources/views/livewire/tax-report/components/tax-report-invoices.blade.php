<div class="flex gap-5" x-data="{ activeTab: 'daftar-pajak' }">

    {{-- Sidebar Navigation - Compact --}}
    <div class="w-56 flex-shrink-0">
        <div
            class="sticky top-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">

            {{-- Sidebar Header --}}
            <div class="border-b border-gray-100 p-4 dark:border-gray-800">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600">
                        <x-filament::icon icon="heroicon-o-document-chart-bar" class="h-4 w-4 text-white" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Tax Report</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Navigasi</p>
                    </div>
                </div>
            </div>

            {{-- Navigation Menu --}}
            <div class="p-2">
                <nav class="space-y-0.5">
                    {{-- Daftar Pajak --}}
                    <button @click="activeTab = 'daftar-pajak'" :class="activeTab === 'daftar-pajak' 
                            ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' 
                            : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800'"
                        class="group flex w-full items-center justify-between rounded-lg px-2.5 py-2 text-sm font-medium transition-all">
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-document-text" class="h-4 w-4" />
                            <span>Daftar Pajak</span>
                        </div>
                        <span :class="activeTab === 'daftar-pajak' 
                                ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400' 
                                : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'"
                            class="rounded-md px-1.5 py-0.5 text-xs font-semibold">
                            {{ $fakturMasukCount + $fakturKeluarCount }}
                        </span>
                    </button>

                    {{-- Kalkulasi --}}
                    <button @click="activeTab = 'kalkulasi'" :class="activeTab === 'kalkulasi' 
                            ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' 
                            : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800'"
                        class="group flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-all">
                        <x-filament::icon icon="heroicon-o-calculator" class="h-4 w-4" />
                        <span>Kalkulasi</span>
                    </button>

                    {{-- Catatan --}}
                    <button @click="activeTab = 'catatan'" :class="activeTab === 'catatan' 
                            ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' 
                            : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800'"
                        class="group flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-all">
                        <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                        <span>Catatan</span>
                    </button>

                    {{-- Kompensasi --}}
                    <button @click="activeTab === 'kompensasi'" :class="activeTab === 'kompensasi' 
                            ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' 
                            : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800'"
                        class="group flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-all">
                        <x-filament::icon icon="heroicon-o-arrow-path-rounded-square" class="h-4 w-4" />
                        <span>Kompensasi</span>
                    </button>

                    {{-- Riwayat --}}
                    <button @click="activeTab = 'riwayat'" :class="activeTab === 'riwayat' 
                            ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' 
                            : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800'"
                        class="group flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-all">
                        <x-filament::icon icon="heroicon-o-clock" class="h-4 w-4" />
                        <span>Riwayat</span>
                    </button>
                </nav>
            </div>

        </div>
    </div>

    {{-- Main Content Area --}}
    <div class="flex-1 space-y-4">

        {{-- Daftar Pajak Tab --}}
        <div x-show="activeTab === 'daftar-pajak'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0" x-cloak>
            @livewire('tax-report.components.invoice-table', ['taxReportId' => $taxReportId], key('invoice-table-' .
            $taxReportId))
        </div>

        {{-- Kalkulasi Tab - COMPACT SIDE BY SIDE --}}
        <div x-show="activeTab === 'kalkulasi'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0" x-cloak>

            <div class="space-y-4">

                {{-- Status Header Card - Compact --}}
                <div
                    class="overflow-hidden rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 p-5 shadow dark:from-blue-600 dark:to-blue-700">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-xs font-medium text-blue-100">Status Pajak</p>
                            <h2 class="mt-1 text-2xl font-bold text-white">
                                {{ $statusFinal }}
                            </h2>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-medium text-blue-100">Saldo Final</p>
                            <p class="mt-1 text-2xl font-bold text-white">
                                Rp {{ number_format(abs($saldoFinal), 0, ',', '.') }}
                            </p>
                            @if($saldoFinal > 0)
                            <span
                                class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-medium text-white">
                                <x-filament::icon icon="heroicon-m-arrow-trending-up" class="h-3 w-3" />
                                Kurang Bayar
                            </span>
                            @elseif($saldoFinal < 0) <span
                                class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-medium text-white">
                                <x-filament::icon icon="heroicon-m-arrow-trending-down" class="h-3 w-3" />
                                Lebih Bayar
                                </span>
                                @else
                                <span
                                    class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-medium text-white">
                                    <x-filament::icon icon="heroicon-m-minus" class="h-3 w-3" />
                                    Nihil
                                </span>
                                @endif
                        </div>
                    </div>
                </div>

                {{-- PPN Comparison - COMPACT SIDE BY SIDE --}}
                <div class="grid gap-4 lg:grid-cols-2">

                    {{-- PPN MASUK --}}
                    <div
                        class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                            <div class="flex items-center gap-2.5">
                                <div class="rounded-lg bg-blue-50 p-1.5 dark:bg-blue-500/10">
                                    <x-filament::icon icon="heroicon-o-arrow-down-circle"
                                        class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">PPN Masuk</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Kredit Pajak</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="space-y-2.5">
                                {{-- Jumlah Faktur --}}
                                <div
                                    class="flex items-baseline justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800/50">
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Jumlah
                                        Faktur</span>
                                    <span class="text-base font-bold text-gray-900 dark:text-white">
                                        {{ $fakturMasukCount }}
                                    </span>
                                </div>

                                @if($fakturMasukExcludedCount > 0)
                                <div
                                    class="flex items-baseline justify-between rounded-lg bg-orange-50 px-3 py-2 dark:bg-orange-500/10">
                                    <span
                                        class="text-xs font-medium text-orange-700 dark:text-orange-400">Dikecualikan</span>
                                    <span class="text-base font-bold text-orange-700 dark:text-orange-400">
                                        {{ $fakturMasukExcludedCount }}
                                    </span>
                                </div>
                                @endif

                                {{-- Total PPN Masuk --}}
                                <div class="mt-3 rounded-lg bg-blue-50 px-3 py-2.5 dark:bg-blue-500/10">
                                    <p
                                        class="text-xs font-medium uppercase tracking-wide text-blue-700 dark:text-blue-400">
                                        Total PPN</p>
                                    <p class="mt-1 text-xl font-bold text-blue-900 dark:text-blue-300">
                                        Rp {{ number_format($ppnMasuk, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PPN KELUAR --}}
                    <div
                        class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                            <div class="flex items-center gap-2.5">
                                <div class="rounded-lg bg-blue-50 p-1.5 dark:bg-blue-500/10">
                                    <x-filament::icon icon="heroicon-o-arrow-up-circle"
                                        class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">PPN Keluar</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Pajak Keluaran</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="space-y-2.5">
                                {{-- Jumlah Faktur --}}
                                <div
                                    class="flex items-baseline justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800/50">
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Jumlah
                                        Faktur</span>
                                    <span class="text-base font-bold text-gray-900 dark:text-white">
                                        {{ $fakturKeluarCount }}
                                    </span>
                                </div>

                                @if($fakturKeluarExcludedCount > 0)
                                <div
                                    class="flex items-baseline justify-between rounded-lg bg-orange-50 px-3 py-2 dark:bg-orange-500/10">
                                    <span
                                        class="text-xs font-medium text-orange-700 dark:text-orange-400">Dikecualikan</span>
                                    <span class="text-base font-bold text-orange-700 dark:text-orange-400">
                                        {{ $fakturKeluarExcludedCount }}
                                    </span>
                                </div>
                                @endif

                                {{-- Total PPN Keluar --}}
                                <div class="mt-3 rounded-lg bg-blue-50 px-3 py-2.5 dark:bg-blue-500/10">
                                    <p
                                        class="text-xs font-medium uppercase tracking-wide text-blue-700 dark:text-blue-400">
                                        Total PPN</p>
                                    <p class="mt-1 text-xl font-bold text-blue-900 dark:text-blue-300">
                                        Rp {{ number_format($ppnKeluar, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Selisih & Peredaran - COMPACT --}}
                <div class="grid gap-4 lg:grid-cols-2">

                    {{-- Selisih PPN --}}
                    <div
                        class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="mb-3">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Selisih PPN</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Sebelum kompensasi</p>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-600 dark:text-gray-400">PPN Keluar</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    Rp {{ number_format($ppnKeluar, 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="flex items-center justify-center">
                                <x-filament::icon icon="heroicon-m-minus" class="h-3.5 w-3.5 text-gray-400" />
                            </div>

                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-600 dark:text-gray-400">PPN Masuk</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    Rp {{ number_format($ppnMasuk, 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="border-t-2 border-gray-200 pt-2.5 dark:border-gray-700">
                                <div class="flex items-baseline justify-between">
                                    <span class="text-xs font-semibold text-gray-900 dark:text-white">Selisih</span>
                                    <div class="text-right">
                                        <p class="text-xl font-bold text-blue-600 dark:text-blue-400">
                                            Rp {{ number_format(abs($ppnKeluar - $ppnMasuk), 0, ',', '.') }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            @if($ppnKeluar > $ppnMasuk)
                                            Kurang Bayar
                                            @elseif($ppnKeluar < $ppnMasuk) Lebih Bayar @else Nihil @endif </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Peredaran Bruto --}}
                    <div
                        class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="mb-3 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Peredaran Bruto</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Total omzet</p>
                            </div>
                            <div class="rounded-lg bg-blue-50 p-1.5 dark:bg-blue-500/10">
                                <x-filament::icon icon="heroicon-o-banknotes"
                                    class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div
                                class="flex items-baseline justify-between rounded-lg bg-gray-50 px-2.5 py-1.5 dark:bg-gray-800/50">
                                <span
                                    class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">DPP</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">
                                    Rp {{ number_format($totalDpp, 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="flex items-center justify-center">
                                <x-filament::icon icon="heroicon-m-plus" class="h-3.5 w-3.5 text-gray-400" />
                            </div>

                            <div
                                class="flex items-baseline justify-between rounded-lg bg-gray-50 px-2.5 py-1.5 dark:bg-gray-800/50">
                                <span
                                    class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">DPP
                                    NL</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">
                                    Rp {{ number_format($totalDppNilaiLainnya, 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="border-t-2 border-gray-200 pt-2.5 dark:border-gray-700">
                                <div class="flex items-baseline justify-between">
                                    <span class="text-xs font-semibold text-gray-900 dark:text-white">Total</span>
                                    <p class="text-xl font-bold text-blue-600 dark:text-blue-400">
                                        Rp {{ number_format($peredaranBruto, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Kompensasi - COMPACT (jika ada) --}}
                @if($kompensasiDiterima > 0 || $kompensasiTersedia > 0 || $kompensasiTerpakai > 0)
                <div
                    class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="mb-3">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Detail Kompensasi</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Informasi kompensasi pajak</p>
                    </div>

                    <div class="grid gap-3 md:grid-cols-3">
                        @if($kompensasiDiterima > 0)
                        <div class="rounded-lg bg-blue-50 p-3 dark:bg-blue-500/10">
                            <div class="mb-1.5 flex items-center gap-1.5">
                                <x-filament::icon icon="heroicon-o-arrow-down-tray"
                                    class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" />
                                <span
                                    class="text-xs font-medium uppercase tracking-wide text-blue-900 dark:text-blue-300">Diterima</span>
                            </div>
                            <p class="text-lg font-bold text-blue-900 dark:text-blue-200">
                                Rp {{ number_format($kompensasiDiterima, 0, ',', '.') }}
                            </p>
                        </div>
                        @endif

                        @if($kompensasiTersedia > 0)
                        <div class="rounded-lg bg-blue-50 p-3 dark:bg-blue-500/10">
                            <div class="mb-1.5 flex items-center gap-1.5">
                                <x-filament::icon icon="heroicon-o-archive-box"
                                    class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" />
                                <span
                                    class="text-xs font-medium uppercase tracking-wide text-blue-900 dark:text-blue-300">Tersedia</span>
                            </div>
                            <p class="text-lg font-bold text-blue-900 dark:text-blue-200">
                                Rp {{ number_format($kompensasiTersedia, 0, ',', '.') }}
                            </p>
                        </div>
                        @endif

                        @if($kompensasiTerpakai > 0)
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
                            <div class="mb-1.5 flex items-center gap-1.5">
                                <x-filament::icon icon="heroicon-o-check-circle"
                                    class="h-3.5 w-3.5 text-gray-600 dark:text-gray-400" />
                                <span
                                    class="text-xs font-medium uppercase tracking-wide text-gray-600 dark:text-gray-400">Terpakai</span>
                            </div>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">
                                Rp {{ number_format($kompensasiTerpakai, 0, ',', '.') }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Info Box - COMPACT --}}
                <div class="rounded-xl bg-blue-50 p-3.5 dark:bg-blue-500/10">
                    <div class="flex gap-2.5">
                        <div class="flex-shrink-0">
                            <div class="rounded-lg bg-blue-100 p-1.5 dark:bg-blue-500/20">
                                <x-filament::icon icon="heroicon-o-information-circle"
                                    class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xs font-semibold text-blue-900 dark:text-blue-200">Aturan Kalkulasi</h4>
                            <ul class="mt-1.5 grid gap-1 text-xs text-blue-800 dark:text-blue-300 sm:grid-cols-2">
                                <li class="flex items-start gap-1.5">
                                    <span class="mt-0.5 text-blue-600 dark:text-blue-400">•</span>
                                    <span>Faktur 02,03,07,08 dikecualikan</span>
                                </li>
                                <li class="flex items-start gap-1.5">
                                    <span class="mt-0.5 text-blue-600 dark:text-blue-400">•</span>
                                    <span>Hitung revisi terbaru saja</span>
                                </li>
                                <li class="flex items-start gap-1.5">
                                    <span class="mt-0.5 text-blue-600 dark:text-blue-400">•</span>
                                    <span>Masuk yang business-related</span>
                                </li>
                                <li class="flex items-start gap-1.5">
                                    <span class="mt-0.5 text-blue-600 dark:text-blue-400">•</span>
                                    <span>Bruto = DPP + DPP NL</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Catatan Tab --}}
        <div x-show="activeTab === 'catatan'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0" x-cloak>

            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Catatan</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Catatan untuk periode ini</p>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Tambah Catatan
                        </label>
                        <textarea rows="3"
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            placeholder="Tulis catatan..."></textarea>
                        <button
                            class="mt-2 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                            Simpan
                        </button>
                    </div>

                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
                        <h4 class="mb-2 text-xs font-semibold text-gray-900 dark:text-white">Catatan Sebelumnya</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Belum ada catatan.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kompensasi Tab --}}
        <div x-show="activeTab === 'kompensasi'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0" x-cloak>

            <div class="space-y-4">
                <div
                    class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Kompensasi Pajak</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pengelolaan kompensasi</p>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-500/10">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-xs font-medium text-blue-900 dark:text-blue-200">Tersedia</span>
                                <div class="rounded-lg bg-blue-100 p-1.5 dark:bg-blue-500/20">
                                    <x-filament::icon icon="heroicon-o-banknotes"
                                        class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" />
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                                Rp {{ number_format($kompensasiTersedia, 0, ',', '.') }}
                            </p>
                        </div>

                        @if($kompensasiDiterima > 0)
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-900 dark:text-white">Diterima</span>
                                <div class="rounded-lg bg-gray-200 p-1.5 dark:bg-gray-700">
                                    <x-filament::icon icon="heroicon-o-arrow-down-tray"
                                        class="h-3.5 w-3.5 text-gray-600 dark:text-gray-400" />
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                Rp {{ number_format($kompensasiDiterima, 0, ',', '.') }}
                            </p>
                        </div>
                        @endif
                    </div>

                    <div class="mt-4 rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
                        <h4 class="mb-2 text-xs font-semibold text-gray-900 dark:text-white">Informasi</h4>
                        <ul class="space-y-1.5 text-xs text-gray-600 dark:text-gray-400">
                            <li class="flex items-start gap-1.5">
                                <span class="mt-0.5 text-blue-600">•</span>
                                <span>Dapat digunakan periode berikutnya</span>
                            </li>
                            <li class="flex items-start gap-1.5">
                                <span class="mt-0.5 text-blue-600">•</span>
                                <span>Update setelah pelaporan</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Riwayat Tab --}}
        <div x-show="activeTab === 'riwayat'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0" x-cloak>

            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Riwayat</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Log aktivitas</p>
                </div>

                <div class="relative border-l-2 border-gray-200 pl-4 dark:border-gray-700">
                    <div class="mb-4">
                        <div
                            class="absolute -left-2 mt-1 h-4 w-4 rounded-full border-2 border-white bg-blue-600 dark:border-gray-900">
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-900 dark:text-white">
                                    Laporan Dibuat
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    Baru saja
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                Tax report telah dibuat
                            </p>
                        </div>
                    </div>

                    <div class="text-center">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Riwayat lebih lanjut akan muncul di sini
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>