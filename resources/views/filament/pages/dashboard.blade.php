<x-filament-panels::page class="w-full">
    {{-- Stats Overview --}}
    <div class="grid gap-4 md:grid-cols-4 mb-8">
        <x-filament::card>
            <div class="flex items-center gap-4">
                <div class="p-2 bg-blue-50 rounded-lg">
                    <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-blue-500"/>
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
                    <x-heroicon-o-play class="w-6 h-6 text-green-500"/>
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
                    <x-heroicon-o-check-circle class="w-6 h-6 text-purple-500"/>
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
                    <x-heroicon-o-document class="w-6 h-6 text-yellow-500"/>
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

                @foreach($statuses as $status)
                    <a href="?status={{ str_replace(' ', '_', $status) }}"
                        @class([
                            'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm',
                            'border-primary-500 text-primary-600' => $currentStatus === str_replace(' ', '_', $status),
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' => $currentStatus !== str_replace(' ', '_', $status),
                        ])
                    >
                        {{ ucwords($status) }}
                        @if($status !== 'all')
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
    <div class="space-y-6">
        @foreach ($clients as $client)
            @php
                $filteredProjects = $client->projects->when($currentStatus !== 'all', function ($projects) use ($currentStatus) {
                    return $projects->where('status', $currentStatus);
                });
            @endphp

            @if($filteredProjects->isNotEmpty())
                @foreach($filteredProjects as $project)
                    <div x-data="{ open: false }" class="bg-white rounded-lg shadow">
                        {{-- Project Header --}}
                        <div @click="open = !open" class="p-4 cursor-pointer hover:bg-gray-50 transition-colors duration-150">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-4">
                                    @if($client->logo)
                                        <img src="{{ Storage::url($client->logo) }}" alt="{{ $client->name }}" class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                                            <span class="text-primary-700 font-semibold">{{ substr($client->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <h3 class="text-lg font-semibold">{{ $project->name }}</h3>
                                        <p class="text-sm text-gray-500">{{ $client->name }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <x-filament::badge
                                        :color="match($project->status) {
                                            'completed' => 'success',
                                            'in_progress' => 'warning',
                                            'on_hold' => 'danger',
                                            default => 'secondary'
                                        }"
                                    >
                                        {{ ucwords(str_replace('_', ' ', $project->status)) }}
                                    </x-filament::badge>
                                    <x-heroicon-o-chevron-down 
                                        class="w-5 h-5 text-gray-400 transition-transform duration-200"
                                        x-bind:class="open ? 'rotate-180' : ''"
                                    />
                                </div>
                            </div>
                            @if($project->description)
                                <p class="text-sm text-gray-600 ml-14">{{ $project->description }}</p>
                            @endif
                        </div>

                        {{-- Project Details --}}
                        <div x-show="open" x-collapse>
                            <div class="border-t px-4 py-5">
                                <div class="relative">
                                    {{-- Timeline line --}}
                                    <div class="absolute left-5 top-0 h-full w-0.5 bg-gray-200 -z-10"></div>
                                    
                                    <div class="space-y-6">
                                    @foreach ($project->steps->sortBy('order') as $step)
                                        <div x-data="{ stepOpen: false }" class="relative">
                                            {{-- Step Header --}}
                                            <div @click="stepOpen = !stepOpen" class="cursor-pointer flex items-center gap-4 mb-2">
                                                <div @class([
                                                    'w-10 h-10 rounded-full flex items-center justify-center',
                                                    'bg-success-500 text-white' => $step->status === 'completed',
                                                    'bg-warning-500 text-white' => $step->status === 'in_progress',
                                                    'bg-primary-100 text-primary-700' => $step->status === 'pending',
                                                    'bg-danger-500 text-white' => $step->status === 'waiting_for_documents',
                                                ])>
                                                    {{ $step->order }}
                                                </div>
                                                <div class="flex-1">
                                                    <div class="flex items-center justify-between">
                                                        <h4 class="font-medium">{{ $step->name }}</h4>
                                                        <div class="flex items-center gap-2">
                                                            <x-filament::badge
                                                                :color="match($step->status) {
                                                                    'completed' => 'success',
                                                                    'in_progress' => 'warning',
                                                                    'waiting_for_documents' => 'danger',
                                                                    default => 'secondary'
                                                                }"
                                                            >
                                                                {{ ucwords(str_replace('_', ' ', $step->status)) }}
                                                            </x-filament::badge>
                                                            <x-heroicon-o-chevron-down 
                                                                class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                                                x-bind:class="stepOpen ? 'rotate-180' : ''"
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Step Details --}}
                                            <div x-show="stepOpen" x-collapse class="ml-14">
                                                @if($step->tasks->isNotEmpty())
                                                    <div class="mb-4">
                                                        <h5 class="text-sm font-medium text-gray-900 mb-2">Tasks</h5>
                                                        <div class="space-y-2">
                                                            @foreach($step->tasks as $task)
                                                                <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                                                                    <div>
                                                                        <p class="font-medium">{{ $task->title }}</p>
                                                                        @if($task->description)
                                                                            <p class="text-sm text-gray-500 mt-1">{{ $task->description }}</p>
                                                                        @endif
                                                                    </div>
                                                                    <x-filament::badge
                                                                        size="sm"
                                                                        :color="match($task->status) {
                                                                            'completed' => 'success',
                                                                            'in_progress' => 'warning',
                                                                            'blocked' => 'danger',
                                                                            default => 'secondary'
                                                                        }"
                                                                    >
                                                                        {{ ucwords(str_replace('_', ' ', $task->status)) }}
                                                                    </x-filament::badge>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($step->requiredDocuments->isNotEmpty())
                                                    <div>
                                                        <h5 class="text-sm font-medium text-gray-900 mb-2">Required Documents</h5>
                                                        <div class="space-y-2">
                                                            @foreach($step->requiredDocuments as $document)
                                                                <div class="flex items-center justify-between bg-white border border-gray-200 p-3 rounded-lg">
                                                                    <div class="flex items-center gap-2">
                                                                        <x-heroicon-o-paper-clip class="w-4 h-4 text-gray-400"/>
                                                                        <div>
                                                                            <p class="font-medium">{{ $document->name }}</p>
                                                                            @if($document->description)
                                                                                <p class="text-sm text-gray-500">{{ $document->description }}</p>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                    @php
                                                                        $submittedDoc = $document->submittedDocuments->first();
                                                                    @endphp
                                                                    <div class="flex items-center gap-2">
                                                                        @if($submittedDoc)
                                                                            <x-filament::badge
                                                                                size="sm"
                                                                                :color="match($submittedDoc->status) {
                                                                                    'approved' => 'success',
                                                                                    'rejected' => 'danger',
                                                                                    default => 'warning'
                                                                                }"
                                                                            >
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
                @endforeach
            @endif
        @endforeach

        {{-- No Projects Message --}}
        @if($clients->flatMap->projects->when($currentStatus !== 'all', function ($projects) use ($currentStatus) {
            return $projects->where('status', $currentStatus);
        })->isEmpty())
            <x-filament::card>
                <div class="text-center py-6">
                    <div class="mb-2">
                        <x-heroicon-o-clipboard-document-list class="mx-auto h-12 w-12 text-gray-400"/>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">No Projects Found</h3>
                    <p class="mt-1 text-sm text-gray-500">No projects with status "{{ ucwords(str_replace('_', ' ', $currentStatus)) }}" were found.</p>
                </div>
            </x-filament::card>
        @endif
    </div>
</x-filament-panels::page>