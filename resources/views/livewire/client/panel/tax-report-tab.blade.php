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
    " @spt-detail-opened.window="window.scrollTo({ top: 0, behavior: 'smooth' })">

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

    @if($selectedClient && $currentTaxReport)
    <div class="space-y-6" x-show="mounted" x-transition:enter="transition ease-out duration-300 delay-100"
        x-transition:enter-start="opacity-0 transform translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0">

        {{-- ===== DETAIL LAPORAN (per bulan) — tampil hanya saat klik SPT ===== --}}
        @if($viewMode === 'detail')

        {{-- Back button --}}
        <div>
            <button type="button" wire:click="backToSptList"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                <x-filament::icon icon="heroicon-m-arrow-left" class="h-4 w-4" />
                Kembali ke Daftar SPT
            </button>
        </div>

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
            class="overflow-hidden rounded-lg md:rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="p-2 md:p-3 space-y-3">
                {{-- Year Navigation --}}
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Navigasi Tahun
                    </h3>

                    @php
                    $availableYears = $this->availableYears;
                    @endphp

                    <div class="flex items-center gap-1">
                        @foreach($availableYears as $year)
                        @php
                        $yearReportCount = \App\Models\TaxReport::where('client_id', $currentClient->id)
                            ->whereYear('created_at', $year)
                            ->count();
                        $isCurrentYear = $year == $selectedYear;
                        @endphp

                        <button 
                            wire:click="selectYear('{{ $year }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold transition-all duration-200 {{ $isCurrentYear ? 'bg-primary-600 text-white shadow-sm' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700' }}">
                            @if($isCurrentYear)
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                            @endif
                            <span>{{ $year }}</span>
                            <span class="px-1.5 py-0.5 rounded-md text-xs font-semibold {{ $isCurrentYear ? 'bg-primary-700 text-primary-100' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                {{ $yearReportCount }}
                            </span>
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Divider --}}
                <div class="border-t border-gray-200 dark:border-gray-700"></div>

                {{-- Month Navigation --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                        Navigasi Bulan - {{ $selectedYear ?? now()->format('Y') }}
                    </h3>

                    <style>
                        /* Warna kartu navigasi bulan berdasarkan status lapor kontrak klien */
                        .tr-month { border-color: #e5e7eb; background: #ffffff; transition: box-shadow .15s, border-color .15s, background .15s; }
                        .tr-month:hover { box-shadow: 0 1px 3px rgba(15,23,42,.10); }
                        .dark .tr-month { border-color: #374151; background: rgba(31,41,55,.5); }
                        .tr-month.is-reported { border-color: #86efac; background: #f0fdf4; }
                        .dark .tr-month.is-reported { border-color: rgba(34,197,94,.45); background: rgba(20,83,45,.30); }
                        .tr-month.is-unreported { border-color: #fdba74; background: #fff7ed; }
                        .dark .tr-month.is-unreported { border-color: rgba(249,115,22,.45); background: rgba(124,45,18,.30); }
                    </style>

                    @php
                    $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September',
                    'October', 'November', 'December'];
                    $currentYear = $selectedYear ?? now()->format('Y');

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

                    {{-- Mobile: Horizontal scrollable, Desktop: Grid --}}
                    <div class="overflow-x-auto">
                        <div class="flex gap-1.5 md:grid md:grid-cols-12 md:gap-2" style="min-width: max-content;">
                            @foreach($months as $index => $monthName)
                            @php
                            $monthReport = $yearReports->get($monthName);
                            $hasReport = !is_null($monthReport);
                            $isCurrent = $currentTaxReport && $monthName === $currentTaxReport->month;

                            // Hanya jenis pajak yang DIKONTRAK klien yang dihitung status lapornya.
                            $contracted = array_keys(array_filter([
                            'ppn'       => $currentClient->ppn_contract ?? false,
                            'pph'       => $currentClient->pph_contract ?? false,
                            'bupot'     => $currentClient->bupot_contract ?? false,
                            'pph_badan' => $currentClient->pph_badan_contract ?? false,
                            ]));

                            $hasActivity = false;
                            $monthIsReported = false;
                            $monthState = 'nodata'; // nodata | reported | unreported | nocontract

                            if ($hasReport) {
                            $hasActivity = ($monthReport->invoices_count ?? 0) > 0 ||
                            ($monthReport->income_taxs_count ?? 0) > 0 ||
                            ($monthReport->bupots_count ?? 0) > 0;

                            $sumByType = ($monthReport->taxCalculationSummaries ?? collect())->keyBy('tax_type');

                            if (count($contracted) === 0) {
                            $monthState = 'nocontract';
                            } else {
                            $reportedContracted = collect($contracted)->filter(
                            fn ($t) => optional($sumByType->get($t))->report_status === 'Sudah Lapor'
                            )->count();
                            $monthIsReported = $reportedContracted === count($contracted);
                            $monthState = $monthIsReported ? 'reported' : 'unreported';
                            }
                            }

                            // Bulan aktif pakai warna primary; selain itu warnai sesuai status (lihat <style> .tr-month).
                            $stateClass = $isCurrent
                            ? 'border-primary-500 bg-primary-50 shadow-sm dark:border-primary-400 dark:bg-primary-900/20'
                            : 'tr-month ' . ($monthState === 'reported' ? 'is-reported' : ($monthState === 'unreported' ? 'is-unreported' : ''));
                            @endphp

                            @if($hasReport)
                            <button wire:click="selectMonth('{{ $monthName }}')"
                                class="group relative flex flex-col items-center justify-center overflow-hidden rounded-md md:rounded-lg border-2 px-2 py-2 md:px-2 md:py-3 transition-all duration-200 flex-shrink-0 w-16 md:w-auto {{ $stateClass }}">

                                {{-- Ripple effect on hover --}}
                                <div class="absolute inset-0 scale-0 rounded-lg bg-primary-400/10 transition-transform duration-300 group-hover:scale-100"></div>

                                {{-- Reporting Status Indicator --}}
                                <div class="absolute left-0.5 top-0.5 md:left-1 md:top-1 z-10">
                                    @if($monthState === 'reported')
                                    <div class="flex h-3 w-3 md:h-4 md:w-4 items-center justify-center rounded-full bg-green-500 shadow-sm" title="Semua kontrak sudah lapor">
                                        <x-heroicon-m-check class="h-1.5 w-1.5 md:h-2.5 md:w-2.5 text-white" />
                                    </div>
                                    @elseif($monthState === 'unreported')
                                    <div class="flex h-3 w-3 md:h-4 md:w-4 items-center justify-center rounded-full bg-orange-500 shadow-sm" title="Belum semua kontrak lapor">
                                        <x-heroicon-m-exclamation-triangle class="h-1.5 w-1.5 md:h-2.5 md:w-2.5 text-white" />
                                    </div>
                                    @endif
                                </div>

                                {{-- Activity Indicator --}}
                                @if($hasActivity)
                                <div class="absolute right-0.5 top-0.5 md:right-1 md:top-1 z-10">
                                    <span class="flex h-1.5 w-1.5 md:h-2 md:w-2">
                                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full {{ $isCurrent ? 'bg-primary-400' : 'bg-green-400' }} opacity-75"></span>
                                        <span class="relative inline-flex h-1.5 w-1.5 md:h-2 md:w-2 rounded-full {{ $isCurrent ? 'bg-primary-500' : 'bg-green-500' }}"></span>
                                    </span>
                                </div>
                                @endif

                                <span class="relative z-10 text-[9px] md:text-[10px] font-semibold uppercase tracking-wider {{ $isCurrent ? 'text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400' }}">
                                    {{ substr($monthName, 0, 3) }}
                                </span>
                                <span class="relative z-10 mt-0.5 text-xs font-bold {{ $isCurrent ? 'text-primary-900 dark:text-primary-100' : 'text-gray-900 dark:text-white' }}">
                                    {{ $index + 1 }}
                                </span>
                            </button>
                            @else
                            <div class="flex flex-col items-center justify-center rounded-md md:rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 px-2 py-2 md:px-2 md:py-3 opacity-50 dark:border-gray-800 dark:bg-gray-900/20 flex-shrink-0 w-16 md:w-auto">
                                <span class="text-[9px] md:text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-600">
                                    {{ substr($monthName, 0, 3) }}
                                </span>
                                <span class="mt-0.5 text-xs font-bold text-gray-400 dark:text-gray-600">
                                    {{ $index + 1 }}
                                </span>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Legend --}}
                    <div class="mt-2 md:mt-3 flex flex-wrap items-center justify-center gap-x-3 md:gap-x-6 gap-y-1 md:gap-y-2 text-xs">
                        <div class="flex items-center gap-1 md:gap-1.5">
                            <div class="h-2 w-2 rounded-full bg-primary-500"></div>
                            <span class="text-gray-600 dark:text-gray-400 text-[10px] md:text-xs">Bulan Aktif</span>
                        </div>
                        <div class="flex items-center gap-1 md:gap-1.5">
                            <div class="h-2 w-2 rounded-full bg-green-500"></div>
                            <span class="text-gray-600 dark:text-gray-400 text-[10px] md:text-xs">Sudah Lapor</span>
                        </div>
                        <div class="flex items-center gap-1 md:gap-1.5">
                            <div class="h-2 w-2 rounded-full bg-orange-500"></div>
                            <span class="text-gray-600 dark:text-gray-400 text-[10px] md:text-xs">Belum Lapor</span>
                        </div>
                        <div class="flex items-center gap-1 md:gap-1.5">
                            <div class="h-2 w-2 rounded-full border-2 border-dashed border-gray-300"></div>
                            <span class="text-gray-600 dark:text-gray-400 text-[10px] md:text-xs">Belum Ada Data</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- Tax Type Navigation Tabs --}}
        <div
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="p-3">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
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

                    {{-- Bupot Tab - Conditional based on client contract --}}
                    @php
                    $hasBupotContract = $currentClient->bupot_contract ?? false;
                    @endphp
                    
                    @if($hasBupotContract)
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
                    @else
                    {{-- Disabled Bupot Tab - Contract not active --}}
                    <div 
                        x-data="{ showTooltip: false }"
                        @mouseenter="showTooltip = true"
                        @mouseleave="showTooltip = false"
                        class="group relative flex flex-row md:flex-col items-center gap-3 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 opacity-60 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800/30">

                        {{-- Lock icon badge --}}
                        <div class="absolute -right-2 -top-2 z-10">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-400 shadow-lg ring-2 ring-white dark:ring-gray-900">
                                <x-heroicon-m-lock-closed class="h-3.5 w-3.5 text-white" />
                            </div>
                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-200 dark:bg-gray-700 flex-shrink-0">
                            <x-heroicon-o-receipt-percent class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                        </div>

                        <div class="flex-1 text-left md:text-center">
                            <p class="text-sm font-bold text-gray-500 dark:text-gray-400">PPh Unifikasi</p>
                            <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">0 Bupot</p>
                            <p class="mt-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500">
                                🔒 Tidak Aktif
                            </p>
                        </div>

                        {{-- Tooltip --}}
                        <div 
                            x-show="showTooltip"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute bottom-full left-1/2 z-50 mb-2 w-56 -translate-x-1/2 rounded-lg bg-gray-900 px-3 py-2 text-center text-xs text-white shadow-xl dark:bg-gray-700"
                        >
                            <p class="font-medium">Fitur PPh Unifikasi tidak tersedia</p>
                            <p class="mt-1 text-gray-300 dark:text-gray-400">Kontrak PPh Unifikasi (Bupot) untuk klien ini belum aktif. Hubungi admin untuk mengaktifkan fitur ini.</p>
                            <div class="absolute -bottom-1 left-1/2 h-2 w-2 -translate-x-1/2 rotate-45 bg-gray-900 dark:bg-gray-700"></div>
                        </div>
                    </div>
                    @endif

                    {{-- PPh Badan Tab - Conditional based on client contract --}}
                    @php
                    $hasPphBadanContract = $currentClient->pph_badan_contract ?? false;
                    $pphBadanReported = false; // Placeholder - will need proper implementation
                    @endphp
                    
                    @if($hasPphBadanContract)
                    <button @click="activeTab = 'pph_badan'"
                        :class="activeTab === 'pph_badan' 
                                ? 'border-indigo-500 bg-gradient-to-br from-indigo-50 to-indigo-100 shadow-lg dark:border-indigo-400 dark:from-indigo-900/30 dark:to-indigo-900/10' 
                                : 'border-gray-200 bg-white hover:border-indigo-300 dark:border-gray-700 dark:bg-gray-800/50'"
                        class="group relative flex flex-row md:flex-col items-center gap-3 rounded-xl border-2 px-4 py-3 transition-all duration-200">

                        <div class="absolute -right-2 -top-2 z-10">
                            @if($pphBadanReported)
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

                        <div :class="activeTab === 'pph_badan' ? 'bg-indigo-600 shadow-lg' : 'bg-gray-100 dark:bg-gray-700'"
                            class="flex h-12 w-12 items-center justify-center rounded-xl transition-all flex-shrink-0">
                            <x-heroicon-o-building-office
                                :class="activeTab === 'pph_badan' ? 'text-white' : 'text-gray-600 dark:text-gray-400'"
                                class="h-6 w-6" />
                        </div>

                        <div class="flex-1 text-left md:text-center">
                            <p :class="activeTab === 'pph_badan' ? 'text-indigo-900 dark:text-indigo-100' : 'text-gray-900 dark:text-white'"
                                class="text-sm font-bold">PPh Badan</p>
                            <p :class="activeTab === 'pph_badan' ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400'"
                                class="mt-0.5 text-xs">0 Transaksi</p>
                            <p
                                class="mt-1 text-[10px] font-semibold {{ $pphBadanReported ? 'text-green-600' : 'text-orange-600' }}">
                                {{ $pphBadanReported ? '✓ Sudah Lapor' : '⚠ Belum Lapor' }}
                            </p>
                        </div>
                    </button>
                    @else
                    {{-- Disabled PPh Badan Tab - Contract not active --}}
                    <div 
                        x-data="{ showTooltip: false }"
                        @mouseenter="showTooltip = true"
                        @mouseleave="showTooltip = false"
                        class="group relative flex flex-row md:flex-col items-center gap-3 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 opacity-60 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800/30">

                        {{-- Lock icon badge --}}
                        <div class="absolute -right-2 -top-2 z-10">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-400 shadow-lg ring-2 ring-white dark:ring-gray-900">
                                <x-heroicon-m-lock-closed class="h-3.5 w-3.5 text-white" />
                            </div>
                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-200 dark:bg-gray-700 flex-shrink-0">
                            <x-heroicon-o-building-office class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                        </div>

                        <div class="flex-1 text-left md:text-center">
                            <p class="text-sm font-bold text-gray-500 dark:text-gray-400">PPh Badan</p>
                            <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">0 Transaksi</p>
                            <p class="mt-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500">
                                🔒 Tidak Aktif
                            </p>
                        </div>

                        {{-- Tooltip --}}
                        <div 
                            x-show="showTooltip"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute bottom-full left-1/2 z-50 mb-2 w-56 -translate-x-1/2 rounded-lg bg-gray-900 px-3 py-2 text-center text-xs text-white shadow-xl dark:bg-gray-700"
                        >
                            <p class="font-medium">Fitur PPh Badan tidak tersedia</p>
                            <p class="mt-1 text-gray-300 dark:text-gray-400">Kontrak PPh Badan untuk klien ini belum aktif. Hubungi admin untuk mengaktifkan fitur ini.</p>
                            <div class="absolute -bottom-1 left-1/2 h-2 w-2 -translate-x-1/2 rotate-45 bg-gray-900 dark:bg-gray-700"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tab Content --}}
        <div class="space-y-4">
            {{-- PPN Content --}}
            <div x-show="activeTab === 'invoices'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
                @livewire('client.panel.tax-report.tax-report-invoices', ['taxReportId' => $currentTaxReport->id])
            </div>

            {{-- PPh Content --}}
            <div x-show="activeTab === 'pph'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
                @livewire('client.panel.tax-report.tax-report-pph', ['taxReportId' => $currentTaxReport->id])
            </div>

            {{-- Bupot Content --}}
            <div x-show="activeTab === 'bupot'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
                @livewire('client.panel.tax-report.tax-report-bupot', ['taxReportId' => $currentTaxReport->id])
            </div>

        </div>

        @endif {{-- /detail mode --}}

        {{-- ===== SPT Dilaporkan — tampil saat buka tab (mode list) ===== --}}
        @if($viewMode === 'list')
        @php
            $allSpts    = $this->reportedSpts;
            $spts       = $this->filteredSpts;
            $grandTotal = $allSpts->count();
            $sptTotal   = $spts->count();
            $hasFilter  = $sptSearch !== '' || count($sptJenis) > 0 || $sptYear !== '';
            $sptLastPage = max(1, (int) ceil($sptTotal / $sptPerPage));
            $sptPageSafe = min(max(1, $sptPage), $sptLastPage);
            $sptOffset = ($sptPageSafe - 1) * $sptPerPage;
            $sptRows = $spts->slice($sptOffset, $sptPerPage);
            $sptFrom = $sptTotal ? $sptOffset + 1 : 0;
            $sptTo = min($sptOffset + $sptPerPage, $sptTotal);

            $sptGroupLabels = ['jenis' => 'Jenis SPT', 'tahun' => 'Tahun', 'status' => 'Status', 'none' => 'Tanpa Grup'];
            $sptGroupOf = function ($r) use ($sptGroupBy) {
                return match ($sptGroupBy) {
                    'tahun'  => (string) $r['year'],
                    'status' => $r['paid'] ? 'Sudah Lapor & Bayar' : 'Sudah Lapor',
                    default  => $r['jenis'],
                };
            };
            $sptGroupCounts = $sptGroupBy === 'none' ? collect() : $spts->groupBy($sptGroupOf)->map->count();
        @endphp

        <style>
            .spt-act { display:inline-flex; align-items:center; justify-content:center; height:1.75rem; width:1.75rem; border-radius:.375rem; transition:background-color .15s, color .15s; }
            .spt-act--view { color:#0891b2; } .spt-act--view:hover { background:#ecfeff; }
            .dark .spt-act--view { color:#22d3ee; } .dark .spt-act--view:hover { background:rgba(8,145,178,.16); }
            .spt-act--open { color:#16a34a; } .spt-act--open:hover { background:#f0fdf4; }
            .dark .spt-act--open { color:#4ade80; } .dark .spt-act--open:hover { background:rgba(22,163,74,.16); }
            .spt-act--dl { color:#dc2626; } .spt-act--dl:hover { background:#fef2f2; }
            .dark .spt-act--dl { color:#f87171; } .dark .spt-act--dl:hover { background:rgba(220,38,38,.16); }

            /* Header grup berwarna sesuai tipe SPT */
            .spt-grp { border-left:3px solid transparent; }
            .spt-grp--ppn        { background:#ecfeff; border-color:#06b6d4; } .spt-grp--ppn .spt-grp-label        { color:#0e7490; }
            .dark .spt-grp--ppn  { background:rgba(8,145,178,.14); }            .dark .spt-grp--ppn .spt-grp-label  { color:#67e8f9; }
            .spt-grp--pph        { background:#faf5ff; border-color:#a855f7; } .spt-grp--pph .spt-grp-label        { color:#7e22ce; }
            .dark .spt-grp--pph  { background:rgba(168,85,247,.14); }           .dark .spt-grp--pph .spt-grp-label  { color:#d8b4fe; }
            .spt-grp--bupot      { background:#fff7ed; border-color:#f97316; } .spt-grp--bupot .spt-grp-label      { color:#c2410c; }
            .dark .spt-grp--bupot{ background:rgba(249,115,22,.14); }           .dark .spt-grp--bupot .spt-grp-label{ color:#fdba74; }
            .spt-grp--pph_badan  { background:#eef2ff; border-color:#6366f1; } .spt-grp--pph_badan .spt-grp-label  { color:#4338ca; }
            .dark .spt-grp--pph_badan { background:rgba(99,102,241,.14); }      .dark .spt-grp--pph_badan .spt-grp-label { color:#a5b4fc; }
            .spt-grp--default    { background:#ecfeff; border-color:#06b6d4; } .spt-grp--default .spt-grp-label    { color:#0e7490; }
            .dark .spt-grp--default { background:rgba(8,145,178,.14); }         .dark .spt-grp--default .spt-grp-label { color:#67e8f9; }
        </style>

        <div class="overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-sm ring-1 ring-gray-900/[0.03] dark:border-gray-800 dark:bg-gray-900 dark:ring-white/[0.04]">
            {{-- Card header --}}
            <div class="relative flex items-start justify-between gap-3 overflow-hidden border-b border-gray-100 bg-gradient-to-br from-primary-50/70 via-white to-white px-4 py-4 dark:border-gray-800 dark:from-primary-500/[0.07] dark:via-gray-900 dark:to-gray-900 md:px-6 md:py-5">
                {{-- aksen dekoratif lembut --}}
                <div class="pointer-events-none absolute -right-8 -top-12 h-32 w-32 rounded-full bg-primary-200/25 blur-3xl dark:bg-primary-500/10"></div>

                <div class="relative flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-600 text-white shadow-sm dark:bg-primary-500">
                        <x-filament::icon icon="heroicon-o-document-check" class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">SPT Dilaporkan</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Riwayat SPT yang telah dilaporkan — klik baris untuk membuka detail.</p>
                    </div>
                </div>

                <span class="relative inline-flex shrink-0 items-center gap-1.5 rounded-full border border-primary-200/70 bg-white/70 px-2.5 py-1 text-xs font-semibold text-primary-700 backdrop-blur dark:border-primary-500/20 dark:bg-gray-900/60 dark:text-primary-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-primary-500"></span>
                    {{ $grandTotal }} SPT
                </span>
            </div>

            @if($grandTotal === 0)
                {{-- Empty: belum ada SPT sama sekali --}}
                <div class="flex flex-col items-center justify-center gap-2 px-4 py-14 text-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                        <x-filament::icon icon="heroicon-o-document-check" class="h-6 w-6" />
                    </div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Belum ada SPT yang dilaporkan</p>
                    <p class="max-w-sm text-xs text-gray-500 dark:text-gray-400">
                        SPT akan muncul di sini setelah berkas SPT/BPE diunggah pada masa pajak terkait.
                    </p>
                </div>
            @else
                <div class="p-4 md:p-6">
                    {{-- Toolbar: search + filter --}}
                    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                        {{-- Search --}}
                        <div class="relative w-full sm:w-72">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <x-filament::icon icon="heroicon-m-magnifying-glass" class="h-4 w-4" />
                            </span>
                            <input type="text" wire:model.live.debounce.300ms="sptSearch"
                                placeholder="Cari jenis, masa, atau no. bukti…"
                                class="h-9 w-full rounded-lg border border-gray-300 bg-white pl-9 pr-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100" />
                        </div>

                        {{-- Jenis filter --}}
                        @if($this->sptJenisOptions->count() > 1)
                        <div class="relative" x-data="{ open: false }">
                            <button type="button" @click="open = !open"
                                class="inline-flex h-9 w-full items-center gap-1.5 rounded-lg border border-dashed border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800 sm:w-auto">
                                <x-filament::icon icon="heroicon-m-funnel" class="h-4 w-4 text-gray-400" />
                                Jenis
                                @if(count($sptJenis))
                                    <span class="mx-0.5 h-4 w-px bg-gray-300 dark:bg-gray-600"></span>
                                    <span class="rounded bg-primary-100 px-1.5 py-0.5 text-xs font-semibold text-primary-700 dark:bg-primary-500/20 dark:text-primary-300">{{ count($sptJenis) }}</span>
                                @endif
                            </button>
                            <div x-show="open" @click.outside="open = false" x-transition x-cloak
                                class="absolute left-0 z-30 mt-2 w-56 rounded-lg border border-gray-200 bg-white p-1.5 shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                <p class="px-2 py-1 text-xs font-medium text-gray-400">Filter Jenis SPT</p>
                                @foreach($this->sptJenisOptions as $val => $label)
                                    <label class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-sm text-gray-700 transition-colors hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">
                                        <input type="checkbox" value="{{ $val }}" wire:model.live="sptJenis"
                                            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800" />
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Tahun filter --}}
                        @if($this->sptYears->count() > 1)
                        <div class="relative" x-data="{ open: false }">
                            <button type="button" @click="open = !open"
                                class="inline-flex h-9 w-full items-center gap-1.5 rounded-lg border border-dashed border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800 sm:w-auto">
                                <x-filament::icon icon="heroicon-m-calendar-days" class="h-4 w-4 text-gray-400" />
                                {{ $sptYear !== '' ? $sptYear : 'Tahun' }}
                                <x-filament::icon icon="heroicon-m-chevron-down" class="h-3.5 w-3.5 text-gray-400" />
                            </button>
                            <div x-show="open" @click.outside="open = false" x-transition x-cloak
                                class="absolute left-0 z-30 mt-2 w-40 rounded-lg border border-gray-200 bg-white p-1.5 shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                <button type="button" wire:click="setSptYear('')" @click="open = false"
                                    class="flex w-full items-center justify-between rounded-md px-2 py-1.5 text-left text-sm transition-colors hover:bg-gray-100 dark:hover:bg-gray-800 {{ $sptYear === '' ? 'font-semibold text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-200' }}">
                                    Semua Tahun
                                    @if($sptYear === '')<x-filament::icon icon="heroicon-m-check" class="h-4 w-4" />@endif
                                </button>
                                @foreach($this->sptYears as $yr)
                                    <button type="button" wire:click="setSptYear('{{ $yr }}')" @click="open = false"
                                        class="flex w-full items-center justify-between rounded-md px-2 py-1.5 text-left text-sm transition-colors hover:bg-gray-100 dark:hover:bg-gray-800 {{ (string) $sptYear === (string) $yr ? 'font-semibold text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-200' }}">
                                        {{ $yr }}
                                        @if((string) $sptYear === (string) $yr)<x-filament::icon icon="heroicon-m-check" class="h-4 w-4" />@endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Reset --}}
                        @if($hasFilter)
                            <button type="button" wire:click="resetSptFilters"
                                class="inline-flex h-9 items-center gap-1 rounded-lg px-2 text-sm font-medium text-gray-500 transition-colors hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                                Reset
                                <x-filament::icon icon="heroicon-m-x-mark" class="h-4 w-4" />
                            </button>
                        @endif

                        {{-- Group by --}}
                        <div class="relative sm:ml-auto" x-data="{ open: false }">
                            <button type="button" @click="open = !open"
                                class="inline-flex h-9 w-full items-center gap-1.5 rounded-lg border border-dashed border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800 sm:w-auto">
                                <x-filament::icon icon="heroicon-m-rectangle-group" class="h-4 w-4 text-gray-400" />
                                <span class="text-gray-400">Grup:</span>
                                {{ $sptGroupLabels[$sptGroupBy] ?? 'Jenis SPT' }}
                                <x-filament::icon icon="heroicon-m-chevron-down" class="h-3.5 w-3.5 text-gray-400" />
                            </button>
                            <div x-show="open" @click.outside="open = false" x-transition x-cloak
                                class="absolute right-0 z-30 mt-2 w-44 rounded-lg border border-gray-200 bg-white p-1.5 shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                <p class="px-2 py-1 text-xs font-medium text-gray-400">Kelompokkan menurut</p>
                                @foreach($sptGroupLabels as $val => $label)
                                    <button type="button" wire:click="setSptGroup('{{ $val }}')" @click="open = false"
                                        class="flex w-full items-center justify-between rounded-md px-2 py-1.5 text-left text-sm transition-colors hover:bg-gray-100 dark:hover:bg-gray-800 {{ $sptGroupBy === $val ? 'font-semibold text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-200' }}">
                                        {{ $label }}
                                        @if($sptGroupBy === $val)<x-filament::icon icon="heroicon-m-check" class="h-4 w-4" />@endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @if($sptTotal === 0)
                        {{-- No results --}}
                        <div class="flex flex-col items-center justify-center gap-2 py-12 text-center">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-5 w-5" />
                            </div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Tidak ada SPT yang cocok</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Coba ubah kata kunci atau filter.</p>
                            <button type="button" wire:click="resetSptFilters" class="mt-1 text-xs font-semibold text-primary-600 hover:underline dark:text-primary-400">Reset filter</button>
                        </div>
                    @else
                        {{-- Table --}}
                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-800/50">
                                    <tr class="border-b border-gray-200 text-left text-xs text-gray-400 dark:border-gray-700 dark:text-gray-500">
                                        <th class="px-4 py-2.5 font-semibold uppercase tracking-wider">Aksi</th>
                                        <th class="px-4 py-2.5">
                                            <button type="button" wire:click="sortBy('jenis')" class="group inline-flex items-center gap-1 font-semibold uppercase tracking-wider transition-colors hover:text-gray-700 dark:hover:text-gray-300">
                                                Jenis SPT
                                                @if($sptSort === 'jenis')
                                                    <x-filament::icon icon="{{ $sptSortDir === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}" class="h-3.5 w-3.5 text-primary-500" />
                                                @else
                                                    <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 text-gray-300 transition group-hover:text-gray-400 dark:text-gray-600" />
                                                @endif
                                            </button>
                                        </th>
                                        <th class="px-4 py-2.5">
                                            <button type="button" wire:click="sortBy('masa')" class="group inline-flex items-center gap-1 font-semibold uppercase tracking-wider transition-colors hover:text-gray-700 dark:hover:text-gray-300">
                                                Masa Pajak
                                                @if($sptSort === 'masa')
                                                    <x-filament::icon icon="{{ $sptSortDir === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}" class="h-3.5 w-3.5 text-primary-500" />
                                                @else
                                                    <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 text-gray-300 transition group-hover:text-gray-400 dark:text-gray-600" />
                                                @endif
                                            </button>
                                        </th>
                                        <th class="px-4 py-2.5 text-center font-semibold uppercase tracking-wider">Pembetulan Ke-</th>
                                        <th class="px-4 py-2.5 text-right">
                                            <button type="button" wire:click="sortBy('nominal')" class="group ml-auto inline-flex items-center gap-1 font-semibold uppercase tracking-wider transition-colors hover:text-gray-700 dark:hover:text-gray-300">
                                                Nominal
                                                @if($sptSort === 'nominal')
                                                    <x-filament::icon icon="{{ $sptSortDir === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}" class="h-3.5 w-3.5 text-primary-500" />
                                                @else
                                                    <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 text-gray-300 transition group-hover:text-gray-400 dark:text-gray-600" />
                                                @endif
                                            </button>
                                        </th>
                                        <th class="px-4 py-2.5">
                                            <button type="button" wire:click="sortBy('status')" class="group inline-flex items-center gap-1 font-semibold uppercase tracking-wider transition-colors hover:text-gray-700 dark:hover:text-gray-300">
                                                Status
                                                @if($sptSort === 'status')
                                                    <x-filament::icon icon="{{ $sptSortDir === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}" class="h-3.5 w-3.5 text-primary-500" />
                                                @else
                                                    <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-3.5 w-3.5 text-gray-300 transition group-hover:text-gray-400 dark:text-gray-600" />
                                                @endif
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @php $prevGroup = null; @endphp
                                    @foreach($sptRows as $row)
                                        @php $g = $sptGroupBy === 'none' ? null : $sptGroupOf($row); @endphp
                                        @if($sptGroupBy !== 'none' && $g !== $prevGroup)
                                            @php
                                                $prevGroup = $g;
                                                $gClass = $sptGroupBy === 'jenis' ? ($row['type'] ?? 'default') : 'default';
                                            @endphp
                                            <tr wire:key="grp-{{ $loop->index }}">
                                                <td colspan="6" class="spt-grp spt-grp--{{ $gClass }} px-4 py-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="spt-grp-label text-xs font-bold uppercase tracking-wider">{{ $g }}</span>
                                                        <span class="spt-grp-label inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-white/70 px-1 text-[10px] font-bold dark:bg-black/20">{{ $sptGroupCounts[$g] ?? 0 }}</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                        <tr wire:key="spt-{{ $row['id'] }}"
                                            wire:click="openSptDetail('{{ $row['month'] }}', '{{ $row['year'] }}')"
                                            class="cursor-pointer bg-white transition-colors hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/50">
                                            <td class="whitespace-nowrap px-4 py-3">
                                                <div class="flex items-center gap-1.5">
                                                    <button type="button" title="Lihat detail laporan"
                                                        wire:click.stop="openSptDetail('{{ $row['month'] }}', '{{ $row['year'] }}')"
                                                        class="spt-act">
                                                        <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                                                    </button>
                                                    @if($row['fileUrl'])
                                                        <a href="{{ $row['fileUrl'] }}" target="_blank" @click.stop
                                                            title="Buka berkas SPT" class="spt-act spt-act--open">
                                                            <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-4 w-4" />
                                                        </a>
                                                        <a href="{{ $row['fileUrl'] }}" download @click.stop
                                                            title="Unduh berkas SPT" class="spt-act spt-act--dl">
                                                            <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4" />
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-900 dark:text-white">
                                                {{ $row['jenis'] }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300">
                                                {{ $row['masa'] }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-center text-gray-600 dark:text-gray-300">
                                                {{ $row['pembetulan'] }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right font-semibold tabular-nums text-gray-900 dark:text-white">
                                                Rp {{ number_format($row['nominal'], 0, ',', '.') }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3">
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    <x-filament::badge color="success" size="sm">Sudah Lapor</x-filament::badge>
                                                    @if($row['paid'])
                                                        <x-filament::badge color="success" size="sm">Sudah Bayar</x-filament::badge>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Footer: per-page + info + pager --}}
                        <div class="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row">
                            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                <span>Baris per halaman</span>
                                <select wire:model.live="sptPerPage"
                                    class="h-8 rounded-md border-gray-300 bg-white py-0 pl-2.5 pr-8 text-sm text-gray-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="text-sm tabular-nums text-gray-500 dark:text-gray-400">{{ $sptFrom }}–{{ $sptTo }} dari {{ $sptTotal }}</span>
                                @if($sptLastPage > 1)
                                    <div class="flex items-center gap-1">
                                        <button type="button" wire:click="gotoSptPage({{ $sptPageSafe - 1 }})"
                                            @disabled($sptPageSafe <= 1)
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-300 text-gray-500 transition-colors hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-800">
                                            <x-filament::icon icon="heroicon-m-chevron-left" class="h-4 w-4" />
                                        </button>
                                        @for($p = 1; $p <= $sptLastPage; $p++)
                                            <button type="button" wire:click="gotoSptPage({{ $p }})"
                                                class="inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2.5 text-sm font-semibold transition-colors {{ $p === $sptPageSafe ? 'bg-primary-600 text-white shadow-sm' : 'border border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800' }}">
                                                {{ $p }}
                                            </button>
                                        @endfor
                                        <button type="button" wire:click="gotoSptPage({{ $sptPageSafe + 1 }})"
                                            @disabled($sptPageSafe >= $sptLastPage)
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-300 text-gray-500 transition-colors hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-800">
                                            <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4" />
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        @endif {{-- /list mode --}}

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