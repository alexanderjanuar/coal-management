<x-filament-panels::page>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap');

        .tax-filter-container {
            font-family: 'DM Sans', system-ui, -apple-system, sans-serif;
        }

        .stat-badge {
            font-variant-numeric: tabular-nums;
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    <div class="tax-filter-container">
        {{-- Single Row Filter: Year Dropdown | Month Pills --}}
        <div class="mb-6 p-4 bg-gradient-to-b from-gray-50/50 to-white dark:from-gray-800/50 dark:to-gray-900 rounded-xl border border-gray-200/60 dark:border-gray-700/60 shadow-sm">
            <div class="flex items-center gap-4 flex-wrap">
                {{-- Year Dropdown Pill --}}
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open"
                        class="inline-flex items-center gap-2.5 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg font-semibold text-sm text-gray-900 dark:text-gray-100 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md transition-all duration-200 shadow-sm">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="font-bold">{{ $selectedYear ?? now()->year }}</span>
                        @php
                        $yearCount = \App\Models\TaxReport::where('year', $selectedYear ?? now()->year)->count();
                        @endphp
                        @if($yearCount > 0)
                        <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            {{ $yearCount }}
                        </span>
                        @endif
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown Menu --}}
                    <div x-show="open" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute left-0 mt-2 w-52 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl overflow-hidden z-50 ring-1 ring-black/5"
                        style="display: none;">
                        <div class="py-2 max-h-64 overflow-y-auto">
                            @foreach($this->getAvailableYears() as $year)
                            @php
                            $count = \App\Models\TaxReport::where('year', $year)->count();
                            $isSelected = $selectedYear == $year;
                            $isCurrent = $year == now()->year;
                            @endphp

                            <button wire:click="$set('selectedYear', {{ $year }})" @click="open = false"
                                @class([
                                    'w-full flex items-center justify-between px-4 py-3 text-sm transition-all duration-150 group',
                                    'bg-gray-50 text-gray-900 font-semibold dark:bg-gray-700/50 dark:text-white' => $isSelected,
                                    'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/30' => !$isSelected,
                                ])
                                >
                                <div class="flex items-center gap-2.5">
                                    <span class="font-semibold">{{ $year }}</span>
                                    @if($isCurrent)
                                    <span class="flex h-2 w-2">
                                        <span class="absolute inline-flex h-2 w-2 rounded-full bg-green-400 opacity-75 {{ !$isSelected ? 'animate-ping' : '' }}"></span>
                                        <span class="relative inline-flex h-2 w-2 rounded-full bg-green-500"></span>
                                    </span>
                                    @endif
                                </div>
                                @if($count > 0)
                                <span @class([
                                    'text-xs px-2.5 py-1 rounded-md font-bold',
                                    'bg-gray-200 text-gray-900 dark:bg-gray-600 dark:text-white' => $isSelected,
                                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 group-hover:bg-gray-200 dark:group-hover:bg-gray-600' => !$isSelected,
                                ])>
                                    {{ $count }}
                                </span>
                                @endif
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Divider --}}
                @if($selectedYear)
                <div class="h-8 w-px bg-gradient-to-b from-transparent via-gray-300 to-transparent dark:via-gray-600"></div>

                {{-- Month Pills --}}
                <div class="flex items-center gap-2 flex-wrap">
                    @php
                    $months = [
                    'January' => 'JAN',
                    'February' => 'FEB',
                    'March' => 'MAR',
                    'April' => 'APR',
                    'May' => 'MEI',
                    'June' => 'JUN',
                    'July' => 'JUL',
                    'August' => 'AGU',
                    'September' => 'SEP',
                    'October' => 'OKT',
                    'November' => 'NOV',
                    'December' => 'DES'
                    ];
                    $currentMonth = now()->format('F');
                    @endphp

                    @foreach($months as $monthFull => $monthShort)
                    @php
                    $count = \App\Models\TaxReport::where('year', $selectedYear)->where('month', $monthFull)->count();
                    $isSelected = $selectedMonth == $monthFull;
                    $isCurrent = $monthFull == $currentMonth && $selectedYear == now()->year;
                    $hasData = $count > 0;
                    @endphp

                    <button wire:click="$set('selectedMonth', '{{ $monthFull }}')" @if(!$hasData) disabled @endif
                        @class([
                            'relative inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold transition-all duration-200 group',
                            'bg-gray-900 text-white shadow-md hover:shadow-lg hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white' => $isSelected && $hasData,
                            'bg-white text-gray-700 border border-gray-200 hover:border-gray-300 hover:shadow-sm dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:border-gray-600' => !$isSelected && $hasData,
                            'bg-gray-50/50 text-gray-300 border border-gray-100 cursor-not-allowed dark:bg-gray-900/50 dark:text-gray-600 dark:border-gray-800' => !$hasData,
                        ])
                        >
                        @if($isCurrent && $hasData)
                        <span class="flex h-2 w-2 absolute -top-1 -right-1">
                            <span class="absolute inline-flex h-full w-full rounded-full {{ $isSelected ? 'bg-white opacity-75' : 'bg-green-500 opacity-75 animate-ping' }}"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full {{ $isSelected ? 'bg-white' : 'bg-green-500' }}"></span>
                        </span>
                        @endif
                        <span class="font-bold tracking-wide">{{ $monthShort }}</span>
                        @if($hasData)
                        <span @class([
                            'px-2 py-0.5 rounded-md text-xs font-bold leading-none',
                            'bg-gray-800 text-gray-100 dark:bg-white dark:text-gray-900' => $isSelected,
                            'bg-gray-100 text-gray-600 group-hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:group-hover:bg-gray-600' => !$isSelected,
                        ])>
                            {{ $count }}
                        </span>
                        @endif
                    </button>
                    @endforeach

                    {{-- Reset Month Filter Button --}}
                    @if($selectedMonth)
                    <button wire:click="$set('selectedMonth', null)"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-all duration-200 border border-gray-200 dark:border-gray-600"
                        title="Reset month filter">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Reset</span>
                    </button>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Table with smooth transition --}}
    <div class="animate-in fade-in duration-500">
        {{ $this->table }}
    </div>
</x-filament-panels::page>