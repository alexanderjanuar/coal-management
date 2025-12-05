<div class="space-y-6" x-data="{ 
        activeTab: 'invoices',
        selectedMonth: @entangle('selectedMonth'),
        selectedClient: @entangle('selectedClient'),
        showMobileMenu: false,
        isMobile: window.innerWidth < 768,
        mounted: false
    }" x-init="
        mounted = true;
        window.addEventListener('resize', () => {
            isMobile = window.innerWidth < 768;
        });
    ">

    {{-- Loading Indicator --}}
    <div wire:loading wire:target="selectMonth,selectClient"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/20 backdrop-blur-sm">
        <div class="mx-4 flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-2xl dark:bg-gray-900">
            <svg class="h-6 w-6 animate-spin text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span class="text-sm font-semibold text-gray-900 dark:text-white">Loading...</span>
        </div>
    </div>

    @if($clients->isEmpty())
    {{-- Empty State --}}
    <div
        class="flex flex-col items-center justify-center rounded-2xl border border-gray-200 bg-gradient-to-br from-gray-50 to-gray-100 p-12 dark:border-gray-700 dark:from-gray-800 dark:to-gray-900">
        <div class="rounded-full bg-white p-4 shadow-sm dark:bg-gray-800">
            <x-heroicon-o-document-chart-bar class="h-16 w-16 text-gray-400" />
        </div>
        <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-white">Tidak Ada Data Laporan Pajak</h3>
        <p class="mt-2 max-w-md text-center text-sm text-gray-600 dark:text-gray-400">
            Anda belum memiliki akses ke laporan pajak manapun. Hubungi administrator untuk informasi lebih lanjut.
        </p>
    </div>
    @else

    {{-- Client Selector --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
        x-show="mounted" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0">
        <div class="p-5">
            <div
                class="mb-3 flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                <x-heroicon-o-building-office-2 class="h-4 w-4" />
                <span>Pilih Perusahaan</span>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($clients as $client)
                <button wire:click="selectClient({{ $client->id }})" class="group relative overflow-hidden rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                        {{ $selectedClient === $client->id 
                            ? 'bg-primary-600 text-white shadow-lg shadow-primary-500/30 hover:bg-primary-700 dark:bg-primary-500 dark:shadow-primary-400/20' 
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' 
                        }}">
                    {{ $client->name }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

    @if($selectedClient && $currentTaxReport)
    <div class="space-y-6" x-show="mounted" x-transition:enter="transition ease-out duration-300 delay-100"
        x-transition:enter-start="opacity-0 transform translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0">

        {{-- Client Header --}}
        <div
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-col gap-3 p-4 md:flex-row md:items-center md:justify-between md:px-6 md:py-5">
                {{-- Client Info --}}
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 md:h-12 md:w-12 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 shadow-lg">
                        <x-heroicon-o-building-office-2 class="h-5 w-5 md:h-6 md:w-6 text-white" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="truncate text-lg md:text-xl font-bold text-gray-900 dark:text-white">
                            {{ $currentClient->name }}
                        </h1>
                        <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">
                            Tax Report • {{ $currentTaxReport->created_at->format('Y') }}
                        </p>
                    </div>
                </div>

                {{-- Quick Stats --}}
                <div class="flex flex-wrap items-center gap-3 md:gap-4">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                            <x-heroicon-o-document-text class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">PPN</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{
                                $currentTaxReport->invoices->count() }}</p>
                        </div>
                    </div>

                    <div class="hidden md:block h-8 w-px bg-gray-200 dark:bg-gray-700"></div>

                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/30">
                            <x-heroicon-o-banknotes class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">PPh</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{
                                $currentTaxReport->incomeTaxs->count() }}</p>
                        </div>
                    </div>

                    <div class="hidden md:block h-8 w-px bg-gray-200 dark:bg-gray-700"></div>

                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-50 dark:bg-orange-900/30">
                            <x-heroicon-o-receipt-percent class="h-4 w-4 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Bupot</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{
                                $currentTaxReport->bupots->count() }}</p>
                        </div>
                    </div>

                    <div class="hidden md:block h-8 w-px bg-gray-200 dark:bg-gray-700"></div>

                    {{-- Reporting Status --}}
                    @php
                    $summaries = $currentTaxReport->taxCalculationSummaries;
                    $ppnSummary = $summaries->firstWhere('tax_type', 'ppn');
                    $pphSummary = $summaries->firstWhere('tax_type', 'pph');
                    $bupotSummary = $summaries->firstWhere('tax_type', 'bupot');

                    $ppnReported = $ppnSummary && $ppnSummary->report_status === 'Sudah Lapor';
                    $pphReported = $pphSummary && $pphSummary->report_status === 'Sudah Lapor';
                    $bupotReported = $bupotSummary && $bupotSummary->report_status === 'Sudah Lapor';

                    $reportedCount = ($ppnReported ? 1 : 0) + ($pphReported ? 1 : 0) + ($bupotReported ? 1 : 0);
                    $allReported = $reportedCount === 3;
                    @endphp

                    <div
                        class="inline-flex items-center gap-2 rounded-full {{ $allReported ? 'bg-green-50 dark:bg-green-900/20' : ($reportedCount > 0 ? 'bg-yellow-50 dark:bg-yellow-900/20' : 'bg-orange-50 dark:bg-orange-900/20') }} px-3 py-1.5">
                        <span class="relative flex h-2 w-2">
                            @if($allReported)
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-green-500"></span>
                            @elseif($reportedCount > 0)
                            <span
                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-yellow-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-yellow-500"></span>
                            @else
                            <span
                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-orange-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-orange-500"></span>
                            @endif
                        </span>
                        <span
                            class="text-xs font-semibold {{ $allReported ? 'text-green-700 dark:text-green-400' : ($reportedCount > 0 ? 'text-yellow-700 dark:text-yellow-400' : 'text-orange-700 dark:text-orange-400') }}">
                            {{ $allReported ? 'Semua Sudah Lapor' : ($reportedCount > 0 ? "{$reportedCount}/3 Sudah
                            Lapor" : 'Belum Lapor') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 12-Month Navigation --}}
        <div
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="p-3 md:p-4">
                @php
                $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September',
                'October', 'November', 'December'];
                $currentYear = $currentTaxReport->created_at->format('Y');

                $yearReports = \App\Models\TaxReport::where('client_id', $currentClient->id)
                ->whereYear('created_at', $currentYear)
                ->with(['taxCalculationSummaries' => function($query) {
                $query->select('id', 'tax_report_id', 'tax_type', 'report_status');
                }])
                ->withCount(['invoices', 'incomeTaxs', 'bupots'])
                ->select('id', 'client_id', 'month', 'created_at')
                ->get()
                ->keyBy('month');
                @endphp

                <div class="overflow-x-auto">
                    <div class="flex gap-2 md:grid md:grid-cols-12 md:gap-2" style="min-width: max-content;">
                        @foreach($months as $index => $monthName)
                        @php
                        $monthReport = $yearReports->get($monthName);
                        $hasReport = !is_null($monthReport);
                        $isCurrent = $monthName === $currentTaxReport->month;

                        $hasActivity = $hasReport && (
                        ($monthReport->invoices_count ?? 0) > 0 ||
                        ($monthReport->income_taxs_count ?? 0) > 0 ||
                        ($monthReport->bupots_count ?? 0) > 0
                        );
                        @endphp

                        @if($hasReport)
                        @php
                        $monthSummaries = $monthReport->taxCalculationSummaries ?? collect();
                        $ppnSum = $monthSummaries->firstWhere('tax_type', 'ppn');
                        $pphSum = $monthSummaries->firstWhere('tax_type', 'pph');
                        $bupotSum = $monthSummaries->firstWhere('tax_type', 'bupot');

                        $ppnRep = $ppnSum && $ppnSum->report_status === 'Sudah Lapor';
                        $pphRep = $pphSum && $pphSum->report_status === 'Sudah Lapor';
                        $bupotRep = $bupotSum && $bupotSum->report_status === 'Sudah Lapor';

                        $monthIsReported = $ppnRep && $pphRep && $bupotRep;
                        @endphp
                        <button wire:click="selectMonth('{{ $monthName }}')"
                            class="group relative flex flex-col items-center justify-center overflow-hidden rounded-lg border-2 px-3 py-3 transition-all duration-200 flex-shrink-0 w-20 md:w-auto {{ $isCurrent ? 'border-primary-500 bg-primary-50 shadow-sm dark:border-primary-400 dark:bg-primary-900/20' : 'border-gray-200 bg-white hover:border-primary-300 hover:bg-primary-50/50 dark:border-gray-700 dark:bg-gray-800/50' }}">

                            {{-- Reporting Status Indicator --}}
                            <div class="absolute left-1 top-1 z-10">
                                @if($monthIsReported)
                                <div
                                    class="flex h-4 w-4 items-center justify-center rounded-full bg-green-500 shadow-sm">
                                    <x-heroicon-m-check class="h-2.5 w-2.5 text-white" />
                                </div>
                                @else
                                <div
                                    class="flex h-4 w-4 items-center justify-center rounded-full bg-orange-500 shadow-sm">
                                    <x-heroicon-m-exclamation-triangle class="h-2.5 w-2.5 text-white" />
                                </div>
                                @endif
                            </div>

                            {{-- Activity Indicator --}}
                            @if($hasActivity)
                            <div class="absolute right-1 top-1 z-10">
                                <span class="flex h-2 w-2">
                                    <span
                                        class="absolute inline-flex h-full w-full animate-ping rounded-full {{ $isCurrent ? 'bg-primary-400' : 'bg-green-400' }} opacity-75"></span>
                                    <span
                                        class="relative inline-flex h-2 w-2 rounded-full {{ $isCurrent ? 'bg-primary-500' : 'bg-green-500' }}"></span>
                                </span>
                            </div>
                            @endif

                            <span
                                class="relative z-10 text-[10px] font-semibold uppercase tracking-wider {{ $isCurrent ? 'text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400' }}">
                                {{ substr($monthName, 0, 3) }}
                            </span>
                            <span
                                class="relative z-10 mt-0.5 text-sm font-bold {{ $isCurrent ? 'text-primary-900 dark:text-primary-100' : 'text-gray-900 dark:text-white' }}">
                                {{ $index + 1 }}
                            </span>
                        </button>
                        @else
                        <div
                            class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 px-3 py-3 opacity-50 dark:border-gray-800 dark:bg-gray-900/20 flex-shrink-0 w-20 md:w-auto">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">
                                {{ substr($monthName, 0, 3) }}
                            </span>
                            <span class="mt-0.5 text-sm font-bold text-gray-400">
                                {{ $index + 1 }}
                            </span>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>

                {{-- Legend --}}
                <div class="mt-3 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-xs">
                    <div class="flex items-center gap-1.5">
                        <div class="h-2 w-2 rounded-full bg-primary-500"></div>
                        <span class="text-gray-600 dark:text-gray-400">Bulan Aktif</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="h-2 w-2 rounded-full bg-green-500"></div>
                        <span class="text-gray-600 dark:text-gray-400">Sudah Lapor</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="h-2 w-2 rounded-full bg-orange-500"></div>
                        <span class="text-gray-600 dark:text-gray-400">Belum Lapor</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tax Type Navigation Tabs --}}
        <div
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="p-3">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    {{-- PPN Tab --}}
                    <button @click="activeTab = 'invoices'"
                        :class="activeTab === 'invoices' 
                                ? 'border-blue-500 bg-gradient-to-br from-blue-50 to-blue-100 shadow-lg dark:border-blue-400 dark:from-blue-900/30 dark:to-blue-900/10' 
                                : 'border-gray-200 bg-white hover:border-blue-300 dark:border-gray-700 dark:bg-gray-800/50'"
                        class="group relative flex flex-row md:flex-col items-center gap-3 rounded-xl border-2 px-4 py-3 transition-all duration-200">

                        {{-- Status Badge --}}
                        <div class="absolute -right-2 -top-2 z-10">
                            @if($ppnReported)
                            <div
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-green-500 shadow-lg ring-2 ring-white dark:ring-gray-900">
                                <x-heroicon-m-check-circle class="h-4 w-4 text-white" />
                            </div>
                            @else
                            <div
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-orange-500 shadow-lg ring-2 ring-white dark:ring-gray-900">
                                <x-heroicon-m-exclamation-circle class="h-4 w-4 text-white" />
                            </div>
                            @endif
                        </div>

                        <div :class="activeTab === 'invoices' ? 'bg-blue-600 shadow-lg' : 'bg-gray-100 dark:bg-gray-700'"
                            class="flex h-12 w-12 items-center justify-center rounded-xl transition-all flex-shrink-0">
                            <x-heroicon-o-document-text
                                :class="activeTab === 'invoices' ? 'text-white' : 'text-gray-600 dark:text-gray-400'"
                                class="h-6 w-6" />
                        </div>

                        <div class="flex-1 text-left md:text-center">
                            <p :class="activeTab === 'invoices' ? 'text-blue-900 dark:text-blue-100' : 'text-gray-900 dark:text-white'"
                                class="text-sm font-bold">PPN</p>
                            <p :class="activeTab === 'invoices' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400'"
                                class="mt-0.5 text-xs">{{ $currentTaxReport->invoices->count() }} Faktur</p>
                            <p
                                class="mt-1 text-[10px] font-semibold {{ $ppnReported ? 'text-green-600' : 'text-orange-600' }}">
                                {{ $ppnReported ? '✓ Sudah Lapor' : '⚠ Belum Lapor' }}
                            </p>
                        </div>
                    </button>

                    {{-- PPh Tab --}}
                    <button @click="activeTab = 'pph'"
                        :class="activeTab === 'pph' 
                                ? 'border-purple-500 bg-gradient-to-br from-purple-50 to-purple-100 shadow-lg dark:border-purple-400 dark:from-purple-900/30 dark:to-purple-900/10' 
                                : 'border-gray-200 bg-white hover:border-purple-300 dark:border-gray-700 dark:bg-gray-800/50'"
                        class="group relative flex flex-row md:flex-col items-center gap-3 rounded-xl border-2 px-4 py-3 transition-all duration-200">

                        <div class="absolute -right-2 -top-2 z-10">
                            @if($pphReported)
                            <div
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-green-500 shadow-lg ring-2 ring-white dark:ring-gray-900">
                                <x-heroicon-m-check-circle class="h-4 w-4 text-white" />
                            </div>
                            @else
                            <div
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-orange-500 shadow-lg ring-2 ring-white dark:ring-gray-900">
                                <x-heroicon-m-exclamation-circle class="h-4 w-4 text-white" />
                            </div>
                            @endif
                        </div>

                        <div :class="activeTab === 'pph' ? 'bg-purple-600 shadow-lg' : 'bg-gray-100 dark:bg-gray-700'"
                            class="flex h-12 w-12 items-center justify-center rounded-xl transition-all flex-shrink-0">
                            <x-heroicon-o-banknotes
                                :class="activeTab === 'pph' ? 'text-white' : 'text-gray-600 dark:text-gray-400'"
                                class="h-6 w-6" />
                        </div>

                        <div class="flex-1 text-left md:text-center">
                            <p :class="activeTab === 'pph' ? 'text-purple-900 dark:text-purple-100' : 'text-gray-900 dark:text-white'"
                                class="text-sm font-bold">PPh</p>
                            <p :class="activeTab === 'pph' ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500 dark:text-gray-400'"
                                class="mt-0.5 text-xs">{{ $currentTaxReport->incomeTaxs->count() }} Transaksi</p>
                            <p
                                class="mt-1 text-[10px] font-semibold {{ $pphReported ? 'text-green-600' : 'text-orange-600' }}">
                                {{ $pphReported ? '✓ Sudah Lapor' : '⚠ Belum Lapor' }}
                            </p>
                        </div>
                    </button>

                    {{-- Bupot Tab --}}
                    <button @click="activeTab = 'bupot'"
                        :class="activeTab === 'bupot' 
                                ? 'border-orange-500 bg-gradient-to-br from-orange-50 to-orange-100 shadow-lg dark:border-orange-400 dark:from-orange-900/30 dark:to-orange-900/10' 
                                : 'border-gray-200 bg-white hover:border-orange-300 dark:border-gray-700 dark:bg-gray-800/50'"
                        class="group relative flex flex-row md:flex-col items-center gap-3 rounded-xl border-2 px-4 py-3 transition-all duration-200">

                        <div class="absolute -right-2 -top-2 z-10">
                            @if($bupotReported)
                            <div
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-green-500 shadow-lg ring-2 ring-white dark:ring-gray-900">
                                <x-heroicon-m-check-circle class="h-4 w-4 text-white" />
                            </div>
                            @else
                            <div
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-orange-500 shadow-lg ring-2 ring-white dark:ring-gray-900">
                                <x-heroicon-m-exclamation-circle class="h-4 w-4 text-white" />
                            </div>
                            @endif
                        </div>

                        <div :class="activeTab === 'bupot' ? 'bg-orange-600 shadow-lg' : 'bg-gray-100 dark:bg-gray-700'"
                            class="flex h-12 w-12 items-center justify-center rounded-xl transition-all flex-shrink-0">
                            <x-heroicon-o-receipt-percent
                                :class="activeTab === 'bupot' ? 'text-white' : 'text-gray-600 dark:text-gray-400'"
                                class="h-6 w-6" />
                        </div>

                        <div class="flex-1 text-left md:text-center">
                            <p :class="activeTab === 'bupot' ? 'text-orange-900 dark:text-orange-100' : 'text-gray-900 dark:text-white'"
                                class="text-sm font-bold">PPh Unifikasi</p>
                            <p :class="activeTab === 'bupot' ? 'text-orange-600 dark:text-orange-400' : 'text-gray-500 dark:text-gray-400'"
                                class="mt-0.5 text-xs">{{ $currentTaxReport->bupots->count() }} Bupot</p>
                            <p
                                class="mt-1 text-[10px] font-semibold {{ $bupotReported ? 'text-green-600' : 'text-orange-600' }}">
                                {{ $bupotReported ? '✓ Sudah Lapor' : '⚠ Belum Lapor' }}
                            </p>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        {{-- Tab Content --}}
        <div class="space-y-4">
            {{-- PPN Content --}}
            <div x-show="activeTab === 'invoices'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
                @livewire('client.tax-report.tax-report-invoices', ['taxReportId' => $currentTaxReport->id])
            </div>

            {{-- PPh Content --}}
            {{-- <div x-show="activeTab === 'pph'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
                @livewire('tax-report.components.tax-report-pph', ['taxReportId' => $currentTaxReport->id])
            </div> --}}

            {{-- Bupot Content --}}
            {{-- <div x-show="activeTab === 'bupot'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
                @livewire('tax-report.components.tax-report-bupot', ['taxReportId' => $currentTaxReport->id])
            </div> --}}

            {{-- Placeholder for commented content --}}
            {{-- <div
                class="rounded-xl border border-gray-200 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-800">
                <div class="mx-auto max-w-md">
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                        <x-heroicon-o-document-chart-bar class="h-8 w-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Konten Segera Hadir</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Detail laporan pajak untuk {{ $currentTaxReport->month }} {{
                        $currentTaxReport->created_at->format('Y') }} sedang dalam pengembangan.
                    </p>
                </div>
            </div> --}}
        </div>

    </div>
    @endif

    @endif

    <style>
        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .animate-shimmer {
            animation: shimmer 2s infinite;
        }
    </style>
</div>