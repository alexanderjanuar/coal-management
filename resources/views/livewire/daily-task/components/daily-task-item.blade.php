{{-- Desktop View with Enhanced Dark Mode Styling --}}
<div class="hidden lg:block">
    {{-- Horizontal Scrollable Container --}}
    <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-gray-100 dark:scrollbar-track-gray-800 scroll-smooth">
        {{-- Set minimum width to ensure horizontal scroll when needed --}}
        <div class="min-w-[1200px]">
            <div class="grid grid-cols-12 gap-2 lg:gap-4 items-center h-12 px-3 py-2 
                        hover:bg-gray-50 dark:hover:bg-gray-800/50 
                        rounded-lg transition-all duration-200 
                        border border-transparent hover:border-gray-200 dark:hover:border-gray-700">
                
                {{-- Checkbox & Completion Toggle --}}
                <div class="col-span-1 flex items-center justify-center h-full">
                    <button wire:click="toggleTaskCompletion"
                        class="flex-shrink-0 hover:scale-110 transition-transform duration-200 w-6 h-6 flex items-center justify-center group"
                        x-bind:class="{ 'animate-bounce': completionToggling }"
                        @click="completionToggling = true; setTimeout(() => completionToggling = false, 600)">
                        @if($task->status === 'completed')
                        <div class="relative">
                            <x-heroicon-s-check-circle class="w-5 h-5 text-green-500 dark:text-green-400" />
                            <div class="absolute inset-0 bg-green-500 dark:bg-green-400 rounded-full animate-ping opacity-25"></div>
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

                {{-- Task Info --}}
                <div class="col-span-4 h-full flex items-center">
                    <div class="flex-1 cursor-pointer group" wire:click="viewDetails">
                        <h2 class="font-semibold text-gray-900 dark:text-gray-100 text-sm lg:text-base leading-tight truncate 
                                   group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-200
                                   {{ $task->status === 'completed' ? 'line-through text-gray-500 dark:text-gray-400' : '' }}">
                            {{ Str::limit(strip_tags($task->title), 60) }}
                        </h2>
                        @if($task->description)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                            {{ Str::limit(strip_tags($task->description), 60) }}
                        </p>
                        @endif
                    </div>
                </div>

                {{-- Status Dropdown --}}
                <div class="col-span-2 h-full flex items-center">
                    <div class="relative w-full" x-data="{ statusOpen: false, buttonRect: {} }">
                        <button @click="statusOpen = !statusOpen; buttonRect = $el.getBoundingClientRect()"
                            class="inline-flex items-center gap-2 px-3 py-2 h-8 rounded-lg text-xs font-semibold 
                                   transition-all duration-200 hover:scale-105 w-full justify-center shadow-sm border
                                   {{ match($task->status) {
                                       'completed' => 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300 border-green-200 dark:border-green-700/60 hover:bg-green-200 dark:hover:bg-green-900/60 hover:shadow-md dark:hover:shadow-green-900/20',
                                       'in_progress' => 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300 border-yellow-200 dark:border-yellow-700/60 hover:bg-yellow-200 dark:hover:bg-yellow-900/60 hover:shadow-md dark:hover:shadow-yellow-900/20',
                                       'pending' => 'bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600/60 hover:bg-gray-200 dark:hover:bg-gray-600/80 hover:shadow-md dark:hover:shadow-gray-900/20',
                                       'cancelled' => 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300 border-red-200 dark:border-red-700/60 hover:bg-red-200 dark:hover:bg-red-900/60 hover:shadow-md dark:hover:shadow-red-900/20',
                                       default => 'bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600/60 hover:bg-gray-200 dark:hover:bg-gray-600/80'
                                   } }}">
                            <div class="w-2 h-2 rounded-full animate-pulse
                            {{ match($task->status) {
                                'completed' => 'bg-green-500 dark:bg-green-400',
                                'in_progress' => 'bg-yellow-500 dark:bg-yellow-400',
                                'pending' => 'bg-gray-400 dark:bg-gray-500',
                                'cancelled' => 'bg-red-500 dark:bg-red-400',
                                default => 'bg-gray-400 dark:bg-gray-500'
                            } }}"></div>
                            <span class="truncate">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                            <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform duration-200 flex-shrink-0"
                                x-bind:class="{ 'rotate-180': statusOpen }" />
                        </button>

                        {{-- Status Dropdown Menu --}}
                        <template x-teleport="body">
                            <div x-show="statusOpen" x-cloak @click.away="statusOpen = false"
                                @keydown.escape="statusOpen = false" 
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95 translate-y-2" 
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                                class="fixed w-44 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-600 
                                       py-2 overflow-hidden z-50 backdrop-blur-sm dark:shadow-2xl dark:shadow-gray-900/40"
                                x-bind:style="{
                                    top: (buttonRect.bottom + window.scrollY + 8) + 'px',
                                    left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 176 - 8)) + 'px'
                                }">

                                @foreach($this->getStatusOptions() as $statusValue => $statusLabel)
                                <button wire:click="updateStatus('{{ $statusValue }}')" @click="statusOpen = false"
                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 
                                           hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent 
                                           dark:hover:from-primary-900/30 dark:hover:to-transparent 
                                           transition-all duration-200 flex items-center gap-3 
                                           {{ $task->status === $statusValue ? 'bg-primary-50 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 border-l-4 border-l-primary-500 dark:border-l-primary-400' : '' }}">
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

                {{-- Priority Dropdown --}}
                <div class="col-span-1 h-full flex items-center justify-center">
                    <div class="relative" x-data="{ priorityOpen: false, buttonRect: {} }">
                        <button @click="priorityOpen = !priorityOpen; buttonRect = $el.getBoundingClientRect()" 
                            class="inline-flex items-center gap-1.5 px-3 py-2 h-8 rounded-lg text-xs font-bold 
                                   transition-all duration-200 hover:scale-105 shadow-sm border justify-center w-full
                                   {{ match($task->priority) {
                                       'urgent' => 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300 border-red-200 dark:border-red-700/60 animate-pulse hover:shadow-md dark:hover:shadow-red-900/20',
                                       'high' => 'bg-orange-100 dark:bg-orange-900/40 text-orange-800 dark:text-orange-300 border-orange-200 dark:border-orange-700/60 hover:shadow-md dark:hover:shadow-orange-900/20',
                                       'normal' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-700/60 hover:shadow-md dark:hover:shadow-blue-900/20',
                                       'low' => 'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600/60 hover:shadow-md dark:hover:shadow-gray-900/20',
                                       default => 'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600/60'
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
                            <span class="hidden xl:inline truncate">{{ ucfirst($task->priority) }}</span>
                            <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform duration-200 flex-shrink-0"
                                x-bind:class="{ 'rotate-180': priorityOpen }" />
                        </button>

                        {{-- Priority Dropdown Menu --}}
                        <template x-teleport="body">
                            <div x-show="priorityOpen" x-cloak @click.away="priorityOpen = false"
                                @keydown.escape="priorityOpen = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                                class="fixed w-44 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-600 
                                       py-2 overflow-hidden z-50 backdrop-blur-sm dark:shadow-2xl dark:shadow-gray-900/40"
                                x-bind:style="{
                                    top: (buttonRect.bottom + window.scrollY + 8) + 'px',
                                    left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 176 - 8)) + 'px'
                                }">

                                @foreach($this->getPriorityOptions() as $priorityValue => $priorityLabel)
                                <button wire:click="updatePriority('{{ $priorityValue }}')"
                                    @click="priorityOpen = false"
                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 
                                           hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent 
                                           dark:hover:from-primary-900/30 dark:hover:to-transparent 
                                           transition-all duration-200 flex items-center gap-3 
                                           {{ $task->priority === $priorityValue ? 'bg-primary-50 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 border-l-4 border-l-primary-500 dark:border-l-primary-400' : '' }}">
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
                                        'urgent' => 'text-red-500 dark:text-red-400',
                                        'high' => 'text-orange-500 dark:text-orange-400',
                                        'normal' => 'text-blue-500 dark:text-blue-400',
                                        'low' => 'text-gray-400 dark:text-gray-500',
                                        default => 'text-gray-400 dark:text-gray-500'
                                    } }}" />
                                    <span class="font-medium">{{ $priorityLabel }}</span>
                                    @if($task->priority === $priorityValue)
                                    <x-heroicon-s-check class="w-4 h-4 text-primary-600 dark:text-primary-400 ml-auto" />
                                    @endif
                                </button>
                                @endforeach
                            </div>
                        </template>
                    </div>
                </div>
                
                {{-- Assignee Section --}}
                <div class="col-span-2 h-full flex items-center">
                    <div class="relative w-full" x-data="{ assigneeOpen: false, buttonRect: {} }">
                        <button @click="assigneeOpen = !assigneeOpen; buttonRect = $el.getBoundingClientRect()"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 h-8 rounded-lg border border-gray-200 dark:border-gray-700 
                                   hover:border-primary-300 dark:hover:border-primary-600 transition-all duration-200 
                                   bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750 hover:shadow-sm">
                            @if($task->assignedUsers && $task->assignedUsers->count() > 0)
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <div class="flex -space-x-1.5">
                                    @foreach($task->assignedUsers->take(2) as $user)
                                    <div class="w-5 h-5 bg-gradient-to-br from-primary-400 to-primary-600 dark:from-primary-500 dark:to-primary-700 
                                                text-white rounded-full flex items-center justify-center text-xs font-bold 
                                                border-1.5 border-white dark:border-gray-800 shadow-sm"
                                        title="{{ $user->name }}">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    @endforeach
                                    @if($task->assignedUsers->count() > 2)
                                    <div class="w-5 h-5 bg-gray-400 dark:bg-gray-600 text-white rounded-full flex items-center justify-center text-xs font-bold 
                                                border-1.5 border-white dark:border-gray-800 shadow-sm">
                                        +{{ $task->assignedUsers->count() - 2 }}
                                    </div>
                                    @endif
                                </div>
                                @if($task->assignedUsers->count() === 1)
                                <span class="hidden xl:inline text-xs font-medium text-gray-700 dark:text-gray-300 truncate">
                                    {{ $task->assignedUsers->first()->name }}
                                </span>
                                @else
                                <span class="hidden lg:inline text-xs text-gray-500 dark:text-gray-400">
                                    {{ $task->assignedUsers->count() }} orang
                                </span>
                                @endif
                            </div>
                            @else
                            <div class="flex items-center gap-2 text-gray-400 dark:text-gray-500 flex-1">
                                <div class="w-5 h-5 bg-gray-100 dark:bg-gray-700 border-1.5 border-dashed border-gray-300 dark:border-gray-600 
                                            rounded-full flex items-center justify-center">
                                    <x-heroicon-o-plus class="w-2.5 h-2.5" />
                                </div>
                                <span class="hidden lg:inline text-xs truncate">Belum ditugaskan</span>
                            </div>
                            @endif
                            <x-heroicon-o-chevron-down class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500 flex-shrink-0 transition-transform duration-200"
                                x-bind:class="{ 'rotate-180': assigneeOpen }" />
                        </button>

                        {{-- Assignee Dropdown Menu --}}
                        <template x-teleport="body">
                            <div x-show="assigneeOpen" x-cloak @click.away="assigneeOpen = false"
                                @keydown.escape="assigneeOpen = false" 
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95 translate-y-2" 
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                                class="fixed w-64 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-600 
                                       py-2 overflow-hidden max-h-80 overflow-y-auto z-50 backdrop-blur-sm dark:shadow-2xl dark:shadow-gray-900/40"
                                x-bind:style="{
                                    top: (buttonRect.bottom + window.scrollY + 8) + 'px',
                                    left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 256 - 8)) + 'px'
                                }">

                                <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide 
                                            border-b border-gray-100 dark:border-gray-700">
                                    Assign Users
                                </div>

                                @foreach($this->getUserOptions() as $userId => $userName)
                                @php $isAssigned = $task->assignedUsers->contains($userId); @endphp
                                <button wire:click="{{ $isAssigned ? 'unassignUser' : 'assignUser' }}({{ $userId }})"
                                    @click="assigneeOpen = false"
                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 
                                           hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent 
                                           dark:hover:from-primary-900/30 dark:hover:to-transparent 
                                           transition-all duration-200 flex items-center gap-3">
                                    <div class="w-8 h-8 bg-gradient-to-br from-primary-400 to-primary-600 dark:from-primary-500 dark:to-primary-700 
                                                text-white rounded-full flex items-center justify-center text-xs font-bold">
                                        {{ strtoupper(substr($userName, 0, 1)) }}
                                    </div>
                                    <span class="font-medium flex-1 truncate">{{ $userName }}</span>
                                    @if($isAssigned)
                                    <x-heroicon-s-check class="w-4 h-4 text-green-600 dark:text-green-400" />
                                    @else
                                    <x-heroicon-o-plus class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                                    @endif
                                </button>
                                @endforeach
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Project Dropdown --}}
                <div class="col-span-1 h-full flex items-center justify-center">
                    <div class="relative" x-data="{ projectOpen: false, buttonRect: {} }">
                        <button @click="projectOpen = !projectOpen; buttonRect = $el.getBoundingClientRect()" 
                            class="flex items-center gap-1.5 px-3 py-2 h-8 rounded-lg text-xs font-semibold 
                                   transition-all duration-200 hover:scale-105 border shadow-sm w-full justify-center
                                   @if($task->project)
                                       bg-gradient-to-r from-indigo-100 to-purple-100 dark:from-indigo-900/40 dark:to-purple-900/40 
                                       text-indigo-800 dark:text-indigo-300 border-indigo-200 dark:border-indigo-700/60
                                       hover:shadow-md dark:hover:shadow-indigo-900/20
                                   @else
                                       bg-gray-100 dark:bg-gray-700/60 border-2 border-dashed border-gray-300 dark:border-gray-600/60 
                                       text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600/80
                                   @endif">
                            @if($task->project)
                            <x-heroicon-o-folder class="w-3 h-3 flex-shrink-0" />
                            <span class="hidden xl:inline truncate" title="{{ $task->project->name }}">
                                {{ Str::limit($task->project->name, 8) }}
                            </span>
                            @else
                            <x-heroicon-o-folder-plus class="w-3 h-3 flex-shrink-0" />
                            <span class="hidden lg:inline">None</span>
                            @endif
                            <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform duration-200 flex-shrink-0"
                                x-bind:class="{ 'rotate-180': projectOpen }" />
                        </button>

                        {{-- Project Dropdown with Client Selection --}}
                        <template x-teleport="body">
                            <div x-show="projectOpen" x-cloak @click.away="projectOpen = false"
                                @keydown.escape="projectOpen = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                                class="fixed w-80 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-600 
                                       overflow-hidden max-h-96 overflow-y-auto z-50 backdrop-blur-sm dark:shadow-2xl dark:shadow-gray-900/40"
                                x-bind:style="{
                                    top: (buttonRect.bottom + window.scrollY + 8) + 'px',
                                    left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 320 - 8)) + 'px'
                                }">

                                {{-- Header --}}
                                <div class="px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 
                                            border-b border-gray-200 dark:border-gray-600">
                                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                        <x-heroicon-o-building-office class="w-4 h-4" />
                                        Select Project
                                    </h3>
                                </div>

                                {{-- No Project Option --}}
                                <button wire:click="updateProject(null)" @click="projectOpen = false"
                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 
                                           hover:bg-gradient-to-r hover:from-red-50 hover:to-transparent 
                                           dark:hover:from-red-900/20 dark:hover:to-transparent 
                                           transition-all duration-200 flex items-center gap-3 border-b border-gray-100 dark:border-gray-700 
                                           {{ !$task->project_id ? 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border-l-4 border-l-red-500 dark:border-l-red-400' : '' }}">
                                    <x-heroicon-o-minus-circle class="w-4 h-4 text-red-500 dark:text-red-400" />
                                    <span class="font-medium">Remove Project</span>
                                    @if(!$task->project_id)
                                    <x-heroicon-s-check class="w-4 h-4 text-red-600 dark:text-red-400 ml-auto" />
                                    @endif
                                </button>

                                {{-- Client Selection --}}
                                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">
                                        <x-heroicon-o-users class="w-3 h-3 inline mr-1" />
                                        Pilih Client Dulu:
                                    </label>
                                    <select wire:model.live="selectedClientId"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg 
                                               focus:ring-2 focus:ring-primary-500 focus:border-primary-500 
                                               dark:bg-gray-800 dark:text-gray-100 bg-white">
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
                                    <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                        Projects ({{ count($projects) }})
                                    </div>
                                    @foreach($projects as $projectId => $projectName)
                                    <button wire:click="updateProject({{ $projectId }})" @click="projectOpen = false"
                                        class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 
                                               hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent 
                                               dark:hover:from-primary-900/30 dark:hover:to-transparent 
                                               transition-all duration-200 flex items-center gap-3 
                                               {{ $task->project_id == $projectId ? 'bg-primary-50 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 border-l-4 border-l-primary-500 dark:border-l-primary-400' : '' }}">
                                        <x-heroicon-o-folder class="w-4 h-4 text-indigo-500 dark:text-indigo-400 flex-shrink-0" />
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium truncate">{{ $projectName }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                {{ $this->getClientOptions()[$selectedClientId] ?? 'Client' }}
                                            </div>
                                        </div>
                                        @if($task->project_id == $projectId)
                                        <x-heroicon-s-check class="w-4 h-4 text-primary-600 dark:text-primary-400 flex-shrink-0" />
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

                {{-- Due Date with Clickable Edit --}}
                <div class="col-span-1 h-full flex items-center justify-center">
                    <div class="relative" x-data="{ dateOpen: false, buttonRect: {} }">
                        @php
                        $isOverdue = $task->task_date->isPast() && $task->status !== 'completed';
                        $isToday = $task->task_date->isToday();
                        $isTomorrow = $task->task_date->isTomorrow();
                        @endphp

                        <button @click="dateOpen = !dateOpen; buttonRect = $el.getBoundingClientRect()" 
                            class="flex items-center gap-1.5 px-3 py-2 h-8 rounded-lg transition-all duration-200 hover:scale-105 
                                   border shadow-sm w-full justify-center
                                   {{ $isOverdue ? 
                                       'bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 border-red-200 dark:border-red-700/60 hover:bg-red-200 dark:hover:bg-red-900/60 hover:shadow-md dark:hover:shadow-red-900/20' : 
                                       ($isToday ? 
                                           'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-600 dark:text-yellow-400 border-yellow-200 dark:border-yellow-700/60 hover:bg-yellow-200 dark:hover:bg-yellow-900/60 hover:shadow-md dark:hover:shadow-yellow-900/20' : 
                                           'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-600/60 hover:bg-gray-200 dark:hover:bg-gray-600/80 hover:shadow-sm'
                                       ) 
                                   }}">
                            <div class="p-0.5 rounded flex-shrink-0 
                                        {{ $isOverdue ? 'bg-red-200 dark:bg-red-800/60' : ($isToday ? 'bg-yellow-200 dark:bg-yellow-800/60' : 'bg-gray-200 dark:bg-gray-600/60') }}">
                                <x-heroicon-o-calendar-days class="w-3 h-3" />
                            </div>

                            <div class="flex flex-col min-w-0">
                                <span class="font-medium text-xs truncate">
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

                            <x-heroicon-o-pencil class="w-3 h-3 opacity-60 flex-shrink-0" />
                        </button>

                        {{-- Date Picker Dropdown --}}
                        <template x-teleport="body">
                            <div x-show="dateOpen" x-cloak @click.away="dateOpen = false"
                                @keydown.escape="dateOpen = false" 
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                                class="fixed w-80 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-600 
                                       z-50 backdrop-blur-sm dark:shadow-2xl dark:shadow-gray-900/40"
                                x-bind:style="{
                                    top: (buttonRect.bottom + window.scrollY + 8) + 'px',
                                    left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 320 - 8)) + 'px'
                                }">

                                {{-- Header --}}
                                <div class="px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 
                                            border-b border-gray-200 dark:border-gray-600">
                                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
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
                                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2 pt-3">
                                        Quick Options
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button wire:click="updateTaskDate('{{ today()->format('Y-m-d') }}')"
                                            @click="dateOpen = false"
                                            class="px-3 py-2 text-xs text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 
                                                   hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                            Today
                                        </button>
                                        <button wire:click="updateTaskDate('{{ today()->addDay()->format('Y-m-d') }}')"
                                            @click="dateOpen = false"
                                            class="px-3 py-2 text-xs text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 
                                                   hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                            Tomorrow
                                        </button>
                                        <button wire:click="updateTaskDate('{{ today()->addDays(7)->format('Y-m-d') }}')"
                                            @click="dateOpen = false"
                                            class="px-3 py-2 text-xs text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 
                                                   hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                            Next Week
                                        </button>
                                        <button wire:click="updateTaskDate('{{ today()->addMonth()->format('Y-m-d') }}')"
                                            @click="dateOpen = false"
                                            class="px-3 py-2 text-xs text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 
                                                   hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
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
</div>