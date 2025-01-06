<x-filament-panels::page class="w-full">
    {{-- Stats Overview --}}
    <div class="grid gap-4 md:grid-cols-4 mb-8">
        <x-filament::card>
            <div class="flex items-center gap-4">
                <div class="p-2 bg-primary-100 rounded-lg">
                    <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-primary-500"/>
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
                    <x-heroicon-o-play class="w-6 h-6 text-success-500"/>
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
                    <x-heroicon-o-check-circle class="w-6 h-6 text-info-500"/>
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
                    <x-heroicon-o-document class="w-6 h-6 text-warning-500"/>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Pending Documents</p>
                    <p class="text-2xl font-semibold">{{ $stats['pending_documents'] }}</p>
                </div>
            </div>
        </x-filament::card>
    </div>

    {{-- Projects List --}}
    <div class="space-y-6">
        @foreach ($clients as $client)
            @foreach ($client->projects as $project)
                <x-filament::card>
                    {{-- Project Header --}}
                    <div class="flex items-center justify-between mb-4">
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
                        <x-filament::badge
                            :color="match($project->status) {
                                'completed' => 'success',
                                'in_progress' => 'warning',
                                'on_hold' => 'danger',
                                default => 'secondary'
                            }"
                        >
                            {{ str_replace('_', ' ', ucfirst($project->status)) }}
                        </x-filament::badge>
                    </div>

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
                                                <x-filament::badge
                                                    size="sm"
                                                    :color="match($step->status) {
                                                        'completed' => 'success',
                                                        'in_progress' => 'warning',
                                                        'waiting_for_documents' => 'danger',
                                                        default => 'secondary'
                                                    }"
                                                >
                                                    {{ str_replace('_', ' ', ucfirst($step->status)) }}
                                                </x-filament::badge>
                                            </div>
                                            
                                            {{-- Tasks --}}
                                            @if($step->tasks->isNotEmpty())
                                                <div class="mt-2 space-y-2">
                                                    @foreach($step->tasks as $task)
                                                        <div class="flex items-center justify-between bg-gray-50 p-2 rounded-lg">
                                                            <span class="text-sm">{{ $task->title }}</span>
                                                            <x-filament::badge
                                                                size="sm"
                                                                :color="match($task->status) {
                                                                    'completed' => 'success',
                                                                    'in_progress' => 'warning',
                                                                    'blocked' => 'danger',
                                                                    default => 'secondary'
                                                                }"
                                                            >
                                                                {{ ucfirst($task->status) }}
                                                            </x-filament::badge>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            {{-- Documents --}}
                                            @if($step->requiredDocuments->isNotEmpty())
                                                <div class="mt-2">
                                                    <p class="text-sm font-medium text-gray-500 mb-2">Required Documents:</p>
                                                    <div class="space-y-2">
                                                        @foreach($step->requiredDocuments as $document)
                                                            <div class="flex items-center justify-between bg-gray-50 p-2 rounded-lg">
                                                                <div class="flex items-center gap-2">
                                                                    <x-heroicon-o-paper-clip class="w-4 h-4 text-gray-400"/>
                                                                    <span class="text-sm">{{ $document->name }}</span>
                                                                </div>
                                                                @php
                                                                    $submittedDoc = $document->submittedDocuments->first();
                                                                @endphp
                                                                @if($submittedDoc)
                                                                    <x-filament::badge
                                                                        size="sm"
                                                                        :color="match($submittedDoc->status) {
                                                                            'approved' => 'success',
                                                                            'rejected' => 'danger',
                                                                            default => 'warning'
                                                                        }"
                                                                    >
                                                                        {{ ucfirst($submittedDoc->status) }}
                                                                    </x-filament::badge>
                                                                @else
                                                                    <x-filament::badge size="sm" color="gray">
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
                </x-filament::card>
            @endforeach
        @endforeach
    </div>
</x-filament-panels::page>