<x-filament-panels::page>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.40.0/dist/apexcharts.min.js"></script>

    <div class="space-y-8">
        <!-- Header Stats Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @php $stats = $this->getTaxStats(); @endphp
            
            <div class="p-6 bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl shadow-sm border border-indigo-100">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-indigo-100 text-indigo-600">
                        <x-heroicon-o-document-text class="w-6 h-6" />
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Total Laporan Pajak</h3>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_reports']) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl shadow-sm border border-green-100">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-green-100 text-green-600">
                        <x-heroicon-o-currency-dollar class="w-6 h-6" />
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Total Pajak</h3>
                        <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($stats['total_tax'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6 bg-gradient-to-br from-amber-50 to-yellow-50 rounded-xl shadow-sm border border-amber-100">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-amber-100 text-amber-600">
                        <x-heroicon-o-calendar class="w-6 h-6" />
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Laporan Tahun Ini</h3>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_this_year']) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6 bg-gradient-to-br from-red-50 to-rose-50 rounded-xl shadow-sm border border-red-100">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-red-100 text-red-600">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Belum Dibayar</h3>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['unpaid_reports']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- @livewire('tax-report.tax-calendar') --}}
        
        <!-- Monthly PPN Chart & Tax Distribution -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Monthly PPN Chart - 2/3 width -->
            <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-200 lg:col-span-2 overflow-hidden">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900">Tren Pajak Bulanan 2025</h2>
                    <div class="flex items-center space-x-2">
                        <div class="relative">
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        <a href="{{ route('filament.admin.resources.tax-reports.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                            Lihat Semua
                        </a>
                    </div>
                </div>
                <div id="monthly-taxes-chart" class="h-96 -mx-6 -mb-6"></div>
            </div>
            
            <!-- Tax Distribution - 1/3 width -->
            <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <h2 class="text-lg font-bold text-gray-900 mb-6">Distribusi Jenis Pajak</h2>
                <div id="tax-distribution-chart" class="h-80 -mx-6 -mb-6"></div>
            </div>
        </div>
        
        <!-- Recent Reports & Top Clients -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
            <!-- Recent Tax Reports -->
            <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900">Laporan Pajak Terbaru</h2>
                    <a href="{{ route('filament.admin.resources.tax-reports.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                        Lihat Semua
                    </a>
                </div>
                
                @if(count($this->getRecentTaxReports()) > 0)
                    <div class="overflow-hidden">
                        <ul class="divide-y divide-gray-200">
                            @foreach($this->getRecentTaxReports() as $report)
                                <li class="py-4 hover:bg-gray-50 rounded-lg transition-colors duration-150 -mx-4 px-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold">
                                                    {{ strtoupper(substr($report->client->name ?? 'C', 0, 1)) }}
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900">{{ $report->client->name ?? 'Client' }}</p>
                                                    <p class="text-xs text-gray-500">{{ $report->month }} · {{ $report->created_at->format('d M Y') }}</p>
                                                </div>
                                            </div>
                                            <div class="mt-2 flex flex-wrap gap-1.5">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path>
                                                    </svg>
                                                    {{ $report->invoices->count() }} Faktur
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $report->incomeTaxs->count() }} PPh 21
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $report->bupots->count() }} Bupot
                                                </span>
                                            </div>
                                        </div>
                                        <a href="{{ route('filament.admin.resources.tax-reports.view', $report) }}" 
                                           class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
                    <div class="py-12 text-center text-gray-500 bg-gray-50 rounded-lg">
                        <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada laporan pajak</h3>
                        <p class="mt-1 text-sm text-gray-500">Buat laporan pajak baru untuk memulai.</p>
                        <div class="mt-6">
                            <a href="{{ route('filament.admin.resources.tax-reports.create') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Buat Laporan Pajak
                            </a>
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Top Clients -->
            <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900">Klien Teratas</h2>
                    <a href="{{ route('filament.admin.resources.clients.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                        Lihat Semua
                    </a>
                </div>
                
                @if(count($this->getTopClients()) > 0)
                    <div class="overflow-hidden">
                        <ul class="grid gap-4">
                            @foreach($this->getTopClients() as $client)
                                <li class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-150">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="h-12 w-12 flex items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 font-bold text-lg">
                                                {{ strtoupper(substr($client->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-semibold text-gray-900">{{ $client->name }}</p>
                                                <div class="flex items-center">
                                                    <svg class="h-4 w-4 text-indigo-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span class="text-sm font-medium text-gray-600">{{ $client->taxreports_count }}</span>
                                                </div>
                                            </div>
                                            <div class="mt-2 flex justify-between items-center">
                                                <div>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                                        {{ $client->projects_count }} Proyek
                                                    </span>
                                                </div>
                                                <p class="text-sm font-medium text-green-600">Rp {{ number_format($client->invoices_sum_ppn ?? 0, 0, ',', '.') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="py-12 text-center text-gray-500 bg-gray-50 rounded-lg">
                        <x-heroicon-o-user-group class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada klien</h3>
                        <p class="mt-1 text-sm text-gray-500">Tambahkan klien untuk memulai.</p>
                        <div class="mt-6">
                            <a href="{{ route('filament.admin.resources.clients.create') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Tambah Klien
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Tax Calendar & Tips -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Tax Calendar - 2/3 width -->
            <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-200 lg:col-span-2">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900">Kalender Pajak</h2>
                </div>
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-5 border border-blue-100">
                    <h3 class="text-sm font-semibold text-blue-900 mb-4">Jadwal Pelaporan Pajak untuk Bulan {{ date('F Y') }}</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <div class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-lg bg-blue-200 text-blue-800 text-sm font-bold">
                                15
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-gray-900">
                                    Deadline Faktur Pajak (PPN)
                                </p>
                                <p class="mt-1 text-sm text-gray-600">
                                    Pelaporan PPN untuk bulan sebelumnya
                                </p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-lg bg-green-200 text-green-800 text-sm font-bold">
                                20
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-gray-900">
                                    Deadline PPh 21
                                </p>
                                <p class="mt-1 text-sm text-gray-600">
                                    Penyetoran PPh 21 untuk masa pajak bulan sebelumnya
                                </p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-lg bg-amber-200 text-amber-800 text-sm font-bold">
                                30
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-semibold text-gray-900">
                                    Bukti Potong (PPh 23/PPh 26)
                                </p>
                                <p class="mt-1 text-sm text-gray-600">
                                    Penyetoran dan pelaporan PPh 23/26
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Tax Tips -->
            <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 mb-6">Tips Perpajakan</h2>
                <div class="space-y-4">
                    <div class="p-4 bg-blue-50 rounded-lg border border-blue-100">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-semibold text-blue-800">Faktur Pajak</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    Pastikan semua faktur pajak memiliki bukti pendukung yang valid untuk menghindari pemeriksaan lebih lanjut.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 bg-green-50 rounded-lg border border-green-100">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-semibold text-green-800">PPh 21</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    Perhitungan PPh 21 sebaiknya dilakukan secara teliti dengan mempertimbangkan PTKP terbaru.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-100">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-semibold text-yellow-800">Bukti Potong</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    Selalu simpan bukti potong sebagai dokumen pengurangan pajak yang sah.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ApexCharts Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Monthly PPN Chart
            const monthlyPpnData = @json(json_decode($this->getMonthlyTaxesData('ppn'), true));
            const monthlyPph21Data = @json(json_decode($this->getMonthlyTaxesData('pph21'), true));
            const monthlyBupotData = @json(json_decode($this->getMonthlyTaxesData('bupot'), true));
            
            const monthlyOptions = {
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
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '65%',
                        borderRadius: 4,
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
                xaxis: {
                    categories: monthlyPpnData.map(item => item.x),
                    position: 'bottom',
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                },
                yaxis: {
                    labels: {
                        formatter: function (val) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                        }
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
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
                },
                grid: {
                    borderColor: '#f1f1f1',
                },
                states: {
                    hover: {
                        filter: {
                            type: 'darken',
                            value: 0.9,
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
            // Tax Distribution Chart
            const taxTypeData = @json($this->getTaxTypeDistribution());
            const allZero = taxTypeData.every(item => item.value === 1) && taxTypeData[0].value === 1 && taxTypeData[1].value === 1 && taxTypeData[2].value === 1;

            const distributionOptions = {
                series: taxTypeData.map(item => item.value),
                chart: {
                    type: 'donut',
                    height: 350, // Increased from 300 to 350
                    fontFamily: 'inherit',
                },
                labels: taxTypeData.map(item => item.name),
                colors: ['#4f46e5', '#10b981', '#f59e0b'],
                legend: {
                    position: 'bottom',
                    fontFamily: 'inherit',
                    fontSize: '14px',
                    offsetY: 5,
                    itemMargin: {
                        horizontal: 10,
                        vertical: 5
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%', // Increased from 50% to 65%
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '14px',
                                    offsetY: 0,
                                },
                                value: {
                                    show: true,
                                    fontSize: '16px',
                                    fontWeight: 600,
                                    offsetY: 5,
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