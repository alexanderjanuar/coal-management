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
    
    // Basic metrics for all tax reports
    $ppnKeluar = $record && $record->exists ? $record->getTotalPpnKeluar() : 0;
    $ppnMasuk = $record && $record->exists ? $record->getTotalPpnMasuk() : 0;
    $selisihPpn = $ppnKeluar - $ppnMasuk;
    $effectivePayment = $selisihPpn - $compensation;
    $status = $record && $record->exists ? ($record->invoice_tax_status ?? 'Belum Dihitung') : 'Belum Dihitung';
@endphp

<div 
    x-data="{ 
        isOpen: false,
        currentAmount: 0,
        targetAmount: {{ $compensation }}
    }"
    x-init="
        if (targetAmount > 0) {
            let duration = 800;
            let start = Date.now();
            
            function animate() {
                let elapsed = Date.now() - start;
                let progress = Math.min(elapsed / duration, 1);
                currentAmount = Math.floor(targetAmount * progress);
                
                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    currentAmount = targetAmount;
                }
            }
            animate();
        }
    "
    {{ $attributes->merge(['class' => 'bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden']) }}
>
    {{-- Accordion Header --}}
    <button 
        type="button"
        @click="isOpen = !isOpen"
        class="w-full px-6 py-4 text-left hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-inset"
    >
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                
                <div class="min-w-0 flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Ringkasan PPN</h3>
                    <div class="flex items-center space-x-4 mt-1">
                        <p class="text-sm text-gray-600">{{ $record && $record->client ? $record->client->name : 'Tax Report' }} • {{ $record ? $record->month : 'N/A' }}</p>
                        
                        {{-- Status Badge --}}
                        @php
                            $statusConfig = [
                                'Lebih Bayar' => ['bg-green-50', 'text-green-700', 'border-green-200'],
                                'Kurang Bayar' => ['bg-red-50', 'text-red-700', 'border-red-200'],
                                'Nihil' => ['bg-gray-50', 'text-gray-700', 'border-gray-200'],
                                'Belum Dihitung' => ['bg-yellow-50', 'text-yellow-700', 'border-yellow-200']
                            ];
                            $config = $statusConfig[$status] ?? $statusConfig['Belum Dihitung'];
                        @endphp
                        
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium border {{ implode(' ', $config) }}">
                            {{ $status }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                {{-- Key Amount Display --}}
                <div class="text-right">
                    <div class="text-sm text-gray-500">
                        @if($effectivePayment > 0)
                            Harus Bayar
                        @elseif($effectivePayment < 0)
                            Kelebihan Bayar
                        @else
                            Nihil
                        @endif
                    </div>
                    <div class="text-lg font-semibold {{ $effectivePayment > 0 ? 'text-red-600' : ($effectivePayment < 0 ? 'text-green-600' : 'text-gray-600') }}">
                        Rp {{ number_format(abs($effectivePayment), 0, ',', '.') }}
                    </div>
                </div>

                {{-- Expand Icon --}}
                <div class="flex-shrink-0">
                    <svg 
                        class="w-5 h-5 text-gray-400 transition-transform duration-200"
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
        class="border-t border-gray-200"
    >
        <div class="px-6 py-5 space-y-6">
            {{-- PPN Breakdown --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">PPN Keluar</p>
                            <p class="text-xl font-semibold text-gray-900 mt-1">Rp {{ number_format($ppnKeluar, 0, ',', '.') }}</p>
                        </div>
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Pajak dari penjualan</p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">PPN Masuk</p>
                            <p class="text-xl font-semibold text-gray-900 mt-1">Rp {{ number_format($ppnMasuk, 0, ',', '.') }}</p>
                        </div>
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Pajak dari pembelian</p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Selisih</p>
                            <p class="text-xl font-semibold {{ $selisihPpn > 0 ? 'text-red-600' : ($selisihPpn < 0 ? 'text-green-600' : 'text-gray-900') }} mt-1">
                                Rp {{ number_format(abs($selisihPpn), 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="w-8 h-8 {{ $selisihPpn > 0 ? 'bg-red-100' : ($selisihPpn < 0 ? 'bg-green-100' : 'bg-gray-100') }} rounded-lg flex items-center justify-center">
                            @if($selisihPpn > 0)
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9"></path>
                                </svg>
                            @elseif($selisihPpn < 0)
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            @else
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @endif
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Keluar - Masuk</p>
                </div>
            </div>

            {{-- Compensation Section (if exists) --}}
            @if($hasCompensation)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-green-800">Kompensasi Diterima</h4>
                            <p class="text-lg font-bold text-green-700 mt-1">
                                Rp <span x-text="currentAmount.toLocaleString('id-ID')">{{ number_format($compensation, 0, ',', '.') }}</span>
                            </p>
                            <p class="text-xs text-green-600 mt-1">Dari kelebihan pembayaran periode sebelumnya</p>
                            @if($notes)
                                <div class="mt-3 p-3 bg-white border border-green-200 rounded text-sm text-green-700">
                                    {{ $notes }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Final Calculation --}}
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-gray-800 mb-3">Perhitungan Final</h4>
                
                {{-- Formula Display --}}
                <div class="bg-white border border-gray-200 rounded p-3 mb-4">
                    <div class="text-sm text-gray-600 text-center font-mono">
                        PPN Keluar - PPN Masuk 
                        @if($hasCompensation)
                            - Kompensasi 
                        @endif
                        = Pembayaran Efektif
                    </div>
                    <div class="text-sm text-gray-800 text-center font-mono mt-1">
                        {{ number_format($ppnKeluar, 0, ',', '.') }} - {{ number_format($ppnMasuk, 0, ',', '.') }} 
                        @if($hasCompensation)
                            - {{ number_format($compensation, 0, ',', '.') }} 
                        @endif
                        = {{ number_format($effectivePayment, 0, ',', '.') }}
                    </div>
                </div>

                {{-- Result --}}
                <div class="text-center">
                    <div class="text-2xl font-bold {{ $effectivePayment > 0 ? 'text-red-600' : ($effectivePayment < 0 ? 'text-green-600' : 'text-gray-600') }} mb-2">
                        {{ $effectivePayment >= 0 ? '' : '+' }}Rp {{ number_format(abs($effectivePayment), 0, ',', '.') }}
                    </div>
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $effectivePayment > 0 ? 'bg-red-100 text-red-800' : ($effectivePayment < 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
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

            {{-- Additional Information --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h5 class="text-sm font-semibold text-blue-800 mb-2">Informasi</h5>
                <div class="text-sm text-blue-700 space-y-1">
                    @if($hasCompensation)
                        <p>• Kompensasi sebesar <strong>Rp {{ number_format($compensation, 0, ',', '.') }}</strong> telah mengurangi kewajiban PPN</p>
                        <p>• Kompensasi berasal dari kelebihan pembayaran periode sebelumnya</p>
                    @else
                        <p>• Tidak ada kompensasi untuk periode ini</p>
                        <p>• Perhitungan berdasarkan selisih PPN Keluar dan PPN Masuk</p>
                    @endif
                    <p>• Total {{ $record ? $record->invoices()->count() : 0 }} faktur telah diperhitungkan</p>
                    <p>• Status: <strong>{{ $status }}</strong></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
        <div class="flex items-center justify-between text-xs text-gray-500">
            <div class="flex items-center space-x-4">
                <span class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    {{ $hasCompensation ? 'Dengan kompensasi' : 'Tanpa kompensasi' }}
                </span>
                <span class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" clip-rule="evenodd"></path>
                    </svg>
                    Otomatis dihitung
                </span>
            </div>
            @if($record && $record->updated_at)
                <span>Diperbarui: {{ $record->updated_at->format('d M Y H:i') }}</span>
            @endif
        </div>
    </div>
</div>

{{-- Minimal CSS for accordion --}}
<style>
    [x-cloak] { display: none !important; }
    
    @media (max-width: 768px) {
        .tax-compensation-info .md\\:grid-cols-3 {
            grid-template-columns: 1fr;
        }
        
        .tax-compensation-info .text-xl {
            font-size: 1.125rem;
        }
        
        .tax-compensation-info .text-2xl {
            font-size: 1.25rem;
        }
    }
    
    @media (max-width: 640px) {
        .tax-compensation-info .space-x-4 > * + * {
            margin-left: 0;
            margin-top: 0.25rem;
        }
    }
</style>