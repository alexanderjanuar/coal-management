<div class="" x-data="{ activeTab: 'daftar-pajak' }">
    <div class="px-2 py-4 sm:px-4 lg:px-6">

        {{-- Header Section --}}
        <div class="mb-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white sm:text-2xl">Detail Laporan PPh</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Periode {{ \Carbon\Carbon::now()->format('F Y') }} • Client Name
                    </p>
                </div>

                {{-- Quick Status Badge --}}
                <div class="flex items-center gap-3">
                    {{-- Status badge will be dynamic based on PPh calculation --}}
                    <div
                        class="rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 px-5 py-3 shadow-sm ring-1 ring-blue-200 dark:from-blue-500/10 dark:to-blue-600/10 dark:ring-blue-500/20">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500 shadow-lg">
                                <x-filament::icon icon="heroicon-m-document-check" class="h-5 w-5 text-white" />
                            </div>
                            <div>
                                <span class="block text-xs font-medium text-blue-600 dark:text-blue-400">Status
                                    PPh</span>
                                <span class="block text-sm font-bold text-blue-700 dark:text-blue-300">Aktif</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Navigation --}}
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
                            0
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

                    <button @click="activeTab = 'karyawan'"
                        :class="activeTab === 'karyawan' 
                            ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' 
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="inline-flex flex-shrink-0 items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors whitespace-nowrap">
                        <x-filament::icon icon="heroicon-o-arrow-path-rounded-square" class="h-5 w-5" />
                        <span>Karyawan</span>
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
                {{-- COMMENTED: Invoice/Bupot Table Component will be created later --}}
                @livewire('tax-report.pph.pph-tax-list', ['taxReportId' => $taxReportId])
            </div>

            {{-- Kalkulasi Tab --}}
            {{-- Kalkulasi Tab --}}
            <div x-show="activeTab === 'kalkulasi'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-cloak>

                {{-- Section 1: Ringkasan PPh Cards --}}
                <div class="mb-6 grid gap-4 sm:gap-6 md:grid-cols-2 xl:grid-cols-4">

                    {{-- PPh 21 Card --}}
                    <div
                        class="rounded-lg bg-white border border-gray-200 dark:bg-gray-900 dark:border-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">PPh 21</h3>
                                <span
                                    class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    Karyawan
                                </span>
                            </div>
                        </div>
                        <div class="px-5 py-4">
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pajak Terutang</p>
                                    <p class="text-xl font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($pph21Total, 0, ',', '.') }}
                                    </p>
                                </div>
                                <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">DPP</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            Rp {{ number_format($pph21Bruto, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm mt-2">
                                        <span class="text-gray-600 dark:text-gray-400">Bukti Potong</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ $pph21Count }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PPh 23 Card --}}
                    <div
                        class="rounded-lg bg-white border border-gray-200 dark:bg-gray-900 dark:border-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">PPh 23</h3>
                                <span
                                    class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    Jasa/Dividen
                                </span>
                            </div>
                        </div>
                        <div class="px-5 py-4">
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pajak Terutang</p>
                                    <p class="text-xl font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($pph23Total, 0, ',', '.') }}
                                    </p>
                                </div>
                                <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">DPP</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            Rp {{ number_format($pph23Bruto, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm mt-2">
                                        <span class="text-gray-600 dark:text-gray-400">Bukti Potong</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ $pph23Count }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PPh 4(2) Card --}}
                    <div
                        class="rounded-lg bg-white border border-gray-200 dark:bg-gray-900 dark:border-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">PPh 4(2)</h3>
                                <span
                                    class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    Final
                                </span>
                            </div>
                        </div>
                        <div class="px-5 py-4">
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pajak Terutang</p>
                                    <p class="text-xl font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($pph42Total, 0, ',', '.') }}
                                    </p>
                                </div>
                                <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">DPP</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            Rp {{ number_format($pph42Bruto, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm mt-2">
                                        <span class="text-gray-600 dark:text-gray-400">Bukti Potong</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ $pph42Count }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Total PPh Card --}}
                    <div
                        class="rounded-lg bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-300 dark:from-gray-800 dark:to-gray-900 dark:border-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Total PPh</h3>
                                <span
                                    class="inline-flex items-center rounded-md bg-white px-2 py-1 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600">
                                    Keseluruhan
                                </span>
                            </div>
                        </div>
                        <div class="px-5 py-4">
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Total Pajak</p>
                                    <p class="text-xl font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($totalPph, 0, ',', '.') }}
                                    </p>
                                </div>
                                <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Total DPP</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            Rp {{ number_format($pph21Bruto + $pph23Bruto + $pph42Bruto, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm mt-2">
                                        <span class="text-gray-600 dark:text-gray-400">Total Bukti Potong</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ $totalBuktiPotong }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Section 2: Summary Table --}}
                <div
                    class="rounded-lg bg-white border border-gray-200 dark:bg-gray-900 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Rincian Perhitungan</h3>
                            <button wire:click="refreshCalculations"
                                class="inline-flex items-center gap-2 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:hover:bg-gray-700">
                                <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" />
                                Refresh
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Jenis PPh
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        DPP
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Pajak Terutang
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Jumlah
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-800">
                                <tr>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        PPh 21
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                                        Rp {{ number_format($pph21Bruto, 0, ',', '.') }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900 dark:text-white">
                                        Rp {{ number_format($pph21Total, 0, ',', '.') }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">
                                        {{ $pph21Count }}
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        PPh 23
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                                        Rp {{ number_format($pph23Bruto, 0, ',', '.') }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900 dark:text-white">
                                        Rp {{ number_format($pph23Total, 0, ',', '.') }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">
                                        {{ $pph23Count }}
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        PPh 4(2)
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                                        Rp {{ number_format($pph42Bruto, 0, ',', '.') }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900 dark:text-white">
                                        Rp {{ number_format($pph42Total, 0, ',', '.') }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">
                                        {{ $pph42Count }}
                                    </td>
                                </tr>
                                <tr class="bg-gray-50 dark:bg-gray-800">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                        TOTAL
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($pph21Bruto + $pph23Bruto + $pph42Bruto, 0, ',', '.') }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($totalPph, 0, ',', '.') }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-center font-bold text-gray-900 dark:text-white">
                                        {{ $totalBuktiPotong }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Info Note --}}
                <div
                    class="mt-6 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/50">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-300 mb-1">Catatan Perhitungan
                            </h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                Data dihitung berdasarkan bukti potong yang telah diinput. PPh 21 untuk karyawan, PPh 23
                                untuk jasa/dividen, dan PPh 4(2) untuk pajak final.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="activeTab === 'karyawan'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-cloak>
                @if($taxReport && $taxReport->client_id)
                @livewire('tax-report.pph.karyawan-list', [
                'clientId' => $taxReport->client_id,
                'taxReportId' => $taxReportId
                ])
                @else
                <div
                    class="rounded-xl border border-gray-200 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-800">
                    <div class="mx-auto max-w-md">
                        <div
                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                            <x-filament::icon icon="heroicon-o-user-group" class="h-8 w-8 text-gray-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Client Tidak Ditemukan</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Tidak dapat menampilkan data karyawan
                        </p>
                    </div>
                </div>
                @endif
            </div>


            {{-- Yearly Summary Tab --}}
            <div x-show="activeTab === 'yearly-summary'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-cloak>
                {{-- COMMENTED: Yearly Summary Component will be created later --}}
                {{-- @livewire('tax-report.components.pph-yearly-summary', ['taxReportId' => $taxReportId, 'clientId' =>
                $clientId]) --}}

                <div
                    class="rounded-xl border border-gray-200 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-800">
                    <div class="mx-auto max-w-md">
                        <div
                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                            <x-filament::icon icon="heroicon-o-chart-bar-square" class="h-8 w-8 text-gray-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Yearly Summary PPh</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Ringkasan tahunan PPh akan ditampilkan di sini
                        </p>
                    </div>
                </div>
            </div>

            {{-- Catatan Tab --}}
            <div x-show="activeTab === 'catatan'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-cloak>
                <div
                    class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10 sm:p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Catatan PPh</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Catatan untuk perhitungan PPh periode
                            ini</p>
                    </div>

                    {{-- Notes form will be implemented when component is ready --}}
                    <div class="rounded-xl bg-gray-50 p-8 dark:bg-gray-800/50">
                        <div class="flex flex-col items-center justify-center text-center">
                            <div class="rounded-full bg-gray-100 p-3 dark:bg-gray-800">
                                <x-filament::icon icon="heroicon-o-document-text" class="h-6 w-6 text-gray-400" />
                            </div>
                            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                                Fitur catatan akan tersedia setelah komponen diimplementasi
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Riwayat Tab --}}
            <div x-show="activeTab === 'riwayat'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-cloak>
                <div
                    class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10 sm:p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Aktivitas</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Log aktivitas dan perubahan PPh</p>
                    </div>

                    <div class="flex items-center justify-center py-12">
                        <div class="text-center">
                            <div class="mx-auto rounded-full bg-gray-100 p-3 dark:bg-gray-800">
                                <x-filament::icon icon="heroicon-o-clock" class="h-6 w-6 text-gray-400" />
                            </div>
                            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                                Riwayat aktivitas PPh akan muncul di sini
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>