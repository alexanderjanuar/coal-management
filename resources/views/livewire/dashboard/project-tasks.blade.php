<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h4 class="flex items-center gap-2 text-sm font-medium text-gray-700">
            <x-heroicon-m-clipboard-document-list class="w-5 h-5" />
            Tasks
        </h4>
        <!-- Task Progress Bar -->
        <div class="flex items-center gap-3">
            <div class="w-48 h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full transition-all duration-500 rounded-full
                    {{ $this->taskProgress === 100 ? 'bg-success-500' : 'bg-primary-500' }}"
                    style="width: {{ $this->taskProgress }}%">
                </div>
            </div>
            <span class="text-sm text-gray-600">{{ number_format($this->taskProgress, 0) }}%</span>
        </div>
    </div>

    <!-- Tasks Grid -->
    <div class="grid gap-4">
        @foreach($step->tasks as $task)
        <div wire:key="task-{{ $task->id }}" x-data="{ open: false }"
            class="group bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300">
            <div class="p-4">
                <div class="flex items-start justify-between gap-4">
                    <!-- Task Info -->
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div @class([ 'w-2.5 h-2.5 rounded-full transform transition-all duration-300'
                                , 'bg-success-500 scale-110'=> $task->status === 'completed',
                                'bg-warning-500 animate-pulse' => $task->status === 'in_progress',
                                'bg-danger-500' => $task->status === 'blocked',
                                'bg-gray-300' => $task->status === 'pending'
                                ])></div>
                            <h5 class="font-medium text-gray-900">{{ $task->title }}</h5>
                        </div>
                    </div>

                    <!-- Task Actions -->
                    <div class="flex items-center gap-3">
                        <x-filament::badge size="sm" :color="match($task->status) {
                                    'completed' => 'success',
                                    'in_progress' => 'warning',
                                    'blocked' => 'danger',
                                    default => 'secondary'
                                }">
                            {{ str_replace('_', ' ', \Str::title($task->status)) }}
                        </x-filament::badge>

                        <!-- Comments Button -->
                        <button x-on:click.stop="$dispatch('open-modal', { id: 'task-modal-{{ $task->id }}' })" 
                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-sm transition-all duration-200
                            {{ $task->comments()->count() > 0 
                                ? 'bg-primary-50 text-primary-700 hover:bg-primary-100' 
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            <x-heroicon-m-chat-bubble-left-right class="w-4 h-4" />
                            <span>{{ $task->comments()->count() }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Task Modal -->
            <x-filament::modal id="task-modal-{{ $task->id }}" width="4xl" slide-over>
                <div class="space-y-4">
                    <!-- Comments Section -->
                    @livewire('comments-modal', [
                    'modelType' => \App\Models\Task::class,
                    'modelId' => $task->id
                    ])
                </div>
            </x-filament::modal>
        </div>
        @endforeach
    </div>
</div>