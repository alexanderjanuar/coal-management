{{-- File: resources/views/filament/modals/client-core-tax-credentials.blade.php --}}
<div class="space-y-4">
    <div
        class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-6 border border-blue-200 dark:border-blue-800">
        <div class="flex items-center space-x-3 mb-4">
            @if($record->logo)
            <img src="{{ Storage::url($record->logo) }}" alt="{{ $record->name }}"
                class="w-12 h-12 rounded-full object-cover border-2 border-white dark:border-gray-700 shadow-sm">
            @else
            <div
                class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center border-2 border-white dark:border-gray-700 shadow-sm">
                <span class="text-white font-bold text-lg">{{ substr($record->name, 0, 1) }}</span>
            </div>
            @endif
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $record->name }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Core Tax Application Credentials</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Core Tax User ID --}}
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Core Tax User ID
                </label>
                <div class="flex items-center space-x-2">
                    <div class="flex-1 relative">
                        <input type="text" value="{{ $record->core_tax_user_id ?: 'Not configured' }}" readonly
                            class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 font-mono text-sm {{ $record->core_tax_user_id ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                        @if($record->core_tax_user_id)
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                            </svg>
                        </div>
                        @endif
                    </div>
                    @if($record->core_tax_user_id)
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $record->core_tax_user_id }}'); 
                                     this.textContent = 'Copied!'; 
                                     setTimeout(() => this.textContent = 'Copy', 2000);"
                        class="px-3 py-2 text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">
                        Copy
                    </button>
                    @endif
                </div>
            </div>

            {{-- Core Tax Password --}}
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Core Tax Password
                </label>
                <div class="flex items-center space-x-2">
                    <div class="flex-1 relative">
                        <input type="password" id="password-field-{{ $record->id }}"
                            value="{{ $record->core_tax_password ?: '' }}" readonly
                            placeholder="{{ $record->core_tax_password ? '' : 'Not configured' }}"
                            class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 pr-10 font-mono text-sm {{ $record->core_tax_password ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                        @if($record->core_tax_password)
                        <button type="button"
                            onclick="const field = document.getElementById('password-field-{{ $record->id }}'); 
                                         field.type = field.type === 'password' ? 'text' : 'password';
                                         this.innerHTML = field.type === 'password' ? '<svg class=&quot;w-4 h-4&quot; fill=&quot;currentColor&quot; viewBox=&quot;0 0 20 20&quot;><path d=&quot;M10 12a2 2 0 100-4 2 2 0 000 4z&quot;/><path fill-rule=&quot;evenodd&quot; d=&quot;M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z&quot;/></svg>' : '<svg class=&quot;w-4 h-4&quot; fill=&quot;currentColor&quot; viewBox=&quot;0 0 20 20&quot;><path fill-rule=&quot;evenodd&quot; d=&quot;M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z&quot;/><path d=&quot;M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z&quot;/></svg>';"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                <path fill-rule="evenodd"
                                    d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </button>
                        <div class="absolute inset-y-0 right-8 flex items-center pr-1">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                            </svg>
                        </div>
                        @endif
                    </div>
                    @if($record->core_tax_password)
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $record->core_tax_password }}'); 
                                     this.textContent = 'Copied!'; 
                                     setTimeout(() => this.textContent = 'Copy', 2000);"
                        class="px-3 py-2 text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-lg hover:bg-green-200 dark:hover:bg-green-800 transition-colors">
                        Copy
                    </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- PIC Credentials Section --}}
        @if($record->pic)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            {{-- PIC NIK --}}
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    PIC NIK
                </label>
                <div class="flex items-center space-x-2">
                    <div class="flex-1 relative">
                        <input type="text" value="{{ $record->pic->nik ?: 'Not configured' }}" readonly
                            class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 font-mono text-sm {{ $record->pic->nik ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                        @if($record->pic->nik)
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                            </svg>
                        </div>
                        @endif
                    </div>
                    @if($record->pic->nik)
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $record->pic->nik }}'); 
                                     this.textContent = 'Copied!'; 
                                     setTimeout(() => this.textContent = 'Copy', 2000);"
                        class="px-3 py-2 text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">
                        Copy
                    </button>
                    @endif
                </div>
            </div>

            {{-- PIC Password --}}
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    PIC Password
                </label>
                <div class="flex items-center space-x-2">
                    <div class="flex-1 relative">
                        <input type="password" id="pic-password-field-{{ $record->pic->id }}"
                            value="{{ $record->pic->password ?: '' }}" readonly
                            placeholder="{{ $record->pic->password ? '' : 'Not configured' }}"
                            class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 pr-10 font-mono text-sm {{ $record->pic->password ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                        @if($record->pic->password)
                        <button type="button"
                            onclick="const field = document.getElementById('pic-password-field-{{ $record->pic->id }}'); 
                             field.type = field.type === 'password' ? 'text' : 'password';
                             this.innerHTML = field.type === 'password' ? '<svg class=&quot;w-4 h-4&quot; fill=&quot;currentColor&quot; viewBox=&quot;0 0 20 20&quot;><path d=&quot;M10 12a2 2 0 100-4 2 2 0 000 4z&quot;/><path fill-rule=&quot;evenodd&quot; d=&quot;M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z&quot;/></svg>' : '<svg class=&quot;w-4 h-4&quot; fill=&quot;currentColor&quot; viewBox=&quot;0 0 20 20&quot;><path fill-rule=&quot;evenodd&quot; d=&quot;M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z&quot;/><path d=&quot;M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z&quot;/></svg>';"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                <path fill-rule="evenodd"
                                    d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </button>
                        <div class="absolute inset-y-0 right-8 flex items-center pr-1">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                            </svg>
                        </div>
                        @endif
                    </div>
                    @if($record->pic->password)
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $record->pic->password }}'); 
                         this.textContent = 'Copied!'; 
                         setTimeout(() => this.textContent = 'Copy', 2000);"
                        class="px-3 py-2 text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-lg hover:bg-green-200 dark:hover:bg-green-800 transition-colors">
                        Copy
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @else
        {{-- No PIC Assigned Alert --}}
        <div class="mt-6">
            <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                            No PIC assigned to this client
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                            Please assign a PIC (Person in Charge) to this client to view PIC credentials.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Status Alert --}}
        <div class="mt-6">
            @php
                $hasCoreTax = $record->core_tax_user_id && $record->core_tax_password;
                $hasPic = $record->pic && $record->pic->nik && $record->pic->password;
                $allComplete = $hasCoreTax && $hasPic;
            @endphp
            
            @if($allComplete)
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">
                            All credentials are complete and ready to use
                        </p>
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                            Both Core Tax and PIC credentials are configured
                        </p>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                            Credentials are incomplete
                        </p>
                        <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                            Missing: 
                            @if(!$hasCoreTax && !$hasPic)
                                Core Tax credentials and PIC assignment
                            @elseif(!$hasCoreTax)
                                Core Tax {{ !$record->core_tax_user_id ? 'User ID' : 'Password' }}
                            @elseif(!$record->pic)
                                PIC assignment
                            @else
                                PIC {{ !$record->pic->nik ? 'NIK' : 'Password' }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Security Notice --}}
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3">
        <div class="flex items-start space-x-2">
            <svg class="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" />
            </svg>
            <div class="text-xs text-amber-700 dark:text-amber-300">
                <p class="font-medium">Security Notice:</p>
                <p class="mt-1">Keep these credentials secure and do not share them unnecessarily. Only authorized
                    personnel should have access to Core Tax credentials.</p>
                <p class="mt-1 text-amber-600 dark:text-amber-400">Last updated: {{ $record->updated_at->format('M d, Y
                    \a\t H:i') }}</p>
            </div>
        </div>
    </div>
</div>