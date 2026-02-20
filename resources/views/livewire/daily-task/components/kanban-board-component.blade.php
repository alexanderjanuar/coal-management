{{-- resources/views/livewire/daily-task/components/kanban-board-component.blade.php --}}
{{-- Modern Premium Kanban Board Design --}}
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
}" class="kanban-container w-full">

    {{-- Loading Skeleton --}}
    <div x-show="isLoading" x-cloak class="w-full animate-pulse">
        <div class="flex gap-5 p-6">
            @foreach($columns as $status => $config)
            <div class="flex-1 min-w-[420px] bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 rounded-2xl p-5 space-y-3 shadow-lg">
                <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded-lg w-32"></div>
                <div class="space-y-3">
                    @for($i = 0; $i < 3; $i++)
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 space-y-3 shadow">
                        <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                    </div>
                    @endfor
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Main Kanban Board - Responsive --}}
    <div x-show="!isLoading" x-cloak>
        <div class="kanban-board flex flex-col sm:flex-row sm:items-start gap-4 sm:gap-5 p-3 sm:p-4 lg:p-6 overflow-x-auto">
            @foreach($columns as $status => $config)
            {{-- Hide columns not matching status filter --}}
            @if(!empty($currentFilters['status']) && !in_array($status, $currentFilters['status']))
                @continue
            @endif
            @php
                $tasks = $kanbanTasks->get($status, collect());
                $stats = $this->getColumnStats($status);
                $hasMore = $this->hasMoreTasks($status);
                $isCreating = isset($creatingInColumn[$status]) && $creatingInColumn[$status];
                $isLoadingMore = isset($loadingMore[$status]) && $loadingMore[$status];
                
                // Premium UI/UX Pro Max Color System
                $colorScheme = [
                    'pending' => [
                        'bg' => 'bg-gradient-to-br from-gray-50/50 to-gray-100/30 dark:from-gray-800/20 dark:to-gray-900/10',
                        'border' => 'border-gray-200/60 dark:border-gray-700/60',
                        'dot' => 'bg-gradient-to-br from-gray-400 to-gray-500',
                        'dotRing' => 'ring-gray-100 dark:ring-gray-800',
                        'badge' => 'bg-gray-100/80 text-gray-700 dark:bg-gray-800/80 dark:text-gray-300',
                        'headerGlow' => 'shadow-sm shadow-gray-500/5'
                    ],
                    'in_progress' => [
                        'bg' => 'bg-gradient-to-br from-blue-50/50 to-indigo-50/30 dark:from-blue-900/10 dark:to-indigo-900/5',
                        'border' => 'border-blue-200/60 dark:border-blue-800/60',
                        'dot' => 'bg-gradient-to-br from-blue-500 to-indigo-600',
                        'dotRing' => 'ring-blue-100 dark:ring-blue-900/30',
                        'badge' => 'bg-blue-100/80 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
                        'headerGlow' => 'shadow-sm shadow-blue-500/10'
                    ],
                    'completed' => [
                        'bg' => 'bg-gradient-to-br from-emerald-50/50 to-green-50/30 dark:from-emerald-900/10 dark:to-green-900/5',
                        'border' => 'border-emerald-200/60 dark:border-emerald-800/60',
                        'dot' => 'bg-gradient-to-br from-emerald-500 to-green-600',
                        'dotRing' => 'ring-emerald-100 dark:ring-emerald-900/30',
                        'badge' => 'bg-emerald-100/80 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300',
                        'headerGlow' => 'shadow-sm shadow-emerald-500/10'
                    ],
                ];
                $colors = $colorScheme[$status];
            @endphp

            {{-- Column + Add Button Wrapper - Responsive --}}
            <div class="flex flex-col gap-3 w-full sm:w-[420px]">
                {{-- Minimal Status Header --}}
                <div wire:key="column-{{ $status }}-{{ json_encode($currentFilters) }}"
                     data-status="{{ $status }}">
                    <div class="flex items-center justify-between mb-3 sm:mb-4 px-1 sm:px-2">
                        <div class="flex items-center gap-2 sm:gap-3">
                            {{-- Status Dot --}}
                            <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full {{ $colors['dot'] }} shadow-sm"></div>
                            
                            {{-- Title - Responsive --}}
                            <h3 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wide">
                                {{ $config['title'] }}
                            </h3>
                            
                            {{-- Count Badge - Responsive --}}
                            <span class="inline-flex items-center justify-center min-w-[24px] sm:min-w-[28px] h-5 sm:h-6 px-1.5 sm:px-2
                                        rounded-lg text-[10px] sm:text-xs font-bold tabular-nums
                                        {{ $colors['badge'] }} shadow-sm">
                                {{ $stats['total'] }}
                            </span>
                        </div>

                        {{-- WIP Limit Indicator - Responsive --}}
                        @if($stats['limit'])
                        <div class="flex items-center gap-1.5 sm:gap-2 px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg text-[10px] sm:text-xs font-bold
                                    {{ $stats['isAtLimit']
                                        ? 'bg-gradient-to-r from-red-500 to-red-600 text-white shadow-md shadow-red-500/20'
                                        : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm' }}">
                            <svg class="w-3 sm:w-3.5 h-3 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span class="tabular-nums">{{ $stats['total'] }}/{{ $stats['limit'] }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Task Cards Area - Responsive --}}
                <div class="column-content custom-scrollbar space-y-2 sm:space-y-3 px-3 py-2
                            {{ $tasks->count() === 0 ? 'min-h-[200px] sm:min-h-[240px]' : '' }}
                            {{ $tasks->count() > 3 ? 'max-h-[calc(100vh-300px)] sm:max-h-[calc(100vh-400px)] overflow-y-auto' : '' }}"
                     style="scrollbar-width: thin;"
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
                    {{-- Premium Empty State --}}
                    <div class="flex flex-col items-center justify-center py-20 text-gray-400 dark:text-gray-500
                                animate-in fade-in duration-300">
                        <div class="relative mb-6">
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br 
                                        from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700
                                        flex items-center justify-center shadow-sm
                                        transition-all duration-300 hover:scale-110 hover:rotate-3">
                                <x-dynamic-component
                                    :component="'heroicon-o-' . str_replace('heroicon-o-', '', $config['icon'])"
                                    class="w-10 h-10 text-gray-400 dark:text-gray-600" />
                            </div>
                            <div class="absolute -top-1 -right-1 w-6 h-6 rounded-full 
                                        bg-gradient-to-br from-blue-500 to-indigo-600 
                                        flex items-center justify-center
                                        shadow-lg shadow-blue-500/30
                                        animate-pulse">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-sm font-semibold mb-1.5 text-gray-500 dark:text-gray-400">Belum ada tugas</p>
                        <p class="text-xs text-gray-400 dark:text-gray-600">Seret tugas ke sini atau buat yang baru</p>
                    </div>
                    @endif
                    @endforelse

                    {{-- Load More Trigger --}}
                    @if($hasMore)
                    <div class="load-more-trigger" data-load-status="{{ $status }}">
                        @if($isLoadingMore)
                        <div class="flex items-center justify-center py-4">
                            <svg class="w-5 h-5 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        @else
                        <button wire:click="loadMoreTasksInColumn('{{ $status }}')"
                                class="w-full py-3 text-xs font-semibold text-gray-500 dark:text-gray-500
                                       hover:text-gray-700 dark:hover:text-gray-300 
                                       hover:bg-white/50 dark:hover:bg-gray-900/50
                                       rounded-xl transition-all duration-200 hover:shadow-md">
                            Muat {{ $stats['total'] - $stats['loaded'] }} lagi →
                        </button>
                        @endif
                    </div>
                    @endif


                </div>

                {{-- Task Creation Form — Outside scrollable area --}}
                <div x-data="{ show: false }"
                     x-init="$watch('$wire.creatingInColumn.{{ $status }}', value => show = !!value)"
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                     class="relative bg-white dark:bg-gray-800 
                            rounded-lg sm:rounded-xl lg:rounded-2xl 
                            border-2 border-gray-300 dark:border-gray-600
                            shadow-md sm:shadow-lg origin-top mx-1">
                    
                    <div class="p-3 sm:p-4 lg:p-5">
                        {{-- Filament Form --}}
                        <form wire:submit="saveKanbanTask('{{ $status }}')" class="space-y-3">
                            {{ $this->form }}
                            
                            {{-- Action Buttons --}}
                            <div class="grid grid-cols-2 gap-2 pt-2">
                                <button type="submit"
                                        class="w-full px-2.5 sm:px-4 py-1.5 sm:py-2
                                               text-xs font-bold
                                               bg-blue-600 hover:bg-blue-700
                                               text-white rounded-lg
                                               transition-all duration-200
                                               shadow-sm hover:shadow-md
                                               active:scale-95
                                               flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Simpan</span>
                                </button>
                                
                                <button type="button"
                                        wire:click="cancelKanbanTask('{{ $status }}')"
                                        class="w-full px-2.5 sm:px-4 py-1.5 sm:py-2
                                               text-xs font-semibold
                                               bg-gray-100 dark:bg-gray-700
                                               hover:bg-gray-200 dark:hover:bg-gray-600
                                               text-gray-700 dark:text-gray-300
                                               rounded-lg border-2 border-gray-200 dark:border-gray-600
                                               transition-all duration-200
                                               active:scale-95
                                               flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <span>Batal</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            {{-- Add Task Button - Below Column - Responsive with Animation --}}
            @if(!$stats['isAtLimit'])
            <div x-data="{ showButton: false }"
                 x-init="$watch('$wire.creatingInColumn.{{ $status }}', value => showButton = !!value)"
                 x-show="!showButton"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
            <button wire:click="startCreatingKanbanTask('{{ $status }}')"
                    class="w-full rounded-xl sm:rounded-2xl p-3 sm:p-4
                           border-2 border-dashed 
                           transition-all duration-200 cursor-pointer
                           flex items-center justify-center gap-2
                           group
                           @if($status === 'pending')
                               bg-gray-50 dark:bg-gray-900/50 border-gray-300 dark:border-gray-700
                               text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100
                               hover:bg-gray-100 dark:hover:bg-gray-800 hover:border-gray-400
                           @elseif($status === 'in_progress')
                               bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-700
                               text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-100
                               hover:bg-blue-100 dark:hover:bg-blue-800/30 hover:border-blue-400
                           @elseif($status === 'completed')
                               bg-emerald-50 dark:bg-emerald-900/20 border-emerald-300 dark:border-emerald-700
                               text-emerald-600 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-100
                               hover:bg-emerald-100 dark:hover:bg-emerald-800/30 hover:border-emerald-400
                           @endif">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 transition-transform duration-200 group-hover:scale-110 group-hover:rotate-90" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span class="text-xs sm:text-sm font-medium">Tambah Tugas Baru</span>
            </button>
            </div>
            @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Sortable.js --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    {{-- Enhanced Inline Styles --}}
    <style>
        /* Container */
        .kanban-container { width: 100%; max-width: 100%; }

        /* Alpine cloak */
        [x-cloak] { display: none !important; }

        /* Responsive Layout */
        @media (max-width: 1023px) {
            .kanban-board { flex-direction: column; }
            .kanban-column { width: 100%; }
        }

        /* Ultra-slim Scrollbar — applies to ALL scrollable elements in kanban */
        .kanban-container,
        .kanban-container *,
        .custom-scrollbar {
            scrollbar-width: thin !important;
            scrollbar-color: rgba(148, 163, 184, 0.3) transparent !important;
        }

        .kanban-container ::-webkit-scrollbar,
        .custom-scrollbar::-webkit-scrollbar {
            width: 2px !important;
            height: 2px !important;
        }

        .kanban-container ::-webkit-scrollbar-track,
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent !important;
        }

        .kanban-container ::-webkit-scrollbar-thumb,
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.3) !important;
            border-radius: 0 !important;
        }

        .kanban-container ::-webkit-scrollbar-thumb:hover,
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.5) !important;
        }

        /* Dark Mode Scrollbar */
        .dark .kanban-container,
        .dark .kanban-container *,
        .dark .custom-scrollbar {
            scrollbar-color: rgba(71, 85, 105, 0.3) transparent !important;
        }

        .dark .kanban-container ::-webkit-scrollbar-thumb,
        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(71, 85, 105, 0.3) !important;
        }

        .dark .kanban-container ::-webkit-scrollbar-thumb:hover,
        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.5) !important;
        }

        /* Enhanced Drag States */
        .kanban-ghost {
            opacity: 0.5;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 3px dashed #3b82f6;
            transform: rotate(3deg);
        }
        
        .dark .kanban-ghost {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            border-color: #60a5fa;
        }

        .kanban-chosen {
            cursor: grabbing !important;
            transform: rotate(2deg) scale(1.05);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25), 0 0 0 2px rgba(59, 130, 246, 0.5);
            z-index: 9999;
        }
        
        .dark .kanban-chosen {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6), 0 0 0 2px rgba(96, 165, 250, 0.5);
        }

        .kanban-drag { opacity: 0; }

        body.dragging { 
            cursor: grabbing !important; 
            user-select: none; 
        }
        
        body.dragging * { 
            cursor: grabbing !important; 
        }

        /* Card Interaction */
        .kanban-card {
            transition: box-shadow 0.2s ease;
            cursor: grab;
        }
        
        .kanban-card:hover {
            /* Shadow-only hover — no transform, no overflow clipping */
        }
        
        .kanban-card:active { 
            cursor: grabbing; 
        }

        /* Smooth Animations */
        @keyframes fade-in {
            from { 
                opacity: 0; 
                transform: translateY(-10px) scale(0.95); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
        }
        
        .animate-in { 
            animation: fade-in 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        }

        /* Column Hover Effects */
        .kanban-column {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .kanban-column:hover {
            transform: translateY(-2px);
        }
    </style>

</div>
