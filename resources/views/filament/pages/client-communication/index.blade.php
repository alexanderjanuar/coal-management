<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total (Filtered) --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $this->stats['total'] }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $this->dateRangeLabel }}</p>
                    </div>
                    <div class="p-4 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl">
                        <x-heroicon-o-chat-bubble-left-right class="w-7 h-7 text-white" />
                    </div>
                </div>
            </div>

            {{-- Upcoming --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Upcoming</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $this->stats['upcoming'] }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Future scheduled</p>
                    </div>
                    <div class="p-4 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl">
                        <x-heroicon-o-calendar-days class="w-7 h-7 text-white" />
                    </div>
                </div>
            </div>

            {{-- Scheduled --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Scheduled</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $this->stats['scheduled'] }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">In date range</p>
                    </div>
                    <div class="p-4 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl">
                        <x-heroicon-o-clock class="w-7 h-7 text-white" />
                    </div>
                </div>
            </div>

            {{-- Completed --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $this->stats['completed'] }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">In date range</p>
                    </div>
                    <div class="p-4 bg-gradient-to-br from-green-500 to-green-600 rounded-xl">
                        <x-heroicon-o-check-circle class="w-7 h-7 text-white" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Improved Collapsible Filters --}}
        <div x-data="{ filtersOpen: false }"
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

            {{-- Main Filter Bar (Always Visible) --}}
            <div class="p-4">
                <div class="flex flex-col lg:flex-row gap-4">
                    {{-- Search & Client Selection --}}
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="relative">
                            <x-heroicon-o-magnifying-glass
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                            <input type="text" wire:model.live.debounce.300ms="search"
                                placeholder="Search communications..."
                                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                        </div>
                        <div class="relative">
                            <x-heroicon-o-building-office
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                            <select wire:model.live="filterClient"
                                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all appearance-none">
                                <option value="">All Clients</option>
                                @foreach($this->clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Quick Date Range Pills --}}
                    <div class="flex flex-wrap gap-2 items-center">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 hidden lg:block">Quick:</span>

                        <button wire:click="$set('filterDateRange', 'today')"
                            class="px-3 py-1.5 text-sm font-medium rounded-full transition-all duration-200 {{ $filterDateRange === 'today' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-primary-100 dark:hover:bg-primary-900/30' }}">
                            Today
                        </button>
                        <button wire:click="$set('filterDateRange', 'week')"
                            class="px-3 py-1.5 text-sm font-medium rounded-full transition-all duration-200 {{ $filterDateRange === 'week' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-primary-100 dark:hover:bg-primary-900/30' }}">
                            Week
                        </button>
                        <button wire:click="$set('filterDateRange', 'month')"
                            class="px-3 py-1.5 text-sm font-medium rounded-full transition-all duration-200 {{ $filterDateRange === 'month' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-primary-100 dark:hover:bg-primary-900/30' }}">
                            Month
                        </button>
                    </div>

                    {{-- Filter Toggle & Actions --}}
                    <div class="flex items-center gap-3">
                        {{-- Active Filter Count --}}
                        @php
                        $activeFilters = collect([
                        $search !== '',
                        $filterClient !== '',
                        $filterType !== '',
                        $filterStatus !== '',
                        $filterTimeOfDay !== '',
                        $filterDateRange === 'custom' && ($filterDateFrom || $filterDateTo),
                        !in_array($filterDateRange, ['month', 'all'])
                        ])->filter()->count();
                        @endphp

                        @if($activeFilters > 0)
                        <span
                            class="px-2 py-1 text-xs font-medium bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 rounded-full">
                            {{ $activeFilters }} active
                        </span>
                        @endif

                        <button x-on:click="filtersOpen = !filtersOpen"
                            class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            <x-heroicon-o-funnel class="w-4 h-4" />
                            <span x-text="filtersOpen ? 'Hide Filters' : 'More Filters'"></span>
                            <x-heroicon-m-chevron-down class="w-4 h-4 transition-transform"
                                x-bind:class="filtersOpen ? 'rotate-180' : ''" />
                        </button>

                        <button wire:click="resetFilters"
                            class="px-4 py-2.5 text-sm font-medium text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors">
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            {{-- Advanced Filters (Collapsible) --}}
            <div x-show="filtersOpen" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2"
                class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <div class="p-4 space-y-4">

                    {{-- Extended Date Range Options --}}
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide">
                            Date Range
                        </label>
                        <div class="flex flex-wrap gap-2">
                            @foreach([
                            'today' => 'Today',
                            'yesterday' => 'Yesterday',
                            'week' => 'This Week',
                            'month' => 'This Month',
                            'last_month' => 'Last Month',
                            'custom' => 'Custom Range'
                            ] as $key => $label)
                            <button wire:click="$set('filterDateRange', '{{ $key }}')"
                                class="px-3 py-1.5 text-sm font-medium rounded-lg transition-all {{ $filterDateRange === $key ? 'bg-primary-600 text-white shadow-sm' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:border-primary-500' }}">
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>

                        {{-- Custom Date Inputs --}}
                        @if($filterDateRange === 'custom')
                        <div class="grid grid-cols-2 gap-3 mt-3">
                            <input type="date" wire:model.live="filterDateFrom" placeholder="From"
                                class="px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500/20">
                            <input type="date" wire:model.live="filterDateTo" placeholder="To"
                                class="px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500/20">
                        </div>
                        @endif
                    </div>

                    {{-- Compact Filter Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        {{-- Communication Type --}}
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide">
                                Type
                            </label>
                            <div class="grid grid-cols-2 gap-1">
                                @foreach([
                                '' => ['All', '🔍'],
                                'meeting' => ['Meeting', '📅'],
                                'email' => ['Email', '✉️'],
                                'phone' => ['Phone', '📞'],
                                'video_call' => ['Video', '🎥'],
                                'other' => ['Other', '💬']
                                ] as $value => [$label, $icon])
                                <button wire:click="$set('filterType', '{{ $value }}')"
                                    class="px-2 py-1.5 text-xs font-medium rounded-md transition-all text-left {{ $filterType === $value ? 'bg-blue-500 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:border-blue-400' }}">
                                    {{ $icon }} {{ $label }}
                                </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Status --}}
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide">
                                Status
                            </label>
                            <div class="grid grid-cols-2 gap-1">
                                @foreach([
                                '' => ['All', '🔍'],
                                'scheduled' => ['Scheduled', '⏰'],
                                'completed' => ['Completed', '✅'],
                                'cancelled' => ['Cancelled', '❌'],
                                'rescheduled' => ['Rescheduled', '🔄']
                                ] as $value => [$label, $icon])
                                <button wire:click="$set('filterStatus', '{{ $value }}')"
                                    class="px-2 py-1.5 text-xs font-medium rounded-md transition-all text-left {{ $filterStatus === $value ? 'bg-green-500 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:border-green-400' }}">
                                    {{ $icon }} {{ $label }}
                                </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Time of Day --}}
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide">
                                Time of Day
                            </label>
                            <div class="grid grid-cols-2 gap-1">
                                @foreach([
                                '' => ['All Day', '🌍'],
                                'morning' => ['Morning', '🌅'],
                                'afternoon' => ['Afternoon', '☀️'],
                                'evening' => ['Evening', '🌙']
                                ] as $value => [$label, $icon])
                                <button wire:click="$set('filterTimeOfDay', '{{ $value }}')"
                                    class="px-2 py-1.5 text-xs font-medium rounded-md transition-all text-left {{ $filterTimeOfDay === $value ? 'bg-orange-500 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:border-orange-400' }}">
                                    {{ $icon }} {{ $label }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Communications Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($this->communications as $communication)
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all duration-300 overflow-hidden group">
                {{-- Header with gradient --}}
                <div class="p-6 pb-4 
                        @if($communication->type === 'meeting') bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20
                        @elseif($communication->type === 'email') bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20
                        @elseif($communication->type === 'phone') bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20
                        @elseif($communication->type === 'video_call') bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20
                        @else bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900/20 dark:to-gray-800/20
                        @endif
                    ">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3">
                            {{-- Type Icon --}}
                            <div class="p-2 rounded-lg
                                    @if($communication->type === 'meeting') bg-blue-500
                                    @elseif($communication->type === 'email') bg-green-500
                                    @elseif($communication->type === 'phone') bg-yellow-500
                                    @elseif($communication->type === 'video_call') bg-purple-500
                                    @else bg-gray-500
                                    @endif
                                ">
                                @if($communication->type === 'meeting')
                                <x-heroicon-o-calendar class="w-5 h-5 text-white" />
                                @elseif($communication->type === 'email')
                                <x-heroicon-o-envelope class="w-5 h-5 text-white" />
                                @elseif($communication->type === 'phone')
                                <x-heroicon-o-phone class="w-5 h-5 text-white" />
                                @elseif($communication->type === 'video_call')
                                <x-heroicon-o-video-camera class="w-5 h-5 text-white" />
                                @else
                                <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 text-white" />
                                @endif
                            </div>

                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    {{ ucfirst(str_replace('_', ' ', $communication->type)) }}
                                </p>
                            </div>
                        </div>

                        {{-- Priority Badge --}}
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                @if($communication->priority === 'urgent') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                @elseif($communication->priority === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400
                                @elseif($communication->priority === 'normal') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                                @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400
                                @endif
                            ">
                            {{ ucfirst($communication->priority) }}
                        </span>
                    </div>
                </div>

                {{-- Content --}}
                <div class="p-6 space-y-4">
                    {{-- Client Name --}}
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Client</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $communication->client->name }}
                        </p>
                    </div>

                    {{-- Title --}}
                    <div>
                        <h3
                            class="text-base font-semibold text-gray-900 dark:text-white line-clamp-2 group-hover:text-primary-600 transition-colors">
                            {{ $communication->title }}
                        </h3>
                        @if($communication->description)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 line-clamp-2">
                            {{ $communication->description }}
                        </p>
                        @endif
                    </div>

                    {{-- Date & Time --}}
                    <div class="flex items-center space-x-4 text-sm">
                        <div class="flex items-center text-gray-600 dark:text-gray-400">
                            <x-heroicon-o-calendar-days class="w-4 h-4 mr-1.5" />
                            {{ \Carbon\Carbon::parse($communication->communication_date)->format('d M Y') }}
                        </div>
                        @if($communication->communication_time)
                        <div class="flex items-center text-gray-600 dark:text-gray-400">
                            <x-heroicon-o-clock class="w-4 h-4 mr-1.5" />
                            {{ \Carbon\Carbon::parse($communication->communication_time)->format('H:i') }}
                        </div>
                        @endif
                    </div>

                    {{-- Location --}}
                    @if($communication->location)
                    <div class="flex items-start text-sm text-gray-600 dark:text-gray-400">
                        <x-heroicon-o-map-pin class="w-4 h-4 mr-1.5 mt-0.5 flex-shrink-0" />
                        <span class="line-clamp-1">{{ $communication->location }}</span>
                    </div>
                    @endif

                    {{-- Project --}}
                    @if($communication->project)
                    <div class="flex items-center text-sm">
                        <x-heroicon-o-briefcase class="w-4 h-4 mr-1.5 text-gray-400" />
                        <span class="text-gray-600 dark:text-gray-400">{{ $communication->project->name }}</span>
                    </div>
                    @endif

                    {{-- Status Badge --}}
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full
                                    @if($communication->status === 'completed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                    @elseif($communication->status === 'scheduled') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                                    @elseif($communication->status === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                    @else bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                                    @endif
                                ">
                                @if($communication->status === 'completed')
                                <x-heroicon-m-check-circle class="w-4 h-4 mr-1" />
                                @elseif($communication->status === 'scheduled')
                                <x-heroicon-m-clock class="w-4 h-4 mr-1" />
                                @elseif($communication->status === 'cancelled')
                                <x-heroicon-m-x-circle class="w-4 h-4 mr-1" />
                                @else
                                <x-heroicon-m-arrow-path class="w-4 h-4 mr-1" />
                                @endif
                                {{ ucfirst($communication->status) }}
                            </span>

                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                by {{ $communication->user->name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full">
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <div class="max-w-md mx-auto">
                        <div
                            class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <x-heroicon-o-chat-bubble-left-right class="w-8 h-8 text-gray-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No communications found
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            @if($search || $filterClient || $filterType || $filterStatus)
                            Try adjusting your filters or search terms
                            @else
                            No communications in the selected date range
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($this->communications->hasPages())
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            {{ $this->communications->links() }}
        </div>
        @endif
    </div>
</x-filament-panels::page>