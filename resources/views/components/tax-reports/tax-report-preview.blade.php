{{-- resources/views/components/tax-reports/tax-report-preview.blade.php --}}
<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-4">
    @if($clientId && !empty(array_filter($months)))
        <!-- Header -->
        <div class="flex items-center gap-2 pb-3 border-b border-gray-200 dark:border-gray-700">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ $createMultiple ? 'Preview Laporan Pajak Multiple' : 'Preview Laporan Pajak' }}
            </h3>
        </div>

        <!-- Client and Year Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-400">Klien:</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $clientName }}</span>
            </div>
            
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-400">Tahun:</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $year }}</span>
            </div>
        </div>

        <!-- Months Status -->
        <div class="space-y-3">
            @if(!empty($existingMonths))
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <div class="flex-1">
                            <h4 class="font-medium text-amber-800 dark:text-amber-200">Sudah Ada</h4>
                            <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                                Bulan-bulan ini sudah memiliki laporan pajak dan akan dilewati:
                            </p>
                            <div class="flex flex-wrap gap-1 mt-2">
                                @foreach($existingMonths as $month)
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-amber-100 dark:bg-amber-800 text-amber-800 dark:text-amber-200 rounded-md">
                                        @switch($month)
                                            @case('January')
                                                Januari
                                                @break
                                            @case('February')
                                                Februari
                                                @break
                                            @case('March')
                                                Maret
                                                @break
                                            @case('April')
                                                April
                                                @break
                                            @case('May')
                                                Mei
                                                @break
                                            @case('June')
                                                Juni
                                                @break
                                            @case('July')
                                                Juli
                                                @break
                                            @case('August')
                                                Agustus
                                                @break
                                            @case('September')
                                                September
                                                @break
                                            @case('October')
                                                Oktober
                                                @break
                                            @case('November')
                                                November
                                                @break
                                            @case('December')
                                                Desember
                                                @break
                                            @default
                                                {{ $month }}
                                        @endswitch
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if(!empty($newMonths))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="flex-1">
                            <h4 class="font-medium text-green-800 dark:text-green-200">Akan Dibuat</h4>
                            <p class="text-sm text-green-700 dark:text-green-300 mt-1">
                                {{ count($newMonths) }} laporan pajak baru akan dibuat untuk bulan:
                            </p>
                            <div class="flex flex-wrap gap-1 mt-2">
                                @foreach($newMonths as $month)
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-200 rounded-md">
                                        @switch($month)
                                            @case('January')
                                                Januari
                                                @break
                                            @case('February')
                                                Februari
                                                @break
                                            @case('March')
                                                Maret
                                                @break
                                            @case('April')
                                                April
                                                @break
                                            @case('May')
                                                Mei
                                                @break
                                            @case('June')
                                                Juni
                                                @break
                                            @case('July')
                                                Juli
                                                @break
                                            @case('August')
                                                Agustus
                                                @break
                                            @case('September')
                                                September
                                                @break
                                            @case('October')
                                                Oktober
                                                @break
                                            @case('November')
                                                November
                                                @break
                                            @case('December')
                                                Desember
                                                @break
                                            @default
                                                {{ $month }}
                                        @endswitch
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="flex-1">
                            <h4 class="font-medium text-red-800 dark:text-red-200">Tidak Ada Laporan Baru</h4>
                            <p class="text-sm text-red-700 dark:text-red-300">
                                Semua bulan yang dipilih sudah memiliki laporan pajak untuk klien dan tahun ini.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Summary Stats -->
        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ count($filteredMonths) }}</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Dipilih</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ count($newMonths) }}</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Akan Dibuat</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ count($existingMonths) }}</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Sudah Ada</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $year }}</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Tahun</div>
                </div>
            </div>
        </div>

        <!-- Action Button Preview (Optional) -->
        @if(!empty($newMonths))
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <div>
                            <h4 class="font-medium text-blue-800 dark:text-blue-200">Siap Untuk Dibuat</h4>
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                Klik tombol "Create" untuk membuat {{ count($newMonths) }} laporan pajak
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ count($newMonths) }}</div>
                        <div class="text-xs text-blue-600 dark:text-blue-400">Laporan Baru</div>
                    </div>
                </div>
            </div>
        @endif

    @else
        <!-- Empty State -->
        <div class="text-center py-8">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Siap Membuat Laporan Pajak</h3>
            <p class="text-gray-600 dark:text-gray-400">
                Pilih klien dan {{ $createMultiple ? 'bulan-bulan' : 'bulan' }} untuk melihat preview dan ringkasan pembuatan.
            </p>
            <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    💡 <strong>Tips:</strong> Aktifkan toggle "Buat Multiple Bulan" untuk membuat beberapa laporan sekaligus
                </div>
            </div>
        </div>
    @endif
</div>