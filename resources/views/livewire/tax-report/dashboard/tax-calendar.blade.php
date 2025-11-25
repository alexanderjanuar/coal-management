<div class="relative">
    <!-- Main Calendar Card -->
    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

        <!-- Calendar Header -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <!-- Month Navigation -->
                <div class="flex items-center gap-3">
                    <button wire:click="goToPreviousMonth"
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <div class="text-center min-w-[180px]">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ $currentDate->translatedFormat('F Y') }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            Kalender Pajak
                        </p>
                    </div>

                    <button wire:click="goToNextMonth"
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <!-- Quick Stats -->
                @php
                $currentMonthEvents = $this->getTaxSchedule();
                $highPriorityCount = collect($currentMonthEvents)->where('priority', 'high')->count();
                $totalEvents = count($currentMonthEvents);
                @endphp

                <div class="flex items-center gap-6">
                    <!-- High Priority -->
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-red-50 dark:bg-red-900/20">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $highPriorityCount }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Prioritas Tinggi</div>
                        </div>
                    </div>

                    <div class="w-px h-10 bg-gray-200 dark:bg-gray-700"></div>

                    <!-- Total Events -->
                    <div class="flex items-center gap-2">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $totalEvents }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Total Jadwal</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="p-6">
            <!-- Day Headers -->
            <div class="grid grid-cols-7 gap-2 mb-2">
                @foreach(['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $day)
                <div
                    class="text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider py-2">
                    {{ $day }}
                </div>
                @endforeach
            </div>

            <!-- Calendar Days -->
            <div class="grid grid-cols-7 gap-2">
                @foreach($calendarDays as $day)
                @php
                // Determine if this is a designated tax date
                $dayNumber = (int) $day['day'];
                $isPPhReportDay = $dayNumber === 10; // PPh 21 & PPh Unifikasi report deadline
                $isPPNReportDay = $dayNumber === 20; // PPN report deadline
                $isPaymentWarningDay = $dayNumber === 30; // Final payment warning
                $isDesignatedDate = $isPPhReportDay || $isPPNReportDay || $isPaymentWarningDay;

                // Get tooltip text
                $tooltipText = '';
                if ($isPPhReportDay && $day['isCurrentMonth']) {
                $tooltipText = 'Batas Lapor PPh 21 & PPh Unifikasi';
                } elseif ($isPPNReportDay && $day['isCurrentMonth']) {
                $tooltipText = 'Batas Lapor PPN';
                } elseif ($isPaymentWarningDay && $day['isCurrentMonth']) {
                $tooltipText = 'Batas Bayar PPN, PPh 21 & PPh Unifikasi (Peringatan Terakhir)';
                }
                @endphp

                <button wire:key="day-{{ $day['date'] }}" wire:click="selectDate('{{ $day['date'] }}')"
                    title="{{ $tooltipText }}" @class([ 'relative group h-20 rounded-lg transition-all duration-200' ,
                    // Base styles 'hover:bg-gray-50 dark:hover:bg-gray-700/50'=> $day['isCurrentMonth'] &&
                    !$isDesignatedDate,

                    // Designated dates - more prominent
                    'bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/30 dark:to-orange-900/30 border-2
                    border-amber-300 dark:border-amber-600 shadow-md hover:shadow-lg' => $isDesignatedDate &&
                    $day['isCurrentMonth'] && !$day['isToday'] && $selectedDate !== $day['date'],

                    // Today state
                    'bg-blue-50 dark:bg-blue-900/20 ring-2 ring-blue-500 dark:ring-blue-400' => $day['isToday'] &&
                    !$isDesignatedDate,
                    'bg-gradient-to-br from-blue-100 to-amber-100 dark:from-blue-900/40 dark:to-amber-900/40 ring-2
                    ring-blue-500 dark:ring-blue-400 shadow-lg' => $day['isToday'] && $isDesignatedDate,

                    // Selected state
                    'bg-cyan-50 dark:bg-cyan-900/20 ring-2 ring-cyan-500 dark:ring-cyan-400' => $selectedDate ===
                    $day['date'] && !$isDesignatedDate,
                    'bg-gradient-to-br from-cyan-100 to-amber-100 dark:from-cyan-900/40 dark:to-amber-900/40 ring-2
                    ring-cyan-500 dark:ring-cyan-400 shadow-lg' => $selectedDate === $day['date'] && $isDesignatedDate,

                    // Not current month
                    'opacity-40' => !$day['isCurrentMonth'],
                    ])
                    >
                    <!-- Day Number -->
                    <div class="absolute top-2 left-2">
                        <span @class([ 'text-sm font-medium' , 'text-gray-900 dark:text-white'=> $day['isCurrentMonth']
                            && !$isDesignatedDate,
                            'text-amber-700 dark:text-amber-300 font-bold' => $isDesignatedDate &&
                            $day['isCurrentMonth'],
                            'text-gray-400 dark:text-gray-600' => !$day['isCurrentMonth'],
                            'text-blue-600 dark:text-blue-400 font-bold' => $day['isToday'] && !$isDesignatedDate,
                            ])>
                            {{ $day['day'] }}
                        </span>
                    </div>

                    <!-- Tax Event Type Badge (for designated dates) -->
                    @if($isDesignatedDate && $day['isCurrentMonth'])
                    <div class="absolute top-2 right-2">
                        @if($isPPhReportDay)
                        <div
                            class="flex items-center justify-center w-5 h-5 rounded-full bg-purple-500 dark:bg-purple-600 shadow-sm">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        @elseif($isPPNReportDay)
                        <div
                            class="flex items-center justify-center w-5 h-5 rounded-full bg-blue-500 dark:bg-blue-600 shadow-sm">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        @elseif($isPaymentWarningDay)
                        <div
                            class="flex items-center justify-center w-5 h-5 rounded-full bg-red-500 dark:bg-red-600 shadow-sm">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Designated Date Label (shown on hover) -->
                    @if($isDesignatedDate && $day['isCurrentMonth'])
                    <div
                        class="absolute inset-x-0 top-8 px-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <div class="text-[10px] font-medium text-center leading-tight">
                            @if($isPPhReportDay)
                            <span class="text-purple-700 dark:text-purple-300">PPh</span>
                            @elseif($isPPNReportDay)
                            <span class="text-blue-700 dark:text-blue-300">PPN</span>
                            @elseif($isPaymentWarningDay)
                            <span class="text-red-700 dark:text-red-300">Bayar</span>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Event Indicators -->
                    <div class="absolute bottom-2 left-0 right-0 flex items-center justify-center gap-1">
                        @if($day['hasEvent'] && !$isDesignatedDate)
                        <div class="w-1.5 h-1.5 rounded-full bg-amber-500 dark:bg-amber-400"></div>
                        @endif

                        @if($day['pendingClientsCount'] > 0)
                        <span
                            class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-xs font-semibold bg-red-500 text-white shadow-sm">
                            {{ $day['pendingClientsCount'] }}
                        </span>
                        @endif
                    </div>

                    <!-- Hover Overlay -->
                    @if($day['isCurrentMonth'])
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-transparent to-gray-100/50 dark:to-gray-700/50 opacity-0 group-hover:opacity-100 rounded-lg transition-opacity">
                    </div>
                    @endif
                </button>
                @endforeach
            </div>
        </div>

        <!-- Legend -->
        <div class="px-6 pb-6">
            <div class="flex flex-wrap items-center gap-6 text-xs text-gray-600 dark:text-gray-400">
                <div class="flex items-center gap-2">
                    <div
                        class="w-5 h-5 rounded bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/30 dark:to-orange-900/30 border-2 border-amber-300 dark:border-amber-600">
                    </div>
                    <span>Tanggal Penting Pajak</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex items-center justify-center w-5 h-5 rounded-full bg-purple-500">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6" />
                        </svg>
                    </div>
                    <span>Lapor PPh (Tgl 10)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex items-center justify-center w-5 h-5 rounded-full bg-blue-500">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6" />
                        </svg>
                    </div>
                    <span>Lapor PPN (Tgl 20)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex items-center justify-center w-5 h-5 rounded-full bg-red-500">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2" />
                        </svg>
                    </div>
                    <span>Bayar Semua (Tgl 30)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div
                        class="flex items-center justify-center w-5 h-5 rounded-full bg-red-500 text-white text-xs font-semibold">
                        #
                    </div>
                    <span>Klien Belum Lapor/Bayar</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded ring-2 ring-blue-500"></div>
                    <span>Hari Ini</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @include('components.modal.tax-report.tax-events-modal')
    @include('components.modal.tax-report.pending-clients-modal')
</div>