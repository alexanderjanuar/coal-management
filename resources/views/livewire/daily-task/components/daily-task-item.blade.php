{{-- Daily Task Item — Refined Cyan Edition --}}
<div class="w-full" style="font-family:'DM Sans',system-ui,sans-serif">

    {{-- ── Desktop Table Row (lg+) ── --}}
    <div class="hidden lg:block w-full">
        <div class="grid grid-cols-12 gap-2 xl:gap-4 items-center min-h-[52px] px-4 py-2.5
                    border-l-2 border-l-transparent
                    hover:border-l-cyan-500 dark:hover:border-l-cyan-400
                    hover:bg-cyan-50/40 dark:hover:bg-cyan-900/10
                    transition-all duration-150 group"
             :class="selectedTasks && selectedTasks.includes({{ $task->id }}) ? 'bg-cyan-50 dark:bg-cyan-900/20 border-l-cyan-500 dark:border-l-cyan-400' : ''">

            {{-- 1. Completion toggle (1/12) --}}
            <div class="col-span-1 flex items-center justify-center">
                <button wire:click="toggleTaskCompletion"
                        class="flex-shrink-0 w-5 h-5 flex items-center justify-center transition-transform duration-200 hover:scale-110 group/btn">
                    @if($task->status === 'completed')
                        <x-heroicon-s-check-circle class="w-5 h-5 text-emerald-500 dark:text-emerald-400" />
                    @else
                        <div class="w-5 h-5 rounded-full border-2 border-gray-300 dark:border-gray-600
                                    group-hover/btn:border-cyan-500 dark:group-hover/btn:border-cyan-400
                                    group-hover/btn:bg-cyan-50 dark:group-hover/btn:bg-cyan-900/20
                                    flex items-center justify-center transition-all duration-150">
                        </div>
                    @endif
                </button>
            </div>

            {{-- 2. Task title + description (4/12) --}}
            <div class="col-span-4 min-w-0">
                <div class="cursor-pointer" wire:click="viewDetails">
                    <p class="text-[.855rem] font-semibold leading-snug truncate transition-colors duration-150
                               text-gray-800 dark:text-gray-100
                               group-hover:text-cyan-700 dark:group-hover:text-cyan-300
                               {{ $task->status === 'completed' ? 'line-through text-gray-400 dark:text-gray-500' : '' }}">
                        {{ Str::limit(strip_tags($task->title), 60) }}
                    </p>
                    @if($task->description)
                        <p class="text-[.75rem] text-gray-400 dark:text-gray-500 truncate mt-0.5">
                            {{ Str::limit(strip_tags($task->description), 55) }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- 3. Status (2/12) --}}
            <div class="col-span-2 min-w-0">
                <div class="relative w-full" x-data="{ statusOpen: false, buttonRect: {} }">
                    <button @click="statusOpen = !statusOpen; buttonRect = $el.getBoundingClientRect()"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 h-7 rounded-md text-[.74rem] font-medium
                                   w-full justify-center border transition-all duration-150
                                   {{ match($task->status) {
                                       'completed'   => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                       'in_progress' => 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 border-yellow-200 dark:border-yellow-800',
                                       'cancelled'   => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border-red-200 dark:border-red-800',
                                       default       => 'bg-gray-100 dark:bg-white/[.06] text-gray-600 dark:text-gray-400 border-black/[.08] dark:border-white/[.08]',
                                   } }}">
                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ match($task->status) {
                            'completed'   => 'bg-emerald-500',
                            'in_progress' => 'bg-yellow-500 animate-pulse',
                            'cancelled'   => 'bg-red-500',
                            default       => 'bg-gray-400',
                        } }}"></span>
                        <span class="truncate">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                        <x-heroicon-o-chevron-down class="w-3 h-3 flex-shrink-0 opacity-50" x-bind:class="{ 'rotate-180': statusOpen }" />
                    </button>

                    <template x-teleport="body">
                        <div x-show="statusOpen" x-cloak @click.away="statusOpen = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="fixed w-44 bg-white dark:bg-[#1a1a18] rounded-xl shadow-xl border border-black/[.09] dark:border-white/[.10] py-1.5 z-[9999]"
                             x-bind:style="{
                                 top: Math.min(buttonRect.bottom + window.scrollY + 6, window.innerHeight + window.scrollY - 260) + 'px',
                                 left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 176 - 8)) + 'px'
                             }">
                            @foreach($this->getStatusOptions() as $sv => $sl)
                            <button wire:click="updateStatus('{{ $sv }}')" @click="statusOpen = false"
                                    class="w-full text-left flex items-center gap-2.5 px-3.5 py-2.5 text-[.82rem]
                                           text-gray-600 dark:text-gray-300 transition-colors duration-100
                                           hover:bg-gray-50 dark:hover:bg-white/[.05] hover:text-gray-900 dark:hover:text-white
                                           {{ $task->status === $sv ? 'bg-cyan-50 dark:bg-cyan-900/20 text-cyan-700 dark:text-cyan-300 border-l-2 border-l-cyan-500' : '' }}">
                                <span class="w-2 h-2 rounded-full flex-shrink-0 {{ match($sv) {
                                    'completed'   => 'bg-emerald-500',
                                    'in_progress' => 'bg-yellow-500',
                                    'cancelled'   => 'bg-red-500',
                                    default       => 'bg-gray-400',
                                } }}"></span>
                                <span class="font-medium">{{ $sl }}</span>
                                @if($task->status === $sv)
                                    <x-heroicon-s-check class="w-3.5 h-3.5 text-cyan-600 dark:text-cyan-400 ml-auto" />
                                @endif
                            </button>
                            @endforeach
                        </div>
                    </template>
                </div>
            </div>

            {{-- 4. Priority (1/12) --}}
            <div class="col-span-1 min-w-0">
                <div class="relative" x-data="{ priorityOpen: false, buttonRect: {} }">
                    <button @click="priorityOpen = !priorityOpen; buttonRect = $el.getBoundingClientRect()"
                            class="inline-flex items-center gap-1 px-2 py-1.5 h-7 rounded-md text-[.74rem] font-bold
                                   w-full justify-center border transition-all duration-150
                                   {{ match($task->priority) {
                                       'urgent' => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border-red-200 dark:border-red-800',
                                       'high'   => 'bg-cyan-50 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800',
                                       'normal' => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                       'low'    => 'bg-gray-100 dark:bg-white/[.06] text-gray-500 dark:text-gray-400 border-black/[.08] dark:border-white/[.07]',
                                       default  => 'bg-gray-100 dark:bg-white/[.06] text-gray-500 dark:text-gray-400 border-black/[.08] dark:border-white/[.07]',
                                   } }}">
                        @php $priorityIcon = match($task->priority) {
                            'urgent' => 'heroicon-s-exclamation-triangle',
                            'high'   => 'heroicon-o-exclamation-triangle',
                            'low'    => 'heroicon-o-arrow-down',
                            default  => 'heroicon-o-minus',
                        }; @endphp
                        <x-dynamic-component :component="$priorityIcon" class="w-3 h-3 flex-shrink-0" />
                        <span class="hidden 2xl:inline truncate">{{ ucfirst($task->priority) }}</span>
                        <x-heroicon-o-chevron-down class="w-3 h-3 flex-shrink-0 opacity-50" x-bind:class="{ 'rotate-180': priorityOpen }" />
                    </button>

                    <template x-teleport="body">
                        <div x-show="priorityOpen" x-cloak @click.away="priorityOpen = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="fixed w-44 bg-white dark:bg-[#1a1a18] rounded-xl shadow-xl border border-black/[.09] dark:border-white/[.10] py-1.5 z-[9999]"
                             x-bind:style="{
                                 top: Math.min(buttonRect.bottom + window.scrollY + 6, window.innerHeight + window.scrollY - 220) + 'px',
                                 left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 176 - 8)) + 'px'
                             }">
                            @foreach($this->getPriorityOptions() as $pv => $pl)
                            <button wire:click="updatePriority('{{ $pv }}')" @click="priorityOpen = false"
                                    class="w-full text-left flex items-center gap-2.5 px-3.5 py-2.5 text-[.82rem]
                                           text-gray-600 dark:text-gray-300 transition-colors duration-100
                                           hover:bg-gray-50 dark:hover:bg-white/[.05] hover:text-gray-900 dark:hover:text-white
                                           {{ $task->priority === $pv ? 'bg-cyan-50 dark:bg-cyan-900/20 text-cyan-700 dark:text-cyan-300 border-l-2 border-l-cyan-500' : '' }}">
                                @php $icon = match($pv) { 'urgent' => 'heroicon-s-exclamation-triangle', 'high' => 'heroicon-o-exclamation-triangle', 'low' => 'heroicon-o-arrow-down', default => 'heroicon-o-minus' }; @endphp
                                <x-dynamic-component :component="$icon" class="w-4 h-4 flex-shrink-0" />
                                <span class="font-medium">{{ $pl }}</span>
                                @if($task->priority === $pv)
                                    <x-heroicon-s-check class="w-3.5 h-3.5 text-cyan-600 dark:text-cyan-400 ml-auto" />
                                @endif
                            </button>
                            @endforeach
                        </div>
                    </template>
                </div>
            </div>

            {{-- 5. Assignees (2/12) --}}
            <div class="col-span-2 min-w-0">
                <div class="relative w-full" x-data="{ assigneeOpen: false, buttonRect: {} }">
                    <button @click="assigneeOpen = !assigneeOpen; buttonRect = $el.getBoundingClientRect()"
                            class="w-full flex items-center gap-2 px-2 py-1.5 h-7 rounded-md border
                                   border-black/[.08] dark:border-white/[.08]
                                   bg-white dark:bg-transparent
                                   hover:border-cyan-400 dark:hover:border-cyan-600
                                   hover:bg-cyan-50/50 dark:hover:bg-cyan-900/10
                                   transition-all duration-150">
                        @if($task->assignedUsers && $task->assignedUsers->count() > 0)
                            <div class="flex -space-x-1 flex-shrink-0">
                                @foreach($task->assignedUsers->take(2) as $user)
                                    <div class="w-5 h-5 rounded-full bg-cyan-600 dark:bg-cyan-500 text-white
                                                flex items-center justify-center text-[.62rem] font-bold
                                                border border-white dark:border-gray-900"
                                         title="{{ $user->name }}">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endforeach
                                @if($task->assignedUsers->count() > 2)
                                    <div class="w-5 h-5 rounded-full bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200
                                                flex items-center justify-center text-[.62rem] font-bold
                                                border border-white dark:border-gray-900">
                                        +{{ $task->assignedUsers->count() - 2 }}
                                    </div>
                                @endif
                            </div>
                            <span class="hidden xl:inline text-[.75rem] text-gray-600 dark:text-gray-300 truncate">
                                {{ $task->assignedUsers->count() === 1 ? Str::limit($task->assignedUsers->first()->name, 12) : $task->assignedUsers->count() . ' orang' }}
                            </span>
                        @else
                            <div class="w-5 h-5 rounded-full bg-gray-100 dark:bg-white/[.06] border border-dashed border-gray-300 dark:border-gray-600
                                        flex items-center justify-center flex-shrink-0">
                                <x-heroicon-o-plus class="w-2.5 h-2.5 text-gray-400" />
                            </div>
                            <span class="hidden lg:inline text-[.75rem] text-gray-400 dark:text-gray-500 truncate">Unassigned</span>
                        @endif
                        <x-heroicon-o-chevron-down class="w-3 h-3 text-gray-400 flex-shrink-0 ml-auto" x-bind:class="{ 'rotate-180': assigneeOpen }" />
                    </button>

                    <template x-teleport="body">
                        <div x-show="assigneeOpen" x-cloak @click.away="assigneeOpen = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="fixed w-60 bg-white dark:bg-[#1a1a18] rounded-xl shadow-xl border border-black/[.09] dark:border-white/[.10] z-[9999] max-h-72 overflow-y-auto"
                             x-bind:style="{
                                 top: Math.min(buttonRect.bottom + window.scrollY + 6, window.innerHeight + window.scrollY - 300) + 'px',
                                 left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 240 - 8)) + 'px'
                             }">
                            <div class="px-3.5 py-2 text-[.7rem] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 border-b border-black/[.07] dark:border-white/[.07] sticky top-0 bg-white dark:bg-[#1a1a18]">
                                Assign Users
                            </div>
                            @foreach($this->getUserOptions() as $userId => $userName)
                            @php $isAssigned = $task->assignedUsers->contains($userId); @endphp
                            <button wire:click="{{ $isAssigned ? 'unassignUser' : 'assignUser' }}({{ $userId }})"
                                    @click="assigneeOpen = false"
                                    class="w-full text-left flex items-center gap-3 px-3.5 py-2.5 text-[.82rem]
                                           text-gray-600 dark:text-gray-300
                                           hover:bg-gray-50 dark:hover:bg-white/[.04] transition-colors">
                                <div class="w-7 h-7 rounded-full bg-cyan-600 dark:bg-cyan-500 text-white
                                            flex items-center justify-center text-[.7rem] font-bold flex-shrink-0">
                                    {{ strtoupper(substr($userName, 0, 1)) }}
                                </div>
                                <span class="font-medium flex-1 truncate">{{ $userName }}</span>
                                @if($isAssigned)
                                    <x-heroicon-s-check class="w-4 h-4 text-emerald-500 flex-shrink-0" />
                                @endif
                            </button>
                            @endforeach
                        </div>
                    </template>
                </div>
            </div>

            {{-- 6. Project (1/12) --}}
            <div class="col-span-1 min-w-0">
                <div class="relative" x-data="{ projectOpen: false, buttonRect: {} }">
                    <button @click="projectOpen = !projectOpen; buttonRect = $el.getBoundingClientRect()"
                            class="flex items-center gap-1 px-2 py-1.5 h-7 rounded-md text-[.74rem] font-medium
                                   w-full justify-center border transition-all duration-150
                                   {{ $task->project
                                       ? 'bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-300 border-violet-200 dark:border-violet-800'
                                       : 'bg-gray-100 dark:bg-white/[.05] border-dashed border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500' }}">
                        @if($task->project)
                            <x-heroicon-o-folder class="w-3 h-3 flex-shrink-0" />
                            <span class="hidden 2xl:inline truncate" title="{{ $task->project->name }}">{{ Str::limit($task->project->name, 8) }}</span>
                        @else
                            <x-heroicon-o-folder-plus class="w-3 h-3 flex-shrink-0" />
                            <span class="hidden 2xl:inline">Add</span>
                        @endif
                        <x-heroicon-o-chevron-down class="w-3 h-3 flex-shrink-0 opacity-50" x-bind:class="{ 'rotate-180': projectOpen }" />
                    </button>

                    <template x-teleport="body">
                        <div x-show="projectOpen" x-cloak @click.away="projectOpen = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="fixed w-72 bg-white dark:bg-[#1a1a18] rounded-xl shadow-xl border border-black/[.09] dark:border-white/[.10] z-[9999] flex flex-col max-h-[85vh]"
                             x-bind:style="{
                                 top: Math.min(buttonRect.bottom + window.scrollY + 6, window.innerHeight + window.scrollY - 420) + 'px',
                                 left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 288 - 8)) + 'px'
                             }">

                            {{-- Header --}}
                            <div class="flex items-center justify-between px-4 py-3 border-b border-black/[.07] dark:border-white/[.07] flex-shrink-0">
                                <h3 class="text-[.82rem] font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2">
                                    <x-heroicon-o-building-office class="w-4 h-4" /> Select Project
                                </h3>
                                <button wire:click="redirectToCreateProject" @click="projectOpen = false"
                                        class="flex items-center gap-1 px-2.5 py-1.5 text-[.76rem] font-medium
                                               bg-cyan-600 dark:bg-cyan-500 text-white rounded-lg
                                               hover:opacity-90 transition-opacity">
                                    <x-heroicon-o-plus class="w-3 h-3" /> Create
                                </button>
                            </div>

                            <div class="overflow-y-auto flex-1">
                                {{-- Remove --}}
                                <button wire:click="updateProject(null)" @click="projectOpen = false"
                                        class="w-full text-left flex items-center gap-3 px-4 py-2.5 text-[.82rem]
                                               text-gray-600 dark:text-gray-300
                                               hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400
                                               border-b border-black/[.06] dark:border-white/[.06] transition-colors">
                                    <x-heroicon-o-minus-circle class="w-4 h-4 text-red-500 flex-shrink-0" />
                                    <span class="font-medium">Remove Project</span>
                                </button>

                                {{-- Client filter --}}
                                <div class="px-4 py-3 bg-gray-50 dark:bg-white/[.02] border-b border-black/[.06] dark:border-white/[.06]">
                                    <label class="block text-[.73rem] font-medium text-gray-500 dark:text-gray-400 mb-1.5">Filter by Client</label>
                                    <select wire:model.live="selectedClientId"
                                            class="w-full px-3 py-2 text-[.82rem] border border-black/[.09] dark:border-white/[.09]
                                                   rounded-lg bg-white dark:bg-[#111110] text-gray-800 dark:text-gray-200
                                                   focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none">
                                        <option value="">-- Semua Client --</option>
                                        @foreach($this->getClientOptions() as $clientId => $clientName)
                                            <option value="{{ $clientId }}">{{ $clientName }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Projects list --}}
                                @if($selectedClientId)
                                    @php $projects = $this->getProjectOptions(); @endphp
                                    @if(!empty($projects))
                                        <div class="py-1">
                                            <div class="px-4 py-2 text-[.7rem] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                                Projects ({{ count($projects) }})
                                            </div>
                                            @foreach($projects as $projectId => $projectName)
                                            <button wire:click="updateProject({{ $projectId }})" @click="projectOpen = false"
                                                    class="w-full text-left flex items-center gap-3 px-4 py-2.5 text-[.82rem]
                                                           text-gray-700 dark:text-gray-300 transition-colors
                                                           hover:bg-gray-50 dark:hover:bg-white/[.03]
                                                           {{ $task->project_id == $projectId ? 'bg-cyan-50 dark:bg-cyan-900/20 text-cyan-700 dark:text-cyan-300 border-l-2 border-l-cyan-500' : '' }}">
                                                <x-heroicon-o-folder class="w-4 h-4 text-violet-500 flex-shrink-0" />
                                                <div class="flex-1 min-w-0">
                                                    <div class="font-medium truncate">{{ $projectName }}</div>
                                                    <div class="text-[.73rem] text-gray-400 truncate">{{ $this->getClientOptions()[$selectedClientId] ?? '' }}</div>
                                                </div>
                                                @if($task->project_id == $projectId)
                                                    <x-heroicon-s-check class="w-3.5 h-3.5 text-cyan-600 dark:text-cyan-400 flex-shrink-0" />
                                                @endif
                                            </button>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">
                                            <x-heroicon-o-folder-open class="w-10 h-10 mx-auto mb-2 opacity-40" />
                                            <p class="text-[.82rem] font-medium">No projects for this client</p>
                                            <button wire:click="redirectToCreateProject" @click="projectOpen = false"
                                                    class="mt-3 inline-flex items-center gap-1.5 px-3 py-2 text-[.78rem] font-medium
                                                           bg-cyan-600 dark:bg-cyan-500 text-white rounded-lg hover:opacity-90 transition-opacity">
                                                <x-heroicon-o-plus class="w-3.5 h-3.5" /> Create Project
                                            </button>
                                        </div>
                                    @endif
                                @else
                                    <div class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">
                                        <x-heroicon-o-building-office class="w-8 h-8 mx-auto mb-2 opacity-40" />
                                        <p class="text-[.82rem] font-medium">Select client first</p>
                                        <p class="text-[.76rem] mt-0.5">to view projects</p>
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
                        $isOverdue  = $task->task_date->isPast() && $task->status !== 'completed';
                        $isToday    = $task->task_date->isToday();
                        $isTomorrow = $task->task_date->isTomorrow();
                    @endphp

                    <button @click="dateOpen = !dateOpen; buttonRect = $el.getBoundingClientRect()"
                            class="flex items-center gap-1 px-2 py-1.5 h-7 rounded-md text-[.74rem] font-medium
                                   w-full justify-center border transition-all duration-150
                                   {{ $isOverdue
                                       ? 'bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 border-red-200 dark:border-red-800'
                                       : ($isToday
                                           ? 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800'
                                           : 'bg-gray-100 dark:bg-white/[.05] text-gray-600 dark:text-gray-400 border-black/[.08] dark:border-white/[.07]') }}">
                        <x-heroicon-o-calendar-days class="w-3 h-3 flex-shrink-0" />
                        <span class="font-medium truncate">
                            @if($isToday) Today
                            @elseif($isTomorrow) Tom
                            @else {{ $task->task_date->format('M d') }}
                            @endif
                        </span>
                    </button>

                    <template x-teleport="body">
                        <div x-show="dateOpen" x-cloak @click.away="dateOpen = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="fixed w-72 bg-white dark:bg-[#1a1a18] rounded-xl shadow-xl border border-black/[.09] dark:border-white/[.10] z-[9999] flex flex-col max-h-[90vh]"
                             x-bind:style="{
                                 top: Math.min(buttonRect.bottom + window.scrollY + 6, window.innerHeight + window.scrollY - 420) + 'px',
                                 left: Math.max(8, Math.min(buttonRect.left + window.scrollX, window.innerWidth - 288 - 8)) + 'px'
                             }">
                            <div class="flex items-center gap-2 px-4 py-3 border-b border-black/[.07] dark:border-white/[.07] flex-shrink-0">
                                <x-heroicon-o-calendar-days class="w-4 h-4 text-gray-400" />
                                <h3 class="text-[.82rem] font-semibold text-gray-700 dark:text-gray-200">Edit Due Date</h3>
                            </div>
                            <div class="p-4 overflow-y-auto flex-1">
                                {{ $this->dueDateForm }}
                            </div>
                            <div class="px-4 pb-4 pt-2 border-t border-black/[.06] dark:border-white/[.06] flex-shrink-0">
                                <p class="text-[.7rem] font-semibold uppercase tracking-wider text-gray-400 mb-2">Quick Options</p>
                                <div class="grid grid-cols-2 gap-1.5">
                                    @foreach([['Today', today()->format('Y-m-d')], ['Tomorrow', today()->addDay()->format('Y-m-d')], ['Next Week', today()->addDays(7)->format('Y-m-d')], ['Next Month', today()->addMonth()->format('Y-m-d')]] as [$label, $date])
                                    <button wire:click="updateTaskDate('{{ $date }}')" @click="dateOpen = false"
                                            class="px-3 py-2 text-[.76rem] font-medium text-gray-600 dark:text-gray-300
                                                   bg-gray-100 dark:bg-white/[.05] hover:bg-cyan-50 dark:hover:bg-cyan-900/20
                                                   hover:text-cyan-700 dark:hover:text-cyan-300
                                                   rounded-lg transition-colors border border-black/[.07] dark:border-white/[.07]">
                                        {{ $label }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>{{-- end grid row --}}
    </div>{{-- end desktop --}}

    {{-- Tablet View --}}
    @include('components.daily-task.list.daily-task-md-task', ['task' => $task])

    {{-- Mobile View --}}
    @include('components.daily-task.list.daily-task-xs-task', ['task' => $task])

</div>