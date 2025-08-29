{{-- resources/views/livewire/daily-task/daily-task-list-component.blade.php - Fully Responsive with Dark Mode --}}
<div class="space-y-4 lg:space-y-6" x-data="taskManager()">

    {{-- Enhanced Filters Section - Mobile Optimized & Collapsible --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg lg:rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm"
        x-data="{ filtersCollapsed: true }">
        <div class="p-4 lg:p-6 border-b border-gray-100 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-6 h-6 lg:w-8 lg:h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-funnel class="w-3 h-3 lg:w-4 lg:h-4 text-gray-600 dark:text-gray-300" />
                    </div>
                    <div>
                        <h3 class="text-base lg:text-lg font-semibold text-gray-900 dark:text-gray-100">Filters &
                            Controls</h3>
                        <p class="text-xs lg:text-sm text-gray-500 dark:text-gray-400 hidden sm:block">Customize your
                            task view</p>
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
                        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <x-heroicon-o-chevron-down
                            class="w-4 h-4 text-gray-500 dark:text-gray-400 transition-transform duration-200"
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
            <div
                class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-funnel class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                        <span class="text-sm font-medium text-blue-900 dark:text-blue-200">Active Filters</span>
                        <span
                            class="text-xs bg-blue-200 dark:bg-blue-800 text-blue-800 dark:text-blue-200 px-2 py-1 rounded-full">{{
                            count($activeFilters) }}</span>
                    </div>
                    <button wire:click="resetFilters"
                        class="text-xs text-blue-700 dark:text-blue-300 hover:text-blue-900 dark:hover:text-blue-100 font-medium hover:underline flex items-center gap-1">
                        <x-heroicon-o-x-mark class="w-3 h-3" />
                        Clear All
                    </button>
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach($activeFilters as $filter)
                    <div
                        class="inline-flex items-center gap-2 px-3 py-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm text-sm">
                        <x-dynamic-component :component="$filter['icon']" class="w-3 h-3 {{ match($filter['color']) {
                                    'primary' => 'text-primary-600 dark:text-primary-400',
                                    'success' => 'text-green-600 dark:text-green-400',
                                    'warning' => 'text-orange-600 dark:text-orange-400',
                                    'danger' => 'text-red-600 dark:text-red-400',
                                    'info' => 'text-blue-600 dark:text-blue-400',
                                    default => 'text-gray-600 dark:text-gray-400'
                                } }}" />
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $filter['label'] }}:</span>
                        <span class="text-gray-600 dark:text-gray-400">
                            @if(isset($filter['count']) && $filter['count'] > 1)
                            {{ Str::limit($filter['value'], 20) }}
                            <span
                                class="text-xs bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-1.5 py-0.5 rounded-full ml-1">{{
                                $filter['count'] }}</span>
                            @else
                            {{ Str::limit($filter['value'], 30) }}
                            @endif
                        </span>
                        <button wire:click="removeFilter('{{ $filter['type'] }}')"
                            class="ml-1 text-gray-400 dark:text-gray-500 hover:text-red-500 dark:hover:text-red-400 transition-colors"
                            title="Remove filter">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{ $this->filterForm }}

            <div wire:loading.delay class="flex items-center justify-center py-4">
                <div class="flex items-center text-primary-600 dark:text-primary-400">
                    <x-heroicon-o-arrow-path class="w-5 h-5 animate-spin mr-2" />
                    <span class="text-sm font-medium">Loading tasks...</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Enhanced Task List Content - Responsive Views --}}
    <div class="space-y-4 lg:space-y-6">
        @if($viewMode === 'list')
        {{-- Desktop Table View --}}
        <div
            class="hidden lg:block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            {{-- Enhanced Table Header --}}
            <div
                class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 border-b border-gray-200 dark:border-gray-600">
                <div class="px-6 py-4">
                    <div
                        class="grid grid-cols-12 gap-4 text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        <div class="col-span-1 flex items-center">
                            <input type="checkbox" x-model="selectAll" @change="toggleSelectAll"
                                class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 focus:ring-offset-0 dark:bg-gray-700">
                        </div>
                        <div class="col-span-4 flex items-center gap-2">
                            <button wire:click="sortBy('title')"
                                class="flex items-center gap-2 hover:text-primary-600 dark:hover:text-primary-400 transition-colors group">
                                <x-heroicon-o-document-text class="w-4 h-4" />
                                <span>Task</span>
                                @if($sortBy === 'title')
                                @if($sortDirection === 'asc')
                                <x-heroicon-s-chevron-up class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                                @else
                                <x-heroicon-s-chevron-down class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                                @endif
                                @else
                                <x-heroicon-o-chevron-up-down
                                    class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
                                @endif
                            </button>
                        </div>
                        <div class="col-span-2 flex items-center gap-2">
                            <button wire:click="sortBy('status')"
                                class="flex items-center gap-2 hover:text-primary-600 dark:hover:text-primary-400 transition-colors group">
                                <x-heroicon-o-flag class="w-4 h-4" />
                                <span>Status</span>
                                @if($sortBy === 'status')
                                @if($sortDirection === 'asc')
                                <x-heroicon-s-chevron-up class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                                @else
                                <x-heroicon-s-chevron-down class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                                @endif
                                @else
                                <x-heroicon-o-chevron-up-down
                                    class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
                                @endif
                            </button>
                        </div>
                        <div class="col-span-1 flex items-center gap-2">
                            <button wire:click="sortBy('priority')"
                                class="flex items-center gap-2 hover:text-primary-600 dark:hover:text-primary-400 transition-colors group">
                                <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                                <span>Priority</span>
                                @if($sortBy === 'priority')
                                @if($sortDirection === 'asc')
                                <x-heroicon-s-chevron-up class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                                @else
                                <x-heroicon-s-chevron-down class="w-4 h-4 text-primary-600 dark:text-primary-400" />
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
                                class="flex items-center gap-2 hover:text-primary-600 dark:hover:text-primary-400 transition-colors group">
                                <x-heroicon-o-calendar-days class="w-4 h-4" />
                                <span>Due Date</span>
                                @if($sortBy === 'task_date')
                                @if($sortDirection === 'asc')
                                <x-heroicon-s-chevron-up class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                                @else
                                <x-heroicon-s-chevron-down class="w-4 h-4 text-primary-600 dark:text-primary-400" />
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
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($paginatedTasks as $index => $task)
                <div class="px-6 py-4 hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent dark:hover:from-primary-900/20 dark:hover:to-transparent transition-all duration-200 group cursor-pointer border-l-4 border-l-transparent hover:border-l-primary-300 dark:hover:border-l-primary-600"
                    x-data="{ expanded: false }"
                    :class="{ 'bg-primary-25 dark:bg-primary-900/30 border-l-primary-500 dark:border-l-primary-400': selectedTasks.includes({{ $task->id }}) }">
                    <livewire:daily-task.daily-task-item :task="$task" :key="'task-'.$task->id . time()" />
                </div>
                @empty
                <div class="py-16 text-center">
                    <div
                        class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                        <x-heroicon-o-clipboard-document-list class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">No tasks found</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-sm mx-auto">
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
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                {{ $paginatedTasks->links() }}
            </div>
            @endif
            @else
            {{-- Desktop Grouped View --}}
            <div class="divide-y divide-gray-200 dark:divide-gray-600">
                @forelse($groupedTasks as $groupName => $tasks)
                {{-- Enhanced Group Header - Fixed Alpine.js --}}
                <div class="bg-gradient-to-r from-gray-25 to-gray-50 dark:from-gray-700 dark:to-gray-600 px-6 py-4 border-l-4 border-l-primary-500 dark:border-l-primary-400"
                    x-data="{ collapsed: false }">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            @switch($groupBy)
                            @case('status')
                            <div class="w-6 h-6 rounded-lg flex items-center justify-center
                                                    {{ match($groupName) {
                                                        'Completed' => 'bg-green-100 dark:bg-green-900/30',
                                                        'In Progress' => 'bg-yellow-100 dark:bg-yellow-900/30',
                                                        'Pending' => 'bg-gray-100 dark:bg-gray-700',
                                                        'Cancelled' => 'bg-red-100 dark:bg-red-900/30',
                                                        default => 'bg-gray-100 dark:bg-gray-700'
                                                    } }}">
                                <div class="w-3 h-3 rounded-full 
                                                        {{ match($groupName) {
                                                            'Completed' => 'bg-green-500 dark:bg-green-400',
                                                            'In Progress' => 'bg-yellow-500 dark:bg-yellow-400',
                                                            'Pending' => 'bg-gray-400 dark:bg-gray-500',
                                                            'Cancelled' => 'bg-red-500 dark:bg-red-400',
                                                            default => 'bg-gray-400 dark:bg-gray-500'
                                                        } }}">
                                </div>
                            </div>
                            @break
                            @case('priority')
                            <div
                                class="w-6 h-6 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                @php
                                $priorityIcon = match($groupName) {
                                'Urgent' => 'heroicon-s-exclamation-triangle',
                                'High' => 'heroicon-o-exclamation-triangle',
                                'Normal' => 'heroicon-o-minus',
                                'Low' => 'heroicon-o-arrow-down',
                                default => 'heroicon-o-minus'
                                };
                                @endphp
                                <x-dynamic-component :component="$priorityIcon"
                                    class="w-4 h-4 text-orange-600 dark:text-orange-400" />
                            </div>
                            @break
                            @case('project')
                            <div
                                class="w-6 h-6 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-folder class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            @break
                            @case('assignee')
                            <div
                                class="w-6 h-6 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-user class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            @break
                            @case('date')
                            <div
                                class="w-6 h-6 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-calendar-days class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                            </div>
                            @break
                            @endswitch

                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $groupName }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $tasks->count() }} {{
                                    $tasks->count() === 1 ? 'task' : 'tasks' }}</p>
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
                                class="p-1.5 hover:bg-white dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <x-heroicon-o-chevron-down
                                    class="w-4 h-4 text-gray-400 dark:text-gray-500 transition-transform"
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
                            class="px-6 py-1 hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent dark:hover:from-primary-900/20 dark:hover:to-transparent transition-all duration-200 group border-l-4 border-l-transparent hover:border-l-primary-200 dark:hover:border-l-primary-600 -mx-6 mb-2 last:mb-0 border-b-2 border-gray-100 dark:border-gray-700 last:border-b-0">
                            <livewire:daily-task.daily-task-item :task="$task" :key="'task-'.$task->id . time()" />
                        </div>
                        @endforeach

                        {{-- Inline New Task Row --}}
                        @php $groupKey = $this->getGroupKey($groupBy, $groupName); @endphp
                        @if($this->isCreatingTask($groupBy, $groupName))
                        <div
                            class="px-6 py-4 -mx-6 mb-2 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-blue-25 dark:from-blue-900/20 dark:to-blue-900/10 border-l-4 border-l-blue-400 dark:border-l-blue-500">
                            <div class="grid grid-cols-12 gap-4 items-center">
                                {{-- Checkbox placeholder --}}
                                <div class="col-span-1">
                                    <div
                                        class="w-5 h-5 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center">
                                        <x-heroicon-o-plus class="w-3 h-3 text-blue-600 dark:text-blue-300" />
                                    </div>
                                </div>

                                {{-- Task Title Input --}}
                                <div class="col-span-4">
                                    <div class="space-y-2">
                                        <input type="text" wire:model.live="newTaskData.{{ $groupKey }}.title"
                                            placeholder="Masukkan judul task..."
                                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400"
                                            autofocus @keydown.enter="$wire.saveNewTask('{{ $groupKey }}')"
                                            @keydown.escape="$wire.cancelNewTask('{{ $groupKey }}')" />
                                    </div>
                                </div>

                                {{-- Status - Auto filled based on group --}}
                                <div class="col-span-2">
                                    <div class="text-xs font-medium text-gray-600 dark:text-gray-400">
                                        @if($groupBy === 'status')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-700">
                                            <div class="w-2 h-2 rounded-full bg-blue-500 dark:bg-blue-400"></div>
                                            {{ $groupName }}
                                        </span>
                                        @else
                                        <span class="text-gray-500 dark:text-gray-400">Auto-detected</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Priority - Auto filled based on group --}}
                                <div class="col-span-1">
                                    @if($groupBy === 'priority')
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-700">
                                        <x-heroicon-o-exclamation-triangle class="w-3 h-3" />
                                        {{ $groupName }}
                                    </span>
                                    @else
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Normal</span>
                                    @endif
                                </div>

                                {{-- Assignee placeholder --}}
                                <div class="col-span-2">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Akan diassign nanti</span>
                                </div>

                                {{-- Project - Auto filled based on group --}}
                                <div class="col-span-1">
                                    @if($groupBy === 'project' && $groupName !== 'No Project')
                                    <span
                                        class="text-xs font-medium text-indigo-800 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900/30 px-2 py-1 rounded-md border border-indigo-200 dark:border-indigo-700">
                                        {{ Str::limit($groupName, 8) }}
                                    </span>
                                    @else
                                    <span class="text-xs text-gray-500 dark:text-gray-400">-</span>
                                    @endif
                                </div>

                                {{-- Action Buttons --}}
                                <div class="col-span-1">
                                    <div class="flex items-center gap-1">
                                        <button wire:click="saveNewTask('{{ $groupKey }}')" wire:loading.attr="disabled"
                                            class="p-2 bg-green-100 dark:bg-green-900/30 hover:bg-green-200 dark:hover:bg-green-900/50 text-green-700 dark:text-green-400 rounded-lg transition-all duration-200 hover:scale-110 shadow-sm"
                                            title="Simpan task (Enter)">
                                            <x-heroicon-o-check class="w-4 h-4" />
                                        </button>
                                        <button wire:click="cancelNewTask('{{ $groupKey }}')"
                                            class="p-2 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 rounded-lg transition-all duration-200 hover:scale-110 shadow-sm"
                                            title="Batal (Escape)">
                                            <x-heroicon-o-x-mark class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Add New Task Button --}}
                        @if(!$this->isCreatingTask($groupBy, $groupName))
                        <div
                            class="px-6 py-3 -mx-6 border-t border-gray-100 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-gray-25 dark:from-gray-700/50 dark:to-gray-600/50">
                            <button wire:click="startCreatingTask('{{ $groupBy }}', '{{ $groupName }}')"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-blue-25 dark:hover:from-blue-900/20 dark:hover:to-blue-900/10 hover:border-blue-300 dark:hover:border-blue-600 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200 group">
                                <div
                                    class="w-5 h-5 bg-gray-200 dark:bg-gray-600 group-hover:bg-blue-200 dark:group-hover:bg-blue-800 rounded-full flex items-center justify-center transition-colors duration-200">
                                    <x-heroicon-o-plus class="w-3 h-3 transition-colors duration-200" />
                                </div>
                                <span>Tambah Task Baru untuk {{ $groupName }}</span>
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="py-16 text-center">
                    <div
                        class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                        <x-heroicon-o-clipboard-document-list class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">No tasks found</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Try adjusting your filters or create a new task</p>
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
                class="bg-white dark:bg-gray-800 rounded-lg border-2 border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-primary-300 dark:hover:border-primary-600 transition-all duration-200">
                <livewire:daily-task.daily-task-item :task="$task" :key="'mobile-task-'.$task->id . time()" />
            </div>
            @empty
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border-2 border-gray-200 dark:border-gray-700 p-8 text-center">
                <div
                    class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-o-clipboard-document-list class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No tasks found</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4 text-sm">
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
            <div class="bg-white dark:bg-gray-800 rounded-lg border-2 border-gray-200 dark:border-gray-700 p-4">
                {{ $paginatedTasks->links() }}
            </div>
            @endif
            @else
            {{-- Mobile Grouped View --}}
            @forelse($groupedTasks as $groupName => $tasks)
            <div class="bg-white dark:bg-gray-800 rounded-lg border-2 border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden hover:border-primary-300 dark:hover:border-primary-600 transition-all duration-200"
                x-data="{ collapsed: false }">
                {{-- Mobile Group Header --}}
                <div
                    class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 p-4 border-b border-gray-200 dark:border-gray-600">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            @switch($groupBy)
                            @case('status')
                            <div class="w-5 h-5 rounded-lg flex items-center justify-center
                                                    {{ match($groupName) {
                                                        'Completed' => 'bg-green-100 dark:bg-green-900/30',
                                                        'In Progress' => 'bg-yellow-100 dark:bg-yellow-900/30',
                                                        'Pending' => 'bg-gray-100 dark:bg-gray-700',
                                                        'Cancelled' => 'bg-red-100 dark:bg-red-900/30',
                                                        default => 'bg-gray-100 dark:bg-gray-700'
                                                    } }}">
                                <div class="w-2.5 h-2.5 rounded-full 
                                                        {{ match($groupName) {
                                                            'Completed' => 'bg-green-500 dark:bg-green-400',
                                                            'In Progress' => 'bg-yellow-500 dark:bg-yellow-400',
                                                            'Pending' => 'bg-gray-400 dark:bg-gray-500',
                                                            'Cancelled' => 'bg-red-500 dark:bg-red-400',
                                                            default => 'bg-gray-400 dark:bg-gray-500'
                                                        } }}">
                                </div>
                            </div>
                            @break
                            @case('priority')
                            <div
                                class="w-5 h-5 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                @php
                                $priorityIcon = match($groupName) {
                                'Urgent' => 'heroicon-s-exclamation-triangle',
                                'High' => 'heroicon-o-exclamation-triangle',
                                'Normal' => 'heroicon-o-minus',
                                'Low' => 'heroicon-o-arrow-down',
                                default => 'heroicon-o-minus'
                                };
                                @endphp
                                <x-dynamic-component :component="$priorityIcon"
                                    class="w-3 h-3 text-orange-600 dark:text-orange-400" />
                            </div>
                            @break
                            @case('project')
                            <div
                                class="w-5 h-5 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-folder class="w-3 h-3 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            @break
                            @case('assignee')
                            <div
                                class="w-5 h-5 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-user class="w-3 h-3 text-blue-600 dark:text-blue-400" />
                            </div>
                            @break
                            @case('date')
                            <div
                                class="w-5 h-5 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-calendar-days class="w-3 h-3 text-purple-600 dark:text-purple-400" />
                            </div>
                            @break
                            @endswitch

                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $groupName }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $tasks->count() }} {{
                                    $tasks->count() === 1 ? 'task' : 'tasks' }}</p>
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
                                class="p-1 hover:bg-white dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <x-heroicon-o-chevron-down
                                    class="w-4 h-4 text-gray-400 dark:text-gray-500 transition-transform"
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
                    x-transition:leave-end="opacity-0 transform -translate-y-2"
                    class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($tasks as $task)
                    <div
                        class="p-0 border-l-4 border-l-transparent hover:border-l-primary-300 dark:hover:border-l-primary-600 hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent dark:hover:from-primary-900/20 dark:hover:to-transparent transition-all duration-200">
                        <livewire:daily-task.daily-task-item :task="$task"
                            :key="'mobile-grouped-task-'.$task->id . time()" />
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border-2 border-gray-200 dark:border-gray-700 p-8 text-center">
                <div
                    class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-o-clipboard-document-list class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No tasks found</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4 text-sm">Try adjusting your filters or create a new task
                </p>
                <x-filament::button wire:click="resetFilters" color="gray" outlined size="sm">
                    Clear Filters
                </x-filament::button>
            </div>
            @endforelse
            @endif
        </div>
        @else
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