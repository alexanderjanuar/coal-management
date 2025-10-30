<div class="flex gap-6" x-data="{ activeTab: 'daftar-pajak' }">

    {{-- Sidebar Navigation - Modern & Clean Redesign --}}
    <div class="w-72 flex-shrink-0">
        <div
            class="sticky top-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">

            {{-- Sidebar Header --}}
            <div
                class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white p-6 dark:border-gray-700 dark:from-gray-800 dark:to-gray-800">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-600 dark:bg-primary-700">
                        <x-filament::icon icon="heroicon-o-document-chart-bar" class="h-5 w-5 text-white" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Tax Report</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Menu Navigasi</p>
                    </div>
                </div>
            </div>

            {{-- Navigation Menu --}}
            <div class="p-3">
                <nav class="space-y-1">
                    {{-- Daftar Pajak --}}
                    <button @click="activeTab = 'daftar-pajak'" :class="activeTab === 'daftar-pajak' 
                            ? 'bg-primary-600 text-white shadow-md dark:bg-primary-700' 
                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50'"
                        class="group flex w-full items-center justify-between rounded-lg px-4 py-3 text-sm font-medium transition-all duration-200">
                        <div class="flex items-center gap-3">
                            <div :class="activeTab === 'daftar-pajak' ? 'text-white' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300'"
                                class="transition-colors">
                                <x-filament::icon icon="heroicon-o-document-text" class="h-5 w-5" />
                            </div>
                            <span>Daftar Pajak</span>
                        </div>
                        <span :class="activeTab === 'daftar-pajak' 
                                ? 'bg-white text-primary-600 dark:bg-primary-600 dark:text-white' 
                                : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300'"
                            class="rounded-full px-2.5 py-0.5 text-xs font-bold transition-colors">
                            {{ $fakturMasukCount + $fakturKeluarCount }}
                        </span>
                    </button>

                    {{-- Kalkulasi --}}
                    <button @click="activeTab = 'kalkulasi'" :class="activeTab === 'kalkulasi' 
                            ? 'bg-primary-600 text-white shadow-md dark:bg-primary-700' 
                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50'"
                        class="group flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-all duration-200">
                        <div :class="activeTab === 'kalkulasi' ? 'text-white' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300'"
                            class="transition-colors">
                            <x-filament::icon icon="heroicon-o-calculator" class="h-5 w-5" />
                        </div>
                        <span>Kalkulasi</span>
                    </button>

                    {{-- Catatan --}}
                    <button @click="activeTab = 'catatan'" :class="activeTab === 'catatan' 
                            ? 'bg-primary-600 text-white shadow-md dark:bg-primary-700' 
                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50'"
                        class="group flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-all duration-200">
                        <div :class="activeTab === 'catatan' ? 'text-white' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300'"
                            class="transition-colors">
                            <x-filament::icon icon="heroicon-o-pencil-square" class="h-5 w-5" />
                        </div>
                        <span>Catatan</span>
                    </button>

                    {{-- Kompensasi --}}
                    <button @click="activeTab = 'kompensasi'" :class="activeTab === 'kompensasi' 
                            ? 'bg-primary-600 text-white shadow-md dark:bg-primary-700' 
                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50'"
                        class="group flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-all duration-200">
                        <div :class="activeTab === 'kompensasi' ? 'text-white' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300'"
                            class="transition-colors">
                            <x-filament::icon icon="heroicon-o-arrow-path-rounded-square" class="h-5 w-5" />
                        </div>
                        <span>Kompensasi</span>
                    </button>

                    {{-- Riwayat --}}
                    <button @click="activeTab = 'riwayat'" :class="activeTab === 'riwayat' 
                            ? 'bg-primary-600 text-white shadow-md dark:bg-primary-700' 
                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50'"
                        class="group flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-all duration-200">
                        <div :class="activeTab === 'riwayat' ? 'text-white' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300'"
                            class="transition-colors">
                            <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5" />
                        </div>
                        <span>Riwayat</span>
                    </button>
                </nav>
            </div>

            {{-- Sidebar Footer Info --}}
            <div class="border-t border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-100 dark:bg-primary-900/30">
                        <x-filament::icon icon="heroicon-o-information-circle"
                            class="h-4 w-4 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-300">Butuh Bantuan?</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Hubungi support</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Main Content Area --}}
    <div class="flex-1 space-y-4">

        {{-- Daftar Pajak Tab --}}
        <div x-show="activeTab === 'daftar-pajak'" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-x-4"
            x-transition:enter-end="opacity-100 transform translate-x-0" x-cloak>
            {{-- Nested Livewire Component for Invoice Table --}}
            @livewire('tax-report.components.invoice-table', ['taxReportId' => $taxReportId], key('invoice-table-' .$taxReportId))

        </div>

        {{-- Kalkulasi Tab --}}
        <div x-show="activeTab === 'kalkulasi'" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-x-4"
            x-transition:enter-end="opacity-100 transform translate-x-0" x-cloak>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Kalkulasi Pajak</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perhitungan otomatis pajak periode ini</p>
                </div>

                {{-- Calculation Details --}}
                <div class="space-y-4">
                    {{-- PPN Calculation --}}
                    <div
                        class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50">
                        <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Perhitungan PPN</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">PPN Masuk</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    Rp {{ number_format($ppnMasuk, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">PPN Keluar</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    Rp {{ number_format($ppnKeluar, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="border-t border-gray-200 pt-2 dark:border-gray-700">
                                <div class="flex justify-between">
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        @if($ppnKurangBayar > 0) Kurang Bayar @else Lebih Bayar @endif
                                    </span>
                                    <span class="text-lg font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($ppnKurangBayar > 0 ? $ppnKurangBayar : $ppnLebihBayar, 0,
                                        ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Summary Stats --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div
                            class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Faktur Masuk</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $fakturMasukCount }}</p>
                        </div>
                        <div
                            class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Faktur Keluar</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $fakturKeluarCount }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Catatan Tab --}}
        <div x-show="activeTab === 'catatan'" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-x-4"
            x-transition:enter-end="opacity-100 transform translate-x-0" x-cloak>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Catatan</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Catatan penting untuk periode ini</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tambah Catatan Baru
                        </label>
                        <textarea rows="4"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            placeholder="Tulis catatan Anda di sini..."></textarea>
                        <button
                            class="mt-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600">
                            Simpan Catatan
                        </button>
                    </div>

                    <div
                        class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50">
                        <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Catatan Sebelumnya</h4>
                        <div class="space-y-3">
                            <div
                                class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada catatan untuk periode ini.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kompensasi Tab --}}
        <div x-show="activeTab === 'kompensasi'" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-x-4"
            x-transition:enter-end="opacity-100 transform translate-x-0" x-cloak>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Kompensasi</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pengelolaan kompensasi pajak</p>
                </div>

                <div class="space-y-4">
                    <div
                        class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Kompensasi Tersedia
                                </p>
                                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                                    Rp {{ number_format($ppnLebihBayar, 0, ',', '.') }}
                                </p>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-lg border border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-700">
                                <x-filament::icon icon="heroicon-o-banknotes"
                                    class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50">
                        <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Informasi Kompensasi</h4>
                        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <p>• Kompensasi dapat digunakan untuk periode pajak berikutnya</p>
                            <p>• Status kompensasi akan diperbarui setelah pelaporan</p>
                            <p>• Hubungi admin untuk penggunaan kompensasi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Riwayat Tab --}}
        <div x-show="activeTab === 'riwayat'" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-x-4"
            x-transition:enter-end="opacity-100 transform translate-x-0" x-cloak>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Log aktivitas dan perubahan</p>
                </div>

                <div class="space-y-4">
                    <div class="relative border-l-2 border-gray-200 pl-6 dark:border-gray-700">
                        <div class="mb-6">
                            <div
                                class="absolute -left-2 mt-1.5 h-4 w-4 rounded-full border-2 border-white bg-gray-900 dark:border-gray-800 dark:bg-gray-600">
                            </div>
                            <div
                                class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50">
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

</div>