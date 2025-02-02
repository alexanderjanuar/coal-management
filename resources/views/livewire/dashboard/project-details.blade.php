<div class="bg-white hover:shadow-md transition-all duration-300 rounded-b-lg">
    <!-- Timeline Steps -->
    <div class="divide-y">
        @foreach ($project->steps->sortBy('order') as $step)
        @php
        $docCounts = $this->getDocumentCounts($step);
        @endphp
        <div wire:key="step-{{ $step->id }}" x-data="{ 
                isOpen: false,
                tasksCompleted: {{ $step->tasks->where('status', 'completed')->count() }},
                totalTasks: {{ $step->tasks->count() }},
                docsApproved: {{ $docCounts['approved'] }},
                totalDocs: {{ $docCounts['total'] }}
            }" class="relative" :class="{'bg-gray-50': isOpen}">

            <!-- Step Header -->
            <div class="p-6 cursor-pointer hover:bg-gray-50 transition-colors duration-200" @click="isOpen = !isOpen">
                <div class="flex items-center justify-between">
                    <!-- Left Section: Status and Title -->
                    <div class="flex items-center gap-4">
                        <!-- Status Circle with Animation -->
                        <div @class([ 'relative w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-300'
                            , 'bg-success-500'=> $step->status === 'completed',
                            'bg-warning-500' => $step->status === 'in_progress',
                            'bg-primary-500' => $step->status === 'pending',
                            'bg-danger-500' => $step->status === 'waiting_for_documents',
                            ])>
                            <!-- Dynamic Status Icon -->
                            <div class="text-white">
                                @if($step->status === 'completed')
                                <x-heroicon-o-check-circle class="w-6 h-6" />
                                @elseif($step->status === 'in_progress')
                                <x-heroicon-o-clock class="w-6 h-6 animate-spin-slow" />
                                @elseif($step->status === 'waiting_for_documents')
                                <x-heroicon-o-document-text class="w-6 h-6" />
                                @else
                                <x-heroicon-o-queue-list class="w-6 h-6" />
                                @endif
                            </div>

                            <!-- Step Number -->
                            <div class="absolute -top-2 -right-2 bg-white rounded-full shadow-sm p-1.5">
                                <span
                                    class="flex items-center justify-center w-5 h-5 text-sm font-bold bg-gray-50 rounded-full">
                                    {{ $step->order }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $step->name }}</h3>
                            @if($step->description)
                            <div x-data="{ expanded: false }" class="mt-3">
                                <div class="text-sm text-gray-600 text-center sm:text-left">
                                    <!-- Truncated version -->
                                    <template x-if="!expanded">
                                        <div>
                                            {!! Str::limit(strip_tags($step->description), 100) !!}
                                            @if (strlen(strip_tags($step->description)) > 100)
                                            <button @click="expanded = true" type="button"
                                                class="inline-flex items-center text-primary-600 hover:text-primary-700 ml-1">
                                                <span class="text-sm font-medium">Read more</span>
                                                <x-heroicon-m-chevron-down class="w-3 h-3 ml-0.5" />
                                            </button>
                                            @endif
                                        </div>
                                    </template>

                                    <!-- Full version -->
                                    <template x-if="expanded">
                                        <div>
                                            {!! str($step->description)->sanitizeHtml() !!}
                                            <button @click="expanded = false" type="button"
                                                class="inline-flex items-center text-primary-600 hover:text-primary-700 ml-1">
                                                <span class="text-sm font-medium">Show less</span>
                                                <x-heroicon-m-chevron-up class="w-3 h-3 ml-0.5" />
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Right Section: Progress and Status -->
                    <div class="flex items-center gap-4">
                        <!-- Task Progress -->
                        @if($step->tasks->isNotEmpty())
                        <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-full">
                            <div class="flex -space-x-1">
                                @foreach($step->tasks->take(3) as $task)
                                <div @class([ 'w-2.5 h-2.5 rounded-full border-2 border-white transform transition-transform hover:scale-110'
                                    , 'bg-success-500'=> $task->status === 'completed',
                                    'bg-warning-500' => $task->status === 'in_progress',
                                    'bg-gray-300' => $task->status === 'pending'
                                    ])></div>
                                @endforeach
                            </div>
                            <span class="text-sm text-gray-600 font-medium">
                                {{ $step->tasks->where('status', 'completed')->count() }}/{{ $step->tasks->count() }}
                            </span>
                        </div>
                        @endif

                        <!-- Status Badge -->
                        <x-filament::badge :color="match($step->status) {
                                    'completed' => 'success',
                                    'in_progress' => 'warning',
                                    'waiting_for_documents' => 'danger',
                                    default => 'secondary'
                                }">
                            {{ str_replace('_', ' ', Str::title($step->status)) }}
                        </x-filament::badge>

                        <!-- Expand/Collapse Icon -->
                        <x-heroicon-o-chevron-down
                            class="w-5 h-5 text-gray-400 transform transition-transform duration-200"
                            x-bind:class="isOpen ? 'rotate-180' : ''" />
                    </div>
                </div>
            </div>

            <!-- Step Content -->
            <div x-show="isOpen" x-collapse class="border-t">
                <div class="p-6 space-y-6 bg-gray-50">
                    <!-- Tasks Section -->
                    @if($step->tasks->isNotEmpty())
                        <livewire:dashboard.project-tasks :step="$step" :wire:key="'tasks-'.$step->id" />
                    @endif
            
                    <!-- Documents Section -->
                    @if($step->status === 'waiting_for_documents' || $step->requiredDocuments->isNotEmpty())
                        <livewire:dashboard.project-documents
                            :step="$step" 
                            :wire:key="'documents-'.$step->id" 
                        />
                    @endif
                </div>
            </div>


        </div>
        @endforeach
    </div>
</div>