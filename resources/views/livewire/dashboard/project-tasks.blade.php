<div class="space-y-4">
    <!-- Header with Progress -->
    <div class="flex flex-col sm:flex-row sm:items-center space-y-3 sm:space-y-0 justify-between">
        <!-- Title -->
        <h4 class="flex items-center gap-2 text-sm font-medium text-gray-700">
            <x-heroicon-m-clipboard-document-list class="w-5 h-5" />
            Tasks
        </h4>

        <!-- Progress Bar -->
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="w-full sm:w-48 h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full transition-all duration-500 rounded-full
                    {{ $this->taskProgress === 100 ? 'bg-success-500' : 'bg-primary-500' }}"
                    style="width: {{ $this->taskProgress }}%">
                </div>
            </div>
            <span class="text-xs sm:text-sm text-gray-600 whitespace-nowrap">
                {{ number_format($this->taskProgress, 0) }}%
            </span>
        </div>
    </div>

    <!-- Tasks Grid -->
    <div class="grid gap-3 sm:gap-4">
        @foreach($step->tasks as $task)
        <div wire:key="task-{{ $task->id }}" x-data="{ open: false }"
            class="group bg-white rounded-lg sm:rounded-xl shadow-sm hover:shadow-md transition-all duration-300">
            <div class="p-3 sm:p-4">
                <!-- Task Content -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                    <!-- Task Info -->
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <!-- Status Indicator -->
                            <div @class([
                                'w-2 sm:w-2.5 h-2 sm:h-2.5 rounded-full transform transition-all duration-300',
                                'bg-success-500 scale-110' => $task->status === 'completed',
                                'bg-warning-500 animate-pulse' => $task->status === 'in_progress',
                                'bg-danger-500' => $task->status === 'blocked',
                                'bg-gray-300' => $task->status === 'pending'
                            ])></div>
            
                            <!-- Title -->
                            <h5 class="font-medium text-sm sm:text-base text-gray-900 truncate">
                                {{ $task->title }}
                            </h5>
                        </div>
                    </div>
            
                    <!-- Task Actions -->
                    <div class="flex items-center justify-between sm:justify-end gap-2 sm:gap-3">
                        <!-- Status Badge - Now showing full text on all screens -->
                        <x-filament::badge 
                            size="sm" 
                            :color="match($task->status) {
                                'completed' => 'success',
                                'in_progress' => 'warning',
                                'blocked' => 'danger',
                                default => 'secondary'
                            }"
                        >
                            {{ str_replace('_', ' ', \Str::title($task->status)) }}
                        </x-filament::badge>
            
                        <!-- Comments Button -->
                        <button 
                            x-on:click.stop="$dispatch('open-modal', { id: 'task-modal-{{ $task->id }}' })" 
                            class="inline-flex items-center gap-1.5 px-2 sm:px-2.5 py-1 sm:py-1.5 rounded-full text-xs sm:text-sm transition-all duration-200
                                {{ $task->comments()->count() > 0 
                                    ? 'bg-primary-50 text-primary-700 hover:bg-primary-100' 
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                        >
                            <x-heroicon-m-chat-bubble-left-right class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                            <span>{{ $task->comments()->count() }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Task Modal -->
            <x-filament::modal id="task-modal-{{ $task->id }}" width="4xl" slide-over>
                <div class="space-y-4">
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