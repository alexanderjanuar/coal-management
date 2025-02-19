{{-- Enhanced Project List Component --}}
<div class="space-y-6 p-4">
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 relative">
        <div class="flex items-center gap-x-2 sm:gap-x-3 group">
            <div @class([ 'p-2.5 sm:p-3.5 rounded-xl sm:rounded-2xl shadow-sm transition-all duration-300 transform hover:scale-105 hover:rotate-3'
                , 'bg-gradient-to-br from-blue-50/90 to-blue-100/90 hover:from-blue-100/90 hover:to-blue-200/90'=> $type
                === 'total',
                'bg-gradient-to-br from-green-50/90 to-green-100/90 hover:from-green-100/90 hover:to-green-200/90' =>
                $type === 'active',
                'bg-gradient-to-br from-purple-50/90 to-purple-100/90 hover:from-purple-100/90 hover:to-purple-200/90'
                => $type === 'completed',
                'bg-gradient-to-br from-yellow-50/90 to-yellow-100/90 hover:from-yellow-100/90 hover:to-yellow-200/90'
                => $type === 'pending',
                ])>
                @switch($type)
                @case('total')
                <x-heroicon-o-clipboard-document-list
                    class="w-6 h-6 sm:w-8 sm:h-8 text-blue-600 transform group-hover:rotate-6 transition-transform duration-300" />
                @break
                @case('active')
                <x-heroicon-o-play
                    class="w-6 h-6 sm:w-8 sm:h-8 text-green-600 transform group-hover:rotate-6 transition-transform duration-300" />
                @break
                @case('completed')
                <x-heroicon-o-check-circle
                    class="w-6 h-6 sm:w-8 sm:h-8 text-purple-600 transform group-hover:rotate-6 transition-transform duration-300" />
                @break
                @default
                <x-heroicon-o-document
                    class="w-6 h-6 sm:w-8 sm:h-8 text-yellow-600 transform group-hover:rotate-6 transition-transform duration-300" />
                @endswitch
            </div>
            <div class="relative">
                <h2
                    class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight group-hover:translate-x-1 transition-transform duration-300">
                    {{ ucfirst($type) }} {{ $type === 'pending' ? 'Documents' : 'Projects' }} Overview
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 flex items-center gap-1.5 mt-0.5">
                    <x-heroicon-m-clock class="w-3 h-3 sm:w-4 sm:h-4" />
                    Last updated {{ now()->format('M d, Y H:i') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Content Section --}}
    <div class="space-y-4 sm:space-y-6">
        {{-- List Header --}}
        <div class="flex justify-between items-center px-1 sm:px-0">
            <div class="space-y-0.5 sm:space-y-1">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                    Recent {{ ucfirst($type) }} {{ $type === 'pending' ? 'Documents' : 'Projects' }}
                </h3>
                <p class="text-xs sm:text-sm text-gray-500">Showing latest updates</p>
            </div>
        </div>


        {{-- Dynamic List Content --}}
        <div class="space-y-3">
            @if($type === 'pending')
            {{-- Pending Documents List --}}
            @forelse($data['pending_documents'] ?? [] as $document)
            <div
                class="group bg-white rounded-lg sm:rounded-xl border border-gray-100/80 hover:shadow-md transition-all duration-300">
                <div class="p-3 sm:p-4">
                    <div class="flex flex-col sm:flex-row items-start justify-between gap-3 sm:gap-4">
                        <div class="flex items-start gap-2 sm:gap-3 w-full sm:w-auto">
                            <div
                                class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-lg flex items-center justify-center bg-yellow-50 text-yellow-600">
                                @if(pathinfo($document->file_path, PATHINFO_EXTENSION) === 'pdf')
                                <x-heroicon-o-document-text class="w-4 h-4 sm:w-6 sm:h-6" />
                                @elseif(in_array(pathinfo($document->file_path, PATHINFO_EXTENSION), ['jpg', 'jpeg',
                                'png', 'gif']))
                                <x-heroicon-o-photo class="w-4 h-4 sm:w-6 sm:h-6" />
                                @else
                                <x-heroicon-o-document class="w-4 h-4 sm:w-6 sm:h-6" />
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4
                                    class="text-sm sm:text-base font-medium text-gray-900 group-hover:text-primary-600 transition-colors duration-200 truncate">
                                    {{ $document->name }}
                                </h4>
                                <p class="text-xs sm:text-sm text-gray-500">
                                    Uploaded {{ $document->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                            <span
                                class="px-2 sm:px-2.5 py-0.5 sm:py-1 text-xs font-medium rounded-full bg-yellow-50 text-yellow-700 whitespace-nowrap">
                                Pending Review
                            </span>
                            <button wire:click="viewDocument({{ $document->id }})"
                                x-on:click="$dispatch('open-modal', { id: 'preview-documents' })"
                                class="p-1 sm:p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-500 transition-colors duration-200 flex-shrink-0">
                                <x-heroicon-o-eye class="w-4 h-4 sm:w-5 sm:h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div
                class="flex flex-col items-center justify-center py-6 sm:py-12 bg-white rounded-lg sm:rounded-xl border border-gray-100/80">
                <div class="bg-gray-50 rounded-full p-2 sm:p-3 mb-3 sm:mb-4">
                    <x-heroicon-o-inbox class="w-5 h-5 sm:w-8 sm:h-8 text-gray-400" />
                </div>
                <h3 class="text-sm sm:text-base text-gray-900 font-medium mb-1 text-center">No Pending Documents Found
                </h3>
                <p class="text-xs sm:text-sm text-gray-500 text-center max-w-sm px-4">
                    There are no pending documents to display at the moment.
                </p>
            </div>
            @endforelse

            @elseif($type === 'total')
            @forelse($data['recent_projects'] ?? [] as $project)
            <a href="{{ route('filament.admin.resources.projects.view', ['record' => $project->id]) }}"
                class="group bg-white rounded-lg sm:rounded-xl border border-gray-100/80 hover:shadow-md transition-all duration-300 block">
                <div class="p-3 sm:p-4">
                    {{-- Project Header --}}
                    <div class="flex flex-col sm:flex-row items-start gap-3 sm:gap-4">
                        <div class="flex items-start gap-2 sm:gap-3 w-full">
                            <div @class([ 'flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-lg flex items-center justify-center'
                                , 'bg-blue-50 text-blue-600'=> $project->status === 'draft',
                                'bg-amber-50 text-amber-600' => $project->status === 'in_progress',
                                'bg-purple-50 text-purple-600' => $project->status === 'completed',
                                'bg-yellow-50 text-yellow-600' => $project->status === 'on_hold',
                                'bg-red-50 text-red-600' => $project->status === 'canceled',
                                ])>
                                <x-heroicon-o-folder class="w-4 h-4 sm:w-6 sm:h-6" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <h4
                                        class="text-sm sm:text-base font-medium text-gray-900 group-hover:text-primary-600 transition-colors duration-200 truncate">
                                        {{ $project->name }}
                                    </h4>
                                    <span
                                        @class([ 'px-2 sm:px-2.5 py-0.5 sm:py-1 text-xs font-medium rounded-full whitespace-nowrap self-start sm:self-center'
                                        , 'bg-blue-50 text-blue-700 group-hover:bg-blue-100'=> $project->status ===
                                        'draft',
                                        'bg-amber-50 text-amber-700 group-hover:bg-amber-100' => $project->status ===
                                        'in_progress',
                                        'bg-purple-50 text-purple-700 group-hover:bg-purple-100' => $project->status ===
                                        'completed',
                                        'bg-yellow-50 text-yellow-700 group-hover:bg-yellow-100' => $project->status ===
                                        'on_hold',
                                        'bg-red-50 text-red-700 group-hover:bg-red-100' => $project->status ===
                                        'canceled',
                                        ])>
                                        {{ str_replace('_', ' ', ucfirst($project->status)) }}
                                    </span>
                                </div>
                                <p class="text-xs sm:text-sm text-gray-500 flex items-center gap-1.5 mt-1">
                                    <x-heroicon-m-building-office class="w-3 h-3 sm:w-4 sm:h-4" />
                                    <span class="truncate">{{ $project->client->name }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Project Progress --}}
                    @php
                    $totalItems = 0;
                    $completedItems = 0;

                    foreach ($project->steps as $step) {
                    // Count tasks
                    $tasks = $step->tasks;
                    if ($tasks->count() > 0) {
                    $totalItems += $tasks->count();
                    $completedItems += $tasks->where('status', 'completed')->count();
                    }

                    // Count documents
                    $documents = $step->requiredDocuments;
                    if ($documents->count() > 0) {
                    $totalItems += $documents->count();
                    $completedItems += $documents->where('status', 'approved')->count();
                    }
                    }

                    $progressPercentage = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;

                    // Progress color based on percentage
                    $progressColor = match(true) {
                    $progressPercentage == 100 => 'bg-purple-500',
                    $progressPercentage >= 50 => 'bg-amber-500',
                    $progressPercentage >= 25 => 'bg-yellow-500',
                    default => 'bg-red-500'
                    };

                    // Progress text color
                    $progressTextColor = match(true) {
                    $progressPercentage == 100 => 'text-purple-600',
                    $progressPercentage >= 50 => 'text-amber-600',
                    $progressPercentage >= 25 => 'text-yellow-600',
                    default => 'text-red-600'
                    };
                    @endphp

                    <div class="mt-3 sm:mt-4">
                        <div class="flex items-center justify-between text-xs sm:text-sm mb-1.5">
                            <span class="text-gray-500">Progress</span>
                            <span class="font-medium {{ $progressTextColor }}">{{ $progressPercentage }}%</span>
                        </div>
                        <div class="h-1 sm:h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full {{ $progressColor }} rounded-full transition-all duration-500"
                                style="width: {{ $progressPercentage }}%">
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            @empty
            <div
                class="flex flex-col items-center justify-center py-6 sm:py-12 bg-white rounded-lg sm:rounded-xl border border-gray-100/80">
                <div class="bg-gray-50 rounded-full p-2 sm:p-3 mb-3 sm:mb-4">
                    <x-heroicon-o-inbox class="w-5 h-5 sm:w-8 sm:h-8 text-gray-400" />
                </div>
                <h3 class="text-sm sm:text-base text-gray-900 font-medium mb-1 text-center">No Projects Found</h3>
                <p class="text-xs sm:text-sm text-gray-500 text-center max-w-sm px-4">
                    There are no projects to display at the moment. New projects will appear here when they are created.
                </p>
            </div>
            @endforelse

            @else
            {{-- Other project types --}}
            @forelse($data[$type . '_projects'] ?? [] as $project)
            {{-- Similar structure as total projects, but without status badge --}}
            <a href="{{ route('filament.admin.resources.projects.view', ['record' => $project->id]) }}"
                class="group bg-white rounded-lg sm:rounded-xl border border-gray-100/80 hover:shadow-md transition-all duration-300 block">
                <div class="p-3 sm:p-4">
                    <div class="flex flex-col sm:flex-row items-start gap-3 sm:gap-4">
                        <div class="flex items-start gap-2 sm:gap-3 w-full">
                            <div @class([ 'flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-lg flex items-center justify-center'
                                , 'bg-blue-50 text-blue-600'=> $project->status === 'draft',
                                'bg-amber-50 text-amber-600' => $project->status === 'in_progress',
                                'bg-purple-50 text-purple-600' => $project->status === 'completed',
                                'bg-yellow-50 text-yellow-600' => $project->status === 'on_hold',
                                'bg-red-50 text-red-600' => $project->status === 'canceled',
                                ])>
                                <x-heroicon-o-folder class="w-4 h-4 sm:w-6 sm:h-6" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4
                                    class="text-sm sm:text-base font-medium text-gray-900 group-hover:text-primary-600 transition-colors duration-200 truncate">
                                    {{ $project->name }}
                                </h4>
                                <p class="text-xs sm:text-sm text-gray-500 flex items-center gap-1.5 mt-1">
                                    <x-heroicon-m-building-office class="w-3 h-3 sm:w-4 sm:h-4" />
                                    <span class="truncate">{{ $project->client->name }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Project Progress --}}
                    @php
                    $totalItems = 0;
                    $completedItems = 0;

                    foreach ($project->steps as $step) {
                    // Count tasks
                    $tasks = $step->tasks;
                    if ($tasks->count() > 0) {
                    $totalItems += $tasks->count();
                    $completedItems += $tasks->where('status', 'completed')->count();
                    }

                    // Count documents
                    $documents = $step->requiredDocuments;
                    if ($documents->count() > 0) {
                    $totalItems += $documents->count();
                    $completedItems += $documents->where('status', 'approved')->count();
                    }
                    }

                    $progressPercentage = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;

                    // Progress color based on percentage
                    $progressColor = match(true) {
                    $progressPercentage == 100 => 'bg-purple-500',
                    $progressPercentage >= 50 => 'bg-amber-500',
                    $progressPercentage >= 25 => 'bg-yellow-500',
                    default => 'bg-red-500'
                    };

                    // Progress text color
                    $progressTextColor = match(true) {
                    $progressPercentage == 100 => 'text-purple-600',
                    $progressPercentage >= 50 => 'text-amber-600',
                    $progressPercentage >= 25 => 'text-yellow-600',
                    default => 'text-red-600'
                    };
                    @endphp

                    <div class="mt-3 sm:mt-4">
                        <div class="flex items-center justify-between text-xs sm:text-sm mb-1.5">
                            <span class="text-gray-500">Progress</span>
                            <span class="font-medium {{ $progressTextColor }}">{{ $progressPercentage }}%</span>
                        </div>
                        <div class="h-1 sm:h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full {{ $progressColor }} rounded-full transition-all duration-500"
                                style="width: {{ $progressPercentage }}%">
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            @empty
            <div
                class="flex flex-col items-center justify-center py-6 sm:py-12 bg-white rounded-lg sm:rounded-xl border border-gray-100/80">
                <div class="bg-gray-50 rounded-full p-2 sm:p-3 mb-3 sm:mb-4">
                    <x-heroicon-o-inbox class="w-5 h-5 sm:w-8 sm:h-8 text-gray-400" />
                </div>
                <h3 class="text-sm sm:text-base text-gray-900 font-medium mb-1 text-center">No {{ ucfirst($type) }}
                    Projects Found</h3>
                <p class="text-xs sm:text-sm text-gray-500 text-center max-w-sm px-4">
                    There are no {{ $type }} projects to display at the moment.
                </p>
            </div>
            @endforelse
            @endif
        </div>
    </div>
</div>