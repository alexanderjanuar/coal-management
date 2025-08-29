{{-- resources/views/livewire/daily-task/daily-task-detail-modal.blade.php --}}
<div>
    <x-filament::modal id="task-detail-modal" width="6xl" slide-over>
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
                            @if($editingTitle)
                            <div class="flex items-center gap-3 mb-3">
                                <div class="flex-1">
                                    {{ $this->taskEditForm->getComponent('title') }}
                                </div>
                                <div class="flex items-center gap-1">
                                    <button wire:click="saveTitle"
                                        class="p-2 bg-green-100 hover:bg-green-200 text-green-700 rounded-lg transition-all duration-200 hover:scale-110 shadow-sm"
                                        title="Simpan perubahan">
                                        <x-heroicon-o-check class="w-4 h-4" />
                                    </button>
                                    <button wire:click="cancelEditTitle"
                                        class="p-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition-all duration-200 hover:scale-110 shadow-sm"
                                        title="Batal edit">
                                        <x-heroicon-o-x-mark class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                            @else
                            <div class="group flex items-center gap-2 mb-3">
                                <h1
                                    class="text-2xl font-semibold text-gray-900 {{ $task->status === 'completed' ? 'line-through text-gray-500' : '' }}">
                                    {{ $task->title }}
                                </h1>
                                <button wire:click="startEditTitle"
                                    class="opacity-0 group-hover:opacity-100 p-1.5 hover:bg-gray-100 text-gray-500 rounded-md transition-all duration-200 hover:scale-105"
                                    title="Edit judul">
                                    <x-heroicon-o-pencil class="w-4 h-4" />
                                </button>
                            </div>
                            @endif

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
                                        {{ $this->getStatusOptions()[$task->status] ?? $task->status }}
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
                                        {{ $this->getPriorityOptions()[$task->priority] ?? $task->priority }}
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
                                    Hari Ini
                                    @elseif($task->task_date->isTomorrow())
                                    Besok
                                    @else
                                    {{ $task->task_date->format('M d') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 ml-4">
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
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-medium text-gray-500">Deskripsi</h3>
                                @if(!$editingDescription)
                                <button wire:click="startEditDescription"
                                    class="p-1.5 hover:bg-gray-100 text-gray-400 rounded-md transition-all duration-200 hover:scale-105"
                                    title="Edit deskripsi">
                                    <x-heroicon-o-pencil class="w-4 h-4" />
                                </button>
                                @endif
                            </div>

                            @if($editingDescription)
                            <div class="space-y-4 bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                <div class="prose max-w-none">
                                    {{ $this->descriptionForm }}
                                </div>
                                <div class="flex items-center gap-3 pt-3 border-t border-gray-100">
                                    <button wire:click="saveDescription"
                                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all duration-200 text-sm font-medium shadow-sm">
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-o-check class="w-4 h-4" />
                                            Simpan Deskripsi
                                        </div>
                                    </button>
                                    <button wire:click="cancelEditDescription"
                                        class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-all duration-200 text-sm font-medium shadow-sm">
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-o-x-mark class="w-4 h-4" />
                                            Batal
                                        </div>
                                    </button>
                                </div>
                            </div>
                            @else
                            <div class="min-h-[60px] flex items-center">
                                @if($task->description)
                                <p class="text-gray-900 leading-relaxed whitespace-pre-wrap">{!! $task->description !!}
                                </p>
                                @else
                                <button wire:click="startEditDescription"
                                    class="flex items-center text-gray-400 italic hover:text-gray-600 transition-colors">
                                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                                    Klik untuk menambahkan deskripsi
                                </button>
                                @endif
                            </div>
                            @endif
                        </div>

                        {{-- Meta Information Grid --}}
                        <div class="grid grid-cols-2 gap-6">
                            {{-- Project --}}
                            @if($task->project)
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Proyek</h3>
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

                            {{-- Created By --}}
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Dibuat oleh</h3>
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-6 h-6 bg-gray-500 text-white rounded-full flex items-center justify-center text-xs font-medium">
                                        {{ strtoupper(substr($task->creator->name, 0, 1)) }}
                                    </div>
                                    <span class="text-gray-900">{{ $task->creator->name }}</span>
                                </div>
                            </div>

                            {{-- Time Info --}}
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Waktu</h3>
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                                    <div class="text-gray-900 text-sm">
                                        <div>Dibuat {{ $task->created_at->diffForHumans() }}</div>
                                        @if($task->start_task_date)
                                        <div class="text-gray-600">Dimulai {{ $task->start_task_date->format('d M Y') }}
                                        </div>
                                        @endif
                                    </div>
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
                                <span class="text-sm text-gray-600">{{ $completed }}/{{ $total }} selesai</span>
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
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <x-heroicon-o-list-bullet class="w-5 h-5 text-gray-500" />
                                Subtasks
                                @if($task->subtasks && $task->subtasks->count() > 0)
                                <span class="text-sm font-normal text-gray-500">({{ $task->subtasks->count() }})</span>
                                @endif
                            </h3>
                        </div>

                        {{-- Subtasks List --}}
                        <div class="space-y-2 mb-6">
                            @forelse($task->subtasks->sortBy('id') as $subtask)
                            <div
                                class="group flex items-center gap-3 p-4 rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-gray-25 dark:hover:from-gray-700 dark:hover:to-gray-600 transition-all duration-200 border border-transparent hover:border-gray-200 dark:hover:border-gray-600 hover:shadow-sm">
                                {{-- Completion Toggle --}}
                                <button wire:click="toggleSubtask({{ $subtask->id }})"
                                    class="flex-shrink-0 hover:scale-110 transition-transform duration-200">
                                    @if($subtask->status === 'completed')
                                    <div class="relative">
                                        <x-heroicon-s-check-circle class="w-6 h-6 text-green-500 dark:text-green-400" />
                                        <div
                                            class="absolute inset-0 bg-green-500 dark:bg-green-400 rounded-full animate-ping opacity-25">
                                        </div>
                                    </div>
                                    @else
                                    <div
                                        class="w-6 h-6 rounded-full border-2 border-gray-300 dark:border-gray-600 hover:border-green-400 dark:hover:border-green-500 hover:bg-green-50 dark:hover:bg-green-900/30 transition-all duration-200 flex items-center justify-center group">
                                        <div
                                            class="w-0 h-0 bg-green-500 dark:bg-green-400 rounded-full group-hover:w-3 group-hover:h-3 transition-all duration-200">
                                        </div>
                                    </div>
                                    @endif
                                </button>

                                {{-- Subtask Content --}}
                                <div class="flex-1 min-w-0">
                                    @if($editingSubtaskId === $subtask->id)
                                    {{-- Edit Mode --}}
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1">
                                            {{ $this->editSubtaskForm }}
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button wire:click="saveSubtaskEdit"
                                                class="p-2 bg-green-100 dark:bg-green-900/30 hover:bg-green-200 dark:hover:bg-green-900/50 text-green-700 dark:text-green-400 rounded-lg transition-all duration-200 hover:scale-110 shadow-sm"
                                                title="Simpan perubahan">
                                                <x-heroicon-o-check class="w-4 h-4" />
                                            </button>
                                            <button wire:click="cancelEditSubtask"
                                                class="p-2 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 rounded-lg transition-all duration-200 hover:scale-110 shadow-sm"
                                                title="Batal edit">
                                                <x-heroicon-o-x-mark class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                    @else
                                    {{-- View Mode --}}
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="text-gray-900 dark:text-gray-100 {{ $subtask->status === 'completed' ? 'line-through text-gray-500 dark:text-gray-400' : '' }} transition-colors duration-200">
                                            {{ $subtask->title }}
                                        </span>

                                        {{-- Subtask Actions --}}
                                        <div
                                            class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center gap-1">
                                            <button wire:click="startEditSubtask({{ $subtask->id }})"
                                                class="p-1.5 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-md transition-all duration-200 hover:scale-105"
                                                title="Edit subtask">
                                                <x-heroicon-o-pencil class="w-4 h-4" />
                                            </button>
                                            <button wire:click="deleteSubtask({{ $subtask->id }})"
                                                wire:confirm="Yakin ingin menghapus subtask ini?"
                                                class="p-1.5 hover:bg-red-100 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 rounded-md transition-all duration-200 hover:scale-105"
                                                title="Hapus subtask">
                                                <x-heroicon-o-trash class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-12 px-4">
                                <div
                                    class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <x-heroicon-o-list-bullet class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                                </div>
                                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Belum ada subtask
                                </h4>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">Buat subtask untuk membagi task ini
                                    menjadi bagian-bagian kecil</p>
                            </div>
                            @endforelse
                        </div>

                        {{-- Add New Subtask Form --}}
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <form wire:submit="addSubtask" class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-6 h-6 bg-gray-100 dark:bg-gray-700 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-full flex items-center justify-center flex-shrink-0">
                                        <x-heroicon-o-plus class="w-3 h-3 text-gray-400 dark:text-gray-500" />
                                    </div>
                                    <div class="flex-1">
                                        {{ $this->newSubtaskForm }}
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <x-filament::button type="submit" size="sm" color="primary" icon="heroicon-o-plus">
                                        Tambah Subtask
                                    </x-filament::button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @else
                    {{-- Add first subtask when no subtasks exist --}}
                    <div class="border-t border-gray-200 pt-6">
                        <div class="text-center py-8">
                            <div
                                class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <x-heroicon-o-list-bullet class="w-8 h-8 text-gray-400" />
                            </div>
                            <h4 class="text-lg font-medium text-gray-900 mb-2">Belum ada subtask</h4>
                            <p class="text-gray-500 text-sm mb-6">Buat subtask untuk membagi task ini menjadi
                                bagian-bagian kecil</p>
                        </div>

                        <form wire:submit="addSubtask" class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-6 h-6 bg-gray-100 border-2 border-dashed border-gray-300 rounded-full flex items-center justify-center flex-shrink-0">
                                    <x-heroicon-o-plus class="w-3 h-3 text-gray-400" />
                                </div>
                                <div class="flex-1">
                                    {{ $this->newSubtaskForm }}
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <x-filament::button type="submit" size="sm" color="primary" icon="heroicon-o-plus">
                                    Tambah Subtask
                                </x-filament::button>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>

                {{-- Right Column: Comments --}}
                <div class="w-96 flex flex-col bg-gray-50">
                    {{-- Comments Header --}}
                    <div class="flex-shrink-0 p-6 border-b border-gray-200 bg-white">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Komentar</h3>
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
                                <p class="text-gray-500 text-sm">Belum ada komentar</p>
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
                                        Kirim Komentar
                                    </x-filament::button>
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