{{-- Redesigned Daily Task Detail Modal - Responsive & Dark Mode --}}
<div>
    <x-filament::modal id="task-detail-modal" width="2xl" slide-over>
        @if($task)
        <div class="flex flex-col bg-white dark:bg-gray-900 min-h-screen md:min-h-auto">
            {{-- Header with Close Button --}}
            <div
                class="flex-shrink-0 flex items-center justify-between px-4 md:px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <button wire:click="closeModal"
                    class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    <x-heroicon-o-x-mark class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                </button>

                <div class="flex items-center gap-3">
                    {{ $this->deleteAction }}
                </div>
            </div>

            {{-- Content Area --}}
            <div class="flex-1 overflow-y-auto">
                <div class="px-4 md:px-6 py-4 md:py-6 space-y-4 md:space-y-6">
                    {{-- Task Title --}}
                    <div class="flex items-start gap-3 md:gap-4">
                        <button wire:click="toggleTaskCompletion" class="mt-1 flex-shrink-0">
                            @if($task->status === 'completed')
                            <x-heroicon-s-check-circle
                                class="w-5 h-5 md:w-6 md:h-6 text-green-500 dark:text-green-400" />
                            @else
                            <div
                                class="w-5 h-5 md:w-6 md:h-6 rounded-full border-2 border-gray-300 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 transition-colors">
                            </div>
                            @endif
                        </button>

                        <div class="flex-1 min-w-0">
                            @if($editingTitle)
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                                <div class="flex-1 w-full">
                                    {{ $this->taskEditForm }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <button wire:click="saveTitle"
                                        class="p-2 text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/50 rounded">
                                        <x-heroicon-o-check class="w-4 h-4" />
                                    </button>
                                    <button wire:click="cancelEditTitle"
                                        class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/50 rounded">
                                        <x-heroicon-o-x-mark class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                            @else
                            <div class="group flex items-start gap-3">
                                <h1
                                    class="text-lg sm:text-xl md:text-2xl font-semibold text-gray-900 dark:text-gray-100 leading-tight {{ $task->status === 'completed' ? 'line-through text-gray-500 dark:text-gray-400' : '' }}">
                                    {{ $task->title }}
                                </h1>
                                <button wire:click="startEditTitle"
                                    class="opacity-0 group-hover:opacity-100 p-1 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-all">
                                    <x-heroicon-o-pencil class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>

   

                    {{-- Task Meta Information --}}
                    <div class="space-y-3 md:space-y-4">
                        {{-- Created Time --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
                            <div class="flex items-center gap-3">
                                <x-heroicon-o-clock class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">Dibuat</span>
                            </div>
                            <span class="text-sm text-gray-900 dark:text-gray-100 sm:text-right">{{
                                $task->created_at->format('F j, Y') }} {{ $task->created_at->format('g:i A') }}</span>
                        </div>

                        {{-- Creator --}}
                        @if($task->creator)
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
                            <div class="flex items-center gap-3">
                                <x-heroicon-o-user class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">Dibuat oleh</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 bg-gradient-to-br from-primary-400 to-primary-600 dark:from-primary-500 dark:to-primary-700 text-white rounded-full flex items-center justify-center text-xs font-semibold">
                                    {{ strtoupper(substr($task->creator->name, 0, 1)) }}
                                </div>
                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $task->creator->name }}</span>
                            </div>
                        </div>
                        @endif

                        {{-- Status --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
                            <div class="flex items-center gap-3">
                                <x-heroicon-o-flag class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">Status</span>
                            </div>
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium border transition-colors {{ match($task->status) {
                                        'completed' => 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 border-green-200 dark:border-green-700',
                                        'in_progress' => 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 border-yellow-200 dark:border-yellow-700',
                                        'pending' => 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700',
                                        'cancelled' => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border-red-200 dark:border-red-700',
                                        default => 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700'
                                    } }}">
                                    <div class="w-2 h-2 rounded-full {{ match($task->status) {
                                        'completed' => 'bg-green-500 dark:bg-green-400',
                                        'in_progress' => 'bg-yellow-500 dark:bg-yellow-400',
                                        'pending' => 'bg-gray-400 dark:bg-gray-500',
                                        'cancelled' => 'bg-red-500 dark:bg-red-400',
                                        default => 'bg-gray-400 dark:bg-gray-500'
                                    } }}"></div>
                                    {{ $this->getStatusOptions()[$task->status] ?? $task->status }}
                                </button>

                                <div x-show="open" @click.away="open = false" x-cloak
                                    class="absolute right-0 sm:left-0 top-full mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 py-1 z-50">
                                    @foreach($this->getStatusOptions() as $statusValue => $statusLabel)
                                    <button wire:click="updateStatus('{{ $statusValue }}')" @click="open = false"
                                        class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-3 text-gray-700 dark:text-gray-300">
                                        <div class="w-2 h-2 rounded-full {{ match($statusValue) {
                                            'completed' => 'bg-green-500 dark:bg-green-400',
                                            'in_progress' => 'bg-yellow-500 dark:bg-yellow-400',
                                            'pending' => 'bg-gray-400 dark:bg-gray-500',
                                            'cancelled' => 'bg-red-500 dark:bg-red-400',
                                            default => 'bg-gray-400 dark:bg-gray-500'
                                        } }}"></div>
                                        {{ $statusLabel }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Priority --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
                            <div class="flex items-center gap-3">
                                <x-heroicon-o-exclamation-triangle
                                    class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">Priority</span>
                            </div>
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium border transition-colors {{ match($task->priority) {
                                        'urgent' => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border-red-200 dark:border-red-700',
                                        'high' => 'bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 border-orange-200 dark:border-orange-700',
                                        'normal' => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-700',
                                        'low' => 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700',
                                        default => 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700'
                                    } }}">
                                    {{ $this->getPriorityOptions()[$task->priority] ?? $task->priority }}
                                </button>

                                <div x-show="open" @click.away="open = false" x-cloak
                                    class="absolute right-0 sm:left-0 top-full mt-2 w-32 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 py-1 z-50">
                                    @foreach($this->getPriorityOptions() as $priorityValue => $priorityLabel)
                                    <button wire:click="updatePriority('{{ $priorityValue }}')" @click="open = false"
                                        class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        {{ $priorityLabel }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Due Date --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
                            <div class="flex items-center gap-3">
                                <x-heroicon-o-calendar class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">Due Date</span>
                            </div>
                            <span class="text-sm text-gray-900 dark:text-gray-100 break-words">
                                {{ $task->task_date->format('M j, Y') }}
                                @if($task->start_task_date && $task->start_task_date != $task->task_date)
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block">
                                        Dimulai: {{ $task->start_task_date->format('M j, Y') }}
                                    </span>
                                @endif
                            </span>
                        </div>

                        {{-- Assignees --}}
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 sm:gap-3">
                            <div class="flex items-center gap-3">
                                <x-heroicon-o-users class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">Assignees</span>
                            </div>
                            <div class="flex items-center justify-start sm:justify-end">
                                @if($task->assignedUsers && $task->assignedUsers->count() > 0)
                                <div class="flex items-center -space-x-1">
                                    @foreach($task->assignedUsers->take(4) as $user)
                                    <div class="w-6 h-6 bg-gradient-to-br from-primary-400 to-primary-600 dark:from-primary-500 dark:to-primary-700 text-white rounded-full flex items-center justify-center text-xs font-semibold border-2 border-white dark:border-gray-900 shadow-sm"
                                        title="{{ $user->name }}">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    @endforeach
                                    @if($task->assignedUsers->count() > 4)
                                    <div
                                        class="w-6 h-6 bg-gray-400 dark:bg-gray-600 text-white rounded-full flex items-center justify-center text-xs font-semibold border-2 border-white dark:border-gray-900 shadow-sm">
                                        +{{ $task->assignedUsers->count() - 4 }}
                                    </div>
                                    @endif
                                </div>
                                @else
                                <button
                                    class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                                    <x-heroicon-o-user-plus class="w-4 h-4" />
                                </button>
                                @endif
                            </div>
                        </div>

                        {{-- Project --}}
                        @if($task->project)
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
                            <div class="flex items-center gap-3">
                                <x-heroicon-o-folder class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">Project</span>
                            </div>
                            <span class="text-sm text-gray-900 dark:text-gray-100 sm:text-right break-words">{{ $task->project->name }}</span>
                        </div>
                        @endif

                        {{-- Client --}}
                        @if($task->project && $task->project->client)
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
                            <div class="flex items-center gap-3">
                                <x-heroicon-o-building-office class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">Client</span>
                            </div>
                            <span class="text-sm text-gray-900 dark:text-gray-100 sm:text-right break-words">{{ $task->project->client->name }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Project Description --}}
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Deskripsi Task</h3>
                        @if($editingDescription)
                        <div class="space-y-3 md:space-y-4">
                            {{ $this->descriptionForm }}
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                                <button wire:click="saveDescription"
                                    class="w-full sm:w-auto px-4 py-2 bg-primary-600 dark:bg-primary-700 text-white rounded-lg hover:bg-primary-700 dark:hover:bg-primary-800 transition-colors">
                                    Simpan
                                </button>
                                <button wire:click="cancelEditDescription"
                                    class="w-full sm:w-auto px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                                    Batal
                                </button>
                            </div>
                        </div>
                        @else
                        <div class="group relative">
                            @if($task->description)
                            <div
                                class="text-gray-700 dark:text-gray-300 leading-relaxed text-sm md:text-base break-words">
                                {!! $task->description !!}</div>
                            @else
                            <div class="text-gray-500 dark:text-gray-400 italic text-sm md:text-base">Tidak ada
                                deskripsi</div>
                            @endif
                            <button wire:click="startEditDescription"
                                class="absolute top-0 right-0 opacity-0 group-hover:opacity-100 p-1 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-all">
                                <x-heroicon-o-pencil class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                            </button>
                        </div>
                        @endif
                    </div>

                    {{-- Subtasks Section --}}
                    @if($task->subtasks && $task->subtasks->count() > 0)
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 md:pt-8">
                        @php
                        $completed = $task->subtasks->where('status', 'completed')->count();
                        $total = $task->subtasks->count();
                        $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
                        @endphp

                        {{-- Progress Header --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 md:mb-6">
                            <h3 class="text-base md:text-lg font-semibold text-gray-900 dark:text-gray-100">Subtasks
                            </h3>
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $completed }}/{{ $total }}
                                    selesai</span>
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-16 sm:w-24 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-primary-500 dark:bg-primary-600 rounded-full transition-all duration-500"
                                            style="width: {{ $progress }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $progress
                                        }}%</span>
                                </div>
                            </div>
                        </div>

                        {{-- Subtasks List --}}
                        <div class="space-y-2 md:space-y-3">
                            @foreach($task->subtasks->sortBy('id') as $subtask)
                            <div
                                class="group flex items-start gap-3 md:gap-4 p-3 md:p-4 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition-colors border border-transparent hover:border-gray-200 dark:hover:border-gray-600">
                                {{-- Completion Toggle --}}
                                <button wire:click="toggleSubtask({{ $subtask->id }})"
                                    class="flex-shrink-0 mt-0.5 hover:scale-110 transition-transform duration-200">
                                    @if($subtask->status === 'completed')
                                    <div class="relative">
                                        <x-heroicon-s-check-circle class="w-5 h-5 text-green-500 dark:text-green-400" />
                                        <div
                                            class="absolute inset-0 bg-green-500 dark:bg-green-400 rounded-full animate-ping opacity-25">
                                        </div>
                                    </div>
                                    @else
                                    <div
                                        class="w-5 h-5 rounded-full border-2 border-gray-300 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-all duration-200 flex items-center justify-center group">
                                        <div
                                            class="w-0 h-0 bg-primary-500 dark:bg-primary-400 rounded-full group-hover:w-2.5 group-hover:h-2.5 transition-all duration-200">
                                        </div>
                                    </div>
                                    @endif
                                </button>

                                {{-- Subtask Content --}}
                                <div class="flex-1 min-w-0">
                                    @if($editingSubtaskId === $subtask->id)
                                    {{-- Edit Mode --}}
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                                        <div class="flex-1 w-full">
                                            {{ $this->editSubtaskForm }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button wire:click="saveSubtaskEdit"
                                                class="p-2 bg-green-100 dark:bg-green-900/50 hover:bg-green-200 dark:hover:bg-green-900/70 text-green-700 dark:text-green-400 rounded-lg transition-all duration-200 hover:scale-110 shadow-sm"
                                                title="Simpan perubahan">
                                                <x-heroicon-o-check class="w-4 h-4" />
                                            </button>
                                            <button wire:click="cancelEditSubtask"
                                                class="p-2 bg-red-100 dark:bg-red-900/50 hover:bg-red-200 dark:hover:bg-red-900/70 text-red-700 dark:text-red-400 rounded-lg transition-all duration-200 hover:scale-110 shadow-sm"
                                                title="Batal edit">
                                                <x-heroicon-o-x-mark class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                    @else
                                    {{-- View Mode --}}
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="text-sm text-gray-900 dark:text-gray-100 {{ $subtask->status === 'completed' ? 'line-through text-gray-500 dark:text-gray-400' : '' }} transition-colors duration-200 break-words">
                                                {{ $subtask->title }}
                                            </p>
                                            @if($subtask->status === 'completed')
                                            <p class="text-xs text-green-600 dark:text-green-400 mt-1">Selesai {{
                                                $subtask->updated_at->diffForHumans() }}</p>
                                            @endif
                                        </div>

                                        {{-- Subtask Actions --}}
                                        <div
                                            class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center gap-1 flex-shrink-0">
                                            <button wire:click="startEditSubtask({{ $subtask->id }})"
                                                class="p-1.5 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-md transition-all duration-200 hover:scale-105"
                                                title="Edit subtask">
                                                <x-heroicon-o-pencil class="w-4 h-4" />
                                            </button>
                                            <button wire:click="deleteSubtask({{ $subtask->id }})"
                                                wire:confirm="Yakin ingin menghapus subtask ini?"
                                                class="p-1.5 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-md transition-all duration-200 hover:scale-105"
                                                title="Hapus subtask">
                                                <x-heroicon-o-trash class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Add New Subtask Form --}}
                        <div class="mt-4 md:mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <form wire:submit="addSubtask" class="flex flex-col sm:flex-row items-start gap-3 md:gap-4">
                                <div class="flex-shrink-0 mt-0.5">
                                    <div
                                        class="w-5 h-5 bg-gray-100 dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-full flex items-center justify-center">
                                        <x-heroicon-o-plus class="w-3 h-3 text-gray-400 dark:text-gray-500" />
                                    </div>
                                </div>
                                <div class="flex-1 flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full">
                                    <div class="flex-1 w-full">
                                        {{ $this->newSubtaskForm }}
                                    </div>
                                    <button type="submit"
                                        class="w-full sm:w-auto px-4 py-2 bg-primary-600 dark:bg-primary-700 text-white text-sm font-medium rounded-lg hover:bg-primary-700 dark:hover:bg-primary-800 transition-colors shadow-sm">
                                        <div class="flex items-center justify-center gap-2">
                                            <x-heroicon-o-plus class="w-4 h-4" />
                                            Tambah
                                        </div>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @else
                    {{-- Empty State for Subtasks --}}
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 md:pt-8">
                        <div class="text-center py-6 md:py-8">
                            <div
                                class="w-12 h-12 md:w-16 md:h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                <x-heroicon-o-list-bullet
                                    class="w-6 h-6 md:w-8 md:h-8 text-gray-400 dark:text-gray-500" />
                            </div>
                            <h4 class="text-base md:text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Belum ada
                                subtask</h4>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mb-4 md:mb-6 max-w-sm mx-auto px-4">
                                Buat subtask untuk membagi task ini menjadi bagian-bagian yang lebih kecil dan mudah
                                dikelola
                            </p>
                        </div>

                        {{-- Add First Subtask Form --}}
                        <form wire:submit="addSubtask" class="flex flex-col sm:flex-row items-start gap-3 md:gap-4">
                            <div class="flex-shrink-0 mt-0.5">
                                <div
                                    class="w-5 h-5 bg-gray-100 dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-full flex items-center justify-center">
                                    <x-heroicon-o-plus class="w-3 h-3 text-gray-400 dark:text-gray-500" />
                                </div>
                            </div>
                            <div class="flex-1 flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full">
                                <div class="flex-1 w-full">
                                    {{ $this->newSubtaskForm }}
                                </div>
                                <button type="submit"
                                    class="w-full sm:w-auto px-4 py-2 bg-primary-600 dark:bg-primary-700 text-white text-sm font-medium rounded-lg hover:bg-primary-700 dark:hover:bg-primary-800 transition-colors shadow-sm">
                                    <div class="flex items-center justify-center gap-2">
                                        <x-heroicon-o-plus class="w-4 h-4" />
                                        Tambah Subtask
                                    </div>
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>

                {{-- Activity Tabs --}}
                <div class="border-t border-gray-200 dark:border-gray-700">
                    <div class="flex border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                        <button
                            class="flex-shrink-0 px-4 md:px-6 py-3 text-sm font-medium text-primary-600 dark:text-primary-400 border-b-2 border-primary-600 dark:border-primary-400 bg-primary-50 dark:bg-primary-900/30">
                            Activity
                        </button>
                    </div>
                    {{-- Activity Content --}}
                    <div class="px-4 md:px-6 py-4 md:py-6">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">Today</div>

                        <div class="space-y-4">
                            {{-- Activity Item --}}
                            <div class="flex gap-3">
                                <div
                                    class="w-8 h-8 bg-gray-500 dark:bg-gray-600 text-white rounded-full flex items-center justify-center text-xs font-medium flex-shrink-0">
                                    {{ strtoupper(substr($task->creator->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm">
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{
                                            $task->creator->name ?? 'User' }}</span>
                                        <span class="text-gray-600 dark:text-gray-400">created task</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">"{{ $task->title
                                            }}"</span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{
                                        $task->created_at->format('g:i A') }}</div>
                                </div>
                            </div>

                            {{-- Comments --}}
                            @if($task->comments && $task->comments->count() > 0)
                            @foreach($task->comments->sortByDesc('created_at') as $comment)
                            <div class="flex gap-3">
                                <div
                                    class="w-8 h-8 bg-gray-500 dark:bg-gray-600 text-white rounded-full flex items-center justify-center text-xs font-medium flex-shrink-0">
                                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm">
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{
                                            $comment->user->name }}</span>
                                        <span class="text-gray-600 dark:text-gray-400">added comment</span>
                                    </div>
                                    <div class="text-sm text-gray-900 dark:text-gray-100 mt-1 bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                                        {{ $comment->content }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{
                                        $comment->created_at->format('g:i A') }}</div>
                                </div>
                            </div>
                            @endforeach
                            @endif

                            {{-- Add Comment Form --}}
                            <form wire:submit="addComment"
                                class="flex gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                                <div
                                    class="w-8 h-8 bg-gray-500 dark:bg-gray-600 text-white rounded-full flex items-center justify-center text-xs font-medium flex-shrink-0">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div class="flex-1 space-y-3">
                                    {{ $this->commentForm }}
                                    <button type="submit"
                                        class="w-full sm:w-auto px-4 py-2 bg-primary-600 dark:bg-primary-700 text-white rounded-lg hover:bg-primary-700 dark:hover:bg-primary-800 text-sm transition-colors">
                                        Kirim Komentar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <x-filament-actions::modals />
    </x-filament::modal>
</div>