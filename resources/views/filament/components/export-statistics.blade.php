{{-- File: resources/views/filament/components/export-statistics.blade.php --}}
<div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
    <h3 class="text-sm font-medium text-blue-900 dark:text-blue-200 mb-3 flex items-center">
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Selected Clients Summary
    </h3>
    
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        {{-- Total Selected --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-blue-100 dark:border-blue-700">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total'] }}</div>
            <div class="text-xs text-blue-800 dark:text-blue-300">Total Selected</div>
        </div>
        
        {{-- With PIC --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-green-100 dark:border-green-700">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['with_pic'] }}</div>
            <div class="text-xs text-green-800 dark:text-green-300">With PIC Assigned</div>
            @if($stats['without_pic'] > 0)
                <div class="text-xs text-orange-600 dark:text-orange-400 mt-1">{{ $stats['without_pic'] }} without PIC</div>
            @endif
        </div>
        
        {{-- Core Tax Configured --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-purple-100 dark:border-purple-700">
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['with_core_tax'] }}</div>
            <div class="text-xs text-purple-800 dark:text-purple-300">Core Tax Configured</div>
            @if(($stats['total'] - $stats['with_core_tax']) > 0)
                <div class="text-xs text-orange-600 dark:text-orange-400 mt-1">{{ $stats['total'] - $stats['with_core_tax'] }} incomplete</div>
            @endif
        </div>
        
        {{-- PKP Clients --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-emerald-100 dark:border-emerald-700">
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['pkp_clients'] }}</div>
            <div class="text-xs text-emerald-800 dark:text-emerald-300">PKP Status</div>
            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $stats['total'] - $stats['pkp_clients'] }} Non-PKP</div>
        </div>
        
        {{-- Active Contracts --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-indigo-100 dark:border-indigo-700">
            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['active_contracts'] }}</div>
            <div class="text-xs text-indigo-800 dark:text-indigo-300">With Contracts</div>
            @if(($stats['total'] - $stats['active_contracts']) > 0)
                <div class="text-xs text-orange-600 dark:text-orange-400 mt-1">{{ $stats['total'] - $stats['active_contracts'] }} no contracts</div>
            @endif
        </div>
        
        {{-- Completion Score --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-yellow-100 dark:border-yellow-700">
            @php
                $completionScore = $stats['total'] > 0 ? round((($stats['with_pic'] + $stats['with_core_tax'] + $stats['active_contracts']) / ($stats['total'] * 3)) * 100) : 0;
            @endphp
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $completionScore }}%</div>
            <div class="text-xs text-yellow-800 dark:text-yellow-300">Completion Score</div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-2">
                <div class="bg-yellow-500 h-1.5 rounded-full transition-all duration-300" style="width: {{ $completionScore }}%"></div>
            </div>
        </div>
    </div>
    
    {{-- Issues Summary --}}
    @if(($stats['without_pic'] + ($stats['total'] - $stats['with_core_tax']) + ($stats['total'] - $stats['active_contracts'])) > 0)
        <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
            <h4 class="text-xs font-medium text-yellow-800 dark:text-yellow-200 mb-2">⚠️ Potential Issues Found:</h4>
            <div class="space-y-1 text-xs text-yellow-700 dark:text-yellow-300">
                @if($stats['without_pic'] > 0)
                    <div>• {{ $stats['without_pic'] }} client(s) without assigned PIC</div>
                @endif
                @if(($stats['total'] - $stats['with_core_tax']) > 0)
                    <div>• {{ $stats['total'] - $stats['with_core_tax'] }} client(s) with incomplete Core Tax credentials</div>
                @endif
                @if(($stats['total'] - $stats['active_contracts']) > 0)
                    <div>• {{ $stats['total'] - $stats['active_contracts'] }} client(s) without active contracts</div>
                @endif
            </div>
        </div>
    @else
        <div class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <div class="flex items-center text-xs text-green-700 dark:text-green-300">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                </svg>
                All selected clients have complete information!
            </div>
        </div>
    @endif
    
    {{-- Export Preview --}}
    <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
        <h4 class="text-xs font-medium text-blue-800 dark:text-blue-200 mb-2">📊 Export will include:</h4>
        <div class="grid grid-cols-2 gap-2 text-xs text-blue-700 dark:text-blue-300">
            <div>✓ Client basic information</div>
            <div>✓ Tax registration details</div>
            <div>✓ PIC assignments</div>
            <div>✓ Contract status</div>
            <div>✓ Core Tax credentials status</div>
            <div>✓ Creation & update timestamps</div>
        </div>
    </div>
</div>