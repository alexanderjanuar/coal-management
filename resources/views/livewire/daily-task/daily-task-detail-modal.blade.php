{{-- resources/views/livewire/daily-task/daily-task-detail-modal.blade.php --}}
<div>
    <x-filament::modal id="task-detail-modal" width="6xl" :close-by-clicking-away="false" slide-over>
        <x-slot name="heading">
            Modal heading
        </x-slot>
        @if($task)
        <div class="h-[100vh] flex flex-col">
            {{-- Header --}}
            <div class="flex-shrink-0 border-b border-gray-200 p-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4 flex-1">
                        {{-- Completion Toggle --}}
                        <button wire:click="toggleTaskCompletion"
                            class="mt-1 hover:scale-105 transition-transform duration-200">
                            @if($task->status === 'completed')
                            <x-heroicon-s-check-circle class="w-6 h-6 text-green-500" />
                            @else
                            <div
                                class="w-6 h-6 rounded-full border-2 border-gray-300 hover:border-blue-500 transition-colors">
                            </div>
                            @endif
                        </button>

                        {{-- Task Title --}}
                        <div class="flex-1">
                            <h1
                                class="text-2xl font-semibold text-gray-900 mb-3 {{ $task->status === 'completed' ? 'line-through text-gray-500' : '' }}">
                                {{ $task->title }}
                            </h1>

                            {{-- Status Pills --}}
                            <div class="flex items-center gap-3">
                                {{-- Status --}}
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open"
                                        class="flex items-center gap-2 px-3 py-1 rounded-lg text-sm font-medium bg-gray-100 hover:bg-gray-200 transition-colors">
                                        <div class="w-2 h-2 rounded-full {{ match($task->status) {
                                            'completed' => 'bg-green-500',
                                            'in_progress' => 'bg-yellow-500',
                                            'pending' => 'bg-gray-400',
                                            'cancelled' => 'bg-red-500',
                                            default => 'bg-gray-400'
                                        } }}"></div>
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </button>

                                    <div x-show="open" @click.away="open = false"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        class="absolute left-0 top-full mt-2 w-44 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                                        @foreach($this->getStatusOptions() as $statusValue => $statusLabel)
                                        <button wire:click="updateStatus('{{ $statusValue }}')" @click="open = false"
                                            class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 flex items-center gap-3">
                                            <div class="w-2 h-2 rounded-full {{ match($statusValue) {
                                                'completed' => 'bg-green-500',
                                                'in_progress' => 'bg-yellow-500',
                                                'pending' => 'bg-gray-400',
                                                'cancelled' => 'bg-red-500',
                                                default => 'bg-gray-400'
                                            } }}"></div>
                                            {{ $statusLabel }}
                                        </button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Priority --}}
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open"
                                        class="flex items-center gap-2 px-3 py-1 rounded-lg text-sm font-medium bg-gray-100 hover:bg-gray-200 transition-colors">
                                        {{ ucfirst($task->priority) }}
                                    </button>

                                    <div x-show="open" @click.away="open = false"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        class="absolute left-0 top-full mt-2 w-32 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                                        @foreach($this->getPriorityOptions() as $priorityValue => $priorityLabel)
                                        <button wire:click="updatePriority('{{ $priorityValue }}')"
                                            @click="open = false"
                                            class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50">
                                            {{ $priorityLabel }}
                                        </button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Due Date --}}
                                <div class="px-3 py-1 rounded-lg text-sm font-medium bg-gray-100">
                                    @if($task->task_date->isToday())
                                    Today
                                    @elseif($task->task_date->isTomorrow())
                                    Tomorrow
                                    @else
                                    {{ $task->task_date->format('M d') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 ml-4">
                        {{ $this->editAction }}
                        {{ $this->deleteAction }}
                    </div>
                </div>
            </div>

            {{-- Two Column Layout --}}
            <div class="flex-1 flex overflow-hidden">
                {{-- Left Column: Task Details & Subtasks --}}
                <div class="flex-1 flex flex-col p-6 border-r border-gray-200 overflow-y-auto">
                    {{-- Task Details --}}
                    <div class="space-y-6 mb-8">
                        {{-- Description --}}
                        @if($task->description)
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Description</h3>
                            <p class="text-gray-900 leading-relaxed">{{ $task->description }}</p>
                        </div>
                        @endif

                        {{-- Meta Information Grid --}}
                        <div class="grid grid-cols-2 gap-6">
                            {{-- Project --}}
                            @if($task->project)
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Project</h3>
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-folder class="w-4 h-4 text-gray-400" />
                                    <span class="text-gray-900">{{ $task->project->name }}</span>
                                </div>
                            </div>
                            @endif

                            {{-- Assignees --}}
                            @if($task->assignedUsers && $task->assignedUsers->count() > 0)
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Assigned to</h3>
                                <div class="flex items-center gap-2">
                                    <div class="flex -space-x-1">
                                        @foreach($task->assignedUsers->take(3) as $user)
                                        <div class="w-6 h-6 bg-gray-500 text-white rounded-full flex items-center justify-center text-xs font-medium border-2 border-white"
                                            title="{{ $user->name }}">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        @endforeach
                                        @if($task->assignedUsers->count() > 3)
                                        <div
                                            class="w-6 h-6 bg-gray-400 text-white rounded-full flex items-center justify-center text-xs font-medium border-2 border-white">
                                            +{{ $task->assignedUsers->count() - 3 }}
                                        </div>
                                        @endif
                                    </div>
                                    @if($task->assignedUsers->count() === 1)
                                    <span class="text-gray-900 ml-1">{{ $task->assignedUsers->first()->name }}</span>
                                    @endif
                                </div>
                            </div>
                            @endif

                            {{-- Time Info --}}
                            @if($task->start_time || $task->end_time)
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Time</h3>
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                                    <span class="text-gray-900">
                                        @if($task->start_time) {{ $task->start_time->format('H:i') }} @endif
                                        @if($task->start_time && $task->end_time) - @endif
                                        @if($task->end_time) {{ $task->end_time->format('H:i') }} @endif
                                    </span>
                                </div>
                            </div>
                            @endif

                            {{-- Created By --}}
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Created by</h3>
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-6 h-6 bg-gray-500 text-white rounded-full flex items-center justify-center text-xs font-medium">
                                        {{ strtoupper(substr($task->creator->name, 0, 1)) }}
                                    </div>
                                    <span class="text-gray-900">{{ $task->creator->name }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Progress Bar (if has subtasks) --}}
                        @if($task->subtasks && $task->subtasks->count() > 0)
                        @php
                        $completed = $task->subtasks->where('status', 'completed')->count();
                        $total = $task->subtasks->count();
                        $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-medium text-gray-500">Progress</h3>
                                <span class="text-sm text-gray-600">{{ $completed }}/{{ $total }} completed</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full transition-all duration-500"
                                    style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Subtasks Section --}}
                    @if($task->subtasks && $task->subtasks->count() > 0)
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Subtasks</h3>
                        <div class="space-y-3">
                            @foreach($task->subtasks as $subtask)
                            <div
                                class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                                <button wire:click="toggleSubtask({{ $subtask->id }})"
                                    class="flex-shrink-0 hover:scale-105 transition-transform">
                                    @if($subtask->status === 'completed')
                                    <x-heroicon-s-check-circle class="w-5 h-5 text-green-500" />
                                    @else
                                    <div
                                        class="w-5 h-5 rounded-full border-2 border-gray-300 hover:border-blue-500 transition-colors">
                                    </div>
                                    @endif
                                </button>
                                <span
                                    class="text-gray-900 {{ $subtask->status === 'completed' ? 'line-through text-gray-500' : '' }}">
                                    {{ $subtask->title }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Right Column: Comments --}}
                <div class="w-96 flex flex-col bg-gray-50">
                    {{-- Comments Header --}}
                    <div class="flex-shrink-0 p-6 border-b border-gray-200 bg-white">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Comments</h3>
                            @if($task->comments && $task->comments->count() > 0)
                            <span class="text-sm text-gray-500">{{ $task->comments->count() }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Comments Content --}}
                    <div class="flex-1 flex flex-col overflow-hidden">
                        {{-- Existing Comments --}}
                        @if($task->comments && $task->comments->count() > 0)
                        <div class="flex-1 overflow-y-auto p-6 space-y-4">
                            @foreach($task->comments->sortByDesc('created_at') as $comment)
                            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                                <div class="flex gap-3">
                                    <div
                                        class="w-8 h-8 bg-gray-500 text-white rounded-full flex items-center justify-center text-sm font-medium flex-shrink-0">
                                        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="font-medium text-gray-900">{{ $comment->user->name }}</span>
                                            <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans()
                                                }}</span>
                                        </div>
                                        <p class="text-gray-700 text-sm leading-relaxed">{{ $comment->content }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="flex-1 flex items-center justify-center p-6">
                            <div class="text-center">
                                <x-heroicon-o-chat-bubble-left-ellipsis class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                                <p class="text-gray-500 text-sm">No comments yet</p>
                            </div>
                        </div>
                        @endif

                        {{-- Comment Form --}}
                        <div class="flex-shrink-0 p-6 border-t border-gray-200 bg-white">
                            <form wire:submit="addComment" class="space-y-3">
                                <div class="flex gap-3">
                                    <div
                                        class="w-8 h-8 bg-gray-500 text-white rounded-full flex items-center justify-center text-sm font-medium flex-shrink-0">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-1">
                                        {{ $this->commentForm }}
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <x-filament::button type="submit" size="sm">
                                        Comment
                                    </x-filament::button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </x-filament::modal>
</div>