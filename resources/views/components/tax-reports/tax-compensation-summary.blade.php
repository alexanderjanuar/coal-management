{{-- resources/views/components/tax-reports/tax-compensation-summary.blade.php --}}

@props([
    'effectiveAmount' => 0,
    'currentCompensation' => 0,
    'totalNewCompensation' => 0,
    'finalAmount' => 0,
    'showTitle' => true
])

@php
    $totalCompensation = $currentCompensation + $totalNewCompensation;
    $isFullyCompensated = $finalAmount <= 0;
    $hasNewCompensation = $totalNewCompensation > 0;
    
    // Status determination
    $finalStatus = $finalAmount > 0 ? 'Kurang Bayar' : ($finalAmount < 0 ? 'Lebih Bayar' : 'Nihil');
    $finalStatusColor = $finalAmount > 0 ? 'red' : ($finalAmount < 0 ? 'green' : 'gray');
    $savingsAmount = min($totalNewCompensation, $effectiveAmount);
@endphp

<div 
    x-data="{ 
        isOpen: false,
        animateAmount: false,
        currentFinalAmount: {{ abs($finalAmount) }},
        targetFinalAmount: {{ abs($finalAmount) }},
        showCelebration: false
    }"
    x-init="
        // Animate the final amount when accordion opens
        $watch('isOpen', value => {
            if (value && targetFinalAmount !== currentFinalAmount) {
                setTimeout(() => {
                    animateAmount = true;
                    let start = {{ $effectiveAmount }};
                    let duration = 1000;
                    let startTime = Date.now();
                    
                    function animate() {
                        let elapsed = Date.now() - startTime;
                        let progress = Math.min(elapsed / duration, 1);
                        let easeOutQuart = 1 - Math.pow(1 - progress, 4);
                        currentFinalAmount = Math.floor(start - (start - targetFinalAmount) * easeOutQuart);
                        
                        if (progress < 1) {
                            requestAnimationFrame(animate);
                        } else {
                            currentFinalAmount = targetFinalAmount;
                            @if($isFullyCompensated)
                                showCelebration = true;
                                setTimeout(() => showCelebration = false, 2000);
                            @endif
                        }
                    }
                    animate();
                }, 200);
            }
        });
    "
    {{ $attributes->merge(['class' => 'bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden']) }}
