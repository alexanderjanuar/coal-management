<div
    class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 hover:shadow-md transition-all duration-200">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-x-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary-50 dark:bg-primary-500/10">
                <x-filament::icon icon="heroicon-o-user-group" class="h-5 w-5 text-primary-600 dark:text-primary-400" />
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    @if($currentUserRole === 'director')
                    Aktivitas Tim
                    @elseif($currentUserRole === 'manager')
                    Aktivitas Staff
                    @else
                    Aktivitas Saya
                    @endif
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $teamStats['total_users'] }} {{ $teamStats['total_users'] > 1 ? 'anggota' : 'anggota' }}
                    • {{ $teamStats['online_users'] }} online
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

    {{-- Overall Stats Grid --}}
    <div class="grid grid-cols-3 gap-3 mb-4">
        {{-- Today's Tasks --}}
        <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20">
            <div class="text-center">
                <p class="text-xs font-medium text-blue-600 dark:text-blue-400 mb-1">
                    Tugas Hari Ini
                </p>
                <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                    {{ $teamStats['total_tasks_today'] }}
                </p>
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                    {{ $teamStats['completed_today'] }} selesai
                </p>
            </div>
        </div>

        {{-- In Progress --}}
        <div
            class="p-3 rounded-lg bg-orange-50 dark:bg-orange-500/10 border border-orange-100 dark:border-orange-500/20">
            <div class="text-center">
                <p class="text-xs font-medium text-orange-600 dark:text-orange-400 mb-1">
                    Sedang Berjalan
                </p>
                <p class="text-2xl font-bold text-orange-700 dark:text-orange-300">
                    {{ $teamStats['in_progress_today'] }}
                </p>
                <p class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                    aktif dikerjakan
                </p>
            </div>
        </div>

        {{-- Completion Rate --}}
        <div
            class="p-3 rounded-lg bg-success-50 dark:bg-success-500/10 border border-success-100 dark:border-success-500/20">
            <div class="text-center">
                <p class="text-xs font-medium text-success-600 dark:text-success-400 mb-1">
                    Rata-rata
                </p>
                <p class="text-2xl font-bold text-success-700 dark:text-success-300">
                    {{ $teamStats['avg_completion_rate'] }}%
                </p>
                <p class="text-xs text-success-600 dark:text-success-400 mt-1">
                    minggu ini
                </p>
            </div>
        </div>
    </div>

    {{-- User Activity List --}}
    <div class="space-y-2">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                @if($currentUserRole === 'director')
                Daftar Tim
                @elseif($currentUserRole === 'manager')
                Daftar Staff
                @else
                Detail Aktivitas
                @endif
            </p>
        </div>

        @forelse($activityStats->take(5) as $stat)
        <div
            class="flex items-center gap-x-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-750 transition-colors">
            {{-- User Avatar & Online Status --}}
            <div class="shrink-0 relative">
                @if($stat['user']['avatar_url'])
                <img src="{{ $stat['user']['avatar_url'] }}" alt="{{ $stat['user']['name'] }}"
                    class="w-10 h-10 rounded-full object-cover">
                @else
                <div class="w-10 h-10 rounded-full flex items-center justify-center
                            @if($stat['user']['role'] === 'director') bg-primary-100 dark:bg-primary-500/20
                            @elseif($stat['user']['role'] === 'manager') bg-secondary-100 dark:bg-secondary-500/20
                            @else bg-gray-100 dark:bg-gray-500/20
                            @endif">
                    <span class="font-semibold text-sm
                                @if($stat['user']['role'] === 'director') text-primary-700 dark:text-primary-400
                                @elseif($stat['user']['role'] === 'manager') text-secondary-700 dark:text-secondary-400
                                @else text-gray-700 dark:text-gray-400
                                @endif">
                        {{ strtoupper(substr($stat['user']['name'], 0, 2)) }}
                    </span>
                </div>
                @endif

                {{-- Online Indicator --}}
                <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2 border-white dark:border-gray-800
                        @if($stat['activity']['is_online']) bg-success-500
                        @else bg-gray-400
                        @endif">
                </div>
            </div>

            {{-- User Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-x-2 mb-0.5">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ $stat['user']['name'] }}
                    </p>
                    <span class="shrink-0 px-1.5 py-0.5 rounded text-xs font-medium
                            @if($stat['user']['role'] === 'director') bg-primary-100 text-primary-700 dark:bg-primary-500/20 dark:text-primary-400
                            @elseif($stat['user']['role'] === 'manager') bg-secondary-100 text-secondary-700 dark:bg-secondary-500/20 dark:text-secondary-400
                            @else bg-gray-100 text-gray-700 dark:bg-gray-500/20 dark:text-gray-400
                            @endif">
                        {{ ucfirst($stat['user']['role']) }}
                    </span>
                </div>

                {{-- Last Activity --}}
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    @if($stat['activity']['is_online'])
                    <span class="text-success-600 dark:text-success-400 font-medium">● Online</span>
                    @elseif($stat['activity']['last_seen'])
                    Terakhir aktif {{ \Carbon\Carbon::parse($stat['activity']['last_seen'])->diffForHumans() }}
                    @else
                    Belum ada aktivitas
                    @endif
                </p>
            </div>

            {{-- Stats Summary --}}
            <div class="shrink-0 text-right">
                <div class="flex items-center gap-x-2 mb-1">
                    {{-- Today's Completed --}}
                    <div class="flex items-center gap-x-1">
                        <svg class="w-3.5 h-3.5 text-success-600 dark:text-success-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-xs font-semibold text-success-700 dark:text-success-400">
                            {{ $stat['today']['completed'] }}
                        </span>
                    </div>

                    {{-- Today's In Progress --}}
                    @if($stat['today']['in_progress'] > 0)
                    <div class="flex items-center gap-x-1">
                        <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-400">
                            {{ $stat['today']['in_progress'] }}
                        </span>
                    </div>
                    @endif

                    {{-- Today's Pending --}}
                    @if($stat['today']['pending'] > 0)
                    <div class="flex items-center gap-x-1">
                        <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                            {{ $stat['today']['pending'] }}
                        </span>
                    </div>
                    @endif
                </div>

                {{-- Week Completion Rate --}}
                <div class="flex items-center justify-end gap-x-1">
                    <div class="w-16 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-success-500 rounded-full transition-all duration-300"
                            style="width: {{ $stat['week']['completion_rate'] }}%">
                        </div>
                    </div>
                    <span class="text-xs text-gray-600 dark:text-gray-400">
                        {{ $stat['week']['completion_rate'] }}%
                    </span>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Tidak ada data aktivitas
            </p>
        </div>
        @endforelse
    </div>

    {{-- Footer --}}
    @if($activityStats->count() > 0)
    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-x-3 text-xs">
                <div class="flex items-center gap-x-1">
                    <div class="w-2 h-2 rounded-full bg-success-500"></div>
                    <span class="text-gray-500 dark:text-gray-400">Selesai</span>
                </div>
                <div class="flex items-center gap-x-1">
                    <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                    <span class="text-gray-500 dark:text-gray-400">Berjalan</span>
                </div>
                <div class="flex items-center gap-x-1">
                    <div class="w-2 h-2 rounded-full bg-gray-500"></div>
                    <span class="text-gray-500 dark:text-gray-400">Pending</span>
                </div>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                Update otomatis setiap 5 menit
            </div>
        </div>
    </div>
    @endif
</div>