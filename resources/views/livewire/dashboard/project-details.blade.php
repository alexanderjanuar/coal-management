<div class="bg-white hover:shadow-md transition-all duration-300 rounded-b-lg">
    <div class="divide-y">
        @foreach ($project->steps->sortBy('order') as $step)
        <div wire:key="step-{{ $step->id }}" x-data="{ isOpen: false }" class="relative"
            :class="{'bg-gray-50': isOpen}">

            <!-- Step Header -->
            <div class="p-4 sm:p-6 cursor-pointer hover:bg-gray-50 transition-colors duration-200"
                @click="isOpen = !isOpen">
                <!-- Mobile Layout: Stack everything vertically -->
                <div class="flex flex-col space-y-4 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between">
                    <!-- Left Section -->
                    <div class="flex items-start gap-3 sm:gap-4">
                        <!-- Status Circle -->
                        <div @class([ 'relative w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl flex items-center justify-center transition-all duration-300'
                            , 'bg-success-500'=> $step->status === 'completed',
                            'bg-warning-500' => $step->status === 'in_progress',
                            'bg-primary-500' => $step->status === 'pending',
                            'bg-danger-500' => $step->status === 'waiting_for_documents',
                            ])>
                            <!-- Icon -->
                            <div class="text-white">
                                @if($step->status === 'completed')
                                <x-heroicon-o-check-circle class="w-5 h-5 sm:w-6 sm:h-6" />
                                @elseif($step->status === 'in_progress')
                                <x-heroicon-o-clock class="w-5 h-5 sm:w-6 sm:h-6 animate-spin-slow" />
                                @elseif($step->status === 'waiting_for_documents')
                                <x-heroicon-o-document-text class="w-5 h-5 sm:w-6 sm:h-6" />
                                @else
                                <x-heroicon-o-queue-list class="w-5 h-5 sm:w-6 sm:h-6" />
                                @endif
                            </div>

                            <!-- Step Number -->
                            <div class="absolute -top-2 -right-2 bg-white rounded-full shadow-sm p-1">
                                <span
                                    class="flex items-center justify-center w-4 h-4 sm:w-5 sm:h-5 text-xs sm:text-sm font-bold bg-gray-50 rounded-full">
                                    {{ $step->order }}
                                </span>
                            </div>
                        </div>

                        <!-- Title & Description -->
                        <div class="min-w-0">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900">{{ $step->name }}</h3>
                            @if($step->description)
                            <div x-data="{ expanded: false }" class="mt-2">
                                <div class="text-sm text-gray-600">
                                    <template x-if="!expanded">
                                        <div>
                                            {!! Str::limit(strip_tags($step->description), 60) !!}
                                            @if (strlen(strip_tags($step->description)) > 60)
                                            <button @click.stop="expanded = true" type="button"
                                                class="inline-flex items-center text-primary-600 hover:text-primary-700">
                                                <span class="text-xs sm:text-sm font-medium">More</span>
                                                <x-heroicon-m-chevron-down class="w-3 h-3 ml-0.5" />
                                            </button>
                                            @endif
                                        </div>
                                    </template>
                                    <template x-if="expanded">
                                        <div>
                                            {!! str($step->description)->sanitizeHtml() !!}
                                            <button @click.stop="expanded = false" type="button"
                                                class="inline-flex items-center text-primary-600 hover:text-primary-700">
                                                <span class="text-xs sm:text-sm font-medium">Less</span>
                                                <x-heroicon-m-chevron-up class="w-3 h-3 ml-0.5" />
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Right Section -->
                    <!-- On mobile, make indicators scroll horizontally if needed -->
                    <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-y-0 sm:gap-4">
                        <!-- Progress Indicators Container -->
                        <div class="flex items-center gap-2 overflow-x-auto pb-2 sm:pb-0 -mx-4 sm:mx-0 px-4 sm:px-0">
                            <!-- Tasks Indicator -->
                            @if($step->tasks->isNotEmpty())
                            <div
                                class="flex items-center gap-2 bg-gray-50 px-2 sm:px-3 py-1 sm:py-1.5 rounded-full flex-shrink-0">
                                <x-heroicon-m-check-circle class="w-3 h-3 sm:w-4 sm:h-4 text-gray-400" />
                                <div class="flex -space-x-1">
                                    @foreach($step->tasks->take(3) as $task)
                                    <div @class([ 'w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full border-2 border-white transform transition-transform hover:scale-110'
                                        , 'bg-success-500'=> $task->status === 'completed',
                                        'bg-warning-500' => $task->status === 'in_progress',
                                        'bg-gray-300' => $task->status === 'pending'
                                        ])></div>
                                    @endforeach
                                </div>
                                <span class="text-xs sm:text-sm text-gray-600 font-medium whitespace-nowrap">
                                    {{ $step->tasks->where('status', 'completed')->count() }}/{{ $step->tasks->count()
                                    }}
                                </span>
                            </div>
                            @endif

                            <!-- Documents Indicator -->
                            @if($step->requiredDocuments->isNotEmpty())
                            <div
                                class="flex items-center gap-2 bg-gray-50 px-2 sm:px-3 py-1 sm:py-1.5 rounded-full flex-shrink-0">
                                <x-heroicon-m-document-text class="w-3 h-3 sm:w-4 sm:h-4 text-gray-400" />
                                <div class="flex -space-x-1">
                                    @foreach($step->requiredDocuments->take(3) as $document)
                                    <div @class([ 'w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full border-2 border-white transform transition-transform hover:scale-110'
                                        , 'bg-success-500'=> $document->status === 'approved',
                                        'bg-warning-500' => $document->status === 'pending_review',
                                        'bg-danger-500' => $document->status === 'rejected',
                                        'bg-gray-300' => !$document->submittedDocuments->count()
                                        ])></div>
                                    @endforeach
                                </div>
                                <span class="text-xs sm:text-sm text-gray-600 font-medium whitespace-nowrap">
                                    {{ $step->requiredDocuments->where('status', 'approved')->count() }}/{{
                                    $step->requiredDocuments->count() }}
                                </span>
                            </div>
                            @endif


                        </div>

                        <!-- Status & Toggle -->
                        <!-- Status & Toggle -->
                        <div class="flex items-center justify-between sm:justify-end gap-2">
                            <!-- Legend Button - Added before the status badge -->
                            <div x-data="{ showLegend: false }" class="relative hidden sm:block">
                                <button @mouseenter="showLegend = true" @mouseleave="showLegend = false"
                                    class="p-1.5 rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                                    <x-heroicon-m-information-circle class="w-4 h-4" />
                                </button>

                                <!-- Legend Popup -->
                                <div x-show="showLegend" x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-1" @mouseleave="showLegend = false"
                                    class="absolute right-0 mt-2 w-64 rounded-lg bg-white shadow-lg ring-1 ring-gray-900/5 z-50"
                                    style="display: none;">
                                    <div class="p-4">
                                        <!-- Tasks Legend -->
                                        <div class="mb-4">
                                            <h4 class="text-sm font-medium text-gray-900 mb-2 flex items-center gap-2">
                                                <x-heroicon-m-check-circle class="w-4 h-4 text-gray-500" />
                                                Tasks Status
                                            </h4>
                                            <div class="space-y-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2.5 h-2.5 rounded-full bg-success-500"></span>
                                                    <span class="text-xs text-gray-600">Completed</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2.5 h-2.5 rounded-full bg-warning-500"></span>
                                                    <span class="text-xs text-gray-600">In Progress</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span>
                                                    <span class="text-xs text-gray-600">Pending</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Documents Legend -->
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900 mb-2 flex items-center gap-2">
                                                <x-heroicon-m-document-text class="w-4 h-4 text-gray-500" />
                                                Documents Status
                                            </h4>
                                            <div class="space-y-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2.5 h-2.5 rounded-full bg-success-500"></span>
                                                    <span class="text-xs text-gray-600">Approved</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2.5 h-2.5 rounded-full bg-warning-500"></span>
                                                    <span class="text-xs text-gray-600">Under Review</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2.5 h-2.5 rounded-full bg-danger-500"></span>
                                                    <span class="text-xs text-gray-600">Rejected</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span>
                                                    <span class="text-xs text-gray-600">Not Submitted</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Footer Note -->
                                    <div class="border-t px-4 py-3">
                                        <p class="text-xs text-gray-500">
                                            Click on any step to view details
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <x-filament::badge size="sm" :color="match($step->status) {
                                            'completed' => 'success',
                                            'in_progress' => 'warning',
                                            'waiting_for_documents' => 'danger',
                                            default => 'secondary'
                                        }">
                                {{ str_replace('_', ' ', Str::title($step->status)) }}
                            </x-filament::badge>

                            <x-heroicon-o-chevron-down
                                class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 transform transition-transform duration-200"
                                x-bind:class="isOpen ? 'rotate-180' : ''" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step Content -->
            <div x-show="isOpen" x-collapse class="border-t">
                <div class="p-4 sm:p-6 space-y-6 bg-gray-50">
                    @if($step->tasks->isNotEmpty())
                    <livewire:dashboard.project-tasks :step="$step" :wire:key="'tasks-'.$step->id" />
                    @endif

                    @if($step->status === 'waiting_for_documents' || $step->requiredDocuments->isNotEmpty())
                    <livewire:dashboard.project-documents :step="$step" :wire:key="'documents-'.$step->id" />
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>