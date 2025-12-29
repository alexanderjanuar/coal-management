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
        <div class="mb-6 flex items-center gap-4 flex-wrap">
            {{-- Year Dropdown Pill --}}
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button @click="open = !open"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg font-medium text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 shadow-sm">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="font-semibold">{{ $selectedYear ?? now()->year }}</span>
                    @php
                    $yearCount = \App\Models\TaxReport::where('year', $selectedYear ?? now()->year)->count();
                    @endphp
                    @if($yearCount > 0)
                    <span
                        class="px-2 py-0.5 rounded-md text-xs font-semibold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                        {{ $yearCount }}
                    </span>
                    @endif
                    <svg class="w-4 h-4 text-gray-500 transition-transform" :class="{ 'rotate-180': open }" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {{-- Dropdown Menu --}}
                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                    class="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg overflow-hidden z-50"
                    style="display: none;">
                    <div class="py-1 max-h-64 overflow-y-auto">
                        @foreach($this->getAvailableYears() as $year)
                        @php
                        $count = \App\Models\TaxReport::where('year', $year)->count();
                        $isSelected = $selectedYear == $year;
                        $isCurrent = $year == now()->year;
                        @endphp

                        <button wire:click="$set('selectedYear', {{ $year }})" @click="open = false"
                            @class([ 'w-full flex items-center justify-between px-4 py-2.5 text-sm transition-colors'
                            , 'bg-primary-50 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300'=>
                            $isSelected,
                            'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700' => !$isSelected,
                            ])
                            >
                            <div class="flex items-center gap-2">
                                <span class="font-semibold">{{ $year }}</span>
                                @if($isCurrent)
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                @endif
                            </div>
                            @if($count > 0)
                            <span @class([ 'text-xs px-2 py-0.5 rounded-md'
                                , 'bg-primary-200 text-primary-800 dark:bg-primary-800 dark:text-primary-200'=>
                                $isSelected,
                                'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400' => !$isSelected,
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
            <div class="h-6 w-px bg-gray-300 dark:bg-gray-600"></div>

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
                    @class([ 'inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-200'
                    , 'bg-primary-600 text-white shadow-sm hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600'=>
                    $isSelected && $hasData,
                    'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300
                    dark:border-gray-600 dark:hover:bg-gray-700' => !$isSelected && $hasData,
                    'bg-gray-50 text-gray-300 border border-gray-200 cursor-not-allowed dark:bg-gray-900
                    dark:text-gray-600 dark:border-gray-800' => !$hasData,
                    ])
                    >
                    @if($isCurrent && $hasData)
                    <span @class([ 'w-1.5 h-1.5 rounded-full' , 'bg-white'=> $isSelected,
                        'bg-green-500' => !$isSelected,
                        ])></span>
                    @endif
                    <span>{{ $monthShort }}</span>
                    @if($hasData)
                    <span @class([ 'px-1.5 py-0.5 rounded-md text-xs font-semibold'
                        , 'bg-primary-700 text-primary-100 dark:bg-primary-400 dark:text-primary-900'=> $isSelected,
                        'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' => !$isSelected,
                        ])>
                        {{ $count }}
                    </span>
                    @endif
                </button>
                @endforeach

                {{-- Reset Month Filter Button --}}
                @if($selectedMonth)
                <button wire:click="$set('selectedMonth', null)"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-gray-600 transition-colors"
                    title="Reset month filter">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Reset</span>
                </button>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Table with smooth transition --}}
    <div class="animate-in fade-in duration-500">
        {{ $this->table }}
    </div>
</x-filament-panels::page>