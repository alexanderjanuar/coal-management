{{-- File: resources/views/filament/components/client-tax-info.blade.php --}}
<div class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-lg border border-purple-200 dark:border-purple-800 p-6 mb-6">
    <h3 class="text-lg font-semibold text-purple-900 dark:text-purple-200 mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"/>
        </svg>
        Tax Information Summary
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-purple-100 dark:border-purple-700">
            <div class="text-xs font-medium text-purple-600 dark:text-purple-400 uppercase tracking-wider">NPWP</div>
            <div class="mt-1 text-lg font-mono font-semibold text-gray-900 dark:text-white">{{ $record->NPWP }}</div>
        </div>
        
        @if($record->EFIN)
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-purple-100 dark:border-purple-700">
            <div class="text-xs font-medium text-purple-600 dark:text-purple-400 uppercase tracking-wider">EFIN</div>
            <div class="mt-1 text-lg font-mono font-semibold text-gray-900 dark:text-white">{{ $record->EFIN }}</div>
        </div>
        @endif
        
        @if($record->KPP)
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-purple-100 dark:border-purple-700">
            <div class="text-xs font-medium text-purple-600 dark:text-purple-400 uppercase tracking-wider">KPP</div>
            <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $record->KPP }}</div>
        </div>
        @endif
        
        @if($record->account_representative)
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-purple-100 dark:border-purple-700">
            <div class="text-xs font-medium text-purple-600 dark:text-purple-400 uppercase tracking-wider">Account Representative</div>
            <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $record->account_representative }}</div>
            @if($record->ar_phone_number)
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $record->ar_phone_number }}</div>
            @endif
        </div>
        @endif
    </div>
</div>