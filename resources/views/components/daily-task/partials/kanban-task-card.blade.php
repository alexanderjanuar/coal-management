{{-- resources/views/components/daily-task/partials/kanban-task-card.blade.php --}}
{{-- Refined Editorial Kanban Card --}}
@props(['task'])

@php
    // Priority accent system — left-edge color bar
    $priorityAccent = match ($task->priority) {
        'urgent' => 'border-l-rose-500',
        'high' => 'border-l-amber-500',
        'normal' => 'border-l-slate-300 dark:border-l-slate-600',
        'low' => 'border-l-emerald-400',
        default => 'border-l-slate-200 dark:border-l-slate-700',
    };

    $priorityDot = match ($task->priority) {
        'urgent' => 'bg-rose-500',
        'high' => 'bg-amber-500',
        'normal' => 'bg-slate-400 dark:bg-slate-500',
        'low' => 'bg-emerald-400',
        default => 'bg-slate-300',
    };

    // Client name as the primary tag
    $clientName = $task->project?->client?->name;
    $projectName = $task->project?->name;

    // Subtask progress
    $completedSubtasks = $task->subtasks->where('status', 'completed')->count();
    $totalSubtasks = $task->subtasks->count();
    $hasProgress = $totalSubtasks > 0;
    $progressPercent = $hasProgress ? round(($completedSubtasks / $totalSubtasks) * 100) : 0;

    // Assigned user
    $assignedUser = $task->assignedUsers->first();
    $extraAssignees = $task->assignedUsers->count() - 1;

    // Overdue check
    $isOverdue = $task->task_date && $task->task_date->isPast() && $task->status !== 'completed';
@endphp

