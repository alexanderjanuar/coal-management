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
                            <button wire:click="viewDocument({{ $submittedDoc->id }})"
                                x-on:click="$dispatch('open-modal', { id: 'preview-documents' })"
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

    <!-- Preview Modal - Made more responsive -->
    <x-filament::modal id="preview-documents" width="7xl">
        <div class="flex items-center justify-between w-full gap-4">
            <!-- Left side - Document info -->
            <div class="flex items-center gap-4 min-w-0">
                <!-- Icon -->
                <div
                    class="flex-shrink-0 w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center ring-1 ring-primary-100">
                    @if($fileType === 'pdf')
                    <x-heroicon-o-document-text class="w-6 h-6 text-primary-600" />
                    @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
                    <x-heroicon-o-photo class="w-6 h-6 text-primary-600" />
                    @else
                    <x-heroicon-o-document class="w-6 h-6 text-primary-600" />
                    @endif
                </div>

                <!-- Document details -->
                @if($previewingDocument)
                <div class="min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900 truncate">
                        {{ basename($previewingDocument->file_path) }}
                    </h3>
                    <div class="flex items-center gap-3 text-sm text-gray-500">
                        <span>Uploaded {{ $previewingDocument->created_at->diffForHumans() }}</span>
                        @if($totalDocuments > 1)
                        <span class="flex items-center gap-1">
                            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                            <span>{{ $currentIndex + 1 }} of {{ $totalDocuments }}</span>
                        </span>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Right side - Actions -->
            @if($previewingDocument)
            <div class="flex-shrink-0 flex items-center gap-3">
                <x-filament::button wire:click="downloadDocument({{ $previewingDocument->id }})" color="gray" size="sm">
                    <div class="flex items-center gap-2">
                        <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                        <span>Download</span>
                    </div>
                </x-filament::button>
            </div>
            @endif
        </div>

        <div class="relative p-6">
            @if($previewUrl)
            <div class="rounded-xl overflow-hidden bg-gray-50 ring-1 ring-gray-200">
                @if($fileType === 'pdf')
                <div class="w-full h-[calc(100vh-16rem)] bg-gray-50">
                    <iframe src="{{ asset('storage/' . $previewUrl) }}" class="w-full h-full rounded-lg" frameborder="0"></iframe>
                </div>
                @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
                <div class="relative aspect-video flex items-center justify-center bg-gray-50">
                    <img src="{{ asset('storage/' . $previewUrl) }}" alt="Document Preview"
                        class="max-w-full max-h-[calc(100vh-16rem)] object-contain rounded-lg shadow-sm">
                </div>
                @else
                <div class="flex flex-col items-center justify-center py-16">
                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                        <x-heroicon-o-document class="w-8 h-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Preview not available</h3>
                    <p class="text-sm text-gray-500 mb-4">This file type cannot be previewed directly in the browser</p>
                    <x-filament::button wire:click="downloadDocument({{ $previewingDocument->id }})" size="sm">
                        <x-heroicon-m-arrow-down-tray class="w-4 h-4 mr-2" />
                        Download to View
                    </x-filament::button>
                </div>
                @endif
            </div>

            @if($totalDocuments > 1)
            <div
                class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-2 bg-gray-900/80 rounded-full p-2 backdrop-blur-sm">
                <button wire:click="previousDocument" class="p-2 hover:bg-white/20 rounded-full transition-colors">
                    <x-heroicon-m-chevron-left class="w-5 h-5 text-white" />
                </button>

                <div class="w-px h-5 bg-gray-600"></div>

                <button wire:click="nextDocument" class="p-2 hover:bg-white/20 rounded-full transition-colors">
                    <x-heroicon-m-chevron-right class="w-5 h-5 text-white" />
                </button>
            </div>
            @endif
            @else
            <div class="flex flex-col items-center justify-center py-16">
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center animate-pulse">
                    <x-heroicon-o-document class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-4">Loading preview...</h3>
            </div>
            @endif
        </div>
    </x-filament::modal>
    @else
    <div class="text-center py-6">
        <p class="text-sm text-gray-500">No documents required for this step.</p>
    </div>
    @endif
</div>