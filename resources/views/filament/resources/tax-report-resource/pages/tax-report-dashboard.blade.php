<x-filament-panels::page>
    <script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    </script>
    <div class="space-y-6">
        <!-- Header Stats Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @php $stats = $this->getTaxStats(); @endphp
            
            <x-filament::section>
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-primary-50 text-primary-500">
                        <x-heroicon-o-document-text class="w-6 h-6" />
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Total Laporan Pajak</h3>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_reports']) }}</p>
                    </div>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-success-50 text-success-500">
                        <x-heroicon-o-currency-dollar class="w-6 h-6" />
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Total Pajak</h3>
                        <p class="text-2xl font-semibold text-gray-900">Rp {{ number_format($stats['total_tax'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-warning-50 text-warning-500">
                        <x-heroicon-o-calendar class="w-6 h-6" />
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Laporan Tahun Ini</h3>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_this_year']) }}</p>
                    </div>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-danger-50 text-danger-500">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Belum Dibayar</h3>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['unpaid_reports']) }}</p>
                    </div>
                </div>
            </x-filament::section>
        </div>
        
        <!-- Monthly PPN Chart & Tax Distribution -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Monthly PPN Chart - 2/3 width -->
            <x-filament::section class="lg:col-span-2">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Tren PPN Bulanan {{ date('Y') }}</h2>
                    <a href="{{ route('filament.admin.resources.tax-reports.index') }}" class="text-sm text-primary-600 hover:text-primary-500">
                        Lihat Semua
                    </a>
                </div>
                <div class="mt-4" style="height: 300px;">
                    <div id="monthly-ppn-chart"></div>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const monthlyData = @json(json_decode($this->getMonthlyInvoicesData(), true));
                        
                        const options = {
                            series: [{
                                name: 'PPN',
                                data: monthlyData.map(item => item.y)
                            }],
                            chart: {
                                type: 'bar',
                                height: 300,
                                toolbar: {
                                    show: false
                                }
                            },
                            plotOptions: {
                                bar: {
                                    borderRadius: 4,
                                    dataLabels: {
                                        position: 'top'
                                    }
                                }
                            },
                            dataLabels: {
                                enabled: false
                            },
                            colors: ['#3b82f6'],
                            xaxis: {
                                categories: monthlyData.map(item => item.x),
                                position: 'bottom',
                            },
                            yaxis: {
                                labels: {
                                    formatter: function (val) {
                                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                                    }
                                }
                            },
                            tooltip: {
                                y: {
                                    formatter: function (val) {
                                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                                    }
                                }
                            }
                        };

                        const chart = new ApexCharts(document.querySelector("#monthly-ppn-chart"), options);
                        chart.render();
                    });
                </script>
            </x-filament::section>
            
            <!-- Tax Distribution - 1/3 width -->
            <x-filament::section>
                <h2 class="text-lg font-semibold text-gray-900">Distribusi Jenis Pajak</h2>
                <div class="mt-4" style="height: 300px;">
                    <div id="tax-distribution-chart"></div>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const taxTypeData = @json($this->getTaxTypeDistribution());
                        
                        const options = {
                            series: taxTypeData.map(item => item.value),
                            chart: {
                                type: 'donut',
                                height: 300
                            },
                            labels: taxTypeData.map(item => item.name),
                            colors: ['#3b82f6', '#10b981', '#f59e0b'],
                            legend: {
                                show: true,
                                position: 'bottom'
                            },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        labels: {
                                            show: true,
                                            name: {
                                                show: true
                                            },
                                            value: {
                                                show: true,
                                                formatter: function(val) {
                                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                                                }
                                            },
                                            total: {
                                                show: true,
                                                label: 'Total',
                                                formatter: function(w) {
                                                    const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        };

                        const chart = new ApexCharts(document.querySelector("#tax-distribution-chart"), options);
                        chart.render();
                    });
                </script>
            </x-filament::section>
        </div>
        
        <!-- Recent Reports & Top Clients -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Recent Tax Reports -->
            <x-filament::section>
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Laporan Pajak Terbaru</h2>
                    <a href="{{ route('filament.admin.resources.tax-reports.index') }}" class="text-sm text-primary-600 hover:text-primary-500">
                        Lihat Semua
                    </a>
                </div>
                <div class="mt-4 overflow-hidden">
                    <div class="flow-root">
                        <ul role="list" class="divide-y divide-gray-200">
                            @foreach($this->getRecentTaxReports() as $report)
                                <li class="py-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ $report->client->name }} - {{ $report->month }}
                                            </p>
                                            <div class="mt-1 flex">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                                                    {{ $report->invoices->count() }} Faktur
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2">
                                                    {{ $report->incomeTaxs->count() }} PPh 21
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    {{ $report->bupots->count() }} Bupot
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="{{ route('filament.admin.resources.tax-reports.view', $report) }}" 
                                               class="inline-flex items-center px-3 py-1 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500">
                                                Detail
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @if(count($this->getRecentTaxReports()) === 0)
                    <div class="py-12 text-center text-gray-500">
                        <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada laporan pajak</h3>
                        <p class="mt-1 text-sm text-gray-500">Buat laporan pajak baru untuk memulai.</p>
                        <div class="mt-6">
                            <a href="{{ route('filament.admin.resources.tax-reports.create') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
                                <x-heroicon-s-plus class="-ml-1 mr-2 h-5 w-5" />
                                Buat Laporan Pajak
                            </a>
                        </div>
                    </div>
                @endif
            </x-filament::section>
            
            <!-- Top Clients -->
            <x-filament::section>
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Klien Teratas</h2>
                    <a href="{{ route('filament.admin.resources.clients.index') }}" class="text-sm text-primary-600 hover:text-primary-500">
                        Lihat Semua
                    </a>
                </div>
                <div class="mt-4 overflow-hidden">
                    <div class="flow-root">
                        <ul role="list" class="divide-y divide-gray-200">
                            @foreach($this->getTopClients() as $client)
                                <li class="py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 flex items-center justify-center rounded-full bg-primary-100 text-primary-600">
                                                {{ strtoupper(substr($client->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-medium text-gray-900">{{ $client->name }}</p>
                                                <p class="text-sm text-gray-500">{{ $client->taxreports_count }} Laporan</p>
                                            </div>
                                            <div class="mt-1 flex justify-between">
                                                <p class="text-sm text-gray-500">{{ $client->projects_count }} Proyek</p>
                                                <p class="text-sm font-medium text-green-600">Rp {{ number_format($client->invoices_sum_ppn ?? 0, 0, ',', '.') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @if(count($this->getTopClients()) === 0)
                    <div class="py-12 text-center text-gray-500">
                        <x-heroicon-o-user-group class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada klien</h3>
                        <p class="mt-1 text-sm text-gray-500">Tambahkan klien untuk memulai.</p>
                        <div class="mt-6">
                            <a href="{{ route('filament.admin.resources.clients.create') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
                                <x-heroicon-s-plus class="-ml-1 mr-2 h-5 w-5" />
                                Tambah Klien
                            </a>
                        </div>
                    </div>
                @endif
            </x-filament::section>
        </div>
        
        <!-- Tax Calendar & Tips -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Tax Calendar - 2/3 width -->
            <x-filament::section class="lg:col-span-2">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Kalender Pajak</h2>
                </div>
                <div class="mt-4 p-4 bg-primary-50 rounded-lg">
                    <h3 class="text-sm font-medium text-primary-800">Jadwal Pelaporan Pajak untuk Bulan {{ date('F Y') }}</h3>
                    <ul class="mt-2 space-y-3">
                        <li class="flex items-start">
                            <div class="flex-shrink-0 flex h-6 w-6 items-center justify-center rounded-full bg-primary-200 text-primary-600 text-xs font-medium">
                                15
                            </div>
                            <p class="ml-3 text-sm text-gray-700">
                                <span class="font-medium">Deadline Faktur Pajak (PPN)</span>
                                <span class="block text-gray-500">Pelaporan PPN untuk bulan sebelumnya</span>
                            </p>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 flex h-6 w-6 items-center justify-center rounded-full bg-primary-200 text-primary-600 text-xs font-medium">
                                20
                            </div>
                            <p class="ml-3 text-sm text-gray-700">
                                <span class="font-medium">Deadline PPh 21</span>
                                <span class="block text-gray-500">Penyetoran PPh 21 untuk masa pajak bulan sebelumnya</span>
                            </p>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 flex h-6 w-6 items-center justify-center rounded-full bg-primary-200 text-primary-600 text-xs font-medium">
                                30
                            </div>
                            <p class="ml-3 text-sm text-gray-700">
                                <span class="font-medium">Bukti Potong (PPh 23/PPh 26)</span>
                                <span class="block text-gray-500">Penyetoran dan pelaporan PPh 23/26</span>
                            </p>
                        </li>
                    </ul>
                </div>
            </x-filament::section>
            
            <!-- Tax Tips -->
            <x-filament::section>
                <h2 class="text-lg font-semibold text-gray-900">Tips Perpajakan</h2>
                <div class="mt-4 space-y-4">
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <h3 class="text-sm font-medium text-blue-800">Faktur Pajak</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Pastikan semua faktur pajak memiliki bukti pendukung yang valid untuk menghindari pemeriksaan lebih lanjut.
                        </p>
                    </div>
                    <div class="p-4 bg-green-50 rounded-lg">
                        <h3 class="text-sm font-medium text-green-800">PPh 21</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Perhitungan PPh 21 sebaiknya dilakukan secara teliti dengan mempertimbangkan PTKP terbaru.
                        </p>
                    </div>
                    <div class="p-4 bg-yellow-50 rounded-lg">
                        <h3 class="text-sm font-medium text-yellow-800">Bukti Potong</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Selalu simpan bukti potong sebagai dokumen pengurangan pajak yang sah.
                        </p>
                    </div>
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>