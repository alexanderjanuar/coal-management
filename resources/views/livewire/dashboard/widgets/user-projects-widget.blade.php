<div x-data="{ 
        showOverdueAlert: true, 
        showDueSoonAlert: true 
    }"
    class="fi-wi-stats-overview-stat relative rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 hover:shadow-md transition-all duration-200 flex flex-col h-full">

    {{-- Fixed Header --}}
    <div class="p-4 sm:p-6 pb-3 sm:pb-4 shrink-0">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-x-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary-50 dark:bg-primary-500/10">
                    <x-filament::icon icon="heroicon-o-folder-open"
                        class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Proyek Saya
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $stats['total'] }} proyek ditugaskan
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

        {{-- Role Stats Grid --}}
        <div class="grid grid-cols-2 gap-3 mb-4">
            {{-- PIC Projects --}}
            <div
                class="p-3 rounded-lg bg-primary-50 dark:bg-primary-500/10 border border-primary-100 dark:border-primary-500/20">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-primary-600 dark:text-primary-400">
                            Sebagai PIC
                        </p>
                        <p class="text-2xl font-bold text-primary-700 dark:text-primary-300 mt-1">
                            {{ $stats['pic_count'] }}
                        </p>
                    </div>
                    <div
                        class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Member Projects --}}
            <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">
                            Sebagai Anggota
                        </p>
                        <p class="text-2xl font-bold text-gray-700 dark:text-gray-300 mt-1">
                            {{ $stats['member_count'] }}
                        </p>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Deadline Alerts --}}
        @if($stats['overdue_count'] > 0 || $stats['due_soon_count'] > 0)
        <div class="mb-4 space-y-2">
            {{-- Overdue Alert --}}
            @if($stats['overdue_count'] > 0)
            <div x-show="showOverdueAlert" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="flex items-center gap-x-2 p-3 rounded-lg bg-danger-50 dark:bg-danger-500/10 border border-danger-200 dark:border-danger-500/20">
                <div class="shrink-0">
                    <div
                        class="w-8 h-8 rounded-full bg-danger-100 dark:bg-danger-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-danger-600 dark:text-danger-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-danger-800 dark:text-danger-300">
                        {{ $stats['overdue_count'] }} Proyek Terlambat
                    </p>
                    <p class="text-xs text-danger-600 dark:text-danger-400">
                        Melewati batas waktu - perlu perhatian segera
                    </p>
                </div>
                <button @click="showOverdueAlert = false"
                    class="shrink-0 text-danger-400 hover:text-danger-600 dark:text-danger-500 dark:hover:text-danger-400 transition-colors"
                    title="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            @endif

            {{-- Due Soon Alert --}}
            @if($stats['due_soon_count'] > 0)
            <div x-show="showDueSoonAlert" x-transition:enter="transition ease-out duration-200"
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
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-warning-800 dark:text-warning-300">
                        {{ $stats['due_soon_count'] }} Proyek Jatuh Tempo dalam 7 Hari
                    </p>
                    <p class="text-xs text-warning-600 dark:text-warning-400">
                        Mendekati batas waktu
                    </p>
                </div>
                <button @click="showDueSoonAlert = false"
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
        {{-- Recent Projects List --}}
        @if(count($projects) > 0)
        <div class="space-y-2 pb-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    Proyek Terbaru
                </p>
                <a href="{{ route('filament.admin.resources.projects.index') }}"
                    class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium">
                    Lihat semua
                </a>
            </div>

            @foreach($projects->take(3) as $project)
            @php
            $dueDate = isset($project['due_date']) ? \Carbon\Carbon::parse($project['due_date']) : null;
            $isOverdue = $dueDate && $dueDate->isPast();
            $isDueSoon = $dueDate && $dueDate->isFuture() && $dueDate->diffInDays(now()) <= 7; $daysUntilDue=$dueDate ?
                $dueDate->diffInDays(now()) : null;
                @endphp

                <div
                    class="flex items-center gap-x-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-750 transition-colors">
                    {{-- Role Badge --}}
                    <div class="shrink-0">
                        @if($project['is_pic'])
                        <div class="w-6 h-6 rounded bg-primary-100 dark:bg-primary-500/20 flex items-center justify-center"
                            title="Anda adalah PIC">
                            <svg class="w-3.5 h-3.5 text-primary-600 dark:text-primary-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        </div>
                        @else
                        <div class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 flex items-center justify-center"
                            title="Anggota Tim">
                            <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        @endif
                    </div>

                    {{-- Project Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            {{ $project['name'] ?? 'Proyek Tanpa Nama' }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ $project['client']['name'] ?? 'Tidak Ada Klien' }}
                        </p>
                    </div>

                    {{-- Deadline Status --}}
                    <div class="shrink-0">
                        @if($dueDate)
                        @if($isOverdue)
                        <div class="flex flex-col items-end gap-y-1">
                            <div
                                class="flex items-center gap-x-1 px-2 py-1 rounded bg-danger-100 dark:bg-danger-500/20">
                                <svg class="w-3 h-3 text-danger-600 dark:text-danger-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span
                                    class="text-xs font-semibold text-danger-700 dark:text-danger-400 whitespace-nowrap">
                                    Terlambat {{ $daysUntilDue }}h
                                </span>
                            </div>
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                {{ $dueDate->format('d M Y') }}
                            </span>
                        </div>
                        @elseif($isDueSoon)
                        <div class="flex flex-col items-end gap-y-1">
                            <div
                                class="flex items-center gap-x-1 px-2 py-1 rounded bg-warning-100 dark:bg-warning-500/20">
                                <svg class="w-3 h-3 text-warning-600 dark:text-warning-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span
                                    class="text-xs font-semibold text-warning-700 dark:text-warning-400 whitespace-nowrap">
                                    {{ $daysUntilDue }}h lagi
                                </span>
                            </div>
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                {{ $dueDate->format('d M Y') }}
                            </span>
                        </div>
                        @else
                        <div class="flex flex-col items-end gap-y-1">
                            <span class="text-xs text-gray-600 dark:text-gray-400">
                                {{ $dueDate->format('d M Y') }}
                            </span>
                            @if($dueDate->isFuture())
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                {{ $daysUntilDue }} hari lagi
                            </span>
                            @endif
                        </div>
                        @endif
                        @else
                        <span class="text-xs text-gray-400 dark:text-gray-500">
                            Tidak ada deadline
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
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Tidak ada proyek yang ditugaskan
            </p>
        </div>
        @endif
    </div>

    {{-- Fixed Footer --}}
    @if(count($projects) > 0)
    <div class="shrink-0 p-4 sm:p-6 pt-3 sm:pt-4">
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
                    <span class="inline-flex items-center gap-x-1">
                        <div class="w-2 h-2 rounded-full bg-primary-500"></div>
                        <span class="text-gray-500 dark:text-gray-400">PIC</span>
                    </span>
                    <span class="text-gray-300 dark:text-gray-600">•</span>
                    <span class="text-gray-500 dark:text-gray-400">
                        Menampilkan {{ min(count($projects), 3) }} dari {{ count($projects) }}
                    </span>
                </div>
                <a href="{{ route('filament.admin.resources.projects.index') }}"
                    class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium inline-flex items-center gap-x-1">
                    Kelola proyek
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
    @endif
</div>