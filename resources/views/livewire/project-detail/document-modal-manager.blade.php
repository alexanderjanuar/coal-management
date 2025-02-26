<div>
    <x-filament::modal id="documentModal" slide-over width='4xl'>
        @if ($document)
        <x-slot name="header">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <!-- Document Icon -->
                <div class="flex-shrink-0 flex items-center justify-center w-10 sm:w-12 h-10 sm:h-12 rounded-xl bg-primary-950/20 dark:bg-primary-900/20 ring-1 ring-primary-900/10 dark:ring-primary-400/10">
                    @if($document->submittedDocuments->count() > 0)
                        <x-heroicon-o-document-text class="w-5 sm:w-6 h-5 sm:h-6 text-primary-600 dark:text-primary-400" />
                    @else
                        <x-heroicon-o-document-plus class="w-5 sm:w-6 h-5 sm:h-6 text-primary-600 dark:text-primary-400" />
                    @endif
                </div>
        
                <!-- Document Info -->
                <div class="min-w-0 space-y-1">
                    <!-- Project & Client Info -->
                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                        <span class="font-medium text-primary-600 dark:text-primary-400">
                            {{ $document->projectStep->project->client->name }}
                        </span>
                        <span>&bull;</span>
                        <span class="truncate">
                            {{ $document->projectStep->project->name }}
                        </span>
                    </div>
        
                    <!-- Document Name & Step -->
                    <div class="flex items-center gap-2">
                        <h3 class="text-base sm:text-xl font-semibold text-gray-900 dark:text-white leading-tight truncate">
                            {{ $document->name }}
                        </h3>
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                            Step {{ $document->projectStep->order }}
                        </span>
                    </div>
                </div>
            </div>
        </x-slot>

        <div class="space-y-4 sm:space-y-6">
            <!-- Status Section -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700">
                {{-- Status Section --}}
                <div class="px-4 sm:px-6 py-4 sm:py-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-center justify-center">
                                <x-heroicon-m-signal class="w-4 h-4 text-gray-600 dark:text-gray-300" />
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Current Status</span>
                        </div>

                        <div class="w-full sm:w-auto">
                            @if(!auth()->user()->hasRole(['staff', 'client']))
                            <x-filament::dropdown placement="bottom-end" class="w-full">
                                <x-slot name="trigger">
                                    <x-filament::button size="sm" :color="match($document->status) {
                                            'draft' => 'gray',
                                            'uploaded' => 'info',
                                            'pending_review' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            default => 'gray'
                                        }" class="w-full sm:w-auto">
                                        <div class="flex items-center gap-2">
                                            @switch($document->status)
                                            @case('draft')
                                            <x-heroicon-m-document class="w-4 h-4" />
                                            @break
                                            @case('uploaded')
                                            <x-heroicon-m-arrow-up-tray class="w-4 h-4" />
                                            @break
                                            @case('pending_review')
                                            <x-heroicon-m-clock class="w-4 h-4" />
                                            @break
                                            @case('approved')
                                            <x-heroicon-m-check-circle class="w-4 h-4" />
                                            @break
                                            @case('rejected')
                                            <x-heroicon-m-x-circle class="w-4 h-4" />
                                            @break
                                            @default
                                            <x-heroicon-m-question-mark-circle class="w-4 h-4" />
                                            @endswitch

                                            <span>{{ ucwords(str_replace('_', ' ', $document->status ?? 'Not Set'))
                                                }}</span>
                                            <x-heroicon-m-chevron-down class="w-4 h-4" />
                                        </div>
                                    </x-filament::button>
                                </x-slot>

                                <x-filament::dropdown.list class="w-full sm:w-auto">
                                    @if($document->status === 'uploaded')
                                    <x-filament::dropdown.list.item wire:click="updateStatus('pending_review')"
                                        icon="heroicon-m-clock"
                                        :color="$document->status === 'pending_review' ? 'warning' : 'gray'">
                                        Pending Review
                                    </x-filament::dropdown.list.item>
                                    @endif

                                    @if($document->status === 'pending_review')
                                    <x-filament::dropdown.list.item wire:click="updateStatus('approved')"
                                        icon="heroicon-m-check-circle"
                                        :color="$document->status === 'approved' ? 'success' : 'gray'">
                                        Approved
                                    </x-filament::dropdown.list.item>

                                    <x-filament::dropdown.list.item wire:click="updateStatus('rejected')"
                                        icon="heroicon-m-x-circle"
                                        :color="$document->status === 'rejected' ? 'danger' : 'gray'">
                                        Rejected
                                    </x-filament::dropdown.list.item>
                                    @endif
                                </x-filament::dropdown.list>
                            </x-filament::dropdown>
                            @else
                            <div class="px-3 py-2 text-sm font-medium rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                                <div class="flex items-center gap-2">
                                    @switch($document->status)
                                    @case('draft')
                                    <x-heroicon-m-document class="w-4 h-4" />
                                    @break
                                    @case('uploaded')
                                    <x-heroicon-m-arrow-up-tray class="w-4 h-4" />
                                    @break
                                    @case('pending_review')
                                    <x-heroicon-m-clock class="w-4 h-4" />
                                    @break
                                    @case('approved')
                                    <x-heroicon-m-check-circle class="w-4 h-4" />
                                    @break
                                    @case('rejected')
                                    <x-heroicon-m-x-circle class="w-4 h-4" />
                                    @break
                                    @default
                                    <x-heroicon-m-question-mark-circle class="w-4 h-4" />
                                    @endswitch

                                    <span>{{ ucwords(str_replace('_', ' ', $document->status ?? 'Not Set')) }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Reviewer Section --}}
                @if($document->reviewer_id && in_array($document->status, ['pending_review', 'approved', 'rejected']))
                <div class="px-4 sm:px-6 py-3 border-t border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-center justify-center">
                            <x-heroicon-m-user class="w-4 h-4 text-gray-600 dark:text-gray-300" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Reviewer</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $document->reviewer->name }}</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <!-- Upload Section -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700">
                <div class="px-4 sm:px-6 py-4 sm:py-5">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-center justify-center">
                            <x-heroicon-m-arrow-up-tray class="w-4 h-4 text-gray-600 dark:text-gray-300" />
                        </div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">Upload New Document</h4>
                    </div>

                    <form wire:submit="uploadDocument" class="space-y-4">
                        {{ $this->uploadFileForm }}

                        <div class="flex">
                            <x-filament::button type="submit" size="sm" class="w-full justify-center">
                                <div class="inline-flex items-center gap-2">
                                    <x-heroicon-m-arrow-up-tray class="w-4 h-4" />
                                    <span class="font-medium">Upload Document</span>
                                </div>
                            </x-filament::button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if($document->submittedDocuments->count() > 0)
        <div class="space-y-2 sm:space-y-3">
            @foreach($document->submittedDocuments->sortByDesc('created_at') as $submission)
            <div
                class="group flex items-center gap-2 sm:gap-4 p-2 sm:p-3 bg-gray-50/50 dark:bg-gray-800/50 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                <div class="flex-shrink-0">
                    <div
                        class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-white dark:bg-gray-700 ring-1 ring-gray-100 dark:ring-gray-600 flex items-center justify-center">
                        <x-heroicon-o-document-text class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 dark:text-gray-300" />
                    </div>
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ basename($submission->file_path) }}
                    </p>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mt-0.5">
                        <span class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400">{{ $submission->user->name }}</span>
                        <span class="hidden sm:inline text-xs text-gray-300 dark:text-gray-600">&bull;</span>
                        <span class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400">{{ $submission->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                <div class="flex-shrink-0 flex items-center gap-1.5 sm:gap-2">
                    <!-- View Button -->
                    <x-filament::button wire:click="viewDocument({{ $submission->id }})" x-on:click="$dispatch('open-modal', { id: 'preview-document' })"
                        color="gray" size="xs"
                        class="opacity-0 group-hover:opacity-100 transition-opacity hover:bg-gray-100 dark:hover:bg-gray-600">
                        <div class="inline-flex items-center gap-1 sm:gap-2">
                            <x-heroicon-m-eye class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                            <span class="hidden sm:inline font-medium">View</span>
                        </div>
                    </x-filament::button>

                    <!-- Download Button -->
                    <x-filament::button wire:click="downloadDocument({{ $submission->id }})" color="gray" size="xs"
                        class="opacity-0 group-hover:opacity-100 transition-opacity hover:bg-gray-100 dark:hover:bg-gray-600">
                        <div class="inline-flex items-center gap-1 sm:gap-2">
                            <x-heroicon-m-arrow-down-tray class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                            <span class="hidden sm:inline font-medium">Download</span>
                        </div>
                    </x-filament::button>

                    <!-- Remove Button -->
                    @if(!auth()->user()->hasRole(['client']))
                    <x-filament::button
                        x-on:click="$dispatch('open-modal', { id: 'delete-document-{{ $submission->id }}' })"
                        color="danger" size="xs"
                        class="opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-100 dark:hover:bg-red-900">
                        <div class="inline-flex items-center gap-1 sm:gap-2">
                            <x-heroicon-m-trash class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                            <span class="hidden sm:inline font-medium">Remove</span>
                        </div>
                    </x-filament::button>

                    <!-- Delete Confirmation Modal -->
                    <x-filament::modal id="delete-document-{{ $submission->id }}" alignment="center" width="sm">
                        <x-slot name="header">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex-shrink-0 w-10 h-10 rounded-full bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
                                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-500 dark:text-red-400" />
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Delete Document</h3>
                            </div>
                        </x-slot>

                        <div class="space-y-3">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Are you sure you want to delete this document? This action cannot be undone.
                            </p>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ basename($submission->file_path) }}
                            </p>
                        </div>

                        <x-slot name="footer">
                            <div class="flex justify-end gap-2">
                                <x-filament::button
                                    x-on:click="$dispatch('close-modal', { id: 'delete-document-{{ $submission->id }}' })"
                                    color="gray" size="sm">
                                    Cancel
                                </x-filament::button>

                                <x-filament::button wire:click="removeDocument({{ $submission->id }})" color="danger"
                                    size="sm">
                                    <div class="flex items-center gap-1">
                                        <x-heroicon-m-trash class="w-4 h-4" />
                                        <span>Delete Document</span>
                                    </div>
                                </x-filament::button>
                            </div>
                        </x-slot>
                    </x-filament::modal>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <x-filament::modal id="preview-document" wire:model="isPreviewModalOpen" width="7xl">
            <x-slot name="header">
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center ring-1 ring-primary-100 dark:ring-primary-700/30">
                            @if($fileType === 'pdf')
                                <x-heroicon-o-document-text class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                            @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
                                <x-heroicon-o-photo class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                            @else
                                <x-heroicon-o-document class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                            @endif
                        </div>
                        @if($previewingDocument)
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white leading-tight">
                                    {{ basename($previewingDocument->file_path) }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    Uploaded {{ $previewingDocument->created_at->diffForHumans() }}
                                </p>
                            </div>
                        @endif
                    </div>

                    @if($previewingDocument)
                        <x-filament::button wire:click="downloadDocument({{ $previewingDocument->id }})" 
                            color="gray" size="sm">
                            <div class="inline-flex items-center gap-2">
                                <x-heroicon-m-arrow-down-tray class="h-4 w-4" />
                                <span>Download</span>
                            </div>
                        </x-filament::button>
                    @endif
                </div>
            </x-slot>

            <div class="p-6">
                @if($previewUrl)
                    <div class="relative rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-900 ring-1 ring-gray-200 dark:ring-gray-700">
                        @if($fileType === 'pdf')
                            <div class="w-full h-[calc(100vh-16rem)] bg-gray-50 dark:bg-gray-900">
                                <iframe src="{{ $previewUrl }}" class="w-full h-full rounded-lg">
                                    <p>Your browser doesn't support PDF preview.</p>
                                </iframe>
                            </div>
                        @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
                            <div class="relative aspect-video flex items-center justify-center bg-gray-50 dark:bg-gray-900">
                                <img src="{{ $previewUrl }}" alt="Document Preview"
                                    class="max-w-full max-h-[calc(100vh-16rem)] object-contain rounded-lg shadow-sm">
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-16">
                                <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                                    <x-heroicon-o-document class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Preview not available</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">This file type cannot be previewed directly</p>
                                <x-filament::button wire:click="downloadDocument({{ $previewingDocument->id }})" size="sm">
                                    <div class="inline-flex items-center gap-2">
                                        <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                                        <span>Download to View</span>
                                    </div>
                                </x-filament::button>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-16">
                        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center animate-pulse">
                            <x-heroicon-o-document class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mt-4">Loading preview...</h3>
                    </div>
                @endif
            </div>
        </x-filament::modal>
        @endif
    </x-filament::modal>
</div>