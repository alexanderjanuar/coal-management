@filamentPWA

<x-filament-panels::page class="w-full">

    <style>
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
                class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300">
                <!-- Main Content Container -->
                <div class="p-6 flex flex-col space-y-4">
                    <!-- Top Section: Client Info and Actions -->
                    <div class="flex justify-between items-start">
                        <!-- Left: Client Info -->
                        <div class="flex items-center space-x-4">
                            <!-- Logo/Initial -->
                            @if ($client->logo)
                            <div class="relative group/logo">
                                <div
                                    class="w-16 h-16 rounded-xl overflow-hidden ring-2 ring-primary-50 group-hover:ring-primary-100">
                                    <img src="{{ Storage::url($client->logo) }}" alt="{{ $client->name }}"
                                        class="w-full h-full object-cover transform group-hover:scale-105 transition-all duration-300" />
                                </div>
                            </div>
                            @else
                            <div
                                class="w-16 h-16 rounded-xl bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center">
                                <span class="text-2xl font-bold text-primary-600">
                                    {{ substr($client->name, 0, 1) }}
                                </span>
                            </div>
                            @endif

                            <!-- Project & Client Details -->
                            <div class="space-y-1">
                                <h3
                                    class="text-xl font-semibold text-gray-900 group-hover:text-primary-600 transition-colors">
                                    {{ $project->name }}
                                </h3>
                                <div class="flex items-center space-x-3 text-sm text-gray-600">
                                    <span>{{ $client->name }}</span>
                                    @if($project->due_date)
                                    <span class="text-gray-300">•</span>
                                    <div class="flex items-center space-x-1.5">
                                        <x-heroicon-m-calendar-days
                                            class="w-4 h-4 {{ $project->due_date->isPast() ? 'text-red-500' : 'text-gray-500' }}" />
                                        <span
                                            class="{{ $project->due_date->isPast() ? 'text-red-600' : 'text-gray-600' }}">
                                            {{ $project->due_date->format('M d, Y') }}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right: Status and Actions -->
                        <div class="flex items-center space-x-4">
                            <!-- Progress Bar -->
                            <div class="group/progress relative">
                                <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-32 h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full transition-all duration-300 rounded-full {{ match(true) {
                                                    $project->progress === 100 => 'bg-green-500',
                                                    $project->progress >= 50 => 'bg-amber-500',
                                                    default => 'bg-red-500'
                                                } }}" style="width: {{ $project->progress }}%"></div>
                                        </div>
                                        <span class="text-sm font-medium {{ match(true) {
                                            $project->progress === 100 => 'text-green-600',
                                            $project->progress >= 50 => 'text-amber-600',
                                            default => 'text-red-600'
                                        } }}">
                                            {{ $project->progress }}%
                                        </span>
                                    </div>
                                </div>

                                <!-- Hover Tooltip -->
                                <div
                                    class="absolute -top-24 left-1/2 -translate-x-1/2 opacity-0 invisible group-hover/progress:opacity-100 group-hover/progress:visible transition-all duration-200 z-10">
                                    <div class="bg-gray-900 text-white p-3 rounded-lg shadow-lg text-sm">
                                        <div class="space-y-2">
                                            <div class="flex justify-between items-center">
                                                <span>Progress Status</span>
                                                <span class="font-medium">{{ $project->progress }}%</span>
                                            </div>
                                            @if($project->progress < 100) <span class="text-xs text-gray-400">{{ 100 -
                                                $project->progress }}% remaining</span>
                                                @else
                                                <span class="text-xs text-green-400">Complete!</span>
                                                @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <x-filament::badge :color="match ($project->status) {
                                    'completed' => 'success',
                                    'in_progress' => 'warning',
                                    'on_hold' => 'danger',
                                    default => 'secondary',
                                }" class="px-4 py-2">
                                {{ ucwords(str_replace('_', ' ', $project->status)) }}
                            </x-filament::badge>

                            <!-- Action Buttons -->
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('filament.admin.resources.projects.view', ['record' => $project->id]) }}"
                                    class="p-2 rounded-lg text-gray-600 hover:text-primary-600 hover:bg-primary-50 transition-colors">
                                    <x-heroicon-m-eye class="w-5 h-5" />
                                </a>
                                <button
                                    class="p-2 rounded-lg text-gray-600 hover:text-primary-600 hover:bg-primary-50 transition-colors">
                                    <x-heroicon-o-chevron-down class="w-5 h-5 transition-transform duration-300"
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
                                <div @click="stepOpen = !stepOpen"
                                    class="cursor-pointer flex items-center gap-3 md:gap-4 mb-2">
                                    <div @class([ 'w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base'
                                        , 'bg-success-500 text-white'=> $step->status === 'completed',
                                        'bg-warning-500 text-white' => $step->status === 'in_progress',
                                        'bg-primary-100 text-primary-700' => $step->status === 'pending',
                                        'bg-danger-500 text-white' => $step->status === 'waiting_for_documents',
                                        ])>{{ $step->order }}</div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <h4 class="font-medium">{{ $step->name }}</h4>
                                            <div class="flex items-center gap-2">
                                                <x-filament::badge :color="match ($step->status) {
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

                                {{-- Step Details --}}
                                <div x-show="stepOpen" x-collapse class="ml-14">
                                    @if ($step->tasks->isNotEmpty())
                                    <div class="mb-4">
                                        <h5 class="text-sm font-medium text-gray-900 mb-2">Tasks
                                        </h5>
                                        <div class="space-y-2">
                                            @foreach ($step->tasks as $task)
                                            <div
                                                class="flex items-center justify-between bg-gray-50 p-3 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                                <div class="flex-grow">
                                                    <p class="font-medium">{{ $task->title }}</p>
                                                    @if ($task->description)
                                                    <p class="text-sm text-gray-500 mt-1">{!! str(string:
                                                        $task->description)->sanitizeHtml() !!}</p>
                                                    @endif
                                                </div>

                                                <div class="flex items-center gap-3">
                                                    <x-filament::badge size="sm" :color="match ($task->status) {
                                                        'completed' => 'success',
                                                        'in_progress' => 'warning',
                                                        'blocked' => 'danger',
                                                        default => 'secondary',
                                                    }">
                                                        {{ ucwords(str_replace('_', ' ', $task->status)) }}
                                                    </x-filament::badge>

                                                    <!-- Task Details Button with Comment Count -->
                                                    <button
                                                        x-on:click.stop="$dispatch('open-modal', { id: 'task-modal-{{ $task->id }}' })"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full font-medium 
                                                            {{ $task->comments_count > 0 
                                                                ? 'bg-primary-50 text-primary-700 hover:bg-primary-100' 
                                                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} 
                                                            transition-all duration-200">
                                                        <x-heroicon-m-chat-bubble-left-right class="w-4 h-4" />
                                                        {{ $task->comments->count() }}
                                                        <span class="sr-only">comments</span>
                                                    </button>

                                                    <!-- Task Modal with Comments -->
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
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    @if ($step->requiredDocuments->isNotEmpty())
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-900 mb-2">Required
                                            Documents</h5>
                                        <div class="space-y-2">
                                            @foreach ($step->requiredDocuments as $document)
                                            <div
                                                class="flex items-center justify-between bg-white border border-gray-200 p-3 rounded-lg">
                                                <div class="flex items-center gap-2">
                                                    <x-heroicon-o-paper-clip class="w-4 h-4 text-gray-400" />
                                                    <div>
                                                        <p class="font-medium">
                                                            {{ $document->name }}</p>
                                                        @if ($document->description)
                                                        <p class="text-sm text-gray-500">
                                                            {{ $document->description }}
                                                        </p>
                                                        @endif
                                                    </div>
                                                </div>
                                                @php
                                                $submittedDoc = $document->submittedDocuments->first();
                                                @endphp
                                                <div class="flex items-center gap-2">
                                                    @if ($submittedDoc)
                                                    <x-filament::badge size="sm" :color="match (
                                                                                        $submittedDoc->status
                                                                                    ) {
                                                                                        'approved' => 'success',
                                                                                        'rejected' => 'danger',
                                                                                        default => 'warning',
                                                                                    }">
                                                        {{ ucwords(str_replace('_', ' ', $submittedDoc->status)) }}
                                                    </x-filament::badge>
                                                    @else
                                                    <x-filament::badge size="sm" color="gray">
                                                        Not Submitted
                                                    </x-filament::badge>
                                                    @endif
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
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