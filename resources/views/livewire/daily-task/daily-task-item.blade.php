{{-- resources/views/livewire/daily-task/daily-task-item.blade.php - Fully Responsive --}}
<div x-data="taskItemManager({{ $task->id }})" class="space-y-4" x-init="init()">
    {{-- Desktop View (lg and up) --}}
    <div class="hidden lg:block">
        <div class="grid grid-cols-12 gap-4 items-center">
            {{-- Checkbox & Completion Toggle --}}
            <div class="col-span-1 flex items-center gap-2">
                <input type="checkbox" x-model="selected"
                    @change="$dispatch('task-selected', { taskId: {{ $task->id }}, selected: $event.target.checked })"
                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 focus:ring-offset-0 opacity-0 group-hover:opacity-100 transition-opacity">
                <button wire:click="toggleTaskCompletion"
                    class="flex-shrink-0 hover:scale-110 transition-transform duration-200"
                    x-bind:class="{ 'animate-bounce': completionToggling }"
                    @click="completionToggling = true; setTimeout(() => completionToggling = false, 600)">
                    @if($task->status === 'completed')
                    <div class="relative">
                        <x-heroicon-s-check-circle class="w-5 h-5 text-green-500" />
                        <div class="absolute inset-0 bg-green-500 rounded-full animate-ping opacity-25"></div>
                    </div>
                    @else
                    <div
                        class="w-5 h-5 rounded-full border-2 border-gray-300 hover:border-primary-500 hover:bg-primary-50 transition-all duration-200 flex items-center justify-center">
                        <div
                            class="w-0 h-0 bg-primary-500 rounded-full hover:w-2 hover:h-2 transition-all duration-200">
                        </div>
                    </div>
                    @endif
                </button>
            </div>

            {{-- Enhanced Task Info --}}
            <div class="col-span-4">
                <div class="space-y-2">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 cursor-pointer" wire:click="viewDetails">
                            <h4
                                class="font-semibold text-gray-900 text-sm leading-snug {{ $task->status === 'completed' ? 'line-through text-gray-500' : '' }} hover:text-primary-600 transition-colors">
                                {{ $task->title }}
                            </h4>
                            @if($task->description)
                            <p class="text-xs text-gray-500 mt-1 line-clamp-2 leading-relaxed">
                                {{ $task->description }}
                            </p>
                            @endif
                        </div>

                        {{-- Quick Actions --}}
                        <div
                            class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all duration-200 ml-2">
                            <button @click="showDetails = !showDetails"
                                class="p-1.5 hover:bg-primary-100 rounded-lg transition-colors"
                                x-bind:class="{ 'bg-primary-100 text-primary-600': showDetails }" title="View Details">
                                <x-heroicon-o-eye class="w-4 h-4" />
                            </button>
                            <button wire:click="editTask"
                                class="p-1.5 hover:bg-blue-100 text-blue-600 rounded-lg transition-colors"
                                title="Edit Task">
                                <x-heroicon-o-pencil class="w-4 h-4" />
                            </button>
                            <button wire:click="deleteTask" wire:confirm="Are you sure you want to delete this task?"
                                class="p-1.5 hover:bg-red-100 text-red-600 rounded-lg transition-colors"
                                title="Delete Task">
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    {{-- Enhanced Subtasks Indicator --}}
                    @if($task->subtasks && $task->subtasks->count() > 0)
                    <div class="flex items-center gap-2">
                        <button wire:click="toggleSubtasks"
                            class="flex items-center gap-2 text-xs text-gray-500 hover:text-primary-600 transition-colors bg-gray-50 hover:bg-primary-50 px-2 py-1 rounded-md">
                            <x-heroicon-o-list-bullet class="w-3 h-3" />
                            <span>{{ $task->getCompletedSubtasksCount() }}/{{ $task->getTotalSubtasksCount() }}
                                subtasks</span>
                            <x-heroicon-o-chevron-down
                                class="w-3 h-3 {{ $showSubtasks ? 'rotate-180' : '' }} transition-transform duration-200" />
                        </button>

                        {{-- Enhanced Progress Bar --}}
                        @php
                        $progress = $task->subtasks->count() > 0
                        ? round(($task->subtasks->where('status', 'completed')->count() / $task->subtasks->count()) *
                        100)
                        : 0;
                        @endphp
                        <div class="flex-1 max-w-24">
                            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                <div class="h-2 rounded-full transition-all duration-500 ease-out {{ $progress === 100 ? 'bg-green-500' : 'bg-primary-500' }}"
                                    style="width: {{ $progress }}%">
                                    <div class="w-full h-full bg-white opacity-30 animate-pulse"></div>
                                </div>
                            </div>
                        </div>
                        <span class="text-xs font-medium text-gray-600 min-w-[2rem] text-right">{{ $progress }}%</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Enhanced Status Dropdown --}}
            <div class="col-span-2">
                <div class="relative" x-data="{ statusOpen: false, buttonRect: {} }">
                    <button @click="statusOpen = !statusOpen; buttonRect = $el.getBoundingClientRect()"
                        x-ref="statusButton" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105 w-full justify-center shadow-sm border
                    {{ match($task->status) {
                        'completed' => 'bg-green-100 text-green-800 border-green-200 hover:bg-green-200',
                        'in_progress' => 'bg-yellow-100 text-yellow-800 border-yellow-200 hover:bg-yellow-200',
                        'pending' => 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200',
                        'cancelled' => 'bg-red-100 text-red-800 border-red-200 hover:bg-red-200',
                        default => 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200'
                    } }}">
                        <div class="w-2 h-2 rounded-full animate-pulse
                    {{ match($task->status) {
                        'completed' => 'bg-green-500',
                        'in_progress' => 'bg-yellow-500',
                        'pending' => 'bg-gray-400',
                        'cancelled' => 'bg-red-500',
                        default => 'bg-gray-400'
                    } }}">
                        </div>
                        <span>{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                        <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform duration-200"
                            x-bind:class="{ 'rotate-180': statusOpen }" />
                    </button>

                    {{-- Teleport dropdown to body to avoid overflow issues --}}
                    <template x-teleport="body">
                        <div x-show="statusOpen" x-cloak @click.away="statusOpen = false"
                            @keydown.escape="statusOpen = false" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="fixed w-44 bg-white rounded-xl shadow-xl border border-gray-200 py-2 overflow-hidden"
                            style="z-index: 9999;" x-bind:style="{
                         top: (buttonRect.bottom + window.scrollY + 8) + 'px',
                         left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 176 - 8)) + 'px'
                     }">

                            @foreach($this->getStatusOptions() as $statusValue => $statusLabel)
                            <button wire:click="updateStatus('{{ $statusValue }}')" @click="statusOpen = false"
                                class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent transition-all duration-200 flex items-center gap-3 {{ $task->status === $statusValue ? 'bg-primary-50 text-primary-700 border-l-4 border-l-primary-500' : '' }}">
                                <div class="w-3 h-3 rounded-full 
                                {{ match($statusValue) {
                                    'completed' => 'bg-green-500',
                                    'in_progress' => 'bg-yellow-500',
                                    'pending' => 'bg-gray-400',
                                    'cancelled' => 'bg-red-500',
                                    default => 'bg-gray-400'
                                } }}">
                                </div>
                                <span class="font-medium">{{ $statusLabel }}</span>
                                @if($task->status === $statusValue)
                                <x-heroicon-s-check class="w-4 h-4 text-primary-600 ml-auto" />
                                @endif
                            </button>
                            @endforeach
                        </div>
                    </template>
                </div>
            </div>

            {{-- Enhanced Priority Badge --}}
            <div class="col-span-1">
                <div class="flex justify-center">
                    <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-bold transition-all duration-200 hover:scale-105 shadow-sm border
                        {{ match($task->priority) {
                            'urgent' => 'bg-red-100 text-red-800 border-red-200 animate-pulse',
                            'high' => 'bg-orange-100 text-orange-800 border-orange-200',
                            'normal' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'low' => 'bg-gray-100 text-gray-600 border-gray-200',
                            default => 'bg-gray-100 text-gray-600 border-gray-200'
                        } }}">
                        @php
                        $priorityIcon = match($task->priority) {
                        'urgent' => 'heroicon-s-exclamation-triangle',
                        'high' => 'heroicon-o-exclamation-triangle',
                        'normal' => 'heroicon-o-minus',
                        'low' => 'heroicon-o-arrow-down',
                        default => 'heroicon-o-minus'
                        };
                        @endphp
                        <x-dynamic-component :component="$priorityIcon" class="w-3 h-3" />
                        <span>{{ ucfirst($task->priority) }}</span>
                    </span>
                </div>
            </div>

            {{-- Enhanced Assignee Section --}}
            <div class="col-span-2">
                @if($task->assignedUsers && $task->assignedUsers->count() > 0)
                <div class="flex items-center gap-3">
                    <div class="flex -space-x-2">
                        @foreach($task->assignedUsers->take(3) as $user)
                        <div class="w-8 h-8 bg-gradient-to-br from-primary-400 to-primary-600 text-white rounded-full flex items-center justify-center text-xs font-bold border-2 border-white shadow-sm hover:scale-110 transition-transform duration-200 cursor-pointer"
                            title="{{ $user->name }}" x-data @mouseenter="$el.style.zIndex = 10"
                            @mouseleave="$el.style.zIndex = 1">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        @endforeach
                        @if($task->assignedUsers->count() > 3)
                        <div class="w-8 h-8 bg-gray-400 text-white rounded-full flex items-center justify-center text-xs font-bold border-2 border-white shadow-sm hover:scale-110 transition-transform duration-200 cursor-pointer"
                            title="+{{ $task->assignedUsers->count() - 3 }} more">
                            +{{ $task->assignedUsers->count() - 3 }}
                        </div>
                        @endif
                    </div>
                    @if($task->assignedUsers->count() === 1)
                    <span class="text-sm font-medium text-gray-700 truncate">{{ $task->assignedUsers->first()->name
                        }}</span>
                    @else
                    <span class="text-xs text-gray-500">{{ $task->assignedUsers->count() }} people</span>
                    @endif
                </div>
                @else
                <div class="flex items-center gap-3 text-gray-400">
                    <div
                        class="w-8 h-8 bg-gray-100 border-2 border-dashed border-gray-300 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors cursor-pointer">
                        <x-heroicon-o-plus class="w-4 h-4" />
                    </div>
                    <span class="text-sm">Unassigned</span>
                </div>
                @endif
            </div>

            {{-- Enhanced Project Badge --}}
            <div class="col-span-1">
                <div class="flex justify-center">
                    @if($task->project)
                    <div class="flex items-center gap-1.5 px-3 py-2 bg-gradient-to-r from-indigo-100 to-purple-100 text-indigo-800 rounded-lg text-xs font-semibold max-w-full hover:from-indigo-200 hover:to-purple-200 transition-all duration-200 border border-indigo-200 shadow-sm"
                        title="{{ $task->project->name }}">
                        <x-heroicon-o-folder class="w-3 h-3 flex-shrink-0" />
                        <span class="truncate">{{ Str::limit($task->project->name, 8) }}</span>
                    </div>
                    @else
                    <div class="flex items-center gap-1.5 px-3 py-2 bg-gray-100 border-2 border-dashed border-gray-300 text-gray-500 rounded-lg text-xs hover:bg-gray-200 transition-colors cursor-pointer"
                        title="No project assigned">
                        <x-heroicon-o-folder-plus class="w-3 h-3" />
                        <span>None</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Enhanced Due Date --}}
            <div class="col-span-1">
                <div class="flex flex-col items-start space-y-1">
                    <div class="flex items-center gap-2 text-sm">
                        @php
                        $isOverdue = $task->task_date->isPast() && $task->status !== 'completed';
                        $isToday = $task->task_date->isToday();
                        $isTomorrow = $task->task_date->isTomorrow();
                        @endphp

                        <div
                            class="p-1 rounded {{ $isOverdue ? 'bg-red-100 text-red-600' : ($isToday ? 'bg-yellow-100 text-yellow-600' : 'text-gray-600') }}">
                            <x-heroicon-o-calendar-days class="w-4 h-4" />
                        </div>

                        <div class="flex flex-col">
                            <span
                                class="font-medium {{ $isOverdue ? 'text-red-600' : ($isToday ? 'text-yellow-600' : 'text-gray-700') }}">
                                @if($isToday)
                                Today
                                @elseif($isTomorrow)
                                Tomorrow
                                @else
                                {{ $task->task_date->format('M d') }}
                                @endif
                            </span>

                            @if($isOverdue)
                            <span class="text-xs text-red-500 font-medium">Overdue</span>
                            @elseif($task->task_date->diffInDays() <= 3 && !$task->task_date->isPast())
                                <span class="text-xs text-yellow-600">Soon</span>
                                @endif
                        </div>
                    </div>

                    @if($task->start_time || $task->end_time)
                    <div class="text-xs text-gray-500 ml-6 flex items-center gap-1">
                        <x-heroicon-o-clock class="w-3 h-3" />
                        @if($task->start_time)
                        {{ $task->start_time->format('H:i') }}
                        @endif
                        @if($task->start_time && $task->end_time)
                        -
                        @endif
                        @if($task->end_time)
                        {{ $task->end_time->format('H:i') }}
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile & Tablet View (below lg) --}}
    <div class="lg:hidden">
        <div class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-all duration-200">
            {{-- Mobile Header Row --}}
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-start gap-3 flex-1">
                    {{-- Completion Toggle --}}
                    <button wire:click="toggleTaskCompletion"
                        class="flex-shrink-0 hover:scale-110 transition-transform duration-200 mt-0.5"
                        x-bind:class="{ 'animate-bounce': completionToggling }"
                        @click="completionToggling = true; setTimeout(() => completionToggling = false, 600)">
                        @if($task->status === 'completed')
                        <div class="relative">
                            <x-heroicon-s-check-circle class="w-6 h-6 text-green-500" />
                            <div class="absolute inset-0 bg-green-500 rounded-full animate-ping opacity-25"></div>
                        </div>
                        @else
                        <div
                            class="w-6 h-6 rounded-full border-2 border-gray-300 hover:border-primary-500 hover:bg-primary-50 transition-all duration-200 flex items-center justify-center">
                            <div
                                class="w-0 h-0 bg-primary-500 rounded-full hover:w-3 hover:h-3 transition-all duration-200">
                            </div>
                        </div>
                        @endif
                    </button>

                    {{-- Task Title & Description --}}
                    <div class="flex-1 min-w-0">
                        <h4
                            class="font-semibold text-gray-900 text-base leading-snug {{ $task->status === 'completed' ? 'line-through text-gray-500' : '' }} hover:text-primary-600 transition-colors">
                            {{ $task->title }}
                        </h4>
                        @if($task->description)
                        <p class="text-sm text-gray-500 mt-1 line-clamp-2">
                            {{ $task->description }}
                        </p>
                        @endif
                    </div>
                </div>

                {{-- Mobile Actions Menu --}}
                <div class="flex items-center gap-2 ml-3" x-data="{ showActions: false }">
                    <button @click="showActions = !showActions"
                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                        x-bind:class="{ 'bg-gray-100': showActions }">
                        <x-heroicon-o-ellipsis-horizontal class="w-5 h-5 text-gray-500" />
                    </button>

                    {{-- Actions Dropdown --}}
                    <div x-show="showActions" @click.away="showActions = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50">
                        <button @click="showDetails = !showDetails; showActions = false"
                            class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3">
                            <x-heroicon-o-eye class="w-4 h-4" />
                            <span x-text="showDetails ? 'Hide Details' : 'Show Details'"></span>
                        </button>
                        <button wire:click="editTask" @click="showActions = false"
                            class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3">
                            <x-heroicon-o-pencil class="w-4 h-4 text-blue-600" />
                            <span>Edit Task</span>
                        </button>
                        <button wire:click="deleteTask" wire:confirm="Are you sure you want to delete this task?"
                            @click="showActions = false"
                            class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3">
                            <x-heroicon-o-trash class="w-4 h-4 text-red-600" />
                            <span>Delete Task</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Mobile Meta Row --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                {{-- Status --}}
                <div class="flex flex-col space-y-1">
                    <span class="text-xs text-gray-500 font-medium">Status</span>
                    <div class="relative" x-data="{ statusOpen: false, buttonRect: {} }">
                        <button @click="statusOpen = !statusOpen; buttonRect = $el.getBoundingClientRect()" class="inline-flex items-center gap-2 px-2 py-1.5 rounded-md text-xs font-semibold transition-all duration-200 w-full justify-start shadow-sm border
                        {{ match($task->status) {
                            'completed' => 'bg-green-100 text-green-800 border-green-200',
                            'in_progress' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                            'pending' => 'bg-gray-100 text-gray-700 border-gray-200',
                            'cancelled' => 'bg-red-100 text-red-800 border-red-200',
                            default => 'bg-gray-100 text-gray-700 border-gray-200'
                        } }}">
                            <div class="w-2 h-2 rounded-full
                            {{ match($task->status) {
                                'completed' => 'bg-green-500',
                                'in_progress' => 'bg-yellow-500',
                                'pending' => 'bg-gray-400',
                                'cancelled' => 'bg-red-500',
                                default => 'bg-gray-400'
                            } }}">
                            </div>
                            <span class="truncate">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                        </button>

                        {{-- Mobile Status Dropdown --}}
                        <template x-teleport="body">
                            <div x-show="statusOpen" x-cloak @click.away="statusOpen = false"
                                @keydown.escape="statusOpen = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="fixed w-48 bg-white rounded-xl shadow-xl border border-gray-200 py-2 overflow-hidden"
                                style="z-index: 9999;" x-bind:style="{
                                 top: (buttonRect.bottom + window.scrollY + 8) + 'px',
                                 left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 192 - 8)) + 'px'
                             }">
                                @foreach($this->getStatusOptions() as $statusValue => $statusLabel)
                                <button wire:click="updateStatus('{{ $statusValue }}')" @click="statusOpen = false"
                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent transition-all duration-200 flex items-center gap-3 {{ $task->status === $statusValue ? 'bg-primary-50 text-primary-700 border-l-4 border-l-primary-500' : '' }}">
                                    <div class="w-3 h-3 rounded-full 
                                    {{ match($statusValue) {
                                        'completed' => 'bg-green-500',
                                        'in_progress' => 'bg-yellow-500',
                                        'pending' => 'bg-gray-400',
                                        'cancelled' => 'bg-red-500',
                                        default => 'bg-gray-400'
                                    } }}">
                                    </div>
                                    <span class="font-medium">{{ $statusLabel }}</span>
                                    @if($task->status === $statusValue)
                                    <x-heroicon-s-check class="w-4 h-4 text-primary-600 ml-auto" />
                                    @endif
                                </button>
                                @endforeach
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Priority --}}
                <div class="flex flex-col space-y-1">
                    <span class="text-xs text-gray-500 font-medium">Priority</span>
                    <span class="inline-flex items-center gap-1.5 px-2 py-1.5 rounded-md text-xs font-bold w-fit shadow-sm border
                        {{ match($task->priority) {
                            'urgent' => 'bg-red-100 text-red-800 border-red-200',
                            'high' => 'bg-orange-100 text-orange-800 border-orange-200',
                            'normal' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'low' => 'bg-gray-100 text-gray-600 border-gray-200',
                            default => 'bg-gray-100 text-gray-600 border-gray-200'
                        } }}">
                        @php
                        $priorityIcon = match($task->priority) {
                        'urgent' => 'heroicon-s-exclamation-triangle',
                        'high' => 'heroicon-o-exclamation-triangle',
                        'normal' => 'heroicon-o-minus',
                        'low' => 'heroicon-o-arrow-down',
                        default => 'heroicon-o-minus'
                        };
                        @endphp
                        <x-dynamic-component :component="$priorityIcon" class="w-3 h-3" />
                        <span>{{ ucfirst($task->priority) }}</span>
                    </span>
                </div>

                {{-- Due Date --}}
                <div class="flex flex-col space-y-1 sm:col-span-2">
                    <span class="text-xs text-gray-500 font-medium">Due Date</span>
                    <div class="flex items-center gap-2">
                        @php
                        $isOverdue = $task->task_date->isPast() && $task->status !== 'completed';
                        $isToday = $task->task_date->isToday();
                        $isTomorrow = $task->task_date->isTomorrow();
                        @endphp

                        <div class="flex items-center gap-2 text-sm">
                            <div
                                class="p-1 rounded {{ $isOverdue ? 'bg-red-100 text-red-600' : ($isToday ? 'bg-yellow-100 text-yellow-600' : 'text-gray-600') }}">
                                <x-heroicon-o-calendar-days class="w-4 h-4" />
                            </div>
                            <div class="flex flex-col">
                                <span
                                    class="font-medium {{ $isOverdue ? 'text-red-600' : ($isToday ? 'text-yellow-600' : 'text-gray-700') }}">
                                    @if($isToday)
                                    Today
                                    @elseif($isTomorrow)
                                    Tomorrow
                                    @else
                                    {{ $task->task_date->format('M d') }}
                                    @endif
                                </span>
                                @if($isOverdue)
                                <span class="text-xs text-red-500 font-medium">Overdue</span>
                                @elseif($task->task_date->diffInDays() <= 3 && !$task->task_date->isPast())
                                    <span class="text-xs text-yellow-600">Soon</span>
                                    @endif
                            </div>
                        </div>

                        @if($task->start_time || $task->end_time)
                        <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                            <x-heroicon-o-clock class="w-3 h-3" />
                            @if($task->start_time)
                            {{ $task->start_time->format('H:i') }}
                            @endif
                            @if($task->start_time && $task->end_time)
                            -
                            @endif
                            @if($task->end_time)
                            {{ $task->end_time->format('H:i') }}
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Mobile Assignees & Project Row --}}
            <div class="flex items-center justify-between mb-4">
                {{-- Assignees --}}
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-500 font-medium">Assigned to:</span>
                    @if($task->assignedUsers && $task->assignedUsers->count() > 0)
                    <div class="flex items-center gap-2">
                        <div class="flex -space-x-1">
                            @foreach($task->assignedUsers->take(2) as $user)
                            <div class="w-6 h-6 bg-gradient-to-br from-primary-400 to-primary-600 text-white rounded-full flex items-center justify-center text-xs font-bold border-2 border-white shadow-sm"
                                title="{{ $user->name }}">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            @endforeach
                            @if($task->assignedUsers->count() > 2)
                            <div class="w-6 h-6 bg-gray-400 text-white rounded-full flex items-center justify-center text-xs font-bold border-2 border-white shadow-sm"
                                title="+{{ $task->assignedUsers->count() - 2 }} more">
                                +{{ $task->assignedUsers->count() - 2 }}
                            </div>
                            @endif
                        </div>
                        @if($task->assignedUsers->count() === 1)
                        <span class="text-sm font-medium text-gray-700 truncate max-w-24">{{
                            $task->assignedUsers->first()->name }}</span>
                        @else
                        <span class="text-xs text-gray-500">{{ $task->assignedUsers->count() }} people</span>
                        @endif
                    </div>
                    @else
                    <div class="flex items-center gap-2 text-gray-400">
                        <div
                            class="w-6 h-6 bg-gray-100 border border-dashed border-gray-300 rounded-full flex items-center justify-center">
                            <x-heroicon-o-plus class="w-3 h-3" />
                        </div>
                        <span class="text-sm">Unassigned</span>
                    </div>
                    @endif
                </div>

                {{-- Project --}}
                @if($task->project)
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 font-medium">Project:</span>
                    <div class="flex items-center gap-1.5 px-2 py-1 bg-gradient-to-r from-indigo-100 to-purple-100 text-indigo-800 rounded-md text-xs font-semibold border border-indigo-200"
                        title="{{ $task->project->name }}">
                        <x-heroicon-o-folder class="w-3 h-3 flex-shrink-0" />
                        <span class="truncate max-w-20">{{ Str::limit($task->project->name, 15) }}</span>
                    </div>
                </div>
                @endif
            </div>

            {{-- Mobile Subtasks Indicator --}}
            @if($task->subtasks && $task->subtasks->count() > 0)
            <div class="border-t border-gray-100 pt-3">
                <div class="flex items-center justify-between">
                    <button wire:click="toggleSubtasks"
                        class="flex items-center gap-3 text-sm text-gray-500 hover:text-primary-600 transition-colors bg-gray-50 hover:bg-primary-50 px-3 py-2 rounded-lg flex-1 mr-3">
                        <x-heroicon-o-list-bullet class="w-4 h-4" />
                        <span>{{ $task->getCompletedSubtasksCount() }}/{{ $task->getTotalSubtasksCount() }}
                            subtasks</span>
                        <x-heroicon-o-chevron-down
                            class="w-4 h-4 {{ $showSubtasks ? 'rotate-180' : '' }} transition-transform duration-200" />
                    </button>

                    {{-- Mobile Progress Bar --}}
                    @php
                    $progress = $task->subtasks->count() > 0
                    ? round(($task->subtasks->where('status', 'completed')->count() / $task->subtasks->count()) * 100)
                    : 0;
                    @endphp
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="flex-1 min-w-0">
                            <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                <div class="h-2.5 rounded-full transition-all duration-500 ease-out {{ $progress === 100 ? 'bg-green-500' : 'bg-primary-500' }}"
                                    style="width: {{ $progress }}%">
                                    <div class="w-full h-full bg-white opacity-30 animate-pulse"></div>
                                </div>
                            </div>
                        </div>
                        <span class="text-sm font-medium text-gray-600 whitespace-nowrap">{{ $progress }}%</span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Shared Subtasks Section (both desktop and mobile) --}}
    <div x-show="showSubtasks" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="lg:ml-8 lg:pl-6 lg:border-l-2 border-gradient-to-b from-primary-300 to-primary-100">
        @if($task->subtasks && $task->subtasks->count() > 0)
        <div class="space-y-3 bg-gray-50 lg:bg-transparent p-4 lg:p-0 rounded-lg lg:rounded-none">
            {{-- Subtasks Header --}}
            <div class="flex items-center justify-between pb-2 border-b border-gray-200 lg:border-gray-100">
                <h5 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <x-heroicon-o-list-bullet class="w-4 h-4" />
                    Subtasks
                </h5>
                <span class="text-xs text-gray-500 bg-white lg:bg-gray-100 px-2 py-1 rounded-full border lg:border-0">
                    {{ $task->getCompletedSubtasksCount() }}/{{ $task->getTotalSubtasksCount() }} completed
                </span>
            </div>

            {{-- Existing Subtasks --}}
            <div class="space-y-2">
                @foreach($task->subtasks as $subtask)
                <div class="flex items-center justify-between group/subtask p-3 rounded-lg hover:bg-white lg:hover:bg-gray-50 transition-all duration-200 border border-gray-200 lg:border-transparent hover:border-gray-300 lg:hover:border-gray-200 bg-white lg:bg-transparent"
                    x-data="{ subtaskHovered: false }" @mouseenter="subtaskHovered = true"
                    @mouseleave="subtaskHovered = false">
                    <div class="flex items-center gap-3 flex-1">
                        <button wire:click="toggleSubtask({{ $subtask->id }})"
                            class="flex-shrink-0 hover:scale-110 transition-transform duration-200">
                            @if($subtask->status === 'completed')
                            <x-heroicon-s-check-circle class="w-5 h-5 text-green-500" />
                            @else
                            <div
                                class="w-5 h-5 rounded-full border-2 border-gray-300 hover:border-primary-500 hover:bg-primary-50 transition-all duration-200 flex items-center justify-center">
                                <div
                                    class="w-0 h-0 bg-primary-500 rounded-full hover:w-2 hover:h-2 transition-all duration-200">
                                </div>
                            </div>
                            @endif
                        </button>

                        <span
                            class="text-sm font-medium {{ $subtask->status === 'completed' ? 'line-through text-gray-500' : 'text-gray-700' }} transition-colors flex-1">
                            {{ $subtask->title }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span
                            class="text-xs text-gray-400 opacity-0 group-hover/subtask:opacity-100 transition-opacity lg:block hidden">
                            {{ ucfirst($subtask->status) }}
                        </span>
                        <button wire:click="deleteSubtask({{ $subtask->id }})" wire:confirm="Delete this subtask?"
                            class="p-1.5 hover:bg-red-100 text-red-600 rounded-lg transition-all duration-200 opacity-0 group-hover/subtask:opacity-100"
                            x-show="subtaskHovered" x-transition>
                            <x-heroicon-o-trash class="w-4 h-4" />
                        </button>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Add New Subtask Form --}}
            <div class="pt-3 border-t border-gray-200 lg:border-gray-100">
                <form wire:submit="addSubtask" class="flex gap-3">
                    <div class="flex-1">
                        {{ $this->newSubtaskForm }}
                    </div>
                    <x-filament::button type="submit" size="sm" icon="heroicon-o-plus" color="primary">
                        <span class="hidden sm:inline">Add</span>
                        <span class="sm:hidden">+</span>
                    </x-filament::button>
                </form>
            </div>
        </div>
        @else
        <div class="text-center py-6 text-gray-500 bg-gray-50 lg:bg-transparent rounded-lg lg:rounded-none">
            <x-heroicon-o-list-bullet class="w-8 h-8 mx-auto mb-2 text-gray-300" />
            <p class="text-sm">No subtasks yet</p>
            <form wire:submit="addSubtask" class="flex gap-3 mt-3 max-w-md mx-auto">
                <div class="flex-1">
                    {{ $this->newSubtaskForm }}
                </div>
                <x-filament::button type="submit" size="sm" icon="heroicon-o-plus" color="primary">
                    <span class="hidden sm:inline">Add First Subtask</span>
                    <span class="sm:hidden">Add</span>
                </x-filament::button>
            </form>
        </div>
        @endif
    </div>

    {{-- Shared Task Details Panel (both desktop and mobile) --}}
    <div x-show="showDetails" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="lg:ml-8 p-4 bg-gradient-to-r from-gray-50 to-gray-25 rounded-lg border border-gray-200">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 text-sm">
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-gray-600">
                    <x-heroicon-o-calendar class="w-4 h-4" />
                    <span class="font-medium">Created:</span>
                    <span>{{ $task->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="flex items-center gap-2 text-gray-600">
                    <x-heroicon-o-user class="w-4 h-4" />
                    <span class="font-medium">Creator:</span>
                    <span>{{ $task->creator->name }}</span>
                </div>
                @if($task->start_task_date)
                <div class="flex items-center gap-2 text-gray-600">
                    <x-heroicon-o-play class="w-4 h-4" />
                    <span class="font-medium">Started:</span>
                    <span>{{ $task->start_task_date->format('M d, Y') }}</span>
                </div>
                @endif
            </div>

            <div class="space-y-3">
                @if($task->start_task_date)
                <div class="flex items-center gap-2 text-gray-600">
                    <x-heroicon-o-clock class="w-4 h-4" />
                    <span class="font-medium">Days in Progress:</span>
                    <span>{{ $task->getDaysInProgress() }} days</span>
                </div>
                @endif

                @if($task->isOverdue())
                <div class="flex items-center gap-2 text-red-600">
                    <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                    <span class="font-medium">Status:</span>
                    <span>Overdue</span>
                </div>
                @endif
            </div>
        </div>

        @if($task->description)
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex items-start gap-2 text-gray-600">
                <x-heroicon-o-document-text class="w-4 h-4 mt-0.5" />
                <div class="flex-1">
                    <span class="font-medium">Description:</span>
                    <p class="mt-1 text-gray-700 leading-relaxed">{{ $task->description }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <script>
        function taskItemManager(taskId) {
    return {
        taskId: taskId,
        selected: false,
        showDetails: false,
        showSubtasks: @js($showSubtasks ?? false),
        completionToggling: false,
        
        init() {
            // Listen for global selection events
            this.$watch('selected', (value) => {
                this.$dispatch('task-selection-changed', { taskId: this.taskId, selected: value });
            });
        }
    }
}
    </script>
</div>