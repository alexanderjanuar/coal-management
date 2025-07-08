{{-- File: resources/views/filament/modals/core-tax-credentials.blade.php --}}

<div class="space-y-4">
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
            {{ $record->name }} - Core Tax Credentials
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Core Tax User ID
                </label>
                <div class="flex items-center space-x-2">
                    <code class="bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded px-3 py-2 font-mono text-sm flex-1">
                        {{ $record->core_tax_user_id ?: 'Not set' }}
                    </code>
                    @if($record->core_tax_user_id)
                        <button 
                            type="button"
                            onclick="navigator.clipboard.writeText('{{ $record->core_tax_user_id }}'); 
                                     this.textContent = 'Copied!'; 
                                     setTimeout(() => this.textContent = 'Copy', 2000);"
                            class="px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded hover:bg-blue-200 dark:hover:bg-blue-800"
                        >
                            Copy
                        </button>
                    @endif
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Core Tax Password
                </label>
                <div class="flex items-center space-x-2">
                    <div class="relative flex-1">
                        <input 
                            type="password" 
                            id="password-field"
                            value="{{ $record->core_tax_password ?: '' }}" 
                            readonly 
                            class="bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded px-3 py-2 font-mono text-sm w-full pr-10"
                            placeholder="{{ $record->core_tax_password ? '' : 'Not set' }}"
                        >
                        @if($record->core_tax_password)
                            <button 
                                type="button"
                                onclick="const field = document.getElementById('password-field'); 
                                         field.type = field.type === 'password' ? 'text' : 'password';
                                         this.textContent = field.type === 'password' ? '👁️' : '🙈';"
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                            >
                                👁️
                            </button>
                        @endif
                    </div>
                    @if($record->core_tax_password)
                        <button 
                            type="button"
                            onclick="navigator.clipboard.writeText('{{ $record->core_tax_password }}'); 
                                     this.textContent = 'Copied!'; 
                                     setTimeout(() => this.textContent = 'Copy', 2000);"
                            class="px-2 py-1 text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded hover:bg-green-200 dark:hover:bg-green-800"
                        >
                            Copy
                        </button>
                    @endif
                </div>
            </div>
        </div>
        
        @if($record->core_tax_user_id && $record->core_tax_password)
            <div class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded">
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm text-green-700 dark:text-green-300 font-medium">
                        Core Tax credentials are configured
                    </span>
                </div>
            </div>
        @else
            <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded">
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm text-yellow-700 dark:text-yellow-300 font-medium">
                        Core Tax credentials are incomplete
                    </span>
                </div>
            </div>
        @endif
    </div>
    
    <div class="text-xs text-gray-500 dark:text-gray-400">
        <p>⚠️ Keep these credentials secure and do not share them unnecessarily.</p>
        <p>📅 Last updated: {{ $record->updated_at->format('d/m/Y H:i') }}</p>
    </div>
</div>  