>
    {{-- Accordion Header --}}
    <button 
        type="button"
        @click="isOpen = !isOpen"
        class="w-full px-6 py-4 text-left hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset"
    >
        <div class="flex items-center justify-between">
            {{-- Left side: Title and key metrics --}}
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Ringkasan Kompensasi</h3>
                    <div class="flex items-center space-x-4 mt-1">
                        <p class="text-sm text-gray-600">
                            Kewajiban: Rp {{ number_format($effectiveAmount, 0, ',', '.') }}
                        </p>
                        @if($hasNewCompensation)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                                </svg>
                                Kompensasi: Rp {{ number_format($totalNewCompensation, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right side: Final result and expand button --}}
            <div class="flex items-center space-x-4">
                {{-- Final Amount Display --}}
                <div class="text-right">
                    <div class="text-sm text-gray-500 mb-1">{{ $finalStatus }}</div>
                    <div class="text-xl font-bold {{ $finalAmount > 0 ? 'text-red-600' : ($finalAmount < 0 ? 'text-green-600' : 'text-gray-600') }}">
                        {{ $finalAmount >= 0 ? '' : '+' }}Rp {{ number_format(abs($finalAmount), 0, ',', '.') }}
                    </div>
                    @if($isFullyCompensated)
                        <div class="text-xs text-green-600 font-medium">🎉 Terpenuhi!</div>
                    @elseif($savingsAmount > 0)
                        <div class="text-xs text-blue-600">Hemat: Rp {{ number_format($savingsAmount, 0, ',', '.') }}</div>
                    @endif
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
            {{-- Calculation Breakdown --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Left Column: Step by Step Calculation --}}
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-gray-800 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Perhitungan Langkah demi Langkah
                    </h4>

                    <div class="space-y-3">
                        {{-- Step 1: Initial Obligation --}}
                        <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                        <span class="text-sm font-bold text-red-600">1</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-red-800">Kewajiban Awal</p>
                                        <p class="text-xs text-red-600">Sebelum kompensasi</p>
                                    </div>
                                </div>
                                <div class="text-lg font-bold text-red-600">
                                    Rp {{ number_format($effectiveAmount, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: New Compensation --}}
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <span class="text-sm font-bold text-blue-600">2</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-blue-800">Kompensasi Diterapkan</p>
                                        <p class="text-xs text-blue-600">Dari periode sebelumnya</p>
                                    </div>
                                </div>
                                <div class="text-lg font-bold text-blue-600">
                                    -Rp {{ number_format($totalNewCompensation, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        {{-- Step 3: Final Result --}}
                        <div class="bg-{{ $finalStatusColor }}-50 rounded-lg p-4 border border-{{ $finalStatusColor }}-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-{{ $finalStatusColor }}-100 rounded-lg flex items-center justify-center">
                                        <span class="text-sm font-bold text-{{ $finalStatusColor }}-600">=</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-{{ $finalStatusColor }}-800">{{ $finalStatus }}</p>
                                        <p class="text-xs text-{{ $finalStatusColor }}-600">Hasil akhir</p>
                                    </div>
                                </div>
                                <div class="text-lg font-bold text-{{ $finalStatusColor }}-600">
                                    {{ $finalAmount >= 0 ? '' : '+' }}Rp {{ number_format(abs($finalAmount), 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Formula Display --}}
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h5 class="text-xs font-semibold text-gray-700 mb-2">Formula:</h5>
                        <div class="text-center font-mono text-sm">
                            <div class="flex items-center justify-center space-x-2 text-gray-700">
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">{{ number_format($effectiveAmount, 0, ',', '.') }}</span>
                                <span class="text-gray-500">-</span>
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs">{{ number_format($totalNewCompensation, 0, ',', '.') }}</span>
                                <span class="text-gray-500">=</span>
                                <span class="px-2 py-1 bg-{{ $finalStatusColor }}-100 text-{{ $finalStatusColor }}-700 rounded text-xs font-bold">
                                    {{ number_format(abs($finalAmount), 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Final Result with Animation --}}
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-gray-800 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-{{ $finalStatusColor }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Hasil Akhir
                    </h4>

                    {{-- Large Result Display --}}
                    <div class="relative">
                        {{-- Celebration Overlay --}}
                        @if($isFullyCompensated)
                            <div 
                                x-show="showCelebration"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute inset-0 bg-green-500 bg-opacity-10 rounded-lg flex items-center justify-center z-10"
                            >
                                <div class="text-6xl animate-pulse">🎉</div>
                            </div>
                        @endif

                        <div class="bg-gradient-to-br {{ $finalAmount > 0 ? 'from-red-50 to-red-100 border-red-200' : ($finalAmount < 0 ? 'from-green-50 to-green-100 border-green-200' : 'from-gray-50 to-gray-100 border-gray-200') }} border rounded-xl p-6">
                            <div class="text-center space-y-4">
                                {{-- Icon --}}
                                <div class="flex justify-center">
                                    <div class="w-16 h-16 {{ $finalAmount > 0 ? 'bg-red-100' : ($finalAmount < 0 ? 'bg-green-100' : 'bg-gray-100') }} rounded-full flex items-center justify-center">
                                        @if($finalAmount > 0)
                                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                            </svg>
                                        @elseif($finalAmount < 0)
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

                                {{-- Animated Amount --}}
                                <div>
                                    <div class="text-3xl font-bold {{ $finalAmount > 0 ? 'text-red-600' : ($finalAmount < 0 ? 'text-green-600' : 'text-gray-600') }} mb-2">
                                        {{ $finalAmount >= 0 ? '' : '+' }}Rp <span x-text="currentFinalAmount.toLocaleString('id-ID')">{{ number_format(abs($finalAmount), 0, ',', '.') }}</span>
                                    </div>
                                    <div class="text-lg font-semibold {{ $finalAmount > 0 ? 'text-red-800' : ($finalAmount < 0 ? 'text-green-800' : 'text-gray-800') }} mb-2">
                                        {{ $finalStatus }}
                                    </div>
                                    <div class="text-sm {{ $finalAmount > 0 ? 'text-red-600' : ($finalAmount < 0 ? 'text-green-600' : 'text-gray-600') }}">
                                        @if($finalAmount > 0)
                                            Masih perlu dibayar ke kas negara
                                        @elseif($finalAmount < 0)
                                            Kelebihan untuk kompensasi periode berikutnya
                                        @else
                                            Tidak ada kewajiban - seimbang
                                        @endif
                                    </div>
                                </div>

                                {{-- Success Message --}}
                                @if($isFullyCompensated)
                                    <div class="bg-green-100 border border-green-300 rounded-lg p-3">
                                        <div class="flex items-center justify-center space-x-2 text-green-800">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="font-semibold">🎉 Kewajiban Terpenuhi!</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Additional Info Cards --}}
                    @if($savingsAmount > 0)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-blue-800">💰 Penghematan</p>
                                    <p class="text-lg font-bold text-blue-600">Rp {{ number_format($savingsAmount, 0, ',', '.') }}</p>
                                    <p class="text-xs text-blue-600">Dari kompensasi yang diterapkan</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
        <div class="flex items-center justify-between text-xs text-gray-500">
            <span class="flex items-center">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                </svg>
                {{ $hasNewCompensation ? 'Kompensasi diterapkan' : 'Siap untuk kompensasi' }}
            </span>
            <span class="flex items-center">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" clip-rule="evenodd"></path>
                </svg>
                Perhitungan real-time
            </span>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: .5;
        }
    }
    
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    @media (max-width: 1024px) {
        .tax-compensation-summary .lg\\:grid-cols-2 {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 768px) {
        .tax-compensation-summary .text-3xl {
            font-size: 1.75rem;
        }
        
        .tax-compensation-summary .text-xl {
            font-size: 1.125rem;
        }
        
        .tax-compensation-summary .text-lg {
            font-size: 1rem;
        }
        
        .tax-compensation-summary .w-16.h-16 {
            width: 3rem;
            height: 3rem;
        }
        
        .tax-compensation-summary .w-8.h-8 {
            width: 2rem;
            height: 2rem;
        }
    }
    
    @media (max-width: 640px) {
        .tax-compensation-summary .space-x-4 > * + * {
            margin-left: 0;
            margin-top: 0.5rem;
        }
        
        .tax-compensation-summary .flex-col-mobile {
            flex-direction: column;
            align-items: flex-start !important;
        }
    }
</style>