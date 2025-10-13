<div class="bg-white dark:bg-gray-800 rounded-3xl border-2 border-gray-200 dark:border-gray-700 p-4 sm:p-6 hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-xl transition-all duration-300 group"
    wire:key="task-{{ $task->id }}">

    {{-- Mobile Layout (< sm) --}} <div class="flex sm:hidden flex-col gap-3">
        {{-- Title Section --}}
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
                <h3
                    class="text-sm font-bold text-gray-900 dark:text-white mb-1 line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                    {{ $task->title }}
                </h3>
                @if($task->project)
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    {{ $task->project->client?->name ?? $task->project->name }}
                </p>
                @endif
            </div>

            {{-- Mobile Actions --}}
            <div class="relative shrink-0" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false" type="button"
                    class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all">
                    <x-heroicon-o-ellipsis-horizontal class="w-5 h-5" />
                </button>

                {{-- Mobile Actions Dropdown --}}
                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border-2 border-gray-200 dark:border-gray-700 z-30 overflow-hidden">
                    <div class="py-2">
                        <button wire:click="$dispatch('openTaskDetailModal', { taskId: {{ $task->id }} })"
                            @click="open = false" type="button"
                            class="w-full text-left px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors hover:pl-5">
                            <x-heroicon-o-eye class="w-5 h-5" />
                            Lihat Detail
                        </button>
                        <button type="button"
                            class="w-full text-left px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3 transition-all">
                            <x-heroicon-o-pencil class="w-5 h-5" />
                            Edit Tugas
                        </button>
                        <button type="button"
                            class="w-full text-left px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3 transition-all">
                            <x-heroicon-o-share class="w-5 h-5" />
                            Bagikan
                        </button>
                        <div class="border-t-2 border-gray-200 dark:border-gray-700 my-2"></div>
                        <button type="button"
                            class="w-full text-left px-4 py-2.5 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-3 transition-all">
                            <x-heroicon-o-trash class="w-5 h-5" />
                            Hapus Tugas
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile Metadata Row 1: Status & Priority --}}
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Status Badge --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false" type="button"
                    class="px-3 py-1 rounded-lg text-xs font-bold transition-all duration-200 {{ $statusOptions[$task->status]['color'] }} shadow-sm border border-transparent">
                    {{ $statusOptions[$task->status]['label'] }}
                </button>

                {{-- Status Dropdown (same as desktop) --}}
                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="transform opacity-0 scale-95 -translate-y-2"
                    x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="transform opacity-0 scale-95 -translate-y-2"
                    class="absolute left-0 mt-3 w-64 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border-2 border-gray-200 dark:border-gray-700 z-30 overflow-hidden"
                    style="filter: drop-shadow(0 20px 25px rgb(0 0 0 / 0.15));">

                    <div
                        class="px-4 py-3 bg-gradient-to-r from-primary-50 to-primary-100 dark:from-gray-700 dark:to-gray-750 border-b border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-2">
                            <x-heroicon-s-arrows-right-left class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                            <h3 class="text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                Ubah Status Tugas
                            </h3>
                        </div>
                    </div>

                    <div class="p-2 max-h-80 overflow-y-auto">
                        @foreach($statusOptions as $statusKey => $statusOption)
                        <button wire:click="changeTaskStatus({{ $task->id }}, '{{ $statusKey }}')" @click="open = false"
                            type="button" class="group w-full text-left px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 
                                {{ $statusOption['color'] }} 
                                {{ $task->status === $statusKey 
                                    ? 'ring-2 ring-primary-500 ring-offset-2 dark:ring-offset-gray-800 scale-[1.02]' 
                                    : 'hover:scale-[1.02] hover:shadow-md' 
                                }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    @if($statusKey === 'pending')
                                    <x-heroicon-s-clock class="w-4 h-4" />
                                    @elseif($statusKey === 'in_progress')
                                    <x-heroicon-s-arrow-path class="w-4 h-4 group-hover:animate-spin" />
                                    @elseif($statusKey === 'completed')
                                    <x-heroicon-s-check-circle class="w-4 h-4" />
                                    @elseif($statusKey === 'cancelled')
                                    <x-heroicon-s-x-circle class="w-4 h-4" />
                                    @endif
                                    <span>{{ $statusOption['label'] }}</span>
                                </div>
                                @if($task->status === $statusKey)
                                <div class="flex items-center gap-1">
                                    <span class="text-[10px] font-bold opacity-75">Aktif</span>
                                    <x-heroicon-s-check class="w-5 h-5" />
                                </div>
                                @endif
                            </div>
                        </button>
                        @endforeach
                    </div>

                    <div class="px-4 py-2 bg-gray-50 dark:bg-gray-750 border-t border-gray-200 dark:border-gray-600">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 flex items-center gap-1">
                            <x-heroicon-s-information-circle class="w-3 h-3" />
                            Klik untuk mengubah status
                        </p>
                    </div>
                </div>
            </div>

            {{-- Priority Badge --}}
            @php
            $priorityConfig = [
            'urgent' => [
            'label' => 'Mendesak',
            'color' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
            'icon' => 'heroicon-s-exclamation-circle'
            ],
            'high' => [
            'label' => 'Tinggi',
            'color' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
            'icon' => 'heroicon-s-arrow-up-circle'
            ],
            'normal' => [
            'label' => 'Normal',
            'color' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
            'icon' => 'heroicon-s-minus-circle'
            ],
            'low' => [
            'label' => 'Rendah',
            'color' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
            'icon' => 'heroicon-s-arrow-down-circle'
            ],
            ];
            $currentPriority = $priorityConfig[$task->priority] ?? $priorityConfig['normal'];
            @endphp
            <div class="flex items-center gap-1 px-2.5 py-1 {{ $currentPriority['color'] }} rounded-lg shadow-sm">
                @svg($currentPriority['icon'], 'w-3.5 h-3.5')
                <span class="text-xs font-bold">
                    {{ $currentPriority['label'] }}
                </span>
            </div>

            {{-- Due Date --}}
            @php
            $now = now();
            $dueDate = $task->task_date;
            $diffInDays = $now->startOfDay()->diffInDays($dueDate->startOfDay(), false);

            if ($diffInDays < 0) { $daysText=abs($diffInDays)===1 ? 'Terlambat 1 hari' : 'Terlambat ' . abs($diffInDays)
                . ' hari' ; $borderColor='border-red-300 dark:border-red-700' ;
                $textColor='text-red-600 dark:text-red-400' ; $iconColor='text-red-500 dark:text-red-400' ; } elseif
                ($diffInDays===0) { $daysText='Hari ini' ; $borderColor='border-orange-300 dark:border-orange-700' ;
                $textColor='text-orange-600 dark:text-orange-400' ; $iconColor='text-orange-500 dark:text-orange-400' ;
                } elseif ($diffInDays===1) { $daysText='Besok' ; $borderColor='border-yellow-300 dark:border-yellow-700'
                ; $textColor='text-yellow-600 dark:text-yellow-400' ; $iconColor='text-yellow-500 dark:text-yellow-400'
                ; } elseif ($diffInDays <=3) { $daysText=$diffInDays . ' hari lagi' ;
                $borderColor='border-blue-300 dark:border-blue-700' ; $textColor='text-blue-600 dark:text-blue-400' ;
                $iconColor='text-blue-500 dark:text-blue-400' ; } elseif ($diffInDays <=7) { $daysText=$diffInDays
                . ' hari lagi' ; $borderColor='border-green-300 dark:border-green-700' ;
                $textColor='text-green-600 dark:text-green-400' ; $iconColor='text-green-500 dark:text-green-400' ; }
                else { $daysText=$dueDate->format('d M Y');
                $borderColor = 'border-gray-300 dark:border-gray-600';
                $textColor = 'text-gray-600 dark:text-gray-400';
                $iconColor = 'text-gray-500 dark:text-gray-400';
                }
                @endphp
                <div class="flex items-center gap-1.5 px-2.5 py-1 border {{ $borderColor }} rounded-full">
                    <x-heroicon-o-clock class="w-4 h-4 {{ $iconColor }}" />
                    <span class="text-xs font-semibold {{ $textColor }} whitespace-nowrap">
                        {{ $daysText }}
                    </span>
                </div>
        </div>

        {{-- Mobile Metadata Row 2: Users, Comments, Progress --}}
        <div class="flex items-center gap-3 flex-wrap">
            {{-- Assigned Users --}}
            @if($task->assignedUsers->count() > 0)
            <div class="flex items-center gap-1.5">
                <x-heroicon-s-user-group class="w-4 h-4 text-gray-400" />
                <div class="flex -space-x-1.5">
                    @foreach($task->assignedUsers->take(3) as $user)
                    <div class="inline-block h-6 w-6 rounded-full ring-2 ring-white dark:ring-gray-800"
                        title="{{ $user->name }}">
                        @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                            class="h-6 w-6 rounded-full object-cover">
                        @else
                        <div
                            class="h-6 w-6 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-[10px] font-bold">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        @endif
                    </div>
                    @endforeach

                    @if($task->assignedUsers->count() > 3)
                    <div
                        class="inline-block h-6 w-6 rounded-full ring-2 ring-white dark:ring-gray-800 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                        <span class="text-[10px] font-bold text-gray-600 dark:text-gray-400">
                            +{{ $task->assignedUsers->count() - 3 }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Comments --}}
            @if($task->comments_count ?? 0 > 0)
            <div class="flex items-center gap-1 text-gray-500 dark:text-gray-400">
                <x-heroicon-s-chat-bubble-left class="w-4 h-4" />
                <span class="text-xs font-semibold">{{ $task->comments_count }}</span>
            </div>
            @endif

            {{-- Progress --}}
            @if($task->subtasks->count() > 0)
            @php
            $completedSubtasks = $task->subtasks->where('status', 'completed')->count();
            $totalSubtasks = $task->subtasks->count();
            $progressPercentage = round(($completedSubtasks / $totalSubtasks) * 100);
            @endphp
            <div class="flex items-center gap-2 px-2.5 py-1 bg-gray-100 dark:bg-gray-700 rounded-lg shadow-sm">
                <div class="relative w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full overflow-hidden">
                    <div class="absolute top-0 left-0 h-full transition-all duration-500 {{ $progressPercentage == 100 ? 'bg-green-500' : 'bg-primary-500' }}"
                        style="width: {{ $progressPercentage }}%"></div>
                </div>
                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">
                    {{ $progressPercentage }}%
                </span>
            </div>
            @endif
        </div>
