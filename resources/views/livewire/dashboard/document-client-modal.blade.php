<div>
    <!-- Filament Modal -->
    <div x-data="{ 
        showMore: false,
        init() {
            this.$watch('$wire.isOpen', value => {
                if (value) {
                    document.body.classList.add('modal-open');
                } else {
                    document.body.classList.remove('modal-open');
                }
            });
        }
    }">
        <!-- Upload Button -->
        <button wire:click="toggleModal"
            class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg text-primary-600 bg-primary-50 hover:bg-primary-100 transition-colors duration-200">
            <x-heroicon-m-document-plus class="w-4 h-4" />
            <span>Upload</span>
        </button>

        <!-- Custom Modal -->
        <div x-show="$wire.isOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50" style="display: none;">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-backdrop"
                @click="$wire.toggleModal()"></div>

            <!-- Modal Container -->
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="$wire.isOpen" @click.away="$wire.toggleModal()"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-5xl modal-content">

                        <!-- Modal Header -->
                        <div class="border-b border-gray-100 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-900">Client Documents</h2>
                                <button wire:click="toggleModal" class="text-gray-400 hover:text-gray-500">
                                    <x-heroicon-o-x-mark class="h-5 w-5" />
                                </button>
                            </div>
                        </div>

                        <!-- Modal Content -->
                        <div class="px-6 py-5 space-y-6">
                            <!-- Upload Section -->
                            <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                                <h3 class="text-sm font-medium text-gray-900 mb-4">Upload Dokumen</h3>
                                <form wire:submit="uploadDocument" class="space-y-4">
                                    {{ $this->form }}

                                    <div class="flex justify-end gap-x-2">
                                        <button type="submit"
                                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition duration-150">
                                            <x-heroicon-m-arrow-up-tray class="w-4 h-4" />
                                            Upload Documents
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Document History -->
                            <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-100">
                                <div class="px-5 py-4">
                                    <!-- Header -->
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-lg bg-primary-50 flex items-center justify-center">
                                                <x-heroicon-m-clock class="w-4 h-4 text-primary-600" />
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-900">Document History</h4>
                                                <p class="text-xs text-gray-500 mt-0.5">{{
                                                    $this->client_documents->count() }} documents total</p>
                                            </div>
                                        </div>

                                        <!-- Search -->
                                        <div class="relative">
                                            <input type="text" wire:model.live.debounce.300ms="search"
                                                class="w-64 pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                                                placeholder="Search documents...">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <x-heroicon-m-magnifying-glass class="h-4 w-4 text-gray-400" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Document List -->
                                    <div class="space-y-3">
                                        <div :class="{'max-h-[400px]': !showMore, 'max-h-none': showMore}"
                                            class="space-y-2 overflow-y-auto transition-all duration-300 pr-2">
                                            @forelse($this->client_documents as $client_document)
                                            <div
                                                class="group flex items-center gap-4 p-3 bg-gray-50/50 rounded-lg hover:bg-gray-50 transition-all border border-transparent hover:border-gray-100">
                                                <!-- Document Icon -->
                                                <div class="flex-shrink-0">
                                                    <div @class([ 'w-10 h-10 rounded-lg ring-1 ring-gray-100 flex items-center justify-center'
                                                        , 'bg-blue-50 text-blue-600'=>
                                                        Str::endsWith($client_document->file_path, ['.doc', '.docx']),
                                                        'bg-red-50 text-red-600' =>
                                                        Str::endsWith($client_document->file_path, '.pdf'),
                                                        'bg-green-50 text-green-600' =>
                                                        Str::endsWith($client_document->file_path, ['.xlsx', '.xls']),
                                                        'bg-purple-50 text-purple-600' =>
                                                        Str::endsWith($client_document->file_path, ['.jpg', '.jpeg',
                                                        '.png', '.gif']),
                                                        'bg-gray-50 text-gray-600' =>
                                                        !Str::endsWith($client_document->file_path, ['.doc', '.docx',
                                                        '.pdf', '.xlsx', '.xls', '.jpg', '.jpeg', '.png', '.gif']),
                                                        ])>
                                                        @if(Str::endsWith($client_document->file_path, ['.jpg', '.jpeg',
                                                        '.png', '.gif']))
                                                        <x-heroicon-o-photo class="w-5 h-5" />
                                                        @else
                                                        <x-heroicon-o-document-text class="w-5 h-5" />
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Document Info -->
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">
                                                        {{ basename($client_document->file_path) }}
                                                    </p>
                                                    <div class="flex items-center gap-2 mt-0.5">
                                                        <span class="text-xs text-gray-500">
                                                            {{ $client_document->user->name }}
                                                        </span>
                                                        <span class="text-xs text-gray-300">&bull;</span>
                                                        <span class="text-xs text-gray-500">
                                                            {{ $client_document->created_at->diffForHumans() }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- Actions -->
                                                <div class="flex-shrink-0 flex items-center gap-2">
                                                    <button
                                                        wire:click="previewDocument('{{ $client_document->file_path }}')"
                                                        class="opacity-0 group-hover:opacity-100 transition-opacity inline-flex items-center justify-center h-8 w-8 rounded-lg text-gray-400 hover:text-primary-500 hover:bg-primary-50">
                                                        <x-heroicon-m-eye class="w-4 h-4" />
                                                    </button>
                                                    <button wire:click="downloadDocument({{ $client_document->id }})"
                                                        class="opacity-0 group-hover:opacity-100 transition-opacity inline-flex items-center justify-center h-8 w-8 rounded-lg text-gray-400 hover:text-primary-500 hover:bg-primary-50">
                                                        <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                                                    </button>
                                                    <button wire:click="deleteDocument({{ $client_document->id }})"
                                                        wire:confirm="Are you sure you want to delete this document?"
                                                        class="opacity-0 group-hover:opacity-100 transition-opacity inline-flex items-center justify-center h-8 w-8 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50">
                                                        <x-heroicon-m-trash class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </div>
                                            @empty
                                            <div class="text-center py-12">
                                                <div class="flex flex-col items-center">
                                                    <div class="rounded-full bg-primary-50 p-3">
                                                        <x-heroicon-o-document-plus class="w-6 h-6 text-primary-600" />
                                                    </div>
                                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No documents yet
                                                    </h3>
                                                    <p class="mt-1 text-sm text-gray-500">Upload your first document to
                                                        get started.</p>
                                                </div>
                                            </div>
                                            @endforelse
                                        </div>

                                        @if($this->client_documents->count() > 3)
                                        <div class="text-center pt-3 border-t border-gray-100">
                                            <button @click="showMore = !showMore"
                                                class="inline-flex items-center gap-1 text-sm text-primary-600 hover:text-primary-700 font-medium">
                                                <span x-text="showMore ? 'Show Less' : 'Show More'"></span>

                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        @if($showPreview)
        <div class="fixed inset-0 z-[60] overflow-y-auto">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-backdrop"
                @click="$wire.closePreview()"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative bg-white rounded-lg max-w-4xl w-full modal-content"
                        @click.away="$wire.closePreview()">
                        <div class="absolute top-0 right-0 pt-4 pr-4">
                            <button wire:click="closePreview" class="rounded-md text-gray-400 hover:text-gray-500">
                                <span class="sr-only">Close</span>
                                <x-heroicon-o-x-mark class="h-6 w-6" />
                            </button>
                        </div>
                        <div class="h-[80vh] w-full p-4">
                            @if(Str::endsWith($previewUrl, ['.jpg', '.jpeg', '.png', '.gif']))
                            <img src="{{ $previewUrl }}" class="w-full h-full object-contain rounded-lg"
                                alt="Document preview">
                            @else
                            <iframe src="{{ $previewUrl }}" class="w-full h-full rounded-lg" frameborder="0"></iframe>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <style>
        body.modal-open {
            overflow: hidden !important;
        }

        .modal-backdrop {
            pointer-events: all !important;
        }

        .modal-content {
            pointer-events: all !important;
            z-index: 51;
            position: relative;
        }
    </style>
</div>