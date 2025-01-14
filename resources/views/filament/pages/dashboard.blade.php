@filamentPWA

@php
    use App\Filament\Resources\ProjectResource;
@endphp

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
    <div class="space-y-4">
        @foreach ($clients as $client)
            @php
                $filteredProjects = $client->projects->when($currentStatus !== 'all', function ($projects) use ($currentStatus) {
                    return $projects->where('status', $currentStatus);
                });
            @endphp

            @if($filteredProjects->isNotEmpty())
                @foreach($filteredProjects as $project)
                    <a href="{{ ProjectResource::getUrl('view', ['record' => $project]) }}" 
                       class="block bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                        <div class="p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    @if($client->logo)
                                        <img src="{{ Storage::url($client->logo) }}" 
                                             alt="{{ $client->name }}" 
                                             class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                                            <span class="text-primary-700 font-semibold">
                                                {{ substr($client->name, 0, 1) }}
                                            </span>
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
                                    <x-heroicon-o-chevron-right class="w-5 h-5 text-gray-400" />
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            @endif
        @endforeach

        {{-- No Projects Message --}}
        @if($clients->flatMap->projects->isEmpty())
            <x-filament::card>
                <div class="text-center py-6">
                    <x-heroicon-o-clipboard-document-list class="mx-auto h-12 w-12 text-gray-400"/>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">No Projects Found</h3>
                    <p class="mt-1 text-sm text-gray-500">No projects are currently available.</p>
                </div>
            </x-filament::card>
        @endif
    </div>
</x-filament-panels::page>