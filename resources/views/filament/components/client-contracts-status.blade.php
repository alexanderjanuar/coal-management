{{-- File: resources/views/filament/components/client-contracts-status.blade.php --}}
<div class="bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-lg border border-emerald-200 dark:border-emerald-800 p-6 mb-6">
    <h3 class="text-lg font-semibold text-emerald-900 dark:text-emerald-200 mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"/>
        </svg>
        Contract Status
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- PPN Contract --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-2 {{ $record->ppn_contract ? 'border-green-200 dark:border-green-700' : 'border-gray-200 dark:border-gray-700' }}">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-white">PPN Contract</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Value Added Tax</p>
                </div>
                @if($record->ppn_contract)
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                        </svg>
                    </div>
                @else
                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                        </svg>
                    </div>
                @endif
            </div>
            @if($record->pkp_status === 'Non-PKP' && !$record->ppn_contract)
                <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-2">Not available for Non-PKP</p>
            @endif
        </div>
        
        {{-- PPh Contract --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-2 {{ $record->pph_contract ? 'border-green-200 dark:border-green-700' : 'border-gray-200 dark:border-gray-700' }}">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-white">PPh Contract</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Income Tax</p>
                </div>
                @if($record->pph_contract)
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                        </svg>
                    </div>
                @else
                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                        </svg>
                    </div>
                @endif
            </div>
        </div>
        
        {{-- Bupot Contract --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-2 {{ $record->bupot_contract ? 'border-green-200 dark:border-green-700' : 'border-gray-200 dark:border-gray-700' }}">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-white">Bupot Contract</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tax Withholding</p>
                </div>
                @if($record->bupot_contract)
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                        </svg>
                    </div>
                @else
                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                        </svg>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    @if($record->contract_file)
        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <div class="flex items-center">
                <svg class="w-4 h-4 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"/>
                </svg>
                <span class="text-sm font-medium text-blue-700 dark:text-blue-300">Contract document available</span>
                <a href="{{ Storage::url($record->contract_file) }}" target="_blank" class="ml-2 text-xs text-blue-600 dark:text-blue-400 hover:underline">
                    View Document
                </a>
            </div>
        </div>
    @endif
</div>