{{-- resources/views/components/tax-reports/quick-actions-simple.blade.php --}}

@props([
    'clientId' => null,
    'currentMonth' => null
])

@php
    $client = $clientId ? \App\Models\Client::find($clientId) : null;
    $currentMonthIndex = array_search($currentMonth, [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ]);
    
    $nextMonth = $currentMonthIndex !== false ? 
        ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'][($currentMonthIndex + 1) % 12] : 
        null;
    
    $next2Month = $currentMonthIndex !== false ? 
        ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'][($currentMonthIndex + 2) % 12] : 
        null;
    
    // Check existing reports
    $existingCurrent = $clientId && $currentMonth ? 
        \App\Models\TaxReport::where('client_id', $clientId)->where('month', $currentMonth)->exists() : 
        false;
    
    $existingNext = $clientId && $nextMonth ? 
        \App\Models\TaxReport::where('client_id', $clientId)->where('month', $nextMonth)->exists() : 
        false;
        
    $existingNext2 = $clientId && $next2Month ? 
        \App\Models\TaxReport::where('client_id', $clientId)->where('month', $next2Month)->exists() : 
        false;
@endphp

<div class="space-y-3">
    @if($client)
        <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
            <span class="text-blue-600 dark:text-blue-400">{{ $client->name }}</span> - {{ $currentMonth }}
        </div>
    @endif
    
    <div class="grid grid-cols-1 gap-2">
        {{-- Quick Info --}}
        <div class="p-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Status Periode:</div>
            <div class="grid grid-cols-3 gap-2 text-xs">
                <div class="text-center">
                    <div class="font-medium {{ $existingCurrent ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400' }}">
                        {{ $currentMonth }}
                    </div>
                    <div class="text-gray-500 dark:text-gray-400">
                        {{ $existingCurrent ? 'Ada' : 'Baru' }}
                    </div>
                </div>
                @if($nextMonth)
                    <div class="text-center">
                        <div class="font-medium {{ $existingNext ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400' }}">
                            {{ $nextMonth }}
                        </div>
                        <div class="text-gray-500 dark:text-gray-400">
                            {{ $existingNext ? 'Ada' : 'Baru' }}
                        </div>
                    </div>
                @endif
                @if($next2Month)
                    <div class="text-center">
                        <div class="font-medium {{ $existingNext2 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400' }}">
                            {{ $next2Month }}
                        </div>
                        <div class="text-gray-500 dark:text-gray-400">
                            {{ $existingNext2 ? 'Ada' : 'Baru' }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Call to Action --}}
        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg text-center">
            <div class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">
                Buat Multiple Laporan
            </div>
            <div class="text-xs text-blue-600 dark:text-blue-400 mb-3">
                Gunakan tombol <strong>"Buat Laporan Massal"</strong> di bagian atas halaman untuk membuat beberapa laporan sekaligus.
            </div>
            <div class="flex items-center justify-center space-x-1 text-xs text-blue-500 dark:text-blue-400">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                </svg>
                <span>Lihat di header halaman</span>
            </div>
        </div>

        {{-- Tips --}}
        <div class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <div class="text-xs font-medium text-green-800 dark:text-green-300 mb-1">💡 Tips:</div>
            <div class="text-xs text-green-700 dark:text-green-400 space-y-1">
                <div>• Buat laporan berurutan untuk efisiensi</div>
                <div>• Auto-kompensasi dari kelebihan bayar sebelumnya</div>
                <div>• Lewati periode yang sudah ada</div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Responsive adjustments */
    @media (max-width: 640px) {
        .grid-cols-3 {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }
        
        .text-center {
            text-align: left;
            padding: 0.5rem;
        }
    }
    
    /* Dark mode transitions */
    * {
        transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out, color 0.2s ease-in-out;
    }
    
    /* Enhanced visual indicators */
    .grid-cols-3 > div {
        padding: 0.5rem;
        border-radius: 0.375rem;
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    .dark .grid-cols-3 > div {
        background-color: rgba(255, 255, 255, 0.02);
    }
</style>