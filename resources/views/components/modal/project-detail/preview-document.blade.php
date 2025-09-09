<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full">
    <!-- Document Info -->
    <div class="flex items-start gap-3 min-w-0">
        <div
            class="hidden sm:flex flex-shrink-0 items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-primary-50 dark:bg-primary-900/50 ring-1 ring-primary-100 dark:ring-primary-800">
            @if ($fileType === 'pdf')
            <x-heroicon-o-document-text class="w-5 h-5 sm:w-6 sm:h-6 text-primary-600 dark:text-primary-400" />
            @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
            <x-heroicon-o-photo class="w-5 h-5 sm:w-6 sm:h-6 text-primary-600 dark:text-primary-400" />
            @else
            <x-heroicon-o-document class="w-5 h-5 sm:w-6 sm:h-6 text-primary-600 dark:text-primary-400" />
            @endif
        </div>
        @if ($previewingDocument)
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 flex-wrap">
                <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white leading-tight truncate">
                    {{ basename($previewingDocument->file_path) }}
                </h3>
                @php $position = $this->getDocumentPosition(); @endphp
                <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                    {{ $position['current'] }} of {{ $position['total'] }}
                </span>
            </div>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                Uploaded {{ $previewingDocument->created_at->diffForHumans() }} by
                {{ $previewingDocument->user->name }}
            </p>
        </div>
        @endif
    </div>

    <div class="flex flex-wrap items-center gap-2 mt-2 sm:mt-0">
        @if (
        $previewingDocument &&
        !auth()->user()->hasRole(['staff', 'client']))
        <!-- Status Dropdown -->
        <x-filament::dropdown placement="bottom-end">
            <x-slot name="trigger">
                <x-filament::button size="sm" :color="match ($previewingDocument->status) {
                                'uploaded' => 'info',
                                'pending_review' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            }">
                    <div class="flex items-center gap-2">
                        <x-dynamic-component :component="$this->getStatusIcon($previewingDocument->status)"
                            class="w-4 h-4" />
                        <span class="hidden sm:inline">{{ $this->getStatusLabel($previewingDocument->status)
                            }}</span>
                        <span class="sm:hidden">Status</span>
                        <x-heroicon-m-chevron-down class="w-4 h-4" />
                    </div>
                </x-filament::button>
            </x-slot>

            <x-filament::dropdown.list>
                <x-filament::dropdown.list.item
                    wire:click="updateDocumentStatus({{ $previewingDocument->id }}, 'pending_review')"
                    icon="heroicon-m-clock"
                    :color="$previewingDocument->status === 'pending_review' ? 'warning' : 'gray'">
                    Pending Review
                </x-filament::dropdown.list.item>

                <x-filament::dropdown.list.item
                    wire:click="updateDocumentStatus({{ $previewingDocument->id }}, 'approved')"
                    icon="heroicon-m-check-circle"
                    :color="$previewingDocument->status === 'approved' ? 'success' : 'gray'">
                    Approved
                </x-filament::dropdown.list.item>

                <x-filament::dropdown.list.item
                    x-on:click="$dispatch('open-modal', { id: 'rejection-reason-modal' }); $wire.openRejectionModal({{ $previewingDocument->id }})"
                    icon="heroicon-m-x-circle" :color="$previewingDocument->status === 'rejected' ? 'danger' : 'gray'">
                    Rejected
                </x-filament::dropdown.list.item>
            </x-filament::dropdown.list>
        </x-filament::dropdown>
        @endif

        @if ($previewingDocument)
        <!-- Toggle sidebar button - hide on very small screens -->
        <div x-data="{ showSidebar: false }" class="hidden sm:block relative">
            <button @click="showSidebar = !showSidebar; $dispatch('toggle-sidebar')" type="button" class="group relative inline-flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200
                            border border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500
                            text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400
                            bg-white dark:bg-gray-800 hover:bg-primary-50 dark:hover:bg-primary-900/20
                            shadow-sm hover:shadow-md"
                :class="{ 'bg-primary-50 dark:bg-primary-900/20 border-primary-500 dark:border-primary-500': showSidebar }">
                <div class="flex items-center gap-1.5">
                    <template x-if="!showSidebar">
                        <div class="flex items-center gap-1.5">
                            <x-heroicon-o-bars-3 class="w-4 h-4" />
                            <span class="text-sm font-medium">Details</span>
                        </div>
                    </template>
                    <template x-if="showSidebar">
                        <div class="flex items-center gap-1.5">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                            <span class="text-sm font-medium">Close</span>
                        </div>
                    </template>
                </div>
            </button>
        </div>
        @endif

        <!-- Close button -->
        <button x-on:click="$dispatch('close-modal', { id: 'preview-document' })" type="button"
            class="flex-shrink-0 rounded-lg p-2 text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400 transition-colors duration-200">
            <span class="sr-only">Close</span>
            <x-heroicon-m-x-mark class="w-5 h-5" />
        </button>
    </div>
