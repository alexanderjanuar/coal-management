<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header with Project Info -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            @if($record->client && $record->client->logo)
                            <img src="{{ Storage::url($record->client->logo) }}" alt="{{ $record->client->name }}"
                                class="w-16 h-16 rounded-lg object-cover border-2 border-primary-50">
                            @else
                            <div class="w-16 h-16 rounded-lg bg-primary-50 flex items-center justify-center">
                                <span class="text-primary-600 text-xl font-bold">
                                    {{ $record->client ? substr($record->client->name, 0, 2) : 'P' }}
                                </span>
                            </div>
                            @endif
                            <div class="absolute -bottom-1 -right-1">
                                <span
                                    class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-success-50 border-2 border-white">
                                    <svg class="w-3 h-3 text-success-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <h1 class="text-2xl font-bold text-gray-900">
                                    {{ $record->name }}
                                </h1>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ match($record->status) {
                                    'completed' => 'bg-success-50 text-success-700',
                                    'in_progress' => 'bg-warning-50 text-warning-700',
                                    'on_hold' => 'bg-danger-50 text-danger-700',
                                    default => 'bg-gray-100 text-gray-700'
                                } }}">
                                    {{ ucwords(str_replace('_', ' ', $record->status)) }}
                                </span>
                            </div>
                            <p class="text-gray-500 mt-1">{{ $record->client->name }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-primary-700 bg-primary-50 rounded-lg hover:bg-primary-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Add Step
                        </button>
                        <button
                            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            Edit Project
                        </button>
                    </div>
                </div>

                @if($record->description)
                <p class="text-gray-600 mt-4">{{ $record->description }}</p>
                @endif
            </div>

            <!-- Project Progress Tracker -->
            <div class="border-t border-gray-100 px-6 py-8">
                <div class="flex items-start justify-between relative">
                    <!-- Progress Line -->
                    <div class="absolute left-0 right-0 top-4 transform">
                        <div class="h-1 bg-gray-200">
                            <!-- Active Progress -->
                            <div class="h-1 bg-orange-500 transition-all duration-500 ease-in-out" style="width: {{ match($record->status) {
                                    'draft' => '0%',
                                    'in_progress' => '33%',
                                    'review' => '66%',
                                    'completed' => '100%',
                                    default => '0%'
                                } }}">
                            </div>
                        </div>
                    </div>

                    <!-- Status Points -->
                    @foreach(['Draft', 'In Progress', 'Review', 'Completed'] as $index => $stage)
                    @php
                    $isActive = match($record->status) {
                    'draft' => $index === 0,
                    'in_progress' => $index <= 1, 'review'=> $index <= 2, 'completed'=> $index <= 3, default=> $index
                                === 0
                                };
                                $isCurrent = match($record->status) {
                                'draft' => $index === 0,
                                'in_progress' => $index === 1,
                                'review' => $index === 2,
                                'completed' => $index === 3,
                                default => $index === 0
                                };
                                @endphp
                                <div class="relative z-10 flex flex-col items-center" style="width: 120px">
                                    <div class="flex items-center justify-center">
                                        <div class="w-8 h-8 rounded-full border-2 {{ $isActive 
                                ? ($isCurrent ? 'bg-orange-500 border-orange-500' : 'bg-orange-500 border-orange-500') 
                                : 'bg-white border-gray-300' }} flex items-center justify-center">
                                            @if($isActive)
                                            @if($isCurrent)
                                            <span class="text-white text-sm font-medium">{{ $index + 1 }}</span>
                                            @else
                                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                            </svg>
                                            @endif
                                            @else
                                            <span class="text-gray-400 text-sm font-medium">{{ $index + 1 }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span
                                        class="text-sm mt-2 {{ $isActive ? 'text-gray-900 font-medium' : 'text-gray-500' }}">
                                        {{ $stage }}
                                    </span>
                                </div>
                                @endforeach
                </div>
            </div>
        </div>

        <!-- Project Steps -->
        <div class="grid grid-cols-1 gap-6">
            @forelse($record->steps as $step)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg {{ match($step->status) {
                                    'completed' => 'bg-success-50 text-success-600',
                                    'in_progress' => 'bg-warning-50 text-warning-600',
                                    'waiting_for_documents' => 'bg-info-50 text-info-600',
                                    default => 'bg-gray-50 text-gray-600'
                                } }} flex items-center justify-center">
                                <span class="text-lg font-semibold">{{ $loop->iteration }}</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $step->name }}</h3>
                                @if($step->description)
                                <p class="text-sm text-gray-500">{{ $step->description }}</p>
                                @endif
                            </div>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ match($step->status) {
                                'completed' => 'bg-success-50 text-success-700',
                                'in_progress' => 'bg-warning-50 text-warning-700',
                                'waiting_for_documents' => 'bg-info-50 text-info-700',
                                default => 'bg-gray-100 text-gray-700'
                            } }}">
                            {{ ucwords(str_replace('_', ' ', $step->status)) }}
                        </span>
                    </div>

                    @if($step->tasks && $step->tasks->isNotEmpty())
                    <div class="mt-4 space-y-3">
                        @foreach($step->tasks as $task)
                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <input type="checkbox"
                                    class="h-5 w-5 text-primary-600 rounded border-gray-300 focus:ring-primary-500" {{
                                    $task->status === 'completed' ? 'checked' : '' }}>
                            </div>
                            <div class="flex-grow">
                                <h4
                                    class="text-sm font-medium text-gray-900 {{ $task->status === 'completed' ? 'line-through text-gray-500' : '' }}">
                                    {{ $task->title }}
                                </h4>
                                @if($task->description)
                                <p class="text-sm text-gray-500 mt-1">{{ $task->description }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                @if($task->requires_document)
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Requires Document
                                </span>
                                @endif
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium {{ match($task->status) {
                                                'completed' => 'bg-success-50 text-success-700',
                                                'in_progress' => 'bg-warning-50 text-warning-700',
                                                'blocked' => 'bg-danger-50 text-danger-700',
                                                default => 'bg-gray-100 text-gray-700'
                                            } }}">
                                    {{ ucwords(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl shadow-sm p-6 text-center">
                <div class="flex flex-col items-center justify-center">
                    <div class="w-16 h-16 rounded-full bg-primary-50 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">No Steps Yet</h3>
                    <p class="text-gray-500 mt-1">Get started by adding your first project step</p>
                    <button
                        class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Add First Step
                    </button>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>