<div class="p-4 space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between gap-4">
            <!-- Left side - Stats -->
            <div class="flex items-center gap-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Client Documents</h2>
                    <p class="mt-1 text-sm text-gray-500">Manage and organize client documentation</p>
                </div>
                <div class="h-10 w-px bg-gray-200"></div>
                <div class="flex items-center gap-4">
                    <div class="px-3 py-1 rounded-full bg-primary-50 border border-primary-100">
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-primary-500"></div>
                            <span class="text-sm font-medium text-primary-700">{{ $documents->total() }}
                                Documents</span>
                        </div>
                    </div>
                    <div class="px-3 py-1 rounded-full bg-gray-50 border border-gray-200">
                        <div class="flex items-center gap-2">
                            <x-heroicon-m-arrow-up-circle class="w-4 h-4 text-gray-400" />
                            <span class="text-sm font-medium text-gray-600">{{ $documents->where('created_at', '>=',
                                now()->subDays(30))->count() }} New this month</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right side - Actions -->
            <div class="flex items-center gap-3">
                <x-filament::button x-on:click="$dispatch('open-modal', { id: 'upload-document-modal' })"
                    icon="heroicon-m-arrow-up-tray">
                    Upload Document
                </x-filament::button>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="mt-6 flex items-center gap-4">
            <div class="flex-1">
                <x-filament::input type="search" wire:model.live.debounce.300ms="search"
                    placeholder="Search documents..." icon="heroicon-m-magnifying-glass" />
            </div>
            <div class="flex items-center gap-3">
                {{-- <x-filament::select wire:model.live="filters.dateRange">
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="year">This Year</option>
                </x-filament::select>

                <x-filament::select wire:model.live="filters.uploadedBy">
                    <option value="">All Users</option>
                    @foreach($client->users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </x-filament::select> --}}
            </div>
        </div>
    </div>

    <!-- Documents Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($documents as $document)
        <div wire:key="document-{{ $document->id }}"
            class="group relative bg-white rounded-xl border border-gray-200 shadow-sm transition-all duration-300 hover:shadow-md hover:scale-[1.02]">
            <!-- Document Preview -->
            <div class="aspect-[4/3] rounded-t-xl bg-gray-100 border-b border-gray-200">
                @if(in_array(strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png',
                'gif']))
                <img src="{{ Storage::disk('public')->url($document->file_path) }}" alt="Document Preview"
                    class="w-full h-full object-cover rounded-t-xl" />
                @else
                <div class="w-full h-full flex items-center justify-center">
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto rounded-full bg-gray-200 flex items-center justify-center">
                            @switch(strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION)))
                            @case('pdf')
                            <x-heroicon-o-document-text class="w-8 h-8 text-gray-400" />
                            @break
                            @case('doc')
                            @case('docx')
                            <x-heroicon-o-document class="w-8 h-8 text-gray-400" />
                            @break
                            @default
                            <x-heroicon-o-paper-clip class="w-8 h-8 text-gray-400" />
                            @endswitch
                        </div>
                        <span class="mt-2 block text-sm font-medium text-gray-900">
                            {{ strtoupper(pathinfo($document->file_path, PATHINFO_EXTENSION)) }}
                        </span>
                    </div>
                </div>
                @endif

                <!-- Hover Overlay -->
                <div
                    class="absolute inset-0 bg-gray-900/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-t-xl flex items-center justify-center gap-2">
                    <button wire:click="viewDocument({{ $document->id }})"
                        x-on:click="$dispatch('open-modal', { id: 'preview-document-modal' })"
                        class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors">
                        <x-heroicon-m-eye class="w-5 h-5 text-white" />
                    </button>
                    <button wire:click="downloadDocument({{ $document->id }})"
                        class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors">
                        <x-heroicon-m-arrow-down-tray class="w-5 h-5 text-white" />
                    </button>
                    <button wire:click="deleteDocument({{ $document->id }})"
                        class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors">
                        <x-heroicon-m-trash class="w-5 h-5 text-white" />
                    </button>
                </div>
            </div>

            <!-- Document Info -->
            <div class="p-4">
                <h3 class="font-medium text-gray-900 truncate">
                    {{ basename($document->file_path) }}
                </h3>
                <div class="mt-1 flex items-center gap-4 text-sm text-gray-500">
                    <span class="flex items-center gap-1">
                        <x-heroicon-m-clock class="w-4 h-4" />
                        {{ $document->created_at->diffForHumans() }}
                    </span>
                    <span class="flex items-center gap-1">
                        <x-heroicon-m-user class="w-4 h-4" />
                        {{ $document->user->name }}
                    </span>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="text-center py-12 bg-white rounded-xl border border-gray-200">
                <div class="w-16 h-16 mx-auto rounded-full bg-gray-100 flex items-center justify-center">
                    <x-heroicon-o-document-text class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="mt-4 text-sm font-medium text-gray-900">No documents found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by uploading a document.</p>
                <div class="mt-6">
                    <x-filament::button x-on:click="$dispatch('open-modal', { id: 'upload-document-modal' })"
                        icon="heroicon-m-arrow-up-tray">
                        Upload Document
                    </x-filament::button>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($documents->hasPages())
    <div class="mt-6">
        {{ $documents->links() }}
    </div>
    @endif

    

    <x-filament::modal id="preview-document-modal" width="7xl">
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
                    <iframe src="{{ $previewUrl }}" class="w-full h-full rounded-lg" frameborder="0"></iframe>
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
</div>