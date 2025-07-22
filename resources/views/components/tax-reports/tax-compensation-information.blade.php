{{-- resources/views/components/tax-reports/tax-compensation-information.blade.php --}}

@props([
    'record' => null,
    'showTitle' => true,
    'variant' => 'default'
])

@php
    $hasCompensation = $record && $record->exists && $record->ppn_dikompensasi_dari_masa_sebelumnya > 0;
    $compensation = $hasCompensation ? $record->ppn_dikompensasi_dari_masa_sebelumnya : 0;
    $notes = $hasCompensation ? $record->kompensasi_notes : null;
    
    // Updated metrics with revision exclusion
    $ppnKeluar = $record && $record->exists ? $record->getTotalPpnKeluarFiltered() : 0;
    $ppnMasuk = $record && $record->exists ? $record->getTotalPpnMasukFiltered() : 0;
    $peredaranBruto = $record && $record->exists ? $record->getPeredaranBruto() : 0;
    $selisihPpn = $ppnKeluar - $ppnMasuk;
    $effectivePayment = $selisihPpn - $compensation;
    $status = $record && $record->exists ? ($record->invoice_tax_status ?? 'Belum Dihitung') : 'Belum Dihitung';
    
    // Get invoice counts (excluding revisions)
    $totalInvoices = $record ? $record->getInvoiceCount() : 0;
    $fakturKeluarCount = $record ? $record->getInvoiceCount('Faktur Keluaran') : 0;
    $fakturMasukCount = $record ? $record->getInvoiceCount('Faktur Masuk') : 0;
    $filteredFakturKeluarCount = $record ? $record->getFilteredFakturKeluarCount() : 0;
    $revisionCount = $record ? $record->revisionInvoices()->count() : 0;
    $excludedInvoicesCount = $fakturKeluarCount - $filteredFakturKeluarCount;
@endphp

