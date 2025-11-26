{{-- resources/views/components/daily-task/partials/kanban-task-card.blade.php --}}
@props(['task'])

<div class="kanban-task-card group relative bg-white dark:bg-gray-800 rounded-lg 
        border border-gray-200 dark:border-gray-700 p-3 space-y-2.5
        hover:shadow-lg dark:hover:shadow-gray-900/30 
        hover:border-primary-300 dark:hover:border-primary-600
        hover:-translate-y-0.5
        transition-all duration-300 cursor-pointer" wire:click="openTaskDetail({{ $task->id }})">

    {{-- Priority Indicator (Top Border) --}}
    @php
    $priorityColors = [
    'urgent' => 'bg-gradient-to-r from-red-500 to-red-600',
    'high' => 'bg-gradient-to-r from-orange-500 to-orange-600',
    'normal' => 'bg-gradient-to-r from-blue-500 to-blue-600',
    'low' => 'bg-gradient-to-r from-gray-400 to-gray-500',
    ];
    $priorityColor = $priorityColors[$task->priority] ?? 'bg-gray-400';

    $priorityLabels = [
    'urgent' => 'Urgent',
    'high' => 'High',
    'normal' => 'Normal',
    'low' => 'Low',
    ];
    $priorityLabel = $priorityLabels[$task->priority] ?? 'Normal';
    @endphp

    <div class="absolute top-0 left-0 right-0 h-1 {{ $priorityColor }} rounded-t-xl"></div>

    {{-- Header Section --}}
    <div class="space-y-1.5">
        {{-- Task Title --}}
        <div>
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 
                       line-clamp-2 leading-snug group-hover:text-primary-600 dark:group-hover:text-primary-400
                       transition-colors duration-200">
                {{ $task->title }}
            </h4>
        </div>
    </div>

    {{-- Description Preview (if exists) --}}
    @if($task->description && strlen($task->description) > 0)
    <div class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2 leading-relaxed">
        {{ $task->description }}
    </div>
    @endif

    {{-- Meta Information Grid --}}
    <div class="grid grid-cols-1 gap-1.5">
        {{-- Project/Client --}}
        @if($task->project)
        <div class="flex items-center gap-1.5 px-2 py-1 rounded-md
                    bg-gray-50 dark:bg-gray-900/30 
                    border border-gray-100 dark:border-gray-700/50">
            <div class="flex-shrink-0 w-6 h-6 rounded-md bg-primary-100 dark:bg-primary-900/30 
                        flex items-center justify-center">
                <x-heroicon-o-building-office class="w-3.5 h-3.5 text-primary-600 dark:text-primary-400" />
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-xs font-semibold text-gray-900 dark:text-gray-100 truncate">
                    @if($task->project->client)
                    {{ $task->project->client->name }}
                    @else
                    {{ $task->project->name }}
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Due Date --}}
        @if($task->task_date)
        @php
        $isOverdue = $task->task_date->isPast() && $task->status !== 'completed';
        $isToday = $task->task_date->isToday();
        $isTomorrow = $task->task_date->isTomorrow();
        @endphp

        <div class="flex items-center gap-1.5 px-2 py-1 rounded-md
                    {{ $isOverdue 
                        ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' 
                        : ($isToday 
                            ? 'bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800'
                            : 'bg-gray-50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-700/50') }}">
            <div class="flex-shrink-0 w-6 h-6 rounded-md 
                        {{ $isOverdue 
                            ? 'bg-red-100 dark:bg-red-900/40' 
                            : ($isToday 
                                ? 'bg-orange-100 dark:bg-orange-900/40'
                                : 'bg-gray-100 dark:bg-gray-800') }}
                        flex items-center justify-center">
                <x-heroicon-o-calendar-days class="w-3.5 h-3.5 
                    {{ $isOverdue 
                        ? 'text-red-600 dark:text-red-400' 
                        : ($isToday 
                            ? 'text-orange-600 dark:text-orange-400'
                            : 'text-gray-600 dark:text-gray-400') }}" />
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-xs font-semibold
                            {{ $isOverdue 
                                ? 'text-red-700 dark:text-red-300' 
                                : ($isToday 
                                    ? 'text-orange-700 dark:text-orange-300'
                                    : 'text-gray-900 dark:text-gray-100') }}">
                    @if($isToday)
                    Today
                    @elseif($isTomorrow)
                    Tomorrow
                    @else
                    {{ $task->task_date->format('M d, Y') }}
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Subtasks Progress --}}
    @if($task->subtasks->count() > 0)
    @php
    $completedSubtasks = $task->subtasks->where('status', 'completed')->count();
    $totalSubtasks = $task->subtasks->count();
    $progress = $totalSubtasks > 0 ? ($completedSubtasks / $totalSubtasks) * 100 : 0;
    @endphp

    <div class="space-y-1.5 p-2 rounded-md bg-gray-50 dark:bg-gray-900/30 
                border border-gray-100 dark:border-gray-700/50">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-1.5 text-xs font-medium text-gray-700 dark:text-gray-300">
                <x-heroicon-o-list-bullet class="w-3.5 h-3.5" />
                <span>Subtasks</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-semibold text-gray-900 dark:text-gray-100">
                    {{ $completedSubtasks }}/{{ $totalSubtasks }}
                </span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold
                            {{ $progress >= 100 
                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' 
                                : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' }}">
                    {{ round($progress) }}%
                </span>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="relative h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
            <div class="absolute inset-0 {{ $progress >= 100 ? 'bg-gradient-to-r from-green-500 to-green-600' : 'bg-gradient-to-r from-blue-500 to-blue-600' }} 
                        rounded-full transition-all duration-500 ease-out" style="width: {{ $progress }}%">
                {{-- Shimmer effect --}}
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent 
                            -translate-x-full animate-shimmer"></div>
            </div>
        </div>
    </div>
    @endif

    {{-- Footer --}}
    <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700">
        {{-- Assignees --}}
        <div class="flex items-center">
            @if($task->assignedUsers->count() > 0)
            <div class="flex -space-x-1.5">
                @foreach($task->assignedUsers->take(3) as $user)
                <div class="relative group/avatar" title="{{ $user->name }}">
                    @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-6 h-6 rounded-full border-2 border-white dark:border-gray-800
                                ring-1 ring-gray-100 dark:ring-gray-700
                                group-hover/avatar:ring-primary-300 dark:group-hover/avatar:ring-primary-600
                                transition-all duration-200" />
                    @else
                    <div class="w-6 h-6 rounded-full border-2 border-white dark:border-gray-800
                                ring-1 ring-gray-100 dark:ring-gray-700
                                bg-gradient-to-br from-primary-400 to-primary-600
                                flex items-center justify-center 
                                text-white text-[9px] font-bold
                                group-hover/avatar:ring-primary-300 dark:group-hover/avatar:ring-primary-600
                                transition-all duration-200">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    @endif

                    {{-- Hover tooltip --}}
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1
                                bg-gray-900 dark:bg-gray-700 text-white text-xs rounded
                                opacity-0 group-hover/avatar:opacity-100 pointer-events-none
                                whitespace-nowrap transition-opacity duration-200 z-10">
                        {{ $user->name }}
                    </div>
                </div>
                @endforeach

                @if($task->assignedUsers->count() > 3)
                <div class="w-6 h-6 rounded-full border-2 border-white dark:border-gray-800
                            ring-1 ring-gray-100 dark:ring-gray-700
                            bg-gradient-to-br from-gray-300 to-gray-400 dark:from-gray-600 dark:to-gray-700
                            flex items-center justify-center
                            text-[9px] font-bold text-white">
                    +{{ $task->assignedUsers->count() - 3 }}
                </div>
                @endif
            </div>
            @else
            <div class="flex items-center gap-1 px-2 py-0.5 rounded-md
                        bg-gray-100 dark:bg-gray-800 
                        border border-gray-200 dark:border-gray-700">
                <x-heroicon-o-user-plus class="w-3 h-3 text-gray-400 dark:text-gray-500" />
                <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400">Unassigned</span>
            </div>
            @endif
        </div>

        {{-- Action Counts --}}
        <div class="flex items-center gap-2">
            {{-- Comments --}}
            @if(($task->comments_count ?? 0) > 0)
            <div class="flex items-center gap-1 px-1.5 py-0.5 rounded-md
                        bg-gray-100 dark:bg-gray-800 
                        hover:bg-primary-50 dark:hover:bg-primary-900/20
                        transition-colors duration-200">
                <x-heroicon-o-chat-bubble-left class="w-3 h-3 text-gray-500 dark:text-gray-400" />
                <span class="text-[10px] font-semibold text-gray-700 dark:text-gray-300">
                    {{ $task->comments_count }}
                </span>
            </div>
            @endif

            {{-- Attachments --}}
            @if(($task->attachments_count ?? 0) > 0)
            <div class="flex items-center gap-1 px-1.5 py-0.5 rounded-md
                        bg-gray-100 dark:bg-gray-800
                        hover:bg-primary-50 dark:hover:bg-primary-900/20
                        transition-colors duration-200">
                <x-heroicon-o-paper-clip class="w-3 h-3 text-gray-500 dark:text-gray-400" />
                <span class="text-[10px] font-semibold text-gray-700 dark:text-gray-300">
                    {{ $task->attachments_count }}
                </span>
            </div>
            @endif
        </div>
    </div>

    {{-- Hover effect overlay --}}
    <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-primary-500/0 to-primary-600/0 
                group-hover:from-primary-500/5 group-hover:to-primary-600/5 
                transition-all duration-300 pointer-events-none"></div>

    {{-- Add shimmer animation to your CSS --}}
    <style>
        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .animate-shimmer {
            animation: shimmer 2s infinite;
        }
    </style>
</div>