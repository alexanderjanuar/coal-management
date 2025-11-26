{{-- resources/views/components/daily-task/partials/kanban-task-card.blade.php --}}
@props(['task'])

<div class="kanban-task-card group bg-white dark:bg-gray-800 rounded-lg 
        border border-gray-200 dark:border-gray-700 p-3 space-y-3
        hover:shadow-lg dark:hover:shadow-gray-900/50 
        hover:border-gray-300 dark:hover:border-gray-600
        transition-all duration-200 cursor-pointer" wire:click="openTaskDetail({{ $task->id }})">

    {{-- Header: Title + Priority --}}
    <div class="flex items-start gap-2">
        {{-- Priority Indicator --}}
        @php
        $priorityColors = [
        'urgent' => 'bg-red-500',
        'high' => 'bg-orange-500',
        'normal' => 'bg-blue-500',
        'low' => 'bg-gray-400',
        ];
        $priorityColor = $priorityColors[$task->priority] ?? 'bg-gray-400';
        @endphp
        <div class="w-1 h-full {{ $priorityColor }} rounded-full flex-shrink-0 mt-0.5"></div>

        {{-- Title --}}
        <div class="flex-1 min-w-0">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 
                       line-clamp-2 leading-snug mb-1">
                {{ $task->title }}
            </h4>
            <span class="text-[10px] text-gray-400 dark:text-gray-500 font-mono">
        </div>

        {{-- Status Dot --}}
        @php
        $statusColors = [
        'completed' => 'bg-green-500',
        'in_progress' => 'bg-blue-500',
        'pending' => 'bg-gray-400',
        ];
        $statusColor = $statusColors[$task->status] ?? 'bg-gray-400';
        @endphp
        <div class="w-2 h-2 {{ $statusColor }} rounded-full flex-shrink-0 mt-1.5
                    {{ $task->status === 'in_progress' ? 'animate-pulse' : '' }}"></div>
    </div>

    {{-- Meta Info --}}
    <div class="space-y-2">
        {{-- Project/Client --}}
        @if($task->project)
        <div class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400">
            <x-heroicon-o-building-office class="w-3.5 h-3.5 flex-shrink-0" />
            <span class="truncate font-medium">
                @if($task->project->client)
                {{ $task->project->client->name }}
                @else
                {{ $task->project->name }}
                @endif
            </span>
        </div>
        @endif

        {{-- Due Date --}}
        @if($task->task_date)
        @php
        $isOverdue = $task->task_date->isPast() && $task->status !== 'completed';
        $isToday = $task->task_date->isToday();
        $isTomorrow = $task->task_date->isTomorrow();
        @endphp

        <div class="flex items-center gap-1.5 text-xs font-medium
                    {{ $isOverdue 
                        ? 'text-red-600 dark:text-red-400' 
                        : ($isToday 
                            ? 'text-orange-600 dark:text-orange-400'
                            : 'text-gray-600 dark:text-gray-400') }}">
            <x-heroicon-o-calendar-days class="w-3.5 h-3.5 flex-shrink-0" />
            <span>
                @if($isOverdue)
                Overdue: {{ $task->task_date->format('M d') }}
                @elseif($isToday)
                Today
                @elseif($isTomorrow)
                Tomorrow
                @else
                {{ $task->task_date->format('M d, Y') }}
                @endif
            </span>
        </div>
        @endif

        {{-- Subtasks Progress --}}
        @if($task->subtasks->count() > 0)
        @php
        $completedSubtasks = $task->subtasks->where('status', 'completed')->count();
        $totalSubtasks = $task->subtasks->count();
        $progress = $totalSubtasks > 0 ? ($completedSubtasks / $totalSubtasks) * 100 : 0;
        @endphp

        <div class="space-y-1.5">
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                    <x-heroicon-o-list-bullet class="w-3.5 h-3.5" />
                    <span class="font-medium">{{ $completedSubtasks }}/{{ $totalSubtasks }}</span>
                </div>
                <span class="text-gray-500 dark:text-gray-400 font-semibold">
                    {{ round($progress) }}%
                </span>
            </div>

            {{-- Progress Bar --}}
            <div class="h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full {{ $progress >= 100 ? 'bg-green-500' : 'bg-blue-500' }} 
                            rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
            </div>
        </div>
        @endif
    </div>

    {{-- Footer: Assignees + Counts --}}
    <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700">
        {{-- Assignees --}}
        <div class="flex items-center">
            @if($task->assignedUsers->count() > 0)
            <div class="flex -space-x-2">
                @foreach($task->assignedUsers->take(3) as $user)
                <div class="relative" title="{{ $user->name }}">
                    @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                        class="w-6 h-6 rounded-full border-2 border-white dark:border-gray-800" />
                    @else
                    <div class="w-6 h-6 rounded-full border-2 border-white dark:border-gray-800
                                bg-blue-500 flex items-center justify-center 
                                text-white text-[10px] font-semibold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    @endif
                </div>
                @endforeach

                @if($task->assignedUsers->count() > 3)
                <div class="w-6 h-6 rounded-full border-2 border-white dark:border-gray-800
                            bg-gray-200 dark:bg-gray-700 flex items-center justify-center
                            text-[10px] font-semibold text-gray-600 dark:text-gray-300">
                    +{{ $task->assignedUsers->count() - 3 }}
                </div>
                @endif
            </div>
            @else
            <span class="text-xs text-gray-400 dark:text-gray-500">Unassigned</span>
            @endif
        </div>

        {{-- Counts --}}
        <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
            {{-- Comments --}}
            @if(($task->comments_count ?? 0) > 0)
            <div class="flex items-center gap-1">
                <x-heroicon-o-chat-bubble-left class="w-3.5 h-3.5" />
                <span class="font-medium">{{ $task->comments_count }}</span>
            </div>
            @endif

            {{-- Attachments --}}
            @if(($task->attachments_count ?? 0) > 0)
            <div class="flex items-center gap-1">
                <x-heroicon-o-paper-clip class="w-3.5 h-3.5" />
                <span class="font-medium">{{ $task->attachments_count }}</span>
            </div>
            @endif
        </div>
    </div>
</div>