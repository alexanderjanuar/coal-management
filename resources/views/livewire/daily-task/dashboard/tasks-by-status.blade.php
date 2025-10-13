<div class="space-y-6" wire:loading.class="opacity-50 pointer-events-none">
    {{-- Pill Tabs Navigation (seperti wireframe) --}}
    <div class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-hide">
        @foreach($tabs as $key => $tab)
        <button wire:click="changeTab('{{ $key }}')" wire:loading.attr="disabled" class="group relative px-6 py-3 rounded-full text-sm font-semibold whitespace-nowrap transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed
                    {{ $activeTab === $key 
                        ? 'bg-primary-600 text-white' 
                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-2 border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500 hover:text-primary-600 dark:hover:text-primary-400' 
                    }}">
            <span class="flex items-center gap-2">
                @svg($tab['icon'], 'w-5 h-5')
                {{ $tab['label'] }}
                <span class="inline-flex items-center justify-center min-w-[24px] px-2 py-0.5 rounded-full text-xs font-bold
                        {{ $activeTab === $key 
                            ? 'bg-white/20 text-white' 
                            : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' 
                        }}">
                    {{ $this->statusCounts[$key] ?? 0 }}
                </span>
            </span>
        </button>
        @endforeach
    </div>

    {{-- Divider --}}
    <div class="border-t-2 border-gray-200 dark:border-gray-700"></div>

    {{-- Tab Content with Big Rounded Cards --}}
    <div class="space-y-4">
        {{-- All Tasks --}}
        @if($activeTab === 'all')
        <div wire:key="tab-all" class="space-y-5">
            @forelse($this->allTasks as $task)
            @include('livewire.daily-task.dashboard.partials.task-card-pill', ['task' => $task, 'statusOptions' =>
            $statusOptions])
            @empty
            <div
                class="text-center py-16 bg-white dark:bg-gray-800 rounded-3xl border-2 border-dashed border-gray-300 dark:border-gray-700">
                <x-heroicon-o-inbox class="mx-auto h-16 w-16 text-gray-400" />
                <h3 class="mt-4 text-base font-semibold text-gray-900 dark:text-white">Tidak ada tugas</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Belum ada tugas untuk periode ini.
                </p>
            </div>
            @endforelse

            @if($this->hasMoreTasks('all'))
            <div class="flex justify-center pt-4">
                <button wire:click="loadMore('all')" wire:loading.attr="disabled" wire:target="loadMore('all')"
                    class="px-8 py-3 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-full text-sm font-semibold text-gray-700 dark:text-gray-300 hover:border-primary-500 hover:text-primary-600 dark:hover:text-primary-400 transition-all disabled:opacity-50">
                    <span wire:loading.remove wire:target="loadMore('all')">
                        Muat Lebih Banyak
                    </span>
                    <span wire:loading wire:target="loadMore('all')" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Memuat...
                    </span>
                </button>
            </div>
            @endif
        </div>
        @endif

        {{-- Pending Tasks --}}
        @if($activeTab === 'pending')
        <div wire:key="tab-pending" class="space-y-5">
            @forelse($this->pendingTasks as $task)
            @include('livewire.daily-task.dashboard.partials.task-card-pill', ['task' => $task, 'statusOptions' =>
            $statusOptions])
            @empty
            <div
                class="text-center py-16 bg-white dark:bg-gray-800 rounded-3xl border-2 border-dashed border-gray-300 dark:border-gray-700">
                <x-heroicon-o-clock class="mx-auto h-16 w-16 text-orange-400" />
                <h3 class="mt-4 text-base font-semibold text-gray-900 dark:text-white">Tidak ada tugas tertunda</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Semua tugas sudah diproses.
                </p>
            </div>
            @endforelse

            @if($this->hasMoreTasks('pending'))
            <div class="flex justify-center pt-4">
                <button wire:click="loadMore('pending')" wire:loading.attr="disabled" wire:target="loadMore('pending')"
                    class="px-8 py-3 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-full text-sm font-semibold text-gray-700 dark:text-gray-300 hover:border-primary-500 hover:text-primary-600 dark:hover:text-primary-400 transition-all disabled:opacity-50">
                    <span wire:loading.remove wire:target="loadMore('pending')">
                        Muat Lebih Banyak
                    </span>
                    <span wire:loading wire:target="loadMore('pending')" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Memuat...
                    </span>
                </button>
            </div>
            @endif
        </div>
        @endif

        {{-- In Progress Tasks --}}
        @if($activeTab === 'in_progress')
        <div wire:key="tab-in-progress" class="space-y-5">
            @forelse($this->inProgressTasks as $task)
            @include('livewire.daily-task.dashboard.partials.task-card-pill', ['task' => $task, 'statusOptions' =>
            $statusOptions])
            @empty
            <div
                class="text-center py-16 bg-white dark:bg-gray-800 rounded-3xl border-2 border-dashed border-gray-300 dark:border-gray-700">
                <x-heroicon-o-arrow-path class="mx-auto h-16 w-16 text-blue-400" />
                <h3 class="mt-4 text-base font-semibold text-gray-900 dark:text-white">Tidak ada tugas sedang dikerjakan
                </h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Belum ada tugas dalam proses.
                </p>
            </div>
            @endforelse

            @if($this->hasMoreTasks('in_progress'))
            <div class="flex justify-center pt-4">
                <button wire:click="loadMore('in_progress')" wire:loading.attr="disabled"
                    wire:target="loadMore('in_progress')"
                    class="px-8 py-3 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-full text-sm font-semibold text-gray-700 dark:text-gray-300 hover:border-primary-500 hover:text-primary-600 dark:hover:text-primary-400 transition-all disabled:opacity-50">
                    <span wire:loading.remove wire:target="loadMore('in_progress')">
                        Muat Lebih Banyak
                    </span>
                    <span wire:loading wire:target="loadMore('in_progress')" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Memuat...
                    </span>
                </button>
            </div>
            @endif
        </div>
        @endif

        {{-- Completed Tasks --}}
        @if($activeTab === 'completed')
        <div wire:key="tab-completed" class="space-y-5">
            @forelse($this->completedTasks as $task)
            @include('livewire.daily-task.dashboard.partials.task-card-pill', ['task' => $task, 'statusOptions' =>
            $statusOptions])
            @empty
            <div
                class="text-center py-16 bg-white dark:bg-gray-800 rounded-3xl border-2 border-dashed border-gray-300 dark:border-gray-700">
                <x-heroicon-o-check-circle class="mx-auto h-16 w-16 text-green-400" />
                <h3 class="mt-4 text-base font-semibold text-gray-900 dark:text-white">Tidak ada tugas selesai</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Belum ada tugas yang diselesaikan.
                </p>
            </div>
            @endforelse

            @if($this->hasMoreTasks('completed'))
            <div class="flex justify-center pt-4">
                <button wire:click="loadMore('completed')" wire:loading.attr="disabled"
                    wire:target="loadMore('completed')"
                    class="px-8 py-3 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-full text-sm font-semibold text-gray-700 dark:text-gray-300 hover:border-primary-500 hover:text-primary-600 dark:hover:text-primary-400 transition-all disabled:opacity-50">
                    <span wire:loading.remove wire:target="loadMore('completed')">
                        Muat Lebih Banyak
                    </span>
                    <span wire:loading wire:target="loadMore('completed')" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Memuat...
                    </span>
                </button>
            </div>
            @endif
        </div>
        @endif

        {{-- Cancelled Tasks --}}
        @if($activeTab === 'cancelled')
        <div wire:key="tab-cancelled" class="space-y-5">
            @forelse($this->cancelledTasks as $task)
            @include('livewire.daily-task.dashboard.partials.task-card-pill', ['task' => $task, 'statusOptions' =>
            $statusOptions])
            @empty
            <div
                class="text-center py-16 bg-white dark:bg-gray-800 rounded-3xl border-2 border-dashed border-gray-300 dark:border-gray-700">
                <x-heroicon-o-x-circle class="mx-auto h-16 w-16 text-red-400" />
                <h3 class="mt-4 text-base font-semibold text-gray-900 dark:text-white">Tidak ada tugas dibatalkan</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Tidak ada tugas yang dibatalkan.
                </p>
            </div>
            @endforelse

            @if($this->hasMoreTasks('cancelled'))
            <div class="flex justify-center pt-4">
                <button wire:click="loadMore('cancelled')" wire:loading.attr="disabled"
                    wire:target="loadMore('cancelled')"
                    class="px-8 py-3 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-full text-sm font-semibold text-gray-700 dark:text-gray-300 hover:border-primary-500 hover:text-primary-600 dark:hover:text-primary-400 transition-all disabled:opacity-50">
                    <span wire:loading.remove wire:target="loadMore('cancelled')">
                        Muat Lebih Banyak
                    </span>
                    <span wire:loading wire:target="loadMore('cancelled')" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Memuat...
                    </span>
                </button>
            </div>
            @endif
        </div>
        @endif
    </div>

    <livewire:daily-task.modals.daily-task-detail-modal />
</div>