</div>


<!-- Mobile sidebar toggle -->
@if ($previewingDocument)
<div class="sm:hidden flex items-center justify-between mt-4 mb-2">
    <x-filament::button wire:click="downloadDocument({{ $previewingDocument->id }})" color="gray" size="sm"
        class="flex-1 justify-center mr-2">
        <x-heroicon-m-arrow-down-tray class="w-4 h-4 mr-1" />
        Download
    </x-filament::button>

    <x-filament::button x-data="{}"
        x-on:click="$dispatch('open-modal', { id: 'confirm-delete-modal-{{ $previewingDocument->id }}' })"
        color="danger" size="sm" class="flex-1 justify-center">
        <x-heroicon-m-trash class="w-4 h-4 mr-1" />
        Remove
    </x-filament::button>
</div>
@endif

<!-- Enhanced Layout with Toggleable Sidebar -->
<div class="flex flex-col lg:flex-row h-[calc(100vh-14rem)] sm:h-[calc(100vh-10rem)]" x-data="{ showSidebar: false }"
    @toggle-sidebar.window="showSidebar = !showSidebar">
    <!-- Document Preview Section (Takes Full Width by Default) -->
    <div class="w-full transition-all duration-300 ease-in-out h-full p-2 sm:p-4 relative"
        :class="{ 'lg:w-2/3': showSidebar }">
        <!-- Replace the Previous/Next Document navigation section -->
        @if ($this->document->submittedDocuments->count() > 1)
        <div
            class="absolute left-0 right-0 top-1/2 -translate-y-1/2 flex items-center justify-between px-4 pointer-events-none">
            <!-- Previous Document Button - Always visible -->
            <button wire:click="previousDocument" class="p-2 rounded-full bg-white/80 dark:bg-gray-800/80 shadow-lg hover:bg-white dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-700 transition-all group pointer-events-auto
                        hover:scale-110 active:scale-95 transform duration-200"
                title="Previous document (Press Left Arrow)">
                <x-heroicon-o-chevron-left
                    class="w-6 h-6 text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-400" />
                <span class="sr-only">Previous document</span>
            </button>

            <!-- Next Document Button - Always visible -->
            <button wire:click="nextDocument" class="p-2 rounded-full bg-white/80 dark:bg-gray-800/80 shadow-lg hover:bg-white dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-700 transition-all group pointer-events-auto
                            hover:scale-110 active:scale-95 transform duration-200"
                title="Next document (Press Right Arrow)">
                <x-heroicon-o-chevron-right
                    class="w-6 h-6 text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-400" />
                <span class="sr-only">Next document</span>
            </button>
        </div>

        <!-- Optional: Add keyboard navigation support -->
        <script>
            document.addEventListener('keydown', function(e) {
                            if (e.key === 'ArrowLeft') {
                                @this.previousDocument();
                            } else if (e.key === 'ArrowRight') {
                                @this.nextDocument();
                            }
                        });
        </script>
        @endif

        <!-- Document Preview Container -->
        <div
            class="h-full rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-900 ring-1 ring-gray-200 dark:ring-gray-700">
            @if ($previewUrl)
            @if ($fileType === 'pdf')
            <div class="w-full h-full bg-gray-50 dark:bg-gray-900">
                <iframe src="{{ $previewUrl }}" class="w-full h-full rounded-lg">
                    <div class="flex flex-col items-center justify-center p-8">
                        <div
                            class="w-16 h-16 rounded-full bg-primary-50 dark:bg-primary-900 flex items-center justify-center mb-4">
                            <x-heroicon-o-document-text class="w-8 h-8 text-primary-600 dark:text-primary-400" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2 text-center">
                            Unable to display PDF
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 text-center">
                            The PDF viewer is not supported on this device
                        </p>
                    </div>
                </iframe>
            </div>
            @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
            <div class="h-full flex items-center justify-center bg-gray-50 dark:bg-gray-900">
                <img src="{{ $previewUrl }}" alt="Document Preview"
                    class="max-w-full max-h-full object-contain rounded-lg shadow-sm">
            </div>
            @else
            <div class="flex flex-col items-center justify-center h-full py-16">
                <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                    <x-heroicon-o-document class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    Preview not available
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    This file type cannot be previewed directly in the browser
                </p>
                @if ($previewingDocument)
                <x-filament::button wire:click="downloadDocument({{ $previewingDocument->id }})" size="sm"
                    class="inline-flex items-center">
                    <div class="inline-flex items-center gap-2">
                        <x-heroicon-m-arrow-down-tray class="w-4 h-4 -ml-1 mr-2" />
                        <span>Download to View</span>
                    </div>
                </x-filament::button>
                @endif
            </div>
            @endif
            @else
            <div class="flex flex-col items-center justify-center h-full py-16">
                <div
                    class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center animate-pulse">
                    <x-heroicon-o-document class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mt-4">
                    Loading preview...
                </h3>
            </div>
            @endif
        </div>
    </div>

    <!-- Right Sidebar (Hidden by Default) -->
    <div x-show="showSidebar" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-x-8"
        x-transition:enter-end="opacity-100 transform translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-x-0"
        x-transition:leave-end="opacity-0 transform translate-x-8"
        class="w-full lg:w-1/3 h-full p-4 flex flex-col bg-gray-50/50 dark:bg-gray-900/50 rounded-r-xl overflow-y-auto">
        @if ($previewingDocument)
        <div class="space-y-4 flex flex-col h-full">
            <!-- Quick Action Buttons at the top (hidden on mobile since we added them at the top) -->
            <div class="hidden sm:grid grid-cols-2 gap-3">
                <!-- Download Button -->
                <x-filament::button wire:click="downloadDocument({{ $previewingDocument->id }})" color="gray" size="md"
                    class="justify-center">
                    <div class="inline-flex items-center gap-2">
                        <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                        <span>Download</span>
                    </div>
                </x-filament::button>

                <!-- Remove Button with Confirmation Modal -->
                <x-filament::button x-data="{}" x-on:click="$dispatch('open-modal', { id: 'confirm-delete-modal' })"
                    color="danger" size="md" class="justify-center">
                    <div class="inline-flex items-center gap-2">
                        <x-heroicon-m-trash class="w-4 h-4" />
                        <span>Remove</span>
                    </div>
                </x-filament::button>
            </div>

            <!-- Document Status Card with improved design -->
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <!-- Status Header -->
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">Document Status</h3>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ match ($previewingDocument->status) {
                                        'uploaded' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300',
                                        'pending_review' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300',
                                        'approved' => 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-300',
                                        'rejected' => 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300',
                                        default => 'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                    } }}">
                            <x-dynamic-component :component="$this->getStatusIcon($previewingDocument->status)"
                                class="w-3.5 h-3.5 mr-1" />
                            {{ $this->getStatusLabel($previewingDocument->status) }}
                        </span>
                    </div>
                </div>

                <!-- Document Info -->
                <div class="px-4 py-3 space-y-2 bg-gray-50/80 dark:bg-gray-800/80">
                    <div class="flex items-center gap-3 text-sm">
                        <div
                            class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex-shrink-0 flex items-center justify-center text-gray-500 dark:text-gray-400">
                            {{ substr($previewingDocument->user->name, 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ $previewingDocument->user->name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Uploaded {{ $previewingDocument->created_at->format('M d, Y • g:i A') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- File Information -->
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-2 text-sm">
                        <div class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center
                                    {{ match ($fileType) {
                                        'pdf' => 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400',
                                        'jpg', 'jpeg', 'png', 'gif' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400',
                                        'doc', 'docx' => 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
                                        'xls', 'xlsx', 'csv' => 'bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400',
                                        default => 'bg-gray-50 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                                    } }}">
                            @if ($fileType === 'pdf')
                            <x-heroicon-o-document-text class="w-4 h-4" />
                            @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
                            <x-heroicon-o-photo class="w-4 h-4" />
                            @elseif(in_array($fileType, ['doc', 'docx']))
                            <x-heroicon-o-document class="w-4 h-4" />
                            @elseif(in_array($fileType, ['xls', 'xlsx', 'csv']))
                            <x-heroicon-o-table-cells class="w-4 h-4" />
                            @else
                            <x-heroicon-o-document class="w-4 h-4" />
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ basename($previewingDocument->file_path) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                <span class="uppercase">{{ strtoupper($fileType) }}</span>
                                <span class="text-gray-300 dark:text-gray-600">•</span>
                                <span>Document #{{ $previewingDocument->id }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Rejection Reason (if applicable) -->
                @if ($previewingDocument->status === 'rejected' && $previewingDocument->rejection_reason)
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 bg-red-50/20 dark:bg-red-900/10">
                    <div class="flex items-start gap-2">
                        <x-heroicon-o-exclamation-triangle
                            class="w-5 h-5 text-red-500 dark:text-red-400 flex-shrink-0 mt-0.5" />
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-red-700 dark:text-red-400 mb-1">
                                Rejection Reason:
                            </p>
                            <div
                                class="text-xs text-red-600 dark:text-red-300 prose prose-sm max-h-[13rem] overflow-y-auto">
                                {!! $previewingDocument->rejection_reason !!}
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Improved Document Notes with Toggle -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-y-auto flex-1"
                x-data="{ showNotesForm: false }">
                <!-- Notes Header with Toggle Button -->
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/80 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-o-document-text class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                            Document Notes
                        </h3>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Private to staff</span>
                            <button @click="showNotesForm = !showNotesForm" type="button"
                                class="inline-flex items-center justify-center p-1 rounded-md text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400 transition-colors">
                                <x-heroicon-o-pencil-square x-show="!showNotesForm" class="w-5 h-5" />
                                <x-heroicon-o-x-mark x-show="showNotesForm" class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Notes Content Area -->
                <div class="p-4 flex-1 overflow-auto">
                    <!-- Notes Display (when not editing) -->
                    <div x-show="!showNotesForm" class="prose prose-sm dark:prose-invert max-w-none h-full">
                        @if ($previewingDocument && $previewingDocument->notes)
                        <div
                            class="p-4 rounded-lg {{ $statusColors[$previewingDocument->status]['bg'] }} {{ $statusColors[$previewingDocument->status]['bg'] }} border">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 mt-1">
                                    <x-heroicon-m-document-text
                                        class="w-5 h-5 {{ $statusColors[$previewingDocument->status]['text'] }}" />
                                </div>
                                <div class="flex-1 space-y-1">
                                    <p
                                        class="text-sm font-medium {{ $statusColors[$previewingDocument->status]['text'] }}">
                                        Document Notes</p>
                                    <div class="text-gray-700 dark:text-gray-300 max-h-[16rem] overflow-y-auto">
                                        {!! $previewingDocument->notes !!}
                                    </div>
                                    <div class="flex items-center gap-2 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        <x-heroicon-m-clock class="w-4 h-4" />
                                        <span>Last updated
                                            {{ $previewingDocument->updated_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="flex flex-col items-center justify-center py-6 text-center h-full">
                            <div
                                class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                                <x-heroicon-o-document-text class="w-6 h-6 text-gray-400 dark:text-gray-500" />
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">No notes added yet</p>
                            <button @click="showNotesForm = true" type="button"
                                class="mt-3 inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-900/30 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400 transition-colors">
                                <x-heroicon-o-pencil class="w-3.5 h-3.5 mr-1" />
                                <span>Add Notes</span>
                            </button>
                        </div>
                        @endif
                    </div>
                    <!-- Notes Form (when editing) -->
                    <div x-show="showNotesForm" x-cloak>
                        <form wire:submit.prevent="saveDocumentNotes">
                            <div class="space-y-4">
                                {{ $this->documentNotesForm }}
                                <div class="flex items-center justify-end gap-2">
                                    <x-filament::button type="button" color="gray" size="sm"
                                        @click="showNotesForm = false">
                                        Cancel
                                    </x-filament::button>
                                    <x-filament::button type="submit" size="sm" color="primary">
                                        Save Notes
                                    </x-filament::button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Confirmation Modal for Document Deletion -->
<x-filament::modal id="confirm-delete-modal" width="md">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
                <x-heroicon-o-trash class="w-6 h-6 text-red-600 dark:text-red-400" />
            </div>
            <h2 class="text-lg font-medium text-gray-900 dark:text-white leading-6">
                Confirm Document Removal
            </h2>
        </div>
    </x-slot>

    <div class="space-y-4">
        <p class="text-gray-700 dark:text-gray-300">
            Are you sure you want to remove this document? This action cannot be undone.
        </p>

        @if ($previewingDocument)
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 mt-3">
            <div class="flex items-start gap-3">
                <div
                    class="w-9 h-9 flex-shrink-0 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                    @php
                    $fileType = $previewingDocument
                    ? strtolower(pathinfo($previewingDocument->file_path, PATHINFO_EXTENSION))
                    : '';
                    @endphp

                    @if ($fileType === 'pdf')
                    <x-heroicon-o-document-text class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                    @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
                    <x-heroicon-o-photo class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                    @else
                    <x-heroicon-o-document class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ basename($previewingDocument->file_path) }}
                    </h4>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Uploaded by {{ $previewingDocument->user->name }}
                        </span>
                        <span class="text-xs text-gray-300 dark:text-gray-600">•</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $previewingDocument->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <x-slot name="footer">
        <div class="flex justify-end gap-3">
            <x-filament::button
                x-on:click="$dispatch('close-modal', { id: 'confirm-delete-modal-{{ $previewingDocument->id ?? 'null' }}' })"
                color="gray">
                Cancel
            </x-filament::button>

            <x-filament::button wire:click="removeDocument({{ $previewingDocument->id ?? 'null' }})"
                wire:loading.attr="disabled" color="danger">
                <div class="flex items-center gap-1">
                    <x-heroicon-m-trash class="w-4 h-4" />
                    <span>Delete Document</span>
                </div>
            </x-filament::button>
        </div>
    </x-slot>
</x-filament::modal>

<!-- Enhanced Document Rejection Modal -->
<x-filament::modal id="rejection-reason-modal" width="md">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
                <x-heroicon-o-x-circle class="w-6 h-6 text-red-600 dark:text-red-400" />
            </div>
            <div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-white leading-6">
                    Document Rejection
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Please provide a detailed reason for rejecting this document
                </p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-5">
        <!-- Document Info Card -->
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    @if ($documentBeingRejected)
                    <div
                        class="w-10 h-10 rounded-lg bg-white dark:bg-gray-700 flex items-center justify-center shadow-sm">
                        @php
                        $fileType = strtolower(
                        pathinfo($documentBeingRejected->file_path, PATHINFO_EXTENSION),
                        );
                        @endphp
                        @if ($fileType === 'pdf')
                        <x-heroicon-o-document-text class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                        @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
                        <x-heroicon-o-photo class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                        @elseif(in_array($fileType, ['doc', 'docx']))
                        <x-heroicon-o-document class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                        @elseif(in_array($fileType, ['xls', 'xlsx', 'csv']))
                        <x-heroicon-o-table-cells class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                        @else
                        <x-heroicon-o-document class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                        @endif
                    </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    @if ($documentBeingRejected)
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ basename($documentBeingRejected->file_path) }}
                    </h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Uploaded by {{ $documentBeingRejected->user->name }}
                        </span>
                        <span class="text-xs text-gray-300 dark:text-gray-600">•</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $documentBeingRejected->created_at->diffForHumans() }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Rejection Warning -->
            <div class="bg-red-50 dark:bg-red-900/10 rounded-lg p-4 border border-red-100 dark:border-red-800/30">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-exclamation-triangle
                        class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="text-sm font-medium text-red-800 dark:text-red-300">
                            This action requires explanation
                        </p>
                        <p class="text-sm text-red-600 dark:text-red-400 mt-1">
                            The submitter will be notified of your decision with the reason you provide below.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Rejection Form -->
            <form wire:submit.prevent="submitRejection" class="space-y-4">
                <!-- Rejection Reason Field -->
                <div>
                    <div class="mt-1">
                        {{ $this->rejectionForm }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Please be specific about what needs to be corrected or improved.
                    </p>
                </div>

                <!-- Button Group -->
                <div class="flex justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <x-filament::button type="button" color="gray"
                        x-on:click="$dispatch('close-modal', { id: 'rejection-reason-modal' })">
                        Cancel
                    </x-filament::button>

                    <x-filament::button type="submit" color="danger" class="px-4">
                        <div class="flex items-center gap-2">
                            <x-heroicon-m-x-circle class="w-4 h-4" />
                            <span>Reject Document</span>
                        </div>
                    </x-filament::button>
                </div>
            </form>
        </div>
</x-filament::modal>