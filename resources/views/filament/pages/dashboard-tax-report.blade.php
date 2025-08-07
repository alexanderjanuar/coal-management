<x-filament-panels::page>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.40.0/dist/apexcharts.min.js"></script>

    <div class="space-y-8">

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

        <!-- Bottom Section: Top Clients & Tax Tips -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
            <!-- Top Clients -->
            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Klien Teratas</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Berdasarkan jumlah laporan pajak</p>
                    </div>
                    <a href="{{ route('filament.admin.resources.clients.index') }}"
                        class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">
                        Lihat Semua
                    </a>
                </div>

                @if(count($this->getTopClients()) > 0)
                <div class="overflow-hidden">
                    <ul class="grid gap-4">
                        @foreach($this->getTopClients() as $client)
                        <li class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 flex items-center justify-center rounded-lg bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/50 dark:to-purple-900/50 text-indigo-600 dark:text-indigo-400 font-bold text-lg">
                                        {{ strtoupper(substr($client->name, 0, 1)) }}
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $client->name }}</p>
                                        <div class="flex items-center">
                                            <svg class="h-4 w-4 text-indigo-500 dark:text-indigo-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ $client->taxreports_count }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-2 flex justify-between items-center">
                                        <div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 dark:bg-indigo-900/50 text-indigo-800 dark:text-indigo-300">
                                                {{ $client->projects_count }} Proyek
                                            </span>
                                        </div>
                                        <p class="text-sm font-medium text-green-600 dark:text-green-400">Rp {{ number_format($client->invoices_sum_ppn ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @else
                <div class="py-12 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <x-heroicon-o-user-group class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Belum ada klien</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tambahkan klien untuk memulai.</p>
                    <div class="mt-6">
                        <a href="{{ route('filament.admin.resources.clients.create') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Klien
                        </a>
                    </div>
                </div>
                @endif
            </div>

            <!-- Enhanced Tax Tips -->
            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Tips Perpajakan</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Informasi penting untuk pelaporan pajak</p>
                </div>
                <div class="space-y-4">
                    <div class="p-4 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-lg border border-blue-100 dark:border-blue-900/50">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/50 rounded-lg flex items-center justify-center">
                                    <x-heroicon-o-document-check class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                </div>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-300">Faktur Pajak</h3>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                    Pastikan semua faktur pajak memiliki bukti pendukung yang valid untuk menghindari pemeriksaan lebih lanjut.
                                </p>
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300">
                                        Deadline: 20 bulan berikutnya
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg border border-green-100 dark:border-green-900/50">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 dark:bg-green-900/50 rounded-lg flex items-center justify-center">
                                    <x-heroicon-o-receipt-percent class="h-5 w-5 text-green-600 dark:text-green-400" />
                                </div>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-semibold text-green-800 dark:text-green-300">PPh 21</h3>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                    Perhitungan PPh 21 sebaiknya dilakukan secara teliti dengan mempertimbangkan PTKP terbaru tahun {{ date('Y') }}.
                                </p>
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300">
                                        Deadline: 10 bulan berikutnya
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4 bg-gradient-to-r from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 rounded-lg border border-yellow-100 dark:border-yellow-900/50">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900/50 rounded-lg flex items-center justify-center">
                                    <x-heroicon-o-document-text class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                                </div>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-300">Bukti Potong</h3>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                    Selalu simpan bukti potong sebagai dokumen pengurangan pajak yang sah dan lakukan pelaporan tepat waktu.
                                </p>
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300">
                                        Deadline: 20 bulan berikutnya
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Quick Actions -->
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-wrap gap-2">
                            <a href="" 
                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/70 rounded-lg transition-colors duration-200">
                                <x-heroicon-m-plus class="w-3 h-3 mr-1" />
                                Buat Laporan
                            </a>
                            <a href="https://pajak.go.id" target="_blank"
                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200">
                                <x-heroicon-m-arrow-top-right-on-square class="w-3 h-3 mr-1" />
                                DJP Online
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced ApexCharts Script with Dark Mode Support -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Detect dark mode
            const isDarkMode = document.documentElement.classList.contains('dark') || 
                              (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
            
            // Common dark mode theme
            const darkModeTheme = {
                theme: {
                    mode: isDarkMode ? 'dark' : 'light'
                },
                grid: {
                    borderColor: isDarkMode ? '#374151' : '#f1f1f1',
                },
                xaxis: {
                    labels: {
                        style: {
                            colors: isDarkMode ? '#9CA3AF' : '#374151'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: isDarkMode ? '#9CA3AF' : '#374151'
                        }
                    }
                },
                legend: {
                    labels: {
                        colors: isDarkMode ? '#D1D5DB' : '#374151'
                    }
                },
                tooltip: {
                    theme: isDarkMode ? 'dark' : 'light'
                }
            };

            // Monthly Tax Chart Data
            const monthlyPpnData = @json(json_decode($this->getMonthlyTaxesData('ppn'), true));
            const monthlyPph21Data = @json(json_decode($this->getMonthlyTaxesData('pph21'), true));
            const monthlyBupotData = @json(json_decode($this->getMonthlyTaxesData('bupot'), true));
            
            const monthlyOptions = {
                ...darkModeTheme,
                series: [
                    {
                        name: 'PPN',
                        data: monthlyPpnData.map(item => item.y)
                    },
                    {
                        name: 'PPh 21',
                        data: monthlyPph21Data.map(item => item.y)
                    },
                    {
                        name: 'Bupot',
                        data: monthlyBupotData.map(item => item.y)
                    }
                ],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'inherit',
                    background: 'transparent'
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '65%',
                        borderRadius: 6,
                        endingShape: 'rounded',
                        dataLabels: {
                            position: 'top'
                        }
                    },
                },
                dataLabels: {
                    enabled: false
                },
                colors: ['#4f46e5', '#10b981', '#f59e0b'],
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: monthlyPpnData.map(item => item.x),
                    position: 'bottom',
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    labels: {
                        style: {
                            colors: isDarkMode ? '#9CA3AF' : '#374151',
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function (val) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                        },
                        style: {
                            colors: isDarkMode ? '#9CA3AF' : '#374151'
                        }
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    theme: isDarkMode ? 'dark' : 'light',
                    y: {
                        formatter: function (val) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    offsetY: 0,
                    fontSize: '13px',
                    labels: {
                        colors: isDarkMode ? '#D1D5DB' : '#374151'
                    }
                },
                states: {
                    hover: {
                        filter: {
                            type: 'darken',
                            value: 0.1,
                        }
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        legend: {
                            position: 'bottom',
                            offsetX: -10,
                            offsetY: 0
                        }
                    }
                }]
            };

            const monthlyChart = new ApexCharts(document.querySelector("#monthly-taxes-chart"), monthlyOptions);
            monthlyChart.render();
            
            // Tax Distribution Chart
            const taxTypeData = @json($this->getTaxTypeDistribution());
            const allZero = taxTypeData.every(item => item.value === 1) && taxTypeData[0].value === 1 && taxTypeData[1].value === 1 && taxTypeData[2].value === 1;

            const distributionOptions = {
                ...darkModeTheme,
                series: taxTypeData.map(item => item.value),
                chart: {
                    type: 'donut',
                    height: 350,
                    fontFamily: 'inherit',
                    background: 'transparent'
                },
                labels: taxTypeData.map(item => item.name),
                colors: ['#4f46e5', '#10b981', '#f59e0b'],
                legend: {
                    position: 'bottom',
                    fontFamily: 'inherit',
                    fontSize: '14px',
                    offsetY: 5,
                    labels: {
                        colors: isDarkMode ? '#D1D5DB' : '#374151'
                    },
                    itemMargin: {
                        horizontal: 10,
                        vertical: 5
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '14px',
                                    offsetY: 0,
                                    color: isDarkMode ? '#D1D5DB' : '#374151'
                                },
                                value: {
                                    show: true,
                                    fontSize: '16px',
                                    fontWeight: 600,
                                    offsetY: 5,
                                    color: isDarkMode ? '#F9FAFB' : '#111827',
                                    formatter: function(val) {
                                        if (allZero) {
                                            return 'Tidak ada data';
                                        }
                                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '14px',
                                    fontWeight: 600,
                                    color: isDarkMode ? '#9CA3AF' : '#6B7280',
                                    formatter: function(w) {
                                        if (allZero) {
                                            return 'Tidak ada data';
                                        }
                                        const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: false,
                },
                tooltip: {
                    theme: isDarkMode ? 'dark' : 'light',
                    y: {
                        formatter: function(value) {
                            if (allZero) {
                                return 'Tidak ada data';
                            }
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 280
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            const distributionChart = new ApexCharts(document.querySelector("#tax-distribution-chart"), distributionOptions);
            distributionChart.render();
        });
    </script>
</x-filament-panels::page>