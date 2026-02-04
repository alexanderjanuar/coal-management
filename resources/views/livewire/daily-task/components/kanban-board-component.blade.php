{{-- resources/views/livewire/daily-task/components/kanban-board-component.blade.php --}}
{{-- Professional Project Management Kanban Board --}}
<div x-data="{
    sortables: {},
    isDragging: false,
    draggedElement: null,
    isLoading: @entangle('isInitialLoading'),
    observedColumns: {},

    init() {
        this.initializeSortable();
        this.setupIntersectionObservers();

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
                animation: 150,
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
}" class="kanban-container w-full">

    {{-- Loading Skeleton --}}
    <div x-show="isLoading" x-cloak class="w-full animate-pulse">
        <div class="flex gap-4 p-4">
            @foreach($columns as $status => $config)
            <div class="flex-1 min-w-[300px] bg-gray-100 dark:bg-gray-900 rounded-xl p-4 space-y-3">
                <div class="h-6 bg-gray-200 dark:bg-gray-800 rounded w-24"></div>
                <div class="space-y-2">
                    @for($i = 0; $i < 3; $i++)
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-3 space-y-2">
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                    </div>
                    @endfor
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Main Content --}}
    <div x-show="!isLoading" x-cloak>

        {{-- Board Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
            {{-- Quick Stats --}}
            <div class="flex items-center gap-2 flex-wrap">
                @foreach($columns as $status => $config)
                @php $stats = $this->getColumnStats($status); @endphp
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium
                            bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300
                            border border-gray-200 dark:border-gray-700">
                    <span class="w-2 h-2 rounded-full bg-{{ $config['color'] }}-500"></span>
                    <span class="hidden sm:inline">{{ $config['title'] }}</span>
                    <span class="font-bold text-gray-900 dark:text-gray-100">{{ $stats['total'] }}</span>
                </div>
                @endforeach
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2">
                <button wire:click="toggleCompletedTasks"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium transition-all
                        {{ $showCompletedTasks
                            ? 'bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900'
                            : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                    <x-dynamic-component
                        :component="$showCompletedTasks ? 'heroicon-o-eye' : 'heroicon-o-eye-slash'"
                        class="w-3.5 h-3.5" />
                    <span>{{ $showCompletedTasks ? 'Hide' : 'Show' }} Done</span>
                </button>

                <button wire:click="$refresh"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium
                               bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300
                               hover:bg-gray-200 dark:hover:bg-gray-700 transition-all">
                    <x-heroicon-o-arrow-path class="w-3.5 h-3.5" wire:loading.class="animate-spin" />
                    <span class="hidden sm:inline">Refresh</span>
                </button>
            </div>
        </div>

        {{-- Kanban Columns --}}
        <div class="kanban-board flex flex-col lg:flex-row lg:items-start gap-4 pb-4">
            @foreach($columns as $status => $config)
            @php
                $tasks = $kanbanTasks->get($status, collect());
                $stats = $this->getColumnStats($status);
                $hasMore = $this->hasMoreTasks($status);
                $isCreating = isset($creatingInColumn[$status]) && $creatingInColumn[$status];
                $isLoadingMore = isset($loadingMore[$status]) && $loadingMore[$status];
            @endphp

            {{-- Column --}}
            <div wire:key="column-{{ $status }}-{{ json_encode($currentFilters) }}"
                 class="kanban-column flex-1 min-w-[300px] lg:min-w-[320px] flex flex-col rounded-xl
                        bg-gray-50/80 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800
                        overflow-hidden"
                 data-status="{{ $status }}">

                {{-- Column Header --}}
                <div class="column-header flex-shrink-0 px-4 py-3 border-b border-gray-200 dark:border-gray-800
                            bg-white dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            {{-- Status Dot --}}
                            <div class="w-2.5 h-2.5 rounded-full bg-{{ $config['color'] }}-500
                                        ring-4 ring-{{ $config['color'] }}-100 dark:ring-{{ $config['color'] }}-900/30"></div>
                            {{-- Title --}}
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wide">
                                {{ $config['title'] }}
                            </h3>
                            {{-- Count Badge --}}
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-500 tabular-nums">
                                {{ $stats['total'] }}
                            </span>
                        </div>

                        {{-- WIP Limit --}}
                        @if($stats['limit'])
                        <div class="flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold
                                    {{ $stats['isAtLimit']
                                        ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                        : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-500' }}">
                            <span>{{ $stats['total'] }}/{{ $stats['limit'] }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Alert Stats --}}
                    @if($stats['urgent'] > 0 || $stats['overdue'] > 0)
                    <div class="flex items-center gap-2 mt-2">
                        @if($stats['urgent'] > 0)
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium
                                    bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                            <x-heroicon-s-fire class="w-3 h-3" />
                            {{ $stats['urgent'] }} urgent
                        </span>
                        @endif
                        @if($stats['overdue'] > 0)
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium
                                    bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">
                            {{ $stats['overdue'] }} overdue
                        </span>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Column Content (Scrollable) --}}
                <div class="column-content flex-1 overflow-y-auto p-3 space-y-2
                            {{ $tasks->count() === 0 ? 'min-h-[200px]' : '' }}
                            {{ $tasks->count() > 3 ? 'max-h-[600px]' : '' }}"
                     data-status="{{ $status }}"
                     x-ref="column_{{ $status }}">

                    {{-- Task Cards --}}
                    @forelse($tasks as $task)
                    <div class="kanban-card"
                         data-task-id="{{ $task->id }}"
                         data-status="{{ $status }}"
                         wire:key="kanban-task-{{ $task->id }}-{{ $status }}">
                        @include('components.daily-task.partials.kanban-task-card', ['task' => $task])
                    </div>
                    @empty
                    @if(!$isCreating)
                    <div class="flex flex-col items-center justify-center py-12 text-gray-400 dark:text-gray-600">
                        <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-3">
                            <x-dynamic-component
                                :component="'heroicon-o-' . str_replace('heroicon-o-', '', $config['icon'])"
                                class="w-6 h-6" />
                        </div>
                        <p class="text-xs font-medium">No tasks</p>
                        <p class="text-[10px] mt-1">Drag tasks here or add new</p>
                    </div>
                    @endif
                    @endforelse

                    {{-- Load More --}}
                    @if($hasMore)
                    <div class="load-more-trigger" data-load-status="{{ $status }}">
                        @if($isLoadingMore)
                        <div class="flex items-center justify-center py-3">
                            <svg class="w-4 h-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        @else
                        <button wire:click="loadMoreTasksInColumn('{{ $status }}')"
                                class="w-full py-2 text-xs font-medium text-gray-500 dark:text-gray-500
                                       hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800
                                       rounded-lg transition-colors">
                            Load more ({{ $stats['total'] - $stats['loaded'] }})
                        </button>
                        @endif
                    </div>
                    @endif

                    {{-- Inline Task Creation --}}
                    @if($isCreating)
                    <div class="bg-white dark:bg-gray-900 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700 p-3 space-y-2">
                        <input type="text"
                               wire:model.live="newTaskData.{{ $status }}.title"
                               placeholder="Task title..."
                               class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg
                                      bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100
                                      placeholder-gray-400 dark:placeholder-gray-600
                                      focus:ring-2 focus:ring-gray-300 dark:focus:ring-gray-600 focus:border-transparent"
                               autofocus
                               @keydown.enter="$wire.saveKanbanTask('{{ $status }}')"
                               @keydown.escape="$wire.cancelKanbanTask('{{ $status }}')">

                        <textarea wire:model.live="newTaskData.{{ $status }}.description"
                                  placeholder="Description (optional)..."
                                  rows="2"
                                  class="w-full px-3 py-2 text-xs border border-gray-200 dark:border-gray-700 rounded-lg
                                         bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100
                                         placeholder-gray-400 dark:placeholder-gray-600
                                         focus:ring-2 focus:ring-gray-300 dark:focus:ring-gray-600 focus:border-transparent
                                         resize-none"></textarea>

                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <select wire:model.live="newTaskData.{{ $status }}.priority"
                                        class="text-xs px-2 py-1 border border-gray-200 dark:border-gray-700 rounded-lg
                                               bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                                    <option value="low">Low</option>
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>

                                <input type="date"
                                       wire:model.live="newTaskData.{{ $status }}.task_date"
                                       class="text-xs px-2 py-1 border border-gray-200 dark:border-gray-700 rounded-lg
                                              bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                            </div>

                            <div class="flex items-center gap-1">
                                <button wire:click="saveKanbanTask('{{ $status }}')"
                                        class="px-3 py-1 text-xs font-medium bg-gray-900 dark:bg-gray-100
                                               text-white dark:text-gray-900 rounded-lg hover:opacity-90 transition-opacity">
                                    Save
                                </button>
                                <button wire:click="cancelKanbanTask('{{ $status }}')"
                                        class="px-3 py-1 text-xs font-medium bg-gray-200 dark:bg-gray-800
                                               text-gray-700 dark:text-gray-300 rounded-lg hover:opacity-90 transition-opacity">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Column Footer: Add Task --}}
                @if(!$isCreating && !$stats['isAtLimit'])
                <div class="flex-shrink-0 p-3 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                    <button wire:click="startCreatingKanbanTask('{{ $status }}')"
                            class="w-full flex items-center justify-center gap-2 py-2 text-xs font-medium
                                   text-gray-500 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300
                                   hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition-colors">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        <span>Add Task</span>
                    </button>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Sortable.js --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    @push('styles')
    <style>
        /* Container */
        .kanban-container { width: 100%; max-width: 100%; }

        /* Alpine cloak */
        [x-cloak] { display: none !important; }

        /* Column Layout */
        @media (max-width: 1023px) {
            .kanban-board { flex-direction: column; }
            .kanban-column { width: 100%; }
        }

        @media (min-width: 1024px) {
            .kanban-column { flex: 1; min-width: 280px; }
        }

        /* Scrollbar */
        .column-content::-webkit-scrollbar { width: 4px; }
        .column-content::-webkit-scrollbar-track { background: transparent; }
        .column-content::-webkit-scrollbar-thumb { background: rgba(156,163,175,0.3); border-radius: 2px; }
        .column-content::-webkit-scrollbar-thumb:hover { background: rgba(156,163,175,0.5); }

        /* Drag Ghost */
        .kanban-ghost {
            opacity: 0.4;
            background: #f3f4f6;
            border: 2px dashed #9ca3af;
        }
        .dark .kanban-ghost {
            background: #1f2937;
            border-color: #4b5563;
        }

        .kanban-chosen {
            cursor: grabbing !important;
            transform: rotate(2deg) scale(1.02);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            z-index: 9999;
        }
        .dark .kanban-chosen {
            box-shadow: 0 10px 25px rgba(0,0,0,0.4);
        }

        .kanban-drag { opacity: 0; }

        body.dragging { cursor: grabbing !important; user-select: none; }
        body.dragging * { cursor: grabbing !important; }

        /* Card Hover */
        .kanban-card {
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            cursor: grab;
        }
        .kanban-card:hover {
            transform: translateY(-1px);
        }
        .kanban-card:active { cursor: grabbing; }

        /* Animation */
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-in { animation: fade-in 0.2s ease-out; }
    </style>
    @endpush
</div>
