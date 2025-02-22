<x-filament-panels::page class="w-full">
    <style>
        .logo-container {
            width: 2.5rem;
            /* Smaller on mobile */
            height: 2.5rem;
            border-radius: 0.5rem;
            overflow: hidden;
            border: 2px solid rgb(var(--primary-50));
            transition: all 0.3s ease;
        }

        @media (min-width: 640px) {
            .logo-container {
                width: 3rem;
                height: 3rem;
                border-radius: 0.75rem;
            }
        }

        @media (min-width: 768px) {
            .logo-container {
                width: 4rem;
                height: 4rem;
            }
        }

        .logo-container:hover {
            border-color: rgb(var(--primary-100));
            transform: scale(1.02);
        }

        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        /* Improved header container for better mobile layout */
        .header-container {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            padding: 0.75rem;
        }

        @media (min-width: 640px) {
            .header-container {
                padding: 1rem;
                gap: 1rem;
            }
        }

        @media (min-width: 768px) {
            .header-container {
                flex-direction: row;
                justify-content: space-between;
                align-items: flex-start;
                padding: 1.5rem;
            }
        }

        /* Improved content spacing for mobile */
        .content-container {
            padding: 0.75rem;
            margin-bottom: 1rem;
        }

        @media (min-width: 640px) {
            .content-container {
                padding: 1rem;
                margin-bottom: 1.5rem;
            }
        }

        @media (min-width: 768px) {
            .content-container {
                padding: 1.5rem;
                margin-bottom: 2rem;
            }
        }

        /* Status tabs scrolling for mobile */
        .status-tabs-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE and Edge */
        }

        .status-tabs-container::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari and Opera */
        }

        /* Responsive grid for project cards */
        .projects-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 640px) {
            .projects-grid {
                gap: 1.5rem;
            }
        }

        /* Project card responsive adjustments */
        .project-card {
            padding: 0.75rem;
            border-radius: 0.5rem;
        }

        @media (min-width: 640px) {
            .project-card {
                padding: 1rem;
                border-radius: 0.75rem;
            }
        }

        /* Responsive text sizes */
        .client-name {
            font-size: 1rem;
            line-height: 1.5rem;
        }

        @media (min-width: 640px) {
            .client-name {
                font-size: 1.125rem;
                line-height: 1.75rem;
            }
        }

        /* Project info responsive layout */
        .project-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        @media (min-width: 640px) {
            .project-info {
                flex-direction: row;
                align-items: center;
                gap: 1rem;
            }
        }

        /* Progress bar responsive sizing */
        .progress-container {
            width: 100%;
            max-width: 200px;
        }

        @media (min-width: 640px) {
            .progress-container {
                width: 150px;
            }
        }
    </style>
    {{-- Stats Overview --}}
    @livewire('dashboard.project-stats')

    {{-- Status Tabs --}}
    <div class="mb-4 sm:mb-6">
        <div class="border-b border-gray-200">
            <nav class="status-tabs-container -mb-px flex space-x-4 sm:space-x-8">
                @php
                $statuses = ['all', 'draft', 'in progress', 'completed', 'on hold', 'canceled'];
                $currentStatus = request()->query('status', 'in_progress');
                @endphp
    
                @foreach ($statuses as $status)
                <a href="?status={{ str_replace(' ', '_', $status) }}"
                    class="whitespace-nowrap pb-3 sm:pb-4 px-1 border-b-2 font-medium text-xs sm:text-sm
                        {{ $currentStatus === str_replace(' ', '_', $status) 
                            ? 'border-primary-500 text-primary-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    {{ ucwords($status) }}
                    @if ($status !== 'all')
                    <span class="ml-1 sm:ml-2 py-0.5 px-1.5 sm:px-2.5 rounded-full text-xs font-medium bg-gray-100">
                        {{ $clients->flatMap->projects->where('status', str_replace(' ', '_', $status))->count() }}
                    </span>
                    @endif
                </a>
                @endforeach
            </nav>
        </div>
    </div>

    {{-- Projects List --}}
    <div class="space-y-6">
        @foreach ($clients as $client)
        @php
        $filteredProjects = $client->projects->when($currentStatus !== 'all', function ($projects) use ($currentStatus)
        {
        return $projects->where('status', $currentStatus);
        });
        @endphp

        @if ($filteredProjects->isNotEmpty())
        <div x-data="{ isClientOpen: false }"
            class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:border-primary-100 transition-all duration-300">
            {{-- Client Header --}}
            <div class="group cursor-pointer" @click="isClientOpen = !isClientOpen">
                <div
                    class="p-4 sm:p-6 flex items-center justify-between hover:bg-gray-50/50 transition-colors duration-200">
                    <div class="flex items-center gap-4">
                        {{-- Client Logo/Initial with Dynamic Color --}}
                        @if ($client->logo)
                        <div class="logo-container">
                            <img src="{{ Storage::url($client->logo) }}" alt="{{ $client->name }}" />
                        </div>
                        @else
                        @php
                        $hasUrgentProjects = $filteredProjects->contains(fn($project) => $project->priority ===
                        'urgent');
                        $hasInProgress = $filteredProjects->contains(fn($project) =>
                        $project->status === 'in_progress' || $project->progress < 100 );
                            $allCompleted=$filteredProjects->every(fn($project) =>
                            $project->status === 'completed' || $project->progress === 100
                            );
                            @endphp

                            <div class="w-12 h-12 rounded-xl flex items-center justify-center
                            @if ($hasUrgentProjects)
                                bg-gradient-to-br from-red-50 to-red-100
                            @elseif ($allCompleted)
                                bg-gradient-to-br from-green-50 to-green-100
                            @elseif ($hasInProgress)
                                bg-gradient-to-br from-amber-50 to-amber-100
                            @else
                                bg-gradient-to-br from-gray-50 to-gray-100
                            @endif">
                                <span class="text-xl font-bold
                                @if ($hasUrgentProjects)
                                    text-red-600
                                @elseif ($allCompleted)
                                    text-green-600
                                @elseif ($hasInProgress)
                                    text-amber-600
                                @else
                                    text-gray-600
                                @endif">
                                    {{ substr($client->name, 0, 1) }}
                                </span>
                            </div>
                            @endif

                            {{-- Client Info --}}
                            <div>
                                <div class="flex items-center gap-3">
                                    <h3
                                        class="text-lg font-semibold text-gray-900 group-hover:text-primary-600 transition-colors">
                                        {{ $client->name }}
                                    </h3>

                                    @if($hasUrgentProjects)
                                    <div class="relative inline-flex">
                                        <div
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-red-500 text-white text-xs font-medium">
                                            <div
                                                class="animate-ping absolute inline-flex h-full w-full rounded-md bg-red-400 opacity-50">
                                            </div>
                                            <x-heroicon-m-exclamation-triangle class="w-3 h-3" />
                                            <span class="relative">Urgent Project</span>
                                        </div>
                                    </div>
                                    @endif

                                    @php
                                    $hasUploadedDocs = $filteredProjects->flatMap(function ($project) {
                                    return $project->steps->flatMap(function ($step) {
                                    return $step->requiredDocuments->where('status', 'uploaded');
                                    });
                                    })->isNotEmpty();
                                    @endphp

                                    @if($hasUploadedDocs)
                                    <div class="relative inline-flex">
                                        <div
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-blue-500 text-white text-xs font-medium">
                                            <div
                                                class="animate-ping absolute inline-flex h-full w-full rounded-md bg-blue-400 opacity-50">
                                            </div>
                                            <span class="relative">New Document</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-sm font-medium text-gray-600">
                                        {{ $filteredProjects->count() }} {{ Str::plural('Project',
                                        $filteredProjects->count()) }}
                                    </span>
                                    <span class="text-gray-300">•</span>
                                    <span class="text-sm text-gray-500">
                                        Last updated {{
                                        Carbon\Carbon::parse($client->latest_project_activity)->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                    </div>

                    {{-- Toggle Icon and Actions --}}
                    <div class="flex items-center gap-3">
                        <div
                            class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-50 text-sm text-gray-600">
                            <x-heroicon-m-chart-bar class="w-4 h-4 text-gray-500" />
                            <span>{{ $filteredProjects->where('status', 'completed')->count() }}/{{
                                $filteredProjects->count() }} Completed</span>
                        </div>

                        @if(!auth()->user()->hasRole('staff'))
                        <livewire:dashboard.document-client-modal :client="$client" :wire:key="'upload-'.$client->id" />
                        @endif

                        <div
                            class="h-8 w-8 rounded-lg flex items-center justify-center text-gray-400 group-hover:bg-primary-50 group-hover:text-primary-500 transition-all duration-200">
                            <x-heroicon-o-chevron-down class="w-5 h-5 transform transition-transform duration-200"
                                x-bind:class="isClientOpen ? 'rotate-180' : ''" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Projects List --}}
            <div x-show="isClientOpen" x-collapse x-cloak>
                <div class="border-t divide-y divide-gray-100">
                    @foreach ($filteredProjects as $project)
                    <div x-data="{ isProjectOpen: false }" class="group/project">
                        <div class="p-4 sm:p-6 hover:bg-gray-50/50 transition-all duration-200">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                {{-- Project Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3">
                                        <h4
                                            class="text-base font-medium text-gray-900 group-hover/project:text-primary-600 transition-colors">
                                            {{ $project->name }}
                                        </h4>

                                        @if($project->priority === 'urgent')
                                        <div
                                            class="relative inline-flex items-center gap-1 px-2 py-1 rounded-md bg-red-500 text-xs font-medium text-white animate-pulse">
                                            <x-heroicon-m-exclamation-triangle class="w-3 h-3" />
                                            <span>Urgent</span>
                                        </div>
                                        @endif

                                        <x-filament::badge size="sm" :color="match ($project->status) {
                                            'completed' => 'success',
                                            'in_progress' => 'warning',
                                            'on_hold' => 'danger',
                                            default => 'secondary',
                                        }">
                                            {{ ucwords(str_replace('_', ' ', $project->status)) }}
                                        </x-filament::badge>

                                        @php
                                        $uploadedDocsCount = $project->steps->flatMap(function ($step) {
                                        return $step->requiredDocuments->where('status', 'uploaded');
                                        })->count();
                                        @endphp

                                        @if($uploadedDocsCount > 0)
                                        <div
                                            class="relative inline-flex items-center gap-1 px-2 py-1 rounded-md bg-blue-500 text-xs font-medium text-white">
                                            <x-heroicon-m-document class="relative w-3 h-3" />
                                            <span class="relative">{{ $uploadedDocsCount }} new</span>
                                        </div>
                                        @endif
                                    </div>

                                    @if($project->due_date)
                                    <div class="mt-1 flex items-center gap-2 text-sm">
                                        <x-heroicon-m-calendar-days
                                            class="w-4 h-4 {{ $project->due_date->isPast() ? 'text-red-400' : 'text-gray-400' }}" />
                                        <span
                                            class="{{ $project->due_date->isPast() ? 'text-red-600' : 'text-gray-600' }}">
                                            Due {{ $project->due_date->format('M d, Y') }}
                                        </span>
                                    </div>
                                    @endif
                                </div>

                                {{-- Project Actions --}}
                                <div class="flex items-center gap-4">
                                    {{-- Progress Bar --}}
                                    <div
                                        class="group/progress relative flex items-center gap-3 bg-white px-4 py-2 rounded-lg border border-gray-100">
                                        <div class="w-24 h-2 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full transition-all duration-300 {{ match(true) {
                                                $project->progress === 100 => 'bg-green-500',
                                                $project->progress >= 50 => 'bg-amber-500',
                                                default => 'bg-red-500'
                                            } }}" style="width: {{ $project->progress }}%"></div>
                                        </div>
                                        <span class="text-sm font-medium {{ match(true) {
                                            $project->progress === 100 => 'text-green-600',
                                            $project->progress >= 50 => 'text-amber-600',
                                            default => 'text-red-600'
                                        } }}">{{ $project->progress }}%</span>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('filament.admin.resources.projects.view', ['record' => $project->id]) }}"
                                            class="h-8 w-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-primary-50 hover:text-primary-500 transition-all duration-200">
                                            <x-heroicon-m-eye class="w-4 h-4" />
                                        </a>
                                        <button @click="isProjectOpen = !isProjectOpen"
                                            class="h-8 w-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-primary-50 hover:text-primary-500 transition-all duration-200">
                                            <x-heroicon-o-chevron-down
                                                class="w-4 h-4 transform transition-transform duration-200"
                                                x-bind:class="isProjectOpen ? 'rotate-180' : ''" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Project Details --}}
                        <div x-show="isProjectOpen" x-collapse x-cloak class="border-t bg-gray-50/50">
                            <livewire:dashboard.project-details :project="$project" />
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @endforeach

        @if ($hasMoreClients)
        <div class="relative">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center">
                <a href="{{ route('filament.admin.resources.projects.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white rounded-lg text-primary-600 hover:text-primary-500 hover:bg-primary-50 font-medium transition-colors duration-200 border border-gray-200 shadow-sm">
                    <span>See {{ $totalClients - 5 }} More {{ Str::plural('Projects', $totalClients - 5) }}</span>
                    <x-heroicon-m-arrow-long-right class="w-5 h-5" />
                </a>
            </div>
        </div>
        @endif

        <x-filament::modal id="document-client-modal" width="3xl" slide-over>
            @livewire('dashboard.document-client-modal')
        </x-filament::modal>
    </div>
</x-filament-panels::page>