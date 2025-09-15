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
                <p class="text-sm text-gray-600 dark:text-gray-400">Kredensial Aplikasi Klien</p>
            </div>
        </div>

        @php
        $credential = $record->clientCredential;
        @endphp

        @if($credential)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Core Tax User ID --}}
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    ID Pengguna Core Tax
                </label>
                <div class="flex items-center space-x-2">
                    <div class="flex-1 relative">
                        <input type="text" value="{{ $credential->core_tax_user_id ?: 'Belum dikonfigurasi' }}" readonly
                            class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 font-mono text-sm {{ $credential->core_tax_user_id ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                        @if($credential->core_tax_user_id)
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                            </svg>
                        </div>
                        @endif
                    </div>
                    @if($credential->core_tax_user_id)
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $credential->core_tax_user_id }}'); 
                                     this.textContent = 'Disalin!'; 
                                     setTimeout(() => this.textContent = 'Salin', 2000);"
                        class="px-3 py-2 text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">
                        Salin
                    </button>
                    @endif
                </div>
            </div>

            {{-- Core Tax Password --}}
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Kata Sandi Core Tax
                </label>
                <div class="flex items-center space-x-2">
                    <div class="flex-1 relative">
                        <input type="text" id="password-field-{{ $record->id }}" style="display: none;"
                            value="{{ $credential->core_tax_password ?: '' }}" readonly
                            class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 pr-10 font-mono text-sm text-gray-900 dark:text-white">
                        <input type="password" id="password-hidden-{{ $record->id }}"
                            value="{{ $credential->core_tax_password ?: '' }}" readonly
                            placeholder="{{ $credential->core_tax_password ? '' : 'Belum dikonfigurasi' }}"
                            class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 pr-10 font-mono text-sm {{ $credential->core_tax_password ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                        @if($credential->core_tax_password)
                        <button type="button" onclick="const hiddenField = document.getElementById('password-hidden-{{ $record->id }}'); 
                                     const textField = document.getElementById('password-field-{{ $record->id }}');
                                     if (hiddenField.style.display !== 'none') {
                                         hiddenField.style.display = 'none';
                                         textField.style.display = 'block';
                                         this.innerHTML = '<svg class=&quot;w-4 h-4&quot; fill=&quot;currentColor&quot; viewBox=&quot;0 0 20 20&quot;><path fill-rule=&quot;evenodd&quot; d=&quot;M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z&quot;/><path d=&quot;M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z&quot;/></svg>';
                                     } else {
                                         hiddenField.style.display = 'block';
                                         textField.style.display = 'none';
                                         this.innerHTML = '<svg class=&quot;w-4 h-4&quot; fill=&quot;currentColor&quot; viewBox=&quot;0 0 20 20&quot;><path d=&quot;M10 12a2 2 0 100-4 2 2 0 000 4z&quot;/><path fill-rule=&quot;evenodd&quot; d=&quot;M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z&quot;/></svg>';
                                     }"
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
                    @if($credential->core_tax_password)
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $credential->core_tax_password }}'); 
                                     this.textContent = 'Disalin!'; 
                                     setTimeout(() => this.textContent = 'Salin', 2000);"
                        class="px-3 py-2 text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-lg hover:bg-green-200 dark:hover:bg-green-800 transition-colors">
                        Salin
                    </button>
                    @endif
                </div>
            </div>
        </div>



        {{-- Email Credentials Section --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            {{-- Client Email --}}
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Akun Email Klien
                </label>
                <div class="flex items-center space-x-2">
                    <div class="flex-1 relative">
                        <input type="email" value="{{ $credential->email ?: 'Belum dikonfigurasi' }}" readonly
                            class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm {{ $credential->email ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                        @if($credential->email)
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                            </svg>
                        </div>
                        @endif
                    </div>
                    @if($credential->email)
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $credential->email }}'); 
                                     this.textContent = 'Disalin!'; 
                                     setTimeout(() => this.textContent = 'Salin', 2000);"
                        class="px-3 py-2 text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">
                        Salin
                    </button>
                    @endif
                </div>
            </div>

            {{-- Email Password --}}
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Kata Sandi Email
                </label>
                <div class="flex items-center space-x-2">
                    <div class="flex-1 relative">
                        <input type="text" id="email-password-field-{{ $record->id }}" style="display: none;"
                            value="{{ $credential->email_password ?: '' }}" readonly
                            class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 pr-10 text-sm text-gray-900 dark:text-white">
                        <input type="password" id="email-password-hidden-{{ $record->id }}"
                            value="{{ $credential->email_password ?: '' }}" readonly
                            placeholder="{{ $credential->email_password ? '' : 'Belum dikonfigurasi' }}"
                            class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 pr-10 text-sm {{ $credential->email_password ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                        @if($credential->email_password)
                        <button type="button" onclick="const hiddenField = document.getElementById('email-password-hidden-{{ $record->id }}'); 
                                     const textField = document.getElementById('email-password-field-{{ $record->id }}');
                                     if (hiddenField.style.display !== 'none') {
                                         hiddenField.style.display = 'none';
                                         textField.style.display = 'block';
                                         this.innerHTML = '<svg class=&quot;w-4 h-4&quot; fill=&quot;currentColor&quot; viewBox=&quot;0 0 20 20&quot;><path fill-rule=&quot;evenodd&quot; d=&quot;M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z&quot;/><path d=&quot;M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z&quot;/></svg>';
                                     } else {
                                         hiddenField.style.display = 'block';
                                         textField.style.display = 'none';
                                         this.innerHTML = '<svg class=&quot;w-4 h-4&quot; fill=&quot;currentColor&quot; viewBox=&quot;0 0 20 20&quot;><path d=&quot;M10 12a2 2 0 100-4 2 2 0 000 4z&quot;/><path fill-rule=&quot;evenodd&quot; d=&quot;M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z&quot;/></svg>';
                                     }"
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
                    @if($credential->email_password)
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $credential->email_password }}'); 
                         this.textContent = 'Disalin!'; 
                         setTimeout(() => this.textContent = 'Salin', 2000);"
                        class="px-3 py-2 text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-lg hover:bg-green-200 dark:hover:bg-green-800 transition-colors">
                        Salin
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <div class="col-span-2">
                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V4a2 2 0 00-2-2H6zm1 2a1 1 0 000 2h6a1 1 0 100-2H7zM7 8a1 1 0 000 2h6a1 1 0 100-2H7zm0 4a1 1 0 100 2h6a1 1 0 100-2H7z" />
                    </svg>
                    Kredensial DJP Online
                </h4>
            </div>

            {{-- DJP Account --}}
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Akun DJP
                </label>
                <div class="flex items-center space-x-2">
                    <div class="flex-1 relative">
                        <input type="text" value="{{ $credential->djp_account ?: 'Belum dikonfigurasi' }}" readonly
                            class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 font-mono text-sm {{ $credential->djp_account ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                        @if($credential->djp_account)
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                            </svg>
                        </div>
                        @endif
                    </div>
                    @if($credential->djp_account)
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $credential->djp_account }}'); 
                                     this.textContent = 'Disalin!'; 
                                     setTimeout(() => this.textContent = 'Salin', 2000);"
                        class="px-3 py-2 text-xs bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 rounded-lg hover:bg-purple-200 dark:hover:bg-purple-800 transition-colors">
                        Salin
                    </button>
                    @endif
                </div>
            </div>

            {{-- DJP Password --}}
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Kata Sandi DJP
                </label>
                <div class="flex items-center space-x-2">
                    <div class="flex-1 relative">
                        <input type="text" id="djp-password-field-{{ $record->id }}" style="display: none;"
                            value="{{ $credential->djp_password ?: '' }}" readonly
                            class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 pr-10 font-mono text-sm text-gray-900 dark:text-white">
                        <input type="password" id="djp-password-hidden-{{ $record->id }}"
                            value="{{ $credential->djp_password ?: '' }}" readonly
                            placeholder="{{ $credential->djp_password ? '' : 'Belum dikonfigurasi' }}"
                            class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 pr-10 font-mono text-sm {{ $credential->djp_password ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                        @if($credential->djp_password)
                        <button type="button" onclick="const hiddenField = document.getElementById('djp-password-hidden-{{ $record->id }}'); 
                                     const textField = document.getElementById('djp-password-field-{{ $record->id }}');
                                     if (hiddenField.style.display !== 'none') {
                                         hiddenField.style.display = 'none';
                                         textField.style.display = 'block';
                                         this.innerHTML = '<svg class=&quot;w-4 h-4&quot; fill=&quot;currentColor&quot; viewBox=&quot;0 0 20 20&quot;><path fill-rule=&quot;evenodd&quot; d=&quot;M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z&quot;/><path d=&quot;M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z&quot;/></svg>';
                                     } else {
                                         hiddenField.style.display = 'block';
                                         textField.style.display = 'none';
                                         this.innerHTML = '<svg class=&quot;w-4 h-4&quot; fill=&quot;currentColor&quot; viewBox=&quot;0 0 20 20&quot;><path d=&quot;M10 12a2 2 0 100-4 2 2 0 000 4z&quot;/><path fill-rule=&quot;evenodd&quot; d=&quot;M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z&quot;/></svg>';
                                     }"
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
                    @if($credential->djp_password)
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $credential->djp_password }}'); 
                                     this.textContent = 'Disalin!'; 
                                     setTimeout(() => this.textContent = 'Salin', 2000);"
                        class="px-3 py-2 text-xs bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 rounded-lg hover:bg-purple-200 dark:hover:bg-purple-800 transition-colors">
                        Salin
                    </button>
                    @endif
                </div>
            </div>
        </div>

        @else
        {{-- No Credentials Alert --}}
        <div class="mt-6">
            <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                            Tidak ada kredensial yang dikonfigurasi untuk klien ini
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                            Silakan konfigurasi kredensial klien di form edit untuk melihatnya di sini.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- PIC Credentials Section --}}
        @if($record->pic)
        <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
            <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">Kredensial PIC</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- PIC NIK --}}
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        NIK PIC
                    </label>
                    <div class="flex items-center space-x-2">
                        <div class="flex-1 relative">
                            <input type="text" value="{{ $record->pic->nik ?: 'Belum dikonfigurasi' }}" readonly
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
                                         this.textContent = 'Disalin!'; 
                                         setTimeout(() => this.textContent = 'Salin', 2000);"
                            class="px-3 py-2 text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">
                            Salin
                        </button>
                        @endif
                    </div>
                </div>

                {{-- PIC Password --}}
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Password PIC
                    </label>
                    <div class="flex items-center space-x-2">
                        <div class="flex-1 relative">
                            <input type="text" id="pic-password-field-{{ $record->id }}" style="display: none;"
                                value="{{ $record->pic->password ?: '' }}" readonly
                                class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 pr-10 font-mono text-sm text-gray-900 dark:text-white">
                            <input type="password" id="pic-password-hidden-{{ $record->id }}"
                                value="{{ $record->pic->password ?: '' }}" readonly
                                placeholder="{{ $record->pic->password ? '' : 'Belum dikonfigurasi' }}"
                                class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 pr-10 font-mono text-sm {{ $record->pic->password ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                            @if($record->pic->password)
                            <button type="button" onclick="const hiddenField = document.getElementById('pic-password-hidden-{{ $record->id }}'); 
                         const textField = document.getElementById('pic-password-field-{{ $record->id }}');
                         if (hiddenField.style.display !== 'none') {
                             hiddenField.style.display = 'none';
                             textField.style.display = 'block';
                             this.innerHTML = '<svg class=&quot;w-4 h-4&quot; fill=&quot;currentColor&quot; viewBox=&quot;0 0 20 20&quot;><path fill-rule=&quot;evenodd&quot; d=&quot;M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z&quot;/><path d=&quot;M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z&quot;/></svg>';
                         } else {
                             hiddenField.style.display = 'block';
                             textField.style.display = 'none';
                             this.innerHTML = '<svg class=&quot;w-4 h-4&quot; fill=&quot;currentColor&quot; viewBox=&quot;0 0 20 20&quot;><path d=&quot;M10 12a2 2 0 100-4 2 2 0 000 4z&quot;/><path fill-rule=&quot;evenodd&quot; d=&quot;M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z&quot;/></svg>';
                         }" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
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
                     this.textContent = 'Disalin!'; 
                     setTimeout(() => this.textContent = 'Salin', 2000);"
                            class="px-3 py-2 text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-lg hover:bg-green-200 dark:hover:bg-green-800 transition-colors">
                            Salin
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @else
        {{-- No PIC Assigned Alert --}}
        <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
            <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                            Belum ada PIC yang ditugaskan untuk klien ini
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                            Silakan menugaskan PIC (Person in Charge) untuk klien ini untuk melihat kredensial PIC.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Status Alert --}}
        <div class="mt-6">
            @php
            $hasCoreTax = $credential && $credential->core_tax_user_id && $credential->core_tax_password;
            $hasEmail = $credential && $credential->email && $credential->email_password;
            $hasPic = $record->pic && $record->pic->nik && $record->pic->status === 'active';
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
                            Semua kredensial penting sudah lengkap dan siap digunakan
                        </p>
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                            Kredensial Core Tax dan penugasan PIC sudah dikonfigurasi
                            @if($hasEmail) • Kredensial email juga tersedia @endif
                        </p>
                    </div>
                </div>
            </div>
            @else
            <div
                class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                            Kredensial belum lengkap
                        </p>
                        <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                            Yang kurang:
                            @if(!$hasCoreTax && !$hasPic)
                            Kredensial Core Tax dan penugasan PIC
                            @elseif(!$hasCoreTax)
                            @if(!$credential)
                            Data kredensial klien
                            @elseif(!$credential->core_tax_user_id)
                            ID Pengguna Core Tax
                            @else
                            Kata Sandi Core Tax
                            @endif
                            @elseif(!$record->pic)
                            Penugasan PIC
                            @else
                            NIK PIC atau status PIC tidak aktif
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>