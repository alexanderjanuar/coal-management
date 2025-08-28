<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent User Activity</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Track recent actions and updates in your projects</p>
            </div>
            
            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-3">
                <!-- Search Input -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search activities..."
                        class="pl-10 pr-4 py-2 w-full sm:w-64 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400"
                    >
                </div>
                
                <!-- Date Filter -->
                <select 
                    wire:model.live="dateFilter"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                >
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="year">This Year</option>
                    <option value="all">All Time</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table Content -->
    <div class="overflow-x-auto">
        @if($activities->count() > 0)
            <!-- Desktop Table View -->
            <div class="hidden md:block">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($activities as $activity)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                <!-- User Avatar & Name -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img 
                                                class="h-10 w-10 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600" 
                                                src="{{ $this->getUserAvatar($activity) }}" 
                                                alt="{{ $activity->causer?->name ?? 'System' }}"
                                                onerror="this.src='{{ asset('images/default-avatar.png') }}'"
                                            >
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $activity->causer?->name ?? 'System' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Action -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 mr-3">
                                            @php
                                                $iconClass = match($activity->description) {
                                                    'created' => 'text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30',
                                                    'updated' => 'text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30',
                                                    'deleted' => 'text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/30',
                                                    default => 'text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700'
                                                };
                                                
                                                $icon = match($activity->description) {
                                                    'created' => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                                                    'updated' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                                                    'deleted' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
                                                    default => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                                                };
                                            @endphp
                                            <div class="rounded-full p-2 {{ $iconClass }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                                                {{ $this->formatAction($activity) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Client -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $this->getClientName($activity) }}</div>
                                </td>

                                <!-- Time -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col">
                                        <span>{{ $activity->created_at->format('M d, Y') }}</span>
                                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ $activity->created_at->format('H:i') }}</span>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if($this->getViewUrl($activity))
                                        <a 
                                            href="{{ $this->getViewUrl($activity) }}" 
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30 hover:bg-blue-200 dark:hover:bg-blue-900/50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition-colors duration-150"
                                        >
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            View
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">No action</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden">
                @foreach($activities as $activity)
                    <div class="border-b border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <div class="flex items-start space-x-4">
                            <!-- Avatar -->
                            <div class="flex-shrink-0">
                                <img 
                                    class="h-10 w-10 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600" 
                                    src="{{ $this->getUserAvatar($activity) }}" 
                                    alt="{{ $activity->causer?->name ?? 'System' }}"
                                    onerror="this.src='{{ asset('images/default-avatar.png') }}'"
                                >
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $activity->causer?->name ?? 'System' }}
                                    </p>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $activity->created_at->format('M d, H:i') }}
                                    </div>
                                </div>
                                
                                <div class="mt-1 flex items-center">
                                    @php
                                        $iconClass = match($activity->description) {
                                            'created' => 'text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30',
                                            'updated' => 'text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30',
                                            'deleted' => 'text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/30',
                                            default => 'text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700'
                                        };
                                        
                                        $icon = match($activity->description) {
                                            'created' => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                                            'updated' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                                            'deleted' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
                                            default => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                                        };
                                    @endphp
                                    <div class="rounded-full p-1.5 mr-2 {{ $iconClass }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ $this->formatAction($activity) }}</span>
                                </div>
                                
                                <div class="mt-2 flex items-center justify-between">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        <span class="font-medium">Client:</span> {{ $this->getClientName($activity) }}
                                    </div>
                                    @if($this->getViewUrl($activity))
                                        <a 
                                            href="{{ $this->getViewUrl($activity) }}" 
                                            class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30 hover:bg-blue-200 dark:hover:bg-blue-900/50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800"
                                        >
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            View
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="bg-white dark:bg-gray-800 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Showing {{ $activities->firstItem() ?? 0 }} to {{ $activities->lastItem() ?? 0 }} of {{ $activities->total() }} results
                    </div>
                    
                    @if ($activities->hasPages())
                        <div class="flex items-center space-x-2">
                            {{-- Previous Page Link --}}
                            @if ($activities->onFirstPage())
                                <span class="px-3 py-2 text-sm text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 rounded-lg cursor-not-allowed">
                                    Previous
                                </span>
                            @else
                                <button 
                                    wire:click="previousPage" 
                                    class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    Previous
                                </button>
                            @endif

                            {{-- Page Numbers --}}
                            @foreach ($activities->getUrlRange(1, $activities->lastPage()) as $page => $url)
                                @if ($page == $activities->currentPage())
                                    <span class="px-3 py-2 text-sm text-white bg-blue-600 dark:bg-blue-500 rounded-lg">
                                        {{ $page }}
                                    </span>
                                @else
                                    <button 
                                        wire:click="gotoPage({{ $page }})" 
                                        class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        {{ $page }}
                                    </button>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($activities->hasMorePages())
                                <button 
                                    wire:click="nextPage" 
                                    class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    Next
                                </button>
                            @else
                                <span class="px-3 py-2 text-sm text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 rounded-lg cursor-not-allowed">
                                    Next
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="flex flex-col items-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                        <svg class="h-8 w-8 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No activity found</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        @php
                            $dateLabels = [
                                'today' => 'today',
                                'week' => 'this week',
                                'month' => 'this month',
                                'year' => 'this year',
                                'all' => '',
                            ];
                            $dateText = $dateLabels[$dateFilter] ?? '';
                        @endphp
                        No user activity has been recorded {{ $dateText ? $dateText : 'in the selected time period' }}.
                    </p>
                    @if($search)
                        <button 
                            wire:click="$set('search', '')" 
                            class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium"
                        >
                            Clear search
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Loading State -->
    <div wire:loading class="absolute inset-0 bg-white dark:bg-gray-800 bg-opacity-75 dark:bg-opacity-75 flex items-center justify-center">
        <div class="flex items-center space-x-2">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 dark:border-blue-400"></div>
            <span class="text-sm text-gray-600 dark:text-gray-300">Loading...</span>
        </div>
    </div>
</div>