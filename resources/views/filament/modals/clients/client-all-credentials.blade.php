{{-- resources/views/filament/modals/client-all-credentials.blade.php --}}
<div class="space-y-6">
    {{-- Client Header --}}
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-6 border border-blue-200 dark:border-blue-800">
        <div class="flex items-center space-x-3">
            @if($record->logo)
                <img src="{{ Storage::url($record->logo) }}" alt="{{ $record->name }}" 
                     class="w-12 h-12 rounded-full object-cover border-2 border-white dark:border-gray-700 shadow-sm">
            @else
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center border-2 border-white dark:border-gray-700 shadow-sm">
                    <span class="text-white font-bold text-lg">{{ substr($record->name, 0, 1) }}</span>
                </div>
            @endif
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $record->name }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Semua Kredensial Aplikasi</p>
            </div>
        </div>
    </div>

    @php
        $credentials = $record->applicationCredentials()
            ->with('application')
            ->where('is_active', true)
            ->get()
            ->groupBy('application.category');
    @endphp

    @if($credentials->isEmpty())
        {{-- No Credentials --}}
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
            <p class="mt-4 text-gray-500">Belum ada kredensial aplikasi yang dikonfigurasi</p>
            <p class="text-sm text-gray-400 mt-2">Tambahkan kredensial melalui tab "Application Credentials"</p>
        </div>
    @else
        {{-- Loop through categories --}}
        @foreach($credentials as $category => $apps)
            <div class="space-y-4">
                {{-- Category Header --}}
                <div class="flex items-center gap-2 pb-2 border-b border-gray-200 dark:border-gray-700">
                    @php
                        $categoryInfo = match($category) {
                            'tax' => ['label' => 'Aplikasi Perpajakan', 'icon' => 'heroicon-o-document-text', 'color' => 'text-green-600'],
                            'accounting' => ['label' => 'Aplikasi Akuntansi', 'icon' => 'heroicon-o-calculator', 'color' => 'text-blue-600'],
                            'email' => ['label' => 'Email', 'icon' => 'heroicon-o-envelope', 'color' => 'text-orange-600'],
                            'api' => ['label' => 'API Services', 'icon' => 'heroicon-o-code-bracket', 'color' => 'text-purple-600'],
                            default => ['label' => 'Lainnya', 'icon' => 'heroicon-o-folder', 'color' => 'text-gray-600'],
                        };
                    @endphp
                    
                    <x-dynamic-component :component="$categoryInfo['icon']" class="w-5 h-5 {{ $categoryInfo['color'] }}" />
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase">
                        {{ $categoryInfo['label'] }}
                    </h4>
                </div>

                {{-- Applications in this category --}}
                @foreach($apps as $credential)
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        {{-- App Header --}}
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                @if($credential->application->logo)
                                    <img src="{{ Storage::url($credential->application->logo) }}" 
                                         alt="{{ $credential->application->name }}"
                                         class="w-10 h-10 rounded-lg">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">{{ substr($credential->application->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                
                                <div>
                                    <h5 class="font-semibold text-gray-900 dark:text-white">{{ $credential->application->name }}</h5>
                                    @if($credential->application->description)
                                        <p class="text-xs text-gray-500">{{ Str::limit($credential->application->description, 50) }}</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Open App Button (for tax apps with URL) --}}
                            @if($credential->application->app_url && $credential->application->category === 'tax')
                                <a href="{{ $credential->application->app_url }}" 
                                   target="_blank"
                                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors">
                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    Buka
                                </a>
                            @endif
                        </div>

                        {{-- Credentials Grid --}}
                        <div class="grid grid-cols-2 gap-3">
                            {{-- Username --}}
                            <div class="space-y-1">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Username</label>
                                <div class="flex items-center gap-2">
                                    <input type="text" 
                                           value="{{ $credential->username }}" 
                                           readonly
                                           class="flex-1 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 text-sm font-mono text-gray-900 dark:text-white">
                                    <button type="button" 
                                            onclick="navigator.clipboard.writeText('{{ $credential->username }}'); this.textContent = '✓'; setTimeout(() => this.textContent = 'Salin', 2000);"
                                            class="px-2 py-1.5 text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded hover:bg-blue-200 dark:hover:bg-blue-800">
                                        Salin
                                    </button>
                                </div>
                            </div>

                            {{-- Password --}}
                            <div class="space-y-1">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Password</label>
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 relative">
                                        <input type="password" 
                                               id="pwd-hidden-{{ $credential->id }}"
                                               value="{{ $credential->password }}" 
                                               readonly
                                               class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 pr-8 text-sm font-mono text-gray-900 dark:text-white">
                                        <input type="text" 
                                               id="pwd-visible-{{ $credential->id }}"
                                               value="{{ $credential->password }}" 
                                               readonly
                                               style="display: none;"
                                               class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 pr-8 text-sm font-mono text-gray-900 dark:text-white">
                                        <button type="button"
                                                onclick="const hidden = document.getElementById('pwd-hidden-{{ $credential->id }}'); 
                                                         const visible = document.getElementById('pwd-visible-{{ $credential->id }}');
                                                         if (hidden.style.display !== 'none') {
                                                             hidden.style.display = 'none';
                                                             visible.style.display = 'block';
                                                             this.innerHTML = '<svg class=&quot;w-3 h-3&quot; fill=&quot;currentColor&quot; viewBox=&quot;0 0 20 20&quot;><path fill-rule=&quot;evenodd&quot; d=&quot;M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z&quot;/><path d=&quot;M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z&quot;/></svg>';
                                                         } else {
                                                             hidden.style.display = 'block';
                                                             visible.style.display = 'none';
                                                             this.innerHTML = '<svg class=&quot;w-3 h-3&quot; fill=&quot;currentColor&quot; viewBox=&quot;0 0 20 20&quot;><path d=&quot;M10 12a2 2 0 100-4 2 2 0 000 4z&quot;/><path fill-rule=&quot;evenodd&quot; d=&quot;M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z&quot;/></svg>';
                                                         }"
                                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        </button>
                                    </div>
                                    <button type="button" 
                                            onclick="navigator.clipboard.writeText('{{ $credential->password }}'); this.textContent = '✓'; setTimeout(() => this.textContent = 'Salin', 2000);"
                                            class="px-2 py-1.5 text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded hover:bg-green-200 dark:hover:bg-green-800">
                                        Salin
                                    </button>
                                </div>
                            </div>

                            {{-- Activation Code (if exists) --}}
                            @if($credential->activation_code)
                                <div class="space-y-1">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Kode Aktivasi</label>
                                    <div class="flex items-center gap-2">
                                        <input type="text" 
                                               value="{{ $credential->activation_code }}" 
                                               readonly
                                               class="flex-1 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 text-sm font-mono">
                                        <button type="button" 
                                                onclick="navigator.clipboard.writeText('{{ $credential->activation_code }}'); this.textContent = '✓'; setTimeout(() => this.textContent = 'Salin', 2000);"
                                                class="px-2 py-1.5 text-xs bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 rounded">
                                            Salin
                                        </button>
                                    </div>
                                </div>
                            @endif

                            {{-- Account Period (if exists) --}}
                            @if($credential->account_period)
                                <div class="space-y-1">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Berlaku Hingga</label>
                                    <div class="flex items-center gap-2">
                                        <input type="text" 
                                               value="{{ $credential->account_period->format('d M Y') }}" 
                                               readonly
                                               class="flex-1 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 text-sm {{ $credential->isExpired() ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">
                                        @if($credential->isExpired())
                                            <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">Expired</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Additional Data (if exists) --}}
                        @if($credential->additional_data && count($credential->additional_data) > 0)
                            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Data Tambahan:</p>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    @foreach($credential->additional_data as $key => $value)
                                        <div>
                                            <span class="text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                            <span class="text-gray-900 dark:text-white font-mono ml-1">{{ $value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Notes (if exists) --}}
                        @if($credential->notes)
                            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Catatan:</span> {{ $credential->notes }}
                                </p>
                            </div>
                        @endif

                        {{-- Last Used --}}
                        @if($credential->last_used_at)
                            <div class="mt-2 text-xs text-gray-500">
                                Terakhir digunakan: {{ $credential->last_used_at->diffForHumans() }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    @endif

    {{-- PIC Credentials Section --}}
    @if($record->pic)
        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" />
                </svg>
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase">
                    Kredensial Person In Charge (PIC)
                </h4>
            </div>

            <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800 p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center">
                        <span class="text-white font-bold text-sm">{{ substr($record->pic->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <h5 class="font-semibold text-gray-900 dark:text-white">{{ $record->pic->name }}</h5>
                        <p class="text-xs text-gray-500">{{ $record->pic->status === 'active' ? 'Status: Aktif' : 'Status: Nonaktif' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    {{-- PIC NIK --}}
                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">NIK</label>
                        <div class="flex items-center gap-2">
                            <input type="text" 
                                   value="{{ $record->pic->nik }}" 
                                   readonly
                                   class="flex-1 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 text-sm font-mono">
                            <button type="button" 
                                    onclick="navigator.clipboard.writeText('{{ $record->pic->nik }}'); this.textContent = '✓'; setTimeout(() => this.textContent = 'Salin', 2000);"
                                    class="px-2 py-1.5 text-xs bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded">
                                Salin
                            </button>
                        </div>
                    </div>

                    {{-- PIC Password --}}
                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Password</label>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 relative">
                                <input type="password" 
                                       id="pic-pwd-hidden"
                                       value="{{ $record->pic->password }}" 
                                       readonly
                                       class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 pr-8 text-sm font-mono">
                                <input type="text" 
                                       id="pic-pwd-visible"
                                       value="{{ $record->pic->password }}" 
                                       readonly
                                       style="display: none;"
                                       class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 pr-8 text-sm font-mono">
                                <button type="button"
                                        onclick="const hidden = document.getElementById('pic-pwd-hidden'); 
                                                 const visible = document.getElementById('pic-pwd-visible');
                                                 if (hidden.style.display !== 'none') {
                                                     hidden.style.display = 'none';
                                                     visible.style.display = 'block';
                                                 } else {
                                                     hidden.style.display = 'block';
                                                     visible.style.display = 'none';
                                                 }"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </button>
                            </div>
                            <button type="button" 
                                    onclick="navigator.clipboard.writeText('{{ $record->pic->password }}'); this.textContent = '✓'; setTimeout(() => this.textContent = 'Salin', 2000);"
                                    class="px-2 py-1.5 text-xs bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded">
                                Salin
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>