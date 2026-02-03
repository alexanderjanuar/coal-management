<div class="rounded-2xl bg-white dark:bg-gray-950 border border-gray-200/80 dark:border-gray-800/80 shadow-sm hover:shadow-md transition-shadow duration-300 h-full flex flex-col">

    {{-- Header --}}
    <div class="px-6 pt-6 pb-0">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-primary-50 dark:bg-primary-950/50 border border-primary-100 dark:border-primary-900/50 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 tracking-tight">
                        {{ match($period) {
                            'today' => 'Tugas Hari Ini',
                            'week' => 'Tugas Minggu Ini',
                            'month' => 'Tugas Bulan Ini',
                            default => 'Tugas',
                        } }}
                    </h2>
                    <p class="text-[11px] text-gray-500 dark:text-gray-500 mt-0.5">
                        @if($period === 'today')
                            {{ now()->locale('id')->translatedFormat('l, d M') }}
                        @elseif($period === 'week')
                            {{ now()->startOfWeek()->locale('id')->translatedFormat('d M') }} — {{ now()->endOfWeek()->locale('id')->translatedFormat('d M') }}
                        @else
                            {{ now()->locale('id')->translatedFormat('F Y') }}
                        @endif
                    </p>
                </div>
            </div>

            {{-- Refresh --}}
            <button wire:click="$refresh"
                class="w-7 h-7 rounded-lg border border-gray-200 dark:border-gray-800 flex items-center justify-center text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:border-primary-200 dark:hover:border-primary-800 transition-all"
                title="Muat Ulang">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/>
                </svg>
            </button>
        </div>

        {{-- Period Filter + Stats --}}
        <div class="flex items-center justify-between mb-4">
            <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-800 p-0.5 bg-gray-50 dark:bg-gray-900">
                @foreach(['today' => 'Hari', 'week' => 'Minggu', 'month' => 'Bulan'] as $key => $label)
                <button wire:click="$set('period', '{{ $key }}')"
                    class="px-2.5 py-1 text-[11px] font-medium rounded-md transition-all duration-200
                    {{ $period === $key
                        ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm border border-gray-200 dark:border-gray-700'
                        : 'text-gray-500 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
                <span class="text-xs font-semibold tabular-nums text-gray-900 dark:text-gray-100">{{ $taskStats['completed'] }}</span>
                <span class="text-xs text-gray-300 dark:text-gray-700">/</span>
                <span class="text-xs tabular-nums text-gray-400 dark:text-gray-600">{{ $taskStats['total'] }}</span>
            </div>
        </div>

        {{-- Completion bar --}}
        @if($taskStats['total'] > 0)
        <div class="mb-4">
            <div class="w-full h-1 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                @php $completionPct = round(($taskStats['completed'] / $taskStats['total']) * 100); @endphp
                <div class="h-full bg-primary-500 dark:bg-primary-400 rounded-full transition-all duration-500" style="width: {{ $completionPct }}%"></div>
            </div>
            <div class="flex items-center justify-between mt-1.5">
                <span class="text-[10px] text-gray-400 dark:text-gray-600">{{ $completionPct }}% selesai</span>
                <div class="flex items-center gap-3 text-[10px] text-gray-400 dark:text-gray-600">
                    @if($taskStats['in_progress'] > 0)
                    <span class="flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary-500 dark:bg-primary-400 animate-pulse"></span>
                        {{ $taskStats['in_progress'] }} berjalan
                    </span>
                    @endif
                    @if($taskStats['pending'] > 0)
                    <span>{{ $taskStats['pending'] }} menunggu</span>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Task List --}}
    <div class="flex-1 px-6 pb-4 overflow-hidden">
        @if($tasks->count() > 0)
        <div class="space-y-1 max-h-[480px] overflow-y-auto pr-1">
            @foreach($tasks as $task)
            @php
                $isCompleted = $task['status'] === 'completed';
                $isInProgress = $task['status'] === 'in_progress';
            @endphp
            <div class="flex items-start gap-3 p-3 rounded-xl transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-900/80 group
                {{ $isCompleted ? 'opacity-50' : '' }}">
                {{-- Status checkbox --}}
                <div class="shrink-0 mt-0.5">
                    @if($isCompleted)
                    <div class="w-4.5 h-4.5 rounded-md bg-primary-500 dark:bg-primary-400 flex items-center justify-center">
                        <svg class="w-3 h-3 text-white dark:text-primary-950" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>
                    </div>
                    @elseif($isInProgress)
                    <div class="w-4.5 h-4.5 rounded-md border-2 border-primary-500 dark:border-primary-400 flex items-center justify-center">
                        <div class="w-1.5 h-1.5 rounded-sm bg-primary-500 dark:bg-primary-400 animate-pulse"></div>
                    </div>
                    @else
                    <div class="w-4.5 h-4.5 rounded-md border-2 border-gray-300 dark:border-gray-700"></div>
                    @endif
                </div>

                {{-- Task content --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-800 dark:text-gray-200 truncate {{ $isCompleted ? 'line-through text-gray-400 dark:text-gray-600' : '' }}">
                        {{ $task['title'] }}
                    </p>
                    <div class="flex items-center gap-2 mt-1">
                        @if($period !== 'today' && $task['task_date'])
                        <span class="text-[10px] font-medium tabular-nums text-gray-400 dark:text-gray-600">
                            {{ \Carbon\Carbon::parse($task['task_date'])->locale('id')->translatedFormat('d M') }}
                        </span>
                        <span class="text-gray-200 dark:text-gray-800">&middot;</span>
                        @endif
                        @if($task['project'])
                        <span class="text-[11px] text-gray-400 dark:text-gray-600 truncate">{{ $task['project'] }}</span>
                        @endif
                        @if($task['priority'] === 'urgent' || $task['priority'] === 'high')
                        <span class="w-1 h-1 rounded-full {{ $task['priority'] === 'urgent' ? 'bg-gray-900 dark:bg-gray-100' : 'bg-gray-400 dark:bg-gray-500' }}"></span>
                        <span class="text-[10px] font-medium uppercase tracking-wider {{ $task['priority'] === 'urgent' ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400 dark:text-gray-500' }}">
                            {{ $task['priority'] === 'urgent' ? 'Urgent' : 'High' }}
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Assignees --}}
                @if($task['assignees']->count() > 0)
                <div class="shrink-0 flex items-center -space-x-1">
                    @foreach($task['assignees']->take(2) as $assignee)
                        @if($assignee['avatar_url'])
                        <img src="{{ $assignee['avatar_url'] }}" alt="{{ $assignee['name'] }}"
                            class="w-5 h-5 rounded-full border border-white dark:border-gray-950 object-cover" title="{{ $assignee['name'] }}">
                        @else
                        <div class="w-5 h-5 rounded-full border border-white dark:border-gray-950 bg-gray-200 dark:bg-gray-700 flex items-center justify-center" title="{{ $assignee['name'] }}">
                            <span class="text-[9px] font-semibold text-gray-500 dark:text-gray-400">{{ $assignee['initials'] }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-gray-300 dark:text-gray-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-600">
                Tidak ada tugas {{ match($period) { 'today' => 'hari ini', 'week' => 'minggu ini', 'month' => 'bulan ini', default => '' } }}
            </p>
        </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="px-6 py-3 border-t border-gray-100 dark:border-gray-800/80 mt-auto">
        <a href="{{ route('filament.admin.pages.daily-task-dashboard') }}"
            class="text-[11px] font-medium text-gray-400 hover:text-primary-600 dark:text-gray-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-1">
            Lihat semua tugas
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
            </svg>
        </a>
    </div>
</div>
