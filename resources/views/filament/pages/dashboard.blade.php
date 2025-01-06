<x-filament-panels::page class="w-full">
    {{-- Stats Overview --}}
    <div class="grid gap-4 md:grid-cols-4 mb-8">
        <x-filament::card>
            <div class="flex items-center gap-4">
                <div class="p-2 bg-primary-100 rounded-lg">
                    <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-primary-500" />
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Projects</p>
                    <p class="text-2xl font-semibold">{{ $stats['total_projects'] }}</p>
                </div>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="flex items-center gap-4">
                <div class="p-2 bg-success-100 rounded-lg">
                    <x-heroicon-o-play class="w-6 h-6 text-success-500" />
                </div>
                <div>
                    <p class="text-sm text-gray-500">Active Projects</p>
                    <p class="text-2xl font-semibold">{{ $stats['active_projects'] }}</p>
                </div>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="flex items-center gap-4">
                <div class="p-2 bg-info-100 rounded-lg">
                    <x-heroicon-o-check-circle class="w-6 h-6 text-info-500" />
                </div>
                <div>
                    <p class="text-sm text-gray-500">Completed</p>
                    <p class="text-2xl font-semibold">{{ $stats['completed_projects'] }}</p>
                </div>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="flex items-center gap-4">
                <div class="p-2 bg-warning-100 rounded-lg">
                    <x-heroicon-o-document class="w-6 h-6 text-warning-500" />
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
                    $statuses = ['all', 'draft', 'in_progress', 'completed', 'on_hold', 'canceled'];
                    $currentStatus = request()->query('status', 'all');
                @endphp

                @foreach ($statuses as $status)
                    <a href="?status={{ $status }}" @class([
                        'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm',
                        'border-primary-500 text-primary-600' => $currentStatus === $status,
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' =>
                            $currentStatus !== $status,
                    ])>
                        {{ ucwords(str_replace('_', ' ', $status)) }}
                        @if ($status !== 'all')
                            <span class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium bg-gray-100">
                                {{ $clients->flatMap->projects->where('status', $status)->count() }}
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
                $filteredProjects = $client->projects->when($currentStatus !== 'all', function ($projects) use (
                    $currentStatus,
                ) {
                    return $projects->where('status', $currentStatus);
                });
            @endphp

            @if ($filteredProjects->isNotEmpty())
                @foreach ($filteredProjects as $project)
                    <div x-data="{ open: false }" class="bg-white rounded-lg shadow">
                        {{-- Project Header (Clickable) --}}
                        <div @click="open = !open"
                            class="p-4 cursor-pointer hover:bg-gray-50 transition-colors duration-150">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    @if ($client->logo)
                                        <img src="{{ Storage::url($client->logo) }}" alt="{{ $client->name }}"
                                            class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <div
                                            class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                                            <span
                                                class="text-primary-700 font-semibold">{{ substr($client->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <h3 class="text-lg font-semibold">{{ $project->name }}</h3>
                                        <p class="text-sm text-gray-500">{{ $client->name }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <x-filament::badge :color="match ($project->status) {
                                        'completed' => 'success',
                                        'in_progress' => 'warning',
                                        'on_hold' => 'danger',
                                        default => 'secondary',
                                    }">
                                        {{ str_replace('_', ' ', ucfirst($project->status)) }}
                                    </x-filament::badge>
                                </div>
                            </div>
                        </div>

                        {{-- Project Details (Collapsible) --}}
                        <div x-show="open" x-collapse>
                            <div class="border-t px-4 py-5">
                                {{-- Project Steps Progress --}}
                                <div class="relative">
                                    <div class="absolute top-5 left-5 h-full w-0.5 bg-gray-200 -ml-px"></div>
                                    <div class="space-y-6">
                                        @foreach ($project->steps->sortBy('order') as $step)
                                            <div class="relative pl-10">
                                                <div class="flex items-center gap-4">
                                                    <div @class([
                                                        'w-10 h-10 rounded-full flex items-center justify-center -ml-5 relative z-10',
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
                                                            <x-filament::badge :color="match ($step->status) {
                                                                'completed' => 'success',
                                                                'in_progress' => 'warning',
                                                                'waiting_for_documents' => 'danger',
                                                                default => 'secondary',
                                                            }">
                                                                {{ str_replace('_', ' ', ucfirst($step->status)) }}
                                                            </x-filament::badge>
                                                        </div>

                                                        {{-- Tasks --}}
                                                        @if ($step->tasks->isNotEmpty())
                                                            <div class="mt-2 space-y-2">
                                                                @foreach ($step->tasks as $task)
                                                                    <div
                                                                        class="flex items-center justify-between bg-gray-50 p-2 rounded-lg">
                                                                        <span
                                                                            class="text-sm">{{ $task->title }}</span>
                                                                        <x-filament::badge size="sm"
                                                                            :color="match ($task->status) {
                                                                                'completed' => 'success',
                                                                                'in_progress' => 'warning',
                                                                                'blocked' => 'danger',
                                                                                default => 'secondary',
                                                                            }">
                                                                            {{ ucfirst($task->status) }}
                                                                        </x-filament::badge>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif

                                                        {{-- Documents --}}
                                                        @if ($step->requiredDocuments->isNotEmpty())
                                                            <div class="mt-2">
                                                                <p class="text-sm font-medium text-gray-500 mb-2">
                                                                    Required Documents:</p>
                                                                <div class="space-y-2">
                                                                    @foreach ($step->requiredDocuments as $document)
                                                                        <div
                                                                            class="flex items-center justify-between bg-gray-50 p-2 rounded-lg">
                                                                            <div class="flex items-center gap-2">
                                                                                <x-heroicon-o-paper-clip
                                                                                    class="w-4 h-4 text-gray-400" />
                                                                                <span
                                                                                    class="text-sm">{{ $document->name }}</span>
                                                                            </div>
                                                                            @php
                                                                                $submittedDoc = $document->submittedDocuments->first();
                                                                            @endphp
                                                                            @if ($submittedDoc)
                                                                                <x-filament::badge size="sm"
                                                                                    :color="match (
                                                                                        $submittedDoc->status
                                                                                    ) {
                                                                                        'approved' => 'success',
                                                                                        'rejected' => 'danger',
                                                                                        default => 'warning',
                                                                                    }">
                                                                                    {{ ucfirst($submittedDoc->status) }}
                                                                                </x-filament::badge>
                                                                            @else
                                                                                <x-filament::badge size="sm"
                                                                                    color="gray">
                                                                                    Not Submitted
                                                                                </x-filament::badge>
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
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
                    <p class="mt-1 text-sm text-gray-500">No projects with status "{{ $currentStatus }}" were found.
                    </p>
                </div>
            </x-filament::card>
        @endif
    </div>
</x-filament-panels::page>
