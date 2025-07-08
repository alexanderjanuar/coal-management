{{-- File: resources/views/filament/components/client-hero.blade.php --}}
<div class="relative overflow-hidden bg-gradient-to-br from-blue-50 via-white to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-blue-900 rounded-xl border border-gray-200 dark:border-gray-700 mb-6">
    <div class="absolute inset-0 bg-grid-gray-100 dark:bg-grid-gray-800/50 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.6))] dark:[mask-image:linear-gradient(0deg,rgba(255,255,255,0.1),rgba(255,255,255,0.5))]"></div>
    
    <div class="relative p-6">
        <div class="flex items-start space-x-4">
            {{-- Client Logo --}}
            <div class="flex-shrink-0">
                @if($record->logo)
                    <img src="{{ Storage::url($record->logo) }}" 
                         alt="{{ $record->name }}" 
                         class="w-16 h-16 rounded-full border-4 border-white dark:border-gray-700 shadow-lg object-cover">
                @else
                    <div class="w-16 h-16 rounded-full border-4 border-white dark:border-gray-700 shadow-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                        <span class="text-white font-bold text-xl">{{ substr($record->name, 0, 1) }}</span>
                    </div>
                @endif
            </div>
            
            {{-- Client Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-3">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white truncate">
                        {{ $record->name }}
                    </h1>
                    
                    {{-- Status Badge --}}
                    @if($record->status === 'Active')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></div>
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                            <div class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></div>
                            Inactive
                        </span>
                    @endif
                </div>
                
                {{-- Quick Info Row --}}
                <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                    @if($record->NPWP)
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            NPWP: {{ $record->NPWP }}
                        </div>
                    @endif
                    
                    @if($record->email)
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2.586l-5.293 5.293a1 1 0 01-1.414 0L5 6.586V4z"/>
                            </svg>
                            {{ $record->email }}
                        </div>
                    @endif
                    
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1z"/>
                        </svg>
                        Joined: {{ $record->created_at->format('M d, Y') }}
                    </div>
                </div>
                
                {{-- PKP Status --}}
                <div class="mt-3">
                    @if($record->pkp_status === 'PKP')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            PKP (Pengusaha Kena Pajak)
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                            </svg>
                            Non-PKP
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>