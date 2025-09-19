{{-- Enhanced Task List Component dengan Separated Groups --}}
<div class="space-y-4 lg:space-y-6" x-data="taskManager()">

    {{-- Filter Component - Separate component --}}
    <livewire:daily-task.form.daily-task-filter-component :initial-filters="$this->currentFilters"
        :total-tasks="$totalTasks" />

    {{-- Enhanced Task List Content - Responsive Views --}}
    <div class="space-y-4 lg:space-y-6">
        @if($viewMode === 'list')
        {{-- Desktop Table View with Horizontal Scroll --}}
        <div class="hidden lg:block">
            @if($groupBy === 'none')
            {{-- Single Table without Groups --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                {{-- Horizontal Scrollable Container --}}
                <div class="overflow-x-auto">
                    <div class="min-w-[1200px]"> {{-- Minimum width to trigger horizontal scroll --}}
                        {{-- Enhanced Table Header --}}
                        <div class="bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-600">
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
                                            <x-heroicon-s-chevron-up
                                                class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                                            @else
                                            <x-heroicon-s-chevron-down
                                                class="w-4 h-4 text-primary-600 dark:text-primary-400" />
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
                                            <x-heroicon-s-chevron-up
                                                class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                                            @else
                                            <x-heroicon-s-chevron-down
                                                class="w-4 h-4 text-primary-600 dark:text-primary-400" />
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
                                            <x-heroicon-s-chevron-up
                                                class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                                            @else
                                            <x-heroicon-s-chevron-down
                                                class="w-4 h-4 text-primary-600 dark:text-primary-400" />
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
                                            <x-heroicon-s-chevron-up
                                                class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                                            @else
                                            <x-heroicon-s-chevron-down
                                                class="w-4 h-4 text-primary-600 dark:text-primary-400" />
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

                        {{-- Table Body --}}
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($paginatedTasks as $index => $task)
                            <div class="px-6 py-4 hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent dark:hover:from-primary-900/20 dark:hover:to-transparent transition-all duration-200 group cursor-pointer border-l-4 border-l-transparent hover:border-l-primary-300 dark:hover:border-l-primary-600"
                                x-data="{ expanded: false }"
                                :class="{ 'bg-primary-25 dark:bg-primary-900/30 border-l-primary-500 dark:border-l-primary-400': selectedTasks.includes({{ $task->id }}) }">
                                <livewire:daily-task.components.daily-task-item :task="$task" :key="'task-'.$task->id . time()" />
                            </div>
                            @empty
                            <div class="py-16 text-center">
                                <div
                                    class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <x-heroicon-o-clipboard-document-list
                                        class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Tidak ada task
                                    ditemukan</h3>
                                <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-sm mx-auto">
                                    Coba sesuaikan filter Anda untuk melihat lebih banyak task
                                </p>
                                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                    <x-filament::button color="primary" icon="heroicon-o-plus">
                                        Buat Task
                                    </x-filament::button>
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                @if($paginatedTasks && $paginatedTasks->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                    {{ $paginatedTasks->links() }}
                </div>
                @endif
            </div>
            @else
            {{-- Separated Group Cards --}}
            <div class="space-y-6">
                @forelse($groupedTasks as $groupName => $tasks)
                {{-- Individual Group Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border-2 border-gray-200 dark:border-gray-700 overflow-hidden"
                    x-data="{ collapsed: false }"
                    style="background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(248,250,252,0.95) 100%);"
                    class="dark:bg-gradient-to-br dark:from-gray-800 dark:to-gray-900">

                    {{-- Enhanced Group Header --}}
                    <div
                        class="relative bg-white dark:bg-gray-800 px-6 py-6 border-b border-gray-200 dark:border-gray-600">
                        {{-- Header Content --}}
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                {{-- Badge with enhanced styling --}}
                                <div class="relative">
                                    <div class="flex items-center gap-2 mb-2">
                                        @switch($groupBy)
                                        @case('status')
                                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-base font-bold shadow-sm border-2 transition-all duration-200 hover:scale-105
                                            {{ match($groupName) {
                                                'Completed' => 'bg-green-50 text-green-800 border-green-200 dark:bg-green-900/40 dark:text-green-300 dark:border-green-700',
                                                'In Progress' => 'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-900/40 dark:text-yellow-300 dark:border-yellow-700',
                                                'Pending' => 'bg-gray-50 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
                                                'Cancelled' => 'bg-red-50 text-red-800 border-red-200 dark:bg-red-900/40 dark:text-red-300 dark:border-red-700',
                                                default => 'bg-gray-50 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600'
                                            } }}">
                                            <div class="w-3 h-3 rounded-full bg-current"></div>
                                            {{ $groupName }}
                                        </div>
                                        @break
                                        @case('priority')
                                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-base font-bold shadow-sm border-2 transition-all duration-200 hover:scale-105
                                            {{ match($groupName) {
                                                'Urgent' => 'bg-red-50 text-red-800 border-red-200 dark:bg-red-900/40 dark:text-red-300 dark:border-red-700',
                                                'High' => 'bg-orange-50 text-orange-800 border-orange-200 dark:bg-orange-900/40 dark:text-orange-300 dark:border-orange-700',
                                                'Normal' => 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700',
                                                'Low' => 'bg-gray-50 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
                                                default => 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700'
                                            } }}">
                                                                @php
                                            $priorityIcon = match($groupName) {
                                            'Urgent' => 'heroicon-s-exclamation-triangle',
                                            'High' => 'heroicon-o-exclamation-triangle',
                                            'Normal' => 'heroicon-o-minus',
                                            'Low' => 'heroicon-o-arrow-down',
                                            default => 'heroicon-o-minus'
                                            };
                                            @endphp
                                            <x-dynamic-component :component="$priorityIcon" class="w-4 h-4" />
                                            {{ $groupName }}
                                        </div>
                                        @break
                                        @case('project')
                                        <div
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-base font-bold shadow-sm border-2 transition-all duration-200 hover:scale-105 bg-indigo-50 text-indigo-800 border-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:border-indigo-700">
                                            <x-heroicon-o-folder class="w-4 h-4" />
                                            {{ $groupName }}
                                        </div>
                                        @break
                                        @case('assignee')
                                        <div
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-base font-bold shadow-sm border-2 transition-all duration-200 hover:scale-105 bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700">
                                            <x-heroicon-o-user class="w-4 h-4" />
                                            {{ $groupName }}
                                        </div>
                                        @break
                                        @case('date')
                                        <div
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-base font-bold shadow-sm border-2 transition-all duration-200 hover:scale-105 bg-purple-50 text-purple-800 border-purple-200 dark:bg-purple-900/40 dark:text-purple-300 dark:border-purple-700">
                                            <x-heroicon-o-calendar-days class="w-4 h-4" />
                                            {{ $groupName }}
                                        </div>
                                        @break
                                        @default
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $groupName }}
                                        </h3>
                                        @endswitch
                                    </div>
                                </div>
                            </div>

                            {{-- Right Side Controls --}}
                            <div class="flex items-center gap-4">
                                {{-- Enhanced Progress Bar for Status Groups --}}
                                @if($tasks->count() > 0)
                                @php
                                $completedCount = $tasks->where('status', 'completed')->count();
                                $inProgressCount = $tasks->where('status', 'in_progress')->count();
                                $totalCount = $tasks->count();
                                $progressPercentage = $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0;
                                $inProgressPercentage = $totalCount > 0 ? ($inProgressCount / $totalCount) * 100 : 0;
                                @endphp
                                <div class="hidden sm:flex flex-col items-end gap-1">
                                    <div class="text-xs font-medium text-gray-600 dark:text-gray-400">Progress</div>
                                    <div class="flex items-center gap-2">
                                        @switch($groupBy)
                                        @case('status')
                                        {{-- Status-specific progress (completed vs total) --}}
                                        @if($groupName !== 'Completed')
                                        <div class="w-24 h-3 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden shadow-inner">
                                            <div class="h-full bg-green-500 transition-all duration-500 progress-bar shadow-sm" style="width: {{ $progressPercentage }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300 min-w-[3rem] text-right">{{ round($progressPercentage) }}%</span>
                                        @else
                                        <div class="w-24 h-3 bg-green-200 dark:bg-green-800 rounded-full overflow-hidden shadow-inner">
                                            <div class="h-full bg-green-500 transition-all duration-500 progress-bar shadow-sm" style="width: 100%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-green-700 dark:text-green-300 min-w-[3rem] text-right">100%</span>
                                        @endif
                                        @break
                                        
                                        @case('priority')
                                        {{-- Priority-specific progress (completion rate) --}}
                                        <div class="w-24 h-3 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden shadow-inner">
                                            <div class="h-full transition-all duration-500 progress-bar shadow-sm
                                                {{ match($groupName) {
                                                    'Urgent' => 'bg-red-500',
                                                    'High' => 'bg-orange-500', 
                                                    'Normal' => 'bg-blue-500',
                                                    'Low' => 'bg-gray-500',
                                                    default => 'bg-blue-500'
                                                } }}" style="width: {{ $progressPercentage }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300 min-w-[3rem] text-right">{{ round($progressPercentage) }}%</span>
                                        @break
                                        
                                        @case('project')
                                        {{-- Project-specific progress (completion rate) --}}
                                        <div class="w-24 h-3 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden shadow-inner">
                                            <div class="h-full bg-indigo-500 transition-all duration-500 progress-bar shadow-sm" style="width: {{ $progressPercentage }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300 min-w-[3rem] text-right">{{ round($progressPercentage) }}%</span>
                                        @break
                                        
                                        @case('assignee')
                                        {{-- Assignee-specific progress (completion rate) --}}
                                        <div class="w-24 h-3 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden shadow-inner">
                                            <div class="h-full bg-blue-500 transition-all duration-500 progress-bar shadow-sm" style="width: {{ $progressPercentage }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300 min-w-[3rem] text-right">{{ round($progressPercentage) }}%</span>
                                        @break
                                        
                                        @case('date')
                                        {{-- Date-specific progress (completion rate) --}}
                                        <div class="w-24 h-3 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden shadow-inner">
                                            <div class="h-full bg-purple-500 transition-all duration-500 progress-bar shadow-sm" style="width: {{ $progressPercentage }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300 min-w-[3rem] text-right">{{ round($progressPercentage) }}%</span>
                                        @break
                                        
                                        @default
                                        {{-- Default progress bar --}}
                                        <div class="w-24 h-3 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden shadow-inner">
                                            <div class="h-full bg-green-500 transition-all duration-500 progress-bar shadow-sm" style="width: {{ $progressPercentage }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300 min-w-[3rem] text-right">{{ round($progressPercentage) }}%</span>
                                        @endswitch
                                    </div>
                                </div>
                                @endif

                                {{-- Enhanced Task Count Badge --}}
                                <div class="flex flex-col items-center gap-1">
                                    <div class="px-3 py-1.5 rounded-lg font-bold text-lg shadow-sm border-2
                                        {{ match($groupBy) {
                                            'status' => match($groupName) {
                                                'Completed' => 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-700',
                                                'In Progress' => 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-300 dark:border-yellow-700',
                                                'Cancelled' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-700',
                                                default => 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600'
                                            },
                                            'priority' => match($groupName) {
                                                'Urgent' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-700',
                                                'High' => 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-300 dark:border-orange-700',
                                                default => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-700'
                                            },
                                            default => 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600'
                                        } }}">
                                        {{ $tasks->count() }}
                                    </div>
                                </div>

                                {{-- Enhanced Collapse Button --}}
                                <button @click="collapsed = !collapsed"
                                    class="group p-3 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 rounded-xl transition-all duration-200 shadow-md border-2 border-gray-200 dark:border-gray-600 hover:shadow-lg hover:scale-105">
                                    <x-heroicon-o-chevron-down
                                        class="w-5 h-5 text-gray-600 dark:text-gray-400 group-hover:text-gray-800 dark:group-hover:text-gray-200 transition-all duration-200"
                                        x-bind:class="{ 'rotate-180': collapsed }" />
                                </button>
                            </div>
                        </div>

                        {{-- Decorative bottom border --}}
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r 
                            {{ match($groupBy) {
                                'status' => match($groupName) {
                                    'Completed' => 'from-green-400 via-green-500 to-green-400',
                                    'In Progress' => 'from-yellow-400 via-yellow-500 to-yellow-400',
                                    'Cancelled' => 'from-red-400 via-red-500 to-red-400',
                                    default => 'from-gray-300 via-gray-400 to-gray-300'
                                },
                                'priority' => match($groupName) {
                                    'Urgent' => 'from-red-400 via-red-500 to-red-400',
                                    'High' => 'from-orange-400 via-orange-500 to-orange-400',
                                    'Normal' => 'from-blue-400 via-blue-500 to-blue-400',
                                    'Low' => 'from-gray-300 via-gray-400 to-gray-300',
                                    default => 'from-blue-400 via-blue-500 to-blue-400'
                                },
                                'project' => 'from-indigo-400 via-indigo-500 to-indigo-400',
                                'assignee' => 'from-blue-400 via-blue-500 to-blue-400',
                                'date' => 'from-purple-400 via-purple-500 to-purple-400',
                                default => 'from-gray-300 via-gray-400 to-gray-300'
                            } }}"></div>
                    </div>

                    {{-- Group Content with Horizontal Scroll --}}
                    <div x-show="!collapsed" x-transition:enter="transition ease-out duration-500"
                        x-transition:enter-start="opacity-0 transform -translate-y-4"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform -translate-y-4" class="overflow-hidden">

                        {{-- Horizontal Scrollable Table --}}
                        <div class="overflow-x-auto">
                            <div class="min-w-[1200px]"> {{-- Minimum width for horizontal scroll --}}
                                {{-- Group Table Header --}}
                                <div class="bg-gray-50 dark:bg-gray-750 border-b border-gray-200 dark:border-gray-600">
                                    <div class="px-6 py-3">
                                        <div
                                            class="grid grid-cols-12 gap-4 text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="col-span-1"></div>
                                            <div class="col-span-4">Task</div>
                                            <div class="col-span-2">Status</div>
                                            <div class="col-span-1">Priority</div>
                                            <div class="col-span-2">Assignee</div>
                                            <div class="col-span-1">Project</div>
                                            <div class="col-span-1">Due Date</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Group Tasks --}}
                                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach($tasks as $task)
                                    <div
                                        class="px-6 py-4 hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent dark:hover:from-primary-900/20 dark:hover:to-transparent transition-all duration-200 group border-l-4 border-l-transparent hover:border-l-primary-300 dark:hover:border-l-primary-600">
                                        <livewire:daily-task.components.daily-task-item :task="$task"
                                            :key="'group-task-'.$task->id . time()" />
                                    </div>
                                    @endforeach

                                    {{-- Inline New Task Creation --}}
                                    @php $groupKey = $this->getGroupKey($groupBy, $groupName); @endphp
                                    @if($this->isCreatingTask($groupBy, $groupName))
                                    <div
                                        class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-25 dark:from-blue-900/20 dark:to-blue-900/10 border-l-4 border-l-blue-400 dark:border-l-blue-500">
                                        <div class="grid grid-cols-12 gap-4 items-center">
                                            <div class="col-span-1">
                                                <div
                                                    class="w-5 h-5 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center">
                                                    <x-heroicon-o-plus
                                                        class="w-3 h-3 text-blue-600 dark:text-blue-300" />
                                                </div>
                                            </div>
                                            <div class="col-span-4">
                                                <input type="text" wire:model.live="newTaskData.{{ $groupKey }}.title"
                                                    placeholder="Masukkan judul task..."
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400"
                                                    autofocus @keydown.enter="$wire.saveNewTask('{{ $groupKey }}')"
                                                    @keydown.escape="$wire.cancelNewTask('{{ $groupKey }}')" />
                                            </div>
                                            <div class="col-span-7 flex items-center justify-end gap-2">
                                                <button wire:click="saveNewTask('{{ $groupKey }}')"
                                                    wire:loading.attr="disabled"
                                                    class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors duration-200 flex items-center gap-2 shadow-sm">
                                                    <x-heroicon-o-check class="w-4 h-4" />
                                                    <span class="hidden sm:inline">Simpan</span>
                                                </button>
                                                <button wire:click="cancelNewTask('{{ $groupKey }}')"
                                                    class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors duration-200 flex items-center gap-2 shadow-sm">
                                                    <x-heroicon-o-x-mark class="w-4 h-4" />
                                                    <span class="hidden sm:inline">Batal</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Add New Task Section --}}
                        @if(!$this->isCreatingTask($groupBy, $groupName))
                        <div
                            class="px-6 py-4 border-t border-gray-200 dark:border-gray-600 bg-gradient-to-r from-gray-25 to-gray-50 dark:from-gray-700/30 dark:to-gray-600/30">
                            <button wire:click="startCreatingTask('{{ $groupBy }}', '{{ $groupName }}')"
                                class="w-full flex items-center justify-center gap-3 px-6 py-3 text-sm font-medium text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-blue-25 dark:hover:from-blue-900/20 dark:hover:to-blue-900/10 hover:border-blue-300 dark:hover:border-blue-600 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-300 group hover:shadow-md">
                                <div
                                    class="w-6 h-6 bg-gray-200 dark:bg-gray-600 group-hover:bg-blue-200 dark:group-hover:bg-blue-800 rounded-full flex items-center justify-center transition-all duration-300 group-hover:scale-110">
                                    <x-heroicon-o-plus class="w-4 h-4 transition-all duration-300" />
                                </div>
                                <span class="font-semibold">Tambah Task Baru untuk {{ $groupName }}</span>
                                <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" />
                                </div>
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl border-2 border-gray-200 dark:border-gray-700 shadow-lg p-12 text-center">
                    <div
                        class="w-32 h-32 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-inner">
                        <x-heroicon-o-clipboard-document-list class="w-16 h-16 text-gray-400 dark:text-gray-500" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-3">Tidak ada task ditemukan</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-md mx-auto text-lg">Coba sesuaikan filter Anda
                        atau buat task baru untuk memulai</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <x-filament::button color="primary" icon="heroicon-o-plus" size="lg">
                            Buat Task Baru
                        </x-filament::button>
                        <x-filament::button color="gray" icon="heroicon-o-funnel" size="lg">
                            Reset Filter
                        </x-filament::button>
                    </div>
                </div>
                @endforelse
            </div>
            @endif
        </div>

        {{-- Mobile & Tablet List View - Enhanced --}}
        <div class="lg:hidden space-y-4">
            @if($groupBy === 'none')
            {{-- Mobile No Grouping --}}
            @forelse($paginatedTasks as $task)
            <div
                class="bg-white dark:bg-gray-800 rounded-xl border-2 border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-lg hover:border-primary-300 dark:hover:border-primary-600 transition-all duration-300">
                <livewire:daily-task.components.daily-task-item :task="$task" :key="'mobile-task-'.$task->id . time()" />
            </div>
            @empty
            <div
                class="bg-white dark:bg-gray-800 rounded-xl border-2 border-gray-200 dark:border-gray-700 p-8 text-center">
                <div
                    class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-o-clipboard-document-list class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Tidak ada task ditemukan</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4 text-sm">Coba sesuaikan filter Anda untuk melihat lebih
                    banyak task</p>
                <x-filament::button color="primary" icon="heroicon-o-plus" size="sm">
                    Buat Task
                </x-filament::button>
            </div>
            @endforelse

            @if($paginatedTasks && $paginatedTasks->hasPages())
            <div class="bg-white dark:bg-gray-800 rounded-xl border-2 border-gray-200 dark:border-gray-700 p-4">
                {{ $paginatedTasks->links() }}
            </div>
            @endif
            @else
            {{-- Mobile Grouped View - Enhanced Cards --}}
            @forelse($groupedTasks as $groupName => $tasks)
            <div class="bg-white dark:bg-gray-800 rounded-xl border-2 border-gray-200 dark:border-gray-700 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden"
                x-data="{ collapsed: false }">
                {{-- Mobile Group Header --}}
                <div
                    class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 p-4 border-b border-gray-200 dark:border-gray-600">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    @switch($groupBy)
                                    @case('status')
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-semibold
                                        {{ match($groupName) {
                                            'Completed' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                            'In Progress' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                            'Pending' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                            'Cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                        } }}">
                                        <div class="w-2 h-2 rounded-full bg-current"></div>
                                        {{ $groupName }}
                                    </div>
                                    @break
                                    @case('priority')
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-semibold
                                        {{ match($groupName) {
                                            'Urgent' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                            'High' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                            'Normal' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                            'Low' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                            default => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
                                        } }}">
                                        @php
                                        $priorityIcon = match($groupName) {
                                        'Urgent' => 'heroicon-s-exclamation-triangle',
                                        'High' => 'heroicon-o-exclamation-triangle',
                                        'Normal' => 'heroicon-o-minus',
                                        'Low' => 'heroicon-o-arrow-down',
                                        default => 'heroicon-o-minus'
                                        };
                                        @endphp
                                        <x-dynamic-component :component="$priorityIcon" class="w-3 h-3" />
                                        {{ $groupName }}
                                    </div>
                                    @break
                                    @case('project')
                                    <div
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                                        <x-heroicon-o-folder class="w-3 h-3" />
                                        {{ $groupName }}
                                    </div>
                                    @break
                                    @case('assignee')
                                    <div
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                        <x-heroicon-o-user class="w-3 h-3" />
                                        {{ $groupName }}
                                    </div>
                                    @break
                                    @case('date')
                                    <div
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                        <x-heroicon-o-calendar-days class="w-3 h-3" />
                                        {{ $groupName }}
                                    </div>
                                    @break
                                    @default
                                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">{{ $groupName }}
                                    </h3>
                                    @endswitch
                                </div>
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
                                class="p-1.5 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 rounded-lg transition-colors shadow-sm border border-gray-200 dark:border-gray-600">
                                <x-heroicon-o-chevron-down
                                    class="w-4 h-4 text-gray-500 dark:text-gray-400 transition-transform"
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
                        <livewire:daily-task.components.daily-task-item :task="$task"
                            :key="'mobile-grouped-task-'.$task->id . time()" />
                    </div>
                    @endforeach

                    {{-- Mobile Add New Task --}}
                    @php $groupKey = $this->getGroupKey($groupBy, $groupName); @endphp
                    @if(!$this->isCreatingTask($groupBy, $groupName))
                    <div class="p-4 border-t border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50">
                        <button wire:click="startCreatingTask('{{ $groupBy }}', '{{ $groupName }}')"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 dark:hover:border-blue-600 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200">
                            <x-heroicon-o-plus class="w-4 h-4" />
                            <span>Tambah Task untuk {{ $groupName }}</span>
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div
                class="bg-white dark:bg-gray-800 rounded-xl border-2 border-gray-200 dark:border-gray-700 p-8 text-center">
                <div
                    class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-o-clipboard-document-list class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Tidak ada task ditemukan</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4 text-sm">Coba sesuaikan filter Anda atau buat task baru
                </p>
                <x-filament::button color="primary" icon="heroicon-o-plus" size="sm">
                    Buat Task
                </x-filament::button>
            </div>
            @endforelse
            @endif
        </div>
        @else
        {{-- Kanban Board View (placeholder) --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-8 text-center">
            <div
                class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                <x-heroicon-o-squares-2x2 class="w-12 h-12 text-gray-400 dark:text-gray-500" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Kanban Board</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Tampilan Kanban Board akan segera hadir</p>
            <x-filament::button wire:click="$set('currentFilters.view_mode', 'list')" color="primary">
                Kembali ke List View
            </x-filament::button>
        </div>
        @endif
    </div>

    {{-- Task Detail Modal --}}
    <livewire:daily-task.daily-task-detail-modal />

    {{-- Enhanced CSS for better scrolling --}}
    <style>
        /* Custom scrollbar for horizontal scroll */
        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Dark mode scrollbar */
        .dark .overflow-x-auto::-webkit-scrollbar-track {
            background: #374151;
        }

        .dark .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #6b7280;
        }

        .dark .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        /* Smooth transitions for group cards */
        .group-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .group-card:hover {
            transform: translateY(-2px);
        }

        /* Progress bar animation */
        .progress-bar {
            transition: width 0.6s ease-in-out;
        }

        /* Enhanced shadows for depth */
        .shadow-depth {
            box-shadow:
                0 1px 3px 0 rgba(0, 0, 0, 0.1),
                0 1px 2px 0 rgba(0, 0, 0, 0.06),
                0 0 0 1px rgba(0, 0, 0, 0.05);
        }

        .shadow-depth:hover {
            box-shadow:
                0 10px 15px -3px rgba(0, 0, 0, 0.1),
                0 4px 6px -2px rgba(0, 0, 0, 0.05),
                0 0 0 1px rgba(0, 0, 0, 0.05);
        }

        /* Scroll indicators */
        .overflow-x-auto.scroll-left-shadow {
            box-shadow: inset 10px 0 8px -8px rgba(0, 0, 0, 0.15);
        }

        .overflow-x-auto.scroll-right-shadow {
            box-shadow: inset -10px 0 8px -8px rgba(0, 0, 0, 0.15);
        }

        .overflow-x-auto.scroll-left-shadow.scroll-right-shadow {
            box-shadow:
                inset 10px 0 8px -8px rgba(0, 0, 0, 0.15),
                inset -10px 0 8px -8px rgba(0, 0, 0, 0.15);
        }
    </style>

    {{-- JavaScript untuk manajemen task --}}
    <script>
        function taskManager() {
            return {
                selectAll: false,
                selectedTasks: [],
                isMobile: false,
                
                init() {
                    this.checkMobile();
                    window.addEventListener('resize', () => {
                        this.checkMobile();
                    });
                    
                    // Add smooth scrolling behavior
                    this.setupSmoothScrolling();
                },
                
                checkMobile() {
                    this.isMobile = window.innerWidth < 1024;
                },
                
                setupSmoothScrolling() {
                    // Add scroll indicators for horizontal scrollable areas
                    const scrollableElements = document.querySelectorAll('.overflow-x-auto');
                    scrollableElements.forEach(element => {
                        element.addEventListener('scroll', (e) => {
                            this.updateScrollIndicators(e.target);
                        });
                    });
                },
                
                updateScrollIndicators(element) {
                    const isScrolledLeft = element.scrollLeft === 0;
                    const isScrolledRight = element.scrollLeft >= (element.scrollWidth - element.clientWidth);
                    
                    // Add visual indicators if needed
                    element.classList.toggle('scrolled-left', !isScrolledLeft);
                    element.classList.toggle('scrolled-right', !isScrolledRight);
                },
                
                toggleSelectAll() {
                    if (this.selectAll) {
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
                    
                    this.selectAll = this.selectedTasks.length > 0;
                },

                // Method to handle group expansion with smooth animation
                toggleGroup(groupId) {
                    const group = document.getElementById(groupId);
                    if (group) {
                        group.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'nearest' 
                        });
                    }
                }
            }
        }

        // Add intersection observer for scroll indicators
        document.addEventListener('DOMContentLoaded', function() {
            const scrollContainers = document.querySelectorAll('.overflow-x-auto');
            
            scrollContainers.forEach(container => {
                // Add scroll shadows
                container.addEventListener('scroll', function() {
                    const scrollLeft = this.scrollLeft;
                    const scrollWidth = this.scrollWidth;
                    const clientWidth = this.clientWidth;
                    
                    // Update classes based on scroll position
                    this.classList.toggle('scroll-left-shadow', scrollLeft > 0);
                    this.classList.toggle('scroll-right-shadow', scrollLeft < scrollWidth - clientWidth);
                });
                
                // Trigger initial check
                container.dispatchEvent(new Event('scroll'));
            });
        });
    </script>
</div>