{{-- Card Container — clean edges, priority accent bar --}}
<div class="group relative bg-white dark:bg-gray-800
            rounded-lg
            shadow-[0_1px_3px_rgba(0,0,0,0.08),0_1px_2px_rgba(0,0,0,0.04)]
            hover:shadow-[0_4px_12px_rgba(0,0,0,0.1),0_2px_4px_rgba(0,0,0,0.06)]
            dark:shadow-[0_1px_3px_rgba(0,0,0,0.3),0_1px_2px_rgba(0,0,0,0.2)]
            dark:hover:shadow-[0_4px_12px_rgba(0,0,0,0.4),0_2px_4px_rgba(0,0,0,0.3)]
            border border-gray-100 dark:border-gray-700/50
            transition-shadow duration-200 ease-out
            cursor-pointer overflow-hidden" wire:click="openTaskDetail({{ $task->id }})">

    <div class="p-4 space-y-3">
        {{-- Title --}}
        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-50 leading-snug
                   tracking-[-0.01em] line-clamp-2">
            {{ $task->title }}
        </h4>

        {{-- Tags Row --}}
        <div class="flex flex-wrap items-center gap-1.5">
            @if($clientName)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wide
                                            bg-slate-100 text-slate-600
                                            dark:bg-slate-700/60 dark:text-slate-300">
                    {{ $clientName }}
                </span>
            @endif

            @if($projectName)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium
                                            bg-sky-50 text-sky-600
                                            dark:bg-sky-900/30 dark:text-sky-300">
                    {{ $projectName }}
                </span>
            @endif

            @if($task->priority === 'urgent')
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                            bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">
                    <span class="w-1 h-1 rounded-full bg-rose-500 animate-pulse"></span>
                    Urgent
                </span>
            @endif
        </div>

        {{-- Subtask Progress Bar (only if subtasks exist) --}}
        @if($hasProgress)
            <div class="space-y-1">
                <div class="w-full h-1 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500 ease-out
                                            {{ $progressPercent === 100 ? 'bg-emerald-500' : 'bg-sky-500' }}"
                        style="width: {{ $progressPercent }}%"></div>
                </div>
            </div>
        @endif

        {{-- Footer — avatar, date, progress count --}}
        <div class="flex items-center justify-between pt-3 mt-1 border-t border-gray-100 dark:border-gray-700/50">
            {{-- Left: Avatar + Date --}}
            <div class="flex items-center gap-2" x-data="{ assigneeOpen: false, buttonRect: {} }" @click.stop>
                {{-- Clickable Assignee Section --}}
                <button @click="assigneeOpen = !assigneeOpen; buttonRect = $el.getBoundingClientRect()" class="flex items-center gap-2 px-1.5 py-0.5 -mx-1.5 rounded-md
                               hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors duration-150
                               group/ava">
                    @if($assignedUser)
                        @if($assignedUser->avatar_url)
                            <img src="{{ $assignedUser->avatar_url }}" alt="{{ $assignedUser->name }}"
                                class="w-6 h-6 rounded-full object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                        @else
                            <div class="w-6 h-6 rounded-full bg-slate-800 dark:bg-slate-200
                                                flex items-center justify-center text-[9px] font-bold
                                                text-white dark:text-slate-800
                                                ring-1 ring-gray-200 dark:ring-gray-700">
                                {{ strtoupper(substr($assignedUser->name, 0, 1)) }}
                            </div>
                        @endif

                        @if($extraAssignees > 0)
                            <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500">
                                +{{ $extraAssignees }}
                            </span>
                        @endif
                    @else
                        <div
                            class="w-6 h-6 rounded-full border border-dashed border-slate-300 dark:border-slate-600
                                        flex items-center justify-center
                                        group-hover/ava:border-sky-400 dark:group-hover/ava:border-sky-500 transition-colors">
                            <svg class="w-3 h-3 text-slate-400 dark:text-slate-500 group-hover/ava:text-sky-500 transition-colors"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                    @endif
                </button>

                {{-- Assignee Dropdown (teleported to body) --}}
                <template x-teleport="body">
                    <div x-show="assigneeOpen" x-cloak @click.away="assigneeOpen = false"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        class="fixed w-56 bg-white dark:bg-gray-800 rounded-xl shadow-xl
                                border border-gray-200 dark:border-gray-700 z-[9999] max-h-64 overflow-hidden flex flex-col"
                        x-bind:style="{
                             top: Math.max(8, Math.min(buttonRect.top + window.scrollY - 260, window.innerHeight + window.scrollY - 280)) + 'px',
                             left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 240)) + 'px'
                         }">

                        {{-- Header --}}
                        <div
                            class="px-3 py-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500
                                    border-b border-gray-100 dark:border-gray-700 flex-shrink-0 flex items-center gap-1.5 sticky top-0 bg-white dark:bg-gray-800">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            Assign Users
                        </div>

                        {{-- User List --}}
                        <div class="overflow-y-auto flex-1">
                            @foreach($this->getUserOptions() as $userId => $userName)
                                @php $isAssigned = $task->assignedUsers->contains($userId); @endphp
                                <button
                                    wire:click="{{ $isAssigned ? "unassignUser({$task->id}, {$userId})" : "assignUser({$task->id}, {$userId})" }}"
                                    class="w-full text-left flex items-center gap-2.5 px-3 py-2 text-[11px]
                                                   text-slate-600 dark:text-slate-300
                                                   {{ $isAssigned ? 'bg-sky-50/50 dark:bg-sky-900/10' : '' }}
                                                   hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <div class="w-5 h-5 rounded-full bg-slate-800 dark:bg-slate-200
                                                    flex items-center justify-center text-[8px] font-bold
                                                    text-white dark:text-slate-800 flex-shrink-0">
                                        {{ strtoupper(substr($userName, 0, 1)) }}
                                    </div>
                                    <span class="font-medium flex-1 truncate">{{ $userName }}</span>
                                    @if($isAssigned)
                                        <svg class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                </template>

                <span
                    class="text-[11px] font-medium tabular-nums
                            {{ $isOverdue ? 'text-rose-500 dark:text-rose-400' : 'text-slate-400 dark:text-slate-500' }}">
                    @if($task->task_date)
                        {{ $task->task_date->format('M d, Y') }}
                    @endif
                </span>
            </div>

            {{-- Right: Subtask counter --}}
            <div class="flex items-center gap-1 text-slate-400 dark:text-slate-500">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-[11px] font-semibold tabular-nums">
                    {{ $completedSubtasks }}/{{ $totalSubtasks }}
                </span>
            </div>
        </div>
    </div>
</div>