<div
    class="w-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-[#181717] rounded-xl shadow-sm overflow-hidden transition-colors duration-200 h-max-[1000px]">
    <div class="p-4 md:p-6">
        <!-- Calendar Section -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center">
                    <button wire:click="goToPreviousMonth"
                        class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    <h2 class="text-xl font-medium px-4 text-gray-900 dark:text-white">{{
                        $currentDate->translatedFormat('F Y') }}</h2>
                    <button wire:click="goToNextMonth"
                        class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <div class="flex items-center">
                    <div class="p-2 bg-amber-100 dark:bg-amber-900/50 rounded-lg mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600 dark:text-amber-400"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Kalender Pajak</h2>
                </div>
            </div>

            <!-- Calendar Header -->
            <div class="grid grid-cols-7 gap-1 border-b border-gray-200 dark:border-gray-600 pb-3 mb-3">
                @foreach(['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $day)
                <div class="text-center text-gray-500 dark:text-gray-400 font-medium text-sm py-2">
                    {{ $day }}
                </div>
                @endforeach
            </div>

            <!-- Calendar Grid -->
            <div class="grid grid-cols-7 gap-1">
                @foreach($calendarDays as $day)
                <div wire:key="day-{{ $day['date'] }}" class="
                        relative flex flex-col items-center h-24 w-full text-lg font-medium 
                        cursor-pointer transition-all duration-200 rounded-lg group
                        {{ !$day['isCurrentMonth'] ? 'text-gray-300 dark:text-gray-600' : 'text-gray-700 dark:text-gray-200' }}
                        {{ $day['isToday'] ? 'ring-2 ring-blue-400 dark:ring-blue-500 bg-blue-50 dark:bg-blue-900/20' : '' }}
                        {{ $selectedDate === $day['date'] ? 'bg-cyan-100 dark:bg-cyan-900/30 ring-2 ring-cyan-400 dark:ring-cyan-500' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50' }}
                    " wire:click="selectDate('{{ $day['date'] }}')">

                    <!-- Day Number -->
                    <span class="mt-1 {{ $day['isToday'] ? 'text-blue-600 dark:text-blue-400 font-bold' : '' }}">
                        {{ $day['day'] }}
                    </span>

                    <!-- Pending Clients Count -->
                    @if($day['pendingClientsCount'] > 0)
                    <div class="absolute bottom-2 flex items-center justify-center">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-semibold 
                                   bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300 
                                   ring-1 ring-red-200 dark:ring-red-800">
                            {{ $day['pendingClientsCount'] }}
                        </span>
                    </div>
                    @endif

                    <!-- Event Indicator -->
                    @if($day['hasEvent'])
                    <div class="absolute bottom-1 left-1/2 transform -translate-x-1/2">
                        <div
                            class="h-1.5 w-8 bg-gradient-to-r from-yellow-400 to-amber-500 dark:from-yellow-500 dark:to-amber-600 rounded-full">
                        </div>
                    </div>
                    @endif

                    <!-- Hover Effect Overlay -->
                    <div
                        class="absolute inset-0 bg-gray-100 dark:bg-gray-600 opacity-0 group-hover:opacity-10 rounded-lg transition-opacity duration-200">
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Tax Schedule Section -->
        <div>
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Jadwal Pajak Bulan Ini</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $currentDate->translatedFormat('F Y') }}
                        </p>
                    </div>
                </div>

                <!-- Quick Stats -->
                @php
                $currentMonthEvents = $this->getTaxSchedule();
                $highPriorityCount = collect($currentMonthEvents)->where('priority', 'high')->count();
                $totalEvents = count($currentMonthEvents);
                @endphp

                @if($totalEvents > 0)
                <div class="flex items-center space-x-3">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $highPriorityCount }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Prioritas Tinggi</div>
                    </div>
                    <div class="w-px h-8 bg-gray-300 dark:bg-gray-600"></div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $totalEvents }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Total Jadwal</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @include('components.modal.tax-report.tax-events-modal')
    @include('components.modal.tax-report.pending-clients-modal')

</div>