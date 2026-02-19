{{-- Daily Task List Component — Refined Cyan Edition --}}
<div class="space-y-5" x-data="taskManager()" style="font-family:'DM Sans',system-ui,sans-serif">

    @push('styles')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link
            href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Syne:wght@600;700;800&display=swap"
            rel="stylesheet">
        <style>
            .dt-list-root {
                font-family: 'DM Sans', system-ui, sans-serif;
            }

            .dt-list-root .syne {
                font-family: 'Syne', sans-serif;
            }

            /* Thin scrollbar */
            .dt-scroll::-webkit-scrollbar {
                height: 4px;
                width: 4px;
            }

            .dt-scroll::-webkit-scrollbar-track {
                background: transparent;
            }

            .dt-scroll::-webkit-scrollbar-thumb {
                background: rgba(0, 0, 0, .15);
                border-radius: 4px;
            }

            .dark .dt-scroll::-webkit-scrollbar-thumb {
                background: rgba(255, 255, 255, .15);
            }

            /* Kanban drag states */
            .kanban-ghost {
                opacity: .4;
                background: #cffafe;
                border: 2px dashed #06b6d4;
            }

            .dark .kanban-ghost {
                background: #164e63;
                border-color: #22d3ee;
            }

            .kanban-chosen {
                cursor: grabbing;
                transform: rotate(1.5deg);
                box-shadow: 0 12px 32px rgba(0, 0, 0, .25);
            }

            .kanban-drag {
                opacity: 0;
            }

            body.dragging,
            body.dragging * {
                cursor: grabbing !important;
            }

            .kanban-card {
                transition: transform .18s ease, box-shadow .18s ease;
                cursor: grab;
            }

            .kanban-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 18px rgba(0, 0, 0, .12);
            }

            .dark .kanban-card:hover {
                box-shadow: 0 6px 18px rgba(0, 0, 0, .45);
            }

            .kanban-card:active {
                cursor: grabbing;
            }

            .column-content {
                scrollbar-width: thin;
            }

            /* Priority indicators */
            .priority-urgent {
                border-left: 3px solid #ef4444;
            }

            .priority-high {
                border-left: 3px solid #06b6d4;
            }

            .priority-normal {
                border-left: 3px solid #3b82f6;
            }

            .priority-low {
                border-left: 3px solid #9ca3af;
            }
        </style>
    @endpush

    {{-- Filter Component --}}
    <livewire:daily-task.form.daily-task-filter-component :initial-filters="$this->currentFilters"
        :total-tasks="$totalTasks" />

    {{-- Main Content --}}
    <div class="space-y-4 dt-list-root" wire:key="view-content-{{ $viewMode }}-{{ $kanbanMountKey }}">

        {{-- ╔══════════════════════╗ --}}
        {{-- ║ LIST VIEW ║ --}}
        {{-- ╚══════════════════════╝ --}}
        @if($viewMode === 'list')

            {{-- ── Desktop Table (lg+) ── --}}
            <div class="hidden lg:block">
                @if($groupBy === 'none')

                    {{-- Flat card container --}}
                    <div
                        class="bg-white dark:bg-[#111110] rounded-2xl border border-black/[.07] dark:border-white/[.07] shadow-sm overflow-hidden">

                        {{-- Horizontal scroll wrapper --}}
                        <div class="overflow-x-auto dt-scroll">
                            <div class="min-w-[1100px]">

                                {{-- TABLE HEADER --}}
                                <div
                                    class="sticky top-0 z-10 bg-gray-50/90 dark:bg-[#0d0d0b]/90 backdrop-blur-sm border-b border-black/[.07] dark:border-white/[.06]">
                                    <div class="px-4 py-3">
                                        <div
                                            class="grid grid-cols-12 gap-4 text-[.7rem] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">

                                            {{-- Checkbox --}}
                                            <div class="col-span-1 flex items-center">
                                                <input type="checkbox" x-model="selectAll" @change="toggleSelectAll"
                                                    class="rounded border-gray-300 dark:border-gray-600 text-cyan-600 focus:ring-cyan-500 focus:ring-offset-0 dark:bg-gray-800">
                                            </div>

                                            {{-- Task --}}
                                            <div class="col-span-4 flex items-center">
                                                <button wire:click="sortBy('title')"
                                                    class="flex items-center gap-1.5 hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors group">
                                                    <x-heroicon-o-document-text class="w-3.5 h-3.5" />
                                                    <span>Task</span>
                                                    @if($sortBy === 'title')
                                                        @if($sortDirection === 'asc')
                                                            <x-heroicon-s-chevron-up class="w-3.5 h-3.5 text-cyan-500" />
                                                        @else
                                                            <x-heroicon-s-chevron-down class="w-3.5 h-3.5 text-cyan-500" />
                                                        @endif
                                                    @else
                                                        <x-heroicon-o-chevron-up-down
                                                            class="w-3.5 h-3.5 opacity-0 group-hover:opacity-60 transition-opacity" />
                                                    @endif
                                                </button>
                                            </div>

                                            {{-- Status --}}
                                            <div class="col-span-2 flex items-center">
                                                <button wire:click="sortBy('status')"
                                                    class="flex items-center gap-1.5 hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors group">
                                                    <x-heroicon-o-flag class="w-3.5 h-3.5" />
                                                    <span>Status</span>
                                                    @if($sortBy === 'status')
                                                        @if($sortDirection === 'asc') <x-heroicon-s-chevron-up
                                                            class="w-3.5 h-3.5 text-cyan-500" />
                                                        @else <x-heroicon-s-chevron-down class="w-3.5 h-3.5 text-cyan-500" /> @endif
                                                    @endif
                                                </button>
                                            </div>

                                            {{-- Priority --}}
                                            <div class="col-span-1 flex items-center">
                                                <button wire:click="sortBy('priority')"
                                                    class="flex items-center gap-1.5 hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors group">
                                                    <x-heroicon-o-exclamation-triangle class="w-3.5 h-3.5" />
                                                    <span>Pri.</span>
                                                    @if($sortBy === 'priority')
                                                        @if($sortDirection === 'asc') <x-heroicon-s-chevron-up
                                                            class="w-3.5 h-3.5 text-cyan-500" />
                                                        @else <x-heroicon-s-chevron-down class="w-3.5 h-3.5 text-cyan-500" /> @endif
                                                    @endif
                                                </button>
                                            </div>

                                            {{-- Assignee --}}
                                            <div class="col-span-2 flex items-center gap-1.5">
                                                <x-heroicon-o-users class="w-3.5 h-3.5" />
                                                <span>Assignee</span>
                                            </div>

                                            {{-- Project --}}
                                            <div class="col-span-1 flex items-center gap-1.5">
                                                <x-heroicon-o-folder class="w-3.5 h-3.5" />
                                                <span>Project</span>
                                            </div>

                                            {{-- Date --}}
                                            <div class="col-span-1 flex items-center">
                                                <button wire:click="sortBy('task_date')"
                                                    class="flex items-center gap-1.5 hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors group">
                                                    <x-heroicon-o-calendar-days class="w-3.5 h-3.5" />
                                                    <span>Due</span>
                                                    @if($sortBy === 'task_date')
                                                        @if($sortDirection === 'asc') <x-heroicon-s-chevron-up
                                                            class="w-3.5 h-3.5 text-cyan-500" />
                                                        @else <x-heroicon-s-chevron-down class="w-3.5 h-3.5 text-cyan-500" /> @endif
                                                    @endif
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                {{-- /TABLE HEADER --}}

                                {{-- TABLE BODY --}}
                                <div class="divide-y divide-black/[.05] dark:divide-white/[.05]">
                                    @forelse($paginatedTasks as $task)
                                        <div
                                            :class="selectedTasks.includes({{ $task->id }}) ? 'bg-cyan-50 dark:bg-cyan-900/10' : ''">
                                            <livewire:daily-task.components.daily-task-item :task="$task"
                                                :key="'task-' . $task->id . '-' . now()->timestamp" />
                                        </div>
                                    @empty
                                        <div class="py-20 text-center">
                                            <div
                                                class="w-16 h-16 bg-gray-100 dark:bg-white/[.05] rounded-2xl flex items-center justify-center mx-auto mb-4">
                                                <x-heroicon-o-clipboard-document-list
                                                    class="w-8 h-8 text-gray-300 dark:text-gray-600" />
                                            </div>
                                            <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-1">Tidak ada task
                                                ditemukan</h3>
                                            <p class="text-[.84rem] text-gray-400 dark:text-gray-500">Coba sesuaikan filter Anda</p>
                                        </div>
                                    @endforelse
                                </div>
                                {{-- /TABLE BODY --}}

                            </div>
                        </div>

                        {{-- PAGINATION --}}
                        @if($paginatedTasks && $paginatedTasks->hasPages())
                            <div
                                class="px-5 py-3.5 border-t border-black/[.06] dark:border-white/[.06] bg-gray-50/60 dark:bg-white/[.02]">
                                <div class="flex flex-col sm:flex-row items-center justify-between gap-3">

                                    {{-- Per page --}}
                                    <div class="flex items-center gap-2 text-[.8rem]">
                                        <span class="text-gray-500 dark:text-gray-400">Show</span>
                                        <div class="flex gap-1">
                                            @foreach($perPageOptions as $option)
                                                            <button wire:click="updatePerPage({{ $option }})"
                                                                class="px-2.5 py-1 rounded-lg font-medium text-[.78rem] transition-colors duration-150
                                                                           {{ $perPage === $option
                                                ? 'bg-cyan-600 text-white'
                                                : 'bg-gray-100 dark:bg-white/[.06] text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-white/[.10]' }}">
                                                                {{ $option }}
                                                            </button>
                                            @endforeach
                                        </div>
                                        <span class="text-gray-500 dark:text-gray-400">per halaman</span>
                                    </div>

                                    {{-- Info --}}
                                    <p class="text-[.8rem] text-gray-400 dark:text-gray-500">
                                        Menampilkan
                                        <span
                                            class="font-semibold text-gray-700 dark:text-gray-200">{{ $paginatedTasks->firstItem() ?? 0 }}</span>
                                        –
                                        <span
                                            class="font-semibold text-gray-700 dark:text-gray-200">{{ $paginatedTasks->lastItem() ?? 0 }}</span>
                                        dari
                                        <span
                                            class="font-semibold text-gray-700 dark:text-gray-200">{{ $paginatedTasks->total() }}</span>
                                        task
                                    </p>

                                    {{-- Links --}}
                                    <div>{{ $paginatedTasks->links() }}</div>

                                </div>
                            </div>
                        @endif

                    </div>{{-- /flat card --}}

                @else
                    {{-- ════════════════════════════════════════ --}}
                    {{-- GROUPED VIEW --}}
                    {{-- ════════════════════════════════════════ --}}
                    <div class="space-y-3">
                        @forelse($groupedTasks as $groupName => $tasks)
                            @php
                                $groupKey = $this->getGroupKey($groupBy, $groupName);
                                $displayTasks = $this->getGroupTasks($groupName);
                            @endphp

                            <div class="bg-white dark:bg-[#111110] rounded-2xl border border-black/[.07] dark:border-white/[.07] shadow-sm overflow-hidden"
                                x-data="{ collapsed: false }">

                                {{-- Group Header --}}
                                <div class="flex items-center justify-between px-5 py-3.5
                                            border-b border-black/[.06] dark:border-white/[.06]
                                            bg-gray-50/70 dark:bg-white/[.02]">
                                    <div class="flex items-center gap-3">
                                        @include('components.daily-task.partials.group-badge', ['groupBy' => $groupBy, 'groupName' => $groupName])
                                    </div>

                                    <div class="flex items-center gap-3">
                                        {{-- Progress bar --}}
                                        @if($tasks->count() > 0)
                                            @php
                                                $completedCount = $tasks->where('status', 'completed')->count();
                                                $totalCount = $tasks->count();
                                                $pct = $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0;
                                            @endphp
                                            <div class="hidden sm:flex flex-col items-end gap-1">
                                                <span class="text-[.7rem] font-medium text-gray-400">{{ round($pct) }}%</span>
                                                <div class="w-20 h-1.5 bg-gray-200 dark:bg-white/[.08] rounded-full overflow-hidden">
                                                    <div class="h-full bg-emerald-500 dark:bg-emerald-400 rounded-full transition-all duration-500"
                                                        style="width: {{ $pct }}%"></div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Count badge --}}
                                        <span class="px-2.5 py-1 rounded-lg text-[.78rem] font-semibold
                                                     bg-gray-100 dark:bg-white/[.07] text-gray-600 dark:text-gray-300
                                                     border border-black/[.07] dark:border-white/[.07]">
                                            {{ $tasks->count() }}
                                        </span>

                                        {{-- Collapse button --}}
                                        <button @click="collapsed = !collapsed" class="w-7 h-7 flex items-center justify-center rounded-lg
                                                       bg-white dark:bg-white/[.06] hover:bg-gray-100 dark:hover:bg-white/[.10]
                                                       border border-black/[.08] dark:border-white/[.08] transition-colors">
                                            <x-heroicon-o-chevron-down
                                                class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400 transition-transform duration-200"
                                                x-bind:class="{ 'rotate-180': collapsed }" />
                                        </button>
                                    </div>
                                </div>

                                {{-- Group Content --}}
                                <div x-show="!collapsed" x-transition:enter="transition ease-out duration-250"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-1">

                                    <div class="overflow-x-auto dt-scroll">
                                        <div class="min-w-[1100px]">

                                            {{-- Column header (lightweight) --}}
                                            <div class="px-4 py-2.5 border-b border-black/[.04] dark:border-white/[.04]">
                                                <div
                                                    class="grid grid-cols-12 gap-4 text-[.68rem] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                                    <div class="col-span-1"></div>
                                                    <div class="col-span-4">Task</div>
                                                    <div class="col-span-2">Status</div>
                                                    <div class="col-span-1">Pri.</div>
                                                    <div class="col-span-2">Assignee</div>
                                                    <div class="col-span-1">Project</div>
                                                    <div class="col-span-1">Due</div>
                                                </div>
                                            </div>

                                            {{-- Group rows --}}
                                            <div class="divide-y divide-black/[.04] dark:divide-white/[.04]">
                                                @foreach($displayTasks as $task)
                                                    <div
                                                        class="hover:bg-gray-50/60 dark:hover:bg-white/[.02] transition-colors duration-100">
                                                        <livewire:daily-task.components.daily-task-item :task="$task"
                                                            :key="'group-task-' . $task->id . '-' . now()->timestamp" />
                                                    </div>
                                                @endforeach

                                                {{-- Inline new task form --}}
                                                @if($this->isCreatingTask($groupBy, $groupName))
                                                    <div
                                                        class="px-5 py-3.5 bg-cyan-50/60 dark:bg-cyan-900/10 border-l-2 border-l-cyan-500">
                                                        <div class="grid grid-cols-12 gap-4 items-center">
                                                            <div class="col-span-1">
                                                                <div
                                                                    class="w-5 h-5 bg-cyan-100 dark:bg-cyan-900/30 rounded-full flex items-center justify-center">
                                                                    <x-heroicon-o-plus
                                                                        class="w-3 h-3 text-cyan-600 dark:text-cyan-300" />
                                                                </div>
                                                            </div>
                                                            <div class="col-span-5">
                                                                <input type="text" wire:model.live="newTaskData.{{ $groupKey }}.title"
                                                                    placeholder="Masukkan judul task..." autofocus
                                                                    @keydown.enter="$wire.saveNewTask('{{ $groupKey }}')"
                                                                    @keydown.escape="$wire.cancelNewTask('{{ $groupKey }}')"
                                                                    class="w-full px-3 py-2 text-[.84rem] border border-black/[.10] dark:border-white/[.10] rounded-lg
                                                                              bg-white dark:bg-[#1a1a18] text-gray-800 dark:text-gray-100
                                                                              placeholder-gray-400 dark:placeholder-gray-500
                                                                              focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none">
                                                            </div>
                                                            <div class="col-span-6 flex items-center gap-2 justify-end">
                                                                <button wire:click="saveNewTask('{{ $groupKey }}')"
                                                                    wire:loading.attr="disabled" class="flex items-center gap-1.5 px-3.5 py-2 text-[.8rem] font-medium
                                                                               bg-emerald-500 dark:bg-emerald-600 hover:opacity-90 text-white
                                                                               rounded-lg transition-opacity">
                                                                    <x-heroicon-o-check class="w-3.5 h-3.5" /> Simpan
                                                                </button>
                                                                <button wire:click="cancelNewTask('{{ $groupKey }}')"
                                                                    class="flex items-center gap-1.5 px-3.5 py-2 text-[.8rem] font-medium
                                                                               bg-gray-100 dark:bg-white/[.07] hover:bg-gray-200 dark:hover:bg-white/[.12]
                                                                               text-gray-600 dark:text-gray-300 rounded-lg transition-colors">
                                                                    <x-heroicon-o-x-mark class="w-3.5 h-3.5" /> Batal
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                        </div>
                                    </div>

                                    {{-- Load More --}}
                                    @if($this->hasMoreInGroup($groupName))
                                        <div class="px-5 py-3 border-t border-black/[.05] dark:border-white/[.05]">
                                            <button wire:click="loadMoreInGroup('{{ $groupName }}')" wire:loading.attr="disabled"
                                                wire:target="loadMoreInGroup('{{ $groupName }}')" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-[.82rem] font-medium
                                                           text-gray-600 dark:text-gray-400
                                                           bg-gray-50 dark:bg-white/[.03] hover:bg-gray-100 dark:hover:bg-white/[.06]
                                                           border border-black/[.08] dark:border-white/[.07] rounded-xl transition-colors
                                                           disabled:opacity-50 disabled:cursor-not-allowed">
                                                <svg wire:loading.remove wire:target="loadMoreInGroup('{{ $groupName }}')"
                                                    class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                                <svg wire:loading wire:target="loadMoreInGroup('{{ $groupName }}')"
                                                    class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                        stroke-width="4" />
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                                </svg>
                                                <span wire:loading.remove wire:target="loadMoreInGroup('{{ $groupName }}')">
                                                    Muat {{ min($this->groupLoadIncrement, $this->getRemainingCount($groupName)) }} task
                                                    lainnya
                                                </span>
                                                <span wire:loading wire:target="loadMoreInGroup('{{ $groupName }}')">Memuat…</span>
                                                <span wire:loading.remove wire:target="loadMoreInGroup('{{ $groupName }}')"
                                                    class="ml-1 px-2 py-0.5 bg-gray-100 dark:bg-white/[.07] text-gray-500 dark:text-gray-400 rounded-full text-[.74rem] font-semibold">
                                                    {{ $this->getRemainingCount($groupName) }}
                                                </span>
                                            </button>
                                        </div>
                                    @endif

                                    {{-- Add Task Button --}}
                                    @if(!$this->isCreatingTask($groupBy, $groupName))
                                        <div class="px-5 py-3 border-t border-black/[.05] dark:border-white/[.05]">
                                            <button wire:click="startCreatingTask('{{ $groupBy }}', '{{ $groupName }}')" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-[.82rem] font-medium
                                                           text-gray-500 dark:text-gray-400
                                                           border-2 border-dashed border-gray-200 dark:border-white/[.10] rounded-xl
                                                           hover:border-cyan-400 dark:hover:border-cyan-600
                                                           hover:text-cyan-600 dark:hover:text-cyan-400
                                                           hover:bg-cyan-50/40 dark:hover:bg-cyan-900/10
                                                           transition-all duration-150 group">
                                                <div class="w-5 h-5 flex items-center justify-center rounded-full
                                                            bg-gray-100 dark:bg-white/[.07]
                                                            group-hover:bg-cyan-100 dark:group-hover:bg-cyan-900/30 transition-colors">
                                                    <x-heroicon-o-plus class="w-3 h-3" />
                                                </div>
                                                <span>Tambah Task Baru</span>
                                                <span class="hidden sm:inline px-2 py-0.5 bg-gray-100 dark:bg-white/[.07]
                                                             text-gray-500 dark:text-gray-400 rounded text-[.73rem]">
                                                    {{ $groupName }}
                                                </span>
                                            </button>
                                        </div>
                                    @endif

                                </div>{{-- /group content --}}
                            </div>{{-- /group card --}}

                        @empty

                            {{-- Empty State --}}
                            <div
                                class="bg-white dark:bg-[#111110] rounded-2xl border border-black/[.07] dark:border-white/[.07] shadow-sm p-16 text-center">
                                <div
                                    class="w-20 h-20 bg-gray-100 dark:bg-white/[.04] rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-inner">
                                    <x-heroicon-o-clipboard-document-list class="w-10 h-10 text-gray-300 dark:text-gray-600" />
                                </div>
                                <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-2 syne">Tidak ada task ditemukan
                                </h3>
                                <p class="text-gray-400 dark:text-gray-500 mb-7 max-w-sm mx-auto">
                                    Coba sesuaikan filter Anda atau buat task baru untuk memulai
                                </p>
                                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                    <x-filament::button color="primary" icon="heroicon-o-plus" size="lg">Buat Task
                                        Baru</x-filament::button>
                                    <x-filament::button color="gray" icon="heroicon-o-funnel" size="lg">Reset
                                        Filter</x-filament::button>
                                </div>
                            </div>

                        @endforelse
                    </div>{{-- /grouped view --}}
                @endif
            </div>{{-- /desktop --}}

        @else
            {{-- ── Kanban View ── --}}
            <livewire:daily-task.components.kanban-board-component :initial-filters="$this->currentFilters"
                :key="'kanban-' . $kanbanMountKey . '-' . json_encode($this->currentFilters)" />
        @endif

    </div>{{-- /main content --}}

    {{-- Task Detail Modal --}}
    <livewire:daily-task.modals.daily-task-detail-modal />
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    @push('scripts')
        <script>
            function kanbanBoard() {
                return {
                    sortables: {},
                    isDragging: false,
                    draggedElement: null,

                    init() {
                        this.initializeSortable();
                        Livewire.on('taskStatusChanged', () => setTimeout(() => this.initializeSortable(), 100));
                        Livewire.on('task-created', () => setTimeout(() => this.initializeSortable(), 100));
                        Livewire.on('revertTaskMove', (event) => this.revertMove(event.taskId));
                    },

                    initializeSortable() {
                        Object.values(this.sortables).forEach(s => s.destroy());
                        this.sortables = {};

                        document.querySelectorAll('.column-content').forEach(column => {
                            const status = column.dataset.status;
                            this.sortables[status] = new Sortable(column, {
                                group: 'kanban', animation: 200, delay: 50, delayOnTouchOnly: true,
                                touchStartThreshold: 5, ghostClass: 'kanban-ghost', chosenClass: 'kanban-chosen',
                                dragClass: 'kanban-drag', handle: '.kanban-card',
                                onStart: (evt) => { this.isDragging = true; this.draggedElement = evt.item; document.body.classList.add('dragging'); },
                                onEnd: (evt) => {
                                    this.isDragging = false;
                                    document.body.classList.remove('dragging');
                                    const taskId = parseInt(evt.item.dataset.taskId);
                                    const newStatus = evt.to.dataset.status;
                                    const newPosition = evt.newIndex;
                                    if (evt.from !== evt.to) @this.call('handleTaskMoved', taskId, newStatus, newPosition);
                                },
                                onMove: (evt) => {
                                    const limit = this.getColumnLimit(evt.to.dataset.status);
                                    if (limit) {
                                        const count = evt.to.querySelectorAll('.kanban-card').length;
                                        if (count >= limit && evt.from !== evt.to) return false;
                                    }
                                    return true;
                                }
                            });
                        });
                    },

                    getColumnLimit(status) {
                        const limits = { 'in_progress': {{ $columns['in_progress']['limit'] ?? 'null' }} };
                        return limits[status] || null;
                    },

                    revertMove(taskId) {
                        const card = document.querySelector(`[data-task-id="${taskId}"]`);
                        if (card) setTimeout(() => @this.call('$refresh'), 100);
                    }
                }
            }

            function taskManager() {
                return {
                    selectAll: false,
                    selectedTasks: [],
                    isMobile: false,

                    init() {
                        this.checkMobile();
                        window.addEventListener('resize', () => this.checkMobile());
                    },

                    checkMobile() { this.isMobile = window.innerWidth < 1024; },

                    toggleSelectAll() {
                        this.selectedTasks = this.selectAll
                            ? @json($paginatedTasks ? $paginatedTasks->pluck('id')->toArray() : [])
                            : [];
                    },

                    toggleTaskSelection(taskId) {
                        if (this.selectedTasks.includes(taskId)) {
                            this.selectedTasks = this.selectedTasks.filter(id => id !== taskId);
                        } else {
                            this.selectedTasks.push(taskId);
                        }
                        this.selectAll = this.selectedTasks.length > 0;
                    }
                }
            }
        </script>
    @endpush

</div>