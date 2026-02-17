{{-- resources/views/components/daily-task/partials/kanban-task-card.blade.php --}}
{{-- Premium UI/UX Pro Max Enhanced Card Design --}}
@props(['task'])

@php
    // Premium Category/Tag Color System with proper contrast
    $categoryColors = [
        'Website' => [
            'bg' => 'bg-gradient-to-br from-indigo-500 to-indigo-600',
            'text' => 'text-white',
            'glow' => 'shadow-sm shadow-indigo-500/20'
        ],
        'Marketing' => [
            'bg' => 'bg-gradient-to-br from-pink-50 to-pink-100',
            'text' => 'text-pink-700',
            'glow' => 'shadow-sm shadow-pink-500/10'
        ],
        'Management' => [
            'bg' => 'bg-gradient-to-br from-orange-500 to-orange-600',
            'text' => 'text-white',
            'glow' => 'shadow-sm shadow-orange-500/20'
        ],
        'System' => [
            'bg' => 'bg-gradient-to-br from-violet-50 to-violet-100',
            'text' => 'text-violet-700',
            'glow' => 'shadow-sm shadow-violet-500/10'
        ],
        'Other' => [
            'bg' => 'bg-gradient-to-br from-gray-100 to-gray-200',
            'text' => 'text-gray-700',
            'glow' => 'shadow-sm shadow-gray-500/10'
        ],
        'Product' => [
            'bg' => 'bg-gradient-to-br from-emerald-50 to-emerald-100',
            'text' => 'text-emerald-700',
            'glow' => 'shadow-sm shadow-emerald-500/10'
        ],
        'HR' => [
            'bg' => 'bg-gradient-to-br from-emerald-500 to-emerald-600',
            'text' => 'text-white',
            'glow' => 'shadow-sm shadow-emerald-500/20'
        ],
    ];

    // Determine category from task data
    $category = $task->project ? ($task->project->client?->name ?? $task->project->name ?? 'Other') : 'Other';
    $categoryStyle = $categoryColors[$category] ?? $categoryColors['Other'];

    // Calculate subtask progress
    $completedSubtasks = $task->subtasks->where('status', 'completed')->count();
    $totalSubtasks = $task->subtasks->count();

    // Get first assigned user for avatar
    $assignedUser = $task->assignedUsers->first();
@endphp

{{-- Premium Card Container with Layered Depth --}}
<div class="kanban-task-card group relative bg-white dark:bg-gray-800/90 backdrop-blur-sm rounded-2xl p-5
            shadow-md hover:shadow-xl
            transition-all duration-300 ease-out cursor-pointer
            border border-gray-100/50 dark:border-gray-700/50
            hover:-translate-y-1.5 hover:scale-[1.01]
            overflow-hidden" wire:click="openTaskDetail({{ $task->id }})">



    {{-- Content Layer --}}
    <div class="relative z-10">
        {{-- Task Title with Enhanced Typography --}}
        <h4 class="text-[15px] font-semibold text-gray-900 dark:text-gray-50 mb-3 leading-snug
                   tracking-tight group-hover:text-gray-950 dark:group-hover:text-white
                   transition-colors duration-200">
            {{ $task->title }}
        </h4>

        {{-- Premium Category Tags with Subtle Glow --}}
        <div class="flex flex-wrap gap-2 mb-4">
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-[11px] font-medium
                        {{ $categoryStyle['bg'] }} {{ $categoryStyle['text'] }} 
                        {{ $categoryStyle['glow'] }}
                        transition-all duration-200 hover:scale-105">
                {{ $category }}
            </span>

            @if($task->project && $task->project->name !== $category)
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-[11px] font-medium
                                bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-700 
                                dark:bg-gradient-to-br dark:from-emerald-900/30 dark:to-emerald-800/20 dark:text-emerald-300
                                shadow-sm shadow-emerald-500/10
                                transition-all duration-200 hover:scale-105">
                    {{ $task->project->name }}
                </span>
            @endif
        </div>

        {{-- Footer: Avatar, Date & Progress with Refined Spacing --}}
        <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-600">
            {{-- Avatar & Date Group --}}
            <div class="flex items-center gap-2">
                @if($assignedUser)
                    @if($assignedUser->avatar_url)
                        <img src="{{ $assignedUser->avatar_url }}" alt="{{ $assignedUser->name }}"
                            class="w-7 h-7 rounded-full object-cover ring-2 ring-gray-100 dark:ring-gray-700
                                                transition-all duration-200 group-hover:ring-indigo-200 dark:group-hover:ring-indigo-800">
                    @else
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600
                                                flex items-center justify-center text-[10px] font-bold text-white
                                                ring-2 ring-gray-100 dark:ring-gray-700
                                                transition-all duration-200 group-hover:ring-indigo-200 dark:group-hover:ring-indigo-800
                                                shadow-sm">
                            {{ strtoupper(substr($assignedUser->name, 0, 1)) }}
                        </div>
                    @endif
                @else
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600
                                        ring-2 ring-gray-100 dark:ring-gray-700"></div>
                @endif

                <span class="text-[13px] font-medium text-gray-600 dark:text-gray-400
                             group-hover:text-gray-900 dark:group-hover:text-gray-200
                             transition-colors duration-200">
                    @if($task->task_date)
                        {{ $task->task_date->format('M d, Y') }}
                    @else
                        <span class="text-gray-400 dark:text-gray-500">No date</span>
                    @endif
                </span>
            </div>

            {{-- Progress Counter with Enhanced Visual Treatment --}}
            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg
                        bg-gray-50 dark:bg-gray-700/50
                        text-gray-500 dark:text-gray-400
                        group-hover:bg-indigo-50 dark:group-hover:bg-indigo-900/20
                        group-hover:text-indigo-600 dark:group-hover:text-indigo-400
                        transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-[13px] font-semibold tabular-nums">
                    @if($totalSubtasks > 0)
                        {{ $completedSubtasks }}/{{ $totalSubtasks }}
                    @else
                        0/{{ $task->subtasks->count() > 0 ? $task->subtasks->count() : ($task->status === 'completed' ? '1' : '0') }}
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- Premium Shimmer Effect on Hover (Subtle) --}}
    <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 
                bg-gradient-to-r from-transparent via-white/10 to-transparent
                -translate-x-full group-hover:translate-x-full
                transition-all duration-700 pointer-events-none"></div>
</div>