</div>

{{-- Desktop Layout (>= sm) --}}
<div class="hidden sm:flex items-center justify-between gap-4">
    {{-- Left: Title --}}
    <div class="flex-1 min-w-0">
        <h3
            class="text-base font-bold text-gray-900 dark:text-white mb-1 truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
            {{ $task->title }}
        </h3>
        @if($task->project)
        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
            {{ $task->project->client?->name ?? $task->project->name }}
        </p>
        @endif
    </div>

    {{-- Right: Metadata --}}
    <div class="flex items-center gap-2 lg:gap-3 shrink-0 flex-wrap lg:flex-nowrap">
        {{-- Assigned Users --}}
        @if($task->assignedUsers->count() > 0)
        <div class="flex items-center gap-1.5">
            <x-heroicon-s-user-group class="w-4 h-4 text-gray-400" />
            <div class="flex -space-x-1.5">
                @foreach($task->assignedUsers->take(3) as $user)
                <div class="inline-block h-6 w-6 rounded-full ring-2 ring-white dark:ring-gray-800"
                    title="{{ $user->name }}">
                    @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                        class="h-6 w-6 rounded-full object-cover">
                    @else
                    <div
                        class="h-6 w-6 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-[10px] font-bold">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    @endif
                </div>
                @endforeach

                @if($task->assignedUsers->count() > 3)
                <div
                    class="inline-block h-6 w-6 rounded-full ring-2 ring-white dark:ring-gray-800 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                    <span class="text-[10px] font-bold text-gray-600 dark:text-gray-400">
                        +{{ $task->assignedUsers->count() - 3 }}
                    </span>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Comments Count --}}
        @if($task->comments_count ?? 0 > 0)
        <div class="flex items-center gap-1 text-gray-500 dark:text-gray-400">
            <x-heroicon-s-chat-bubble-left class="w-4 h-4" />
            <span class="text-xs font-semibold">{{ $task->comments_count }}</span>
        </div>
        @endif

        {{-- Status Badge --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" @click.away="open = false" type="button"
                class="px-3 py-1 rounded-lg text-xs font-bold transition-all duration-200 {{ $statusOptions[$task->status]['color'] }} shadow-sm border border-transparent hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md">
                {{ $statusOptions[$task->status]['label'] }}
            </button>

            {{-- Enhanced Status Dropdown Menu (same as mobile) --}}
            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="transform opacity-0 scale-95 -translate-y-2"
                x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="transform opacity-0 scale-95 -translate-y-2"
                class="absolute right-0 mt-3 w-64 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border-2 border-gray-200 dark:border-gray-700 z-30 overflow-hidden"
                style="filter: drop-shadow(0 20px 25px rgb(0 0 0 / 0.15));">

                <div
                    class="px-4 py-3 bg-gradient-to-r from-primary-50 to-primary-100 dark:from-gray-700 dark:to-gray-750 border-b border-gray-200 dark:border-gray-600">
                    <div class="flex items-center gap-2">
                        <x-heroicon-s-arrows-right-left class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                        <h3 class="text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                            Ubah Status Tugas
                        </h3>
                    </div>
                </div>

                <div class="p-2 max-h-80 overflow-y-auto">
                    @foreach($statusOptions as $statusKey => $statusOption)
                    <button wire:click="changeTaskStatus({{ $task->id }}, '{{ $statusKey }}')" @click="open = false"
                        type="button" class="group w-full text-left px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 
                                {{ $statusOption['color'] }} 
                                {{ $task->status === $statusKey 
                                    ? 'ring-2 ring-primary-500 ring-offset-2 dark:ring-offset-gray-800 scale-[1.02]' 
                                    : 'hover:scale-[1.02] hover:shadow-md' 
                                }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                @if($statusKey === 'pending')
                                <x-heroicon-s-clock class="w-4 h-4" />
                                @elseif($statusKey === 'in_progress')
                                <x-heroicon-s-arrow-path class="w-4 h-4 group-hover:animate-spin" />
                                @elseif($statusKey === 'completed')
                                <x-heroicon-s-check-circle class="w-4 h-4" />
                                @elseif($statusKey === 'cancelled')
                                <x-heroicon-s-x-circle class="w-4 h-4" />
                                @endif
                                <span>{{ $statusOption['label'] }}</span>
                            </div>
                            @if($task->status === $statusKey)
                            <div class="flex items-center gap-1">
                                <span class="text-[10px] font-bold opacity-75">Aktif</span>
                                <x-heroicon-s-check class="w-5 h-5" />
                            </div>
                            @endif
                        </div>
                    </button>
                    @endforeach
                </div>

                <div class="px-4 py-2 bg-gray-50 dark:bg-gray-750 border-t border-gray-200 dark:border-gray-600">
                    <p class="text-[10px] text-gray-500 dark:text-gray-400 flex items-center gap-1">
                        <x-heroicon-s-information-circle class="w-3 h-3" />
                        Klik untuk mengubah status
                    </p>
                </div>
            </div>
        </div>

        {{-- Priority Badge --}}
        <div class="flex items-center gap-1 px-2.5 py-1 {{ $currentPriority['color'] }} rounded-lg shadow-sm">
            @svg($currentPriority['icon'], 'w-3.5 h-3.5')
            <span class="text-xs font-bold">
                {{ $currentPriority['label'] }}
            </span>
        </div>

        {{-- Due Date --}}
        <div class="flex items-center gap-1.5 px-2.5 py-1 border {{ $borderColor }} rounded-full">
            <x-heroicon-o-clock class="w-4 h-4 {{ $iconColor }}" />
            <span class="text-xs font-semibold {{ $textColor }} whitespace-nowrap">
                {{ $daysText }}
            </span>
        </div>

        {{-- Progress --}}
        @if($task->subtasks->count() > 0)
        <div class="flex items-center gap-2 px-2.5 py-1 bg-gray-100 dark:bg-gray-700 rounded-lg shadow-sm">
            <div class="relative w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full overflow-hidden">
                <div class="absolute top-0 left-0 h-full transition-all duration-500 {{ $progressPercentage == 100 ? 'bg-green-500' : 'bg-primary-500' }}"
                    style="width: {{ $progressPercentage }}%"></div>
            </div>
            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">
                {{ $progressPercentage }}%
            </span>
        </div>
        @endif

        {{-- More Actions --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" @click.away="open = false" type="button"
                class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all">
                <x-heroicon-o-ellipsis-horizontal class="w-5 h-5" />
            </button>

            {{-- Actions Dropdown --}}
            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="transform opacity-0 scale-95 -translate-y-2"
                x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="transform opacity-0 scale-95 -translate-y-2"
                class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border-2 border-gray-200 dark:border-gray-700 z-30 overflow-hidden"
                style="filter: drop-shadow(0 20px 25px rgb(0 0 0 / 0.15));">
                <div class="py-2">
                    <button wire:click="$dispatch('openTaskDetailModal', { taskId: {{ $task->id }} })"
                        @click="open = false" type="button"
                        class="w-full text-left px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3 transition-all hover:pl-5">
                        <x-heroicon-o-eye class="w-5 h-5" />
                        Lihat Detail
                    </button>
                    <button type="button"
                        class="w-full text-left px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3 transition-all hover:pl-5">
                        <x-heroicon-o-pencil class="w-5 h-5" />
                        Edit Tugas
                    </button>
                    <button type="button"
                        class="w-full text-left px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3 transition-all hover:pl-5">
                        <x-heroicon-o-share class="w-5 h-5" />
                        Bagikan
                    </button>
                    <div class="border-t-2 border-gray-200 dark:border-gray-700 my-2"></div>
                    <button type="button"
                        class="w-full text-left px-4 py-2.5 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-3 transition-all hover:pl-5">
                        <x-heroicon-o-trash class="w-5 h-5" />
                        Hapus Tugas
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</div>