<div x-data="{
        tab: @entangle('activeTab'),
        showDeadlineAlert: true
    }"
    class="rounded-2xl bg-white dark:bg-gray-950 border border-gray-200/80 dark:border-gray-800/80 shadow-sm hover:shadow-md transition-shadow duration-300 h-full flex flex-col">

    {{-- ═══════════════════════════════════════════════════════════════
         HEADER
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="px-6 sm:px-8 pt-6 sm:pt-8 pb-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-950/50 border border-primary-100 dark:border-primary-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 tracking-tight">
                        Project Command Center
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-0.5">
                        {{ $pipeline['total'] }} proyek &middot; {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                {{-- Year Filter --}}
                <div class="relative">
                    <select wire:model.live="year"
                        class="appearance-none pl-3 pr-7 py-1.5 text-xs font-medium rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 cursor-pointer transition-all">
                        @foreach($availableYears as $yr)
                            <option value="{{ $yr }}">{{ $yr }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </div>
                </div>

                {{-- Tab Navigation --}}
                <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-800 p-0.5 bg-gray-50 dark:bg-gray-900">
                    <button wire:click="switchTab('overview')"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition-all duration-200
                        {{ $activeTab === 'overview'
                            ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm border border-gray-200 dark:border-gray-700'
                            : 'text-gray-500 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                        Ringkasan
                    </button>
                    <button wire:click="switchTab('team')"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition-all duration-200
                        {{ $activeTab === 'team'
                            ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm border border-gray-200 dark:border-gray-700'
                            : 'text-gray-500 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                        Tim
                    </button>
                </div>

                {{-- Refresh --}}
                <button wire:click="$refresh"
                    class="w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-800 flex items-center justify-center text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:border-primary-200 dark:hover:border-primary-800 transition-all"
                    title="Muat Ulang">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════
             PIPELINE — horizontal status flow
        ═══════════════════════════════════════════════════════════════ --}}
        <div class="flex items-stretch gap-0 mb-6 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden bg-gray-50/50 dark:bg-gray-900/50">
            @php
                $stageKeys = array_keys($pipeline['stages']);
                $lastKey = end($stageKeys);
            @endphp
            @foreach($pipeline['stages'] as $key => $stage)
                @php
                    $isActive = $stage['count'] > 0;
                    $pct = $pipeline['total'] > 0 ? round(($stage['count'] / $pipeline['total']) * 100) : 0;
                @endphp
                <div class="flex-1 relative group text-center py-4 px-2 transition-colors duration-200
                    {{ $isActive ? 'bg-white dark:bg-gray-900' : '' }}
                    {{ $key !== $lastKey ? 'border-r border-gray-200 dark:border-gray-800' : '' }}">

                    <p class="text-2xl font-bold tabular-nums tracking-tight {{ $isActive ? 'text-gray-900 dark:text-gray-100' : 'text-gray-300 dark:text-gray-700' }}">
                        {{ $stage['count'] }}
                    </p>
                    <p class="text-[10px] uppercase tracking-widest font-medium mt-1 {{ $isActive ? 'text-gray-500 dark:text-gray-400' : 'text-gray-300 dark:text-gray-700' }}">
                        {{ $stage['label'] }}
                    </p>

                    {{-- Subtle underline bar --}}
                    @if($isActive)
                    <div class="absolute bottom-0 left-1/2 -translate-x-1/2 h-0.5 rounded-full bg-primary-500 dark:bg-primary-400 transition-all duration-300" style="width: {{ max($pct, 20) }}%"></div>
                    @endif

                    {{-- Connector arrow --}}
                    @if($key !== $lastKey)
                    <div class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-1/2 z-10 w-5 h-5 rounded-full bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center">
                        <svg class="w-2.5 h-2.5 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         OVERDUE / DEADLINE ALERT
    ═══════════════════════════════════════════════════════════════ --}}
    @php $overdueCount = $deadlines->where('is_overdue', true)->count(); @endphp
    @if($overdueCount > 0)
    <div class="px-6 sm:px-8 mb-4">
        <div x-show="showDeadlineAlert"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="w-8 h-8 rounded-lg bg-gray-200 dark:bg-gray-800 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                    {{ $overdueCount }} proyek melewati deadline
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-500">
                    Membutuhkan perhatian segera
                </p>
            </div>
            <button @click="showDeadlineAlert = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════
         TAB: OVERVIEW
    ═══════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'overview'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="flex-1">
        <div class="px-6 sm:px-8 pb-6 sm:pb-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- ─── COLUMN 1: Active Projects ─── --}}
                <div class="lg:col-span-1">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-600">
                            Proyek Aktif
                        </h3>
                        <a href="{{ route('filament.admin.resources.projects.index') }}"
                            class="text-xs text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                            Lihat semua
                        </a>
                    </div>

                    @if($projects->count() > 0)
                    <div class="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                        @foreach($projects as $project)
                        <a href="{{ route('filament.admin.resources.projects.view', $project['id']) }}"
                           class="block group p-4 rounded-xl border border-gray-100 dark:border-gray-800/80 hover:border-gray-300 dark:hover:border-gray-700 bg-white dark:bg-gray-900/50 hover:bg-gray-50/80 dark:hover:bg-gray-900 transition-all duration-200">
                            {{-- Project header --}}
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1 min-w-0 mr-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate group-hover:text-gray-700 dark:group-hover:text-white transition-colors">
                                        {{ $project['name'] }}
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-600 truncate mt-0.5">
                                        {{ $project['client'] }}
                                    </p>
                                </div>
                                <span class="shrink-0 text-xs font-medium tabular-nums
                                    {{ $project['is_overdue'] ? 'text-gray-900 dark:text-gray-100 font-semibold' : 'text-gray-400 dark:text-gray-600' }}">
                                    @if($project['is_overdue'])
                                        {{ abs($project['days_left']) }}h terlambat
                                    @elseif($project['days_left'] !== null)
                                        {{ $project['days_left'] }}h lagi
                                    @endif
                                </span>
                            </div>

                            {{-- Progress bar --}}
                            <div class="mb-3">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-[11px] font-medium tabular-nums text-gray-500 dark:text-gray-500">
                                        {{ $project['progress'] }}%
                                    </span>
                                    <span class="text-[11px] text-gray-400 dark:text-gray-600 tabular-nums">
                                        {{ $project['completed_items'] }}/{{ $project['total_items'] }}
                                    </span>
                                </div>
                                <div class="w-full h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500
                                        {{ $project['progress'] >= 75 ? 'bg-primary-500 dark:bg-primary-400' :
                                           ($project['progress'] >= 40 ? 'bg-gray-400 dark:bg-gray-500' : 'bg-gray-300 dark:bg-gray-600') }}"
                                        style="width: {{ $project['progress'] }}%"></div>
                                </div>
                            </div>

                            {{-- Footer: members + status --}}
                            <div class="flex items-center justify-between">
                                <div class="flex items-center -space-x-1.5">
                                    @foreach($project['members'] as $member)
                                        @if($member['avatar_url'])
                                        <img src="{{ $member['avatar_url'] }}" alt="{{ $member['name'] }}"
                                            class="w-5 h-5 rounded-full border border-white dark:border-gray-900 object-cover"
                                            title="{{ $member['name'] }}">
                                        @else
                                        <div class="w-5 h-5 rounded-full border border-white dark:border-gray-900 bg-gray-200 dark:bg-gray-700 flex items-center justify-center"
                                            title="{{ $member['name'] }}">
                                            <span class="text-[9px] font-semibold text-gray-500 dark:text-gray-400">{{ $member['initials'] }}</span>
                                        </div>
                                        @endif
                                    @endforeach
                                    @if($project['extra_members'] > 0)
                                    <div class="w-5 h-5 rounded-full border border-white dark:border-gray-900 bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                        <span class="text-[9px] font-medium text-gray-400">+{{ $project['extra_members'] }}</span>
                                    </div>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2">
                                    @if($project['priority'] === 'urgent')
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-900 dark:bg-gray-100" title="Urgent"></span>
                                    @endif
                                    <span class="text-[10px] uppercase tracking-wider font-medium text-gray-400 dark:text-gray-600">
                                        {{ match($project['status']) {
                                            'draft' => 'Draft',
                                            'analysis' => 'Analisis',
                                            'in_progress' => 'Berjalan',
                                            'review' => 'Review',
                                            default => ucfirst($project['status']),
                                        } }}
                                    </span>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @else
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-gray-300 dark:text-gray-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/>
                            </svg>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-gray-600">Tidak ada proyek aktif</p>
                    </div>
                    @endif
                </div>

                {{-- ─── COLUMN 2: Deadlines ─── --}}
                <div class="lg:col-span-1">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-600">
                            Deadline 14 Hari
                        </h3>
                        <span class="text-xs tabular-nums font-medium text-gray-400 dark:text-gray-600">
                            {{ $deadlines->count() }} proyek
                        </span>
                    </div>

                    @if($deadlines->count() > 0)
                    <div class="space-y-1.5 max-h-[420px] overflow-y-auto pr-1">
                        @foreach($deadlines as $dl)
                        <div class="flex items-center gap-3 p-3 rounded-xl transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-900/80 group">
                            {{-- Date block --}}
                            <div class="shrink-0 w-11 text-center">
                                <p class="text-base font-bold tabular-nums {{ $dl['is_overdue'] ? 'text-gray-900 dark:text-gray-100' : 'text-gray-600 dark:text-gray-400' }}">
                                    {{ \Illuminate\Support\Str::before($dl['due_date'], ' ') }}
                                </p>
                                <p class="text-[10px] uppercase tracking-wider font-medium {{ $dl['is_overdue'] ? 'text-gray-600 dark:text-gray-400' : 'text-gray-400 dark:text-gray-600' }}">
                                    {{ \Illuminate\Support\Str::after($dl['due_date'], ' ') }}
                                </p>
                            </div>

                            {{-- Vertical line --}}
                            <div class="shrink-0 w-px h-10 {{ $dl['is_overdue'] ? 'bg-gray-400 dark:bg-gray-500' : 'bg-gray-200 dark:bg-gray-800' }}"></div>

                            {{-- Project info --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-800 dark:text-gray-200 truncate {{ $dl['is_overdue'] ? 'font-semibold' : 'font-medium' }}">
                                    {{ $dl['name'] }}
                                </p>
                                <p class="text-[11px] text-gray-400 dark:text-gray-600 truncate mt-0.5">
                                    {{ $dl['client'] }}
                                </p>
                            </div>

                            {{-- Days badge --}}
                            <div class="shrink-0">
                                @if($dl['is_overdue'])
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-[11px] font-semibold tabular-nums bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900">
                                    {{ abs($dl['days_left']) }}h
                                </span>
                                @elseif($dl['days_left'] <= 3)
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-[11px] font-semibold tabular-nums bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                    {{ $dl['days_left'] }}h
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-[11px] font-medium tabular-nums text-gray-400 dark:text-gray-600">
                                    {{ $dl['days_left'] }}h
                                </span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-gray-300 dark:text-gray-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                            </svg>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-gray-600">Tidak ada deadline mendatang</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         TAB: TEAM WORKLOAD
    ═══════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'team'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak class="flex-1">
        <div class="px-6 sm:px-8 pb-6 sm:pb-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-600">
                    Beban Kerja Tim
                </h3>
                <span class="text-xs text-gray-400 dark:text-gray-600">
                    {{ $workload->count() }} anggota &middot; {{ $workload->where('is_online', true)->count() }} online
                </span>
            </div>

            @if($workload->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach($workload as $member)
                <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-800/80 hover:border-gray-300 dark:hover:border-gray-700 bg-white dark:bg-gray-900/50 transition-all duration-200 group">
                    {{-- Member header --}}
                    <div class="flex items-center gap-3 mb-4">
                        <div class="relative shrink-0">
                            @if($member['avatar_url'])
                            <img src="{{ $member['avatar_url'] }}" alt="{{ $member['name'] }}"
                                class="w-9 h-9 rounded-full object-cover border border-gray-200 dark:border-gray-700">
                            @else
                            <div class="w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ $member['initials'] }}</span>
                            </div>
                            @endif
                            {{-- Online dot --}}
                            <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white dark:border-gray-900
                                {{ $member['is_online'] ? 'bg-primary-500 dark:bg-primary-400' : 'bg-gray-300 dark:bg-gray-700' }}"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                {{ $member['name'] }}
                            </p>
                            <p class="text-[10px] uppercase tracking-wider font-medium text-gray-400 dark:text-gray-600">
                                {{ ucfirst($member['role']) }}
                            </p>
                        </div>
                    </div>

                    {{-- Stats grid --}}
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <p class="text-[10px] uppercase tracking-wider font-medium text-gray-400 dark:text-gray-600 mb-0.5">Tugas</p>
                            <p class="text-lg font-bold tabular-nums text-gray-900 dark:text-gray-100">
                                {{ $member['tasks_completed'] }}<span class="text-gray-300 dark:text-gray-700">/</span><span class="text-sm font-normal text-gray-400 dark:text-gray-600">{{ $member['tasks_total'] }}</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider font-medium text-gray-400 dark:text-gray-600 mb-0.5">Proyek</p>
                            <p class="text-lg font-bold tabular-nums text-gray-900 dark:text-gray-100">
                                {{ $member['active_projects'] }}
                            </p>
                        </div>
                    </div>

                    {{-- Completion bar --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[10px] font-medium text-gray-400 dark:text-gray-600">Penyelesaian</span>
                            <span class="text-[11px] font-semibold tabular-nums text-gray-600 dark:text-gray-400">{{ $member['completion_rate'] }}%</span>
                        </div>
                        <div class="w-full h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500
                                {{ $member['completion_rate'] >= 75 ? 'bg-primary-500 dark:bg-primary-400' :
                                   ($member['completion_rate'] >= 40 ? 'bg-gray-400 dark:bg-gray-500' : 'bg-gray-300 dark:bg-gray-600') }}"
                                style="width: {{ $member['completion_rate'] }}%"></div>
                        </div>
                    </div>

                    {{-- Last seen --}}
                    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800/80">
                        <p class="text-[10px] text-gray-400 dark:text-gray-600">
                            @if($member['is_online'])
                                <span class="font-medium text-primary-600 dark:text-primary-400">Online sekarang</span>
                            @elseif($member['last_seen'])
                                Terakhir {{ \Carbon\Carbon::parse($member['last_seen'])->diffForHumans() }}
                            @else
                                Belum ada aktivitas
                            @endif
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-gray-300 dark:text-gray-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-400 dark:text-gray-600">Tidak ada data tim</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         FOOTER
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="px-6 sm:px-8 py-4 border-t border-gray-100 dark:border-gray-800/80 mt-auto">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex items-center gap-4 text-[11px] text-gray-400 dark:text-gray-600">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-1 rounded-full bg-primary-500 dark:bg-primary-400"></span>
                    &ge;75%
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-1 rounded-full bg-gray-400 dark:bg-gray-500"></span>
                    40-74%
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                    &lt;40%
                </span>
            </div>
            <a href="{{ route('filament.admin.resources.projects.index') }}"
                class="text-[11px] font-medium text-gray-400 hover:text-primary-600 dark:text-gray-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-1">
                Kelola semua proyek
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                </svg>
            </a>
        </div>
    </div>
</div>
