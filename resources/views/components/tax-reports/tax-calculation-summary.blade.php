// resources/views/filament/components/tax-calculation-summary.blade.php

<div class="space-y-3 p-4 bg-gray-50 rounded-lg border">
    <h4 class="font-semibold text-gray-900 mb-3">Ringkasan Perhitungan Pajak</h4>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
        <div class="flex justify-between">
            <span class="text-gray-600">PPN Faktur Keluaran:</span>
            <span class="font-medium text-green-700">Rp {{ number_format($calculation['ppn_keluaran'], 0, ',', '.') }}</span>
        </div>
        
        <div class="flex justify-between">
            <span class="text-gray-600">PPN Faktur Masukan:</span>
            <span class="font-medium text-red-700">Rp {{ number_format($calculation['ppn_masukan'], 0, ',', '.') }}</span>
        </div>
        
        <div class="flex justify-between border-t pt-2">
            <span class="text-gray-600">PPN Terutang:</span>
            <span class="font-medium">Rp {{ number_format($calculation['ppn_terutang'], 0, ',', '.') }}</span>
        </div>
        
        <div class="flex justify-between">
            <span class="text-gray-600">Dikompensasi:</span>
            <span class="font-medium text-blue-700">Rp {{ number_format($calculation['ppn_dikompensasi'], 0, ',', '.') }}</span>
        </div>
    </div>
    
    <div class="border-t pt-3 mt-3">
        <div class="flex justify-between items-center">
            <span class="font-semibold text-gray-900">Status Akhir:</span>
            <div class="flex items-center space-x-2">
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ 
                    $calculation['status'] === 'Kurang Bayar' ? 'bg-red-100 text-red-800' : 
                    ($calculation['status'] === 'Lebih Bayar' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') 
                }}">
                    {{ $calculation['status'] }}
                </span>
                <span class="font-bold text-lg">Rp {{ number_format(abs($calculation['final_amount']), 0, ',', '.') }}</span>
            </div>
        </div>
        
        @if($calculation['status'] === 'Lebih Bayar')
            <div class="mt-2 p-2 bg-green-50 rounded border-l-4 border-green-400">
                <p class="text-sm text-green-800">
                    <strong>Tersedia untuk kompensasi masa depan:</strong> 
                    Rp {{ number_format($calculation['available_for_compensation'], 0, ',', '.') }}
                </p>
            </div>
        @endif
    </div>
</div>