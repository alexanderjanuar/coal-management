<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/50 rounded-xl flex items-center justify-center">
                    <x-heroicon-o-document-text class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Dokumen Legal</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $documents->count() }} dokumen untuk {{ $client->name }}
                        <span class="text-gray-400 dark:text-gray-500">• Proyek: {{ $project->name }}</span>
                    </p>
                </div>
            </div>
            
            <!-- Upload Button using Filament Action -->
            {{ $this->uploadAction }}
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-heroicon-m-magnifying-glass class="w-5 h-5 text-gray-400 dark:text-gray-500" />
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari dokumen..."
                    class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400 transition-colors"
                />
            </div>

            <!-- File Type Filter -->
            <select 
                wire:model.live="fileTypeFilter" 
                class="block w-full px-3 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400 transition-colors">
                <option value="">Semua Tipe File</option>
                <option value="pdf">PDF</option>
                <option value="image">Gambar</option>
                <option value="document">Dokumen Word</option>
                <option value="spreadsheet">Spreadsheet</option>
            </select>

            <!-- Uploader Filter -->
            <select 
                wire:model.live="uploaderFilter" 
                class="block w-full px-3 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400 transition-colors">
                <option value="">Semua Pengunggah</option>
                @foreach($uploaders as $uploader)
                    <option value="{{ $uploader->id }}">{{ $uploader->name }}</option>
                @endforeach
            </select>

            <!-- Bulk Actions -->
            @if(!empty($selectedDocuments))
            <div class="flex gap-2">
                {{ $this->downloadZipAction }}
                <x-filament::button 
                    wire:click="deselectAllDocuments" 
                    color="gray"
                    size="sm">
                    Batal
                </x-filament::button>
            </div>
            @endif
        </div>
    </div>

    <!-- Documents List -->
    <div class="divide-y divide-gray-100 dark:divide-gray-700">
        @forelse($documents as $document)
        <div class="p-6 hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-all duration-200">
            <div class="flex items-start gap-4">
                <!-- Selection Checkbox -->
                <div class="flex items-center mt-1">
                    <input 
                        type="checkbox" 
                        wire:click="toggleDocumentSelection({{ $document->id }})"
                        @checked(in_array($document->id, $selectedDocuments))
                        class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 focus:ring-2"
                    />
                </div>

                <!-- File Icon -->
                <div class="flex-shrink-0">
                    @php
                        $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
                        $iconClass = match($extension) {
                            'pdf' => 'w-10 h-10 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
                            'jpg', 'jpeg', 'png', 'gif' => 'w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
                            'doc', 'docx' => 'w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400',
                            'xls', 'xlsx' => 'w-10 h-10 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400',
                            default => 'w-10 h-10 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
                        };
                    @endphp
                    <div class="{{ $iconClass }} flex items-center justify-center">
                        @if($extension === 'pdf')
                            <x-heroicon-o-document-text class="w-6 h-6" />
                        @elseif(in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
                            <x-heroicon-o-photo class="w-6 h-6" />
                        @elseif(in_array($extension, ['doc', 'docx']))
                            <x-heroicon-o-document class="w-6 h-6" />
                        @elseif(in_array($extension, ['xls', 'xlsx']))
                            <x-heroicon-o-table-cells class="w-6 h-6" />
                        @else
                            <x-heroicon-o-document class="w-6 h-6" />
                        @endif
                    </div>
                </div>

                <!-- Document Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ $document->original_filename }}
                            </h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $this->getFileTypeColor($document->file_path) === 'red' ? 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300' : ($this->getFileTypeColor($document->file_path) === 'blue' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300' : ($this->getFileTypeColor($document->file_path) === 'indigo' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300' : ($this->getFileTypeColor($document->file_path) === 'green' ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-300' : 'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300'))) }}">
                                    {{ strtoupper($extension) }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $this->getFileSize($document->file_path) }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Diunggah oleh {{ $document->user->name }} • {{ $document->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <!-- Actions using Filament Actions -->
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if($this->isPreviewable($document->file_path))
                            <x-filament::icon-button
                                wire:click="$dispatch('filament-action', { action: 'preview', arguments: { document: {{ $document->id }} } })"
                                icon="heroicon-o-eye"
                                color="info"
                                size="sm"
                                tooltip="Pratinjau"
                            />
                            @endif
                            
                            <x-filament::icon-button
                                wire:click="downloadDocument({{ $document->id }})"
                                icon="heroicon-o-arrow-down-tray"
                                color="success"
                                size="sm"
                                tooltip="Unduh"
                            />
                            
                            <x-filament::icon-button
                                wire:click="$dispatch('filament-action', { action: 'delete', arguments: { document: {{ $document->id }} } })"
                                icon="heroicon-o-trash"
                                color="danger"
                                size="sm"
                                tooltip="Hapus"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="p-12 text-center">
            <div class="w-16 h-16 mx-auto bg-gray-50 dark:bg-gray-700 rounded-xl flex items-center justify-center mb-4">
                <x-heroicon-o-document-text class="w-8 h-8 text-gray-400 dark:text-gray-500" />
            </div>
            <h3 class="text-base font-medium text-gray-900 dark:text-white">Belum ada dokumen</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Unggah dokumen legal untuk klien ini menggunakan tombol di atas.
            </p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($documents->hasPages())
    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
        {{ $documents->links() }}
    </div>
    @endif

    <!-- Bulk Selection Actions -->
    @if($documents->count() > 0)
    <div class="px-6 py-3 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between text-sm">
            <div class="flex items-center gap-4">
                <x-filament::button 
                    wire:click="selectAllDocuments"
                    color="gray"
                    size="sm">
                    Pilih Semua
                </x-filament::button>
                @if(!empty($selectedDocuments))
                <span class="text-gray-600 dark:text-gray-400">
                    {{ count($selectedDocuments) }} dokumen dipilih
                </span>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Filament Actions -->
    <x-filament-actions::modals />
</div>