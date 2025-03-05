<div>
    <x-filament::modal id="documentModal" slide-over width='4xl'>
        @if ($document)
        <div class="flex flex-col lg:flex-row h-full bg-gray-50 dark:bg-gray-900">
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
            <div
                class="order-1 lg:order-1 flex-1 flex flex-col min-w-0 bg-white dark:bg-gray-900 border-t lg:border-t-0 dark:border-gray-700">
                <!-- Document Header Section -->
                <div
                    class="sticky top-0 z-10 flex items-center justify-between p-4 sm:p-6 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-900 min-h-[76px]">
                    <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                        <!-- Document Icon -->
                        <div
                            class="flex-shrink-0 flex items-center justify-center w-10 sm:w-12 h-10 sm:h-12 rounded-xl bg-primary-950/20 dark:bg-primary-900/20 ring-1 ring-primary-900/10 dark:ring-primary-400/10">
                            @if($document->submittedDocuments->count() > 0)
                            <x-heroicon-o-document-text
                                class="w-5 sm:w-6 h-5 sm:h-6 text-primary-600 dark:text-primary-400" />
                            @else
                            <x-heroicon-o-document-plus
                                class="w-5 sm:w-6 h-5 sm:h-6 text-primary-600 dark:text-primary-400" />
                            @endif
                        </div>

                        <!-- Document Info -->
                        <div class="min-w-0 space-y-1">
                            <!-- Project & Client Info -->
                            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                <a href="{{ route('filament.admin.resources.clients.view', $document->projectStep->project->client->id) }}"
                                    class="font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 hover:underline transition-colors">
                                    {{ $document->projectStep->project->client->name }}
                                </a>
                                <span>&bull;</span>
                                <a href="{{ route('filament.admin.resources.projects.view', $document->projectStep->project->id) }}"
                                    class="truncate hover:text-gray-700 dark:hover:text-gray-300 hover:underline transition-colors">
                                    {{ $document->projectStep->project->name }}
                                </a>
                            </div>

                            <!-- Document Name & Step -->
                            <div class="flex items-center gap-2">
                                <a href="{{ route('filament.admin.resources.projects.view', $document->projectStep->project->id) }}"
                                    class="text-base sm:text-xl font-semibold text-gray-900 dark:text-white leading-tight truncate hover:text-primary-600 dark:hover:text-primary-400 hover:underline transition-colors">
                                    {{ $document->name }}
                                </a>
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                                    Step {{ $document->projectStep->order }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="flex-1 p-4 sm:p-6 space-y-4 sm:space-y-6 overflow-y-auto">
                    <!-- Status Section -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700">
                        {{-- Status Section --}}
                        <div class="px-4 sm:px-6 py-4 sm:py-5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-center justify-center">
                                        <x-heroicon-m-signal class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">Current
                                        Status</span>
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

                                                    <span>{{ ucwords(str_replace('_', ' ', $document->status ?? 'Not
                                                        Set'))
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
                                    <div
                                        class="px-3 py-2 text-sm font-medium rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
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
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Reviewer Section --}}
                        @if($document->reviewer_id && in_array($document->status, ['pending_review', 'approved',
                        'rejected']))
                        <div class="px-4 sm:px-6 py-3 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-center justify-center">
                                    <x-heroicon-m-user class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">Reviewer</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $document->reviewer->name
                                        }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Upload Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700">
                        <div class="px-4 sm:px-6 py-4 sm:py-5">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">
                                <div
                                    class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-center justify-center">
                                    <x-heroicon-m-arrow-up-tray class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                                </div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Upload New Document</h4>
                            </div>

                            <form wire:submit="uploadDocument" class="space-y-4">
                                {{ $this->uploadFileForm }}

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
                    <!-- Document List with better responsiveness -->
                    <div class="space-y-2 sm:space-y-3">
                        <div :class="{ 'max-h-[250px] sm:max-h-[300px] overflow-hidden': window.innerWidth < 1024 }"
                            class="space-y-2 sm:space-y-3">
                            @foreach($document->submittedDocuments->sortByDesc('created_at') as $submission)
                            <!-- Document Item -->
                            <div
                                class="group flex items-center gap-2 sm:gap-4 p-2 sm:p-3 bg-gray-50/50 dark:bg-gray-800/50 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                                <!-- Document Icon -->
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-white dark:bg-gray-800 ring-1 ring-gray-100 dark:ring-gray-700 flex items-center justify-center">
                                        <x-heroicon-o-document-text
                                            class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 dark:text-gray-500" />
                                    </div>
                                </div>

                                <!-- Document Info -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ basename($submission->file_path) }}
                                    </p>
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mt-0.5">
                                        <span class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400">{{
                                            $submission->user->name }}</span>
                                        <span
                                            class="hidden sm:inline text-xs text-gray-300 dark:text-gray-600">&bull;</span>
                                        <span class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400">{{
                                            $submission->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex-shrink-0 flex items-center gap-1.5 sm:gap-2">
                                    <!-- View Button -->
                                    <x-filament::button wire:click="viewDocument({{ $submission->id }})"
                                        x-on:click="$dispatch('open-modal', { id: 'preview-document' })" color="gray"
                                        size="xs"
                                        class="opacity-0 group-hover:opacity-100 transition-opacity hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <div class="inline-flex items-center gap-1 sm:gap-2">
                                            <x-heroicon-m-eye class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                            <span class="hidden sm:inline font-medium">View</span>
                                        </div>
                                    </x-filament::button>

                                    <!-- Remove Button with Filament Modal -->
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
                                    <x-filament::modal id="delete-document-{{ $submission->id }}" alignment="center"
                                        width="sm">
                                        <x-slot name="header">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="flex-shrink-0 w-10 h-10 rounded-full bg-red-50 dark:bg-red-900 flex items-center justify-center">
                                                    <x-heroicon-o-exclamation-triangle
                                                        class="w-5 h-5 text-red-500 dark:text-red-400" />
                                                </div>
                                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                                    Delete Document
                                                </h3>
                                            </div>
                                        </x-slot>

                                        <div class="space-y-3">
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Are you sure you want to delete this document? This action cannot be
                                                undone.
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

                                                <x-filament::button wire:click="removeDocument({{ $submission->id }})"
                                                    color="danger" size="sm">
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

                        <!-- Show More/Less button with improved responsiveness -->
                        @if($document->submittedDocuments->count() > 3)
                        <div class="lg:hidden text-center mt-2">
                            <button x-show="!showMore" x-on:click="showMore = true"
                                class="w-full py-1.5 sm:py-2 text-xs sm:text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Show More History
                            </button>
                            <button x-show="showMore" x-on:click="showMore = false"
                                class="w-full py-1.5 sm:py-2 text-xs sm:text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Show Less
                            </button>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Right Section: Comments -->
            <div
                class="order-2 lg:order-2 lg:w-[400px] border-t lg:border-t-0 lg:border-l border-gray-100 dark:border-gray-700 flex flex-col bg-white dark:bg-gray-900">
                <!-- Comments Header -->
                <div
                    class="sticky top-0 z-10 flex items-center p-4 sm:p-6 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-900 min-h-[76px]">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex-shrink-0 flex items-center justify-center w-10 sm:w-12 h-10 sm:h-12 rounded-xl bg-primary-50/50 dark:bg-primary-900/20 ring-1 ring-primary-100 dark:ring-primary-800">
                            <x-heroicon-m-chat-bubble-left-right
                                class="w-5 sm:w-6 h-5 sm:h-6 text-primary-600 dark:text-primary-400" />
                        </div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base sm:text-xl font-semibold text-gray-900 dark:text-white">Comments</h3>
                            <span
                                class="inline-flex items-center justify-center px-2.5 py-0.5 text-xs font-medium bg-primary-50 dark:bg-primary-900 text-primary-600 dark:text-primary-400 rounded-full">
                                {{ $document->comments()->count() }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Comments List -->
                <div class="flex-1 p-4 sm:p-6 overflow-y-auto bg-gray-50/50 dark:bg-gray-900/50">
                    <div class="space-y-6">
                        @forelse($document->comments()->orderBy('created_at', 'desc')->get() as $comment)
                        <div class="flex gap-4 group">
                            <div class="flex-shrink-0">
                                <div @class([ 'w-8 h-8 rounded-lg flex items-center justify-center'
                                    , 'bg-primary-50 dark:bg-primary-900 ring-1 ring-primary-100 dark:ring-primary-800'=>
                                    $comment->user_id === auth()->id(),
                                    'bg-gray-100 dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700' =>
                                    $comment->user_id
                                    !== auth()->id(),
                                    ])>
                                    <span @class([ 'text-xs font-medium' , 'text-primary-600 dark:text-primary-400'=>
                                        $comment->user_id === auth()->id(),
                                        'text-gray-600 dark:text-gray-400' => $comment->user_id !== auth()->id(),
                                        ])>
                                        {{ substr($comment->user->name ?? 'U', 0, 1) }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div @class([ 'bg-white dark:bg-gray-800 rounded-lg px-4 py-3 shadow-sm ring-1 transition-all'
                                    , 'ring-primary-100 dark:ring-primary-800 hover:ring-primary-200 dark:hover:ring-primary-700'=>
                                    $comment->user_id === auth()->id(),
                                    'ring-gray-100 dark:ring-gray-700 hover:ring-gray-200 dark:hover:ring-gray-600' =>
                                    $comment->user_id !== auth()->id(),
                                    ])>
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $comment->user->name ?? 'Unknown User' }}
                                            </span>
                                            @if($comment->user_id === auth()->id())
                                            <span
                                                class="text-xs font-medium text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/50 px-1.5 py-0.5 rounded">
                                                You
                                            </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                                {{ $comment->created_at->diffForHumans() }}
                                            </span>
                                            @if($comment->user_id === auth()->id())
                                            <div class="relative" x-data="{ isOpen: false }">
                                                <!-- Menu Button -->
                                                <button @click="isOpen = !isOpen" @click.away="isOpen = false"
                                                    class="opacity-0 group-hover:opacity-100 transition-opacity text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400">
                                                    <x-heroicon-m-ellipsis-vertical class="w-4 h-4" />
                                                </button>

                                                <!-- Dropdown Menu -->
                                                <div x-show="isOpen"
                                                    x-transition:enter="transition ease-out duration-100"
                                                    x-transition:enter-start="transform opacity-0 scale-95"
                                                    x-transition:enter-end="transform opacity-100 scale-100"
                                                    x-transition:leave="transition ease-in duration-75"
                                                    x-transition:leave-start="transform opacity-100 scale-100"
                                                    x-transition:leave-end="transform opacity-0 scale-95"
                                                    class="absolute right-0 z-10 mt-1 w-36 origin-top-right rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-gray-200 dark:ring-gray-700 focus:outline-none">
                                                    <div class="py-1">
                                                        <!-- Edit Button -->
                                                        <button wire:click="editComment({{ $comment->id }})"
                                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white">
                                                            <x-heroicon-m-pencil-square class="w-4 h-4" />
                                                            Edit
                                                        </button>

                                                        <!-- Delete Button -->
                                                        <button wire:click="deleteComment({{ $comment->id }})"
                                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30">
                                                            <x-heroicon-m-trash class="w-4 h-4" />
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div
                                        class="prose prose-sm dark:prose-invert max-w-none mt-1 text-gray-600 dark:text-gray-300">
                                        {!! $comment->content !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <div
                                class="w-12 h-12 mx-auto mb-4 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                <x-heroicon-o-chat-bubble-left-right class="w-6 h-6 text-gray-400 dark:text-gray-500" />
                            </div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">No comments yet</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Be the first to comment on this document
                            </p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Comment Input -->
                <!-- Comment Input -->
                <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700"
                    x-data="{ showCommentForm: false }">
                    <!-- Comment Toggle Button -->
                    <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
                        <button @click="showCommentForm = !showCommentForm"
                            class="w-full flex items-center justify-center gap-2 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                            <div class="flex items-center gap-2" x-show="!showCommentForm">
                                <x-heroicon-m-chat-bubble-left-right class="w-4 h-4" />
                                <span>Add Comment</span>
                            </div>
                            <div class="flex items-center gap-2" x-show="showCommentForm">
                                <x-heroicon-m-x-mark class="w-4 h-4" />
                                <span>Close</span>
                            </div>
                        </button>
                    </div>

                    <!-- Comment Form -->
                    <div x-show="showCommentForm" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform translate-y-2" class="p-4">
                        <form wire:submit="addComment">
                            <!-- RichEditor Container with custom styling -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg ring-1 ring-gray-200 dark:ring-gray-700">
                                <div class="relative">
                                    {{ $this->createCommentForm }}
                                </div>

                                <!-- Button Container -->
                                <div
                                    class="px-3 py-2 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 rounded-b-lg">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Clear button -->
                                        <x-filament::button wire:click="$set('commentData.newComment', '')"
                                            type="button" color="gray" size="sm">
                                            Clear
                                        </x-filament::button>

                                        <!-- Submit button -->
                                        <x-filament::button type="submit" size="sm" icon="heroicon-m-paper-airplane"
                                            class="inline-flex items-center bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 dark:from-amber-500 dark:to-amber-400 dark:hover:from-amber-600 dark:hover:to-amber-500">
                                            <span class="mr-2">Send</span>
                                        </x-filament::button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <!-- Document Preview Modal -->
            <x-filament::modal id="preview-document" wire:model="isPreviewModalOpen" width="7xl">
                <x-slot name="header">
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-primary-50 dark:bg-primary-900/50 flex items-center justify-center ring-1 ring-primary-100 dark:ring-primary-800">
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
                        <div class="flex items-center gap-3 flex-shrink-0 mr-8">
                            <!-- Status Dropdown -->
                            @if(!auth()->user()->hasRole(['staff', 'client']))
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
                                            <span>{{ ucwords(str_replace('_', ' ', $document->status ?? 'Not Set'))
                                                }}</span>
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
                            <x-filament::button wire:click="downloadDocument({{ $previewingDocument->id }})"
                                color="gray" size="sm">
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
                    <div
                        class="relative rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-900 ring-1 ring-gray-200 dark:ring-gray-700">
                        @if($fileType === 'pdf')
                        <div class="w-full h-[calc(100vh-16rem)] bg-gray-50 dark:bg-gray-900">
                            <iframe src="{{ $previewUrl }}" class="w-full h-full rounded-lg">
                                <div class="flex flex-col items-center justify-center p-8">
                                    <div
                                        class="w-16 h-16 rounded-full bg-primary-50 dark:bg-primary-900 flex items-center justify-center mb-4">
                                        <x-heroicon-o-document-text
                                            class="w-8 h-8 text-primary-600 dark:text-primary-400" />
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2 text-center">
                                        Unable to display PDF
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 text-center">
                                        The PDF viewer is not supported on this device
                                    </p>
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <x-filament::button tag="a" href="{{ $previewUrl }}" target="_blank"
                                            color="primary" size="sm">
                                            <x-heroicon-m-eye class="w-4 h-4 mr-2" />
                                            Open in New Tab
                                        </x-filament::button>

                                        @if($previewingDocument)
                                        <x-filament::button wire:click="downloadDocument({{ $previewingDocument->id }})"
                                            color="gray" size="sm">
                                            <x-heroicon-m-arrow-down-tray class="w-4 h-4 mr-2" />
                                            Download
                                        </x-filament::button>
                                        @endif
                                    </div>
                                </div>
                            </iframe>
                        </div>
                        @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
                        <div class="relative aspect-video flex items-center justify-center bg-gray-50 dark:bg-gray-900">
                            <img src="{{ $previewUrl }}" alt="Document Preview"
                                class="max-w-full max-h-[calc(100vh-16rem)] object-contain rounded-lg shadow-sm">
                        </div>
                        @else
                        <div class="flex flex-col items-center justify-center py-16">
                            <div
                                class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                                <x-heroicon-o-document class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                Preview not available
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
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
        @endif
    </x-filament::modal>
</div>