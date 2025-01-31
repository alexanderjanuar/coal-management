@filamentPWA

<x-filament-panels::page class="w-full">
    <style>
        .logo-container {
            width: 3rem;
            /* w-12 */
            height: 3rem;
            /* h-12 */
            border-radius: 0.75rem;
            /* rounded-xl */
            overflow: hidden;
            border: 2px solid rgb(var(--primary-50));
            /* ring-2 ring-primary-50 */
            transition: border-color 0.3s ease;
        }

        .logo-container:hover {
            border-color: rgb(var(--primary-100));
            /* hover:ring-primary-100 */
        }

        .logo-container img {
            width: 100%;
            /* w-full */
            height: 100%;
            /* h-full */
            object-fit: cover;
            transform: scale(1);
            transition: transform 0.3s ease;
        }

        .logo-container img:hover {
            transform: scale(1.05);
            /* hover:scale-105 */
        }

        @media (min-width: 768px) {
            .logo-container {
                width: 4rem;
                /* md:w-16 */
                height: 4rem;
                /* md:h-16 */
            }
        }

        @keyframes pulse-subtle {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }
        }

        @keyframes bounce-subtle {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-5px);
            }
        }

        .animate-pulse-subtle {
            animation: pulse-subtle 2s infinite;
        }

        .animate-bounce-subtle {
            animation: bounce-subtle 2s infinite;
        }

        .animate-spin-slow {
            animation: spin 3s linear infinite;
        }


        @keyframes shimmer {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .animate-shimmer {
            animation: shimmer 6s infinite linear;
        }

        .header-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            /* equivalent to space-y-4 */
        }

        @media (min-width: 768px) {

            /* md breakpoint */
            .header-container {
                flex-direction: row;
                justify-content: space-between;
                align-items: flex-start;
                gap: 0;
                /* removes space-y on larger screens */
            }
        }

        .content-container {
            position: relative;
            padding: 1rem;
            padding-bottom: 2rem;
        }

        @media (min-width: 768px) {
            .content-container {
                padding: 1.5rem;
                padding-bottom: 2rem;
            }
        }
    </style>
    {{-- Stats Overview --}}
    <div class="grid gap-4 md:grid-cols-4 mb-8">
        <x-filament::card>
            <div class="flex items-center gap-4">
                <div class="p-2 bg-blue-50 rounded-lg">
                    <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-blue-500" />
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Projects</p>
                    <p class="text-2xl font-semibold">{{ $stats['total_projects'] }}</p>
                </div>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="flex items-center gap-4">
                <div class="p-2 bg-green-50 rounded-lg">
                    <x-heroicon-o-play class="w-6 h-6 text-green-500" />
                </div>
                <div>
                    <p class="text-sm text-gray-500">Active Projects</p>
                    <p class="text-2xl font-semibold">{{ $stats['active_projects'] }}</p>
                </div>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="flex items-center gap-4">
                <div class="p-2 bg-purple-50 rounded-lg">
                    <x-heroicon-o-check-circle class="w-6 h-6 text-purple-500" />
                </div>
                <div>
                    <p class="text-sm text-gray-500">Completed</p>
                    <p class="text-2xl font-semibold">{{ $stats['completed_projects'] }}</p>
                </div>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="flex items-center gap-4">
                <div class="p-2 bg-yellow-50 rounded-lg">
                    <x-heroicon-o-document class="w-6 h-6 text-yellow-500" />
                </div>
                <div>
                    <p class="text-sm text-gray-500">Pending Documents</p>
                    <p class="text-2xl font-semibold">{{ $stats['pending_documents'] }}</p>
                </div>
            </div>
        </x-filament::card>
    </div>

    {{-- Status Tabs --}}
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                @php
                $statuses = ['all', 'draft', 'in progress', 'completed', 'on hold', 'canceled'];
                $currentStatus = request()->query('status', 'all');
                @endphp

                @foreach ($statuses as $status)
                <a href="?status={{ str_replace(' ', '_', $status) }}"
                    @class([ 'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm'
                    , 'border-primary-500 text-primary-600'=>
                    $currentStatus === str_replace(' ', '_', $status),
                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' =>
                    $currentStatus !== str_replace(' ', '_', $status),
                    ])>
                    {{ ucwords($status) }}
                    @if ($status !== 'all')
                    <span class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium bg-gray-100">
                        {{ $clients->flatMap->projects->where('status', str_replace(' ', '_', $status))->count() }}
                    </span>
                    @endif
                </a>
                @endforeach
            </nav>
        </div>
    </div>
    {{-- Projects List --}}
    <div class="space-y-4">
        <!-- Changed to space-y-4 for consistent spacing -->
        @foreach ($clients as $client)
        @php
        $filteredProjects = $client->projects->when($currentStatus !== 'all', function ($projects) use ($currentStatus)
        {
        return $projects->where('status', $currentStatus);
        });
        @endphp

        @if ($filteredProjects->isNotEmpty())
        @foreach ($filteredProjects as $project)
        <div x-data="{ open: false }"
            class="bg-white rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200">
            <!-- Project Header -->
            <div @click="open = !open"
                class=" bg-white border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300">

                <!-- Main Content Container -->
                <div class="content-container">
                    <div class="header-container">
                        <!-- Left: Client Info -->
                        <div class="flex items-start space-x-4">
                            <!-- Logo/Initial -->
                            @if ($client->logo)
                            <div class="relative">
                                <div class="logo-container">
                                    <img src="{{ Storage::url($client->logo) }}" alt="{{ $client->name }}" />
                                </div>
                            </div>
                            @else
                            <div
                                class="w-12 h-12 md:w-16 md:h-16 rounded-xl bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center">
                                <span class="text-xl md:text-2xl font-bold text-primary-600">
                                    {{ substr($client->name, 0, 1) }}
                                </span>
                            </div>
                            @endif

                            <!-- Project & Client Details -->
                            <div class="space-y-1 min-w-0">
                                <h3
                                    class="text-lg md:text-xl font-semibold text-gray-900 hover:text-primary-600 transition-colors truncate">
                                    {{ $project->name }}
                                </h3>
                                <div
                                    class="flex flex-col sm:flex-row sm:items-center space-y-1 sm:space-y-0 sm:space-x-3 text-sm text-gray-600">
                                    <span class="truncate">{{ $client->name }}</span>
                                    @if($project->due_date)
                                    <div class="hidden sm:flex items-center space-x-1">
                                        <span class="text-gray-300">•</span>
                                        <div class="flex items-center space-x-1.5">
                                            <x-heroicon-m-calendar-days
                                                class="w-4 h-4 {{ $project->due_date->isPast() ? 'text-red-500' : 'text-gray-500' }}" />
                                            <span
                                                class="{{ $project->due_date->isPast() ? 'text-red-600' : 'text-gray-600' }}">
                                                {{ $project->due_date->format('M d, Y') }}
                                            </span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right: Status and Actions -->
                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Status Badge -->
                            <div class="order-first sm:order-none">
                                <x-filament::badge :color="match ($project->status) {
                                        'completed' => 'success',
                                        'in_progress' => 'warning',
                                        'on_hold' => 'danger',
                                        default => 'secondary',
                                    }" class="px-3 py-1.5">
                                    {{ ucwords(str_replace('_', ' ', $project->status)) }}
                                </x-filament::badge>
                            </div>

                            <!-- Progress Indicator -->
                            <div
                                class="group/progress relative flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-lg">
                                <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full transition-all duration-300 {{ match(true) {
                                            $project->progress === 100 => 'bg-green-500',
                                            $project->progress >= 50 => 'bg-amber-500',
                                            default => 'bg-red-500'
                                        } }}" style="width: {{ $project->progress }}%"></div>
                                </div>
                                <span class="text-xs font-medium {{ match(true) {
                                        $project->progress === 100 => 'text-green-600',
                                        $project->progress >= 50 => 'text-amber-600',
                                        default => 'text-red-600'
                                    } }}">{{ $project->progress }}%</span>

                                <!-- Hover Tooltip -->
                                <div
                                    class="absolute -top-12 left-1/2 -translate-x-1/2 opacity-0 invisible 
                                            group-hover/progress:opacity-100 group-hover/progress:visible transition-all duration-200">
                                    <div class="bg-gray-900 text-white px-2 py-1 rounded-lg text-xs whitespace-nowrap">
                                        @if($project->progress < 100) {{ 100 - $project->progress }}% remaining
                                            @else
                                            Project completed
                                            @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons - Now positioned after progress indicator -->
                            <div class="flex items-center">
                                <a href="{{ route('filament.admin.resources.projects.view', ['record' => $project->id]) }}"
                                    class="p-1.5 rounded-lg text-gray-500 hover:text-primary-500 hover:bg-primary-50/50 transition-colors">
                                    <x-heroicon-m-eye class="w-4 h-4" />
                                </a>
                                <button
                                    class="p-1.5 rounded-lg text-gray-500 hover:text-primary-500 hover:bg-primary-50/50 transition-colors">
                                    <x-heroicon-o-chevron-down class="w-4 h-4 transition-transform duration-300"
                                        x-bind:class="open ? 'rotate-180' : ''" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            {{-- Project Details --}}
            <div x-show="open" x-collapse>
                <div class="border-t px-3 md:px-4 py-4 md:py-5">
                    <div class="relative">
                        <!-- Timeline line -->
                        <div class="absolute left-4 md:left-5 top-0 h-full w-0.5 bg-gray-200 -z-10"></div>
                        <div class="space-y-4 md:space-y-6">
                            @foreach ($project->steps->sortBy('order') as $step)
                            <div x-data="{ stepOpen: false }" class="relative">
                                <!-- Step Header -->
                                <div @click="stepOpen = !stepOpen" class="cursor-pointer">
                                    <!-- Main container with better mobile layout -->
                                    <div class="flex items-start gap-3 md:gap-4 mb-2 md:items-center">
                                        <!-- Step Number Circle -->
                                        <div @class([ 'flex-shrink-0 w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base'
                                            , 'bg-success-500 text-white'=> $step->status === 'completed',
                                            'bg-warning-500 text-white' => $step->status === 'in_progress',
                                            'bg-primary-100 text-primary-700' => $step->status === 'pending',
                                            'bg-danger-500 text-white' => $step->status === 'waiting_for_documents',
                                            ])>
                                            {{ $step->order }}
                                        </div>

                                        <!-- Content Container -->
                                        <div class="flex-1 min-w-0">
                                            <!-- Title and Status Container -->
                                            <div
                                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                                <!-- Title - with text wrap -->
                                                <h4 class="font-medium text-sm sm:text-base truncate">
                                                    {{ $step->name }}
                                                </h4>

                                                <!-- Badge and Arrow Container -->
                                                <div class="flex items-center gap-2 flex-shrink-0">
                                                    <x-filament::badge size="sm" :color="match ($step->status) {
                                                        'completed' => 'success',
                                                        'in_progress' => 'warning',
                                                        'waiting_for_documents' => 'danger',
                                                        default => 'secondary',
                                                    }">
                                                        {{ ucwords(str_replace('_', ' ', $step->status)) }}
                                                    </x-filament::badge>

                                                    <x-heroicon-o-chevron-down
                                                        class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                                        x-bind:class="stepOpen ? 'rotate-180' : ''" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Step Details --}}
                                <div x-show="stepOpen" x-collapse class="ml-14">
                                    @if ($step->tasks->isNotEmpty())
                                    <div class="mb-4">
                                        <h5 class="text-sm font-medium text-gray-900 mb-2">Tasks
                                        </h5>
                                        <div class="space-y-2">
                                            @foreach ($step->tasks as $task)
                                            <div
                                                class="relative group rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                                <div class="p-4 space-y-3">
                                                    <!-- Task Header -->
                                                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                                        <!-- Title and Status -->
                                                        <div class="flex-grow min-w-0">
                                                            <h4 class="font-medium text-gray-900 truncate">{{
                                                                $task->title }}</h4>
                                                        </div>

                                                        <!-- Status and Comments -->
                                                        <div class="flex items-center gap-2 flex-shrink-0">
                                                            <x-filament::badge size="sm" :color="match ($task->status) {
                                                                'completed' => 'success',
                                                                'in_progress' => 'warning',
                                                                'blocked' => 'danger',
                                                                default => 'secondary',
                                                            }">
                                                                {{ ucwords(str_replace('_', ' ', $task->status)) }}
                                                            </x-filament::badge>

                                                            <!-- Comments Counter -->
                                                            <button
                                                                x-on:click.stop="$dispatch('open-modal', { id: 'task-modal-{{ $task->id }}' })"
                                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-sm font-medium 
                                                                    {{ $task->comments_count > 0 
                                                                        ? 'bg-primary-50 text-primary-700 hover:bg-primary-100' 
                                                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} 
                                                                    transition-all duration-200">
                                                                <x-heroicon-m-chat-bubble-left-right class="w-4 h-4" />
                                                                <span>{{ $task->comments->count() }}</span>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Task Description -->
                                                    @if ($task->description)
                                                    <div class="text-sm text-gray-600 prose prose-sm max-w-none">
                                                        {!! str($task->description)->sanitizeHtml() !!}
                                                    </div>
                                                    @endif
                                                </div>

                                                <!-- Task Modal -->
                                                <x-filament::modal id="task-modal-{{ $task->id }}" width="4xl"
                                                    slide-over>
                                                    <div class="space-y-4">
                                                        <!-- Comments Section -->
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
                                    @endif

                                    @livewire('dashboard.project-documents',['step' => $step])
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        @endif
        @endforeach

        {{-- No Projects Message --}}
        @if (
        $clients->flatMap->projects->when($currentStatus !== 'all', function ($projects) use ($currentStatus) {
        return $projects->where('status', $currentStatus);
        })->isEmpty())
        <x-filament::card>
            <div class="text-center py-6">
                <div class="mb-2">
                    <x-heroicon-o-clipboard-document-list class="mx-auto h-12 w-12 text-gray-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-900">No Projects Found</h3>
                <p class="mt-1 text-sm text-gray-500">No projects with status
                    "{{ ucwords(str_replace('_', ' ', $currentStatus)) }}" were found.</p>
            </div>
        </x-filament::card>
        @endif



    </div>
</x-filament-panels::page>