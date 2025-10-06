<div class="hidden md:block lg:hidden w-full">
    <div class="p-3 space-y-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-lg transition-all duration-200 
                border border-transparent hover:border-gray-200 dark:hover:border-gray-700">

        {{-- Row 1: Main Info --}}
        <div class="flex items-start gap-3">
            {{-- Checkbox --}}
            <button wire:click="toggleTaskCompletion" class="flex-shrink-0 mt-1">
                @if($task->status === 'completed')
                <div class="relative">
                    <x-heroicon-s-check-circle class="w-5 h-5 text-green-500 dark:text-green-400" />
                </div>
                @else
                <div class="w-5 h-5 rounded-full border-2 border-gray-300 dark:border-gray-600 
                            hover:border-primary-500 dark:hover:border-primary-400 transition-all"></div>
                @endif
            </button>

            {{-- Task Title & Description --}}
            <div class="flex-1 min-w-0 cursor-pointer" wire:click="viewDetails">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100 text-sm leading-tight
                           hover:text-primary-600 dark:hover:text-primary-400 transition-colors
                           {{ $task->status === 'completed' ? 'line-through text-gray-500 dark:text-gray-400' : '' }}">
                    {{ $task->title }}
                </h2>
                @if($task->description)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                    {{ strip_tags($task->description) }}
                </p>
                @endif
            </div>

            {{-- Status Badge --}}
            <div x-data="{ statusOpen: false, buttonRect: {} }">
                <button @click="statusOpen = !statusOpen; buttonRect = $el.getBoundingClientRect()" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold shadow-sm border
                           {{ match($task->status) {
                               'completed' => 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300 border-green-200',
                               'in_progress' => 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300 border-yellow-200',
                               'pending' => 'bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 border-gray-200',
                               'cancelled' => 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300 border-red-200',
                               default => 'bg-gray-100 text-gray-700 border-gray-200'
                           } }}">
                    <div class="w-2 h-2 rounded-full bg-current"></div>
                    <span class="whitespace-nowrap">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                    <x-heroicon-o-chevron-down class="w-3 h-3" x-bind:class="{ 'rotate-180': statusOpen }" />
                </button>

                {{-- Status Dropdown --}}
                <template x-teleport="body">
                    <div x-show="statusOpen" x-cloak @click.away="statusOpen = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        class="fixed w-44 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-600 
                               py-2 z-[9999] max-h-[80vh] overflow-y-auto" x-bind:style="{
                            top: Math.min(buttonRect.bottom + window.scrollY + 8, window.innerHeight + window.scrollY - 300) + 'px',
                            left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 176 - 8)) + 'px'
                        }">
                        @foreach($this->getStatusOptions() as $statusValue => $statusLabel)
                        <button wire:click="updateStatus('{{ $statusValue }}')" @click="statusOpen = false"
                            class="w-full text-left px-4 py-3 text-sm hover:bg-gradient-to-r hover:from-primary-50 
                                   hover:to-transparent flex items-center gap-3 
                                   {{ $task->status === $statusValue ? 'bg-primary-50 dark:bg-primary-900/40 border-l-4 border-l-primary-500' : '' }}">
                            <div class="w-3 h-3 rounded-full 
                                {{ match($statusValue) {
                                    'completed' => 'bg-green-500',
                                    'in_progress' => 'bg-yellow-500',
                                    'pending' => 'bg-gray-400',
                                    'cancelled' => 'bg-red-500',
                                    default => 'bg-gray-400'
                                } }}"></div>
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $statusLabel }}</span>
                            @if($task->status === $statusValue)
                            <x-heroicon-s-check class="w-4 h-4 text-primary-600 ml-auto" />
                            @endif
                        </button>
                        @endforeach
                    </div>
                </template>
            </div>
        </div>

        {{-- Row 2: Metadata (Priority, Assignee, Project, Date) --}}
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Priority --}}
            <div x-data="{ priorityOpen: false, buttonRect: {} }">
                <button @click="priorityOpen = !priorityOpen; buttonRect = $el.getBoundingClientRect()" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold shadow-sm border
                           {{ match($task->priority) {
                               'urgent' => 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300 border-red-200',
                               'high' => 'bg-orange-100 dark:bg-orange-900/40 text-orange-800 dark:text-orange-300 border-orange-200',
                               'normal' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 border-blue-200',
                               'low' => 'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 border-gray-200',
                               default => 'bg-gray-100 text-gray-700 border-gray-200'
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
                </button>

                {{-- Priority Dropdown --}}
                <template x-teleport="body">
                    <div x-show="priorityOpen" x-cloak @click.away="priorityOpen = false"
                        class="fixed w-44 bg-white dark:bg-gray-800 rounded-xl shadow-xl border py-2 z-[9999]"
                        x-bind:style="{
                            top: Math.min(buttonRect.bottom + window.scrollY + 8, window.innerHeight + window.scrollY - 250) + 'px',
                            left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 176 - 8)) + 'px'
                        }">
                        @foreach($this->getPriorityOptions() as $priorityValue => $priorityLabel)
                        <button wire:click="updatePriority('{{ $priorityValue }}')" @click="priorityOpen = false"
                            class="w-full text-left px-4 py-3 text-sm hover:bg-primary-50 flex items-center gap-3
                                   {{ $task->priority === $priorityValue ? 'bg-primary-50 dark:bg-primary-900/40 border-l-4 border-l-primary-500' : '' }}">
                            @php
                            $icon = match($priorityValue) {
                            'urgent' => 'heroicon-s-exclamation-triangle',
                            'high' => 'heroicon-o-exclamation-triangle',
                            'normal' => 'heroicon-o-minus',
                            'low' => 'heroicon-o-arrow-down',
                            default => 'heroicon-o-minus'
                            };
                            @endphp
                            <x-dynamic-component :component="$icon" class="w-4 h-4" />
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $priorityLabel }}</span>
                            @if($task->priority === $priorityValue)
                            <x-heroicon-s-check class="w-4 h-4 text-primary-600 ml-auto" />
                            @endif
                        </button>
                        @endforeach
                    </div>
                </template>
            </div>

            {{-- Assignee --}}
            <div x-data="{ assigneeOpen: false, buttonRect: {} }">
                <button @click="assigneeOpen = !assigneeOpen; buttonRect = $el.getBoundingClientRect()" class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg border border-gray-200 dark:border-gray-700 
                           hover:border-primary-300 bg-white dark:bg-gray-800 shadow-sm">
                    @if($task->assignedUsers && $task->assignedUsers->count() > 0)
                    <div class="flex -space-x-1.5">
                        @foreach($task->assignedUsers->take(2) as $user)
                        <div class="w-5 h-5 bg-gradient-to-br from-primary-400 to-primary-600 text-white rounded-full 
                                    flex items-center justify-center text-xs font-bold border border-white"
                            title="{{ $user->name }}">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        @endforeach
                        @if($task->assignedUsers->count() > 2)
                        <div
                            class="w-5 h-5 bg-gray-400 text-white rounded-full flex items-center justify-center text-xs font-bold border border-white">
                            +{{ $task->assignedUsers->count() - 2 }}
                        </div>
                        @endif
                    </div>
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                        @if($task->assignedUsers->count() === 1)
                        {{ Str::limit($task->assignedUsers->first()->name, 15) }}
                        @else
                        {{ $task->assignedUsers->count() }} assigned
                        @endif
                    </span>
                    @else
                    <x-heroicon-o-user-plus class="w-4 h-4 text-gray-400" />
                    <span class="text-xs text-gray-500">Unassigned</span>
                    @endif
                </button>

                {{-- Assignee Dropdown --}}
                <template x-teleport="body">
                    <div x-show="assigneeOpen" x-cloak @click.away="assigneeOpen = false"
                        class="fixed w-64 bg-white dark:bg-gray-800 rounded-xl shadow-xl border z-[9999] max-h-80 overflow-y-auto"
                        x-bind:style="{
                            top: Math.min(buttonRect.bottom + window.scrollY + 8, window.innerHeight + window.scrollY - 350) + 'px',
                            left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 256 - 8)) + 'px'
                        }">
                        <div
                            class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase border-b sticky top-0 bg-white dark:bg-gray-800">
                            Assign Users
                        </div>
                        @foreach($this->getUserOptions() as $userId => $userName)
                        @php $isAssigned = $task->assignedUsers->contains($userId); @endphp
                        <button wire:click="{{ $isAssigned ? 'unassignUser' : 'assignUser' }}({{ $userId }})"
                            @click="assigneeOpen = false"
                            class="w-full text-left px-4 py-3 text-sm hover:bg-primary-50 flex items-center gap-3">
                            <div class="w-7 h-7 bg-gradient-to-br from-primary-400 to-primary-600 text-white rounded-full 
                                        flex items-center justify-center text-xs font-bold">
                                {{ strtoupper(substr($userName, 0, 1)) }}
                            </div>
                            <span class="font-medium flex-1 text-gray-700 dark:text-gray-300">{{ $userName }}</span>
                            @if($isAssigned)
                            <x-heroicon-s-check class="w-4 h-4 text-green-600" />
                            @endif
                        </button>
                        @endforeach
                    </div>
                </template>
            </div>

            {{-- Project --}}
            @if($task->project)
            <div x-data="{ projectOpen: false, buttonRect: {} }">
                <button @click="projectOpen = !projectOpen; buttonRect = $el.getBoundingClientRect()" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold shadow-sm border
                            bg-gradient-to-r from-indigo-100 to-purple-100 dark:from-indigo-900/40 dark:to-purple-900/40 
                            text-indigo-800 dark:text-indigo-300 border-indigo-200 dark:border-indigo-700">
                    <x-heroicon-o-folder class="w-3 h-3" />
                    <span>{{ Str::limit($task->project->name, 15) }}</span>
                    <x-heroicon-o-pencil class="w-3 h-3 opacity-60" />
                </button>

                {{-- Project Dropdown - Same structure as desktop but mobile-optimized positioning --}}
                <template x-teleport="body">
                    <div x-show="projectOpen" x-cloak @click.away="projectOpen = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        class="fixed w-80 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border z-[9999] max-h-[80vh] flex flex-col"
                        x-bind:style="{
                                top: Math.min(buttonRect.bottom + window.scrollY + 8, window.innerHeight + window.scrollY - 450) + 'px',
                                left: Math.max(8, Math.min(buttonRect.left + window.scrollX - 160, window.innerWidth - 320 - 8)) + 'px'
                            }">

                        {{-- Same content as desktop --}}
                        <div class="px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 
                                        border-b flex-shrink-0 rounded-t-xl">
                            <div class="flex items-center justify-between">
                                <h3
                                    class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                    <x-heroicon-o-building-office class="w-4 h-4" />
                                    Select Project
                                </h3>
                                <button @click="projectOpen = false"
                                    class="p-1 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                                    <x-heroicon-o-x-mark class="w-5 h-5 text-gray-500" />
                                </button>
                            </div>
                        </div>

                        <div class="overflow-y-auto flex-1">
                            <button wire:click="updateProject(null)" @click="projectOpen = false"
                                class="w-full text-left px-4 py-3 text-sm hover:bg-red-50 flex items-center gap-3 border-b">
                                <x-heroicon-o-minus-circle class="w-4 h-4 text-red-500" />
                                <span class="font-medium text-gray-700 dark:text-gray-300">Remove Project</span>
                            </button>

                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">
                                    Select Client:
                                </label>
                                <select wire:model.live="selectedClientId"
                                    class="w-full px-3 py-2 text-sm border rounded-lg dark:bg-gray-800 dark:text-gray-100">
                                    <option value="">-- Select Client --</option>
                                    @foreach($this->getClientOptions() as $clientId => $clientName)
                                    <option value="{{ $clientId }}">{{ $clientName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if($selectedClientId)
                            @php $projects = $this->getProjectOptions(); @endphp
                            @if(!empty($projects))
                            <div class="py-2">
                                @foreach($projects as $projectId => $projectName)
                                <button wire:click="updateProject({{ $projectId }})" @click="projectOpen = false"
                                    class="w-full text-left px-4 py-3 text-sm hover:bg-primary-50 flex items-center gap-3
                                            {{ $task->project_id == $projectId ? 'bg-primary-50 border-l-4 border-l-primary-500' : '' }}">
                                    <x-heroicon-o-folder class="w-4 h-4 text-indigo-500" />
                                    <span class="font-medium flex-1 text-gray-700 dark:text-gray-300">{{
                                        $projectName }}</span>
                                    @if($task->project_id == $projectId)
                                    <x-heroicon-s-check class="w-4 h-4 text-primary-600" />
                                    @endif
                                </button>
                                @endforeach
                            </div>
                            @else
                            <div class="px-4 py-6 text-center text-gray-500">
                                <x-heroicon-o-folder-open class="w-10 h-10 mx-auto mb-2 opacity-50" />
                                <p class="text-sm">No projects available</p>
                            </div>
                            @endif
                            @endif
                        </div>
                    </div>
                </template>
            </div>
            @else
            <button wire:click="$set('selectedClientId', null)" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold shadow-sm 
                        border-dashed border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 
                        text-gray-500 dark:text-gray-400">
                <x-heroicon-o-folder-plus class="w-3 h-3" />
                <span>Add Project</span>
            </button>
            @endif

            {{-- Due Date --}}
            <div x-data="{ dateOpen: false, buttonRect: {} }">
                @php
                $isOverdue = $task->task_date->isPast() && $task->status !== 'completed';
                $isToday = $task->task_date->isToday();
                @endphp

                <button @click="dateOpen = !dateOpen; buttonRect = $el.getBoundingClientRect()" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold shadow-sm border
                        {{ $isOverdue ? 
                            'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 border-red-200' : 
                            ($isToday ? 
                            'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300 border-yellow-200' : 
                            'bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 border-gray-200') }}">
                    <x-heroicon-o-calendar-days class="w-3 h-3" />
                    <span>
                        @if($isToday) Today
                        @elseif($task->task_date->isTomorrow()) Tomorrow
                        @else {{ $task->task_date->format('M d, Y') }}
                        @endif
                    </span>
                    <x-heroicon-o-pencil class="w-3 h-3 opacity-60" />
                </button>

                {{-- Date Picker Dropdown --}}
                <template x-teleport="body">
                    <div x-show="dateOpen" x-cloak @click.away="dateOpen = false"
                        class="fixed w-80 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border z-[9999] max-h-[85vh] flex flex-col"
                        x-bind:style="{
                        top: Math.min(buttonRect.bottom + window.scrollY + 8, window.innerHeight + window.scrollY - 450) + 'px',
                        left: Math.max(8, Math.min(buttonRect.left + window.scrollX - 240, window.innerWidth - 320 - 8)) + 'px'
                        }">

                        <div class="px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 
                        border-b flex-shrink-0 rounded-t-xl">
                            <div class="flex items-center justify-between">
                                <h3
                                    class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                    <x-heroicon-o-calendar-days class="w-4 h-4" />
                                    Edit Due Date
                                </h3>
                                <button @click="dateOpen = false"
                                    class="p-1 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                                    <x-heroicon-o-x-mark class="w-5 h-5 text-gray-500" />
                                </button>
                            </div>
                        </div>

                        <div class="p-4 overflow-y-auto flex-1">
                            {{ $this->dueDateForm }}
                        </div>

                        <div class="px-4 pb-4 pt-3 border-t flex-shrink-0">
                            <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Quick Options</div>
                            <div class="grid grid-cols-2 gap-2">
                                <button wire:click="updateTaskDate('{{ today()->format('Y-m-d') }}')"
                                    @click="dateOpen = false" class="px-3 py-2 text-xs font-medium bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 
                               dark:hover:bg-gray-600 rounded-lg text-gray-700 dark:text-gray-300">
                                    Today
                                </button>
                                <button wire:click="updateTaskDate('{{ today()->addDay()->format('Y-m-d') }}')"
                                    @click="dateOpen = false" class="px-3 py-2 text-xs font-medium bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 
                               dark:hover:bg-gray-600 rounded-lg text-gray-700 dark:text-gray-300">
                                    Tomorrow
                                </button>
                                <button wire:click="updateTaskDate('{{ today()->addDays(7)->format('Y-m-d') }}')"
                                    @click="dateOpen = false"
                                    class="px-3 py-2 text-xs font-medium bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 
                               dark:hover:bg-gray-600 rounded-lg text-gray-600 rounded-lg text-gray-700 dark:text-gray-300">
                                    Next Week
                                </button>
                                <button wire:click="updateTaskDate('{{ today()->addMonth()->format('Y-m-d') }}')"
                                    @click="dateOpen = false" class="px-3 py-2 text-xs font-medium bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 
                               dark:hover:bg-gray-600 rounded-lg text-gray-700 dark:text-gray-300">
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