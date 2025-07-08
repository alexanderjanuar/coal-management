{{-- File: resources/views/filament/modals/pic-details.blade.php --}}
<div class="space-y-4">
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-6 border border-blue-200 dark:border-blue-800">
        <div class="flex items-center space-x-3 mb-4">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $record->pic->name }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Person In Charge (PIC)</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Basic Information --}}
            <div class="space-y-3">
                <h4 class="text-sm font-medium text-blue-900 dark:text-blue-200">Basic Information</h4>
                
                <div class="space-y-2">
                    <div>
                        <label class="block text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">
                            Full Name
                        </label>
                        <p class="text-sm text-gray-900 dark:text-white font-medium">{{ $record->pic->name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">
                            NIK
                        </label>
                        <div class="flex items-center space-x-2">
                            <p class="text-sm text-gray-900 dark:text-white font-mono">{{ $record->pic->nik }}</p>
                            <button 
                                type="button"
                                onclick="navigator.clipboard.writeText('{{ $record->pic->nik }}'); 
                                         this.textContent = 'Copied!'; 
                                         setTimeout(() => this.innerHTML = '<svg class=&quot;w-3 h-3&quot; fill=&quot;currentColor&quot; viewBox=&quot;0 0 20 20&quot;><path d=&quot;M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z&quot;/><path d=&quot;M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z&quot;/></svg>', 2000);"
                                class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200"
                                title="Copy NIK"
                            >
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"/>
                                    <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    @if($record->pic->email)
                    <div>
                        <label class="block text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">
                            Email
                        </label>
                        <div class="flex items-center space-x-2">
                            <p class="text-sm text-gray-900 dark:text-white">{{ $record->pic->email }}</p>
                            <button 
                                type="button"
                                onclick="navigator.clipboard.writeText('{{ $record->pic->email }}'); 
                                         this.textContent = 'Copied!'; 
                                         setTimeout(() => this.innerHTML = '<svg class=&quot;w-3 h-3&quot; fill=&quot;currentColor&quot; viewBox=&quot;0 0 20 20&quot;><path d=&quot;M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z&quot;/><path d=&quot;M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z&quot;/></svg>', 2000);"
                                class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200"
                                title="Copy Email"
                            >
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"/>
                                    <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    @endif
                    
                    <div>
                        <label class="block text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">
                            Status
                        </label>
                        @if($record->pic->status === 'active')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1"></div>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                <div class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1"></div>
                                Inactive
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            
            {{-- Assignment Information --}}
            <div class="space-y-3">
                <h4 class="text-sm font-medium text-blue-900 dark:text-blue-200">Assignment Information</h4>
                
                <div class="space-y-2">
                    <div>
                        <label class="block text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">
                            Assigned to Client
                        </label>
                        <p class="text-sm text-gray-900 dark:text-white font-medium">{{ $record->name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">
                            Total Clients Managed
                        </label>
                        <p class="text-sm text-gray-900 dark:text-white">
                            {{ $record->pic->clients()->count() }} 
                            {{ Str::plural('client', $record->pic->clients()->count()) }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">
                            Active Clients
                        </label>
                        <p class="text-sm text-gray-900 dark:text-white">
                            {{ $record->pic->clients()->where('status', 'Active')->count() }} active
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">
                            PIC Since
                        </label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $record->pic->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Other Clients Managed --}}
    @if($record->pic->clients()->where('id', '!=', $record->id)->count() > 0)
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Other Clients Managed by This PIC</h4>
        <div class="space-y-2">
            @foreach($record->pic->clients()->where('id', '!=', $record->id)->get() as $otherClient)
                <div class="flex items-center justify-between py-2 px-3 bg-white dark:bg-gray-700 rounded border border-gray-100 dark:border-gray-600">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 rounded-full {{ $otherClient->status === 'Active' ? 'bg-green-500' : 'bg-red-500' }}"></div>
                        <span class="text-sm text-gray-900 dark:text-white">{{ $otherClient->name }}</span>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $otherClient->status }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
    @endif
    
    {{-- Quick Actions --}}
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 border border-blue-200 dark:border-blue-800">
        <div class="flex items-center space-x-2 text-xs text-blue-700 dark:text-blue-300">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
            </svg>
            <span>Use the table actions to assign, change, or unassign this PIC from the client.</span>
        </div>
    </div>
</div>