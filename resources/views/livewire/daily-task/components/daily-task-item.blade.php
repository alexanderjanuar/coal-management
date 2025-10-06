{{-- Desktop View with Enhanced Dark Mode Styling --}}
<div class="w-full">
    {{-- Desktop View - Full Width Table Row --}}
    <div class="hidden lg:block w-full">
        <div class="grid grid-cols-12 gap-2 xl:gap-4 items-center min-h-[48px] px-3 py-2 
                hover:bg-gray-50 dark:hover:bg-gray-800/50 
                rounded-lg transition-all duration-200 
                border border-transparent hover:border-gray-200 dark:hover:border-gray-700">
            {{-- 1. Checkbox Column (1/12) --}}
            <div class="col-span-1 flex items-center justify-center">
                <button wire:click="toggleTaskCompletion"
                    class="flex-shrink-0 hover:scale-110 transition-transform duration-200 w-6 h-6 flex items-center justify-center group">
                    @if($task->status === 'completed')
                    <div class="relative">
                        <x-heroicon-s-check-circle class="w-5 h-5 text-green-500 dark:text-green-400" />
                        <div
                            class="absolute inset-0 bg-green-500 dark:bg-green-400 rounded-full animate-ping opacity-25">
                        </div>
                    </div>
                    @else
                    <div class="w-5 h-5 rounded-full border-2 border-gray-300 dark:border-gray-600 
                            group-hover:border-primary-500 dark:group-hover:border-primary-400 
                            group-hover:bg-primary-50 dark:group-hover:bg-primary-900/30 
                            transition-all duration-200 flex items-center justify-center">
                        <div class="w-0 h-0 bg-primary-500 dark:bg-primary-400 rounded-full 
                                group-hover:w-2 group-hover:h-2 transition-all duration-200"></div>
                    </div>
                    @endif
                </button>
            </div>

            {{-- 2. Task Info Column (4/12) --}}
            <div class="col-span-4 min-w-0">
                <div class="cursor-pointer group" wire:click="viewDetails">
                    <h2 class="font-semibold text-gray-900 dark:text-gray-100 text-sm xl:text-base leading-tight truncate 
                           group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-200
                           {{ $task->status === 'completed' ? 'line-through text-gray-500 dark:text-gray-400' : '' }}">
                        {{ Str::limit(strip_tags($task->title), 60) }}
                    </h2>
                    @if($task->description)
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                        {{ Str::limit(strip_tags($task->description), 60) }}
                    </p>
                    @endif
                </div>
            </div>

            {{-- 3. Status Dropdown (2/12) --}}
            <div class="col-span-2 min-w-0">
                <div class="relative w-full" x-data="{ statusOpen: false, buttonRect: {} }">
                    <button @click="statusOpen = !statusOpen; buttonRect = $el.getBoundingClientRect()" class="inline-flex items-center gap-2 px-2 xl:px-3 py-2 h-8 rounded-lg text-xs font-semibold 
                           transition-all duration-200 hover:scale-105 w-full justify-center shadow-sm border
                           {{ match($task->status) {
                               'completed' => 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300 border-green-200 dark:border-green-700/60',
                               'in_progress' => 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300 border-yellow-200 dark:border-yellow-700/60',
                               'pending' => 'bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600/60',
                               'cancelled' => 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300 border-red-200 dark:border-red-700/60',
                               default => 'bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600/60'
                           } }}">
                        <div class="w-2 h-2 rounded-full flex-shrink-0
                        {{ match($task->status) {
                            'completed' => 'bg-green-500 dark:bg-green-400',
                            'in_progress' => 'bg-yellow-500 dark:bg-yellow-400 animate-pulse',
                            'pending' => 'bg-gray-400 dark:bg-gray-500',
                            'cancelled' => 'bg-red-500 dark:bg-red-400',
                            default => 'bg-gray-400 dark:bg-gray-500'
                        } }}"></div>
                        <span class="truncate hidden xl:inline">{{ ucfirst(str_replace('_', ' ', $task->status))
                            }}</span>
                        <span class="truncate xl:hidden">{{ Str::limit(ucfirst(str_replace('_', ' ', $task->status)), 8,
                            '') }}</span>
                        <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform duration-200 flex-shrink-0"
                            x-bind:class="{ 'rotate-180': statusOpen }" />
                    </button>

                    {{-- Status Dropdown Menu --}}
                    <template x-teleport="body">
                        <div x-show="statusOpen" x-cloak @click.away="statusOpen = false"
                            @keydown.escape="statusOpen = false" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="fixed w-44 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-600 
                               py-2 z-[9999] backdrop-blur-sm dark:shadow-2xl max-h-[80vh] overflow-y-auto"
                            x-bind:style="{
                            top: Math.min(buttonRect.bottom + window.scrollY + 8, window.innerHeight + window.scrollY - 300) + 'px',
                            left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 176 - 8)) + 'px'
                        }">
                            @foreach($this->getStatusOptions() as $statusValue => $statusLabel)
                            <button wire:click="updateStatus('{{ $statusValue }}')" @click="statusOpen = false"
                                class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 
                                   hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent 
                                   dark:hover:from-primary-900/30 dark:hover:to-transparent 
                                   transition-all duration-200 flex items-center gap-3 
                                   {{ $task->status === $statusValue ? 'bg-primary-50 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 border-l-4 border-l-primary-500' : '' }}">
                                <div class="w-3 h-3 rounded-full 
                                {{ match($statusValue) {
                                    'completed' => 'bg-green-500 dark:bg-green-400',
                                    'in_progress' => 'bg-yellow-500 dark:bg-yellow-400',
                                    'pending' => 'bg-gray-400 dark:bg-gray-500',
                                    'cancelled' => 'bg-red-500 dark:bg-red-400',
                                    default => 'bg-gray-400'
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

            {{-- 4. Priority Dropdown (1/12) --}}
            <div class="col-span-1 min-w-0">
                <div class="relative" x-data="{ priorityOpen: false, buttonRect: {} }">
                    <button @click="priorityOpen = !priorityOpen; buttonRect = $el.getBoundingClientRect()" class="inline-flex items-center gap-1.5 px-2 py-2 h-8 rounded-lg text-xs font-bold 
                           transition-all duration-200 hover:scale-105 shadow-sm border justify-center w-full
                           {{ match($task->priority) {
                               'urgent' => 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300 border-red-200 dark:border-red-700/60 animate-pulse',
                               'high' => 'bg-orange-100 dark:bg-orange-900/40 text-orange-800 dark:text-orange-300 border-orange-200 dark:border-orange-700/60',
                               'normal' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-700/60',
                               'low' => 'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600/60',
                               default => 'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300'
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
                        <x-dynamic-component :component="$priorityIcon" class="w-3 h-3 flex-shrink-0" />
                        <span class="hidden 2xl:inline truncate">{{ ucfirst($task->priority) }}</span>
                        <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform flex-shrink-0"
                            x-bind:class="{ 'rotate-180': priorityOpen }" />
                    </button>

                    {{-- Priority Dropdown Menu --}}
                    <template x-teleport="body">
                        <div x-show="priorityOpen" x-cloak @click.away="priorityOpen = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            class="fixed w-44 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-600 
                               py-2 z-[9999] backdrop-blur-sm max-h-[80vh] overflow-y-auto" x-bind:style="{
                            top: Math.min(buttonRect.bottom + window.scrollY + 8, window.innerHeight + window.scrollY - 300) + 'px',
                            left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 176 - 8)) + 'px'
                        }">
                            @foreach($this->getPriorityOptions() as $priorityValue => $priorityLabel)
                            <button wire:click="updatePriority('{{ $priorityValue }}')" @click="priorityOpen = false"
                                class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 
                                   hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent 
                                   transition-all duration-200 flex items-center gap-3 
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
                                <span class="font-medium">{{ $priorityLabel }}</span>
                                @if($task->priority === $priorityValue)
                                <x-heroicon-s-check class="w-4 h-4 text-primary-600 ml-auto" />
                                @endif
                            </button>
                            @endforeach
                        </div>
                    </template>
                </div>
            </div>

            {{-- 5. Assignee Dropdown (2/12) --}}
            <div class="col-span-2 min-w-0">
                <div class="relative w-full" x-data="{ assigneeOpen: false, buttonRect: {} }">
                    <button @click="assigneeOpen = !assigneeOpen; buttonRect = $el.getBoundingClientRect()" class="w-full flex items-center gap-2 px-2 py-1.5 h-8 rounded-lg border border-gray-200 dark:border-gray-700 
                           hover:border-primary-300 dark:hover:border-primary-600 transition-all duration-200 
                           bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750">
                        @if($task->assignedUsers && $task->assignedUsers->count() > 0)
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <div class="flex -space-x-1.5">
                                @foreach($task->assignedUsers->take(2) as $user)
                                <div class="w-5 h-5 bg-gradient-to-br from-primary-400 to-primary-600 text-white rounded-full 
                                        flex items-center justify-center text-xs font-bold border border-white dark:border-gray-800"
                                    title="{{ $user->name }}">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                @endforeach
                                @if($task->assignedUsers->count() > 2)
                                <div
                                    class="w-5 h-5 bg-gray-400 text-white rounded-full flex items-center justify-center text-xs font-bold border border-white dark:border-gray-800">
                                    +{{ $task->assignedUsers->count() - 2 }}
                                </div>
                                @endif
                            </div>
                            <span
                                class="hidden xl:inline text-xs font-medium text-gray-700 dark:text-gray-300 truncate">
                                @if($task->assignedUsers->count() === 1)
                                {{ Str::limit($task->assignedUsers->first()->name, 12) }}
                                @else
                                {{ $task->assignedUsers->count() }} orang
                                @endif
                            </span>
                        </div>
                        @else
                        <div class="flex items-center gap-2 text-gray-400 dark:text-gray-500 flex-1 min-w-0">
                            <div
                                class="w-5 h-5 bg-gray-100 dark:bg-gray-700 border border-dashed border-gray-300 dark:border-gray-600 rounded-full flex items-center justify-center">
                                <x-heroicon-o-plus class="w-2.5 h-2.5" />
                            </div>
                            <span class="hidden lg:inline text-xs truncate">Unassigned</span>
                        </div>
                        @endif
                        <x-heroicon-o-chevron-down class="w-3.5 h-3.5 text-gray-400 flex-shrink-0"
                            x-bind:class="{ 'rotate-180': assigneeOpen }" />
                    </button>

                    {{-- Assignee Dropdown Menu --}}
                    <template x-teleport="body">
                        <div x-show="assigneeOpen" x-cloak @click.away="assigneeOpen = false" class="fixed w-64 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-600 
                               z-[9999] backdrop-blur-sm max-h-80 overflow-y-auto" x-bind:style="{
                            top: Math.min(buttonRect.bottom + window.scrollY + 8, window.innerHeight + window.scrollY - 350) + 'px',
                            left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 256 - 8)) + 'px'
                        }">
                            <div
                                class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide border-b border-gray-100 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800 z-10">
                                Assign Users
                            </div>
                            @foreach($this->getUserOptions() as $userId => $userName)
                            @php $isAssigned = $task->assignedUsers->contains($userId); @endphp
                            <button wire:click="{{ $isAssigned ? 'unassignUser' : 'assignUser' }}({{ $userId }})"
                                @click="assigneeOpen = false"
                                class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 
                                   hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent flex items-center gap-3">
                                <div class="w-8 h-8 bg-gradient-to-br from-primary-400 to-primary-600 text-white rounded-full 
                                        flex items-center justify-center text-xs font-bold">
                                    {{ strtoupper(substr($userName, 0, 1)) }}
                                </div>
                                <span class="font-medium flex-1 truncate">{{ $userName }}</span>
                                @if($isAssigned)
                                <x-heroicon-s-check class="w-4 h-4 text-green-600" />
                                @endif
                            </button>
                            @endforeach
                        </div>
                    </template>
                </div>
            </div>

            {{-- 6. Project Dropdown (1/12) --}}
            <div class="col-span-1 min-w-0">
                <div class="relative" x-data="{ projectOpen: false, buttonRect: {} }">
                    <button @click="projectOpen = !projectOpen; buttonRect = $el.getBoundingClientRect()" class="flex items-center gap-1 px-2 py-2 h-8 rounded-lg text-xs font-semibold 
                   transition-all duration-200 border shadow-sm w-full justify-center
                   @if($task->project)
                       bg-gradient-to-r from-indigo-100 to-purple-100 dark:from-indigo-900/40 dark:to-purple-900/40 
                       text-indigo-800 dark:text-indigo-300 border-indigo-200 dark:border-indigo-700/60
                       hover:shadow-md
                   @else
                       bg-gray-100 dark:bg-gray-700/60 border-dashed border-gray-300 dark:border-gray-600/60 
                       text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600/80
                   @endif">
                        @if($task->project)
                        <x-heroicon-o-folder class="w-3 h-3 flex-shrink-0" />
                        <span class="hidden 2xl:inline truncate" title="{{ $task->project->name }}">
                            {{ Str::limit($task->project->name, 8) }}
                        </span>
                        @else
                        <x-heroicon-o-folder-plus class="w-3 h-3 flex-shrink-0" />
                        <span class="hidden 2xl:inline">Add</span>
                        @endif
                        <x-heroicon-o-chevron-down class="w-3 h-3 flex-shrink-0"
                            x-bind:class="{ 'rotate-180': projectOpen }" />
                    </button>

                    {{-- Project Dropdown Menu --}}
                    <template x-teleport="body">
                        <div x-show="projectOpen" x-cloak @click.away="projectOpen = false"
                            @keydown.escape="projectOpen = false" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="fixed w-80 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-600 
                                z-[9999] backdrop-blur-sm dark:shadow-2xl max-h-[90vh] flex flex-col" x-bind:style="{
                                top: Math.min(buttonRect.bottom + window.scrollY + 8, window.innerHeight + window.scrollY - 450) + 'px',
                                left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 320 - 8)) + 'px'
                            }">

                            {{-- Fixed Header --}}
                            <div class="px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 
                            border-b border-gray-200 dark:border-gray-600 flex-shrink-0 rounded-t-xl">
                                <div class="flex items-center justify-between">
                                    <h3
                                        class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                        <x-heroicon-o-building-office class="w-4 h-4" />
                                        Select Project
                                    </h3>
                                    <button wire:click="redirectToCreateProject" @click="projectOpen = false" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium 
                                        bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 
                                        text-white rounded-lg transition-all duration-200 hover:scale-105 shadow-sm">
                                                <x-heroicon-o-plus class="w-3 h-3" />
                                        <span>Create</span>
                                    </button>
                                </div>

                                @if($selectedClientId)
                                <div class="mt-2 text-xs text-gray-600 dark:text-gray-400 flex items-center gap-1">
                                    <x-heroicon-o-information-circle class="w-3 h-3" />
                                    <span>Client: {{ $this->getClientOptions()[$selectedClientId] ?? 'Unknown' }}</span>
                                </div>
                                @endif
                            </div>

                            {{-- Scrollable Content --}}
                            <div class="overflow-y-auto flex-1">
                                {{-- Remove Project Option --}}
                                <button wire:click="updateProject(null)" @click="projectOpen = false"
                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 
                               hover:bg-gradient-to-r hover:from-red-50 hover:to-transparent 
                               dark:hover:from-red-900/20 dark:hover:to-transparent 
                               transition-all duration-200 flex items-center gap-3 
                               border-b border-gray-100 dark:border-gray-700
                               {{ !$task->project_id ? 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border-l-4 border-l-red-500 dark:border-l-red-400' : '' }}">
                                    <x-heroicon-o-minus-circle
                                        class="w-4 h-4 text-red-500 dark:text-red-400 flex-shrink-0" />
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
                                        Select Client:
                                    </label>
                                    <select wire:model.live="selectedClientId" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg 
                                   focus:ring-2 focus:ring-primary-500 focus:border-primary-500 
                                   dark:bg-gray-800 dark:text-gray-100 bg-white">
                                        <option value="">-- Select Client --</option>
                                        @foreach($this->getClientOptions() as $clientId => $clientName)
                                        <option value="{{ $clientId }}">{{ $clientName }}</option>
                                        @endforeach
                                    </select>

                                    @if($selectedClientId)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 flex items-center gap-1">
                                        <x-heroicon-o-information-circle class="w-3 h-3" />
                                        No project? Click "Create" button above
                                    </p>
                                    @endif
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
                                        class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 
                                   hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent 
                                   dark:hover:from-primary-900/30 dark:hover:to-transparent 
                                   transition-all duration-200 flex items-center gap-3 
                                   {{ $task->project_id == $projectId ? 'bg-primary-50 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 border-l-4 border-l-primary-500 dark:border-l-primary-400' : '' }}">
                                        <x-heroicon-o-folder
                                            class="w-4 h-4 text-indigo-500 dark:text-indigo-400 flex-shrink-0" />
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
                                    <x-heroicon-o-folder-open class="w-12 h-12 mx-auto mb-3 opacity-50" />
                                    <p class="text-sm font-medium mb-2">No projects for this client</p>
                                    <button wire:click="redirectToCreateProject" @click="projectOpen = false" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium 
                                   bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 
                                   text-white rounded-lg transition-all duration-200 hover:scale-105 shadow-sm">
                                        <x-heroicon-o-plus class="w-4 h-4" />
                                        Create First Project
                                    </button>
                                </div>
                                @endif
                                @else
                                <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <x-heroicon-o-building-office class="w-8 h-8 mx-auto mb-2 opacity-50" />
                                    <p class="text-sm font-medium">Select client first</p>
                                    <p class="text-xs mt-1">to view projects list</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- 7. Due Date (1/12) --}}
            <div class="col-span-1 min-w-0">
                <div class="relative" x-data="{ dateOpen: false, buttonRect: {} }">
                    @php
                    $isOverdue = $task->task_date->isPast() && $task->status !== 'completed';
                    $isToday = $task->task_date->isToday();
                    $isTomorrow = $task->task_date->isTomorrow();
                    @endphp

                    <button @click="dateOpen = !dateOpen; buttonRect = $el.getBoundingClientRect()" class="flex items-center gap-1 px-2 py-2 h-8 rounded-lg transition-all duration-200 
                            border shadow-sm w-full justify-center
                            {{ $isOverdue ? 
                                'bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 border-red-200 dark:border-red-700/60 hover:bg-red-200 dark:hover:bg-red-900/60' : 
                                ($isToday ? 
                                    'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-600 dark:text-yellow-400 border-yellow-200 dark:border-yellow-700/60 hover:bg-yellow-200 dark:hover:bg-yellow-900/60' : 
                                    'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-600/60 hover:bg-gray-200 dark:hover:bg-gray-600/80'
                                ) 
                    }}">
                        <x-heroicon-o-calendar-days class="w-3 h-3 flex-shrink-0" />
                        <span class="font-medium text-xs truncate">
                            @if($isToday) Today
                            @elseif($isTomorrow) Tom
                            @else {{ $task->task_date->format('M d') }}
                            @endif
                        </span>
                        <x-heroicon-o-pencil class="w-2.5 h-2.5 opacity-60 flex-shrink-0" />
                    </button>

                    {{-- Date Picker Dropdown --}}
                    <template x-teleport="body">
                        <div x-show="dateOpen" x-cloak @click.away="dateOpen = false" @keydown.escape="dateOpen = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="fixed w-80 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-600 
                                z-[9999] backdrop-blur-sm dark:shadow-2xl max-h-[90vh] flex flex-col" x-bind:style="{
                                top: Math.min(buttonRect.bottom + window.scrollY + 8, window.innerHeight + window.scrollY - 450) + 'px',
                                left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 320 - 8)) + 'px'
                            }">

                            {{-- Fixed Header --}}
                            <div class="px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 
                            border-b border-gray-200 dark:border-gray-600 flex-shrink-0 rounded-t-xl">
                                <h3
                                    class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                    <x-heroicon-o-calendar-days class="w-4 h-4" />
                                    Edit Due Date
                                </h3>
                            </div>

                            {{-- Scrollable Content --}}
                            <div class="p-4 overflow-y-auto flex-1">
                                {{ $this->dueDateForm }}
                            </div>

                            {{-- Fixed Quick Options Footer --}}
                            <div class="px-4 pb-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex-shrink-0">
                                <div
                                    class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                                    Quick Options
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <button wire:click="updateTaskDate('{{ today()->format('Y-m-d') }}')"
                                        @click="dateOpen = false" class="px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 
                                   bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 
                                   rounded-lg transition-colors border border-gray-200 dark:border-gray-600">
                                        Today
                                    </button>
                                    <button wire:click="updateTaskDate('{{ today()->addDay()->format('Y-m-d') }}')"
                                        @click="dateOpen = false" class="px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 
                                   bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 
                                   rounded-lg transition-colors border border-gray-200 dark:border-gray-600">
                                        Tomorrow
                                    </button>
                                    <button wire:click="updateTaskDate('{{ today()->addDays(7)->format('Y-m-d') }}')"
                                        @click="dateOpen = false" class="px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 
                                   bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 
                                   rounded-lg transition-colors border border-gray-200 dark:border-gray-600">
                                        Next Week
                                    </button>
                                    <button wire:click="updateTaskDate('{{ today()->addMonth()->format('Y-m-d') }}')"
                                        @click="dateOpen = false" class="px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 
                                   bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 
                                   rounded-lg transition-colors border border-gray-200 dark:border-gray-600">
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


    {{-- Tablet View - Compact Two-Row Layout --}}
    @include('components.daily-task.list.daily-task-md-task', ['task' => $task])

    {{-- Mobile View - Stacked Card Layout --}}
    @include('components.daily-task.list.daily-task-xs-task', ['task' => $task])
    
</div>
</div>