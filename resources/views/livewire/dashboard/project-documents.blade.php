<div class="space-y-4">
    <!-- Header -->
    <h4 class="flex items-center gap-2 text-sm font-medium text-gray-700">
        <x-heroicon-m-document-text class="w-5 h-5" />
        Required Documents
    </h4>

    @if ($step->requiredDocuments->isNotEmpty())
    <div class="space-y-3">
        <!-- Documents List -->
        <div class="grid gap-3">
            @foreach ($step->requiredDocuments as $document)
            @php
            $submittedDoc = $document->submittedDocuments->first();
            @endphp
            <!-- Document Card -->
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300">
                <!-- Document Header -->
                <div class="p-3 sm:p-4">
                    <!-- Main Content -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full">
                        <!-- Icon -->
                        <div
                            class="flex-shrink-0 w-8 h-8 rounded-lg bg-gray-50 sm:flex items-center justify-center hidden">
                            <x-heroicon-o-paper-clip class="w-4 h-4 text-gray-400" />
                        </div>

                        <!-- Document Info with proper width constraints -->
                        <div class="flex-1 min-w-0">
                            <!-- Added min-w-0 and flex-1 -->
                            <div class="flex flex-col gap-1">
                                <!-- Title and Badge Container -->
                                <div class="flex flex-wrap items-center gap-2">
                                    <!-- Title Container -->
                                    <div class="min-w-0 flex-1">
                                        <!-- Added min-w-0 and flex-1 -->
                                        <h4 class="font-medium text-sm sm:text-base text-gray-900 truncate max-w-full">
                                            {{ $document->name }}
                                        </h4>
                                    </div>

                                    <!-- Required Badge -->
                                    @if($document->is_required)
                                    <span
                                        class="flex-shrink-0 inline-flex px-1.5 py-0.5 rounded-md text-xs font-medium bg-red-50 text-red-600">
                                        Required
                                    </span>
                                    @endif
                                </div>

                                <!-- Description -->
                                @if($document->description)
                                <div class="min-w-0">
                                    <!-- Added min-w-0 -->
                                    <p class="text-xs sm:text-sm text-gray-500 truncate">
                                        {{ $document->description }}
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Document Actions -->
                <div class="border-t px-3 py-2 sm:px-4 sm:py-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <!-- Status -->
                        @if ($submittedDoc)
                        <x-filament::badge size="sm" :color="match ($document->status) {
                                'pending_review' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'info'
                            }">
                            {{ match ($document->status) {
                            'pending_review' => 'Under Review',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                            default => 'Info'
                            } }}
                        </x-filament::badge>

                        <!-- Actions -->
                        <div class="flex items-center gap-2">
                            @if($submittedDoc->rejection_reason)
                            <button x-data="" x-tooltip.raw="{{ $submittedDoc->rejection_reason }}"
                                class="p-1.5 rounded-md hover:bg-gray-50 transition-colors">
                                <x-heroicon-m-information-circle class="w-4 h-4 text-red-500" />
                            </button>
                            @endif

                            @if($submittedDoc->file_path)
                            <button
                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-xs bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                <x-heroicon-m-eye class="w-3.5 h-3.5" />
                                <span>View</span>
                            </button>
                            @endif
                        </div>
                        @else
                        <x-filament::badge size="sm" color="gray">
                            Not Submitted
                        </x-filament::badge>


                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="text-center py-6">
        <p class="text-sm text-gray-500">No documents required for this step.</p>
    </div>
    @endif
</div>