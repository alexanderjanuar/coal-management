<x-filament::page>
    {{-- Livewire Page Transition Styles --}}
    <style>
        /* Smooth page transitions for wire:navigate */
        [wire\:navigate] {
            transition: opacity 150ms ease-in-out, transform 150ms ease-in-out;
        }

        [wire\:navigate]:active {
            transform: scale(0.98);
        }

        /* Page transition animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-transition {
            animation: fadeIn 300ms ease-out;
        }

        /* Loading state */
        .loading-blur {
            filter: blur(2px);
            pointer-events: none;
        }

        /* Progress bar for navigation */
        @keyframes progress {
            0% {
                width: 0%;
            }

            50% {
                width: 70%;
            }

            100% {
                width: 100%;
            }
        }

        .progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, #3B82F6, #60A5FA);
            z-index: 9999;
            animation: progress 800ms ease-in-out;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
        }

        /* Status pulse animation */
        @keyframes statusPulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.8;
                transform: scale(1.05);
            }
        }

        .status-pulse {
            animation: statusPulse 2s ease-in-out infinite;
        }

        /* Custom scrollbar for month navigation on mobile */
        .month-scroll::-webkit-scrollbar {
            height: 3px;
        }

        .month-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .month-scroll::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }

        .month-scroll::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        /* Mobile tooltip adjustments */
        .mobile-tooltip {
            position: fixed;
            bottom: 4px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
        }

        @media (min-width: 768px) {
            .mobile-tooltip {
                position: absolute;
                bottom: auto;
                top: 100%;
                left: 50%;
                transform: translateX(-50%);
            }
        }

        /* Responsive grid adjustments */
        @media (max-width: 640px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
            }
        }
    </style>

    {{-- Progress Bar (shown during navigation) --}}
    <div wire:loading wire:target="$refresh" class="progress-bar"></div>

    <div x-data="{ 
        activeTab: 'invoices',
        selectedMonth: '{{ $record->month }}',
        showMobileMenu: false,
        isMobile: window.innerWidth < 768
    }" x-init="
        window.addEventListener('resize', () => {
            isMobile = window.innerWidth < 768;
        });
    " wire:loading.class="opacity-50 pointer-events-none" wire:target="$refresh">

        {{-- Loading Indicator --}}
        <div wire:loading wire:target="$refresh"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/20 backdrop-blur-sm">
            <div class="mx-4 flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-2xl dark:bg-gray-900">
                <svg class="h-6 w-6 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">Loading...</span>
            </div>
        </div>

        {{-- Client Header - Responsive --}}
        <div class="page-transition mb-3 md:mb-4">
            <div
                class="overflow-hidden rounded-lg md:rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex flex-col gap-3 p-3 md:flex-row md:items-center md:justify-between md:px-6 md:py-4">
                    {{-- Client Info --}}
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-8 w-8 md:h-10 md:w-10 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-blue-600">
                            <x-filament::icon icon="heroicon-o-building-office-2"
                                class="h-4 w-4 md:h-5 md:w-5 text-white" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h1 class="truncate text-base md:text-lg font-bold text-gray-900 dark:text-white">
                                {{ $record->client->name }}
                            </h1>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Tax Report • {{ $record->created_at->format('Y') }}
                            </p>
                        </div>
                    </div>

                    {{-- Status & Quick Stats - Responsive --}}
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:gap-4">
                        {{-- Quick Stats - Mobile: 2x2 grid, Desktop: horizontal --}}
                        <div class="stats-grid grid grid-cols-3 gap-2 md:flex md:items-center md:gap-4">
                            <div class="flex items-center gap-1.5 md:gap-2">
                                <div
                                    class="flex h-6 w-6 md:h-8 md:w-8 items-center justify-center rounded-md md:rounded-lg bg-blue-50 dark:bg-blue-900/30">
                                    <x-filament::icon icon="heroicon-o-document-text"
                                        class="h-3 w-3 md:h-4 md:w-4 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400">PPN</p>
                                    <p class="text-xs md:text-sm font-bold text-gray-900 dark:text-white">{{
                                        $record->invoices->count() }}</p>
                                </div>
                            </div>

                            <div class="hidden md:block h-8 w-px bg-gray-200 dark:bg-gray-700"></div>

                            <div class="flex items-center gap-1.5 md:gap-2">
                                <div
                                    class="flex h-6 w-6 md:h-8 md:w-8 items-center justify-center rounded-md md:rounded-lg bg-purple-50 dark:bg-purple-900/30">
                                    <x-filament::icon icon="heroicon-o-banknotes"
                                        class="h-3 w-3 md:h-4 md:w-4 text-purple-600 dark:text-purple-400" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400">PPh</p>
                                    <p class="text-xs md:text-sm font-bold text-gray-900 dark:text-white">{{
                                        $record->incomeTaxs->count() }}</p>
                                </div>
                            </div>

                            <div class="hidden md:block h-8 w-px bg-gray-200 dark:bg-gray-700"></div>

                            <div class="flex items-center gap-1.5 md:gap-2">
                                <div
                                    class="flex h-6 w-6 md:h-8 md:w-8 items-center justify-center rounded-md md:rounded-lg bg-orange-50 dark:bg-orange-900/30">
                                    <x-filament::icon icon="heroicon-o-receipt-percent"
                                        class="h-3 w-3 md:h-4 md:w-4 text-orange-600 dark:text-orange-400" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400">Bupot</p>
                                    <p class="text-xs md:text-sm font-bold text-gray-900 dark:text-white">{{
                                        $record->bupots->count() }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="hidden md:block h-8 w-px bg-gray-200 dark:bg-gray-700"></div>

                        {{-- Reporting Status Indicator - Responsive --}}
                        @php
                        // Efficient: Use eager-loaded summaries
                        $summaries = $record->relationLoaded('taxCalculationSummaries')
                        ? $record->taxCalculationSummaries
                        : $record->taxCalculationSummaries()->select('id', 'tax_report_id', 'tax_type',
                        'report_status')->get();

                        // Get status for each tax type from summaries
                        $ppnSummary = $summaries->firstWhere('tax_type', 'ppn');
                        $pphSummary = $summaries->firstWhere('tax_type', 'pph');
                        $bupotSummary = $summaries->firstWhere('tax_type', 'bupot');

                        $ppnReported = $ppnSummary && $ppnSummary->report_status === 'Sudah Lapor';
                        $pphReported = $pphSummary && $pphSummary->report_status === 'Sudah Lapor';
                        $bupotReported = $bupotSummary && $bupotSummary->report_status === 'Sudah Lapor';

                        // Count reported
                        $reportedCount = ($ppnReported ? 1 : 0) + ($pphReported ? 1 : 0) + ($bupotReported ? 1 : 0);
                        $totalCount = 3;

                        // All reported check
                        $allReported = $reportedCount === $totalCount;

                        // Build not reported list
                        $notReportedList = [];
                        if (!$ppnReported && $ppnSummary) $notReportedList[] = 'PPN';
                        if (!$pphReported && $pphSummary) $notReportedList[] = 'PPh';
                        if (!$bupotReported && $bupotSummary) $notReportedList[] = 'Bupot';

                        // Status text with details
                        if ($allReported) {
                        $statusText = 'Semua Sudah Lapor';
                        $statusDetail = null;
                        } elseif ($reportedCount > 0) {
                        $statusText = "{$reportedCount}/{$totalCount} Sudah Lapor";
                        $statusDetail = 'Belum: ' . implode(', ', $notReportedList);
                        } else {
                        $statusText = 'Belum Lapor';
                        $statusDetail = count($notReportedList) > 0 ? 'Belum: ' . implode(', ', $notReportedList) :
                        'Semua belum dilaporkan';
                        }
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
                            <div class="flex flex-col gap-0.5">
                                <span
                                    class="text-xs font-semibold leading-none {{ $allReported ? 'text-green-700 dark:text-green-400' : ($reportedCount > 0 ? 'text-yellow-700 dark:text-yellow-400' : 'text-orange-700 dark:text-orange-400') }}">
                                    {{ $statusText }}
                                </span>
                                @if($statusDetail)
                                <span
                                    class="text-[10px] leading-none {{ $reportedCount > 0 ? 'text-yellow-600 dark:text-yellow-500' : 'text-orange-600 dark:text-orange-500' }}">
                                    {{ $statusDetail }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 12-Month Navigation Tabs - Responsive --}}
        <div class="mb-3 md:mb-4">
            <div
                class="overflow-hidden rounded-lg md:rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="p-2 md:p-3">
                    @php
                    $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September',
                    'October', 'November', 'December'];
                    $currentYear = $record->created_at->format('Y');

                    // EFFICIENT: Single query with eager loading and minimal columns
                    $yearReports = \App\Models\TaxReport::where('client_id', $record->client_id)
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
                    <div class="month-scroll overflow-x-auto md:overflow-x-visible">
                        <div class="flex gap-1.5 md:grid md:grid-cols-12 md:gap-2" style="min-width: max-content;">
                            @foreach($months as $index => $monthName)
                            @php
                            $monthReport = $yearReports->get($monthName);
                            $hasReport = !is_null($monthReport);
                            $isCurrent = $monthName === $record->month;

                            // Use counts from withCount (already loaded, no extra query)
                            $hasActivity = $hasReport && (
                            ($monthReport->invoices_count ?? 0) > 0 ||
                            ($monthReport->income_taxs_count ?? 0) > 0 ||
                            ($monthReport->bupots_count ?? 0) > 0
                            );
                            @endphp

                            @if($hasReport)
                            @php
                            // Check if ALL tax types are reported using summaries
                            $monthSummaries = $monthReport->taxCalculationSummaries ?? collect();
                            $ppnSum = $monthSummaries->firstWhere('tax_type', 'ppn');
                            $pphSum = $monthSummaries->firstWhere('tax_type', 'pph');
                            $bupotSum = $monthSummaries->firstWhere('tax_type', 'bupot');

                            $ppnRep = $ppnSum && $ppnSum->report_status === 'Sudah Lapor';
                            $pphRep = $pphSum && $pphSum->report_status === 'Sudah Lapor';
                            $bupotRep = $bupotSum && $bupotSum->report_status === 'Sudah Lapor';

                            $monthIsReported = $ppnRep && $pphRep && $bupotRep;
                            @endphp
                            <a href="{{ route('filament.admin.resources.tax-reports.view', $monthReport) }}"
                                wire:navigate
                                class="group relative flex flex-col items-center justify-center overflow-hidden rounded-md md:rounded-lg border-2 px-2 py-2 md:px-2 md:py-3 transition-all duration-200 flex-shrink-0 w-16 md:w-auto {{ $isCurrent ? 'border-blue-500 bg-blue-50 shadow-sm dark:border-blue-400 dark:bg-blue-900/20' : 'border-gray-200 bg-white hover:border-blue-300 hover:bg-blue-50/50 hover:shadow-sm dark:border-gray-700 dark:bg-gray-800/50 dark:hover:border-blue-600 dark:hover:bg-blue-900/10' }}">

                                {{-- Ripple effect on hover --}}
                                <div
                                    class="absolute inset-0 scale-0 rounded-lg bg-blue-400/10 transition-transform duration-300 group-hover:scale-100">
                                </div>

                                {{-- Reporting Status Indicator --}}
                                <div class="absolute left-0.5 top-0.5 md:left-1 md:top-1 z-10">
                                    @if($monthIsReported)
                                    <div class="flex h-3 w-3 md:h-4 md:w-4 items-center justify-center rounded-full bg-green-500 shadow-sm"
                                        title="Sudah Lapor">
                                        <x-filament::icon icon="heroicon-m-check"
                                            class="h-1.5 w-1.5 md:h-2.5 md:w-2.5 text-white" />
                                    </div>
                                    @else
                                    <div class="flex h-3 w-3 md:h-4 md:w-4 items-center justify-center rounded-full bg-orange-500 shadow-sm"
                                        title="Belum Lapor">
                                        <x-filament::icon icon="heroicon-m-exclamation-triangle"
                                            class="h-1.5 w-1.5 md:h-2.5 md:w-2.5 text-white" />
                                    </div>
                                    @endif
                                </div>

                                {{-- Activity Indicator --}}
                                @if($hasActivity)
                                <div class="absolute right-0.5 top-0.5 md:right-1 md:top-1 z-10">
                                    <span class="flex h-1.5 w-1.5 md:h-2 md:w-2">
                                        <span
                                            class="absolute inline-flex h-full w-full animate-ping rounded-full {{ $isCurrent ? 'bg-blue-400' : 'bg-green-400' }} opacity-75"></span>
                                        <span
                                            class="relative inline-flex h-1.5 w-1.5 md:h-2 md:w-2 rounded-full {{ $isCurrent ? 'bg-blue-500' : 'bg-green-500' }}"></span>
                                    </span>
                                </div>
                                @endif

                                <span
                                    class="relative z-10 text-[9px] md:text-[10px] font-semibold uppercase tracking-wider {{ $isCurrent ? 'text-blue-700 dark:text-blue-300' : 'text-gray-600 dark:text-gray-400' }}">
                                    {{ substr($monthName, 0, 3) }}
                                </span>
                                <span
                                    class="relative z-10 mt-0.5 text-xs font-bold {{ $isCurrent ? 'text-blue-900 dark:text-blue-100' : 'text-gray-900 dark:text-white' }}">
                                    {{ $index + 1 }}
                                </span>

                                {{-- Tooltip on hover (hidden on mobile touch) --}}
                                <div class="absolute left-1/2 top-full z-50 mt-2 hidden min-w-[140px] -translate-x-1/2 rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-xl group-hover:block dark:bg-gray-800 md:block"
                                    x-show="!isMobile">
                                    <div class="space-y-0.5">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-gray-400">PPN:</span>
                                            <span class="font-semibold">{{ $monthReport->invoices_count ?? 0 }}</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-gray-400">PPh:</span>
                                            <span class="font-semibold">{{ $monthReport->income_taxs_count ?? 0
                                                }}</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-gray-400">Bupot:</span>
                                            <span class="font-semibold">{{ $monthReport->bupots_count ?? 0 }}</span>
                                        </div>
                                    </div>
                                    <div
                                        class="absolute -top-1 left-1/2 h-2 w-2 -translate-x-1/2 rotate-45 bg-gray-900 dark:bg-gray-800">
                                    </div>
                                </div>
                            </a>
                            @else
                            <div
                                class="flex flex-col items-center justify-center rounded-md md:rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 px-2 py-2 md:px-2 md:py-3 opacity-50 dark:border-gray-800 dark:bg-gray-900/20 flex-shrink-0 w-16 md:w-auto">
                                <span
                                    class="text-[9px] md:text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-600">
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

                    {{-- Legend - Responsive --}}
                    <div
                        class="mt-2 md:mt-3 flex flex-wrap items-center justify-center gap-x-3 md:gap-x-6 gap-y-1 md:gap-y-2 text-xs">
                        <div class="flex items-center gap-1 md:gap-1.5">
                            <div class="h-2 w-2 rounded-full bg-blue-500"></div>
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

        {{-- Tax Type Navigation Tabs - Responsive Redesign --}}
        <div class="mb-3 md:mb-4">
            <div
                class="overflow-hidden rounded-lg md:rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="p-2 md:p-3">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 md:gap-3">
                        {{-- PPN Tab --}}
                        <button @click="activeTab = 'invoices'"
                            :class="activeTab === 'invoices' 
                                    ? 'border-blue-500 bg-gradient-to-br from-blue-50 to-blue-100 shadow-lg shadow-blue-500/20 dark:border-blue-400 dark:from-blue-900/30 dark:to-blue-900/10' 
                                    : 'border-gray-200 bg-white hover:border-blue-300 hover:bg-blue-50/50 dark:border-gray-700 dark:bg-gray-800/50 dark:hover:border-blue-600'"
                            class="group relative flex flex-row md:flex-col items-center gap-3 rounded-lg md:rounded-xl border-2 px-3 py-3 md:px-4 md:py-4 transition-all duration-200">

                            {{-- Reporting Status Badge --}}
                            <div class="absolute -right-1 -top-1 md:-right-2 md:-top-2 z-10">
                                @if($ppnReported)
                                <div class="flex h-6 w-6 md:h-8 md:w-8 items-center justify-center rounded-full bg-green-500 shadow-lg ring-2 ring-white dark:ring-gray-900"
                                    title="Sudah Lapor">
                                    <x-filament::icon icon="heroicon-m-check-circle"
                                        class="h-4 w-4 md:h-5 md:w-5 text-white" />
                                </div>
                                @else
                                <div class="status-pulse flex h-6 w-6 md:h-8 md:w-8 items-center justify-center rounded-full bg-orange-500 shadow-lg ring-2 ring-white dark:ring-gray-900"
                                    title="Belum Lapor">
                                    <x-filament::icon icon="heroicon-m-exclamation-circle"
                                        class="h-4 w-4 md:h-5 md:w-5 text-white" />
                                </div>
                                @endif
                            </div>

                            {{-- Icon --}}
                            <div :class="activeTab === 'invoices' ? 'bg-blue-600 shadow-lg shadow-blue-500/50' : 'bg-gray-100 group-hover:bg-blue-100 dark:bg-gray-700'"
                                class="flex h-10 w-10 md:h-12 md:w-12 items-center justify-center rounded-lg md:rounded-xl transition-all duration-200 flex-shrink-0">
                                <x-filament::icon icon="heroicon-o-document-text"
                                    :class="activeTab === 'invoices' ? 'text-white' : 'text-gray-600 group-hover:text-blue-600 dark:text-gray-400'"
                                    class="h-5 w-5 md:h-6 md:w-6" />
                            </div>

                            {{-- Label & Count --}}
                            <div class="flex-1 text-left md:text-center">
                                <p :class="activeTab === 'invoices' ? 'text-blue-900 dark:text-blue-100' : 'text-gray-900 dark:text-white'"
                                    class="text-sm font-bold">
                                    PPN
                                </p>
                                <p :class="activeTab === 'invoices' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400'"
                                    class="mt-0.5 text-xs font-medium">
                                    {{ $record->invoices->count() }} Faktur
                                </p>
                                {{-- Status Text --}}
                                <p
                                    class="mt-1 text-[10px] font-semibold {{ $ppnReported ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">
                                    {{ $ppnReported ? '✓ Sudah Lapor' : '⚠ Belum Lapor' }}
                                </p>
                            </div>

                            {{-- Active Indicator - Desktop Only --}}
                            <div x-show="activeTab === 'invoices'"
                                class="absolute -bottom-1 left-1/2 -translate-x-1/2 hidden md:block">
                                <div class="h-1 w-16 rounded-full bg-blue-600 shadow-lg"></div>
                            </div>
                        </button>

                        {{-- PPh Tab --}}
                        <button @click="activeTab = 'pph'"
                            :class="activeTab === 'pph' 
                                    ? 'border-purple-500 bg-gradient-to-br from-purple-50 to-purple-100 shadow-lg shadow-purple-500/20 dark:border-purple-400 dark:from-purple-900/30 dark:to-purple-900/10' 
                                    : 'border-gray-200 bg-white hover:border-purple-300 hover:bg-purple-50/50 dark:border-gray-700 dark:bg-gray-800/50 dark:hover:border-purple-600'"
                            class="group relative flex flex-row md:flex-col items-center gap-3 rounded-lg md:rounded-xl border-2 px-3 py-3 md:px-4 md:py-4 transition-all duration-200">

                            {{-- Reporting Status Badge --}}
                            <div class="absolute -right-1 -top-1 md:-right-2 md:-top-2 z-10">
                                @if($pphReported)
                                <div class="flex h-6 w-6 md:h-8 md:w-8 items-center justify-center rounded-full bg-green-500 shadow-lg ring-2 ring-white dark:ring-gray-900"
                                    title="Sudah Lapor">
                                    <x-filament::icon icon="heroicon-m-check-circle"
                                        class="h-4 w-4 md:h-5 md:w-5 text-white" />
                                </div>
                                @else
                                <div class="status-pulse flex h-6 w-6 md:h-8 md:w-8 items-center justify-center rounded-full bg-orange-500 shadow-lg ring-2 ring-white dark:ring-gray-900"
                                    title="Belum Lapor">
                                    <x-filament::icon icon="heroicon-m-exclamation-circle"
                                        class="h-4 w-4 md:h-5 md:w-5 text-white" />
                                </div>
                                @endif
                            </div>

                            {{-- Icon --}}
                            <div :class="activeTab === 'pph' ? 'bg-purple-600 shadow-lg shadow-purple-500/50' : 'bg-gray-100 group-hover:bg-purple-100 dark:bg-gray-700'"
                                class="flex h-10 w-10 md:h-12 md:w-12 items-center justify-center rounded-lg md:rounded-xl transition-all duration-200 flex-shrink-0">
                                <x-filament::icon icon="heroicon-o-banknotes"
                                    :class="activeTab === 'pph' ? 'text-white' : 'text-gray-600 group-hover:text-purple-600 dark:text-gray-400'"
                                    class="h-5 w-5 md:h-6 md:w-6" />
                            </div>

                            {{-- Label & Count --}}
                            <div class="flex-1 text-left md:text-center">
                                <p :class="activeTab === 'pph' ? 'text-purple-900 dark:text-purple-100' : 'text-gray-900 dark:text-white'"
                                    class="text-sm font-bold">
                                    PPh
                                </p>
                                <p :class="activeTab === 'pph' ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500 dark:text-gray-400'"
                                    class="mt-0.5 text-xs font-medium">
                                    {{ $record->incomeTaxs->count() }} Transaksi
                                </p>
                                {{-- Status Text --}}
                                <p
                                    class="mt-1 text-[10px] font-semibold {{ $pphReported ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">
                                    {{ $pphReported ? '✓ Sudah Lapor' : '⚠ Belum Lapor' }}
                                </p>
                            </div>

                            {{-- Active Indicator - Desktop Only --}}
                            <div x-show="activeTab === 'pph'"
                                class="absolute -bottom-1 left-1/2 -translate-x-1/2 hidden md:block">
                                <div class="h-1 w-16 rounded-full bg-purple-600 shadow-lg"></div>
                            </div>
                        </button>

                        {{-- Bupot Tab --}}
                        <button @click="activeTab = 'bupot'"
                            :class="activeTab === 'bupot' 
                                    ? 'border-orange-500 bg-gradient-to-br from-orange-50 to-orange-100 shadow-lg shadow-orange-500/20 dark:border-orange-400 dark:from-orange-900/30 dark:to-orange-900/10' 
                                    : 'border-gray-200 bg-white hover:border-orange-300 hover:bg-orange-50/50 dark:border-gray-700 dark:bg-gray-800/50 dark:hover:border-orange-600'"
                            class="group relative flex flex-row md:flex-col items-center gap-3 rounded-lg md:rounded-xl border-2 px-3 py-3 md:px-4 md:py-4 transition-all duration-200">

                            {{-- Reporting Status Badge --}}
                            <div class="absolute -right-1 -top-1 md:-right-2 md:-top-2 z-10">
                                @if($bupotReported)
                                <div class="flex h-6 w-6 md:h-8 md:w-8 items-center justify-center rounded-full bg-green-500 shadow-lg ring-2 ring-white dark:ring-gray-900"
                                    title="Sudah Lapor">
                                    <x-filament::icon icon="heroicon-m-check-circle"
                                        class="h-4 w-4 md:h-5 md:w-5 text-white" />
                                </div>
                                @else
                                <div class="status-pulse flex h-6 w-6 md:h-8 md:w-8 items-center justify-center rounded-full bg-orange-500 shadow-lg ring-2 ring-white dark:ring-gray-900"
                                    title="Belum Lapor">
                                    <x-filament::icon icon="heroicon-m-exclamation-circle"
                                        class="h-4 w-4 md:h-5 md:w-5 text-white" />
                                </div>
                                @endif
                            </div>

                            {{-- Icon --}}
                            <div :class="activeTab === 'bupot' ? 'bg-orange-600 shadow-lg shadow-orange-500/50' : 'bg-gray-100 group-hover:bg-orange-100 dark:bg-gray-700'"
                                class="flex h-10 w-10 md:h-12 md:w-12 items-center justify-center rounded-lg md:rounded-xl transition-all duration-200 flex-shrink-0">
                                <x-filament::icon icon="heroicon-o-receipt-percent"
                                    :class="activeTab === 'bupot' ? 'text-white' : 'text-gray-600 group-hover:text-orange-600 dark:text-gray-400'"
                                    class="h-5 w-5 md:h-6 md:w-6" />
                            </div>

                            {{-- Label & Count --}}
                            <div class="flex-1 text-left md:text-center">
                                <p :class="activeTab === 'bupot' ? 'text-orange-900 dark:text-orange-100' : 'text-gray-900 dark:text-white'"
                                    class="text-sm font-bold">
                                    <span class="md:hidden">Bupot</span>
                                    <span class="hidden md:inline">PPh Unifikasi</span>
                                </p>
                                <p :class="activeTab === 'bupot' ? 'text-orange-600 dark:text-orange-400' : 'text-gray-500 dark:text-gray-400'"
                                    class="mt-0.5 text-xs font-medium">
                                    {{ $record->bupots->count() }} Bupot
                                </p>
                                {{-- Status Text --}}
                                <p
                                    class="mt-1 text-[10px] font-semibold {{ $bupotReported ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">
                                    {{ $bupotReported ? '✓ Sudah Lapor' : '⚠ Belum Lapor' }}
                                </p>
                            </div>

                            {{-- Active Indicator - Desktop Only --}}
                            <div x-show="activeTab === 'bupot'"
                                class="absolute -bottom-1 left-1/2 -translate-x-1/2 hidden md:block">
                                <div class="h-1 w-16 rounded-full bg-orange-600 shadow-lg"></div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Content - Responsive --}}
        <div class="space-y-4">
            {{-- PPN Content --}}
            <div x-show="activeTab === 'invoices'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
                @livewire('tax-report.components.tax-report-invoices', ['taxReportId' => $record->id])
            </div>

            {{-- PPh Content --}}
            <div x-show="activeTab === 'pph'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
                @livewire('tax-report.components.tax-report-pph', ['taxReportId' => $record->id])
            </div>

            {{-- Bupot Content --}}
            <div x-show="activeTab === 'bupot'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
                @livewire('tax-report.components.tax-report-bupot', ['taxReportId' => $record->id])
            </div>
        </div>

    </div>
</x-filament::page>