<div 
    x-data="{ 
        isOpen: false,
        currentAmount: 0,
        targetAmount: {{ $compensation }},
        currentPeredaran: 0,
        targetPeredaran: {{ $peredaranBruto }}
    }"
    x-init="
        if (targetAmount > 0) {
            let duration = 800;
            let start = Date.now();
            
            function animateCompensation() {
                let elapsed = Date.now() - start;
                let progress = Math.min(elapsed / duration, 1);
                currentAmount = Math.floor(targetAmount * progress);
                
                if (progress < 1) {
                    requestAnimationFrame(animateCompensation);
                } else {
                    currentAmount = targetAmount;
                }
            }
            animateCompensation();
        }
        
        if (targetPeredaran > 0) {
            let duration = 1000;
            let start = Date.now();
            
            function animatePeredaran() {
                let elapsed = Date.now() - start;
                let progress = Math.min(elapsed / duration, 1);
                currentPeredaran = Math.floor(targetPeredaran * progress);
                
                if (progress < 1) {
                    requestAnimationFrame(animatePeredaran);
                } else {
                    currentPeredaran = targetPeredaran;
                }
            }
            animatePeredaran();
        }
    "
    {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden transition-colors duration-200']) }}
>
    {{-- Accordion Header --}}
    <button 
        type="button"
        @click="isOpen = !isOpen"
        class="w-full px-6 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:focus:ring-amber-400 focus:ring-inset"
    >
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                
                <div class="min-w-0 flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Ringkasan PPN & Peredaran</h3>
                    <div class="flex items-center space-x-4 mt-1">
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $record && $record->client ? $record->client->name : 'Tax Report' }} • {{ $record ? $record->month : 'N/A' }}</p>
                        
                        {{-- Revision indicator --}}
                        @if($revisionCount > 0)
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-800">
                                {{ $revisionCount }} Revisi
                            </span>
                        @endif
                        
                        {{-- Status Badge --}}
                        @php
                            $statusConfig = [
                                'Lebih Bayar' => [
                                    'light' => ['bg-green-50', 'text-green-700', 'border-green-200'],
                                    'dark' => ['dark:bg-green-900/20', 'dark:text-green-300', 'dark:border-green-800']
                                ],
                                'Kurang Bayar' => [
                                    'light' => ['bg-red-50', 'text-red-700', 'border-red-200'],
                                    'dark' => ['dark:bg-red-900/20', 'dark:text-red-300', 'dark:border-red-800']
                                ],
                                'Nihil' => [
                                    'light' => ['bg-gray-50', 'text-gray-700', 'border-gray-200'],
                                    'dark' => ['dark:bg-gray-800', 'dark:text-gray-300', 'dark:border-gray-600']
                                ],
                                'Belum Dihitung' => [
                                    'light' => ['bg-yellow-50', 'text-yellow-700', 'border-yellow-200'],
                                    'dark' => ['dark:bg-yellow-900/20', 'dark:text-yellow-300', 'dark:border-yellow-800']
                                ]
                            ];
                            $config = $statusConfig[$status] ?? $statusConfig['Belum Dihitung'];
                            $lightClasses = implode(' ', $config['light']);
                            $darkClasses = implode(' ', $config['dark']);
                        @endphp
                        
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium border {{ $lightClasses }} {{ $darkClasses }}">
                            {{ $status }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                {{-- Key Amount Display --}}
                <div class="text-right">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        @if($effectivePayment > 0)
                            Harus Bayar
                        @elseif($effectivePayment < 0)
                            Kelebihan Bayar
                        @else
                            Nihil
                        @endif
                    </div>
                    <div class="text-lg font-semibold {{ $effectivePayment > 0 ? 'text-red-600 dark:text-red-400' : ($effectivePayment < 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400') }}">
                        Rp {{ number_format(abs($effectivePayment), 0, ',', '.') }}
                    </div>
                </div>

                {{-- Expand Icon --}}
                <div class="flex-shrink-0">
                    <svg 
                        class="w-5 h-5 text-gray-400 dark:text-gray-500 transition-transform duration-200"
                        :class="{ 'rotate-180': isOpen }"
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>
        </div>
    </button>

    {{-- Accordion Content --}}
    <div 
        x-show="isOpen" 
        x-collapse
        class="border-t border-gray-200 dark:border-gray-700"
    >
        <div class="px-6 py-5 space-y-6">
            {{-- Peredaran Bruto Section --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-300">Peredaran Bruto</h4>
                            <p class="text-xs text-blue-600 dark:text-blue-400">Total DPP faktur keluaran (tanpa revisi)</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                            Rp <span x-text="currentPeredaran.toLocaleString('id-ID')">{{ number_format($peredaranBruto, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-xs text-blue-600 dark:text-blue-400">
                            {{ $fakturKeluarCount }} faktur keluaran
                            @if($revisionCount > 0)
                                <span class="text-yellow-600 dark:text-yellow-400">({{ $revisionCount }} revisi dikecualikan)</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Invoice Breakdown --}}
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">Detail Faktur</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div class="text-center">
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $totalInvoices }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Total Faktur</div>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-semibold text-green-600 dark:text-green-400">{{ $fakturKeluarCount }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Faktur Keluar</div>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-semibold text-blue-600 dark:text-blue-400">{{ $fakturMasukCount }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Faktur Masuk</div>
                    </div>
                    @if($revisionCount > 0)
                        <div class="text-center">
                            <div class="text-lg font-semibold text-yellow-600 dark:text-yellow-400">{{ $revisionCount }}</div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">Revisi</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- PPN Breakdown --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center space-x-2">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">PPN Keluar</p>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300" title="Tidak termasuk nomor faktur 02, 03, 07, 08 dan revisi">
                                    Filtered
                                </span>
                            </div>
                            <p class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-1">Rp {{ number_format($ppnKeluar, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $filteredFakturKeluarCount }} dari {{ $fakturKeluarCount }} faktur
                                @if($excludedInvoicesCount > 0)
                                    <span class="text-orange-600 dark:text-orange-400">({{ $excludedInvoicesCount }} dikecualikan)</span>
                                @endif
                            </p>
                        </div>
                        <div class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">PPN Masuk</p>
                            <p class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-1">Rp {{ number_format($ppnMasuk, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $fakturMasukCount }} faktur masuk</p>
                        </div>
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Selisih</p>
                            <p class="text-xl font-semibold {{ $selisihPpn > 0 ? 'text-red-600 dark:text-red-400' : ($selisihPpn < 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-gray-100') }} mt-1">
                                Rp {{ number_format(abs($selisihPpn), 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="w-8 h-8 {{ $selisihPpn > 0 ? 'bg-red-100 dark:bg-red-900/30' : ($selisihPpn < 0 ? 'bg-green-100 dark:bg-green-900/30' : 'bg-gray-100 dark:bg-gray-700') }} rounded-lg flex items-center justify-center">
                            @if($selisihPpn > 0)
                                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9"></path>
                                </svg>
                            @elseif($selisihPpn < 0)
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            @else
                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @endif
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Keluar - Masuk</p>
                </div>
            </div>

            {{-- Filter Information Alert --}}
            <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-orange-800 dark:text-orange-300">Pengecualian Perhitungan</h4>
                        <div class="space-y-2 mt-2">
                            <p class="text-sm text-orange-700 dark:text-orange-400">
                                <strong>Faktur yang dikecualikan:</strong>
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-orange-600 dark:text-orange-400">
                                <div class="space-y-1">
                                    <div>• <strong>Revisi faktur:</strong> Semua faktur revisi</div>
                                    <div>• <strong>Nomor 02:</strong> Ekspor BKP</div>
                                    <div>• <strong>Nomor 03:</strong> Ekspor BKP dengan fasilitas</div>
                                </div>
                                <div class="space-y-1">
                                    <div>• <strong>Nomor 07:</strong> Penyerahan yang PPN-nya tidak dipungut</div>
                                    <div>• <strong>Nomor 08:</strong> Penyerahan yang dibebaskan dari PPN</div>
                                </div>
                            </div>
                            @if($revisionCount > 0 || $excludedInvoicesCount > 0)
                                <div class="mt-3 p-3 bg-white dark:bg-gray-800 border border-orange-200 dark:border-orange-700 rounded text-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="text-orange-700 dark:text-orange-300">Total faktur dikecualikan:</span>
                                        <span class="font-semibold text-orange-800 dark:text-orange-200">
                                            {{ $revisionCount + $excludedInvoicesCount }} faktur
                                            @if($revisionCount > 0 && $excludedInvoicesCount > 0)
                                                ({{ $revisionCount }} revisi + {{ $excludedInvoicesCount }} filter nomor)
                                            @elseif($revisionCount > 0)
                                                ({{ $revisionCount }} revisi)
                                            @else
                                                ({{ $excludedInvoicesCount }} filter nomor)
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Compensation Section (if exists) --}}
            @if($hasCompensation)
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-green-800 dark:text-green-300">Kompensasi Diterima</h4>
                            <p class="text-lg font-bold text-green-700 dark:text-green-400 mt-1">
                                Rp <span x-text="currentAmount.toLocaleString('id-ID')">{{ number_format($compensation, 0, ',', '.') }}</span>
                            </p>
                            <p class="text-xs text-green-600 dark:text-green-400 mt-1">Dari kelebihan pembayaran periode sebelumnya</p>
                            @if($notes)
                                <div class="mt-3 p-3 bg-white dark:bg-gray-800 border border-green-200 dark:border-green-700 rounded text-sm text-green-700 dark:text-green-300">
                                    {{ $notes }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Final Calculation --}}
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">Perhitungan Final</h4>
                
                {{-- Formula Display --}}
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded p-3 mb-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400 text-center font-mono">
                        PPN Keluar* - PPN Masuk* 
                        @if($hasCompensation)
                            - Kompensasi 
                        @endif
                        = Pembayaran Efektif
                    </div>
                    <div class="text-sm text-gray-800 dark:text-gray-200 text-center font-mono mt-1">
                        {{ number_format($ppnKeluar, 0, ',', '.') }} - {{ number_format($ppnMasuk, 0, ',', '.') }} 
                        @if($hasCompensation)
                            - {{ number_format($compensation, 0, ',', '.') }} 
                        @endif
                        = {{ number_format($effectivePayment, 0, ',', '.') }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 text-center mt-2">
                        *Dikecualikan: revisi faktur dan nomor 02,03,07,08 (hanya PPN Keluar)
                    </div>
                </div>

                {{-- Result --}}
                <div class="text-center">
                    <div class="text-2xl font-bold {{ $effectivePayment > 0 ? 'text-red-600 dark:text-red-400' : ($effectivePayment < 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400') }} mb-2">
                        {{ $effectivePayment >= 0 ? '' : '+' }}Rp {{ number_format(abs($effectivePayment), 0, ',', '.') }}
                    </div>
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $effectivePayment > 0 ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' : ($effectivePayment < 0 ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300') }}">
                        @if($effectivePayment > 0)
                            Wajib setor ke kas negara
                        @elseif($effectivePayment < 0)
                            Kelebihan bayar - dapat dikompensasi
                        @else
                            Nihil - tidak ada kewajiban
                        @endif
                    </div>
                </div>
            </div>

            {{-- Summary Information --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <h5 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">Ringkasan Informasi</h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-700 dark:text-blue-300">
                    <div class="space-y-1">
                        <p>• <strong>Peredaran Bruto:</strong> Rp {{ number_format($peredaranBruto, 0, ',', '.') }}</p>
                        <p>• <strong>Total Faktur:</strong> {{ $totalInvoices }} faktur ({{ $fakturKeluarCount }} keluar, {{ $fakturMasukCount }} masuk)</p>
                        <p>• <strong>Faktur Diproses:</strong> {{ $filteredFakturKeluarCount }} dari {{ $fakturKeluarCount }} faktur keluar</p>
                        @if($hasCompensation)
                            <p>• <strong>Kompensasi:</strong> Rp {{ number_format($compensation, 0, ',', '.') }}</p>
                        @else
                            <p>• <strong>Kompensasi:</strong> Tidak ada</p>
                        @endif
                    </div>
                    <div class="space-y-1">
                        <p>• <strong>Status Akhir:</strong> {{ $status }}</p>
                        @if($revisionCount > 0)
                            <p>• <strong>Revisi Dikecualikan:</strong> {{ $revisionCount }} faktur</p>
                        @endif
                        @if($excludedInvoicesCount > 0)
                            <p>• <strong>Filter Nomor:</strong> {{ $excludedInvoicesCount }} faktur dikecualikan</p>
                        @endif
                        <p>• <strong>Perhitungan:</strong> Otomatis berdasarkan data faktur asli</p>
                    </div>
                </div>
            </div>

            {{-- Show excluded invoices details if any --}}
            @if($record && ($excludedInvoicesCount > 0 || $revisionCount > 0))
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <button 
                        type="button"
                        x-data="{ showDetails: false }"
                        @click="showDetails = !showDetails"
                        class="w-full text-left focus:outline-none"
                    >
                        <div class="flex items-center justify-between">
                            <h5 class="text-sm font-semibold text-yellow-800 dark:text-yellow-300">Detail Faktur Dikecualikan</h5>
                            <svg 
                                class="w-4 h-4 text-yellow-600 dark:text-yellow-400 transition-transform duration-200"
                                :class="{ 'rotate-180': showDetails }"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        
                        <div x-show="showDetails" x-collapse class="mt-3">
                            @if($revisionCount > 0)
                                <div class="mb-3">
                                    <h6 class="text-xs font-medium text-yellow-700 dark:text-yellow-300 mb-2">Faktur Revisi ({{ $revisionCount }}):</h6>
                                    <div class="bg-white dark:bg-gray-800 border border-yellow-200 dark:border-yellow-700 rounded p-2 text-xs text-yellow-700 dark:text-yellow-300">
                                        Semua faktur dengan status revisi dikecualikan dari perhitungan untuk menghindari duplikasi data.
                                    </div>
                                </div>
                            @endif
                            
                            @if($excludedInvoicesCount > 0)
                                <div>
                                    <h6 class="text-xs font-medium text-yellow-700 dark:text-yellow-300 mb-2">Faktur dengan Nomor Dikecualikan ({{ $excludedInvoicesCount }}):</h6>
                                    <div class="space-y-1">
                                        @php
                                            $filteredOut = $record->getFilteredOutInvoices();
                                        @endphp
                                        @forelse($filteredOut->take(5) as $invoice)
                                            <div class="bg-white dark:bg-gray-800 border border-yellow-200 dark:border-yellow-700 rounded p-2 text-xs">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <span class="font-medium text-yellow-800 dark:text-yellow-200">{{ $invoice->invoice_number }}</span>
                                                        <span class="text-yellow-600 dark:text-yellow-400"> - {{ $invoice->company_name }}</span>
                                                    </div>
                                                    <div class="text-right text-yellow-700 dark:text-yellow-300">
                                                        <div>DPP: Rp {{ number_format($invoice->dpp, 0, ',', '.') }}</div>
                                                        <div>PPN: Rp {{ number_format($invoice->ppn, 0, ',', '.') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-xs text-yellow-600 dark:text-yellow-400">Tidak ada data faktur yang dikecualikan.</p>
                                        @endforelse
                                        
                                        @if($filteredOut->count() > 5)
                                            <div class="text-xs text-yellow-600 dark:text-yellow-400 text-center pt-2">
                                                Dan {{ $filteredOut->count() - 5 }} faktur lainnya...
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Footer --}}
    <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
            <div class="flex items-center space-x-4">
                <span class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    {{ $hasCompensation ? 'Dengan kompensasi' : 'Tanpa kompensasi' }}
                </span>
                <span class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Mengecualikan revisi & filter nomor
                </span>
            </div>
            @if($record && $record->updated_at)
                <span>Diperbarui: {{ $record->updated_at->format('d M Y H:i') }}</span>
            @endif
        </div>
    </div>
</div>