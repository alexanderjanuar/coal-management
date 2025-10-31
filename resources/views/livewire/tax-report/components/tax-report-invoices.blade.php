<div class="flex gap-6" x-data="{ activeTab: 'daftar-pajak' }">

    {{-- Sidebar Navigation - Minimalist & Elegant --}}
    <div class="w-64 flex-shrink-0">
        <div
            class="sticky top-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">

            {{-- Sidebar Header --}}
            <div class="border-b border-gray-100 p-6 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600">
                        <x-filament::icon icon="heroicon-o-document-chart-bar" class="h-5 w-5 text-white" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Tax Report</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Navigasi</p>
                    </div>
                </div>
            </div>

            {{-- Navigation Menu --}}
            <div class="p-3">
                <nav class="space-y-1">
                    {{-- Daftar Pajak --}}
                    <button @click="activeTab = 'daftar-pajak'" :class="activeTab === 'daftar-pajak' 
                            ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' 
                            : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800'"
                        class="group flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium transition-all">
                        <div class="flex items-center gap-2.5">
                            <x-filament::icon icon="heroicon-o-document-text" class="h-4 w-4" />
                            <span>Daftar Pajak</span>
                        </div>
                        <span :class="activeTab === 'daftar-pajak' 
                                ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400' 
                                : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'"
                            class="rounded-lg px-2 py-0.5 text-xs font-semibold">
                            {{ $fakturMasukCount + $fakturKeluarCount }}
                        </span>
                    </button>

                    {{-- Kalkulasi --}}
                    <button @click="activeTab = 'kalkulasi'" :class="activeTab === 'kalkulasi' 
                            ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' 
                            : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800'"
                        class="group flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium transition-all">
                        <x-filament::icon icon="heroicon-o-calculator" class="h-4 w-4" />
                        <span>Kalkulasi</span>
                    </button>

                    {{-- Catatan --}}
                    <button @click="activeTab = 'catatan'" :class="activeTab === 'catatan' 
                            ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' 
                            : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800'"
                        class="group flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium transition-all">
                        <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                        <span>Catatan</span>
                    </button>

                    {{-- Kompensasi --}}
                    <button @click="activeTab = 'kompensasi'" :class="activeTab === 'kompensasi' 
                            ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' 
                            : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800'"
                        class="group flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium transition-all">
                        <x-filament::icon icon="heroicon-o-arrow-path-rounded-square" class="h-4 w-4" />
                        <span>Kompensasi</span>
                    </button>

                    {{-- Riwayat --}}
                    <button @click="activeTab = 'riwayat'" :class="activeTab === 'riwayat' 
                            ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' 
                            : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800'"
                        class="group flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium transition-all">
                        <x-filament::icon icon="heroicon-o-clock" class="h-4 w-4" />
                        <span>Riwayat</span>
                    </button>
                </nav>
            </div>

        </div>
    </div>

    {{-- Main Content Area --}}
    <div class="flex-1 space-y-6">

        {{-- Daftar Pajak Tab --}}
        <div x-show="activeTab === 'daftar-pajak'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0" x-cloak>
            @livewire('tax-report.components.invoice-table', ['taxReportId' => $taxReportId], key('invoice-table-' .
            $taxReportId))
        </div>

        {{-- Kalkulasi Tab - ELEGANT & MINIMALIST --}}
        <div x-show="activeTab === 'kalkulasi'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0" x-cloak>

            <div class="space-y-6">

                {{-- Status Header Card --}}
                <div
                    class="overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 p-8 shadow-lg dark:from-blue-600 dark:to-blue-700">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-blue-100">Status Pajak</p>
                            <h2 class="mt-2 text-4xl font-bold text-white">
                                {{ $statusFinal }}
                            </h2>
                            <p class="mt-1 text-sm text-blue-100">
                                Hasil perhitungan setelah kompensasi
                            </p>
                        </div>
                        <div class="flex flex-col items-end">
                            <p class="text-xs font-medium text-blue-100">Saldo Final</p>
                            <p class="mt-1 text-3xl font-bold text-white">
                                Rp {{ number_format(abs($saldoFinal), 0, ',', '.') }}
                            </p>
                            @if($saldoFinal > 0)
                            <span
                                class="mt-2 inline-flex items-center gap-1 rounded-full bg-white/20 px-3 py-1 text-xs font-medium text-white">
                                <x-filament::icon icon="heroicon-m-arrow-trending-up" class="h-3 w-3" />
                                Harus dibayar
                            </span>
                            @elseif($saldoFinal < 0) <span
                                class="mt-2 inline-flex items-center gap-1 rounded-full bg-white/20 px-3 py-1 text-xs font-medium text-white">
                                <x-filament::icon icon="heroicon-m-arrow-trending-down" class="h-3 w-3" />
                                Dapat dikompensasi
                                </span>
                                @else
                                <span
                                    class="mt-2 inline-flex items-center gap-1 rounded-full bg-white/20 px-3 py-1 text-xs font-medium text-white">
                                    <x-filament::icon icon="heroicon-m-minus" class="h-3 w-3" />
                                    Nihil
                                </span>
                                @endif
                        </div>
                    </div>
                </div>

                {{-- Main Grid --}}
                <div class="grid gap-6 lg:grid-cols-2">

                    {{-- Left Column --}}
                    <div class="space-y-6">

                        {{-- Peredaran Bruto Card --}}
                        <div
                            class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Peredaran Bruto</h3>
                                <div class="rounded-lg bg-blue-50 p-2 dark:bg-blue-500/10">
                                    <x-filament::icon icon="heroicon-o-banknotes"
                                        class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                </div>
                            </div>

                            {{-- Total DPP --}}
                            <div class="mb-3 rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                                <div class="flex items-baseline justify-between">
                                    <span
                                        class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total
                                        DPP</span>
                                    <span class="text-lg font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($totalDpp, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            {{-- Total DPP Nilai Lainnya --}}
                            <div class="mb-4 rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                                <div class="flex items-baseline justify-between">
                                    <span
                                        class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">DPP
                                        Nilai Lainnya</span>
                                    <span class="text-lg font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($totalDppNilaiLainnya, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            {{-- Grand Total --}}
                            <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                                <div class="flex items-baseline justify-between">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Total
                                        Peredaran</span>
                                    <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                        Rp {{ number_format($peredaranBruto, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- PPN Masuk Card --}}
                        <div
                            class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                            <div class="mb-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="rounded-lg bg-blue-50 p-2 dark:bg-blue-500/10">
                                        <x-filament::icon icon="heroicon-o-arrow-down-circle"
                                            class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">PPN Masuk</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Kredit Pajak</p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-baseline justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Jumlah Faktur</span>
                                    <span class="text-base font-semibold text-gray-900 dark:text-white">
                                        {{ $fakturMasukCount }} faktur
                                    </span>
                                </div>

                                @if($fakturMasukExcludedCount > 0)
                                <div class="flex items-baseline justify-between">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Dikecualikan</span>
                                    <span class="text-sm font-medium text-gray-400 dark:text-gray-500">
                                        {{ $fakturMasukExcludedCount }} faktur
                                    </span>
                                </div>
                                @endif

                                <div class="border-t border-gray-200 pt-3 dark:border-gray-700">
                                    <div class="flex items-baseline justify-between">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Total
                                            PPN</span>
                                        <span class="text-2xl font-bold text-gray-900 dark:text-white">
                                            Rp {{ number_format($ppnMasuk, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Right Column --}}
                    <div class="space-y-6">

                        {{-- PPN Keluar Card --}}
                        <div
                            class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                            <div class="mb-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="rounded-lg bg-blue-50 p-2 dark:bg-blue-500/10">
                                        <x-filament::icon icon="heroicon-o-arrow-up-circle"
                                            class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">PPN Keluar
                                        </h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Pajak Keluaran</p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-baseline justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Jumlah Faktur</span>
                                    <span class="text-base font-semibold text-gray-900 dark:text-white">
                                        {{ $fakturKeluarCount }} faktur
                                    </span>
                                </div>

                                @if($fakturKeluarExcludedCount > 0)
                                <div class="flex items-baseline justify-between">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Dikecualikan</span>
                                    <span class="text-sm font-medium text-gray-400 dark:text-gray-500">
                                        {{ $fakturKeluarExcludedCount }} faktur
                                    </span>
                                </div>
                                @endif

                                <div class="border-t border-gray-200 pt-3 dark:border-gray-700">
                                    <div class="flex items-baseline justify-between">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Total
                                            PPN</span>
                                        <span class="text-2xl font-bold text-gray-900 dark:text-white">
                                            Rp {{ number_format($ppnKeluar, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Selisih Card --}}
                        <div
                            class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                            <div class="mb-4">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Selisih PPN</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Sebelum kompensasi</p>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-baseline justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">PPN Keluar</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        Rp {{ number_format($ppnKeluar, 0, ',', '.') }}
                                    </span>
                                </div>

                                <div class="flex items-baseline justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">PPN Masuk</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        Rp {{ number_format($ppnMasuk, 0, ',', '.') }}
                                    </span>
                                </div>

                                <div class="border-t-2 border-gray-200 pt-3 dark:border-gray-700">
                                    <div class="flex items-baseline justify-between">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Selisih</span>
                                        <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                            Rp {{ number_format(abs($ppnKeluar - $ppnMasuk), 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-right text-xs text-gray-500 dark:text-gray-400">
                                        @if($ppnKeluar > $ppnMasuk)
                                        Kurang Bayar
                                        @elseif($ppnKeluar < $ppnMasuk) Lebih Bayar @else Nihil @endif </p>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                {{-- Kompensasi Section (jika ada) --}}
                @if($kompensasiDiterima > 0 || $kompensasiTersedia > 0 || $kompensasiTerpakai > 0)
                <div
                    class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="mb-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Detail Kompensasi</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Informasi kompensasi pajak</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        @if($kompensasiDiterima > 0)
                        <div class="rounded-xl bg-blue-50 p-4 dark:bg-blue-500/10">
                            <div class="mb-2 flex items-center gap-2">
                                <x-filament::icon icon="heroicon-o-arrow-left-circle"
                                    class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                <span
                                    class="text-xs font-medium uppercase tracking-wide text-blue-900 dark:text-blue-300">Diterima</span>
                            </div>
                            <p class="text-xl font-bold text-blue-900 dark:text-blue-200">
                                Rp {{ number_format($kompensasiDiterima, 0, ',', '.') }}
                            </p>
                        </div>
                        @endif

                        @if($kompensasiTersedia > 0)
                        <div class="rounded-xl bg-blue-50 p-4 dark:bg-blue-500/10">
                            <div class="mb-2 flex items-center gap-2">
                                <x-filament::icon icon="heroicon-o-archive-box"
                                    class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                <span
                                    class="text-xs font-medium uppercase tracking-wide text-blue-900 dark:text-blue-300">Tersedia</span>
                            </div>
                            <p class="text-xl font-bold text-blue-900 dark:text-blue-200">
                                Rp {{ number_format($kompensasiTersedia, 0, ',', '.') }}
                            </p>
                        </div>
                        @endif

                        @if($kompensasiTerpakai > 0)
                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                            <div class="mb-2 flex items-center gap-2">
                                <x-filament::icon icon="heroicon-o-check-circle"
                                    class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                                <span
                                    class="text-xs font-medium uppercase tracking-wide text-gray-600 dark:text-gray-400">Terpakai</span>
                            </div>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">
                                Rp {{ number_format($kompensasiTerpakai, 0, ',', '.') }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Info Box --}}
                <div class="rounded-2xl bg-blue-50 p-5 dark:bg-blue-500/10">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0">
                            <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-500/20">
                                <x-filament::icon icon="heroicon-o-information-circle"
                                    class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-200">Aturan Kalkulasi</h4>
                            <ul class="mt-2 space-y-1 text-xs text-blue-800 dark:text-blue-300">
                                <li class="flex items-start gap-2">
                                    <span class="mt-0.5 text-blue-600 dark:text-blue-400">•</span>
                                    <span>Faktur Keluar dikecualikan: Nomor awalan 02, 03, 07, 08</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="mt-0.5 text-blue-600 dark:text-blue-400">•</span>
                                    <span>Hanya menghitung revisi faktur terbaru</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="mt-0.5 text-blue-600 dark:text-blue-400">•</span>
                                    <span>Faktur Masuk hanya yang terkait bisnis utama</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="mt-0.5 text-blue-600 dark:text-blue-400">•</span>
                                    <span>Peredaran Bruto: DPP + DPP Nilai Lainnya</span>
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

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="mb-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Catatan</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Catatan penting untuk periode ini</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tambah Catatan Baru
                        </label>
                        <textarea rows="4"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            placeholder="Tulis catatan Anda di sini..."></textarea>
                        <button
                            class="mt-3 rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                            Simpan Catatan
                        </button>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                        <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Catatan Sebelumnya</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada catatan untuk periode ini.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kompensasi Tab --}}
        <div x-show="activeTab === 'kompensasi'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0" x-cloak>

            <div class="space-y-6">
                <div
                    class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="mb-6">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Kompensasi Pajak</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Pengelolaan kompensasi pajak</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl bg-blue-50 p-6 dark:bg-blue-500/10">
                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-sm font-medium text-blue-900 dark:text-blue-200">Kompensasi
                                    Tersedia</span>
                                <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-500/20">
                                    <x-filament::icon icon="heroicon-o-banknotes"
                                        class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                </div>
                            </div>
                            <p class="text-3xl font-bold text-blue-900 dark:text-blue-100">
                                Rp {{ number_format($kompensasiTersedia, 0, ',', '.') }}
                            </p>
                        </div>

                        @if($kompensasiDiterima > 0)
                        <div class="rounded-xl bg-gray-50 p-6 dark:bg-gray-800/50">
                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Kompensasi
                                    Diterima</span>
                                <div class="rounded-lg bg-gray-200 p-2 dark:bg-gray-700">
                                    <x-filament::icon icon="heroicon-o-arrow-down-tray"
                                        class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                                </div>
                            </div>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">
                                Rp {{ number_format($kompensasiDiterima, 0, ',', '.') }}
                            </p>
                        </div>
                        @endif
                    </div>

                    <div class="mt-6 rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                        <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Informasi</h4>
                        <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5 text-blue-600">•</span>
                                <span>Kompensasi dapat digunakan untuk periode pajak berikutnya</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5 text-blue-600">•</span>
                                <span>Status kompensasi diperbarui setelah pelaporan</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5 text-blue-600">•</span>
                                <span>Hubungi admin untuk penggunaan kompensasi</span>
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

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="mb-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Riwayat</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Log aktivitas dan perubahan</p>
                </div>

                <div class="relative border-l-2 border-gray-200 pl-6 dark:border-gray-700">
                    <div class="mb-6">
                        <div
                            class="absolute -left-2.5 mt-1.5 h-5 w-5 rounded-full border-2 border-white bg-blue-600 dark:border-gray-900">
                        </div>
                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                    Laporan Dibuat
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    Baru saja
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Tax report untuk periode ini telah dibuat
                            </p>
                        </div>
                    </div>

                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Riwayat lebih lanjut akan muncul di sini
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>