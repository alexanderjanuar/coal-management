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

        /* Kartu tampil flat — buang box/border/bg/shadow container tabel Filament. */
        .tax-reports-table .fi-ta-ctn,
        .tax-reports-table .fi-ta {
            background-color: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            border-radius: 0 !important;
        }

        /* HAPUS container di atas kartu: bar "Active filters", indikator seleksi,
           dan baris select-all + "Sort by" (sort dipindah ke dropdown custom). */
        .tax-reports-table .fi-ta-filter-indicators,
        .tax-reports-table .fi-ta-selection-indicator,
        .tax-reports-table .gap-x-6.bg-gray-50 {
            display: none !important;
        }

        /* Toolbar bawaan Filament (search + filter) disembunyikan — diganti toolbar custom
           (Search + Filter + Urutkan) di atas. Sekalian buang divider/garis di bawahnya. */
        .tax-reports-table .fi-ta-header-toolbar,
        .tax-reports-table .fi-ta-header {
            display: none !important;
        }
        .tax-reports-table .fi-ta-content {
            border-top: 0 !important;
        }

        /* Pagination → footer rapi: jarak dari kartu + garis pemisah tipis. */
        .tax-reports-table .fi-ta-pagination {
            font-family: 'DM Sans', system-ui, -apple-system, sans-serif;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgb(229 231 235) !important;
        }
        .dark .tax-reports-table .fi-ta-pagination {
            border-top-color: rgb(255 255 255 / 0.06) !important;
        }
    </style>

    <div class="tax-filter-container">
        {{-- Single Row Filter: Year Dropdown | Month Pills --}}
        <div
            class="mb-6 p-4 bg-gradient-to-b from-gray-50/50 to-white dark:from-gray-800/50 dark:to-gray-900 rounded-xl border border-gray-200/60 dark:border-gray-700/60 shadow-sm">
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
                        <span
                            class="px-2 py-0.5 rounded-md text-xs font-bold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            {{ $yearCount }}
                        </span>
                        @endif
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                            :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                @class([ 'w-full flex items-center justify-between px-4 py-3 text-sm transition-all duration-150 group'
                                , 'bg-gray-50 text-gray-900 font-semibold dark:bg-gray-700/50 dark:text-white'=>
                                $isSelected,
                                'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/30' =>
                                !$isSelected,
                                ])
                                >
                                <div class="flex items-center gap-2.5">
                                    <span class="font-semibold">{{ $year }}</span>
                                    @if($isCurrent)
                                    <span class="flex h-2 w-2">
                                        <span
                                            class="absolute inline-flex h-2 w-2 rounded-full bg-green-400 opacity-75 {{ !$isSelected ? 'animate-ping' : '' }}"></span>
                                        <span class="relative inline-flex h-2 w-2 rounded-full bg-green-500"></span>
                                    </span>
                                    @endif
                                </div>
                                @if($count > 0)
                                <span @class([ 'text-xs px-2.5 py-1 rounded-md font-bold'
                                    , 'bg-gray-200 text-gray-900 dark:bg-gray-600 dark:text-white'=> $isSelected,
                                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400
                                    group-hover:bg-gray-200 dark:group-hover:bg-gray-600' => !$isSelected,
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
                <div class="h-8 w-px bg-gradient-to-b from-transparent via-gray-300 to-transparent dark:via-gray-600">
                </div>

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
                        @class([ 'relative inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold transition-all duration-200 group'
                        , 'bg-teal-600 text-white shadow-md hover:shadow-lg hover:bg-teal-700 dark:bg-teal-500 dark:text-white dark:hover:bg-teal-600'=>
                        $isSelected && $hasData,
                        'bg-white text-gray-700 hover:bg-gray-50 hover:shadow-sm dark:bg-gray-800 dark:text-gray-300
                        dark:hover:bg-gray-700/50' => !$isSelected && $hasData,
                        'bg-gray-50/50 text-gray-300 cursor-not-allowed dark:bg-gray-900/50 dark:text-gray-600' =>
                        !$hasData,
                        ])
                        >
                        @if($isCurrent && $hasData)
                        <span class="flex h-2 w-2 absolute -top-1 -right-1">
                            <span
                                class="absolute inline-flex h-full w-full rounded-full {{ $isSelected ? 'bg-white opacity-75' : 'bg-green-500 opacity-75 animate-ping' }}"></span>
                            <span
                                class="relative inline-flex h-2 w-2 rounded-full {{ $isSelected ? 'bg-white' : 'bg-green-500' }}"></span>
                        </span>
                        @endif
                        <span class="font-bold tracking-wide">{{ $monthShort }}</span>
                        @if($hasData)
                        <span @class([ 'px-2 py-0.5 rounded-md text-xs font-bold leading-none'
                            , 'bg-teal-700 text-teal-50 dark:bg-teal-400 dark:text-teal-950'=> $isSelected,
                            'bg-gray-100 text-gray-600 group-hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400
                            dark:group-hover:bg-gray-600' => !$isSelected,
                            ])>
                            {{ $count }}
                        </span>
                        @endif
                    </button>
                    @endforeach

                    {{-- Reset Month Filter Button --}}
                    @if($selectedMonth)
                    <button wire:click="$set('selectedMonth', null)"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-all duration-200"
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

    {{-- Toolbar custom: Search + Filter + Urutkan (satu baris, menggantikan chrome tabel Filament). --}}
    @php
        // ── Sort ──
        $sortCol = $this->getTableSortColumn();
        $sortDir = $this->getTableSortDirection();
        $sortOptions = [
            ['label' => 'Default',    'col' => null,          'dir' => null],
            ['label' => 'Client A–Z', 'col' => 'client.name', 'dir' => 'asc'],
            ['label' => 'Client Z–A', 'col' => 'client.name', 'dir' => 'desc'],
            ['label' => 'Periode',    'col' => 'month',        'dir' => 'asc'],
        ];
        $current = collect($sortOptions)->first(fn ($o) => $o['col'] === $sortCol && ($o['col'] === null || $o['dir'] === $sortDir));
        $sortLabel = $current['label'] ?? 'Default';

        // ── Filter (hitung yang aktif di luar default, untuk badge) ──
        $tf       = $this->tableFilters ?? [];
        $fPpn     = $tf['ppn_report_status']['value'] ?? null;
        $fBayar   = $tf['payment_status']['values'] ?? [];
        $fKontrak = (bool) ($tf['has_contracts']['isActive'] ?? false);
        $fKlien   = $tf['client_status']['values'] ?? [];
        $fJenis   = $tf['contract_types']['values'] ?? [];
        $filterCount = ($fPpn ? 1 : 0)
            + (count($fBayar) ? 1 : 0)
            + (! $fKontrak ? 1 : 0)
            + ((count($fKlien) && $fKlien !== ['Active']) ? 1 : 0)
            + (count($fJenis) ? 1 : 0);
    @endphp
    <div class="tax-filter-container mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        {{-- Search --}}
        <div class="relative sm:flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m21 21-4.3-4.3" />
            </svg>
            <input type="search" wire:model.live.debounce.400ms="tableSearch" placeholder="Cari client..."
                   class="w-full rounded-lg border border-gray-200 bg-white py-2 pl-9 pr-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500">
        </div>

        <div class="flex items-center gap-2 sm:justify-end">
            {{-- Filter dropdown (desain seragam dengan Urutkan) --}}
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button @click="open = !open" type="button"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:border-gray-600">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18l-7 8v5l-4 2v-7L3 5z" />
                    </svg>
                    <span>Filter</span>
                    @if($filterCount)
                        <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-teal-600 px-1 text-xs font-bold text-white">{{ $filterCount }}</span>
                    @endif
                    <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-cloak
                     x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 z-50 mt-2 w-72 rounded-xl border border-gray-200 bg-white p-4 shadow-xl ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800"
                     style="display: none;">
                    <label class="flex items-center justify-between gap-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                        Kontrak Aktif
                        <input type="checkbox" wire:model.live="tableFilters.has_contracts.isActive"
                               class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500 dark:border-gray-600 dark:bg-gray-700">
                    </label>

                    <div class="mt-4">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Jenis Kontrak</div>
                        <div class="flex flex-wrap gap-x-4 gap-y-2">
                            @foreach(['ppn_contract' => 'PPN', 'pph_contract' => 'PPh', 'bupot_contract' => 'Bupot', 'pph_badan_contract' => 'PPh Badan'] as $val => $lbl)
                                <label class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" value="{{ $val }}" wire:model.live="tableFilters.contract_types.values"
                                           class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500 dark:border-gray-600 dark:bg-gray-700">{{ $lbl }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Status Klien</div>
                        <div class="flex flex-wrap gap-x-4 gap-y-2">
                            @foreach(['Active' => 'Aktif', 'Inactive' => 'Nonaktif'] as $val => $lbl)
                                <label class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" value="{{ $val }}" wire:model.live="tableFilters.client_status.values"
                                           class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500 dark:border-gray-600 dark:bg-gray-700">{{ $lbl }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Status Lapor PPN</div>
                        <select wire:model.live="tableFilters.ppn_report_status.value"
                                class="w-full rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-sm text-gray-700 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500/30 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-200">
                            <option value="">Semua</option>
                            <option value="Sudah Lapor">Sudah Lapor</option>
                            <option value="Belum Lapor">Belum Lapor</option>
                        </select>
                    </div>

                    <div class="mt-4">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Status Bayar</div>
                        <div class="flex flex-wrap gap-x-4 gap-y-2">
                            @foreach(['Lebih Bayar', 'Kurang Bayar', 'Nihil'] as $val)
                                <label class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" value="{{ $val }}" wire:model.live="tableFilters.payment_status.values"
                                           class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500 dark:border-gray-600 dark:bg-gray-700">{{ $val }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <button type="button" wire:click="resetTableFiltersForm" @click="open = false"
                        class="mt-4 w-full rounded-lg border border-gray-200 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700/40">
                        Reset filter
                    </button>
                </div>
            </div>

            {{-- Urutkan dropdown --}}
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button @click="open = !open" type="button"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:border-gray-600">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h12M3 12h9M3 17h6M17 8l4 4-4 4" />
                </svg>
                <span>{{ $sortLabel }}</span>
                <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 z-50 mt-2 w-48 overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-xl ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800"
                 style="display: none;">
                @foreach($sortOptions as $opt)
                    @php $active = $opt['label'] === $sortLabel; @endphp
                    <button type="button" @click="open = false"
                        wire:click="sortTable(@js($opt['col']), @js($opt['dir']))"
                        @class([
                            'flex w-full items-center justify-between px-4 py-2.5 text-sm transition',
                            'bg-gray-50 font-semibold text-teal-600 dark:bg-gray-700/40 dark:text-teal-400' => $active,
                            'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/30' => ! $active,
                        ])>
                        {{ $opt['label'] }}
                        @if($active)
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" /></svg>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
        </div>
    </div>

    {{-- Tabel: kartu di-render flat tanpa container Filament (lihat .tax-reports-table) --}}
    <div class="tax-reports-table animate-in fade-in duration-500">
        {{ $this->table }}
    </div>
</x-filament-panels::page>