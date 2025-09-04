{{-- resources/views/livewire/daily-task/daily-task-item.blade.php - Fully Responsive with Dark Mode --}}
<div x-data="taskItemManager({{ $task->id }})" class="space-y-2" x-init="init()">
    {{-- Desktop View (xl and up) --}}
    <div class="hidden lg:block">
        <div class="grid grid-cols-12 gap-2 lg:gap-4 items-center">
            {{-- Checkbox & Completion Toggle --}}
            <div class="col-span-1 flex items-center gap-2">
                <button wire:click="toggleTaskCompletion"
                    class="flex-shrink-0 hover:scale-110 transition-transform duration-200"
                    x-bind:class="{ 'animate-bounce': completionToggling }"
                    @click="completionToggling = true; setTimeout(() => completionToggling = false, 600)">
                    @if($task->status === 'completed')
                    <div class="relative">
                        <x-heroicon-s-check-circle class="w-5 h-5 text-green-500 dark:text-green-400" />
                        <div class="absolute inset-0 bg-green-500 dark:bg-green-400 rounded-full animate-ping opacity-25"></div>
                    </div>
                    @else
                    <div class="w-5 h-5 rounded-full border-2 border-gray-300 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-all duration-200 flex items-center justify-center">
                        <div class="w-0 h-0 bg-primary-500 dark:bg-primary-400 rounded-full hover:w-2 hover:h-2 transition-all duration-200"></div>
                    </div>
                    @endif
                </button>
            </div>

            {{-- Enhanced Task Info --}}
            <div class="col-span-4">
                <div class="space-y-2">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 cursor-pointer" wire:click="viewDetails">
                            <h2 class="font-semibold text-gray-900 dark:text-gray-100 text-sm lg:text-[16px] leading-snug {{ $task->status === 'completed' ? 'line-through text-gray-500 dark:text-gray-400' : '' }} hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                {{ $task->title }}
                            </h2>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Enhanced Status Dropdown --}}
            <div class="col-span-2">
                <div class="relative" x-data="{ statusOpen: false, buttonRect: {} }">
                    <button @click="statusOpen = !statusOpen; buttonRect = $el.getBoundingClientRect()"
                        x-ref="statusButton" class="inline-flex items-center gap-1 lg:gap-2 px-2 lg:px-3 py-1 lg:py-2 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105 w-full justify-center shadow-sm border
                    {{ match($task->status) {
                        'completed' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 border-green-200 dark:border-green-700 hover:bg-green-200 dark:hover:bg-green-900/50',
                        'in_progress' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 border-yellow-200 dark:border-yellow-700 hover:bg-yellow-200 dark:hover:bg-yellow-900/50',
                        'pending' => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-200 dark:hover:bg-gray-600',
                        'cancelled' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 border-red-200 dark:border-red-700 hover:bg-red-200 dark:hover:bg-red-900/50',
                        default => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-200 dark:hover:bg-gray-600'
                    } }}">
                        <div class="w-2 h-2 rounded-full animate-pulse
                    {{ match($task->status) {
                        'completed' => 'bg-green-500 dark:bg-green-400',
                        'in_progress' => 'bg-yellow-500 dark:bg-yellow-400',
                        'pending' => 'bg-gray-400 dark:bg-gray-500',
                        'cancelled' => 'bg-red-500 dark:bg-red-400',
                        default => 'bg-gray-400 dark:bg-gray-500'
                    } }}"></div>
                        <span class="hidden xl:inline">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                        <span class="xl:hidden">{{ Str::limit(ucfirst(str_replace('_', ' ', $task->status)), 8) }}</span>
                        <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform duration-200"
                            x-bind:class="{ 'rotate-180': statusOpen }" />
                    </button>

                    {{-- Status Dropdown --}}
                    <template x-teleport="body">
                        <div x-show="statusOpen" x-cloak @click.away="statusOpen = false"
                            @keydown.escape="statusOpen = false" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="fixed w-44 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-600 py-2 overflow-hidden"
                            style="z-index: 9999;" x-bind:style="{
                        top: (buttonRect.bottom + window.scrollY + 8) + 'px',
                        left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 176 - 8)) + 'px'
                    }">

                            @foreach($this->getStatusOptions() as $statusValue => $statusLabel)
                            <button wire:click="updateStatus('{{ $statusValue }}')" @click="statusOpen = false"
                                class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent dark:hover:from-primary-900/20 dark:hover:to-transparent transition-all duration-200 flex items-center gap-3 {{ $task->status === $statusValue ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 border-l-4 border-l-primary-500 dark:border-l-primary-400' : '' }}">
                                <div class="w-3 h-3 rounded-full 
                                {{ match($statusValue) {
                                    'completed' => 'bg-green-500 dark:bg-green-400',
                                    'in_progress' => 'bg-yellow-500 dark:bg-yellow-400',
                                    'pending' => 'bg-gray-400 dark:bg-gray-500',
                                    'cancelled' => 'bg-red-500 dark:bg-red-400',
                                    default => 'bg-gray-400 dark:bg-gray-500'
                                } }}"></div>
                                <span class="font-medium">{{ $statusLabel }}</span>
                                @if($task->status === $statusValue)
                                <x-heroicon-s-check class="w-4 h-4 text-primary-600 dark:text-primary-400 ml-auto" />
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
                    <div class="relative" x-data="{ priorityOpen: false, buttonRect: {} }">
                        <button @click="priorityOpen = !priorityOpen; buttonRect = $el.getBoundingClientRect()" class="inline-flex items-center gap-1 lg:gap-1.5 px-2 lg:px-3 py-1 lg:py-2 rounded-lg text-xs font-bold transition-all duration-200 hover:scale-105 shadow-sm border w-full justify-center
                            {{ match($task->priority) {
                                'urgent' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 border-red-200 dark:border-red-700 animate-pulse',
                                'high' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300 border-orange-200 dark:border-orange-700',
                                'normal' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-700',
                                'low' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600',
                                default => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600'
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
                            <span class="hidden xl:inline">{{ ucfirst($task->priority) }}</span>
                            <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform duration-200"
                                x-bind:class="{ 'rotate-180': priorityOpen }" />
                        </button>

                        {{-- Priority Dropdown --}}
                        <template x-teleport="body">
                            <div x-show="priorityOpen" x-cloak @click.away="priorityOpen = false"
                                @keydown.escape="priorityOpen = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="fixed w-44 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-600 py-2 overflow-hidden"
                                style="z-index: 9999;" x-bind:style="{
                                top: (buttonRect.bottom + window.scrollY + 8) + 'px',
                                left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 176 - 8)) + 'px'
                            }">

                                @foreach($this->getPriorityOptions() as $priorityValue => $priorityLabel)
                                <button wire:click="updatePriority('{{ $priorityValue }}')"
                                    @click="priorityOpen = false"
                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent dark:hover:from-primary-900/20 dark:hover:to-transparent transition-all duration-200 flex items-center gap-3 {{ $task->priority === $priorityValue ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 border-l-4 border-l-primary-500 dark:border-l-primary-400' : '' }}">
                                    @php
                                    $icon = match($priorityValue) {
                                    'urgent' => 'heroicon-s-exclamation-triangle',
                                    'high' => 'heroicon-o-exclamation-triangle',
                                    'normal' => 'heroicon-o-minus',
                                    'low' => 'heroicon-o-arrow-down',
                                    default => 'heroicon-o-minus'
                                    };
                                    @endphp
                                    <x-dynamic-component :component="$icon" class="w-4 h-4 {{ match($priorityValue) {
                                        'urgent' => 'text-red-500',
                                        'high' => 'text-orange-500',
                                        'normal' => 'text-blue-500',
                                        'low' => 'text-gray-400',
                                        default => 'text-gray-400'
                                    } }}" />
                                    <span class="font-medium">{{ $priorityLabel }}</span>
                                    @if($task->priority === $priorityValue)
                                    <x-heroicon-s-check
                                        class="w-4 h-4 text-primary-600 dark:text-primary-400 ml-auto" />
                                    @endif
                                </button>
                                @endforeach
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Enhanced Assignee Section --}}
            <div class="col-span-2">
                <div class="relative" x-data="{ assigneeOpen: false, buttonRect: {} }">
                    <button @click="assigneeOpen = !assigneeOpen; buttonRect = $el.getBoundingClientRect()"
                        class="w-full flex items-center gap-2 lg:gap-3 px-2 lg:px-3 py-1 lg:py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-600 transition-all duration-200">
                        @if($task->assignedUsers && $task->assignedUsers->count() > 0)
                        <div class="flex items-center gap-2 lg:gap-3">
                            <div class="flex -space-x-2">
                                @foreach($task->assignedUsers->take(3) as $user)
                                <div class="w-6 h-6 lg:w-8 lg:h-8 bg-gradient-to-br from-primary-400 to-primary-600 dark:from-primary-500 dark:to-primary-700 text-white rounded-full flex items-center justify-center text-xs font-bold border-2 border-white dark:border-gray-800 shadow-sm"
                                    title="{{ $user->name }}">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                @endforeach
                                @if($task->assignedUsers->count() > 3)
                                <div
                                    class="w-6 h-6 lg:w-8 lg:h-8 bg-gray-400 dark:bg-gray-600 text-white rounded-full flex items-center justify-center text-xs font-bold border-2 border-white dark:border-gray-800 shadow-sm">
                                    +{{ $task->assignedUsers->count() - 3 }}
                                </div>
                                @endif
                            </div>
                            @if($task->assignedUsers->count() === 1)
                            <span class="hidden xl:inline text-sm font-medium text-gray-700 dark:text-gray-300 truncate">{{
                                $task->assignedUsers->first()->name }}</span>
                            @else
                            <span class="hidden lg:inline text-xs text-gray-500 dark:text-gray-400">{{ $task->assignedUsers->count() }}
                                people</span>
                            @endif
                        </div>
                        @else
                        <div class="flex items-center gap-2 lg:gap-3 text-gray-400 dark:text-gray-500">
                            <div
                                class="w-6 h-6 lg:w-8 lg:h-8 bg-gray-100 dark:bg-gray-700 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-full flex items-center justify-center">
                                <x-heroicon-o-plus class="w-4 h-4" />
                            </div>
                            <span class="hidden lg:inline text-sm">Unassigned</span>
                        </div>
                        @endif
                        <x-heroicon-o-chevron-down
                            class="w-4 h-4 text-gray-400 ml-auto transition-transform duration-200"
                            x-bind:class="{ 'rotate-180': assigneeOpen }" />
                    </button>

                    {{-- Assignee Dropdown --}}
                    <template x-teleport="body">
                        <div x-show="assigneeOpen" x-cloak @click.away="assigneeOpen = false"
                            @keydown.escape="assigneeOpen = false" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="fixed w-64 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-600 py-2 overflow-hidden max-h-80 overflow-y-auto"
                            style="z-index: 9999;" x-bind:style="{
                            top: (buttonRect.bottom + window.scrollY + 8) + 'px',
                            left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 256 - 8)) + 'px'
                        }">

                            <div
                                class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide border-b border-gray-100 dark:border-gray-700">
                                Assign Users
                            </div>

                            @foreach($this->getUserOptions() as $userId => $userName)
                            @php $isAssigned = $task->assignedUsers->contains($userId); @endphp
                            <button wire:click="{{ $isAssigned ? 'unassignUser' : 'assignUser' }}({{ $userId }})"
                                @click="assigneeOpen = false"
                                class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent dark:hover:from-primary-900/20 dark:hover:to-transparent transition-all duration-200 flex items-center gap-3">
                                <div
                                    class="w-8 h-8 bg-gradient-to-br from-primary-400 to-primary-600 dark:from-primary-500 dark:to-primary-700 text-white rounded-full flex items-center justify-center text-xs font-bold">
                                    {{ strtoupper(substr($userName, 0, 1)) }}
                                </div>
                                <span class="font-medium flex-1">{{ $userName }}</span>
                                @if($isAssigned)
                                <x-heroicon-s-check class="w-4 h-4 text-green-600 dark:text-green-400" />
                                @else
                                <x-heroicon-o-plus class="w-4 h-4 text-gray-400" />
                                @endif
                            </button>
                            @endforeach
                        </div>
                    </template>
                </div>
            </div>

            {{-- Enhanced Project Dropdown with Client Selection --}}
            <div class="col-span-1">
                <div class="flex justify-center">
                    <div class="relative" x-data="{ projectOpen: false, buttonRect: {} }">
                        <button @click="projectOpen = !projectOpen; buttonRect = $el.getBoundingClientRect()" class="flex items-center gap-1 lg:gap-1.5 px-2 lg:px-3 py-1 lg:py-2 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105 border shadow-sm
                @if($task->project)
                    bg-gradient-to-r from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 text-indigo-800 dark:text-indigo-300 border-indigo-200 dark:border-indigo-700
                @else
                    bg-gray-100 dark:bg-gray-700 border-2 border-dashed border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400
                @endif">
                            @if($task->project)
                            <x-heroicon-o-folder class="w-3 h-3 flex-shrink-0" />
                            <span class="hidden xl:inline truncate" title="{{ $task->project->name }}">{{
                                Str::limit($task->project->name, 8) }}</span>
                            @else
                            <x-heroicon-o-folder-plus class="w-3 h-3" />
                            <span class="hidden lg:inline">None</span>
                            @endif
                            <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform duration-200"
                                x-bind:class="{ 'rotate-180': projectOpen }" />
                        </button>

                        {{-- Project Dropdown with Client Selection --}}
                        <template x-teleport="body">
                            <div x-show="projectOpen" x-cloak @click.away="projectOpen = false"
                                @keydown.escape="projectOpen = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="fixed w-80 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-600 overflow-hidden max-h-96 overflow-y-auto"
                                style="z-index: 9999;" x-bind:style="{
                    top: (buttonRect.bottom + window.scrollY + 8) + 'px',
                    left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 320 - 8)) + 'px'
                }">

                                {{-- Header --}}
                                <div
                                    class="px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 border-b border-gray-200 dark:border-gray-600">
                                    <h3
                                        class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                        <x-heroicon-o-building-office class="w-4 h-4" />
                                        Select Project
                                    </h3>
                                </div>

                                {{-- No Project Option --}}
                                <button wire:click="updateProject(null)" @click="projectOpen = false"
                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gradient-to-r hover:from-red-50 hover:to-transparent dark:hover:from-red-900/20 dark:hover:to-transparent transition-all duration-200 flex items-center gap-3 border-b border-gray-100 dark:border-gray-700 {{ !$task->project_id ? 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border-l-4 border-l-red-500 dark:border-l-red-400' : '' }}">
                                    <x-heroicon-o-minus-circle class="w-4 h-4 text-red-500" />
                                    <span class="font-medium">Remove Project</span>
                                    @if(!$task->project_id)
                                    <x-heroicon-s-check class="w-4 h-4 text-red-600 dark:text-red-400 ml-auto" />
                                    @endif
                                </button>

                                {{-- Client Selection --}}
                                <div
                                    class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">
                                        <x-heroicon-o-users class="w-3 h-3 inline mr-1" />
                                        Pilih Client Dulu:
                                    </label>
                                    <select wire:model.live="selectedClientId"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:text-gray-100">
                                        <option value="">-- Pilih Client --</option>
                                        @foreach($this->getClientOptions() as $clientId => $clientName)
                                        <option value="{{ $clientId }}">{{ $clientName }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Projects List --}}
                                @if($selectedClientId)
                                @php $projects = $this->getProjectOptions(); @endphp
                                @if(!empty($projects))
                                <div class="py-2">
                                    <div
                                        class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                        Projects ({{ count($projects) }})
                                    </div>
                                    @foreach($projects as $projectId => $projectName)
                                    <button wire:click="updateProject({{ $projectId }})" @click="projectOpen = false"
                                        class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent dark:hover:from-primary-900/20 dark:hover:to-transparent transition-all duration-200 flex items-center gap-3 {{ $task->project_id == $projectId ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 border-l-4 border-l-primary-500 dark:border-l-primary-400' : '' }}">
                                        <x-heroicon-o-folder class="w-4 h-4 text-indigo-500 flex-shrink-0" />
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium truncate">{{ $projectName }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                {{ $this->getClientOptions()[$selectedClientId] ?? 'Client' }}
                                            </div>
                                        </div>
                                        @if($task->project_id == $projectId)
                                        <x-heroicon-s-check
                                            class="w-4 h-4 text-primary-600 dark:text-primary-400 flex-shrink-0" />
                                        @endif
                                    </button>
                                    @endforeach
                                </div>
                                @else
                                <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <x-heroicon-o-folder-open class="w-8 h-8 mx-auto mb-2 opacity-50" />
                                    <p class="text-sm">Tidak ada project untuk client ini</p>
                                </div>
                                @endif
                                @else
                                <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <x-heroicon-o-building-office class="w-8 h-8 mx-auto mb-2 opacity-50" />
                                    <p class="text-sm">Pilih client terlebih dahulu</p>
                                    <p class="text-xs mt-1">untuk melihat daftar project</p>
                                </div>
                                @endif
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Enhanced Due Date with Clickable Edit --}}
            <div class="col-span-1">
                <div class="flex flex-col items-start space-y-1">
                    <div class="relative" x-data="{ dateOpen: false, buttonRect: {} }">
                        @php
                        $isOverdue = $task->task_date->isPast() && $task->status !== 'completed';
                        $isToday = $task->task_date->isToday();
                        $isTomorrow = $task->task_date->isTomorrow();
                        @endphp

                        {{-- Clickable Due Date Button --}}
                        <button @click="dateOpen = !dateOpen; buttonRect = $el.getBoundingClientRect()" class="flex items-center gap-1 lg:gap-2 text-xs lg:text-sm px-1 lg:px-2 py-1 lg:py-1.5 rounded-lg transition-all duration-200 hover:scale-105 border shadow-sm
                            {{ $isOverdue ? 
                                'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 border-red-200 dark:border-red-700 hover:bg-red-200 dark:hover:bg-red-900/50' : 
                                ($isToday ? 
                                    'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 border-yellow-200 dark:border-yellow-700 hover:bg-yellow-200 dark:hover:bg-yellow-900/50' : 
                                    'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-600 hover:bg-gray-200 dark:hover:bg-gray-600'
                                ) 
                            }}">
                            <div class="p-0.5 lg:p-1 rounded {{ $isOverdue ? 'bg-red-200 dark:bg-red-800' : ($isToday ? 'bg-yellow-200 dark:bg-yellow-800' : 'bg-gray-200 dark:bg-gray-600') }}">
                                <x-heroicon-o-calendar-days class="w-2 h-2 lg:w-3 lg:h-3" />
                            </div>

                            <div class="flex flex-col">
                                <span class="font-medium">
                                    @if($isToday)
                                    Today
                                    @elseif($isTomorrow)
                                    <span class="hidden lg:inline">Tomorrow</span>
                                    <span class="lg:hidden">Tom</span>
                                    @else
                                    {{ $task->task_date->format('M d') }}
                                    @endif
                                </span>

                                @if($isOverdue)
                                <span class="text-xs font-medium">Overdue</span>
                                @elseif($task->task_date->diffInDays() <= 3 && !$task->task_date->isPast())
                                    <span class="text-xs hidden lg:inline">Soon</span>
                                    @endif
                            </div>

                            <x-heroicon-o-pencil class="w-2 h-2 lg:w-3 lg:h-3 ml-1 opacity-60" />
                        </button>

                        {{-- Date Picker Dropdown --}}
                        <template x-teleport="body">
                            <div x-show="dateOpen" x-cloak @click.away="dateOpen = false"
                                @keydown.escape="dateOpen = false" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="fixed w-80 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-600"
                                style="z-index: 9999;" x-bind:style="{
                                top: (buttonRect.bottom + window.scrollY + 8) + 'px',
                                left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 320 - 8)) + 'px'
                                    }">

                                {{-- Header --}}
                                <div
                                    class="px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 border-b border-gray-200 dark:border-gray-600">
                                    <h3
                                        class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                        <x-heroicon-o-calendar-days class="w-4 h-4" />
                                        Edit Due Date
                                    </h3>
                                </div>

                                {{-- Date Form --}}
                                <div class="p-4">
                                    {{ $this->dueDateForm }}
                                </div>

                                {{-- Quick Date Options --}}
                                <div class="px-4 pb-4 border-t border-gray-100 dark:border-gray-700">
                                    <div
                                        class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                                        Quick Options
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button wire:click="updateTaskDate('{{ today()->format('Y-m-d') }}')"
                                            @click="dateOpen = false"
                                            class="px-3 py-2 text-xs text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                            Today
                                        </button>
                                        <button wire:click="updateTaskDate('{{ today()->addDay()->format('Y-m-d') }}')"
                                            @click="dateOpen = false"
                                            class="px-3 py-2 text-xs text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                            Tomorrow
                                        </button>
                                        <button
                                            wire:click="updateTaskDate('{{ today()->addDays(7)->format('Y-m-d') }}')"
                                            @click="dateOpen = false"
                                            class="px-3 py-2 text-xs text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                            Next Week
                                        </button>
                                        <button
                                            wire:click="updateTaskDate('{{ today()->addMonth()->format('Y-m-d') }}')"
                                            @click="dateOpen = false"
                                            class="px-3 py-2 text-xs text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                            Next Month
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Large Desktop/Tablet View (lg to xl) --}}
    <div class="hidden lg:block xl:hidden">
        <div class="grid grid-cols-10 gap-3 items-center">
            {{-- Checkbox --}}
            <div class="col-span-1 flex justify-center">
                <button wire:click="toggleTaskCompletion"
                    class="flex-shrink-0 hover:scale-110 transition-transform duration-200"
                    x-bind:class="{ 'animate-bounce': completionToggling }"
                    @click="completionToggling = true; setTimeout(() => completionToggling = false, 600)">
                    @if($task->status === 'completed')
                    <div class="relative">
                        <x-heroicon-s-check-circle class="w-5 h-5 text-green-500 dark:text-green-400" />
                        <div
                            class="absolute inset-0 bg-green-500 dark:bg-green-400 rounded-full animate-ping opacity-25">
                        </div>
                    </div>
                    @else
                    <div
                        class="w-5 h-5 rounded-full border-2 border-gray-300 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-all duration-200 flex items-center justify-center">
                        <div
                            class="w-0 h-0 bg-primary-500 dark:bg-primary-400 rounded-full hover:w-2 hover:h-2 transition-all duration-200">
                        </div>
                    </div>
                    @endif
                </button>
            </div>

            {{-- Task Title --}}
            <div class="col-span-4">
                <div class="cursor-pointer" wire:click="viewDetails">
                    <h3
                        class="font-medium text-gray-900 dark:text-gray-100 {{ $task->status === 'completed' ? 'line-through text-gray-500 dark:text-gray-400' : '' }} hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                        {{ $task->title }}
                    </h3>
                </div>
            </div>

            {{-- Status --}}
            <div class="col-span-2">
                <div class="relative" x-data="{ statusOpen: false, buttonRect: {} }">
                    <button @click="statusOpen = !statusOpen; buttonRect = $el.getBoundingClientRect()" class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-semibold w-full justify-center transition-all duration-200 hover:scale-105 shadow-sm border
                    {{ match($task->status) {
                        'completed' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 border-green-200 dark:border-green-700',
                        'in_progress' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 border-yellow-200 dark:border-yellow-700',
                        'pending' => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600',
                        'cancelled' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 border-red-200 dark:border-red-700',
                        default => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600'
                    } }}">
                        <div class="w-2 h-2 rounded-full
                    {{ match($task->status) {
                        'completed' => 'bg-green-500 dark:bg-green-400',
                        'in_progress' => 'bg-yellow-500 dark:bg-yellow-400',
                        'pending' => 'bg-gray-400 dark:bg-gray-500',
                        'cancelled' => 'bg-red-500 dark:bg-red-400',
                        default => 'bg-gray-400 dark:bg-gray-500'
                    } }}"></div>
                        <span>{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                        <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform duration-200 ml-1"
                            x-bind:class="{ 'rotate-180': statusOpen }" />
                    </button>

                    {{-- Status Dropdown --}}
                    <template x-teleport="body">
                        <div x-show="statusOpen" x-cloak @click.away="statusOpen = false"
                            @keydown.escape="statusOpen = false" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="fixed w-44 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-600 py-2 overflow-hidden"
                            style="z-index: 9999;" x-bind:style="{
                         top: (buttonRect.bottom + window.scrollY + 8) + 'px',
                         left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 176 - 8)) + 'px'
                     }">
                            @foreach($this->getStatusOptions() as $statusValue => $statusLabel)
                            <button wire:click="updateStatus('{{ $statusValue }}')" @click="statusOpen = false"
                                class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent dark:hover:from-primary-900/20 dark:hover:to-transparent transition-all duration-200 flex items-center gap-3 {{ $task->status === $statusValue ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 border-l-4 border-l-primary-500 dark:border-l-primary-400' : '' }}">
                                <div class="w-3 h-3 rounded-full 
                            {{ match($statusValue) {
                                'completed' => 'bg-green-500 dark:bg-green-400',
                                'in_progress' => 'bg-yellow-500 dark:bg-yellow-400',
                                'pending' => 'bg-gray-400 dark:bg-gray-500',
                                'cancelled' => 'bg-red-500 dark:bg-red-400',
                                default => 'bg-gray-400 dark:bg-gray-500'
                            } }}"></div>
                                <span class="font-medium">{{ $statusLabel }}</span>
                                @if($task->status === $statusValue)
                                <x-heroicon-s-check class="w-4 h-4 text-primary-600 dark:text-primary-400 ml-auto" />
                                @endif
                            </button>
                            @endforeach
                        </div>
                    </template>
                </div>
            </div>

            {{-- Priority --}}
            <div class="col-span-1 flex justify-center">
                <span class="inline-flex items-center gap-1 px-1.5 py-1 rounded text-xs font-bold
                {{ match($task->priority) {
                    'urgent' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                    'high' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300',
                    'normal' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
                    'low' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
                    default => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'
                } }}" title="{{ ucfirst($task->priority) }}">
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
                </span>
            </div>

            {{-- Assignees --}}
            <div class="col-span-1 flex justify-center">
                @if($task->assignedUsers && $task->assignedUsers->count() > 0)
                <div class="flex -space-x-1">
                    @foreach($task->assignedUsers->take(2) as $user)
                    <div class="w-6 h-6 bg-gradient-to-br from-primary-400 to-primary-600 text-white rounded-full flex items-center justify-center text-xs font-bold border-2 border-white dark:border-gray-800"
                        title="{{ $user->name }}">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    @endforeach
                    @if($task->assignedUsers->count() > 2)
                    <div
                        class="w-6 h-6 bg-gray-400 text-white rounded-full flex items-center justify-center text-xs font-bold border-2 border-white dark:border-gray-800">
                        +{{ $task->assignedUsers->count() - 2 }}
                    </div>
                    @endif
                </div>
                @else
                <div
                    class="w-6 h-6 bg-gray-100 dark:bg-gray-700 border border-dashed border-gray-300 dark:border-gray-600 rounded-full flex items-center justify-center">
                    <x-heroicon-o-plus class="w-3 h-3 text-gray-400" />
                </div>
                @endif
            </div>

            {{-- Due Date --}}
            <div class="col-span-1 flex justify-center">
                @php
                $isOverdue = $task->task_date->isPast() && $task->status !== 'completed';
                $isToday = $task->task_date->isToday();
                @endphp
                <div class="text-center">
                    <div class="text-xs font-medium 
                    {{ $isOverdue ? 'text-red-600 dark:text-red-400' : 
                       ($isToday ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-600 dark:text-gray-400') }}">
                        @if($isToday)
                        Today
                        @else
                        {{ $task->task_date->format('M d') }}
                        @endif
                    </div>
                    @if($isOverdue)
                    <div class="text-xs text-red-500 dark:text-red-400">Late</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Medium Tablet View (md to lg) --}}
    <div class="hidden md:block lg:hidden">
        <div
            class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-all duration-200">
            {{-- Header Row --}}
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-start gap-3 flex-1">
                    <button wire:click="toggleTaskCompletion"
                        class="flex-shrink-0 hover:scale-110 transition-transform mt-0.5"
                        x-bind:class="{ 'animate-bounce': completionToggling }"
                        @click="completionToggling = true; setTimeout(() => completionToggling = false, 600)">
                        @if($task->status === 'completed')
                        <div class="relative">
                            <x-heroicon-s-check-circle class="w-6 h-6 text-green-500 dark:text-green-400" />
                            <div
                                class="absolute inset-0 bg-green-500 dark:bg-green-400 rounded-full animate-ping opacity-25">
                            </div>
                        </div>
                        @else
                        <div
                            class="w-6 h-6 rounded-full border-2 border-gray-300 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-all duration-200 flex items-center justify-center">
                            <div
                                class="w-0 h-0 bg-primary-500 dark:bg-primary-400 rounded-full hover:w-3 hover:h-3 transition-all duration-200">
                            </div>
                        </div>
                        @endif
                    </button>

                    <div class="flex-1 min-w-0">
                        <div class="cursor-pointer" wire:click="viewDetails">
                            <h4
                                class="font-medium text-gray-900 dark:text-gray-100 text-lg {{ $task->status === 'completed' ? 'line-through text-gray-500 dark:text-gray-400' : '' }} hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                {{ $task->title }}
                            </h4>
                            @if($task->description)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                                {{ $task->description }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Priority Badge --}}
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-bold
                {{ match($task->priority) {
                    'urgent' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                    'high' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300',
                    'normal' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
                    'low' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
                    default => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'
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

            {{-- Meta Row --}}
            <div class="grid grid-cols-3 gap-4 text-sm">
                {{-- Status --}}
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1 font-medium">Status</span>
                    <div class="relative" x-data="{ statusOpen: false, buttonRect: {} }">
                        <button @click="statusOpen = !statusOpen; buttonRect = $el.getBoundingClientRect()" class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-semibold w-full justify-start transition-all duration-200 hover:scale-105 shadow-sm border
                        {{ match($task->status) {
                            'completed' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 border-green-200 dark:border-green-700',
                            'in_progress' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 border-yellow-200 dark:border-yellow-700',
                            'pending' => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600',
                            'cancelled' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 border-red-200 dark:border-red-700',
                            default => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600'
                        } }}">
                            <div class="w-2 h-2 rounded-full
                        {{ match($task->status) {
                            'completed' => 'bg-green-500 dark:bg-green-400',
                            'in_progress' => 'bg-yellow-500 dark:bg-yellow-400',
                            'pending' => 'bg-gray-400 dark:bg-gray-500',
                            'cancelled' => 'bg-red-500 dark:bg-red-400',
                            default => 'bg-gray-400 dark:bg-gray-500'
                        } }}"></div>
                            <span class="truncate">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                            <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform duration-200 ml-auto"
                                x-bind:class="{ 'rotate-180': statusOpen }" />
                        </button>

                        {{-- Status Dropdown --}}
                        <template x-teleport="body">
                            <div x-show="statusOpen" x-cloak @click.away="statusOpen = false"
                                @keydown.escape="statusOpen = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="fixed w-44 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-600 py-2 overflow-hidden"
                                style="z-index: 9999;" x-bind:style="{
                             top: (buttonRect.bottom + window.scrollY + 8) + 'px',
                             left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 176 - 8)) + 'px'
                         }">
                                @foreach($this->getStatusOptions() as $statusValue => $statusLabel)
                                <button wire:click="updateStatus('{{ $statusValue }}')" @click="statusOpen = false"
                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent dark:hover:from-primary-900/20 dark:hover:to-transparent transition-all duration-200 flex items-center gap-3 {{ $task->status === $statusValue ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 border-l-4 border-l-primary-500 dark:border-l-primary-400' : '' }}">
                                    <div class="w-3 h-3 rounded-full 
                                {{ match($statusValue) {
                                    'completed' => 'bg-green-500 dark:bg-green-400',
                                    'in_progress' => 'bg-yellow-500 dark:bg-yellow-400',
                                    'pending' => 'bg-gray-400 dark:bg-gray-500',
                                    'cancelled' => 'bg-red-500 dark:bg-red-400',
                                    default => 'bg-gray-400 dark:bg-gray-500'
                                } }}"></div>
                                    <span class="font-medium">{{ $statusLabel }}</span>
                                    @if($task->status === $statusValue)
                                    <x-heroicon-s-check
                                        class="w-4 h-4 text-primary-600 dark:text-primary-400 ml-auto" />
                                    @endif
                                </button>
                                @endforeach
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Assignees --}}
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1 font-medium">Assigned</span>
                    @if($task->assignedUsers && $task->assignedUsers->count() > 0)
                    <div class="flex items-center gap-2">
                        <div class="flex -space-x-1">
                            @foreach($task->assignedUsers->take(3) as $user)
                            <div class="w-6 h-6 bg-gradient-to-br from-primary-400 to-primary-600 text-white rounded-full flex items-center justify-center text-xs font-bold border-2 border-white dark:border-gray-800"
                                title="{{ $user->name }}">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            @endforeach
                            @if($task->assignedUsers->count() > 3)
                            <div
                                class="w-6 h-6 bg-gray-400 text-white rounded-full flex items-center justify-center text-xs font-bold border-2 border-white dark:border-gray-800">
                                +{{ $task->assignedUsers->count() - 3 }}
                            </div>
                            @endif
                        </div>
                        <span class="text-xs text-gray-600 dark:text-gray-400">
                            {{ $task->assignedUsers->count() }} {{ $task->assignedUsers->count() === 1 ? 'person' :
                            'people' }}
                        </span>
                    </div>
                    @else
                    <div class="flex items-center gap-2">
                        <div
                            class="w-6 h-6 bg-gray-100 dark:bg-gray-700 border border-dashed border-gray-300 dark:border-gray-600 rounded-full flex items-center justify-center">
                            <x-heroicon-o-plus class="w-3 h-3 text-gray-400" />
                        </div>
                        <span class="text-xs text-gray-400 dark:text-gray-500">Unassigned</span>
                    </div>
                    @endif
                </div>

                {{-- Due Date --}}
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1 font-medium">Due Date</span>
                    @php
                    $isOverdue = $task->task_date->isPast() && $task->status !== 'completed';
                    $isToday = $task->task_date->isToday();
                    $isTomorrow = $task->task_date->isTomorrow();
                    @endphp
                    <div class="flex items-center gap-1 text-sm
                    {{ $isOverdue ? 'text-red-600 dark:text-red-400' : 
                       ($isToday ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-600 dark:text-gray-400') }}">
                        <div
                            class="p-1 rounded {{ $isOverdue ? 'bg-red-100 dark:bg-red-900/30' : ($isToday ? 'bg-yellow-100 dark:bg-yellow-900/30' : '') }}">
                            <x-heroicon-o-calendar-days class="w-4 h-4" />
                        </div>
                        <div class="flex flex-col">
                            <span class="font-medium">
                                @if($isToday)
                                Today
                                @elseif($isTomorrow)
                                Tomorrow
                                @else
                                {{ $task->task_date->format('M d, Y') }}
                                @endif
                            </span>
                            @if($isOverdue)
                            <span class="text-xs text-red-500 dark:text-red-400 font-medium">Overdue</span>
                            @elseif($task->task_date->diffInDays() <= 3 && !$task->task_date->isPast())
                                <span class="text-xs text-yellow-600 dark:text-yellow-400">Soon</span>
                                @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Project Row --}}
            @if($task->project)
            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Project:</span>
                    <div
                        class="flex items-center gap-1.5 px-2 py-1 bg-gradient-to-r from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 text-indigo-800 dark:text-indigo-300 rounded-md text-xs font-semibold border border-indigo-200 dark:border-indigo-700">
                        <x-heroicon-o-folder class="w-3 h-3 flex-shrink-0" />
                        <span class="truncate">{{ $task->project->name }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Mobile View (sm and below) --}}
    <div class="block md:hidden">
        <div
            class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-all duration-200">
            {{-- Mobile Header Row --}}
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    {{-- Completion Toggle --}}
                    <button wire:click="toggleTaskCompletion"
                        class="flex-shrink-0 hover:scale-110 transition-transform duration-200 mt-0.5"
                        x-bind:class="{ 'animate-bounce': completionToggling }"
                        @click="completionToggling = true; setTimeout(() => completionToggling = false, 600)">
                        @if($task->status === 'completed')
                        <div class="relative">
                            <x-heroicon-s-check-circle class="w-6 h-6 text-green-500 dark:text-green-400" />
                            <div
                                class="absolute inset-0 bg-green-500 dark:bg-green-400 rounded-full animate-ping opacity-25">
                            </div>
                        </div>
                        @else
                        <div
                            class="w-6 h-6 rounded-full border-2 border-gray-300 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-all duration-200 flex items-center justify-center">
                            <div
                                class="w-0 h-0 bg-primary-500 dark:bg-primary-400 rounded-full hover:w-3 hover:h-3 transition-all duration-200">
                            </div>
                        </div>
                        @endif
                    </button>

                    {{-- Task Title & Description --}}
                    <div class="flex-1 min-w-0">
                        <div class="cursor-pointer" wire:click="viewDetails">
                            <h4
                                class="font-semibold text-gray-900 dark:text-gray-100 text-base leading-snug {{ $task->status === 'completed' ? 'line-through text-gray-500 dark:text-gray-400' : '' }} hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                {{ $task->title }}
                            </h4>
                        </div>
                    </div>
                </div>

                {{-- Mobile Actions Menu --}}
                <div class="flex items-center gap-2 ml-3" x-data="{ showActions: false }">
                    {{-- Priority Badge --}}
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-bold
                    {{ match($task->priority) {
                        'urgent' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                        'high' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300',
                        'normal' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
                        'low' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
                        default => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'
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
                        <span class="hidden sm:inline">{{ ucfirst($task->priority) }}</span>
                    </span>

                    {{-- Actions Menu Button --}}
                    <div class="relative">
                        <button @click="showActions = !showActions"
                            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                            x-bind:class="{ 'bg-gray-100 dark:bg-gray-700': showActions }">
                            <x-heroicon-o-ellipsis-horizontal class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                        </button>

                        {{-- Actions Dropdown --}}
                        <div x-show="showActions" x-cloak @click.away="showActions = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-600 py-2 z-50">
                            <button @click="showDetails = !showDetails; showActions = false"
                                class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-3">
                                <x-heroicon-o-eye class="w-4 h-4" />
                                <span x-text="showDetails ? 'Hide Details' : 'Show Details'"></span>
                            </button>
                            <button wire:click="editTask" @click="showActions = false"
                                class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-3">
                                <x-heroicon-o-pencil class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                <span>Edit Task</span>
                            </button>
                            <button wire:click="deleteTask" wire:confirm="Are you sure you want to delete this task?"
                                @click="showActions = false"
                                class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-3">
                                <x-heroicon-o-trash class="w-4 h-4 text-red-600 dark:text-red-400" />
                                <span>Delete Task</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mobile Meta Grid --}}
            <div class="grid grid-cols-2 gap-3 mb-4">
                {{-- Status --}}
                <div class="flex flex-col space-y-1">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Status</span>
                    <div class="relative" x-data="{ statusOpen: false, buttonRect: {} }">
                        <button @click="statusOpen = !statusOpen; buttonRect = $el.getBoundingClientRect()" class="inline-flex items-center gap-2 px-2 py-1.5 rounded-md text-xs font-semibold transition-all duration-200 w-full justify-start shadow-sm border
                        {{ match($task->status) {
                            'completed' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 border-green-200 dark:border-green-700',
                            'in_progress' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 border-yellow-200 dark:border-yellow-700',
                            'pending' => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600',
                            'cancelled' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 border-red-200 dark:border-red-700',
                            default => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600'
                        } }}">
                            <div class="w-2 h-2 rounded-full
                        {{ match($task->status) {
                            'completed' => 'bg-green-500 dark:bg-green-400',
                            'in_progress' => 'bg-yellow-500 dark:bg-yellow-400',
                            'pending' => 'bg-gray-400 dark:bg-gray-500',
                            'cancelled' => 'bg-red-500 dark:bg-red-400',
                            default => 'bg-gray-400 dark:bg-gray-500'
                        } }}"></div>
                            <span class="truncate">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                            <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform duration-200 ml-auto"
                                x-bind:class="{ 'rotate-180': statusOpen }" />
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
                                class="fixed w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-600 py-2 overflow-hidden"
                                style="z-index: 9999;" x-bind:style="{
                             top: (buttonRect.bottom + window.scrollY + 8) + 'px',
                             left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 192 - 8)) + 'px'
                         }">
                                @foreach($this->getStatusOptions() as $statusValue => $statusLabel)
                                <button wire:click="updateStatus('{{ $statusValue }}')" @click="statusOpen = false"
                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent dark:hover:from-primary-900/20 dark:hover:to-transparent transition-all duration-200 flex items-center gap-3 {{ $task->status === $statusValue ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 border-l-4 border-l-primary-500 dark:border-l-primary-400' : '' }}">
                                    <div class="w-3 h-3 rounded-full 
                                {{ match($statusValue) {
                                    'completed' => 'bg-green-500 dark:bg-green-400',
                                    'in_progress' => 'bg-yellow-500 dark:bg-yellow-400',
                                    'pending' => 'bg-gray-400 dark:bg-gray-500',
                                    'cancelled' => 'bg-red-500 dark:bg-red-400',
                                    default => 'bg-gray-400 dark:bg-gray-500'
                                } }}"></div>
                                    <span class="font-medium">{{ $statusLabel }}</span>
                                    @if($task->status === $statusValue)
                                    <x-heroicon-s-check
                                        class="w-4 h-4 text-primary-600 dark:text-primary-400 ml-auto" />
                                    @endif
                                </button>
                                @endforeach
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Due Date --}}
                <div class="flex flex-col space-y-1">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Due Date</span>
                    @php
                    $isOverdue = $task->task_date->isPast() && $task->status !== 'completed';
                    $isToday = $task->task_date->isToday();
                    $isTomorrow = $task->task_date->isTomorrow();
                    @endphp
                    <div class="flex items-center gap-2">
                        <div
                            class="p-1 rounded {{ $isOverdue ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' : ($isToday ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400' : 'text-gray-600 dark:text-gray-400') }}">
                            <x-heroicon-o-calendar-days class="w-4 h-4" />
                        </div>
                        <div class="flex flex-col">
                            <span
                                class="font-medium text-sm {{ $isOverdue ? 'text-red-600 dark:text-red-400' : ($isToday ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-700 dark:text-gray-300') }}">
                                @if($isToday)
                                Today
                                @elseif($isTomorrow)
                                Tomorrow
                                @else
                                {{ $task->task_date->format('M d') }}
                                @endif
                            </span>
                            @if($isOverdue)
                            <span class="text-xs text-red-500 dark:text-red-400 font-medium">Overdue</span>
                            @elseif($task->task_date->diffInDays() <= 3 && !$task->task_date->isPast())
                                <span class="text-xs text-yellow-600 dark:text-yellow-400">Soon</span>
                                @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mobile Assignees & Project Row --}}
            <div class="space-y-3">
                {{-- Assignees --}}
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Assigned to:</span>
                    @if($task->assignedUsers && $task->assignedUsers->count() > 0)
                    <div class="flex items-center gap-2">
                        <div class="flex -space-x-1">
                            @foreach($task->assignedUsers->take(3) as $user)
                            <div class="w-6 h-6 bg-gradient-to-br from-primary-400 to-primary-600 dark:from-primary-500 dark:to-primary-700 text-white rounded-full flex items-center justify-center text-xs font-bold border-2 border-white dark:border-gray-800 shadow-sm"
                                title="{{ $user->name }}">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            @endforeach
                            @if($task->assignedUsers->count() > 3)
                            <div class="w-6 h-6 bg-gray-400 dark:bg-gray-600 text-white rounded-full flex items-center justify-center text-xs font-bold border-2 border-white dark:border-gray-800 shadow-sm"
                                title="+{{ $task->assignedUsers->count() - 3 }} more">
                                +{{ $task->assignedUsers->count() - 3 }}
                            </div>
                            @endif
                        </div>
                        @if($task->assignedUsers->count() === 1)
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate max-w-24">{{
                            $task->assignedUsers->first()->name }}</span>
                        @else
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $task->assignedUsers->count() }}
                            people</span>
                        @endif
                    </div>
                    @else
                    <div class="flex items-center gap-2 text-gray-400 dark:text-gray-500">
                        <div
                            class="w-6 h-6 bg-gray-100 dark:bg-gray-700 border border-dashed border-gray-300 dark:border-gray-600 rounded-full flex items-center justify-center">
                            <x-heroicon-o-plus class="w-3 h-3" />
                        </div>
                        <span class="text-sm">Unassigned</span>
                    </div>
                    @endif
                </div>

                {{-- Project --}}
                @if($task->project)
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Project:</span>
                    <div class="flex items-center gap-1.5 px-2 py-1 bg-gradient-to-r from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 text-indigo-800 dark:text-indigo-300 rounded-md text-xs font-semibold border border-indigo-200 dark:border-indigo-700"
                        title="{{ $task->project->name }}">
                        <x-heroicon-o-folder class="w-3 h-3 flex-shrink-0" />
                        <span class="truncate max-w-32">{{ Str::limit($task->project->name, 20) }}</span>
                    </div>
                </div>
                @endif
            </div>

            {{-- Expandable Details Section (Mobile) --}}
            <div x-data="{ showDetails: false }" class="mt-4">
                <button @click="showDetails = !showDetails"
                    class="w-full flex items-center justify-center gap-2 px-3 py-2 text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors border border-gray-200 dark:border-gray-600">
                    <span x-text="showDetails ? 'Hide Details' : 'Show Details'"></span>
                    <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform duration-200"
                        x-bind:class="{ 'rotate-180': showDetails }" />
                </button>

                <div x-show="showDetails" x-cloak x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform translate-y-0"
                    x-transition:leave-end="opacity-0 transform -translate-y-2"
                    class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 space-y-3">

                    {{-- Task Description (if not shown above) --}}
                    @if($task->description && strlen($task->description) > 100)
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium block mb-1">Description</span>
                        <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                            {{ $task->description }}
                        </p>
                    </div>
                    @endif

                    {{-- Task Meta Information --}}
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        {{-- Created Info --}}
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium block mb-1">Created</span>
                            <div class="text-gray-700 dark:text-gray-300">
                                <div class="text-xs">{{ $task->created_at->format('M d, Y') }}</div>
                                @if($task->creator)
                                <div class="text-xs text-gray-500 dark:text-gray-400">by {{ $task->creator->name }}
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Progress Info --}}
                        @if($task->subtasks && $task->subtasks->count() > 0)
                        <div>
                            <span
                                class="text-xs text-gray-500 dark:text-gray-400 font-medium block mb-1">Progress</span>
                            <div class="text-gray-700 dark:text-gray-300">
                                <div class="text-xs">{{ $task->subtasks->where('status', 'completed')->count() }}/{{
                                    $task->subtasks->count() }} subtasks</div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-1">
                                    <div class="bg-primary-600 dark:bg-primary-500 h-1.5 rounded-full transition-all duration-300"
                                        style="width: {{ $task->progress_percentage }}%"></div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Mobile Action Buttons --}}
                    <div class="flex gap-2 pt-2">
                        <button wire:click="editTask"
                            class="flex-1 flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 rounded-lg transition-colors border border-blue-200 dark:border-blue-700">
                            <x-heroicon-o-pencil class="w-4 h-4" />
                            <span>Edit</span>
                        </button>

                        <button wire:click="duplicateTask"
                            class="flex-1 flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors border border-gray-200 dark:border-gray-600">
                            <x-heroicon-o-document-duplicate class="w-4 h-4" />
                            <span>Copy</span>
                        </button>

                        <button wire:click="deleteTask" wire:confirm="Are you sure you want to delete this task?"
                            class="px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 rounded-lg transition-colors border border-red-200 dark:border-red-700">
                            <x-heroicon-o-trash class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
</div>