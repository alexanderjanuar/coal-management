<div class="space-y-6 md:space-y-8" x-data="{ openMonth: null }">
    {{-- Header with Export Button --}}
    <div>
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-lg md:text-xl font-semibold text-gray-900 dark:text-gray-100">
                    Riwayat Laporan PPN {{ $timelineData['year'] }}
                </h2>
                <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Ringkasan perhitungan pajak bulanan tahun {{ $timelineData['year'] }}
                </p>
            </div>
            
            <div class="flex items-center gap-2">
                {{-- Year Stats Badge --}}
                <div class="inline-flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ $timelineData['statistics']['months_with_reports'] }}/12 Bulan
                    </span>
                </div>

                {{-- Export PDF Button --}}
                <button 
                    wire:click="exportYearlyReportPdf"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold text-sm rounded-lg transition-all duration-200 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed dark:bg-red-500 dark:hover:bg-red-600"
                >
                    <svg wire:loading.remove wire:target="exportYearlyReportPdf" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <svg wire:loading wire:target="exportYearlyReportPdf" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="exportYearlyReportPdf">Export PDF</span>
                    <span wire:loading wire:target="exportYearlyReportPdf">Exporting...</span>
                </button>

                {{-- Export Excel Button --}}
                <button 
                    wire:click="exportYearlyReport"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-semibold text-sm rounded-lg transition-all duration-200 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed dark:bg-primary-500 dark:hover:bg-primary-600"
                >
                    <svg wire:loading.remove wire:target="exportYearlyReport" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <svg wire:loading wire:target="exportYearlyReport" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="exportYearlyReport">Export Excel</span>
                    <span wire:loading wire:target="exportYearlyReport">Exporting...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="relative space-y-6 md:space-y-8">
        @foreach($timelineData['timeline'] as $item)
            <div class="relative flex gap-4 md:gap-6" x-data="{ isOpen: {{ $item['is_current'] ? 'true' : 'false' }} }">
                {{-- Timeline Indicator --}}
                <div class="flex flex-col items-center flex-shrink-0">
                    @php
                        $statusColor = match($item['ppn_summary']?->status_final) {
                            'Lebih Bayar' => 'bg-green-500 dark:bg-green-600',
                            'Kurang Bayar' => 'bg-red-500 dark:bg-red-600',
                            default => 'bg-gray-400 dark:bg-gray-600',
                        };
                        $ringColor = match($item['ppn_summary']?->status_final) {
                            'Lebih Bayar' => 'ring-green-100 dark:ring-green-900/30',
                            'Kurang Bayar' => 'ring-red-100 dark:ring-red-900/30',
                            default => 'ring-gray-100 dark:ring-gray-800',
                        };
                    @endphp
                    
                    <div class="relative">
                        <div class="w-8 h-8 md:w-10 md:h-10 {{ $statusColor }} rounded-full flex items-center justify-center {{ $item['is_current'] ? 'ring-4 ' . $ringColor : '' }} transition-all duration-300">
                            <div class="w-2 h-2 md:w-3 md:h-3 bg-white dark:bg-gray-900 rounded-full"></div>
                        </div>
                    </div>
                    
                    @if(!$loop->last)
                        <div class="w-0.5 h-full bg-gray-200 dark:bg-gray-700 mt-2"></div>
                    @endif
                </div>

                {{-- Content --}}
                <div class="flex-1 pb-6 md:pb-8 min-w-0">
                    {{-- Accordion Header - Clickable --}}
                    <button 
                        @click="isOpen = !isOpen"
                        class="w-full text-left group"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-base md:text-lg font-semibold text-gray-900 dark:text-gray-100 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">
                                        {{ $item['month_name'] }}
                                    </h3>
                                    @if($item['is_current'])
                                        <span class="px-2.5 py-1 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 text-xs font-medium rounded-md">
                                            Aktif
                                        </span>
                                    @endif
                                    
                                    {{-- Chevron Icon --}}
                                    <svg 
                                        class="w-5 h-5 text-gray-400 dark:text-gray-500 transition-transform duration-300 ml-auto sm:ml-0"
                                        :class="{ 'rotate-180': isOpen }"
                                        fill="none" 
                                        stroke="currentColor" 
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                                @if($item['ppn_summary'])
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <div class="w-1.5 h-1.5 rounded-full {{ $item['ppn_summary']->status_final === 'Lebih Bayar' ? 'bg-green-500 dark:bg-green-600' : ($item['ppn_summary']->status_final === 'Kurang Bayar' ? 'bg-red-500 dark:bg-red-600' : 'bg-gray-400 dark:bg-gray-600') }}"></div>
                                        <span class="text-xs md:text-sm {{ $item['ppn_summary']->status_final === 'Lebih Bayar' ? 'text-green-600 dark:text-green-400' : ($item['ppn_summary']->status_final === 'Kurang Bayar' ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400') }}">
                                            {{ $item['ppn_summary']->status_final }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </button>

                    {{-- Accordion Content - Expandable --}}
                    <div 
                        x-show="isOpen"
                        x-collapse
                        x-cloak
                    >
                        @if($item['ppn_summary'])
                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                                {{-- Main Numbers Section --}}
                                <div class="p-5 md:p-6">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6">
                                        {{-- PPN Masuk --}}
                                        <div class="space-y-2">
                                            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">PPN Masuk</p>
                                            <p class="text-xl md:text-2xl font-bold text-gray-900 dark:text-gray-100 break-words">
                                                Rp {{ number_format($item['ppn_summary']->pajak_masuk, 0, ',', '.') }}
                                            </p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">Kredit Pajak</p>
                                        </div>

                                        {{-- PPN Keluar --}}
                                        <div class="space-y-2">
                                            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">PPN Keluar</p>
                                            <p class="text-xl md:text-2xl font-bold text-gray-900 dark:text-gray-100 break-words">
                                                Rp {{ number_format($item['ppn_summary']->pajak_keluar, 0, ',', '.') }}
                                            </p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">Pajak Keluaran</p>
                                        </div>

                                        {{-- Saldo Final --}}
                                        <div class="space-y-2">
                                            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Saldo Final</p>
                                            <p class="text-xl md:text-2xl font-bold break-words {{ $item['ppn_summary']->status_final === 'Lebih Bayar' ? 'text-green-600 dark:text-green-400' : ($item['ppn_summary']->status_final === 'Kurang Bayar' ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100') }}">
                                                Rp {{ number_format(abs($item['ppn_summary']->saldo_final), 0, ',', '.') }}
                                            </p>
                                            <div class="flex items-center gap-1.5">
                                                <div class="w-1.5 h-1.5 rounded-full {{ $item['ppn_summary']->status_final === 'Lebih Bayar' ? 'bg-green-500 dark:bg-green-600' : ($item['ppn_summary']->status_final === 'Kurang Bayar' ? 'bg-red-500 dark:bg-red-600' : 'bg-gray-400 dark:bg-gray-600') }}"></div>
                                                <span class="text-xs {{ $item['ppn_summary']->status_final === 'Lebih Bayar' ? 'text-green-600 dark:text-green-400' : ($item['ppn_summary']->status_final === 'Kurang Bayar' ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400') }}">
                                                    {{ $item['ppn_summary']->status_final }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Divider --}}
                                <div class="border-t border-gray-100 dark:border-gray-700"></div>

                                {{-- Info Section --}}
                                <div class="p-5 md:p-6 bg-gray-50 dark:bg-gray-900/50">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
                                        {{-- Compensation Info --}}
                                        <div>
                                            <div class="flex items-center gap-2 mb-3">
                                                <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                </svg>
                                                <p class="text-xs font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wide">Kompensasi</p>
                                            </div>
                                            @if($item['ppn_summary']->kompensasi_diterima > 0 || $item['ppn_summary']->kompensasi_terpakai > 0)
                                                <div class="space-y-2">
                                                    @if($item['ppn_summary']->kompensasi_diterima > 0)
                                                        <div class="flex items-center justify-between">
                                                            <span class="text-xs text-gray-600 dark:text-gray-400">Diterima</span>
                                                            <span class="text-sm font-semibold text-green-600 dark:text-green-400">
                                                                Rp {{ number_format($item['ppn_summary']->kompensasi_diterima, 0, ',', '.') }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                    @if($item['ppn_summary']->kompensasi_terpakai > 0)
                                                        <div class="flex items-center justify-between">
                                                            <span class="text-xs text-gray-600 dark:text-gray-400">Terpakai</span>
                                                            <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                                                                Rp {{ number_format($item['ppn_summary']->kompensasi_terpakai, 0, ',', '.') }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                    @if($item['ppn_summary']->kompensasi_tersedia > $item['ppn_summary']->kompensasi_terpakai)
                                                        <div class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                                                            <span class="text-xs font-medium text-gray-900 dark:text-gray-100">Tersedia</span>
                                                            <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                                                Rp {{ number_format($item['ppn_summary']->kompensasi_tersedia - $item['ppn_summary']->kompensasi_terpakai, 0, ',', '.') }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <p class="text-sm text-gray-400 dark:text-gray-500">Tidak ada</p>
                                            @endif
                                        </div>

                                        {{-- Report Status --}}
                                        <div>
                                            <div class="flex items-center gap-2 mb-3">
                                                <svg class="w-4 h-4 {{ $item['ppn_summary']->report_status === 'Sudah Lapor' ? 'text-green-500 dark:text-green-400' : 'text-gray-400 dark:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <p class="text-xs font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wide">Status Lapor</p>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium {{ $item['ppn_summary']->report_status === 'Sudah Lapor' ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-gray-100' }}">
                                                    {{ $item['ppn_summary']->report_status }}
                                                </span>
                                                @if($item['ppn_summary']->reported_at)
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ \Carbon\Carbon::parse($item['ppn_summary']->reported_at)->format('d M Y') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-white dark:bg-gray-800 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl p-8 md:p-12 text-center">
                                <svg class="mx-auto w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada data</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Empty State --}}
    @if(empty($timelineData['timeline']))
        <div class="text-center py-12 md:py-16">
            <svg class="mx-auto w-12 h-12 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Belum ada laporan pajak untuk tahun {{ $timelineData['year'] }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Mulai dengan membuat laporan pajak pertama</p>
        </div>
    @endif
    
    {{-- Add Alpine.js Collapse Plugin Styles --}}
    <style>
        [x-cloak] { 
            display: none !important; 
        }
    </style>
</div>
