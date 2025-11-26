{{-- resources/views/livewire/daily-task/components/kanban-board-component.blade.php --}}
<div x-data="{
    sortables: {},
    isDragging: false,
    draggedElement: null,
    isLoading: @entangle('isInitialLoading'),
    observedColumns: {},
    
    init() {
        this.initializeSortable();
        this.setupIntersectionObservers();
        
        // Mark loading as complete after initial render
        setTimeout(() => {
            if (this.isLoading) {
                $wire.completeInitialLoad();
            }
        }, 500);
        
        Livewire.on('taskStatusChanged', () => {
            setTimeout(() => this.initializeSortable(), 100);
        });

        Livewire.on('task-created', () => {
            setTimeout(() => this.initializeSortable(), 100);
        });

        Livewire.on('revertTaskMove', (event) => {
            this.revertMove(event.taskId);
        });
    },

    setupIntersectionObservers() {
        // Observer for lazy loading
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const status = entry.target.dataset.loadStatus;
                    if (status && !this.observedColumns[status]) {
                        this.observedColumns[status] = true;
                        $wire.call('loadMoreTasksInColumn', status);
                    }
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '50px'
        });

        // Observe load more triggers
        document.querySelectorAll('.load-more-trigger').forEach(trigger => {
            observer.observe(trigger);
        });
    },

    initializeSortable() {
        Object.values(this.sortables).forEach(sortable => sortable.destroy());
        this.sortables = {};

        document.querySelectorAll('.column-content').forEach(column => {
            const status = column.dataset.status;
            
            this.sortables[status] = new Sortable(column, {
                group: 'kanban',
                animation: 200,
                delay: 50,
                delayOnTouchOnly: true,
                touchStartThreshold: 5,
                ghostClass: 'kanban-ghost',
                chosenClass: 'kanban-chosen',
                dragClass: 'kanban-drag',
                handle: '.kanban-card',
                
                onStart: (evt) => {
                    this.isDragging = true;
                    this.draggedElement = evt.item;
                    document.body.classList.add('dragging');
                },

                onEnd: (evt) => {
                    this.isDragging = false;
                    document.body.classList.remove('dragging');
                    
                    const taskId = parseInt(evt.item.dataset.taskId);
                    const newStatus = evt.to.dataset.status;
                    const newPosition = evt.newIndex;

                    if (evt.from !== evt.to) {
                        $wire.call('handleTaskMoved', taskId, newStatus, newPosition);
                    }
                },

                onMove: (evt) => {
                    const toColumn = evt.to;
                    const status = toColumn.dataset.status;
                    const limit = this.getColumnLimit(status);
                    
                    if (limit) {
                        const currentCount = toColumn.querySelectorAll('.kanban-card').length;
                        if (currentCount >= limit && evt.from !== evt.to) {
                            return false;
                        }
                    }
                    
                    return true;
                }
            });
        });
    },

    getColumnLimit(status) {
        const limits = {
            'in_progress': {{ $columns['in_progress']['limit'] ?? 'null' }}
        };
        return limits[status] || null;
    },

    revertMove(taskId) {
        const card = document.querySelector(`[data-task-id='${taskId}']`);
        if (card) {
            setTimeout(() => {
                $wire.call('$refresh');
            }, 100);
        }
    }
}" class="kanban-container w-full h-full">

    {{-- Skeleton Loading State --}}
    <div x-show="isLoading" x-cloak class="w-full">
        {{-- Board Header Skeleton --}}
        <div class="p-4 mb-4 animate-pulse">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    @for($i = 0; $i < 3; $i++)
                    <div class="h-8 w-24 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    @endfor
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-10 w-32 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    <div class="h-10 w-24 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                </div>
            </div>
        </div>

        {{-- Columns Skeleton --}}
        <div class="kanban-board flex flex-col lg:flex-row lg:items-start gap-4 pb-4">
            @foreach($columns as $status => $config)
            <div class="w-full lg:flex-1 lg:min-w-[330px] xl:min-w-[400px] 
                        bg-{{ $config['color'] }}-50/30 dark:bg-{{ $config['color'] }}-950/20
                        border border-{{ $config['color'] }}-200 dark:border-{{ $config['color'] }}-800/50
                        rounded-xl overflow-hidden">
                {{-- Column Header Skeleton --}}
                <div class="px-4 py-3 border-b-2 border-{{ $config['color'] }}-300 dark:border-{{ $config['color'] }}-700/70">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 bg-{{ $config['color'] }}-200 dark:bg-{{ $config['color'] }}-800 rounded-lg animate-pulse"></div>
                            <div class="h-5 w-32 bg-{{ $config['color'] }}-200 dark:bg-{{ $config['color'] }}-800 rounded animate-pulse"></div>
                            <div class="h-6 w-10 bg-{{ $config['color'] }}-200 dark:bg-{{ $config['color'] }}-800 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                </div>

                {{-- Task Cards Skeleton --}}
                <div class="p-3 space-y-2.5">
                    @for($i = 0; $i < 3; $i++)
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 space-y-3 animate-pulse">
                        {{-- Title skeleton --}}
                        <div class="flex items-start gap-2">
                            <div class="w-1 h-16 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                            </div>
                        </div>
                        
                        {{-- Meta skeleton --}}
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/4"></div>
                            </div>
                        </div>

                        {{-- Footer skeleton --}}
                        <div class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex -space-x-2">
                                @for($j = 0; $j < 2; $j++)
                                <div class="w-6 h-6 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                                @endfor
                            </div>
                            <div class="flex gap-2">
                                <div class="w-8 h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Actual Content (Hidden during loading) --}}
    <div x-show="!isLoading" x-cloak>
        {{-- Enhanced Board Header - Responsive --}}
        <div class="p-4 mb-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    {{-- Board Stats - Enhanced with Icons --}}
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($columns as $status => $config)
                        @php $stats = $this->getColumnStats($status); @endphp
                        <div class="group relative px-3 py-1.5 rounded-lg text-xs font-semibold
                                    transition-all duration-200 hover:scale-105 cursor-pointer
                                    bg-{{ $config['color'] }}-100 dark:bg-{{ $config['color'] }}-900/30
                                    text-{{ $config['color'] }}-700 dark:text-{{ $config['color'] }}-300
                                    border-2 border-{{ $config['color'] }}-200 dark:border-{{ $config['color'] }}-700/50
                                    hover:border-{{ $config['color'] }}-400 dark:hover:border-{{ $config['color'] }}-500
                                    shadow-sm hover:shadow-md">
                            <div class="flex items-center gap-1.5">
                                <x-dynamic-component
                                    :component="'heroicon-o-' . str_replace('heroicon-o-', '', $config['icon'])"
                                    class="w-3.5 h-3.5" />
                                <span class="hidden sm:inline">{{ $config['title'] }}</span>
                                <span class="sm:hidden">{{ substr($config['title'], 0, 3) }}</span>
                                <span class="px-1.5 py-0.5 bg-{{ $config['color'] }}-200 dark:bg-{{ $config['color'] }}-800 
                                             rounded-full text-xs font-bold">
                                    {{ $stats['total'] }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Board Actions - Enhanced Buttons --}}
                <div class="flex items-center gap-2">
                    {{-- Toggle Completed --}}
                    <button wire:click="toggleCompletedTasks" 
                        class="group flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium
                               transition-all duration-200 hover:scale-105 active:scale-95
                               {{ $showCompletedTasks 
                                  ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/30 hover:bg-blue-600' 
                                  : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}
                               border border-transparent hover:border-gray-300 dark:hover:border-gray-500">
                        <x-dynamic-component 
                            :component="$showCompletedTasks ? 'heroicon-o-eye' : 'heroicon-o-eye-slash'"
                            class="w-4 h-4 group-hover:rotate-12 transition-transform duration-200" />
                        <span class="hidden sm:inline">{{ $showCompletedTasks ? 'Hide' : 'Show' }} Completed</span>
                        <span class="sm:hidden">{{ $showCompletedTasks ? 'Hide' : 'Show' }}</span>
                    </button>

                    {{-- Refresh --}}
                    <button wire:click="$refresh" 
                        class="group flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium
                               bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300
                               hover:bg-gray-200 dark:hover:bg-gray-600
                               transition-all duration-200 hover:scale-105 active:scale-95
                               border border-transparent hover:border-gray-300 dark:hover:border-gray-500">
                        <x-heroicon-o-arrow-path class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" />
                        <span class="hidden sm:inline">Refresh</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Kanban Columns - Responsive Layout with Dynamic Heights --}}
        <div class="kanban-board-wrapper w-full">
            <div class="kanban-board flex flex-col lg:flex-row lg:items-start gap-3 lg:gap-4 pb-4"
                style="scroll-behavior: smooth;">

                @foreach($columns as $status => $config)
                @php
                $tasks = $kanbanTasks->get($status, collect());
                $stats = $this->getColumnStats($status);
                $hasMore = $this->hasMoreTasks($status);
                $isCreating = isset($creatingInColumn[$status]) && $creatingInColumn[$status];
                $isLoadingMore = isset($loadingMore[$status]) && $loadingMore[$status];
                @endphp

                {{-- Kanban Column - Responsive with Dynamic Height --}}
                <div wire:key="column-{{ $status }}-{{ json_encode($currentFilters) }}" 
                    class="kanban-column 
                        w-full lg:flex-1 lg:min-w-[330px] xl:min-w-[400px]
                        flex flex-col
                        bg-{{ $config['color'] }}-50/30 dark:bg-{{ $config['color'] }}-950/20
                        border border-{{ $config['color'] }}-200 dark:border-{{ $config['color'] }}-800/50
                        rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200
                        {{ $tasks->count() > 5 ? 'h-full' : 'h-auto' }}" 
                    data-status="{{ $status }}">

                    {{-- Enhanced Column Header --}}
                    <div class="column-header flex-shrink-0 px-4 py-3
                                bg-gradient-to-br from-white to-{{ $config['color'] }}-50/50 
                                dark:from-gray-800 dark:to-{{ $config['color'] }}-950/30 
                                backdrop-blur-sm
                                border-b-2 border-{{ $config['color'] }}-300 dark:border-{{ $config['color'] }}-700/70
                                shadow-sm">

                        <div class="flex items-center justify-between">
                            {{-- Title & Count --}}
                            <div class="flex items-center gap-2.5">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-{{ $config['color'] }}-400 dark:bg-{{ $config['color'] }}-600 
                                                rounded-lg blur-sm opacity-50"></div>
                                    <div class="relative p-2 rounded-lg 
                                                bg-gradient-to-br from-{{ $config['color'] }}-400 to-{{ $config['color'] }}-600 
                                                shadow-lg">
                                        <x-dynamic-component
                                            :component="'heroicon-o-' . str_replace('heroicon-o-', '', $config['icon'])"
                                            class="w-4 h-4 text-white" />
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold uppercase tracking-wider
                                            text-{{ $config['color'] }}-900 dark:text-{{ $config['color'] }}-100">
                                        {{ $config['title'] }}
                                    </h3>
                                    <div class="px-2.5 py-1 rounded-full text-xs font-bold
                                                bg-{{ $config['color'] }}-200 dark:bg-{{ $config['color'] }}-800/80
                                                text-{{ $config['color'] }}-800 dark:text-{{ $config['color'] }}-200
                                                border-2 border-{{ $config['color'] }}-300 dark:border-{{ $config['color'] }}-700
                                                shadow-sm">
                                        {{ $stats['loaded'] }}/{{ $stats['total'] }}
                                    </div>
                                </div>
                            </div>

                            {{-- WIP Limit Indicator --}}
                            @if($stats['limit'])
                            <div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-bold
                                        transition-all duration-200
                                        {{ $stats['isAtLimit'] 
                                            ? 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 border-2 border-red-400 dark:border-red-600 animate-pulse shadow-lg shadow-red-500/30' 
                                            : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border-2 border-gray-300 dark:border-gray-600' }}">
                                @if($stats['isAtLimit'])
                                <x-heroicon-s-exclamation-triangle class="w-4 h-4" />
                                @else
                                <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                                @endif
                                <span>{{ $stats['total'] }}/{{ $stats['limit'] }}</span>
                            </div>
                            @endif
                        </div>

                        {{-- Enhanced Stats Bar --}}
                        @if($stats['urgent'] > 0 || $stats['overdue'] > 0)
                        <div class="flex items-center gap-3 mt-3 pt-3 
                                    border-t border-{{ $config['color'] }}-200 dark:border-{{ $config['color'] }}-800/50">
                            @if($stats['urgent'] > 0)
                            <div class="flex items-center gap-1.5 px-2 py-1 rounded-md
                                        bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800
                                        text-xs text-red-700 dark:text-red-300 font-semibold
                                        shadow-sm">
                                <x-heroicon-s-fire class="w-4 h-4 animate-pulse" />
                                <span>{{ $stats['urgent'] }} urgent</span>
                            </div>
                            @endif
                            @if($stats['overdue'] > 0)
                            <div class="flex items-center gap-1.5 px-2 py-1 rounded-md
                                        bg-orange-50 dark:bg-orange-900/30 border border-orange-200 dark:border-orange-800
                                        text-xs text-orange-700 dark:text-orange-300 font-semibold
                                        shadow-sm">
                                <x-heroicon-o-clock class="w-4 h-4" />
                                <span>{{ $stats['overdue'] }} overdue</span>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Column Content (Scrollable) - Dynamic Height Based on Content --}}
                    <div class="column-content 
                                overflow-y-auto p-2.5 lg:p-3 space-y-2 lg:space-y-2.5 
                                custom-scrollbar
                                {{ $tasks->count() === 0 ? 'min-h-[200px]' : '' }}
                                {{ $tasks->count() <= 3 ? 'max-h-fit' : 'max-h-[500px] lg:max-h-[600px]' }}"
                        data-status="{{ $status }}" 
                        x-ref="column_{{ $status }}">

                        {{-- Tasks --}}
                        @forelse($tasks as $task)
                        <div class="kanban-card" 
                            data-task-id="{{ $task->id }}" 
                            data-status="{{ $status }}"
                            wire:key="kanban-task-{{ $task->id }}-{{ $status }}">
                            @include('components.daily-task.partials.kanban-task-card', ['task' => $task])
                        </div>
                        @empty
                        @if(!$isCreating)
                        <div class="text-center py-8 lg:py-16 text-gray-400 dark:text-gray-600">
                            <div class="w-12 h-12 lg:w-16 lg:h-16 mx-auto mb-2 lg:mb-3 rounded-full 
                                        bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                <x-dynamic-component
                                    :component="'heroicon-o-' . str_replace('heroicon-o-', '', $config['icon'])"
                                    class="w-6 h-6 lg:w-8 lg:h-8 opacity-40" />
                            </div>
                            <p class="text-xs lg:text-sm font-medium">No tasks yet</p>
                            <p class="text-xs mt-1 hidden lg:block">Add a new task to get started</p>
                        </div>
                        @endif
                        @endforelse

                        {{-- Load More Indicator with Skeleton --}}
                        @if($hasMore)
                        <div class="load-more-trigger" data-load-status="{{ $status }}">
                            @if($isLoadingMore)
                            {{-- Loading Skeleton --}}
                            <div class="space-y-2.5 animate-pulse">
                                @for($i = 0; $i < 2; $i++)
                                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 space-y-3">
                                    <div class="flex items-start gap-2">
                                        <div class="w-1 h-16 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                                        <div class="flex-1 space-y-2">
                                            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                                            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div>
                                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
                                    </div>
                                </div>
                                @endfor
                            </div>
                            @else
                            {{-- Load More Button --}}
                            <button 
                                wire:click="loadMoreTasksInColumn('{{ $status }}')"
                                class="w-full flex items-center justify-center gap-2 px-3 py-2.5 
                                       text-xs font-medium text-{{ $config['color'] }}-700 dark:text-{{ $config['color'] }}-300
                                       bg-{{ $config['color'] }}-50 dark:bg-{{ $config['color'] }}-950/30
                                       border border-{{ $config['color'] }}-200 dark:border-{{ $config['color'] }}-700
                                       rounded-lg hover:bg-{{ $config['color'] }}-100 dark:hover:bg-{{ $config['color'] }}-900/40
                                       transition-all duration-200 group">
                                <x-heroicon-o-chevron-down class="w-4 h-4 group-hover:translate-y-0.5 transition-transform" />
                                <span>Load More ({{ $stats['total'] - $stats['loaded'] }} remaining)</span>
                            </button>
                            @endif
                        </div>
                        @endif

                        {{-- Inline Task Creation - Responsive --}}
                        @if($isCreating)
                        <div class="bg-white dark:bg-gray-800 rounded-lg border-2 border-dashed 
                                    border-blue-400 dark:border-blue-600 p-3 lg:p-4 space-y-2 lg:space-y-3
                                    shadow-lg dark:shadow-gray-900/50 animate-in fade-in duration-200">

                            {{-- Title Input --}}
                            <input type="text" 
                                wire:model.live="newTaskData.{{ $status }}.title"
                                placeholder="Enter task title..." 
                                class="w-full px-3 py-2 lg:py-2.5 text-xs lg:text-sm 
                                          border border-gray-300 dark:border-gray-600
                                          rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                          dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 
                                          transition-all duration-200" 
                                autofocus
                                @keydown.enter="$wire.saveKanbanTask('{{ $status }}')"
                                @keydown.escape="$wire.cancelKanbanTask('{{ $status }}')" />

                            {{-- Description Input --}}
                            <textarea 
                                wire:model.live="newTaskData.{{ $status }}.description"
                                placeholder="Add description (optional)..." 
                                rows="2"
                                class="w-full px-3 py-2 text-xs lg:text-sm 
                                          border border-gray-300 dark:border-gray-600
                                          rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                          dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 
                                          transition-all duration-200 resize-none"></textarea>

                            {{-- Quick Actions - Responsive --}}
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    {{-- Priority Selector --}}
                                    <select 
                                        wire:model.live="newTaskData.{{ $status }}.priority" 
                                        class="text-xs px-2 lg:px-3 py-1.5 border border-gray-300 dark:border-gray-600
                                               rounded-lg dark:bg-gray-700 dark:text-gray-300
                                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                               transition-all duration-200 flex-1 sm:flex-none">
                                        <option value="low">Low</option>
                                        <option value="normal">Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>

                                    {{-- Due Date --}}
                                    <input type="date" 
                                        wire:model.live="newTaskData.{{ $status }}.task_date" 
                                        class="text-xs px-2 lg:px-3 py-1.5 border border-gray-300 dark:border-gray-600
                                              rounded-lg dark:bg-gray-700 dark:text-gray-300
                                              focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                              transition-all duration-200 flex-1 sm:flex-none" />
                                </div>

                                {{-- Action Buttons --}}
                                <div class="flex items-center gap-1.5">
                                    <button 
                                        wire:click="saveKanbanTask('{{ $status }}')" 
                                        class="flex-1 sm:flex-none px-3 py-1.5 bg-green-500 hover:bg-green-600 
                                               text-white rounded-lg transition-all duration-200 
                                               text-xs font-medium hover:shadow-md active:scale-95 
                                               flex items-center justify-center gap-1.5" 
                                        title="Save (Enter)">
                                        <x-heroicon-o-check class="w-4 h-4" />
                                        <span>Save</span>
                                    </button>
                                    <button 
                                        wire:click="cancelKanbanTask('{{ $status }}')" 
                                        class="flex-1 sm:flex-none px-3 py-1.5 bg-gray-500 hover:bg-gray-600 
                                               text-white rounded-lg transition-all duration-200 
                                               text-xs font-medium hover:shadow-md active:scale-95 
                                               flex items-center justify-center gap-1.5" 
                                        title="Cancel (Esc)">
                                        <x-heroicon-o-x-mark class="w-4 h-4" />
                                        <span>Cancel</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Column Footer (Add Task Button) - Responsive --}}
                    @if(!$isCreating && !$stats['isAtLimit'])
                    <div class="column-footer flex-shrink-0 p-2.5 lg:p-3 
                                border-t border-{{ $config['color'] }}-200 
                                dark:border-{{ $config['color'] }}-800/50 
                                bg-white/50 dark:bg-gray-800/30">
                        <button 
                            wire:click="startCreatingKanbanTask('{{ $status }}')" 
                            class="w-full flex items-center justify-center gap-2 
                                   px-2.5 py-2 lg:px-3 lg:py-2.5 text-xs lg:text-sm font-medium
                                   text-{{ $config['color'] }}-700 dark:text-{{ $config['color'] }}-300
                                   bg-white dark:bg-gray-800 border-2 border-dashed 
                                   border-{{ $config['color'] }}-300 dark:border-{{ $config['color'] }}-700
                                   rounded-lg hover:bg-{{ $config['color'] }}-50 dark:hover:bg-{{ $config['color'] }}-950/30
                                   hover:border-{{ $config['color'] }}-400 dark:hover:border-{{ $config['color'] }}-600
                                   hover:shadow-sm active:scale-[0.98]
                                   transition-all duration-200 group">
                            <x-heroicon-o-plus class="w-4 h-4 group-hover:scale-110 transition-transform duration-200" />
                            <span>Add Task</span>
                        </button>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Load Sortable.js from CDN - Required for drag & drop --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    @push('styles')
    <style>
        /* Kanban Container - Full Width & Responsive */
        .kanban-container {
            width: 100%;
            max-width: 100%;
        }

        .kanban-board-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .kanban-board {
            min-width: 100%;
            -webkit-overflow-scrolling: touch;
        }

        /* Alpine x-cloak */
        [x-cloak] {
            display: none !important;
        }

        /* Mobile: Stack columns vertically */
        @media (max-width: 1023px) {
            .kanban-board {
                flex-direction: column;
            }

            .kanban-column {
                width: 100%;
            }

            /* Limit height on mobile only when column has many tasks */
            .kanban-column.h-full {
                max-height: 500px;
            }
        }

        /* Desktop: Horizontal layout with dynamic heights */
        @media (min-width: 1024px) {
            .kanban-board {
                flex-direction: row;
                align-items: flex-start;
            }

            .kanban-column {
                flex: 1;
                min-width: 280px;
            }

            /* Only apply full height when column has many tasks */
            .kanban-column.h-full {
                max-height: calc(100vh - 300px);
            }

            /* Auto height for columns with few tasks */
            .kanban-column.h-auto {
                height: auto;
            }
        }

        /* Custom Scrollbar Design - Clearer and Simpler */
        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: rgba(156, 163, 175, 0.8) rgba(243, 244, 246, 0.5);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        @media (min-width: 1024px) {
            .custom-scrollbar::-webkit-scrollbar {
                width: 12px;
                height: 12px;
            }
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(243, 244, 246, 0.8);
            border-radius: 6px;
            margin: 4px 0;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.9);
            border-radius: 6px;
            border: 2px solid rgba(243, 244, 246, 0.8);
            transition: all 0.2s ease;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(107, 114, 128, 1);
            border: 2px solid rgba(243, 244, 246, 0.9);
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:active {
            background: rgba(75, 85, 99, 1);
        }

        /* Dark mode scrollbar */
        .dark .custom-scrollbar {
            scrollbar-color: rgba(107, 114, 128, 0.8) rgba(31, 41, 55, 0.5);
        }

        .dark .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(31, 41, 55, 0.8);
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(107, 114, 128, 0.9);
            border-color: rgba(31, 41, 55, 0.8);
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(156, 163, 175, 1);
            border-color: rgba(31, 41, 55, 0.9);
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb:active {
            background: rgba(209, 213, 219, 1);
        }

        /* Horizontal Scrollbar for Board - Clearer Design */
        .kanban-board-wrapper::-webkit-scrollbar {
            height: 12px;
        }

        .kanban-board-wrapper::-webkit-scrollbar-track {
            background: rgba(243, 244, 246, 0.8);
            border-radius: 6px;
            margin: 0 8px;
        }

        .kanban-board-wrapper::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.9);
            border-radius: 6px;
            border: 2px solid rgba(243, 244, 246, 0.8);
            transition: background 0.2s ease;
        }

        .kanban-board-wrapper::-webkit-scrollbar-thumb:hover {
            background: rgba(107, 114, 128, 1);
        }

        .kanban-board-wrapper::-webkit-scrollbar-thumb:active {
            background: rgba(75, 85, 99, 1);
        }

        /* Dark mode horizontal scrollbar */
        .dark .kanban-board-wrapper::-webkit-scrollbar-track {
            background: rgba(31, 41, 55, 0.8);
        }

        .dark .kanban-board-wrapper::-webkit-scrollbar-thumb {
            background: rgba(107, 114, 128, 0.9);
            border-color: rgba(31, 41, 55, 0.8);
        }

        .dark .kanban-board-wrapper::-webkit-scrollbar-thumb:hover {
            background: rgba(156, 163, 175, 1);
        }

        .dark .kanban-board-wrapper::-webkit-scrollbar-thumb:active {
            background: rgba(209, 213, 219, 1);
        }


        body.dragging {
            cursor: grabbing !important;
            user-select: none;
        }

        body.dragging * {
            cursor: grabbing !important;
        }

        /* Card Hover Effect */
        .kanban-card {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: grab;
        }

        .kanban-card:hover {
            transform: translateY(-2px);
        }

        .kanban-card:active {
            cursor: grabbing;
        }

        /* Smooth Animations */
        .kanban-column {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @media (min-width: 1024px) {
            .kanban-column:hover {
                transform: translateY(-1px);
            }
        }

        /* Animation keyframes */
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fade-in 0.2s ease-out;
        }

        /* Mobile touch improvements */
        @media (max-width: 1023px) {
            .kanban-card {
                touch-action: none;
            }
        }

        /* Loading skeleton pulse animation */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
    @endpush
</div>