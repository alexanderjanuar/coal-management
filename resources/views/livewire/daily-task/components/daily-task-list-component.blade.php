{{-- Enhanced Task List Component dengan Dark Mode Support --}}
<div class="space-y-4 lg:space-y-6" x-data="taskManager()">


    {{-- Filter Component - Separate component --}}
    <livewire:daily-task.form.daily-task-filter-component :initial-filters="$this->currentFilters"
        :total-tasks="$totalTasks" />

    {{-- Enhanced Task List Content - Responsive Views --}}
    <div class="space-y-4 lg:space-y-6">
        @if($viewMode === 'list')

        {{-- Desktop Table View (lg breakpoint and above) --}}
        <div class="hidden lg:block">
            @if($groupBy === 'none')
            {{-- Ungrouped View dengan Pagination --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 
                    shadow-sm dark:shadow-lg overflow-hidden">

                {{-- Horizontal Scrollable Container --}}
                <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 
                        scrollbar-track-gray-100 dark:scrollbar-track-gray-800">
                    <div class="min-w-[1200px]">

                        {{-- Table Header --}}
                        <div
                            class="sticky top-0 z-10 bg-gray-100 dark:bg-gray-900/60 border-b border-gray-200 dark:border-gray-600">
                            <div class="px-6 py-4">
                                <div class="grid grid-cols-12 gap-4 text-xs font-semibold text-gray-700 dark:text-gray-200 
                                        uppercase tracking-wider">
                                    {{-- Checkbox Column --}}
                                    <div class="col-span-1 flex items-center">
                                        <input type="checkbox" x-model="selectAll" @change="toggleSelectAll" class="rounded border-gray-300 dark:border-gray-600 text-primary-600 
                                                  focus:ring-primary-500 focus:ring-offset-0 dark:bg-gray-700">
                                    </div>

                                    {{-- Task Title Column --}}
                                    <div class="col-span-4 flex items-center gap-2">
                                        <button wire:click="sortBy('title')" class="flex items-center gap-2 hover:text-primary-600 dark:hover:text-primary-400 
                                                   transition-colors group">
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

                                    {{-- Status Column --}}
                                    <div class="col-span-2 flex items-center gap-2">
                                        <button wire:click="sortBy('status')" class="flex items-center gap-2 hover:text-primary-600 dark:hover:text-primary-400 
                                                   transition-colors group">
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
                                            @endif
                                        </button>
                                    </div>

                                    {{-- Priority Column --}}
                                    <div class="col-span-1 flex items-center gap-2">
                                        <button wire:click="sortBy('priority')" class="flex items-center gap-2 hover:text-primary-600 dark:hover:text-primary-400 
                                                   transition-colors group">
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
                                            @endif
                                        </button>
                                    </div>

                                    {{-- Assignee Column --}}
                                    <div class="col-span-2 flex items-center gap-2">
                                        <x-heroicon-o-users class="w-4 h-4" />
                                        <span>Assignee</span>
                                    </div>

                                    {{-- Project Column --}}
                                    <div class="col-span-1 flex items-center gap-2">
                                        <x-heroicon-o-folder class="w-4 h-4" />
                                        <span>Project</span>
                                    </div>

                                    {{-- Due Date Column --}}
                                    <div class="col-span-1 flex items-center gap-2">
                                        <button wire:click="sortBy('task_date')" class="flex items-center gap-2 hover:text-primary-600 dark:hover:text-primary-400 
                                                   transition-colors group">
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
                                            @endif
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Table Body --}}
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($paginatedTasks as $task)
                            <div class="px-6 py-4 hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent 
                                        dark:hover:from-primary-900/20 dark:hover:to-transparent transition-all duration-200 
                                        group cursor-pointer border-l-4 border-l-transparent 
                                        hover:border-l-primary-300 dark:hover:border-l-primary-600"
                                :class="{ 'bg-primary-25 dark:bg-primary-900/30 border-l-primary-500': selectedTasks.includes({{ $task->id }}) }">
                                <livewire:daily-task.components.daily-task-item :task="$task"
                                    :key="'task-'.$task->id . '-' . now()->timestamp" />
                            </div>
                            @empty
                            {{-- Empty State --}}
                            <div class="py-16 text-center">
                                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700/60 rounded-full flex items-center justify-center 
                                            mx-auto mb-6 shadow-inner">
                                    <x-heroicon-o-clipboard-document-list
                                        class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                    Tidak ada task ditemukan
                                </h3>
                                <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-sm mx-auto">
                                    Coba sesuaikan filter Anda untuk melihat lebih banyak task
                                </p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Enhanced Pagination --}}
                @if($paginatedTasks && $paginatedTasks->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        {{-- Per Page Selector --}}
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Tampilkan:</span>
                            <div class="flex gap-1">
                                @foreach($perPageOptions as $option)
                                <button wire:click="updatePerPage({{ $option }})"
                                    class="px-3 py-1.5 text-sm font-medium rounded-lg transition-all duration-200
                                                   {{ $perPage === $option 
                                                       ? 'bg-primary-500 text-white shadow-md' 
                                                       : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                    {{ $option }}
                                </button>
                                @endforeach
                            </div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">per halaman</span>
                        </div>

                        {{-- Pagination Info --}}
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Menampilkan
                            <span class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ $paginatedTasks->firstItem() ?? 0 }}
                            </span>
                            sampai
                            <span class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ $paginatedTasks->lastItem() ?? 0 }}
                            </span>
                            dari
                            <span class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ $paginatedTasks->total() }}
                            </span>
                            task
                        </div>

                        {{-- Pagination Links --}}
                        <div>
                            {{ $paginatedTasks->links() }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @else
            {{-- Grouped View --}}
            <div class="space-y-2">
                @forelse($groupedTasks as $groupName => $tasks)
                @php
                $groupKey = $this->getGroupKey($groupBy, $groupName);
                $displayTasks = $this->getGroupTasks($groupName);
                @endphp

                <div class="overflow-hidden" x-data="{ collapsed: false }">
                    {{-- Group Header --}}
                    <div class="px-6 py-4
                    border-b border-gray-200 dark:border-gray-600
                    backdrop-blur-sm bg-opacity-95 dark:bg-opacity-95
                    shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                {{-- Group Badge --}}
                                @include('components.daily-task.partials.group-badge', [
                                'groupBy' => $groupBy,
                                'groupName' => $groupName
                                ])
                            </div>

                            <div class="flex items-center gap-3">
                                {{-- Progress Bar --}}
                                @if($tasks->count() > 0)
                                @php
                                $completedCount = $tasks->where('status', 'completed')->count();
                                $totalCount = $tasks->count();
                                $progressPercentage = $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0;
                                @endphp
                                <div class="hidden sm:flex flex-col items-end gap-1">
                                    <div class="text-xs font-medium text-gray-600 dark:text-gray-400">Progress</div>
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-20 h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden shadow-inner">
                                            <div class="h-full bg-green-500 dark:bg-green-400 transition-all duration-500"
                                                style="width: {{ $progressPercentage }}%"></div>
                                        </div>
                                        <span
                                            class="text-xs font-semibold text-gray-700 dark:text-gray-300 min-w-[2.5rem] text-right">
                                            {{ round($progressPercentage) }}%
                                        </span>
                                    </div>
                                </div>
                                @endif

                                {{-- Task Count Badge --}}
                                <div class="px-2.5 py-1 rounded-lg font-semibold text-base shadow-sm border
                                bg-gray-50 text-gray-700 border-gray-200 
                                dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                                    {{ $tasks->count() }}
                                </div>

                                {{-- Collapse Button --}}
                                <button @click="collapsed = !collapsed" class="p-2 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 
                                   rounded-lg transition-all duration-200 border border-gray-200 dark:border-gray-600 
                                   hover:shadow-md">
                                    <x-heroicon-o-chevron-down
                                        class="w-4 h-4 text-gray-600 dark:text-gray-400 transition-all duration-200"
                                        x-bind:class="{ 'rotate-180': collapsed }" />
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Group Content --}}
                    <div x-show="!collapsed" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0">

                        <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 
                                    scrollbar-track-gray-100 dark:scrollbar-track-gray-800">
                            <div class="min-w-[1200px]">
                                {{-- Group Table Header --}}
                                <div
                                    class="border-b border-gray-200 dark:border-gray-600">
                                    <div class="px-6 py-3">
                                        <div class="grid grid-cols-12 gap-4 text-xs font-semibold text-gray-700 dark:text-gray-200 
                                                    uppercase tracking-wider">
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
                                    @foreach($displayTasks as $task)
                                    <div
                                        class="hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent 
                                                    dark:hover:from-primary-900/20 dark:hover:to-transparent transition-all duration-200">
                                        <livewire:daily-task.components.daily-task-item :task="$task"
                                            :key="'group-task-'.$task->id . '-' . now()->timestamp" />
                                    </div>
                                    @endforeach

                                    {{-- Inline New Task Creation --}}
                                    @if($this->isCreatingTask($groupBy, $groupName))
                                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-25 
                                                    dark:from-blue-900/20 dark:to-blue-900/10 border-l-4 border-l-blue-400 
                                                    dark:border-l-blue-500">
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
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 
                                                                  rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                                                                  dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 bg-white" autofocus
                                                    @keydown.enter="$wire.saveNewTask('{{ $groupKey }}')"
                                                    @keydown.escape="$wire.cancelNewTask('{{ $groupKey }}')" />
                                            </div>
                                            <div class="col-span-7 flex items-center justify-end gap-2">
                                                <button wire:click="saveNewTask('{{ $groupKey }}')"
                                                    wire:loading.attr="disabled" class="px-4 py-2 bg-green-500 hover:bg-green-600 dark:bg-green-600 
                                                                   dark:hover:bg-green-700 text-white rounded-lg transition-colors 
                                                                   duration-200 flex items-center gap-2 shadow-sm">
                                                    <x-heroicon-o-check class="w-4 h-4" />
                                                    <span>Simpan</span>
                                                </button>
                                                <button wire:click="cancelNewTask('{{ $groupKey }}')" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 dark:bg-gray-600 
                                                                   dark:hover:bg-gray-700 text-white rounded-lg transition-colors 
                                                                   duration-200 flex items-center gap-2 shadow-sm">
                                                    <x-heroicon-o-x-mark class="w-4 h-4" />
                                                    <span>Batal</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Load More Button --}}
                        @if($this->hasMoreInGroup($groupName))
                        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                            <button wire:click="loadMoreInGroup('{{ $groupName }}')" wire:loading.attr="disabled"
                                wire:target="loadMoreInGroup('{{ $groupName }}')" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium 
                                    text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 
                                    border border-gray-300 dark:border-gray-600 rounded-lg
                                    hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500
                                    transition-all duration-200 group
                                    disabled:opacity-50 disabled:cursor-not-allowed">

                                {{-- Icon dengan loading state --}}
                                <svg wire:loading.remove wire:target="loadMoreInGroup('{{ $groupName }}')"
                                    class="w-4 h-4 transition-transform group-hover:translate-y-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>

                                <svg wire:loading wire:target="loadMoreInGroup('{{ $groupName }}')"
                                    class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>

                                {{-- Text --}}
                                <span wire:loading.remove wire:target="loadMoreInGroup('{{ $groupName }}')">
                                    Muat {{ min($this->groupLoadIncrement, $this->getRemainingCount($groupName)) }} Task
                                    Lainnya
                                </span>
                                <span wire:loading wire:target="loadMoreInGroup('{{ $groupName }}')">
                                    Memuat...
                                </span>

                                {{-- Badge count --}}
                                <span wire:loading.remove wire:target="loadMoreInGroup('{{ $groupName }}')" class="ml-1 px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 
                                    rounded-full text-xs font-semibold">
                                    {{ $this->getRemainingCount($groupName) }}
                                </span>
                            </button>
                        </div>
                        @endif

                        {{-- Add New Task Section --}}
                        @if(!$this->isCreatingTask($groupBy, $groupName))
                        <div
                            class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                            <button wire:click="startCreatingTask('{{ $groupBy }}', '{{ $groupName }}')" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium 
                                    text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800 
                                    border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg
                                    hover:bg-blue-50 dark:hover:bg-blue-900/20 
                                    hover:border-blue-400 dark:hover:border-blue-500 
                                    hover:text-blue-600 dark:hover:text-blue-400
                                    transition-all duration-200 group">

                                {{-- Icon --}}
                                <div class="w-5 h-5 flex items-center justify-center rounded-full 
                                    bg-gray-200 dark:bg-gray-700 
                                    group-hover:bg-blue-200 dark:group-hover:bg-blue-800
                                    transition-all duration-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>

                                {{-- Text --}}
                                <span class="font-medium">
                                    Tambah Task Baru
                                </span>

                                {{-- Group name badge (optional) --}}
                                <span class="hidden sm:inline-flex px-2 py-0.5 bg-gray-100 dark:bg-gray-700 
                                    text-gray-600 dark:text-gray-400 rounded text-xs">
                                    {{ $groupName }}
                                </span>
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                {{-- Empty State --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border-2 border-gray-200 dark:border-gray-700 
                            shadow-lg dark:shadow-xl p-12 text-center">
                    <div class="w-32 h-32 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 
                                rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-inner">
                        <x-heroicon-o-clipboard-document-list class="w-16 h-16 text-gray-400 dark:text-gray-500" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-3">
                        Tidak ada task ditemukan
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-md mx-auto text-lg">
                        Coba sesuaikan filter Anda atau buat task baru untuk memulai
                    </p>
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

        @else
        {{-- Kanban Board View (placeholder) --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 
                    shadow-sm dark:shadow-lg dark:shadow-gray-900/20 p-8 text-center">
            <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center 
                       mx-auto mb-6 shadow-inner dark:shadow-gray-900/50">
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
    <livewire:daily-task.modals.daily-task-detail-modal />

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

    {{-- CSS untuk scroll indicators dan dark mode enhancements --}}
    <style>
        /* Custom scrollbar untuk dark mode */
        .scrollbar-thin::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            @apply bg-gray-100 dark: bg-gray-800 rounded-lg;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            @apply bg-gray-300 dark: bg-gray-600 rounded-lg;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            @apply bg-gray-400 dark: bg-gray-500;
        }

        /* Scroll shadows untuk visual feedback */
        .scroll-left-shadow {
            box-shadow: inset 10px 0 8px -8px rgba(0, 0, 0, 0.15);
        }

        .dark .scroll-left-shadow {
            box-shadow: inset 10px 0 8px -8px rgba(0, 0, 0, 0.3);
        }

        .scroll-right-shadow {
            box-shadow: inset -10px 0 8px -8px rgba(0, 0, 0, 0.15);
        }

        .dark .scroll-right-shadow {
            box-shadow: inset -10px 0 8px -8px rgba(0, 0, 0, 0.3);
        }

        /* Progress bar animations */
        .progress-bar {
            transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Enhanced hover effects untuk dark mode */
        .group:hover .progress-bar {
            filter: brightness(1.1);
        }

        .dark .group:hover .progress-bar {
            filter: brightness(1.2);
        }

        /* Skeleton pulse animation */
        @keyframes skeleton-pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .animate-pulse {
            animation: skeleton-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Smooth transition untuk loading states */
        [wire\:loading\.delay] {
            transition: opacity 0.3s ease-in-out;
        }

        [wire\:loading\.remove\.delay] {
            transition: opacity 0.3s ease-in-out;
        }
    </style>
</div>