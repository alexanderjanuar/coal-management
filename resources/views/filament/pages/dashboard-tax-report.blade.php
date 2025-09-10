<x-filament-panels::page>
    <div class="space-y-8">

        <!-- Top Stats Overview -->
        @livewire(\App\Livewire\TaxReport\StatsOverview::class)


        <!-- Monthly Tax Chart & Tax Distribution -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Monthly Tax Chart - 2/3 width -->
            <div class="lg:col-span-2 overflow-hidden">
                @livewire(\App\Livewire\TaxReport\TaxReportCountChart::class)
            </div>

            <!-- Tax Distribution - 1/3 width -->
            <div class="
            overflow-hidden">
                @livewire(\App\Livewire\TaxReport\TaxReportTypeChart::class)
            </div>
        </div>

        <!-- Tax Calendar & Recent Reports -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Tax Calendar - 2/3 width -->
            <div class="lg:col-span-2">
                @livewire('tax-report.tax-calendar')
            </div>

            <!-- Recent Tax Reports -->
            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Laporan Pajak Terbaru</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">5 laporan terakhir</p>
                    </div>
                    <a href=""
                        class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">
                        Lihat Semua
                    </a>
                </div>

                @if(count($this->getRecentTaxReports()) > 0)
                <div class="overflow-hidden">
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($this->getRecentTaxReports() as $report)
                        <li class="py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition-colors duration-150 -mx-4 px-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/50 dark:to-purple-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-semibold">
                                            {{ strtoupper(substr($report->client->name ?? 'C', 0, 1)) }}
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $report->client->name ?? 'Client' }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $report->month }} · {{ $report->created_at->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Status badges -->
                                    <div class="mt-3 flex flex-wrap gap-1">
                                        <!-- PPN Status -->
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $report->ppn_report_status === 'Sudah Lapor' ? 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300' }}">
                                            @if($report->ppn_report_status === 'Sudah Lapor')
                                                <x-heroicon-m-check-circle class="w-3 h-3 mr-1" />
                                            @else
                                                <x-heroicon-m-clock class="w-3 h-3 mr-1" />
                                            @endif
                                            PPN
                                        </span>
                                        
                                        <!-- PPh Status -->
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $report->pph_report_status === 'Sudah Lapor' ? 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300' }}">
                                            @if($report->pph_report_status === 'Sudah Lapor')
                                                <x-heroicon-m-check-circle class="w-3 h-3 mr-1" />
                                            @else
                                                <x-heroicon-m-clock class="w-3 h-3 mr-1" />
                                            @endif
                                            PPh
                                        </span>
                                        
                                        <!-- Bupot Status -->
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $report->bupot_report_status === 'Sudah Lapor' ? 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300' }}">
                                            @if($report->bupot_report_status === 'Sudah Lapor')
                                                <x-heroicon-m-check-circle class="w-3 h-3 mr-1" />
                                            @else
                                                <x-heroicon-m-clock class="w-3 h-3 mr-1" />
                                            @endif
                                            Bupot
                                        </span>
                                    </div>
                                    
                                    <!-- Data summary -->
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path>
                                            </svg>
                                            {{ $report->invoices->count() }} Faktur
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $report->incomeTaxs->count() }} PPh 21
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $report->bupots->count() }} Bupot
                                        </span>
                                    </div>
                                </div>
                                <a href=""
                                    class="ml-4 inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-indigo-400">
                                    Detail
                                    <svg class="ml-1.5 -mr-1 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @else
                <div class="py-12 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Belum ada laporan pajak</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Buat laporan pajak baru untuk memulai.</p>
                    <div class="mt-6">
                        <a href=""
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Buat Laporan Pajak
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>