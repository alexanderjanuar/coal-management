{{-- resources/views/components/daily-task/partials/kanban-task-card.blade.php --}}
{{-- Professional Project Management Style --}}
@props(['task'])

@php
    $priorityConfig = [
        'urgent' => ['border' => 'border-l-red-500', 'badge' => 'bg-red-500 text-white', 'label' => 'Urgent'],
        'high' => ['border' => 'border-l-orange-500', 'badge' => 'bg-orange-500 text-white', 'label' => 'High'],
        'normal' => ['border' => 'border-l-blue-500', 'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300', 'label' => 'Normal'],
        'low' => ['border' => 'border-l-gray-400', 'badge' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400', 'label' => 'Low'],
    ];
    $priority = $priorityConfig[$task->priority] ?? $priorityConfig['normal'];

    $isOverdue = $task->task_date?->isPast() && $task->status !== 'completed';
    $isToday = $task->task_date?->isToday();
    $isTomorrow = $task->task_date?->isTomorrow();

    $completedSubtasks = $task->subtasks->where('status', 'completed')->count();
    $totalSubtasks = $task->subtasks->count();
    $subtaskProgress = $totalSubtasks > 0 ? ($completedSubtasks / $totalSubtasks) * 100 : 0;
@endphp

<div class="kanban-task-card group relative bg-white dark:bg-gray-900 rounded-lg
            border border-l-4 {{ $priority['border'] }} border-gray-200 dark:border-gray-800
            shadow-sm hover:shadow-md dark:shadow-gray-950/20
            transition-all duration-200 cursor-pointer overflow-hidden"
     wire:click="openTaskDetail({{ $task->id }})">

    {{-- Card Content --}}
    <div class="p-3 space-y-2.5">

        {{-- Header: Priority Badge + Task ID --}}
        <div class="flex items-center justify-between">
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider {{ $priority['badge'] }}">
                {{ $priority['label'] }}
            </span>
            <span class="text-[10px] font-mono text-gray-400 dark:text-gray-600">#{{ $task->id }}</span>
        </div>

        {{-- Task Title --}}
        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 leading-snug line-clamp-2
                   group-hover:text-gray-700 dark:group-hover:text-gray-200 transition-colors">
            {{ $task->title }}
        </h4>

        {{-- Description Preview --}}
        @if($task->description)
        <p class="text-xs text-gray-500 dark:text-gray-500 line-clamp-2 leading-relaxed">
            {{ $task->description }}
        </p>
        @endif

        {{-- Labels / Tags Row --}}
        <div class="flex flex-wrap gap-1">
            {{-- Project/Client Tag --}}
            @if($task->project)
            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium
                        bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400
                        border border-gray-200 dark:border-gray-700 max-w-[140px]">
                <svg class="w-2.5 h-2.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/>
                </svg>
                <span class="truncate">{{ $task->project->client?->name ?? $task->project->name }}</span>
            </span>
            @endif

            {{-- Due Date Tag --}}
            @if($task->task_date)
            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium
                        {{ $isOverdue
                            ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400 border border-red-200 dark:border-red-800'
                            : ($isToday
                                ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800'
                                : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700') }}">
                <svg class="w-2.5 h-2.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                </svg>
                @if($isToday)
                    Today
                @elseif($isTomorrow)
                    Tomorrow
                @elseif($isOverdue)
                    Overdue
                @else
                    {{ $task->task_date->format('M d') }}
                @endif
            </span>
            @endif
        </div>

        {{-- Subtasks Progress --}}
        @if($totalSubtasks > 0)
        <div class="space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-medium text-gray-500 dark:text-gray-500">Subtasks</span>
                <span class="text-[10px] font-semibold text-gray-700 dark:text-gray-300">{{ $completedSubtasks }}/{{ $totalSubtasks }}</span>
            </div>
            <div class="h-1 bg-gray-200 dark:bg-gray-800 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-300
                            {{ $subtaskProgress >= 100 ? 'bg-green-500' : 'bg-blue-500' }}"
                     style="width: {{ $subtaskProgress }}%"></div>
            </div>
        </div>
        @endif
    </div>

    {{-- Card Footer --}}
    <div class="px-3 py-2 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50
                flex items-center justify-between">

        {{-- Assignees --}}
        <div class="flex items-center -space-x-1.5">
            @forelse($task->assignedUsers->take(3) as $user)
            <div class="relative group/avatar" title="{{ $user->name }}">
                @if($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                     class="w-5 h-5 rounded-full border-2 border-white dark:border-gray-900 object-cover">
                @else
                <div class="w-5 h-5 rounded-full border-2 border-white dark:border-gray-900
                            bg-gray-200 dark:bg-gray-700 flex items-center justify-center
                            text-[8px] font-bold text-gray-600 dark:text-gray-400">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                @endif
            </div>
            @empty
            <div class="flex items-center gap-1 text-[10px] text-gray-400 dark:text-gray-600">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                </svg>
                <span>Unassigned</span>
            </div>
            @endforelse

            @if($task->assignedUsers->count() > 3)
            <div class="w-5 h-5 rounded-full border-2 border-white dark:border-gray-900
                        bg-gray-300 dark:bg-gray-600 flex items-center justify-center
                        text-[8px] font-bold text-gray-700 dark:text-gray-300">
                +{{ $task->assignedUsers->count() - 3 }}
            </div>
            @endif
        </div>

        {{-- Meta Counts --}}
        <div class="flex items-center gap-2 text-gray-400 dark:text-gray-600">
            @if(($task->comments_count ?? 0) > 0)
            <div class="flex items-center gap-0.5 text-[10px]">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
                </svg>
                <span class="font-medium">{{ $task->comments_count }}</span>
            </div>
            @endif

            @if(($task->attachments_count ?? 0) > 0)
            <div class="flex items-center gap-0.5 text-[10px]">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/>
                </svg>
                <span class="font-medium">{{ $task->attachments_count }}</span>
            </div>
            @endif
        </div>
    </div>
</div>
