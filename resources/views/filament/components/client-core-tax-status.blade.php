{{-- File: resources/views/filament/components/client-core-tax-status.blade.php --}}
<div class="mt-4">
    @if($record->core_tax_user_id && $record->core_tax_password)
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">Core Tax credentials configured</p>
                    <p class="text-xs text-green-600 dark:text-green-400">User ID: {{ $record->core_tax_user_id }}</p>
                </div>
            </div>
        </div>
    @elseif($record->core_tax_user_id || $record->core_tax_password)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Core Tax credentials incomplete</p>
                    <p class="text-xs text-yellow-600 dark:text-yellow-400">
                        Missing: {{ !$record->core_tax_user_id ? 'User ID' : 'Password' }}
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
                </svg>
                <p class="text-sm text-gray-600 dark:text-gray-400">No Core Tax credentials configured</p>
            </div>
        </div>
    @endif
</div>