{{-- resources/views/filament/components/simple-compensation-view.blade.php --}}

<div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 space-y-4">
    {{-- Current Period Info --}}
    <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ $record->client->name }} - {{ $record->month }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Periode yang akan dikompensasi
            </p>
        </div>
        <div class="text-right">
            <div class="text-sm text-gray-600 dark:text-gray-400">Selisih Awal</div>
            <div class="text-lg font-bold text-orange-600 dark:text-orange-400">
                Rp {{ number_format($currentSelisih, 0, ',', '.') }}
            </div>
        </div>
    </div>

    {{-- Compensation Details --}}
    @if($sourceReport && $compensationAmount > 0)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Source Period --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <div class="flex items-center space-x-2 mb-2">
                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    <h4 class="font-medium text-blue-900 dark:text-blue-100">Periode Sumber</h4>
                </div>
                <div class="text-sm text-blue-800 dark:text-blue-200">
                    <div class="font-semibold">{{ $sourceReport->month }}</div>
                    <div class="mt-1">
                        Tersedia: <span class="font-medium">Rp {{ number_format($availableAmount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Compensation Amount --}}
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                <div class="flex items-center space-x-2 mb-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <h4 class="font-medium text-green-900 dark:text-green-100">Jumlah Kompensasi</h4>
                </div>
                <div class="text-sm text-green-800 dark:text-green-200">
                    <div class="text-lg font-bold">
                        Rp {{ number_format($compensationAmount, 0, ',', '.') }}
                    </div>
                    @if($compensationAmount > $availableAmount)
                        <div class="text-red-600 dark:text-red-400 text-xs mt-1">
                            ⚠️ Melebihi yang tersedia
                        </div>
                    @endif
                </div>
            </div>

            {{-- Result --}}
            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                <div class="flex items-center space-x-2 mb-2">
                    <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                    <h4 class="font-medium text-purple-900 dark:text-purple-100">Hasil Akhir</h4>
                </div>
                <div class="text-sm text-purple-800 dark:text-purple-200">
                    <div class="font-semibold">
                        Rp {{ number_format($effectiveAmount, 0, ',', '.') }}
                    </div>
                    <div class="mt-1">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            @if($newStatus === 'Nihil') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                            @elseif($newStatus === 'Lebih Bayar') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200
                            @else bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-200
                            @endif">
                            {{ $newStatus }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Calculation Flow --}}
        <div class="bg-white dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <h4 class="font-medium text-gray-900 dark:text-white mb-3 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                Perhitungan Kompensasi
            </h4>
            <div class="flex items-center justify-center space-x-4 text-sm">
                <div class="text-center">
                    <div class="font-medium text-gray-900 dark:text-white">
                        Rp {{ number_format($currentSelisih, 0, ',', '.') }}
                    </div>
                    <div class="text-gray-600 dark:text-gray-400">Kurang Bayar</div>
                </div>
                
                <div class="text-2xl text-gray-400">−</div>
                
                <div class="text-center">
                    <div class="font-medium text-green-600 dark:text-green-400">
                        Rp {{ number_format($compensationAmount, 0, ',', '.') }}
                    </div>
                    <div class="text-gray-600 dark:text-gray-400">Kompensasi</div>
                </div>
                
                <div class="text-2xl text-gray-400">=</div>
                
                <div class="text-center">
                    <div class="font-bold text-purple-600 dark:text-purple-400">
                        Rp {{ number_format($effectiveAmount, 0, ',', '.') }}
                    </div>
                    <div class="text-gray-600 dark:text-gray-400">{{ $newStatus }}</div>
                </div>
            </div>
        </div>

        {{-- Status Change Alert --}}
        @if($newStatus !== 'Kurang Bayar')
            <div class="rounded-lg p-4 
                @if($newStatus === 'Nihil') bg-blue-50 border border-blue-200 dark:bg-blue-900/20 dark:border-blue-800
                @else bg-green-50 border border-green-200 dark:bg-green-900/20 dark:border-green-800
                @endif">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2 
                        @if($newStatus === 'Nihil') text-blue-600 dark:text-blue-400
                        @else text-green-600 dark:text-green-400
                        @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="
                        @if($newStatus === 'Nihil') text-blue-800 dark:text-blue-200
                        @else text-green-800 dark:text-green-200
                        @endif">
                        @if($newStatus === 'Nihil')
                            <strong>Status akan berubah menjadi Nihil</strong> - Tidak ada kewajiban pajak tersisa.
                        @else
                            <strong>Status akan berubah menjadi Lebih Bayar</strong> - Kelebihan Rp {{ number_format(abs($effectiveAmount), 0, ',', '.') }} dapat dikompensasikan ke periode berikutnya.
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Validation Status --}}
        @if(!$hasValidSelection)
            <div class="bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-800 rounded-lg p-4">
                <div class="flex items-center text-red-800 dark:text-red-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <span class="font-medium">
                        @if(!$sourceReport)
                            Pilih periode sumber terlebih dahulu
                        @elseif($compensationAmount <= 0)
                            Masukkan jumlah kompensasi yang valid
                        @elseif($compensationAmount > $availableAmount)
                            Jumlah kompensasi melebihi yang tersedia
                        @endif
                    </span>
                </div>
            </div>
        @endif

    @else
        {{-- No Compensation Selected --}}
        <div class="text-center py-8">
            <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                Siap untuk Kompensasi
            </h3>
            <p class="text-gray-600 dark:text-gray-400">
                Pilih periode sumber dan jumlah kompensasi untuk melihat preview hasil
            </p>
        </div>
    @endif
</div>