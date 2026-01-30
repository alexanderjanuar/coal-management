<div x-data="{ 
        showInProgressAlert: true,
        showDraftAlert: true
    }"
    class="fi-wi-stats-overview-stat relative rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 hover:shadow-md transition-all duration-200 flex flex-col h-full">

    {{-- Fixed Header Section --}}
    <div class="shrink-0 p-4 sm:p-6 pb-3 sm:pb-4">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-x-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-success-50 dark:bg-success-500/10">
                    <x-filament::icon icon="heroicon-o-clipboard-document-check"
                        class="h-5 w-5 text-success-600 dark:text-success-400" />
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tugas Hari Ini
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $stats['total'] }} tugas untuk hari ini
                    </p>
                </div>
            </div>

            {{-- Refresh Button --}}
            <button wire:click="$refresh"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors" title="Muat Ulang">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>

        {{-- Task Status Grid --}}
        <div class="grid grid-cols-2 gap-3 mb-4">
            {{-- In Progress Tasks --}}
            <div
                class="p-3 rounded-lg bg-primary-50 dark:bg-primary-500/10 border border-primary-100 dark:border-primary-500/20">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-primary-600 dark:text-primary-400">
                            Sedang Berjalan
                        </p>
                        <p class="text-2xl font-bold text-primary-700 dark:text-primary-300 mt-1">
                            {{ $stats['in_progress'] }}
                        </p>
                    </div>
                    <div
                        class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Draft Tasks --}}
            <div
                class="p-3 rounded-lg bg-warning-50 dark:bg-warning-500/10 border border-warning-100 dark:border-warning-500/20">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-warning-600 dark:text-warning-400">
                            Draft
                        </p>
                        <p class="text-2xl font-bold text-warning-700 dark:text-warning-300 mt-1">
                            {{ $stats['draft'] }}
                        </p>
                    </div>
                    <div
                        class="w-8 h-8 rounded-full bg-warning-100 dark:bg-warning-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Alerts --}}
        @if($stats['in_progress'] > 0 || $stats['draft'] > 0)
        <div class="space-y-2">
            {{-- In Progress Alert --}}
            @if($stats['in_progress'] > 0)
            <div x-show="showInProgressAlert" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="flex items-center gap-x-2 p-3 rounded-lg bg-primary-50 dark:bg-primary-500/10 border border-primary-200 dark:border-primary-500/20">
                <div class="shrink-0">
                    <div
                        class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-primary-600 dark:text-primary-400 animate-pulse" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-primary-800 dark:text-primary-300">
                        {{ $stats['in_progress'] }} Tugas Sedang Dikerjakan
                    </p>
                    <p class="text-xs text-primary-600 dark:text-primary-400">
                        Teruskan progres tugas yang aktif
                    </p>
                </div>
                <button @click="showInProgressAlert = false"
                    class="shrink-0 text-primary-400 hover:text-primary-600 dark:text-primary-500 dark:hover:text-primary-400 transition-colors"
                    title="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            @endif

            {{-- Draft Alert --}}
            @if($stats['draft'] > 0)
            <div x-show="showDraftAlert" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="flex items-center gap-x-2 p-3 rounded-lg bg-warning-50 dark:bg-warning-500/10 border border-warning-200 dark:border-warning-500/20">
                <div class="shrink-0">
                    <div
                        class="w-8 h-8 rounded-full bg-warning-100 dark:bg-warning-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-warning-800 dark:text-warning-300">
                        {{ $stats['draft'] }} Tugas Masih Draft
                    </p>
                    <p class="text-xs text-warning-600 dark:text-warning-400">
                        Selesaikan dan mulai tugas draft
                    </p>
                </div>
                <button @click="showDraftAlert = false"
                    class="shrink-0 text-warning-400 hover:text-warning-600 dark:text-warning-500 dark:hover:text-warning-400 transition-colors"
                    title="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            @endif
        </div>
        @endif
    </div>

    {{-- Scrollable Content --}}
    <div class="flex-1 overflow-y-auto px-4 sm:px-6">
        {{-- Tasks List --}}
        @if(count($tasks) > 0)
        <div class="space-y-2 pb-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    Daftar Tugas
                </p>
                <a href=""
                    class="text-xs text-success-600 hover:text-success-700 dark:text-success-400 font-medium">
                    Lihat semua
                </a>
            </div>

            @foreach($tasks->take(10) as $task)
            @php
            $isInProgress = $task['status'] === 'in_progress';
            $isDraft = $task['status'] === 'draft';
            $isCompleted = $task['status'] === 'completed';
            $isHighPriority = in_array($task['priority'], ['urgent', 'high']);
            @endphp

            <div class="flex items-center gap-x-3 p-3 rounded-lg transition-all duration-200 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-750
                        @if($isInProgress) border-l-4 border-l-primary-500
                        @elseif($isDraft) border-l-4 border-l-warning-500
                        @elseif($isCompleted) border-l-4 border-l-success-500
                        @else border-l-4 border-l-gray-300 dark:border-l-gray-600
                        @endif">

                {{-- Status Indicator --}}
                <div class="shrink-0">
                    @if($isCompleted)
                    <div class="w-6 h-6 rounded bg-success-100 dark:bg-success-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-success-600 dark:text-success-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    @elseif($isInProgress)
                    <div class="w-6 h-6 rounded bg-primary-100 dark:bg-primary-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-primary-600 dark:text-primary-400 animate-pulse" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    @elseif($isDraft)
                    <div class="w-6 h-6 rounded bg-warning-100 dark:bg-warning-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    @else
                    <div class="w-6 h-6 rounded border-2 border-gray-300 dark:border-gray-600"></div>
                    @endif
                </div>

                {{-- Task Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-start gap-x-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate
                                        @if($isCompleted) text-gray-500 dark:text-gray-400 line-through
                                        @else text-gray-900 dark:text-white
                                        @endif">
                                {{ $task['title'] ?? 'Tugas Tanpa Nama' }}
                            </p>
                            @if($task['project'])
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                {{ $task['project']['name'] }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Status Badge & Priority --}}
                <div class="shrink-0 flex flex-col items-end gap-y-1">
                    {{-- Status Badge --}}
                    <span class="px-2 py-1 rounded text-xs font-semibold whitespace-nowrap
                                @if($isInProgress) bg-primary-100 text-primary-700 dark:bg-primary-500/20 dark:text-primary-400
                                @elseif($isDraft) bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400
                                @elseif($isCompleted) bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400
                                @else bg-gray-100 text-gray-700 dark:bg-gray-500/20 dark:text-gray-400
                                @endif">
                        @if($isInProgress)
                        ⚡ Berjalan
                        @elseif($isDraft)
                        📝 Draft
                        @elseif($isCompleted)
                        ✓ Selesai
                        @else
                        {{ ucfirst($task['status']) }}
                        @endif
                    </span>

                    {{-- Priority Indicator --}}
                    @if($isHighPriority && !$isCompleted)
                    <span class="flex items-center gap-x-1 px-2 py-0.5 rounded text-xs
                                    @if($task['priority'] === 'urgent') bg-danger-100 text-danger-700 dark:bg-danger-500/20 dark:text-danger-400
                                    @else bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400
                                    @endif">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        {{ ucfirst($task['priority']) }}
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Tidak ada tugas untuk hari ini
            </p>
        </div>
        @endif
    </div>

    {{-- Fixed Footer --}}
    @if(count($tasks) > 0)
    <div class="shrink-0 p-4 sm:p-6 pt-3 sm:pt-4">
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
                    <div class="flex items-center gap-x-1">
                        <div class="w-2 h-2 rounded-full bg-primary-500"></div>
                        <span class="text-gray-500 dark:text-gray-400">Berjalan</span>
                    </div>
                    <div class="flex items-center gap-x-1">
                        <div class="w-2 h-2 rounded-full bg-warning-500"></div>
                        <span class="text-gray-500 dark:text-gray-400">Draft</span>
                    </div>
                    <div class="flex items-center gap-x-1">
                        <div class="w-2 h-2 rounded-full bg-success-500"></div>
                        <span class="text-gray-500 dark:text-gray-400">Selesai</span>
                    </div>
                </div>
                <a href=""
                    class="text-xs text-success-600 hover:text-success-700 dark:text-success-400 font-medium inline-flex items-center gap-x-1">
                    Kelola tugas
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
    @endif
</div>