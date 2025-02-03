<div class="flex flex-col lg:flex-row h-full bg-gray-50">
    <style>
        @media (max-width: 1024px) {
            .comment-truncate {
                overflow: hidden;
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
            }

            .history-truncate {
                overflow: hidden;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
            }
        }
    </style>

    <!-- Left Section: Document Details & Upload -->
    <div class="order-1 lg:order-1 flex-1 flex flex-col min-w-0 bg-white border-t lg:border-t-0">
        <!-- Document Header Section -->
        <div
            class="sticky top-0 z-10 flex items-center justify-between p-4 sm:p-6 border-b border-gray-100 bg-white min-h-[76px]">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div
                    class="flex-shrink-0 flex items-center justify-center w-10 sm:w-12 h-10 sm:h-12 rounded-xl bg-primary-50/50 ring-1 ring-primary-100">
                    @if($document->submittedDocuments->count() > 0)
                    <x-heroicon-o-document-text class="w-5 sm:w-6 h-5 sm:h-6 text-primary-600" />
                    @else
                    <x-heroicon-o-document-plus class="w-5 sm:w-6 h-5 sm:h-6 text-primary-600" />
                    @endif
                </div>
                <div class="min-w-0">
                    <h3 class="text-base sm:text-xl font-semibold text-gray-900 leading-tight truncate">
                        {{ $document->name }}
                    </h3>
                    @if($document->description)
                    <p class="text-xs sm:text-sm text-gray-500 mt-0.5 line-clamp-1">
                        {{ $document->description }}
                    </p>
                    @endif
                </div>
            </div>

            {{-- Close Button --}}
            <button x-on:click="$dispatch('close-modal', { id: 'document-modal-{{ $document->id }}' })" type="button"
                class="flex-shrink-0 rounded-lg p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors duration-200">
                <span class="sr-only">Close</span>
                <x-heroicon-m-x-mark class="w-5 h-5" />
            </button>
        </div>

        <div class="flex-1 p-4 sm:p-6 space-y-4 sm:space-y-6 overflow-y-auto">
            <!-- Status Section -->
            <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-100">
                <div class="px-4 sm:px-6 py-4 sm:py-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center">
                                <x-heroicon-m-signal class="w-4 h-4 text-gray-600" />
                            </div>
                            <span class="text-sm font-medium text-gray-900">Current Status</span>
                        </div>

                        <div class="w-full sm:w-auto">
                            @if(!auth()->user()->hasRole('staff'))
                            <x-filament::dropdown placement="bottom-end" class="w-full">
                                <x-slot name="trigger">
                                    <x-filament::button size="sm" :color="match($document->status) {
                                            'pending_review' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            default => 'gray'
                                        }" class="w-full sm:w-auto">
                                        <div class="flex items-center gap-2">
                                            {{-- Status Icon --}}
                                            @switch($document->status)
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

                                            {{-- Status Text --}}
                                            <span>
                                                {{ ucwords(str_replace('_', ' ', $document->status ?? 'Not Set')) }}
                                            </span>

                                            {{-- Dropdown Indicator --}}
                                            <x-heroicon-m-chevron-down class="w-4 h-4" />
                                        </div>
                                    </x-filament::button>
                                </x-slot>

                                <x-filament::dropdown.list class="w-full sm:w-auto">
                                    <x-filament::dropdown.list.item wire:click="updateStatus('pending_review')"
                                        icon="heroicon-m-clock"
                                        :color="$document->status === 'pending_review' ? 'warning' : 'gray'">
                                        Pending Review
                                    </x-filament::dropdown.list.item>

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
                                </x-filament::dropdown.list>
                            </x-filament::dropdown>
                            @else
                            <div class="px-3 py-2 text-sm font-medium rounded-lg bg-gray-50 text-gray-500">
                                {{ ucwords(str_replace('_', ' ', $document->status ?? 'Not Set')) }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Section -->
            <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-100">
                <div class="px-4 sm:px-6 py-4 sm:py-5">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center">
                            <x-heroicon-m-arrow-up-tray class="w-4 h-4 text-gray-600" />
                        </div>
                        <h4 class="text-sm font-medium text-gray-900">Upload New Document</h4>
                    </div>

                    <form wire:submit="uploadDocument" class="space-y-4">
                        {{ $this->form }}

                        <div class="flex">
                            <x-filament::button type="submit" size="sm"
                                class="w-full justify-center bg-warning-500 hover:bg-warning-600 text-white focus:ring-warning-500/50">
                                <div class="inline-flex items-center gap-2">
                                    <x-heroicon-m-arrow-up-tray class="w-4 h-4" />
                                    <span class="font-medium">Upload Document</span>
                                </div>
                            </x-filament::button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Document History -->
            @if($document->submittedDocuments->count() > 0)
            <div x-data="{ showMore: false }" class="bg-white rounded-xl shadow-sm ring-1 ring-gray-100">
                <div class="px-3 sm:px-6 py-3 sm:py-5">
                    <!-- Header -->
                    <div class="flex items-center justify-between gap-2 sm:gap-3 mb-3 sm:mb-4">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-gray-50 flex items-center justify-center">
                                <x-heroicon-m-clock class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-600" />
                            </div>
                            <h4 class="text-sm font-medium text-gray-900">Document History</h4>
                        </div>
                    </div>

                    <!-- Document List -->
                    <div class="space-y-2 sm:space-y-3">
                        <div :class="{ 'max-h-[250px] sm:max-h-[300px] overflow-hidden': !showMore && window.innerWidth < 1024 }"
                            class="space-y-2 sm:space-y-3">
                            @foreach($document->submittedDocuments->sortByDesc('created_at') as $submission)
                            <!-- Document Item -->
                            <div
                                class="group flex items-center gap-2 sm:gap-4 p-2 sm:p-3 bg-gray-50/50 rounded-lg hover:bg-gray-50 transition-all">
                                <!-- Document Icon -->
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-white ring-1 ring-gray-100 flex items-center justify-center">
                                        <x-heroicon-o-document-text class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400" />
                                    </div>
                                </div>

                                <!-- Document Info -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs sm:text-sm font-medium text-gray-900 truncate">
                                        {{ basename($submission->file_path) }}
                                    </p>
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mt-0.5">
                                        <span class="text-[11px] sm:text-xs text-gray-500">{{ $submission->user->name
                                            }}</span>
                                        <span class="hidden sm:inline text-xs text-gray-300">&bull;</span>
                                        <span class="text-[11px] sm:text-xs text-gray-500">{{
                                            $submission->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>

                                <!-- View Button -->
                                <div class="flex-shrink-0">
                                    <x-filament::button wire:click="viewDocument({{ $submission->id }})"
                                        x-on:click="$dispatch('open-modal', { id: 'preview-document' })" color="gray"
                                        size="xs" sm:size="sm"
                                        class="opacity-0 group-hover:opacity-100 transition-opacity hover:bg-gray-100">
                                        <div class="inline-flex items-center gap-1 sm:gap-2">
                                            <x-heroicon-m-eye class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                            <span class="hidden sm:inline font-medium">View</span>
                                        </div>
                                    </x-filament::button>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Show More/Less button -->
                        @if($document->submittedDocuments->count() > 3)
                        <div class="lg:hidden text-center">
                            <button x-show="!showMore" x-on:click="showMore = true"
                                class="w-full py-1.5 sm:py-2 text-xs sm:text-sm text-primary-600 hover:text-primary-700 font-medium">
                                Show More History
                            </button>
                            <button x-show="showMore" x-on:click="showMore = false"
                                class="w-full py-1.5 sm:py-2 text-xs sm:text-sm text-primary-600 hover:text-primary-700 font-medium">
                                Show Less
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Right Section: Comments -->
    <!-- Right Section: Comments -->
    <div
        class="order-2 lg:order-2 lg:w-[400px] border-t lg:border-t-0 lg:border-l border-gray-100 flex flex-col bg-white">
        <!-- Comments Header -->
        <div class="sticky top-0 z-10 flex items-center p-4 sm:p-6 border-b border-gray-100 bg-white min-h-[76px]">
            <div class="flex items-center gap-3">
                <div
                    class="flex-shrink-0 flex items-center justify-center w-10 sm:w-12 h-10 sm:h-12 rounded-xl bg-primary-50/50 ring-1 ring-primary-100">
                    <x-heroicon-m-chat-bubble-left-right class="w-5 sm:w-6 h-5 sm:h-6 text-primary-600" />
                </div>
                <div class="flex items-center gap-2">
                    <h3 class="text-base sm:text-xl font-semibold text-gray-900">Comments</h3>
                    <span
                        class="inline-flex items-center justify-center px-2.5 py-0.5 text-xs font-medium bg-primary-50 text-primary-600 rounded-full">
                        {{ $document->comments()->count() }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Comments List -->
        <div class="flex-1 p-4 sm:p-6 overflow-y-auto bg-gray-50/50">
            <div class="space-y-6">
                @forelse($document->comments()->orderBy('created_at', 'desc')->get() as $comment)
                <div class="flex gap-4 group">
                    <div class="flex-shrink-0">
                        <div @class([ 'w-8 h-8 rounded-lg flex items-center justify-center'
                            , 'bg-primary-50 ring-1 ring-primary-100'=> $comment->user_id === auth()->id(),
                            'bg-gray-100 ring-1 ring-gray-200' => $comment->user_id !== auth()->id(),
                            ])>
                            <span @class([ 'text-xs font-medium' , 'text-primary-600'=> $comment->user_id ===
                                auth()->id(),
                                'text-gray-600' => $comment->user_id !== auth()->id(),
                                ])>
                                {{ substr($comment->user->name ?? 'U', 0, 1) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div @class([ 'bg-white rounded-lg px-4 py-3 shadow-sm ring-1 transition-all'
                            , 'ring-primary-100 hover:ring-primary-200'=> $comment->user_id === auth()->id(),
                            'ring-gray-100 hover:ring-gray-200' => $comment->user_id !== auth()->id(),
                            ])>
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ $comment->user->name ?? 'Unknown User' }}
                                    </span>
                                    @if($comment->user_id === auth()->id())
                                    <span
                                        class="text-xs font-medium text-primary-600 bg-primary-50 px-1.5 py-0.5 rounded">
                                        You
                                    </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400">
                                        {{ $comment->created_at->diffForHumans() }}
                                    </span>
                                    @if($comment->user_id === auth()->id())
                                    <button
                                        class="opacity-0 group-hover:opacity-100 transition-opacity text-gray-400 hover:text-gray-600">
                                        <x-heroicon-m-ellipsis-vertical class="w-4 h-4" />
                                    </button>
                                    @endif
                                </div>
                            </div>
                            <div class="prose prose-sm max-w-none mt-1 text-gray-600">
                                {!! nl2br(e($comment->content)) !!}
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12">
                    <div class="w-12 h-12 mx-auto mb-4 rounded-xl bg-gray-100 flex items-center justify-center">
                        <x-heroicon-o-chat-bubble-left-right class="w-6 h-6 text-gray-400" />
                    </div>
                    <h4 class="text-sm font-medium text-gray-900 mb-1">No comments yet</h4>
                    <p class="text-xs text-gray-500">Be the first to comment on this document</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Comment Input -->
        <div class="sticky bottom-0 p-4 bg-white border-t border-gray-100">
            <form wire:submit="addComment">
                <div class="relative">
                    <x-filament::input wire:model="newComment" type="text" placeholder="Write a comment..."
                        class="w-full pr-16 rounded-lg border-gray-200 focus:border-amber-500 focus:ring focus:ring-amber-500/20"
                        x-on:keydown.enter.prevent="$wire.addComment()" />
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                        <x-filament::button type="submit" size="sm"
                            class="gap-2 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600">
                            <x-heroicon-m-paper-airplane
                                class="w-4 h-4 transition-transform group-hover:translate-x-1" />
                            <span class="sr-only">Send comment</span>
                        </x-filament::button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <!-- Document Preview Modal -->
    <x-filament::modal id="preview-document" wire:model="isPreviewModalOpen" width="7xl">
        <x-slot name="header">
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center ring-1 ring-primary-100">
                        @if($fileType === 'pdf')
                        <x-heroicon-o-document-text class="w-6 h-6 text-primary-600" />
                        @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
                        <x-heroicon-o-photo class="w-6 h-6 text-primary-600" />
                        @else
                        <x-heroicon-o-document class="w-6 h-6 text-primary-600" />
                        @endif
                    </div>
                    @if($previewingDocument)
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 leading-tight">
                            {{ basename($previewingDocument->file_path) }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Uploaded {{ $previewingDocument->created_at->diffForHumans() }}
                        </p>
                    </div>
                    @endif
                </div>

                @if($previewingDocument)
                <div class="flex items-center gap-3 flex-shrink-0 mr-8">
                    <!-- Status Dropdown -->
                    @if(!auth()->user()->hasRole('staff'))
                    <x-filament::dropdown placement="bottom-end">
                        <x-slot name="trigger">
                            <x-filament::button size="sm" :color="match($document->status) {
                                'pending_review' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray'
                            }">
                                <div class="flex items-center gap-2">
                                    {{-- Status Icon --}}
                                    @switch($document->status)
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

                                    {{-- Status Text --}}
                                    <span>{{ ucwords(str_replace('_', ' ', $document->status ?? 'Not Set')) }}</span>
                                    <x-heroicon-m-chevron-down class="w-4 h-4" />
                                </div>
                            </x-filament::button>
                        </x-slot>

                        <x-filament::dropdown.list>
                            <x-filament::dropdown.list.item wire:click="updateStatus('pending_review')"
                                icon="heroicon-m-clock"
                                :color="$document->status === 'pending_review' ? 'warning' : 'gray'">
                                Pending Review
                            </x-filament::dropdown.list.item>

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
                        </x-filament::dropdown.list>
                    </x-filament::dropdown>
                    @endif

                    <!-- Download Button -->
                    <x-filament::button wire:click="downloadDocument({{ $previewingDocument->id }})" color="gray"
                        size="sm">
                        <div class="inline-flex items-center gap-2">
                            <x-heroicon-m-arrow-down-tray class="h-4 w-4" />
                            <span>Download</span>
                        </div>
                    </x-filament::button>
                </div>
                @endif
            </div>
        </x-slot>

        <!-- Preview Content -->
        <div class="p-6">
            @if($previewUrl)
            <div class="relative rounded-xl overflow-hidden bg-gray-50 ring-1 ring-gray-200">
                @if($fileType === 'pdf')
                <div class="w-full h-[calc(100vh-16rem)] bg-gray-50">
                    <iframe src="{{ $previewUrl }}" class="w-full h-full rounded-lg" frameborder="0">
                    </iframe>
                </div>
                @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
                <div class="relative aspect-video flex items-center justify-center bg-gray-50">
                    <img src="{{ $previewUrl }}" alt="Document Preview"
                        class="max-w-full max-h-[calc(100vh-16rem)] object-contain rounded-lg shadow-sm">
                </div>
                @else
                <div class="flex flex-col items-center justify-center py-16">
                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                        <x-heroicon-o-document class="w-8 h-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        Preview not available
                    </h3>
                    <p class="text-sm text-gray-500 mb-4">
                        This file type cannot be previewed directly in the browser
                    </p>
                    @if($previewingDocument)
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
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-16">
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center animate-pulse">
                    <x-heroicon-o-document class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-4">
                    Loading preview...
                </h3>
            </div>
            @endif
        </div>
    </x-filament::modal>

    <!-- Add this to your layout -->
    <script>
        document.addEventListener('livewire:initialized', () => {
                Livewire.on('download-file', ({ url, name }) => {
                    const link = document.createElement('a')
                    link.href = url
                    link.download = name
                    document.body.appendChild(link)
                    link.click()
                    document.body.removeChild(link)
                })
            })
    </script>
</div>