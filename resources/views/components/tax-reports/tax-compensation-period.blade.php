{{-- resources/views/components/tax-reports/tax-compensation-period.blade.php --}}

@props([
    'record' => null,
    'currentSelisih' => 0,
    'currentCompensation' => 0,
    'effectiveAmount' => 0,
    'showTitle' => true
])

@php
    $statusColor = $effectiveAmount > 0 ? 'red' : ($effectiveAmount < 0 ? 'green' : 'gray');
    $statusLabel = $effectiveAmount > 0 ? 'Harus Bayar' : ($effectiveAmount < 0 ? 'Kelebihan Bayar' : 'Nihil');
    $hasCompensation = $currentCompensation > 0;
@endphp

<div 
    x-data="{ isOpen: false }"
    {{ $attributes->merge(['class' => 'bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden']) }}
>
    {{-- Accordion Header --}}
    <button 
        type="button"
        @click="isOpen = !isOpen"
        class="w-full px-6 py-4 text-left hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset"
    >
        <div class="flex items-center justify-between">
            {{-- Left side: Title and basic info --}}
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Periode Saat Ini</h3>
                    <div class="flex items-center space-x-3 mt-1">
                        <p class="text-sm text-gray-600">{{ $record && $record->client ? $record->client->name : 'N/A' }}</p>
                        <span class="text-gray-400">•</span>
                        <p class="text-sm text-gray-600">{{ $record ? $record->month : 'N/A' }}</p>
                        @if($hasCompensation)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Ada Kompensasi
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right side: Key metrics and expand button --}}
            <div class="flex items-center space-x-4">
                {{-- Status Badge --}}
                <div class="text-right">
                    <div class="text-sm text-gray-500 mb-1">{{ $statusLabel }}</div>
                    <div class="text-xl font-bold {{ $effectiveAmount > 0 ? 'text-red-600' : ($effectiveAmount < 0 ? 'text-green-600' : 'text-gray-600') }}">
                        {{ $effectiveAmount >= 0 ? '' : '+' }}Rp {{ number_format(abs($effectiveAmount), 0, ',', '.') }}
                    </div>
                </div>

                {{-- Expand Button --}}
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
            {{-- Detailed Breakdown --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Left Column: Current Calculation --}}
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-gray-800 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Perhitungan PPN
                    </h4>

                    {{-- PPN Calculation Cards --}}
                    <div class="space-y-3">
                        {{-- Selisih PPN --}}
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 {{ $currentSelisih > 0 ? 'bg-red-100' : ($currentSelisih < 0 ? 'bg-green-100' : 'bg-gray-100') }} rounded-lg flex items-center justify-center">
                                        @if($currentSelisih > 0)
                                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                            </svg>
                                        @elseif($currentSelisih < 0)
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Selisih PPN</p>
                                        <p class="text-xs text-gray-500">PPN Keluar - PPN Masuk</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold {{ $currentSelisih > 0 ? 'text-red-600' : ($currentSelisih < 0 ? 'text-green-600' : 'text-gray-600') }}">
                                        {{ $currentSelisih >= 0 ? '' : '+' }}Rp {{ number_format(abs($currentSelisih), 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Current Compensation --}}
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 {{ $hasCompensation ? 'bg-green-100' : 'bg-gray-100' }} rounded-lg flex items-center justify-center">
                                        @if($hasCompensation)
                                            <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Kompensasi Saat Ini</p>
                                        <p class="text-xs text-gray-500">Dari periode sebelumnya</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold {{ $hasCompensation ? 'text-green-600' : 'text-gray-500' }}">
                                        {{ $hasCompensation ? '-' : '' }}Rp {{ number_format($currentCompensation, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Final Result --}}
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-gray-800 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Hasil Akhir
                    </h4>

                    {{-- Final Result Card --}}
                    <div class="bg-gradient-to-br {{ $effectiveAmount > 0 ? 'from-red-50 to-red-100 border-red-200' : ($effectiveAmount < 0 ? 'from-green-50 to-green-100 border-green-200' : 'from-gray-50 to-gray-100 border-gray-200') }} border rounded-lg p-6">
                        <div class="text-center space-y-3">
                            {{-- Icon --}}
                            <div class="flex justify-center">
                                <div class="w-16 h-16 {{ $effectiveAmount > 0 ? 'bg-red-100' : ($effectiveAmount < 0 ? 'bg-green-100' : 'bg-gray-100') }} rounded-full flex items-center justify-center">
                                    @if($effectiveAmount > 0)
                                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                    @elseif($effectiveAmount < 0)
                                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @endif
                                </div>
                            </div>

                            {{-- Amount --}}
                            <div>
                                <div class="text-3xl font-bold {{ $effectiveAmount > 0 ? 'text-red-600' : ($effectiveAmount < 0 ? 'text-green-600' : 'text-gray-600') }} mb-2">
                                    {{ $effectiveAmount >= 0 ? '' : '+' }}Rp {{ number_format(abs($effectiveAmount), 0, ',', '.') }}
                                </div>
                                <div class="text-lg font-semibold {{ $effectiveAmount > 0 ? 'text-red-800' : ($effectiveAmount < 0 ? 'text-green-800' : 'text-gray-800') }} mb-1">
                                    {{ $statusLabel }}
                                </div>
                                <div class="text-sm {{ $effectiveAmount > 0 ? 'text-red-600' : ($effectiveAmount < 0 ? 'text-green-600' : 'text-gray-600') }}">
                                    @if($effectiveAmount > 0)
                                        Wajib setor ke kas negara
                                    @elseif($effectiveAmount < 0)
                                        Kelebihan yang dapat dikompensasi
                                    @else
                                        Tidak ada kewajiban
                                    @endif
                                </div>
                            </div>

                            {{-- Calculation Formula --}}
                            <div class="mt-4 pt-4 border-t {{ $effectiveAmount > 0 ? 'border-red-200' : ($effectiveAmount < 0 ? 'border-green-200' : 'border-gray-200') }}">
                                <div class="text-xs text-gray-600 font-mono">
                                    {{ number_format($currentSelisih, 0, ',', '.') }} 
                                    @if($hasCompensation)
                                        - {{ number_format($currentCompensation, 0, ',', '.') }}
                                    @endif
                                    = {{ number_format($effectiveAmount, 0, ',', '.') }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Selisih PPN{{ $hasCompensation ? ' - Kompensasi' : '' }} = {{ $statusLabel }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Additional Information --}}
            @if($hasCompensation || $effectiveAmount > 0)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h5 class="text-sm font-semibold text-blue-800 mb-2">💡 Informasi Kompensasi</h5>
                            <div class="text-sm text-blue-700 space-y-1">
                                @if($hasCompensation)
                                    <p>• Kompensasi Rp {{ number_format($currentCompensation, 0, ',', '.') }} sudah diperhitungkan dalam jumlah di atas</p>
                                @endif
                                @if($effectiveAmount > 0)
                                    <p>• Anda dapat menambah kompensasi dari periode sebelumnya untuk mengurangi kewajiban</p>
                                    <p>• Pilih periode sumber kompensasi pada bagian selanjutnya</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Footer --}}
    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
        <div class="flex items-center justify-between text-xs text-gray-500">
            <span class="flex items-center">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                </svg>
                {{ $hasCompensation ? 'Dengan kompensasi aktif' : 'Belum ada kompensasi' }}
            </span>
            @if($record && $record->updated_at)
                <span>Diperbarui: {{ $record->updated_at->format('d M Y H:i') }}</span>
            @endif
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    
    @media (max-width: 1024px) {
        .tax-compensation-period .lg\\:grid-cols-2 {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 768px) {
        .tax-compensation-period .text-3xl {
            font-size: 1.75rem;
        }
        
        .tax-compensation-period .text-xl {
            font-size: 1.125rem;
        }
        
        .tax-compensation-period .text-lg {
            font-size: 1rem;
        }
    }
    
    @media (max-width: 640px) {
        .tax-compensation-period .space-x-4 > * + * {
            margin-left: 0;
            margin-top: 0.5rem;
        }
        
        .tax-compensation-period .flex-col-mobile {
            flex-direction: column;
            align-items: flex-start !important;
        }
    }
</style>