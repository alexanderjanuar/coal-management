<div class="block md:hidden w-full">
    <div class="p-4 space-y-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 
                shadow-sm hover:shadow-md transition-all duration-200">

        {{-- Header Row: Checkbox + Title --}}
        <div class="flex items-start gap-3">
            {{-- Checkbox --}}
            <button wire:click="toggleTaskCompletion" class="flex-shrink-0 mt-0.5">
                @if($task->status === 'completed')
                <div class="relative">
                    <x-heroicon-s-check-circle class="w-6 h-6 text-green-500 dark:text-green-400" />
                    <div class="absolute inset-0 bg-green-500 rounded-full animate-ping opacity-25"></div>
                </div>
                @else
                <div class="w-6 h-6 rounded-full border-2 border-gray-300 dark:border-gray-600 
                            active:scale-95 transition-transform"></div>
                @endif
            </button>

            {{-- Task Title & Description --}}
            <div class="flex-1 min-w-0" wire:click="viewDetails">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100 text-base leading-snug
                           {{ $task->status === 'completed' ? 'line-through text-gray-500 dark:text-gray-400' : '' }}">
                    {{ $task->title }}
                </h2>
                @if($task->description)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1.5 line-clamp-2 leading-relaxed">
                    {{ strip_tags($task->description) }}
                </p>
                @endif
            </div>
        </div>

        {{-- Status & Priority Row --}}
        <div class="flex items-center gap-2">
            {{-- Status Badge --}}
            <div class="flex-1" x-data="{ statusOpen: false, buttonRect: {} }">
                <button @click="statusOpen = !statusOpen; buttonRect = $el.getBoundingClientRect()" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold shadow-sm border
                           {{ match($task->status) {
                               'completed' => 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300 border-green-200 dark:border-green-700',
                               'in_progress' => 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300 border-yellow-200 dark:border-yellow-700',
                               'pending' => 'bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600',
                               'cancelled' => 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300 border-red-200 dark:border-red-700',
                               default => 'bg-gray-100 text-gray-700 border-gray-200'
                           } }}">
                    <div class="w-2.5 h-2.5 rounded-full bg-current 
                        {{ $task->status === 'in_progress' ? 'animate-pulse' : '' }}"></div>
                    <span class="flex-1 text-center">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                    <x-heroicon-o-chevron-down class="w-4 h-4" x-bind:class="{ 'rotate-180': statusOpen }" />
                </button>

                {{-- Status Dropdown - Mobile Optimized --}}
                <template x-teleport="body">
                    <div x-show="statusOpen" x-cloak @click.away="statusOpen = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0" class="fixed inset-x-4 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-600 
                               z-[9999] max-h-[70vh] overflow-y-auto" x-bind:style="{
                            top: Math.min(buttonRect.bottom + window.scrollY + 12, window.innerHeight + window.scrollY - 400) + 'px'
                        }">

                        {{-- Header --}}
                        <div
                            class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 sticky top-0 z-10">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Change Status
                                </h3>
                                <button @click="statusOpen = false"
                                    class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg">
                                    <x-heroicon-o-x-mark class="w-5 h-5 text-gray-500" />
                                </button>
                            </div>
                        </div>

                        {{-- Status Options --}}
                        <div class="p-2">
                            @foreach($this->getStatusOptions() as $statusValue => $statusLabel)
                            <button wire:click="updateStatus('{{ $statusValue }}')" @click="statusOpen = false"
                                class="w-full text-left px-4 py-3.5 rounded-xl text-base font-medium
                                       hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors
                                       flex items-center gap-3 mb-1
                                       {{ $task->status === $statusValue ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300' }}">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center
                                    {{ match($statusValue) {
                                        'completed' => 'bg-green-100 dark:bg-green-900/40',
                                        'in_progress' => 'bg-yellow-100 dark:bg-yellow-900/40',
                                        'pending' => 'bg-gray-100 dark:bg-gray-700',
                                        'cancelled' => 'bg-red-100 dark:bg-red-900/40',
                                        default => 'bg-gray-100'
                                    } }}">
                                    <div class="w-3 h-3 rounded-full 
                                        {{ match($statusValue) {
                                            'completed' => 'bg-green-500',
                                            'in_progress' => 'bg-yellow-500',
                                            'pending' => 'bg-gray-400',
                                            'cancelled' => 'bg-red-500',
                                            default => 'bg-gray-400'
                                        } }}"></div>
                                </div>
                                <span class="flex-1">{{ $statusLabel }}</span>
                                @if($task->status === $statusValue)
                                <x-heroicon-s-check class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                                @endif
                            </button>
                            @endforeach
                        </div>
                    </div>
                </template>
            </div>

            {{-- Priority Badge --}}
            <div x-data="{ priorityOpen: false, buttonRect: {} }">
                <button @click="priorityOpen = !priorityOpen; buttonRect = $el.getBoundingClientRect()" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-bold shadow-sm border
                           {{ match($task->priority) {
                               'urgent' => 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300 border-red-200',
                               'high' => 'bg-orange-100 dark:bg-orange-900/40 text-orange-800 dark:text-orange-300 border-orange-200',
                               'normal' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 border-blue-200',
                               'low' => 'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 border-gray-200',
                               default => 'bg-gray-100 text-gray-700'
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
                    <x-dynamic-component :component="$priorityIcon" class="w-4 h-4" />
                    <span>{{ ucfirst($task->priority) }}</span>
                </button>

                {{-- Priority Dropdown - Mobile Optimized --}}
                <template x-teleport="body">
                    <div x-show="priorityOpen" x-cloak @click.away="priorityOpen = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="fixed inset-x-4 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border z-[9999]"
                        x-bind:style="{
                            top: Math.min(buttonRect.bottom + window.scrollY + 12, window.innerHeight + window.scrollY - 350) + 'px'
                        }">

                        {{-- Header --}}
                        <div
                            class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Set Priority</h3>
                                <button @click="priorityOpen = false"
                                    class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg">
                                    <x-heroicon-o-x-mark class="w-5 h-5 text-gray-500" />
                                </button>
                            </div>
                        </div>

                        {{-- Priority Options --}}
                        <div class="p-2">
                            @foreach($this->getPriorityOptions() as $priorityValue => $priorityLabel)
                            <button wire:click="updatePriority('{{ $priorityValue }}')" @click="priorityOpen = false"
                                class="w-full text-left px-4 py-3.5 rounded-xl text-base font-medium
                                       hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors
                                       flex items-center gap-3 mb-1
                                       {{ $task->priority === $priorityValue ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300' }}">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center
                                    {{ match($priorityValue) {
                                        'urgent' => 'bg-red-100 dark:bg-red-900/40',
                                        'high' => 'bg-orange-100 dark:bg-orange-900/40',
                                        'normal' => 'bg-blue-100 dark:bg-blue-900/40',
                                        'low' => 'bg-gray-100 dark:bg-gray-700',
                                        default => 'bg-gray-100'
                                    } }}">
                                    @php
                                    $icon = match($priorityValue) {
                                    'urgent' => 'heroicon-s-exclamation-triangle',
                                    'high' => 'heroicon-o-exclamation-triangle',
                                    'normal' => 'heroicon-o-minus',
                                    'low' => 'heroicon-o-arrow-down',
                                    default => 'heroicon-o-minus'
                                    };
                                    @endphp
                                    <x-dynamic-component :component="$icon" class="w-5 h-5" />
                                </div>
                                <span class="flex-1">{{ $priorityLabel }}</span>
                                @if($task->priority === $priorityValue)
                                <x-heroicon-s-check class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                                @endif
                            </button>
                            @endforeach
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Assignee Section --}}
        <div x-data="{ assigneeOpen: false, buttonRect: {} }">
            <button @click="assigneeOpen = !assigneeOpen; buttonRect = $el.getBoundingClientRect()"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 
                       hover:border-primary-300 dark:hover:border-primary-600 bg-white dark:bg-gray-800 shadow-sm transition-colors">
                @if($task->assignedUsers && $task->assignedUsers->count() > 0)
                <div class="flex -space-x-2">
                    @foreach($task->assignedUsers->take(3) as $user)
                    <div class="w-8 h-8 bg-gradient-to-br from-primary-400 to-primary-600 text-white rounded-full 
                                flex items-center justify-center text-sm font-bold border-2 border-white dark:border-gray-800"
                        title="{{ $user->name }}">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    @endforeach
                    @if($task->assignedUsers->count() > 3)
                    <div class="w-8 h-8 bg-gray-400 text-white rounded-full flex items-center justify-center 
                                text-xs font-bold border-2 border-white dark:border-gray-800">
                        +{{ $task->assignedUsers->count() - 3 }}
                    </div>
                    @endif
                </div>
                <div class="flex-1 text-left">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Assigned to</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        @if($task->assignedUsers->count() === 1)
                        {{ $task->assignedUsers->first()->name }}
                        @else
                        {{ $task->assignedUsers->count() }} people
                        @endif
                    </p>
                </div>
                @else
                <x-heroicon-o-user-plus class="w-8 h-8 text-gray-400" />
                <div class="flex-1 text-left">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Not assigned yet</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Tap to assign users</p>
                </div>
                @endif
                <x-heroicon-o-chevron-right class="w-5 h-5 text-gray-400 flex-shrink-0" />
            </button>

            {{-- Assignee Modal - Full Screen Bottom Sheet --}}
            <template x-teleport="body">
                <div x-show="assigneeOpen" x-cloak x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 z-[9998]"
                    @click="assigneeOpen = false">
                </div>

                <div x-show="assigneeOpen" x-cloak @click.away="assigneeOpen = false"
                    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full"
                    x-transition:enter-end="translate-y-0" x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
                    class="fixed inset-x-0 bottom-0 bg-white dark:bg-gray-800 rounded-t-3xl shadow-2xl z-[9999] max-h-[85vh] flex flex-col">

                    {{-- Handle Bar --}}
                    <div class="flex justify-center pt-3 pb-2">
                        <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
                    </div>

                    {{-- Header --}}
                    <div class="px-4 pb-3 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Assign Users</h3>
                            <button @click="assigneeOpen = false"
                                class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
                                <x-heroicon-o-x-mark class="w-6 h-6 text-gray-500" />
                            </button>
                        </div>
                    </div>

                    {{-- User List - Scrollable --}}
                    <div class="flex-1 overflow-y-auto p-4 space-y-2">
                        @foreach($this->getUserOptions() as $userId => $userName)
                        @php $isAssigned = $task->assignedUsers->contains($userId); @endphp
                        <button wire:click="{{ $isAssigned ? 'unassignUser' : 'assignUser' }}({{ $userId }})"
                            class="w-full flex items-center gap-3 p-3 rounded-xl transition-all
                                   {{ $isAssigned ? 'bg-primary-50 dark:bg-primary-900/30 border-2 border-primary-500 dark:border-primary-400' : 'bg-gray-50 dark:bg-gray-700/50 border-2 border-transparent hover:border-gray-300 dark:hover:border-gray-600' }}">
                            <div class="w-12 h-12 bg-gradient-to-br from-primary-400 to-primary-600 text-white rounded-full 
                                        flex items-center justify-center text-lg font-bold flex-shrink-0">
                                {{ strtoupper(substr($userName, 0, 1)) }}
                            </div>
                            <span class="flex-1 text-left font-medium text-gray-900 dark:text-gray-100">{{ $userName
                                }}</span>
                            @if($isAssigned)
                            <div
                                class="w-10 h-10 bg-green-100 dark:bg-green-900/40 rounded-full flex items-center justify-center">
                                <x-heroicon-s-check class="w-6 h-6 text-green-600 dark:text-green-400" />
                            </div>
                            @else
                            <div
                                class="w-10 h-10 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                <x-heroicon-o-plus class="w-5 h-5 text-gray-400" />
                            </div>
                            @endif
                        </button>
                        @endforeach
                    </div>
                </div>
            </template>
        </div>

        {{-- Project & Due Date Row --}}
        {{-- MOBILE VIEW - Project & Due Date Section (< 768px) --}} <div class="block md:hidden w-full">
            {{-- Project Section - Full Width Button --}}
            <div x-data="{ projectOpen: false }">
                <button @click="projectOpen = !projectOpen" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 
                   hover:border-primary-300 dark:hover:border-primary-600 bg-white dark:bg-gray-800 
                   shadow-sm hover:shadow-md transition-all active:scale-98">
                    @if($task->project)
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                        <x-heroicon-o-folder class="w-6 h-6 text-white" />
                    </div>
                    <div class="flex-1 text-left min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-0.5">Project</p>
                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate">
                            {{ $task->project->name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ $task->project->client->name ?? 'Client' }}
                        </p>
                    </div>
                    @else
                    <div
                        class="w-12 h-12 bg-gray-100 dark:bg-gray-700 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl flex items-center justify-center flex-shrink-0">
                        <x-heroicon-o-folder-plus class="w-6 h-6 text-gray-400 dark:text-gray-500" />
                    </div>
                    <div class="flex-1 text-left">
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Add to project</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Tap to select or create</p>
                    </div>
                    @endif
                    <x-heroicon-o-chevron-right class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" />
                </button>

                {{-- Project Modal - Full Screen Bottom Sheet --}}
                <template x-teleport="body">
                    {{-- Backdrop Overlay --}}
                    <div x-show="projectOpen" x-cloak x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9998]"
                        @click="projectOpen = false">
                    </div>

                    {{-- Bottom Sheet Modal --}}
                    <div x-show="projectOpen" x-cloak @click.away="projectOpen = false"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0"
                        x-transition:leave-end="translate-y-full" class="fixed inset-x-0 bottom-0 bg-white dark:bg-gray-800 rounded-t-3xl shadow-2xl 
                       z-[9999] max-h-[90vh] flex flex-col">

                        {{-- Drag Handle Bar --}}
                        <div class="flex justify-center pt-4 pb-2 flex-shrink-0">
                            <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
                        </div>

                        {{-- Modal Header --}}
                        <div class="px-5 pb-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center">
                                        <x-heroicon-o-building-office class="w-5 h-5 text-white" />
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Select
                                            Project</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Choose from list or
                                            create new</p>
                                    </div>
                                </div>
                                <button @click="projectOpen = false"
                                    class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors active:scale-95">
                                    <x-heroicon-o-x-mark class="w-6 h-6 text-gray-500 dark:text-gray-400" />
                                </button>
                            </div>
                        </div>

                        {{-- Scrollable Content Area --}}
                        <div class="flex-1 overflow-y-auto overscroll-contain">
                            {{-- Remove Project Option --}}
                            <div
                                class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                                <button wire:click="updateProject(null)" @click="projectOpen = false"
                                    class="w-full flex items-center gap-3 p-4 rounded-xl transition-all active:scale-98
                                   {{ !$task->project_id 
                                       ? 'bg-red-50 dark:bg-red-900/30 border-2 border-red-500 dark:border-red-400 shadow-sm' 
                                       : 'bg-white dark:bg-gray-800 border-2 border-transparent hover:border-red-300 dark:hover:border-red-700' }}">
                                    <div
                                        class="w-12 h-12 bg-red-100 dark:bg-red-900/40 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <x-heroicon-o-minus-circle class="w-6 h-6 text-red-600 dark:text-red-400" />
                                    </div>
                                    <span
                                        class="flex-1 text-left text-base font-bold text-gray-900 dark:text-gray-100">Remove
                                        Project</span>
                                    @if(!$task->project_id)
                                    <div
                                        class="w-8 h-8 bg-red-600 dark:bg-red-500 rounded-full flex items-center justify-center">
                                        <x-heroicon-s-check class="w-5 h-5 text-white" />
                                    </div>
                                    @endif
                                </button>
                            </div>

                            {{-- Client Selection Section --}}
                            <div class="p-5 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900/50 dark:to-gray-800 
                                border-b border-gray-200 dark:border-gray-700">
                                <label
                                    class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                                    <x-heroicon-o-users class="w-5 h-5" />
                                    Select Client First:
                                </label>
                                <select wire:model.live="selectedClientId" class="w-full px-4 py-3.5 text-base font-medium border-2 border-gray-300 dark:border-gray-600 
                                   rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 
                                   dark:bg-gray-800 dark:text-gray-100 bg-white shadow-sm transition-all">
                                    <option value="">-- Choose a client --</option>
                                    @foreach($this->getClientOptions() as $clientId => $clientName)
                                    <option value="{{ $clientId }}">{{ $clientName }}</option>
                                    @endforeach
                                </select>

                                {{-- Create Project Button - Show when client selected --}}
                                @if($selectedClientId)
                                <button wire:click="redirectToCreateProject" @click="projectOpen = false" class="w-full mt-3 inline-flex items-center justify-center gap-2 px-5 py-3.5 text-base font-bold 
                                   bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 
                                   text-white rounded-xl transition-all shadow-md hover:shadow-lg active:scale-98">
                                    <x-heroicon-o-plus class="w-5 h-5" />
                                    Create New Project
                                </button>
                                <p class="text-xs text-center text-gray-500 dark:text-gray-400 mt-2">
                                    Can't find your project? Create one now
                                </p>
                                @endif
                            </div>

                            {{-- Projects List --}}
                            @if($selectedClientId)
                            @php $projects = $this->getProjectOptions(); @endphp

                            @if(!empty($projects))
                            <div class="p-4 space-y-2">
                                <p
                                    class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-2 mb-3 flex items-center gap-2">
                                    <x-heroicon-o-rectangle-stack class="w-4 h-4" />
                                    Available Projects ({{ count($projects) }})
                                </p>

                                @foreach($projects as $projectId => $projectName)
                                <button wire:click="updateProject({{ $projectId }})" @click="projectOpen = false"
                                    class="w-full flex items-center gap-3 p-4 rounded-xl transition-all active:scale-98
                                       {{ $task->project_id == $projectId 
                                           ? 'bg-primary-50 dark:bg-primary-900/30 border-2 border-primary-500 dark:border-primary-400 shadow-sm' 
                                           : 'bg-gray-50 dark:bg-gray-700/50 border-2 border-transparent hover:border-primary-300 dark:hover:border-primary-600' }}">
                                    <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl 
                                            flex items-center justify-center flex-shrink-0 shadow-sm">
                                        <x-heroicon-o-folder class="w-7 h-7 text-white" />
                                    </div>
                                    <div class="flex-1 text-left min-w-0">
                                        <p class="font-bold text-base text-gray-900 dark:text-gray-100 truncate mb-0.5">
                                            {{ $projectName }}
                                        </p>
                                        <p
                                            class="text-xs text-gray-500 dark:text-gray-400 truncate flex items-center gap-1">
                                            <x-heroicon-o-building-office class="w-3 h-3" />
                                            {{ $this->getClientOptions()[$selectedClientId] ?? 'Client' }}
                                        </p>
                                    </div>
                                    @if($task->project_id == $projectId)
                                    <div
                                        class="w-10 h-10 bg-primary-600 dark:bg-primary-500 rounded-full flex items-center justify-center flex-shrink-0">
                                        <x-heroicon-s-check class="w-6 h-6 text-white" />
                                    </div>
                                    @else
                                    <div
                                        class="w-10 h-10 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center flex-shrink-0">
                                        <x-heroicon-o-arrow-right class="w-5 h-5 text-gray-400" />
                                    </div>
                                    @endif
                                </button>
                                @endforeach
                            </div>
                            @else
                            {{-- Empty State - No Projects --}}
                            <div class="p-8 text-center">
                                <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 
                                        rounded-3xl flex items-center justify-center mx-auto mb-4 shadow-inner">
                                    <x-heroicon-o-folder-open class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                                </div>
                                <p class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">No projects yet
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 px-4">
                                    Create your first project for {{ $this->getClientOptions()[$selectedClientId] ??
                                    'this client' }}
                                </p>
                                <button wire:click="redirectToCreateProject" @click="projectOpen = false" class="inline-flex items-center gap-2 px-6 py-3.5 text-base font-bold 
                                       bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 
                                       text-white rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                                    <x-heroicon-o-plus class="w-5 h-5" />
                                    Create Project
                                </button>
                            </div>
                            @endif
                            @else
                            {{-- Empty State - No Client Selected --}}
                            <div class="p-8 text-center">
                                <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 
                                    rounded-3xl flex items-center justify-center mx-auto mb-4 shadow-inner">
                                    <x-heroicon-o-building-office class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                                </div>
                                <p class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">Select a client
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 px-4">
                                    Choose a client from the dropdown above to see their projects
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </template>
            </div>

            {{-- Due Date Section - Full Width Button --}}
            <div x-data="{ dateOpen: false }" class="mt-3">
                @php
                $isOverdue = $task->task_date->isPast() && $task->status !== 'completed';
                $isToday = $task->task_date->isToday();
                $isTomorrow = $task->task_date->isTomorrow();
                $isYesterday = $task->task_date->isYesterday();
                @endphp

                <button @click="dateOpen = !dateOpen"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-98
                        {{ $isOverdue 
                        ? 'bg-red-50 dark:bg-red-900/30 border-2 border-red-200 dark:border-red-700 hover:bg-red-100 dark:hover:bg-red-900/40' 
                       : ($isToday 
                           ? 'bg-yellow-50 dark:bg-yellow-900/30 border-2 border-yellow-200 dark:border-yellow-700 hover:bg-yellow-100 dark:hover:bg-yellow-900/40' 
                           : 'bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-600') }}">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm
                            {{ $isOverdue 
                            ? 'bg-red-100 dark:bg-red-900/40' 
                            : ($isToday 
                                ? 'bg-yellow-100 dark:bg-yellow-900/40' 
                                : 'bg-gray-100 dark:bg-gray-700') }}">
                        <x-heroicon-o-calendar-days class="w-6 h-6 
                                {{ $isOverdue 
                                ? 'text-red-600 dark:text-red-400' 
                                : ($isToday 
                                    ? 'text-yellow-600 dark:text-yellow-400' 
                                    : 'text-gray-500 dark:text-gray-400') }}" />
                    </div>
                    <div class="flex-1 text-left">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-0.5">Due Date</p>
                        <p class="text-sm font-bold 
                                {{ $isOverdue 
                                    ? 'text-red-700 dark:text-red-300' 
                                    : ($isToday 
                                        ? 'text-yellow-700 dark:text-yellow-300' 
                                        : 'text-gray-900 dark:text-gray-100') }}">
                            @if($isToday)
                            Today
                            @elseif($isTomorrow)
                            Tomorrow
                            @elseif($isYesterday)
                            Yesterday
                            @else
                            {{ $task->task_date->format('l') }}
                            @endif
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $task->task_date->format('M d, Y') }}
                            @if($isOverdue)
                            <span class="text-red-600 dark:text-red-400 font-semibold">• Overdue!</span>
                            @endif
                        </p>
                    </div>
                    <x-heroicon-o-chevron-right class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" />
                </button>

                {{-- Date Picker Modal - Full Screen Bottom Sheet --}}
                <template x-teleport="body">
                    {{-- Backdrop Overlay --}}
                    <div x-show="dateOpen" x-cloak x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9998]"
                        @click="dateOpen = false">
                    </div>

                    {{-- Bottom Sheet Modal --}}
                    <div x-show="dateOpen" x-cloak @click.away="dateOpen = false"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0"
                        x-transition:leave-end="translate-y-full" class="fixed inset-x-0 bottom-0 bg-white dark:bg-gray-800 rounded-t-3xl shadow-2xl 
                       z-[9999] max-h-[90vh] flex flex-col">

                        {{-- Drag Handle Bar --}}
                        <div class="flex justify-center pt-4 pb-2 flex-shrink-0">
                            <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
                        </div>

                        {{-- Modal Header --}}
                        <div class="px-5 pb-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center">
                                        <x-heroicon-o-calendar-days class="w-5 h-5 text-white" />
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Change Due
                                            Date</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Select a new date for
                                            this task</p>
                                    </div>
                                </div>
                                <button @click="dateOpen = false"
                                    class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors active:scale-95">
                                    <x-heroicon-o-x-mark class="w-6 h-6 text-gray-500 dark:text-gray-400" />
                                </button>
                            </div>
                        </div>

                        {{-- Scrollable Content Area --}}
                        <div class="flex-1 overflow-y-auto overscroll-contain">
                            {{-- Date Picker Form --}}
                            <div class="p-5 bg-gray-50 dark:bg-gray-900/50">
                                {{ $this->dueDateForm }}
                            </div>

                            {{-- Quick Date Options --}}
                            <div class="p-5">
                                <p
                                    class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                                    <x-heroicon-o-bolt class="w-4 h-4" />
                                    Quick Select:
                                </p>
                                <div class="grid grid-cols-2 gap-3">
                                    <button wire:click="updateTaskDate('{{ today()->format('Y-m-d') }}')"
                                        @click="dateOpen = false"
                                        class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all active:scale-95
                                            {{ $isToday 
                                           ? 'bg-primary-50 dark:bg-primary-900/30 border-primary-500 dark:border-primary-400 shadow-md' 
                                           : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-sm' }}">
                                        <div
                                            class="w-12 h-12 rounded-xl flex items-center justify-center mb-2
                                            {{ $isToday ? 'bg-primary-500 dark:bg-primary-600' : 'bg-gray-200 dark:bg-gray-600' }}">
                                            <x-heroicon-o-calendar
                                                class="w-6 h-6 {{ $isToday ? 'text-white' : 'text-gray-500' }}" />
                                        </div>
                                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100">Today</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{
                                            today()->format('M d') }}</span>
                                    </button>

                                    <button wire:click="updateTaskDate('{{ today()->addDay()->format('Y-m-d') }}')"
                                        @click="dateOpen = false"
                                        class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all active:scale-95
                                       {{ $isTomorrow 
                                           ? 'bg-primary-50 dark:bg-primary-900/30 border-primary-500 dark:border-primary-400 shadow-md' 
                                           : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-sm' }}">
                                        <div
                                            class="w-12 h-12 rounded-xl flex items-center justify-center mb-2
                                    {{ $isTomorrow ? 'bg-primary-500 dark:bg-primary-600' : 'bg-gray-200 dark:bg-gray-600' }}">
                                            <x-heroicon-o-calendar-days
                                                class="w-6 h-6 {{ $isTomorrow ? 'text-white' : 'text-gray-500' }}" />
                                        </div>
                                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100">Tomorrow</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{
                                            today()->addDay()->format('M d') }}</span>
                                    </button>

                                    <button wire:click="updateTaskDate('{{ today()->addDays(7)->format('Y-m-d') }}')"
                                        @click="dateOpen = false" class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all active:scale-95
                                       bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 
                                       hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-sm">
                                        <div
                                            class="w-12 h-12 bg-gray-200 dark:bg-gray-600 rounded-xl flex items-center justify-center mb-2">
                                            <x-heroicon-o-arrow-right
                                                class="w-6 h-6 text-gray-500 dark:text-gray-400" />
                                        </div>
                                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100">Next
                                            Week</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{
                                            today()->addDays(7)->format('M d') }}</span>
                                    </button>

                                    <button wire:click="updateTaskDate('{{ today()->addMonth()->format('Y-m-d') }}')"
                                        @click="dateOpen = false" class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all active:scale-95
                                       bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 
                                       hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-sm">
                                        <div
                                            class="w-12 h-12 bg-gray-200 dark:bg-gray-600 rounded-xl flex items-center justify-center mb-2">
                                            <x-heroicon-o-forward class="w-6 h-6 text-gray-500 dark:text-gray-400" />
                                        </div>
                                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100">Next
                                            Month</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{
                                            today()->addMonth()->format('M d') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
    </div>
</div>