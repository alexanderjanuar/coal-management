{{-- resources/views/livewire/daily-task/daily-task-list-component.blade.php - Fully Responsive --}}
<div class="space-y-4 lg:space-y-6" x-data="taskManager()">


    {{-- Enhanced Filters Section - Mobile Optimized & Collapsible --}}
    <div class="bg-white rounded-lg lg:rounded-xl border border-gray-200 shadow-sm" x-data="{ filtersCollapsed: true }">
        <div class="p-4 lg:p-6 border-b border-gray-100">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 lg:w-8 lg:h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-funnel class="w-3 h-3 lg:w-4 lg:h-4 text-gray-600" />
                    </div>
                    <div>
                        <h3 class="text-base lg:text-lg font-semibold text-gray-900">Filters & Controls</h3>
                        <p class="text-xs lg:text-sm text-gray-500 hidden sm:block">Customize your task view</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <x-filament::badge color="primary" size="lg" icon="heroicon-o-document-text">
                        <span class="hidden sm:inline">{{ number_format($totalTasks) }} tasks</span>
                        <span class="sm:hidden">{{ $totalTasks }}</span>
                    </x-filament::badge>

                    <x-filament::button wire:click="resetFilters" color="gray" size="sm" icon="heroicon-o-arrow-path"
                        outlined>
                        <span class="hidden sm:inline">Reset</span>
                        <span class="sm:hidden sr-only">Reset Filters</span>
                    </x-filament::button>

                    <button @click="filtersCollapsed = !filtersCollapsed"
                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-500 transition-transform duration-200"
                            x-bind:class="{ 'rotate-180': !filtersCollapsed }" />
                    </button>
                </div>
            </div>
        </div>

        <div x-show="!filtersCollapsed" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-2" class="p-4 lg:p-6">

            {{-- Active Filters Display --}}
            @if(!empty($activeFilters))
            <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-funnel class="w-4 h-4 text-blue-600" />
                        <span class="text-sm font-medium text-blue-900">Active Filters</span>
                        <span class="text-xs bg-blue-200 text-blue-800 px-2 py-1 rounded-full">{{ count($activeFilters)
                            }}</span>
                    </div>
                    <button wire:click="resetFilters"
                        class="text-xs text-blue-700 hover:text-blue-900 font-medium hover:underline flex items-center gap-1">
                        <x-heroicon-o-x-mark class="w-3 h-3" />
                        Clear All
                    </button>
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach($activeFilters as $filter)
                    <div class="inline-flex items-center gap-2 px-3 py-2 bg-white rounded-lg border shadow-sm text-sm">
                        <x-dynamic-component :component="$filter['icon']" class="w-3 h-3 {{ match($filter['color']) {
                                    'primary' => 'text-primary-600',
                                    'success' => 'text-green-600',
                                    'warning' => 'text-orange-600',
                                    'danger' => 'text-red-600',
                                    'info' => 'text-blue-600',
                                    default => 'text-gray-600'
                                } }}" />
                        <span class="font-medium text-gray-700">{{ $filter['label'] }}:</span>
                        <span class="text-gray-600">
                            @if(isset($filter['count']) && $filter['count'] > 1)
                            {{ Str::limit($filter['value'], 20) }}
                            <span class="text-xs bg-gray-200 text-gray-700 px-1.5 py-0.5 rounded-full ml-1">{{
                                $filter['count'] }}</span>
                            @else
                            {{ Str::limit($filter['value'], 30) }}
                            @endif
                        </span>
                        <button wire:click="removeFilter('{{ $filter['type'] }}')"
                            class="ml-1 text-gray-400 hover:text-red-500 transition-colors" title="Remove filter">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{ $this->filterForm }}

            <div wire:loading.delay class="flex items-center justify-center py-4">
                <div class="flex items-center text-primary-600">
                    <x-heroicon-o-arrow-path class="w-5 h-5 animate-spin mr-2" />
                    <span class="text-sm font-medium">Loading tasks...</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Enhanced Task List Content - Responsive Views --}}
    <div wire:loading.remove class="space-y-4 lg:space-y-6">
        @if($viewMode === 'list')
        {{-- Desktop Table View --}}
        <div class="hidden lg:block bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            {{-- Enhanced Table Header --}}
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <div class="px-6 py-4">
                    <div class="grid grid-cols-12 gap-4 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        <div class="col-span-1 flex items-center">
                            <input type="checkbox" x-model="selectAll" @change="toggleSelectAll"
                                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 focus:ring-offset-0">
                        </div>
                        <div class="col-span-4 flex items-center gap-2">
                            <button wire:click="sortBy('title')"
                                class="flex items-center gap-2 hover:text-primary-600 transition-colors group">
                                <x-heroicon-o-document-text class="w-4 h-4" />
                                <span>Task</span>
                                @if($sortBy === 'title')
                                @if($sortDirection === 'asc')
                                <x-heroicon-s-chevron-up class="w-4 h-4 text-primary-600" />
                                @else
                                <x-heroicon-s-chevron-down class="w-4 h-4 text-primary-600" />
                                @endif
                                @else
                                <x-heroicon-o-chevron-up-down
                                    class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
                                @endif
                            </button>
                        </div>
                        <div class="col-span-2 flex items-center gap-2">
                            <button wire:click="sortBy('status')"
                                class="flex items-center gap-2 hover:text-primary-600 transition-colors group">
                                <x-heroicon-o-flag class="w-4 h-4" />
                                <span>Status</span>
                                @if($sortBy === 'status')
                                @if($sortDirection === 'asc')
                                <x-heroicon-s-chevron-up class="w-4 h-4 text-primary-600" />
                                @else
                                <x-heroicon-s-chevron-down class="w-4 h-4 text-primary-600" />
                                @endif
                                @else
                                <x-heroicon-o-chevron-up-down
                                    class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
                                @endif
                            </button>
                        </div>
                        <div class="col-span-1 flex items-center gap-2">
                            <button wire:click="sortBy('priority')"
                                class="flex items-center gap-2 hover:text-primary-600 transition-colors group">
                                <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                                <span>Priority</span>
                                @if($sortBy === 'priority')
                                @if($sortDirection === 'asc')
                                <x-heroicon-s-chevron-up class="w-4 h-4 text-primary-600" />
                                @else
                                <x-heroicon-s-chevron-down class="w-4 h-4 text-primary-600" />
                                @endif
                                @else
                                <x-heroicon-o-chevron-up-down
                                    class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
                                @endif
                            </button>
                        </div>
                        <div class="col-span-2 flex items-center gap-2">
                            <x-heroicon-o-users class="w-4 h-4" />
                            <span>Assignee</span>
                        </div>
                        <div class="col-span-1 flex items-center gap-2">
                            <x-heroicon-o-folder class="w-4 h-4" />
                            <span>Project</span>
                        </div>
                        <div class="col-span-1 flex items-center gap-2">
                            <button wire:click="sortBy('task_date')"
                                class="flex items-center gap-2 hover:text-primary-600 transition-colors group">
                                <x-heroicon-o-calendar-days class="w-4 h-4" />
                                <span>Due Date</span>
                                @if($sortBy === 'task_date')
                                @if($sortDirection === 'asc')
                                <x-heroicon-s-chevron-up class="w-4 h-4 text-primary-600" />
                                @else
                                <x-heroicon-s-chevron-down class="w-4 h-4 text-primary-600" />
                                @endif
                                @else
                                <x-heroicon-o-chevron-up-down
                                    class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Desktop Table Body --}}
            @if($groupBy === 'none')
            {{-- No Grouping - Direct Pagination --}}
            <div class="divide-y divide-gray-100">
                @forelse($paginatedTasks as $index => $task)
                <div class="px-6 py-4 hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent transition-all duration-200 group cursor-pointer border-l-4 border-l-transparent hover:border-l-primary-300"
                    x-data="{ expanded: false }"
                    :class="{ 'bg-primary-25 border-l-primary-500': selectedTasks.includes({{ $task->id }}) }">
                    <livewire:daily-task.daily-task-item :task="$task" :key="'task-'.$task->id . time()" />
                </div>
                @empty
                <div class="py-16 text-center">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <x-heroicon-o-clipboard-document-list class="w-12 h-12 text-gray-400" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No tasks found</h3>
                    <p class="text-gray-500 mb-6 max-w-sm mx-auto">
                        @if(!empty(array_filter($this->getCurrentFilters())))
                        Try adjusting your filters to see more tasks
                        @else
                        Get started by creating your first task above
                        @endif
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <x-filament::button wire:click="resetFilters" color="gray" outlined>
                            Clear Filters
                        </x-filament::button>
                        <x-filament::button color="primary" icon="heroicon-o-plus">
                            Create Task
                        </x-filament::button>
                    </div>
                </div>
                @endforelse
            </div>

            @if($paginatedTasks && $paginatedTasks->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $paginatedTasks->links() }}
            </div>
            @endif
            @else
            {{-- Desktop Grouped View --}}
            <div class="divide-y divide-gray-200">
                @forelse($groupedTasks as $groupName => $tasks)
                {{-- Enhanced Group Header - Fixed Alpine.js --}}
                <div class="bg-gradient-to-r from-gray-25 to-gray-50 px-6 py-4 border-l-4 border-l-primary-500"
                    x-data="{ collapsed: false }">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            @switch($groupBy)
                            @case('status')
                            <div class="w-6 h-6 rounded-lg flex items-center justify-center
                                                    {{ match($groupName) {
                                                        'Completed' => 'bg-green-100',
                                                        'In Progress' => 'bg-yellow-100',
                                                        'Pending' => 'bg-gray-100',
                                                        'Cancelled' => 'bg-red-100',
                                                        default => 'bg-gray-100'
                                                    } }}">
                                <div class="w-3 h-3 rounded-full 
                                                        {{ match($groupName) {
                                                            'Completed' => 'bg-green-500',
                                                            'In Progress' => 'bg-yellow-500',
                                                            'Pending' => 'bg-gray-400',
                                                            'Cancelled' => 'bg-red-500',
                                                            default => 'bg-gray-400'
                                                        } }}">
                                </div>
                            </div>
                            @break
                            @case('priority')
                            <div class="w-6 h-6 bg-orange-100 rounded-lg flex items-center justify-center">
                                @php
                                $priorityIcon = match($groupName) {
                                'Urgent' => 'heroicon-s-exclamation-triangle',
                                'High' => 'heroicon-o-exclamation-triangle',
                                'Normal' => 'heroicon-o-minus',
                                'Low' => 'heroicon-o-arrow-down',
                                default => 'heroicon-o-minus'
                                };
                                @endphp
                                <x-dynamic-component :component="$priorityIcon" class="w-4 h-4 text-orange-600" />
                            </div>
                            @break
                            @case('project')
                            <div class="w-6 h-6 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-folder class="w-4 h-4 text-indigo-600" />
                            </div>
                            @break
                            @case('assignee')
                            <div class="w-6 h-6 bg-blue-100 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-user class="w-4 h-4 text-blue-600" />
                            </div>
                            @break
                            @case('date')
                            <div class="w-6 h-6 bg-purple-100 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-calendar-days class="w-4 h-4 text-purple-600" />
                            </div>
                            @break
                            @endswitch

                            <div>
                                <h3 class="text-base font-semibold text-gray-900">{{ $groupName }}</h3>
                                <p class="text-sm text-gray-500">{{ $tasks->count() }} {{ $tasks->count() === 1 ? 'task'
                                    : 'tasks' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <x-filament::badge :color="match($groupBy) {
                                            'status' => match($groupName) {
                                                'Completed' => 'success',
                                                'In Progress' => 'warning',
                                                'Cancelled' => 'danger',
                                                default => 'gray'
                                            },
                                            'priority' => match($groupName) {
                                                'Urgent' => 'danger',
                                                'High' => 'warning',
                                                default => 'primary'
                                            },
                                            default => 'primary'
                                        }" size="sm">
                                {{ $tasks->count() }}
                            </x-filament::badge>

                            <button @click="collapsed = !collapsed"
                                class="p-1.5 hover:bg-white rounded-lg transition-colors">
                                <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400 transition-transform"
                                    x-bind:class="{ 'rotate-180': collapsed }" />
                            </button>
                        </div>
                    </div>

                    {{-- Group Tasks - Fixed collapse --}}
                    <div x-show="!collapsed" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform -translate-y-2" class="mt-4">
                        @foreach($tasks as $task)
                        <div
                            class="px-6 py-4 hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent transition-all duration-200 group border-l-4 border-l-transparent hover:border-l-primary-200 -mx-6 mb-2 last:mb-0 border-b border-gray-100 last:border-b-0">
                            <livewire:daily-task.daily-task-item :task="$task" :key="'task-'.$task->id . time()" />
                        </div>
                        @endforeach
                    </div>
                </div>
                @empty
                <div class="py-16 text-center">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <x-heroicon-o-clipboard-document-list class="w-12 h-12 text-gray-400" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No tasks found</h3>
                    <p class="text-gray-500 mb-6">Try adjusting your filters or create a new task</p>
                    <x-filament::button wire:click="resetFilters" color="gray" outlined>
                        Clear Filters
                    </x-filament::button>
                </div>
                @endforelse
            </div>
            @endif
        </div>

        {{-- Mobile & Tablet List View --}}
        <div class="lg:hidden space-y-3">
            @if($groupBy === 'none')
            {{-- Mobile No Grouping --}}
            @forelse($paginatedTasks as $task)
            <div
                class="bg-white rounded-lg border-2 border-gray-200 shadow-sm hover:shadow-md hover:border-primary-300 transition-all duration-200">
                <livewire:daily-task.daily-task-item :task="$task" :key="'mobile-task-'.$task->id . time()" />
            </div>
            @empty
            <div class="bg-white rounded-lg border-2 border-gray-200 p-8 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-o-clipboard-document-list class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No tasks found</h3>
                <p class="text-gray-500 mb-4 text-sm">
                    @if(!empty(array_filter($this->getCurrentFilters())))
                    Try adjusting your filters to see more tasks
                    @else
                    Get started by creating your first task above
                    @endif
                </p>
                <div class="flex flex-col gap-3">
                    <x-filament::button wire:click="resetFilters" color="gray" outlined size="sm">
                        Clear Filters
                    </x-filament::button>
                    <x-filament::button color="primary" icon="heroicon-o-plus" size="sm">
                        Create Task
                    </x-filament::button>
                </div>
            </div>
            @endforelse

            @if($paginatedTasks && $paginatedTasks->hasPages())
            <div class="bg-white rounded-lg border-2 border-gray-200 p-4">
                {{ $paginatedTasks->links() }}
            </div>
            @endif
            @else
            {{-- Mobile Grouped View --}}
            @forelse($groupedTasks as $groupName => $tasks)
            <div class="bg-white rounded-lg border-2 border-gray-200 shadow-sm overflow-hidden hover:border-primary-300 transition-all duration-200"
                x-data="{ collapsed: false }">
                {{-- Mobile Group Header --}}
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            @switch($groupBy)
                            @case('status')
                            <div class="w-5 h-5 rounded-lg flex items-center justify-center
                                                    {{ match($groupName) {
                                                        'Completed' => 'bg-green-100',
                                                        'In Progress' => 'bg-yellow-100',
                                                        'Pending' => 'bg-gray-100',
                                                        'Cancelled' => 'bg-red-100',
                                                        default => 'bg-gray-100'
                                                    } }}">
                                <div class="w-2.5 h-2.5 rounded-full 
                                                        {{ match($groupName) {
                                                            'Completed' => 'bg-green-500',
                                                            'In Progress' => 'bg-yellow-500',
                                                            'Pending' => 'bg-gray-400',
                                                            'Cancelled' => 'bg-red-500',
                                                            default => 'bg-gray-400'
                                                        } }}">
                                </div>
                            </div>
                            @break
                            @case('priority')
                            <div class="w-5 h-5 bg-orange-100 rounded-lg flex items-center justify-center">
                                @php
                                $priorityIcon = match($groupName) {
                                'Urgent' => 'heroicon-s-exclamation-triangle',
                                'High' => 'heroicon-o-exclamation-triangle',
                                'Normal' => 'heroicon-o-minus',
                                'Low' => 'heroicon-o-arrow-down',
                                default => 'heroicon-o-minus'
                                };
                                @endphp
                                <x-dynamic-component :component="$priorityIcon" class="w-3 h-3 text-orange-600" />
                            </div>
                            @break
                            @case('project')
                            <div class="w-5 h-5 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-folder class="w-3 h-3 text-indigo-600" />
                            </div>
                            @break
                            @case('assignee')
                            <div class="w-5 h-5 bg-blue-100 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-user class="w-3 h-3 text-blue-600" />
                            </div>
                            @break
                            @case('date')
                            <div class="w-5 h-5 bg-purple-100 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-calendar-days class="w-3 h-3 text-purple-600" />
                            </div>
                            @break
                            @endswitch

                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">{{ $groupName }}</h3>
                                <p class="text-xs text-gray-500">{{ $tasks->count() }} {{ $tasks->count() === 1 ? 'task'
                                    : 'tasks' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <x-filament::badge :color="match($groupBy) {
                                            'status' => match($groupName) {
                                                'Completed' => 'success',
                                                'In Progress' => 'warning',
                                                'Cancelled' => 'danger',
                                                default => 'gray'
                                            },
                                            'priority' => match($groupName) {
                                                'Urgent' => 'danger',
                                                'High' => 'warning',
                                                default => 'primary'
                                            },
                                            default => 'primary'
                                        }" size="sm">
                                {{ $tasks->count() }}
                            </x-filament::badge>

                            <button @click="collapsed = !collapsed"
                                class="p-1 hover:bg-white rounded-lg transition-colors">
                                <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400 transition-transform"
                                    x-bind:class="{ 'rotate-180': collapsed }" />
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Mobile Group Tasks --}}
                <div x-show="!collapsed" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform translate-y-0"
                    x-transition:leave-end="opacity-0 transform -translate-y-2" class="divide-y divide-gray-100">
                    @foreach($tasks as $task)
                    <div
                        class="p-0 border-l-4 border-l-transparent hover:border-l-primary-300 hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent transition-all duration-200">
                        <livewire:daily-task.daily-task-item :task="$task"
                            :key="'mobile-grouped-task-'.$task->id . time()" />
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="bg-white rounded-lg border-2 border-gray-200 p-8 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-o-clipboard-document-list class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No tasks found</h3>
                <p class="text-gray-500 mb-4 text-sm">Try adjusting your filters or create a new task</p>
                <x-filament::button wire:click="resetFilters" color="gray" outlined size="sm">
                    Clear Filters
                </x-filament::button>
            </div>
            @endforelse
            @endif
        </div>
        @else
        {{-- Enhanced Kanban View - Fully Responsive --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-6">
            @foreach(['pending', 'in_progress', 'completed', 'cancelled'] as $status)
            @php
            $statusTasks = $groupedTasks->flatten()->where('status', $status);
            $statusConfig = [
            'pending' => ['color' => 'gray', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'icon' =>
            'heroicon-o-clock'],
            'in_progress' => ['color' => 'warning', 'bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'icon' =>
            'heroicon-o-play'],
            'completed' => ['color' => 'success', 'bg' => 'bg-green-50', 'border' => 'border-green-200', 'icon' =>
            'heroicon-o-check-circle'],
            'cancelled' => ['color' => 'danger', 'bg' => 'bg-red-50', 'border' => 'border-red-200', 'icon' =>
            'heroicon-o-x-circle']
            ];
            $config = $statusConfig[$status];
            @endphp

            <div class="bg-white rounded-lg lg:rounded-xl border-2 {{ $config['border'] }} overflow-hidden">
                {{-- Kanban Column Header --}}
                <div class="p-3 lg:p-4 {{ $config['bg'] }} border-b {{ $config['border'] }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 lg:gap-3">
                            <div
                                class="w-6 h-6 lg:w-8 lg:h-8 {{ $config['bg'] }} rounded-lg flex items-center justify-center border {{ $config['border'] }}">
                                <x-dynamic-component :component="$config['icon']" class="w-3 h-3 lg:w-4 lg:h-4" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 text-sm lg:text-base">{{ $statusOptions[$status]
                                    }}</h3>
                                <p class="text-xs lg:text-sm text-gray-500">
                                    <span class="lg:hidden">{{ $statusTasks->count() }}</span>
                                    <span class="hidden lg:inline">{{ $statusTasks->count() }} tasks</span>
                                </p>
                            </div>
                        </div>
                        <x-filament::badge :color="$config['color']" size="sm" class="lg:text-base lg:px-3 lg:py-1">
                            {{ $statusTasks->count() }}
                        </x-filament::badge>
                    </div>
                </div>

                {{-- Kanban Column Content --}}
                <div
                    class="p-3 lg:p-4 space-y-3 min-h-[400px] lg:min-h-[500px] max-h-[600px] lg:max-h-[700px] overflow-y-auto">
                    @forelse($statusTasks as $task)
                    <div
                        class="bg-white rounded-lg border border-gray-200 p-3 lg:p-4 shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer group">
                        {{-- Task Card Content --}}
                        <div class="space-y-3">
                            <div class="flex items-start justify-between">
                                <h4
                                    class="font-medium text-gray-900 text-sm line-clamp-2 group-hover:text-primary-600 transition-colors flex-1 pr-2">
                                    {{ $task->title }}
                                </h4>
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                    {{ match($task->priority) {
                                                        'urgent' => 'bg-red-100 text-red-800',
                                                        'high' => 'bg-orange-100 text-orange-800',
                                                        'normal' => 'bg-blue-100 text-blue-800',
                                                        'low' => 'bg-gray-100 text-gray-600',
                                                        default => 'bg-gray-100 text-gray-600'
                                                    } }}">
                                        <span class="hidden sm:inline">{{ ucfirst($task->priority) }}</span>
                                        <span class="sm:hidden">
                                            {{ match($task->priority) {
                                            'urgent' => '!!!',
                                            'high' => '!!',
                                            'normal' => '!',
                                            'low' => '-',
                                            default => '!'
                                            } }}
                                        </span>
                                    </span>
                                </div>
                            </div>

                            @if($task->description)
                            <p class="text-xs text-gray-500 line-clamp-2">
                                {{ $task->description }}
                            </p>
                            @endif

                            {{-- Task Meta --}}
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <div class="flex items-center gap-1.5">
                                    <x-heroicon-o-calendar-days class="w-3 h-3" />
                                    <span
                                        class="{{ $task->task_date->isPast() && $task->status !== 'completed' ? 'text-red-600 font-medium' : '' }}">
                                        @if($task->task_date->isToday())
                                        Today
                                        @elseif($task->task_date->isTomorrow())
                                        Tomorrow
                                        @else
                                        {{ $task->task_date->format('M d') }}
                                        @endif
                                    </span>
                                </div>

                                @if($task->project)
                                <div class="flex items-center gap-1">
                                    <x-heroicon-o-folder class="w-3 h-3" />
                                    <span class="truncate max-w-16 sm:max-w-20" title="{{ $task->project->name }}">
                                        {{ Str::limit($task->project->name, 10) }}
                                    </span>
                                </div>
                                @endif
                            </div>

                            {{-- Assignees --}}
                            @if($task->assignedUsers && $task->assignedUsers->count() > 0)
                            <div class="flex items-center gap-2">
                                <div class="flex -space-x-1">
                                    @foreach($task->assignedUsers->take(2) as $user)
                                    <div class="w-5 h-5 lg:w-6 lg:h-6 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center text-xs font-medium border-2 border-white"
                                        title="{{ $user->name }}">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    @endforeach
                                    @if($task->assignedUsers->count() > 2)
                                    <div class="w-5 h-5 lg:w-6 lg:h-6 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center text-xs font-medium border-2 border-white"
                                        title="+{{ $task->assignedUsers->count() - 2 }} more">
                                        +{{ $task->assignedUsers->count() - 2 }}
                                    </div>
                                    @endif
                                </div>
                                @if($task->assignedUsers->count() === 1)
                                <span class="text-xs text-gray-600 truncate max-w-20 hidden sm:inline">{{
                                    $task->assignedUsers->first()->name }}</span>
                                @else
                                <span class="text-xs text-gray-500">{{ $task->assignedUsers->count() }} people</span>
                                @endif
                            </div>
                            @endif

                            {{-- Subtasks Progress --}}
                            @if($task->subtasks && $task->subtasks->count() > 0)
                            @php
                            $completed = $task->subtasks->where('status', 'completed')->count();
                            $total = $task->subtasks->count();
                            $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
                            @endphp
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span>{{ $completed }}/{{ $total }} subtasks</span>
                                    <span>{{ $progress }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-primary-600 h-1.5 rounded-full transition-all duration-300"
                                        style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                            @endif

                            {{-- Mobile Quick Actions --}}
                            <div class="lg:hidden pt-2 border-t border-gray-100">
                                <div class="flex items-center justify-between">
                                    <button
                                        class="p-1.5 hover:bg-primary-100 text-primary-600 rounded transition-colors">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </button>
                                    <div class="flex items-center gap-1">
                                        <button class="p-1.5 hover:bg-blue-100 text-blue-600 rounded transition-colors">
                                            <x-heroicon-o-pencil class="w-4 h-4" />
                                        </button>
                                        <button class="p-1.5 hover:bg-red-100 text-red-600 rounded transition-colors">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <div
                            class="w-10 h-10 lg:w-12 lg:h-12 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <x-dynamic-component :component="$config['icon']"
                                class="w-5 h-5 lg:w-6 lg:h-6 text-gray-400" />
                        </div>
                        <p class="text-sm text-gray-500">
                            <span class="hidden sm:inline">No {{ strtolower($statusOptions[$status]) }} tasks</span>
                            <span class="sm:hidden">No tasks</span>
                        </p>
                    </div>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <livewire:daily-task.daily-task-detail-modal />

    {{-- Responsive JavaScript --}}
    <script>
        function taskManager() {
            return {
                selectAll: false,
                selectedTasks: [],
                isMobile: false,
                
                init() {
                    // Check if mobile on initialization
                    this.checkMobile();
                    
                    // Listen for window resize
                    window.addEventListener('resize', () => {
                        this.checkMobile();
                    });
                },
                
                checkMobile() {
                    this.isMobile = window.innerWidth < 1024; // lg breakpoint
                },
                
                toggleSelectAll() {
                    if (this.selectAll) {
                        // Select all visible tasks
                        this.selectedTasks = @json($paginatedTasks ? $paginatedTasks->pluck('id')->toArray() : []);
                    } else {
                        this.selectedTasks = [];
                    }
                },
                
                toggleTaskSelection(taskId) {
                    if (this.selectedTasks.includes(taskId)) {
                        this.selectedTasks = this.selectedTasks.filter(id => id !== taskId);
                    } else {
                        this.selectedTasks.push(taskId);
                    }
                    
                    // Update selectAll state
                    this.selectAll = this.selectedTasks.length > 0;
                }
            }
        }
    </script>
</div>