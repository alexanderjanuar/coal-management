<div class="grid gap-4 md:grid-cols-4 mb-8" x-data="{ 
        hoveredCard: null,
        tooltipData: {
            'total': { previous: 45, change: 12 },
            'active': { previous: 32, change: 8.2 },
            'completed': { previous: 28, change: -3.1 },
            'pending': { previous: 15, change: 5.4 }
        }
     }">

    {{-- Total Projects Card --}}
    <div x-on:click="$dispatch('open-modal', { id: 'stats-modal-total' })"
        class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-200 p-5 group cursor-pointer"
        x-data="{ showTooltip: false }" @mouseenter="showTooltip = true" @mouseleave="showTooltip = false">
        <div class="space-y-3 relative">
            {{-- Enhanced Right-sided Tooltip --}}
            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-x-1" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-1"
                class="absolute left-[105%] top-0 bg-white rounded-xl text-gray-800 p-4 shadow-xl z-10 w-72 border border-gray-100/50 backdrop-blur-sm">
                <div class="space-y-4">
                    {{-- Header --}}
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div>
                            <h3 class="font-semibold text-gray-800">Project Analytics</h3>
                            <p class="text-xs text-gray-500">Last 30 days performance</p>
                        </div>
                        <span class="text-xs px-2 py-1 bg-blue-50 text-blue-600 rounded-full font-medium">Monthly</span>
                    </div>

                    {{-- Stats Grid --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-xs text-gray-500">Current</span>
                            <p class="text-lg font-bold text-gray-800">{{ $stats['total_projects']['current'] }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs text-gray-500">Previous</span>
                            <p class="text-lg font-bold text-gray-800">{{ $stats['total_projects']['previous'] }}</p>
                        </div>
                    </div>

                    {{-- Progress Bars --}}
                    <div class="space-y-3">
                        @if($stats['total_projects']['growth_rate'] > 0)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium">Growth Rate</span>
                                <span class="text-green-500 font-semibold">+{{ $stats['total_projects']['growth_rate']
                                    }}%</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 rounded-full"
                                    style="width: {{ min(100, $stats['total_projects']['growth_rate']) }}%"></div>
                            </div>
                        </div>
                        @endif

                        @if($stats['total_projects']['decline_rate'] > 0)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium">Decline Rate</span>
                                <span class="text-red-500 font-semibold">-{{ $stats['total_projects']['decline_rate']
                                    }}%</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-red-500 rounded-full"
                                    style="width: {{ min(100, $stats['total_projects']['decline_rate']) }}%"></div>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Trend Indicators --}}
                    <div class="flex gap-3 pt-2 border-t border-gray-100">
                        <div
                            class="flex-1 p-2 {{ $stats['total_projects']['trend'] === 'up' ? 'bg-green-50/50' : 'bg-red-50/50' }} rounded-lg">
                            <div class="flex items-center gap-1">
                                @if($stats['total_projects']['trend'] === 'up')
                                <x-heroicon-s-arrow-trending-up class="w-4 h-4 text-green-500" />
                                <span class="text-xs font-medium text-green-600">+{{ $stats['total_projects']['change']
                                    }}% Growth</span>
                                @else
                                <x-heroicon-s-arrow-trending-down class="w-4 h-4 text-red-500" />
                                <span class="text-xs font-medium text-red-600">{{ $stats['total_projects']['change'] }}%
                                    Decline</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Compared to last month</p>
                        </div>
                    </div>

                    {{-- Footer Info --}}
                    <div class="text-xs text-gray-500">
                        Based on {{ $stats['total_projects']['current'] }} total projects tracked in the system.
                    </div>
                </div>
                <div class="absolute top-4 -left-2 w-4 h-4 bg-white rotate-45 border-l border-b border-gray-100/50">
                </div>
            </div>

            {{-- Card Content --}}
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Total Projects</span>
                <div class="p-2 bg-blue-50 rounded-lg group-hover:bg-blue-100 transition-colors duration-200">
                    <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-blue-500" />
                </div>
            </div>

            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold text-gray-900">{{ $stats['total_projects']['current'] }}</span>
                <span
                    class="text-sm {{ $stats['total_projects']['trend'] === 'up' ? 'text-green-500' : 'text-red-500' }} flex items-center">
                    @if($stats['total_projects']['trend'] === 'up')
                    <x-heroicon-s-arrow-trending-up class="w-4 h-4 mr-1" />
                    @else
                    <x-heroicon-s-arrow-trending-down class="w-4 h-4 mr-1" />
                    @endif
                    {{ $stats['total_projects']['change'] }}%
                </span>
            </div>
            <div class="flex items-center justify-between text-xs text-gray-500">
                <span>vs last month</span>
                <div class="flex items-center gap-1">
                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                    <span class="font-medium">Total Projects</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Projects Card --}}
    <div x-on:click="$dispatch('open-modal', { id: 'stats-modal-active' })"
        class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-200 p-5 group cursor-pointer"
        x-data="{ showTooltip: false }" @mouseenter="showTooltip = true" @mouseleave="showTooltip = false">
        <div class="space-y-3 relative">
            {{-- Enhanced Right-sided Tooltip --}}
            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-x-1" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-1"
                class="absolute left-[105%] top-0 bg-white rounded-xl text-gray-800 p-4 shadow-xl z-10 w-72 border border-gray-100/50 backdrop-blur-sm">
                <div class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div>
                            <h3 class="font-semibold text-gray-800">Active Projects</h3>
                            <p class="text-xs text-gray-500">Last 30 days performance</p>
                        </div>
                        <span
                            class="text-xs px-2 py-1 bg-green-50 text-green-600 rounded-full font-medium">Monthly</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-xs text-gray-500">Current</span>
                            <p class="text-lg font-bold text-gray-800">{{ $stats['active_projects']['current'] }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs text-gray-500">Previous</span>
                            <p class="text-lg font-bold text-gray-800">{{ $stats['active_projects']['previous'] }}</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @if($stats['active_projects']['growth_rate'] > 0)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium">Growth Rate</span>
                                <span class="text-green-500 font-semibold">+{{ $stats['active_projects']['growth_rate']
                                    }}%</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 rounded-full"
                                    style="width: {{ min(100, $stats['active_projects']['growth_rate']) }}%"></div>
                            </div>
                        </div>
                        @endif

                        @if($stats['active_projects']['decline_rate'] > 0)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium">Decline Rate</span>
                                <span class="text-red-500 font-semibold">-{{ $stats['active_projects']['decline_rate']
                                    }}%</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-red-500 rounded-full"
                                    style="width: {{ min(100, $stats['active_projects']['decline_rate']) }}%"></div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="flex gap-3 pt-2 border-t border-gray-100">
                        <div
                            class="flex-1 p-2 {{ $stats['active_projects']['trend'] === 'up' ? 'bg-green-50/50' : 'bg-red-50/50' }} rounded-lg">
                            <div class="flex items-center gap-1">
                                @if($stats['active_projects']['trend'] === 'up')
                                <x-heroicon-s-arrow-trending-up class="w-4 h-4 text-green-500" />
                                <span class="text-xs font-medium text-green-600">+{{ $stats['active_projects']['change']
                                    }}% Growth</span>
                                @else
                                <x-heroicon-s-arrow-trending-down class="w-4 h-4 text-red-500" />
                                <span class="text-xs font-medium text-red-600">{{ $stats['active_projects']['change']
                                    }}% Decline</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Compared to last month</p>
                        </div>
                    </div>

                    <div class="text-xs text-gray-500">
                        Based on {{ $stats['active_projects']['current'] }} active projects tracked in the system.
                    </div>
                </div>
                <div class="absolute top-4 -left-2 w-4 h-4 bg-white rotate-45 border-l border-b border-gray-100/50">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Active Projects</span>
                <div class="p-2 bg-green-50 rounded-lg group-hover:bg-green-100 transition-colors duration-200">
                    <x-heroicon-o-play class="w-5 h-5 text-green-500" />
                </div>
            </div>

            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold text-gray-900">{{ $stats['active_projects']['current'] }}</span>
                <span
                    class="text-sm {{ $stats['active_projects']['trend'] === 'up' ? 'text-green-500' : 'text-red-500' }} flex items-center">
                    @if($stats['active_projects']['trend'] === 'up')
                    <x-heroicon-s-arrow-trending-up class="w-4 h-4 mr-1" />
                    @else
                    <x-heroicon-s-arrow-trending-down class="w-4 h-4 mr-1" />
                    @endif
                    {{ $stats['active_projects']['change'] }}%
                </span>
            </div>

            <div class="flex items-center justify-between text-xs text-gray-500">
                <span>vs last month</span>
                <div class="flex items-center gap-1">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <span class="font-medium">In Progress</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Completed Projects Card --}}
    <div x-on:click="$dispatch('open-modal', { id: 'stats-modal-completed' })"
        class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-200 p-5 group cursor-pointer"
        x-data="{ showTooltip: false }" @mouseenter="showTooltip = true" @mouseleave="showTooltip = false">
        <div class="space-y-3 relative">
            {{-- Enhanced Right-sided Tooltip --}}
            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-x-1" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-1"
                class="absolute left-[105%] top-0 bg-white rounded-xl text-gray-800 p-4 shadow-xl z-10 w-72 border border-gray-100/50 backdrop-blur-sm">
                <div class="space-y-4">
                    {{-- Header --}}
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div>
                            <h3 class="font-semibold text-gray-800">Completed Projects</h3>
                            <p class="text-xs text-gray-500">Last 30 days performance</p>
                        </div>
                        <span
                            class="text-xs px-2 py-1 bg-purple-50 text-purple-600 rounded-full font-medium">Monthly</span>
                    </div>

                    {{-- Stats Grid --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-xs text-gray-500">Current</span>
                            <p class="text-lg font-bold text-gray-800">{{ $stats['completed_projects']['current'] }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs text-gray-500">Previous</span>
                            <p class="text-lg font-bold text-gray-800">{{ $stats['completed_projects']['previous'] }}
                            </p>
                        </div>
                    </div>

                    {{-- Progress Bars --}}
                    <div class="space-y-3">
                        @if($stats['completed_projects']['growth_rate'] > 0)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium">Growth Rate</span>
                                <span class="text-green-500 font-semibold">+{{
                                    $stats['completed_projects']['growth_rate'] }}%</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 rounded-full"
                                    style="width: {{ min(100, $stats['completed_projects']['growth_rate']) }}%"></div>
                            </div>
                        </div>
                        @endif

                        @if($stats['completed_projects']['decline_rate'] > 0)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium">Decline Rate</span>
                                <span class="text-red-500 font-semibold">-{{
                                    $stats['completed_projects']['decline_rate'] }}%</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-red-500 rounded-full"
                                    style="width: {{ min(100, $stats['completed_projects']['decline_rate']) }}%"></div>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Trend Indicators --}}
                    <div class="flex gap-3 pt-2 border-t border-gray-100">
                        <div
                            class="flex-1 p-2 {{ $stats['completed_projects']['trend'] === 'up' ? 'bg-green-50/50' : 'bg-red-50/50' }} rounded-lg">
                            <div class="flex items-center gap-1">
                                @if($stats['completed_projects']['trend'] === 'up')
                                <x-heroicon-s-arrow-trending-up class="w-4 h-4 text-green-500" />
                                <span class="text-xs font-medium text-green-600">+{{
                                    $stats['completed_projects']['change'] }}% Growth</span>
                                @else
                                <x-heroicon-s-arrow-trending-down class="w-4 h-4 text-red-500" />
                                <span class="text-xs font-medium text-red-600">{{ $stats['completed_projects']['change']
                                    }}% Decline</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Compared to last month</p>
                        </div>
                    </div>

                    {{-- Footer Info --}}
                    <div class="text-xs text-gray-500">
                        Based on {{ $stats['completed_projects']['current'] }} completed projects this month.
                    </div>
                </div>
                {{-- Arrow pointing left --}}
                <div class="absolute top-4 -left-2 w-4 h-4 bg-white rotate-45 border-l border-b border-gray-100/50">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Completed</span>
                <div class="p-2 bg-purple-50 rounded-lg group-hover:bg-purple-100 transition-colors duration-200">
                    <x-heroicon-o-check-circle class="w-5 h-5 text-purple-500" />
                </div>
            </div>

            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold text-gray-900">{{ $stats['completed_projects']['current'] }}</span>
                <span
                    class="text-sm {{ $stats['completed_projects']['trend'] === 'up' ? 'text-green-500' : 'text-red-500' }} flex items-center">
                    @if($stats['completed_projects']['trend'] === 'up')
                    <x-heroicon-s-arrow-trending-up class="w-4 h-4 mr-1" />
                    @else
                    <x-heroicon-s-arrow-trending-down class="w-4 h-4 mr-1" />
                    @endif
                    {{ $stats['completed_projects']['change'] }}%
                </span>
            </div>

            <div class="flex items-center justify-between text-xs text-gray-500">
                <span>vs last month</span>
                <div class="flex items-center gap-1">
                    <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                    <span class="font-medium">Finished</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Pending Documents Card --}}
    <div x-on:click="$dispatch('open-modal', { id: 'stats-modal-pending' })"
        class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-200 p-5 group cursor-pointer"
        x-data="{ showTooltip: false }" @mouseenter="showTooltip = true" @mouseleave="showTooltip = false">
        <div class="space-y-3 relative">
            {{-- Enhanced Right-sided Tooltip --}}
            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-x-1" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-1"
                class="absolute right-[105%] top-0 bg-white rounded-xl text-gray-800 p-4 shadow-xl z-10 w-72 border border-gray-100/50 backdrop-blur-sm">
                <div class="space-y-4">
                    {{-- Header --}}
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div>
                            <h3 class="font-semibold text-gray-800">Pending Documents</h3>
                            <p class="text-xs text-gray-500">Last 30 days performance</p>
                        </div>
                        <span
                            class="text-xs px-2 py-1 bg-yellow-50 text-yellow-600 rounded-full font-medium">Monthly</span>
                    </div>

                    {{-- Stats Grid --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-xs text-gray-500">Current</span>
                            <p class="text-lg font-bold text-gray-800">{{ $stats['pending_documents']['current'] }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs text-gray-500">Previous</span>
                            <p class="text-lg font-bold text-gray-800">{{ $stats['pending_documents']['previous'] }}</p>
                        </div>
                    </div>

                    {{-- Progress Bars --}}
                    <div class="space-y-3">
                        @if($stats['pending_documents']['growth_rate'] > 0)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium">Growth Rate</span>
                                <span class="text-yellow-500 font-semibold">+{{
                                    $stats['pending_documents']['growth_rate'] }}%</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-yellow-500 rounded-full"
                                    style="width: {{ min(100, $stats['pending_documents']['growth_rate']) }}%"></div>
                            </div>
                        </div>
                        @endif

                        @if($stats['pending_documents']['decline_rate'] > 0)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium">Decline Rate</span>
                                <span class="text-green-500 font-semibold">-{{
                                    $stats['pending_documents']['decline_rate'] }}%</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 rounded-full"
                                    style="width: {{ min(100, $stats['pending_documents']['decline_rate']) }}%"></div>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Trend Indicators --}}
                    <div class="flex gap-3 pt-2 border-t border-gray-100">
                        <div
                            class="flex-1 p-2 {{ $stats['pending_documents']['trend'] === 'up' ? 'bg-yellow-50/50' : 'bg-green-50/50' }} rounded-lg">
                            <div class="flex items-center gap-1">
                                @if($stats['pending_documents']['trend'] === 'up')
                                <x-heroicon-s-arrow-trending-up class="w-4 h-4 text-yellow-500" />
                                <span class="text-xs font-medium text-yellow-600">+{{
                                    $stats['pending_documents']['change'] }}% Increase</span>
                                @else
                                <x-heroicon-s-arrow-trending-down class="w-4 h-4 text-green-500" />
                                <span class="text-xs font-medium text-green-600">{{
                                    $stats['pending_documents']['change'] }}% Decrease</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Compared to last month</p>
                        </div>
                    </div>

                    {{-- Footer Info --}}
                    <div class="text-xs text-gray-500">
                        Based on {{ $stats['pending_documents']['current'] }} pending documents requiring review.
                    </div>
                </div>
                {{-- Arrow pointing right --}}
                <div class="absolute top-4 -right-2 w-4 h-4 bg-white rotate-45 border-r border-t border-gray-100/50">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Pending Documents</span>
                <div class="p-2 bg-yellow-50 rounded-lg group-hover:bg-yellow-100 transition-colors duration-200">
                    <x-heroicon-o-document class="w-5 h-5 text-yellow-500" />
                </div>
            </div>

            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold text-gray-900">{{ $stats['pending_documents']['current'] }}</span>
                <span
                    class="text-sm {{ $stats['pending_documents']['trend'] === 'up' ? 'text-yellow-500' : 'text-green-500' }} flex items-center">
                    @if($stats['pending_documents']['trend'] === 'up')
                    <x-heroicon-s-arrow-trending-up class="w-4 h-4 mr-1" />
                    @else
                    <x-heroicon-s-arrow-trending-down class="w-4 h-4 mr-1" />
                    @endif
                    {{ $stats['pending_documents']['change'] }}%
                </span>
            </div>

            <div class="flex items-center justify-between text-xs text-gray-500">
                <span>vs last month</span>
                <div class="flex items-center gap-1">
                    <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                    <span class="font-medium">Pending</span>
                </div>
            </div>
        </div>
    </div>



    {{-- Modals --}}
    <x-filament::modal id="stats-modal-total" width="3xl">
        @livewire('dashboard.stats-detail-modal', [
        'type' => 'total',
        'data' => $stats['total_projects']
        ], key('modal-total'))
    </x-filament::modal>

    <x-filament::modal id="stats-modal-active" width="3xl">
        @livewire('dashboard.stats-detail-modal', [
        'type' => 'active',
        'data' => $stats['active_projects']
        ], key('modal-active'))
    </x-filament::modal>

    <x-filament::modal id="stats-modal-completed" width="3xl">
        @livewire('dashboard.stats-detail-modal', [
        'type' => 'completed',
        'data' => $stats['completed_projects']
        ], key('modal-completed'))
    </x-filament::modal>

    <x-filament::modal id="stats-modal-pending" width="3xl">
        @livewire('dashboard.stats-detail-modal', [
        'type' => 'pending',
        'data' => $stats['pending_documents']
        ], key('modal-pending'))
    </x-filament::modal>


    @push('scripts')
    <script>
        function showTooltip(event, data) {
            // Create and show a tooltip using something like tippy.js
            tippy(event.currentTarget, {
                content: `
                    <div class="p-2">
                        <div class="font-medium">Monthly Comparison</div>
                        <div class="text-sm">Previous: ${data.previous}</div>
                        <div class="text-sm ${data.change >= 0 ? 'text-green-500' : 'text-red-500'}">
                            Change: ${data.change}%
                        </div>
                    </div>
                `,
                allowHTML: true,
                placement: 'top',
            });
        }
    </script>
    @endpush


</div>