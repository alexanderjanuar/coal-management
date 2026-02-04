<div @if($isLive) wire:poll.30s @endif>

    {{-- Component Scoped Styles --}}
    <style>
        @keyframes activity-slide-in {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        .activity-row-enter {
            animation: activity-slide-in 0.3s ease-out;
        }
        .live-dot {
            animation: pulse-dot 2s ease-in-out infinite;
        }
        .activity-feed-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(156,163,175,0.3) transparent;
        }
        .activity-feed-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .activity-feed-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .activity-feed-scroll::-webkit-scrollbar-thumb {
            background: rgba(156,163,175,0.3);
            border-radius: 2px;
        }
        .activity-feed-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(156,163,175,0.5);
        }
    </style>

    {{-- Main Container --}}
    <div class="rounded-2xl bg-white dark:bg-gray-950 border border-gray-200/60 dark:border-gray-800/60 shadow-sm overflow-hidden">

        {{-- ============================================================== --}}
        {{-- HEADER BAR --}}
        {{-- ============================================================== --}}
        <div class="border-b border-gray-100 dark:border-gray-800/80">

            {{-- Top Row: Title + Live Indicator + Filters --}}
            <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                {{-- Left: Title Block --}}
                <div class="flex items-center gap-3">
                    {{-- Icon --}}
                    <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center ring-1 ring-gray-200/50 dark:ring-gray-700/50">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>
                    {{-- Text --}}
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 tracking-tight">Log Aktivitas</h3>
                            {{-- Live Indicator --}}
                            <button wire:click="toggleLive"
                                    class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium tracking-wide uppercase transition-all
                                    {{ $isLive
                                        ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900'
                                        : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-600' }}"
                                    title="{{ $isLive ? 'Pembaruan otomatis aktif — klik untuk jeda' : 'Pembaruan otomatis dijeda — klik untuk lanjutkan' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isLive ? 'bg-white dark:bg-gray-900 live-dot' : 'bg-gray-400 dark:bg-gray-600' }}"></span>
                                {{ $isLive ? 'Live' : 'Dijeda' }}
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-gray-600 mt-0.5">
                            {{ $this->totalCount }} aktivitas
                            <span class="text-gray-300 dark:text-gray-700 mx-1">/</span>
                            {{ $dateFilter === 'today' ? 'hari ini' : ($dateFilter === 'yesterday' ? 'kemarin' : ($dateFilter === 'week' ? 'minggu ini' : 'bulan ini')) }}
                        </p>
                    </div>
                </div>

                {{-- Right: Filters --}}
                <div class="flex items-center gap-2 flex-wrap">
                    {{-- Search --}}
                    <div class="relative">
                        <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 dark:text-gray-600 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                        </svg>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               placeholder="Cari..."
                               class="w-36 sm:w-44 text-xs rounded-lg border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 py-1.5 pl-8 pr-3 placeholder-gray-400 dark:placeholder-gray-600 focus:ring-1 focus:ring-gray-300 dark:focus:ring-gray-600 focus:border-gray-300 dark:focus:border-gray-600 transition-colors">
                    </div>

                    {{-- Date Filter Pills --}}
                    <div class="hidden sm:flex items-center gap-1 bg-gray-100 dark:bg-gray-900 rounded-lg p-0.5">
                        @foreach(['today' => 'Hari Ini', 'yesterday' => 'Kemarin', 'week' => 'Minggu', 'month' => 'Bulan'] as $value => $label)
                            <button wire:click="setDateFilter('{{ $value }}')"
                                    class="px-2.5 py-1 text-[11px] font-medium rounded-md transition-all
                                    {{ $dateFilter === $value
                                        ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm'
                                        : 'text-gray-500 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Mobile Date Filter --}}
                    <select wire:model.live="dateFilter"
                            class="sm:hidden text-xs rounded-lg border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 py-1.5 pl-3 pr-8 focus:ring-1 focus:ring-gray-300 dark:focus:ring-gray-600 focus:border-gray-300 dark:focus:border-gray-600">
                        <option value="today">Hari Ini</option>
                        <option value="yesterday">Kemarin</option>
                        <option value="week">Minggu Ini</option>
                        <option value="month">Bulan Ini</option>
                    </select>

                    {{-- User Filter --}}
                    <select wire:model.live="userFilter"
                            class="text-xs rounded-lg border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 py-1.5 pl-3 pr-8 focus:ring-1 focus:ring-gray-300 dark:focus:ring-gray-600 focus:border-gray-300 dark:focus:border-gray-600">
                        <option value="">Semua Pengguna</option>
                        @foreach($this->users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Tab Bar --}}
            <div class="px-5 flex gap-0.5">
                @foreach(['activity' => 'Daftar Aktivitas', 'statistics' => 'Statistik'] as $tabKey => $tabLabel)
                    <button wire:click="setTab('{{ $tabKey }}')"
                            class="relative px-4 py-2.5 text-xs font-medium tracking-wide uppercase transition-all
                            {{ $activeTab === $tabKey
                                ? 'text-gray-900 dark:text-gray-100'
                                : 'text-gray-400 dark:text-gray-600 hover:text-gray-600 dark:hover:text-gray-400' }}">
                        {{ $tabLabel }}
                        @if($activeTab === $tabKey)
                            <span class="absolute bottom-0 left-2 right-2 h-0.5 bg-gray-900 dark:bg-gray-100 rounded-full"></span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- TAB CONTENT --}}
        {{-- ============================================================== --}}
        <div>
            @if($activeTab === 'activity')
                {{-- ====================================================== --}}
                {{-- ACTIVITY TABLE --}}
                {{-- ====================================================== --}}

                {{-- Desktop Table (hidden on mobile) --}}
                <div class="hidden md:block">
                    <div class="activity-feed-scroll max-h-[520px] overflow-y-auto">
                        <table class="w-full">
                            <thead class="sticky top-0 z-10">
                                <tr class="bg-gray-50/95 dark:bg-gray-900/95 backdrop-blur-sm border-b border-gray-100 dark:border-gray-800/80">
                                    <th class="text-left py-2.5 px-5 text-[10px] font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-600 w-[180px]">Pengguna</th>
                                    <th class="text-left py-2.5 px-4 text-[10px] font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-600 w-[100px]">Aksi</th>
                                    <th class="text-left py-2.5 px-4 text-[10px] font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-600">Deskripsi</th>
                                    <th class="text-left py-2.5 px-4 text-[10px] font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-600 w-[140px]">Konteks</th>
                                    <th class="text-right py-2.5 px-5 text-[10px] font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-600 w-[120px]">Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($this->activities as $index => $activity)
                                    @php
                                        $isNew = $this->isRecent($activity->created_at);
                                        $actionLabel = $this->getActionLabel($activity->action);
                                    @endphp
                                    <tr class="group border-b border-gray-50 dark:border-gray-900 hover:bg-gray-50/70 dark:hover:bg-gray-900/50 transition-colors {{ $isNew ? 'activity-row-enter' : '' }}"
                                        wire:key="activity-{{ $activity->id }}">

                                        {{-- User Column --}}
                                        <td class="py-3 px-5">
                                            <div class="flex items-center gap-2.5">
                                                {{-- Avatar --}}
                                                <div class="w-7 h-7 rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400 ring-1 ring-gray-300/50 dark:ring-gray-700/50 shrink-0">
                                                    {{ substr($activity->user?->name ?? 'S', 0, 2) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate block">
                                                        {{ $activity->user?->name ?? 'Sistem' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Action Column --}}
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-2">
                                                {{-- Action Dot --}}
                                                <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $this->getActionDotColor($activity->action) }}"></span>
                                                {{-- Action Badge --}}
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider {{ $this->getActionBadgeColor($actionLabel) }}">
                                                    {{ $this->translateActionLabel($actionLabel) }}
                                                </span>
                                            </div>
                                        </td>

                                        {{-- Description Column --}}
                                        <td class="py-3 px-4">
                                            <p class="text-sm text-gray-600 dark:text-gray-400 truncate max-w-md group-hover:text-gray-900 dark:group-hover:text-gray-200 transition-colors" title="{{ $activity->description }}">
                                                {{ $activity->description }}
                                            </p>
                                        </td>

                                        {{-- Context Column (Client / Project) --}}
                                        <td class="py-3 px-4">
                                            @if($activity->client || $activity->project)
                                                <div class="flex flex-col gap-0.5">
                                                    @if($activity->client)
                                                        <span class="text-xs text-gray-500 dark:text-gray-500 truncate" title="{{ $activity->client->name }}">
                                                            {{ Str::limit($activity->client->name, 18) }}
                                                        </span>
                                                    @endif
                                                    @if($activity->project)
                                                        <span class="text-[10px] text-gray-400 dark:text-gray-600 truncate" title="{{ $activity->project->name }}">
                                                            {{ Str::limit($activity->project->name, 20) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-300 dark:text-gray-700 text-xs">—</span>
                                            @endif
                                        </td>

                                        {{-- Time Column --}}
                                        <td class="py-3 px-5 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if($isNew)
                                                    <span class="w-1 h-1 rounded-full bg-gray-900 dark:bg-gray-100 live-dot shrink-0"></span>
                                                @endif
                                                <span class="text-xs tabular-nums text-gray-400 dark:text-gray-600 font-mono whitespace-nowrap {{ $isNew ? 'text-gray-700 dark:text-gray-300 font-medium' : '' }}">
                                                    {{ $activity->created_at->diffForHumans(null, false, true) }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-16 text-center">
                                            <div class="flex flex-col items-center gap-3">
                                                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-gray-300 dark:text-gray-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-400 dark:text-gray-600">Tidak ada aktivitas</p>
                                                    <p class="text-xs text-gray-300 dark:text-gray-700 mt-0.5">Sesuaikan filter untuk melihat data aktivitas</p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Mobile Card Layout (hidden on desktop) --}}
                <div class="md:hidden activity-feed-scroll max-h-[480px] overflow-y-auto">
                    <div class="divide-y divide-gray-50 dark:divide-gray-900">
                        @forelse($this->activities as $activity)
                            @php
                                $isNew = $this->isRecent($activity->created_at);
                                $actionLabel = $this->getActionLabel($activity->action);
                            @endphp
                            <div class="px-4 py-3.5 hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition-colors {{ $isNew ? 'activity-row-enter' : '' }}"
                                 wire:key="activity-mobile-{{ $activity->id }}">

                                {{-- Top: User + Time --}}
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-[9px] font-bold uppercase text-gray-500 dark:text-gray-400">
                                            {{ substr($activity->user?->name ?? 'S', 0, 2) }}
                                        </div>
                                        <span class="text-xs font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $activity->user?->name ?? 'Sistem' }}
                                        </span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-semibold uppercase tracking-wider {{ $this->getActionBadgeColor($actionLabel) }}">
                                            {{ $this->translateActionLabel($actionLabel) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        @if($isNew)
                                            <span class="w-1 h-1 rounded-full bg-gray-900 dark:bg-gray-100 live-dot"></span>
                                        @endif
                                        <span class="text-[10px] tabular-nums font-mono text-gray-400 dark:text-gray-600">
                                            {{ $activity->created_at->diffForHumans(null, false, true) }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Description --}}
                                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed line-clamp-2">
                                    {{ $activity->description }}
                                </p>

                                {{-- Context --}}
                                @if($activity->client || $activity->project)
                                    <div class="flex items-center gap-2 mt-1.5">
                                        @if($activity->client)
                                            <span class="text-[10px] text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-gray-800/50 px-1.5 py-0.5 rounded">
                                                {{ Str::limit($activity->client->name, 20) }}
                                            </span>
                                        @endif
                                        @if($activity->project)
                                            <span class="text-[10px] text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-gray-800/50 px-1.5 py-0.5 rounded">
                                                {{ Str::limit($activity->project->name, 20) }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs text-gray-400 dark:text-gray-600">Tidak ada aktivitas</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Load More Footer --}}
                @if($this->activities->count() >= $limit)
                    <div class="border-t border-gray-100 dark:border-gray-800/80 px-5 py-3">
                        <button wire:click="loadMore"
                                wire:loading.attr="disabled"
                                class="w-full flex items-center justify-center gap-2 py-2 text-xs font-medium text-gray-500 dark:text-gray-500 hover:text-gray-900 dark:hover:text-gray-100 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-all">
                            <span wire:loading.remove wire:target="loadMore">
                                Muat lebih banyak
                            </span>
                            <span wire:loading wire:target="loadMore" class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memuat...
                            </span>
                            <span class="text-gray-300 dark:text-gray-700 text-[10px] font-mono" wire:loading.remove wire:target="loadMore">
                                ({{ $this->activities->count() }} / {{ $this->totalCount }})
                            </span>
                        </button>
                    </div>
                @endif

            @else
                {{-- ====================================================== --}}
                {{-- STATISTICS TAB --}}
                {{-- ====================================================== --}}
                <div class="p-5 space-y-6">

                    {{-- Total Activities Banner --}}
                    <div class="text-center py-5 px-6 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800/50">
                        <p class="text-4xl font-bold text-gray-900 dark:text-gray-100 tabular-nums tracking-tight">{{ $this->statisticsData['total'] }}</p>
                        <p class="text-xs font-medium uppercase tracking-widest text-gray-400 dark:text-gray-600 mt-1.5">Total Aktivitas</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                        {{-- Activity by User --}}
                        <div class="rounded-xl border border-gray-100 dark:border-gray-800/50 overflow-hidden">
                            <div class="px-4 py-3 bg-gray-50/80 dark:bg-gray-900/30 border-b border-gray-100 dark:border-gray-800/50">
                                <h4 class="text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-500">Per Pengguna</h4>
                            </div>
                            <div class="divide-y divide-gray-50 dark:divide-gray-900">
                                @forelse($this->statisticsData['user_stats'] as $index => $stat)
                                    <div class="px-4 py-3 flex items-center justify-between hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition-colors group">
                                        <div class="flex items-center gap-3">
                                            {{-- Rank --}}
                                            <span class="w-5 text-right text-[10px] font-mono text-gray-300 dark:text-gray-700 tabular-nums">{{ $index + 1 }}.</span>
                                            {{-- Avatar --}}
                                            <div class="w-7 h-7 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400 ring-1 ring-gray-200/50 dark:ring-gray-700/50">
                                                {{ substr($stat->user?->name ?? 'S', 0, 2) }}
                                            </div>
                                            <span class="text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100 transition-colors">
                                                {{ $stat->user?->name ?? 'Sistem' }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            {{-- Mini bar --}}
                                            @php
                                                $maxActivity = $this->statisticsData['user_stats']->max('total_activities');
                                                $barWidth = $maxActivity > 0 ? ($stat->total_activities / $maxActivity) * 100 : 0;
                                            @endphp
                                            <div class="w-16 h-1 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden hidden sm:block">
                                                <div class="h-full bg-gray-400 dark:bg-gray-500 rounded-full transition-all" style="width: {{ $barWidth }}%"></div>
                                            </div>
                                            <span class="text-sm font-bold text-gray-900 dark:text-gray-100 tabular-nums font-mono min-w-[2rem] text-right">
                                                {{ $stat->total_activities }}
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-4 py-8 text-center text-xs text-gray-400 dark:text-gray-600">Tidak ada data</div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Activity by Type --}}
                        <div class="rounded-xl border border-gray-100 dark:border-gray-800/50 overflow-hidden">
                            <div class="px-4 py-3 bg-gray-50/80 dark:bg-gray-900/30 border-b border-gray-100 dark:border-gray-800/50">
                                <h4 class="text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-500">Per Tipe</h4>
                            </div>
                            <div class="divide-y divide-gray-50 dark:divide-gray-900">
                                @forelse($this->statisticsData['action_stats'] as $stat)
                                    <div class="px-4 py-3 flex items-center justify-between hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition-colors">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $this->getActionColor(strtolower($stat->action_type)) }}"></span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider {{ $this->getActionBadgeColor($stat->action_type) }}">
                                                {{ $this->translateActionLabel($stat->action_type) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @php
                                                $maxCount = $this->statisticsData['action_stats']->max('count');
                                                $typeBarWidth = $maxCount > 0 ? ($stat->count / $maxCount) * 100 : 0;
                                            @endphp
                                            <div class="w-16 h-1 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden hidden sm:block">
                                                <div class="h-full bg-gray-400 dark:bg-gray-500 rounded-full transition-all" style="width: {{ $typeBarWidth }}%"></div>
                                            </div>
                                            <span class="text-sm font-bold text-gray-900 dark:text-gray-100 tabular-nums font-mono min-w-[2rem] text-right">
                                                {{ $stat->count }}
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-4 py-8 text-center text-xs text-gray-400 dark:text-gray-600">Tidak ada data</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ============================================================== --}}
        {{-- BOTTOM STATUS BAR --}}
        {{-- ============================================================== --}}
        <div class="border-t border-gray-100 dark:border-gray-800/80 bg-gray-50/50 dark:bg-gray-900/30 px-5 py-2 flex items-center justify-between">
            <div class="flex items-center gap-3 text-[10px] text-gray-400 dark:text-gray-600 font-mono">
                <span>{{ $this->totalCount }} data</span>
                <span class="text-gray-200 dark:text-gray-800">|</span>
                <span>
                    @if($this->latestTimestamp)
                        Terakhir: {{ \Carbon\Carbon::parse($this->latestTimestamp)->diffForHumans(null, false, true) }}
                    @else
                        Tidak ada data
                    @endif
                </span>
            </div>
            <div class="flex items-center gap-1.5 text-[10px] text-gray-400 dark:text-gray-600 font-mono">
                @if($isLive)
                    <span class="w-1 h-1 rounded-full bg-gray-400 dark:bg-gray-600 live-dot"></span>
                    <span>Polling 30d</span>
                @else
                    <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                    <span>Dijeda</span>
                @endif
            </div>
        </div>

    </div>
</div>
