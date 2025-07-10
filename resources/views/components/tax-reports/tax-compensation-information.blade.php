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
    
    // Updated metrics with new filtering logic
    $ppnKeluar = $record && $record->exists ? $record->getTotalPpnKeluarFiltered() : 0;
    $ppnMasuk = $record && $record->exists ? $record->getTotalPpnMasukFiltered() : 0;
    $peredaranBruto = $record && $record->exists ? $record->getPeredaranBruto() : 0;
    $selisihPpn = $ppnKeluar - $ppnMasuk;
    $effectivePayment = $selisihPpn - $compensation;
    $status = $record && $record->exists ? ($record->invoice_tax_status ?? 'Belum Dihitung') : 'Belum Dihitung';
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
            {{-- Peredaran Bruto Section (New) --}}
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
                            <p class="text-xs text-blue-600 dark:text-blue-400">Total DPP faktur keluaran (tanpa filter)</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                            Rp <span x-text="currentPeredaran.toLocaleString('id-ID')">{{ number_format($peredaranBruto, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-xs text-blue-600 dark:text-blue-400">
                            {{ $record ? $record->invoices()->where('type', 'Faktur Keluaran')->count() : 0 }} faktur keluaran
                        </div>
                    </div>
                </div>
            </div>

            {{-- PPN Breakdown --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center space-x-2">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">PPN Keluar</p>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300" title="Tidak termasuk nomor faktur 02, 03, 07, 08">
                                    Filtered
                                </span>
                            </div>
                            <p class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-1">Rp {{ number_format($ppnKeluar, 0, ',', '.') }}</p>
                        </div>
                        <div class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Pajak dari penjualan*</p>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">PPN Masuk</p>
                            <p class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-1">Rp {{ number_format($ppnMasuk, 0, ',', '.') }}</p>
                        </div>
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Pajak dari pembelian</p>
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
                        <h4 class="text-sm font-semibold text-orange-800 dark:text-orange-300">Filter Nomor Faktur</h4>
                        <p class="text-sm text-orange-700 dark:text-orange-400 mt-1">
                            Perhitungan PPN mengecualikan faktur dengan nomor berawalan: <strong>02, 03, 07, 08</strong>
                        </p>
                        <div class="text-xs text-orange-600 dark:text-orange-400 mt-2 space-y-1">
                            <div>• 02: Ekspor BKP</div>
                            <div>• 03: Ekspor BKP dengan fasilitas</div>
                            <div>• 07: Penyerahan yang PPN-nya tidak dipungut</div>
                            <div>• 08: Penyerahan yang dibebaskan dari PPN</div>
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
                        *Filter hanya berlaku untuk PPN Keluar, tidak untuk PPN Masuk
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
                        <p>• <strong>Total Faktur:</strong> {{ $record ? $record->invoices()->count() : 0 }} faktur</p>
                        @if($hasCompensation)
                            <p>• <strong>Kompensasi:</strong> Rp {{ number_format($compensation, 0, ',', '.') }}</p>
                        @else
                            <p>• <strong>Kompensasi:</strong> Tidak ada</p>
                        @endif
                    </div>
                    <div class="space-y-1">
                        <p>• <strong>Status Akhir:</strong> {{ $status }}</p>
                        <p>• <strong>Filter Aktif:</strong> Mengecualikan nomor 02,03,07,08</p>
                        <p>• <strong>Perhitungan:</strong> Otomatis berdasarkan data faktur</p>
                    </div>
                </div>
            </div>
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
                    Dengan filter nomor faktur
                </span>
            </div>
            @if($record && $record->updated_at)
                <span>Diperbarui: {{ $record->updated_at->format('d M Y H:i') }}</span>
            @endif
        </div>
    </div>
</div>

{{-- Enhanced CSS for accordion with dark mode support --}}
<style>
    [x-cloak] { display: none !important; }
    
    /* Dark mode transition support */
    .tax-compensation-info * {
        transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out, color 0.2s ease-in-out;
    }
    
    /* Dark mode scrollbar styling */
    @media (prefers-color-scheme: dark) {
        .tax-compensation-info ::-webkit-scrollbar-thumb {
            background: rgb(75 85 99);
            border-radius: 4px;
        }
        
        .tax-compensation-info ::-webkit-scrollbar-thumb:hover {
            background: rgb(107 114 128);
        }
    }
    
    /* Enhanced focus styles for dark mode */
    .dark .tax-compensation-info button:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgb(251 191 36 / 0.5);
    }
    
    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .tax-compensation-info .md\\:grid-cols-3 {
            grid-template-columns: 1fr;
        }
        
        .tax-compensation-info .md\\:grid-cols-2 {
            grid-template-columns: 1fr;
        }
        
        .tax-compensation-info .text-xl {
            font-size: 1.125rem;
        }
        
        .tax-compensation-info .text-2xl {
            font-size: 1.25rem;
        }
        
        .tax-compensation-info .px-6 {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .tax-compensation-info .space-x-4 {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .tax-compensation-info .space-x-4 > * + * {
            margin-left: 0;
            margin-top: 0.5rem;
        }
    }
    
    @media (max-width: 640px) {
        .tax-compensation-info .text-right {
            text-align: left;
            margin-top: 0.5rem;
        }
        
        .tax-compensation-info .justify-between {
            flex-direction: column;
            align-items: flex-start;
        }
    }
    
    /* Animation enhancements */
    .tax-compensation-info [x-collapse] {
        overflow: hidden;
        transition: max-height 0.3s ease-in-out;
    }
    
    /* High contrast mode support */
    @media (prefers-contrast: high) {
        .tax-compensation-info .border {
            border-width: 2px;
        }
        
        .tax-compensation-info .bg-gray-50 {
            background-color: rgb(249 250 251);
        }
        
        .dark .tax-compensation-info .bg-gray-50 {
            background-color: rgb(31 41 55);
        }
    }
    
    /* Reduced motion support */
    @media (prefers-reduced-motion: reduce) {
        .tax-compensation-info * {
            transition: none !important;
            animation: none !important;
        }
    }
    
    /* Filter badge pulse animation */
    @keyframes pulse-orange {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.8;
        }
    }
    
    .tax-compensation-info .filter-badge {
        animation: pulse-orange 2s ease-in-out infinite;
    }
    
    /* Custom scrollbar for content areas */
    .tax-compensation-info .overflow-auto {
        scrollbar-width: thin;
        scrollbar-color: rgb(156 163 175) rgb(243 244 246);
    }
    
    .dark .tax-compensation-info .overflow-auto {
        scrollbar-color: rgb(75 85 99) rgb(55 65 81);
    }
    
    /* Enhanced hover effects */
    .tax-compensation-info .hover\\:scale-105:hover {
        transform: scale(1.05);
        transition: transform 0.2s ease-in-out;
    }
    
    /* Smooth transitions for all interactive elements */
    .tax-compensation-info button,
    .tax-compensation-info .transition-all {
        transition: all 0.2s ease-in-out;
    }
    
    /* Enhanced card shadows */
    .tax-compensation-info .shadow-enhanced {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    .dark .tax-compensation-info .shadow-enhanced {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
    }
    
    /* Print styles */
    @media print {
        .tax-compensation-info {
            break-inside: avoid;
        }
        
        .tax-compensation-info [x-show] {
            display: block !important;
        }
        
        .tax-compensation-info .no-print {
            display: none !important;
        }
    }
</style>