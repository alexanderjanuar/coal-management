<div class="space-y-4 sm:space-y-6" x-data="{ mounted: false }" x-init="setTimeout(() => mounted = true, 100)">
    @if ($clients->isEmpty())
    {{-- Empty State --}}
    <div
        class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-8 sm:p-12 dark:border-gray-700 dark:bg-gray-800/50">
        <x-heroicon-o-document-text class="mb-4 h-12 w-12 text-gray-400 sm:h-16 sm:w-16" />
        <h3 class="mb-2 text-base font-semibold text-gray-900 dark:text-white sm:text-lg">Tidak Ada Client</h3>
        <p class="text-center text-sm text-gray-600 dark:text-gray-400 sm:text-base">
            Anda belum memiliki akses ke client manapun.<br class="hidden sm:inline">
            Hubungi administrator untuk mendapatkan akses.
        </p>
    </div>
    @else
    {{-- Client Selector --}}
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800"
        x-show="mounted" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                <x-heroicon-o-building-office-2 class="h-5 w-5 text-gray-400" />
                <span>Pilih Perusahaan:</span>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($clients as $client)
                <button wire:click="selectClient({{ $client->id }})" class="rounded-lg px-4 py-2 text-sm font-medium transition-colors
                                {{ $selectedClientId === $client->id 
                                    ? 'bg-primary-600 text-white hover:bg-primary-700' 
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' 
                                }}">
                    {{ $client->name }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Selected Client Content --}}
    @if($selectedClientId && $currentClient)
    <div class="space-y-4 sm:space-y-5" x-show="mounted" x-transition:enter="transition ease-out duration-300 delay-100"
        x-transition:enter-start="opacity-0 transform translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0">

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            {{-- Uploaded --}}
            <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800 sm:p-4">
                <div class="flex items-center gap-2">
                    <div class="rounded-md bg-success-100 p-1.5 dark:bg-success-900/20 sm:p-2">
                        <x-heroicon-o-check-circle
                            class="h-4 w-4 text-success-600 dark:text-success-400 sm:h-5 sm:w-5" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 sm:text-sm">Terupload</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white sm:text-xl">{{ $stats['uploaded'] ?? 0
                            }}</p>
                    </div>
                </div>
            </div>

            {{-- Valid --}}
            <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800 sm:p-4">
                <div class="flex items-center gap-2">
                    <div class="rounded-md bg-primary-100 p-1.5 dark:bg-primary-900/20 sm:p-2">
                        <x-heroicon-o-document-check
                            class="h-4 w-4 text-primary-600 dark:text-primary-400 sm:h-5 sm:w-5" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 sm:text-sm">Valid</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white sm:text-xl">{{ $stats['valid'] ?? 0 }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Expired --}}
            <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800 sm:p-4">
                <div class="flex items-center gap-2">
                    <div class="rounded-md bg-danger-100 p-1.5 dark:bg-danger-900/20 sm:p-2">
                        <x-heroicon-o-exclamation-circle
                            class="h-4 w-4 text-danger-600 dark:text-danger-400 sm:h-5 sm:w-5" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 sm:text-sm">Expired</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white sm:text-xl">{{ $stats['expired'] ?? 0
                            }}</p>
                    </div>
                </div>
            </div>

            {{-- Completion --}}
            <div class="rounded-lg bg-gradient-to-br from-primary-500 to-primary-600 p-3 text-white shadow-md sm:p-4">
                <div class="flex items-center gap-2">
                    <div class="rounded-md bg-white/20 p-1.5 sm:p-2">
                        <x-heroicon-o-chart-bar class="h-4 w-4 sm:h-5 sm:w-5" />
                    </div>
                    <div>
                        <p class="text-xs opacity-90 sm:text-sm">Progress</p>
                        <p class="text-lg font-bold sm:text-xl">{{ $stats['completion_percentage'] ?? 0 }}%</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
            <div class="relative h-2.5 bg-gradient-to-r from-primary-500 to-primary-600 transition-all duration-500 ease-out sm:h-3"
                style="width: {{ $stats['completion_percentage'] ?? 0 }}%">
                <div
                    class="absolute inset-0 animate-shimmer bg-gradient-to-r from-transparent via-white/30 to-transparent">
                </div>
            </div>
        </div>

        {{-- Legal Documents Table --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div
                class="border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/50 sm:px-6 sm:py-4">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white sm:text-base">Dokumen Legal Wajib</h4>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300 sm:px-6">
                                Dokumen</th>
                            <th scope="col"
                                class="hidden px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300 sm:table-cell sm:px-6">
                                Nomor</th>
                            <th scope="col"
                                class="hidden px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300 md:table-cell md:px-6">
                                Kadaluarsa</th>
                            <th scope="col"
                                class="hidden px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300 lg:table-cell lg:px-6">
                                Upload Oleh</th>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300 sm:px-6">
                                Status</th>
                            <th scope="col"
                                class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300 sm:px-6">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        @forelse($checklist as $doc)
                        <tr class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-4 py-4 sm:px-6">
                                <div class="flex items-start gap-3">
                                    <x-heroicon-o-document
                                        class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400 sm:h-5 sm:w-5" />
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $doc['name'] }}
                                        </p>
                                        @if($doc['is_required'])
                                        <span
                                            class="mt-1 inline-flex rounded-full bg-danger-100 px-2 py-0.5 text-xs font-medium text-danger-700 dark:bg-danger-900/30 dark:text-danger-400">Wajib</span>
                                        @endif

                                        {{-- Mobile: Show number and expiry --}}
                                        @if($doc['is_uploaded'])
                                        <div class="mt-2 space-y-1 text-xs text-gray-600 dark:text-gray-400 sm:hidden">
                                            @if($doc['uploaded_document']?->document_number)
                                            <div class="flex items-center gap-1">
                                                <x-heroicon-o-hashtag class="h-3 w-3" />
                                                <span>{{ $doc['uploaded_document']->document_number }}</span>
                                            </div>
                                            @endif
                                            @if($doc['uploaded_document']?->expired_at)
                                            <div class="flex items-center gap-1">
                                                <x-heroicon-o-calendar class="h-3 w-3" />
                                                <span>{{
                                                    \Carbon\Carbon::parse($doc['uploaded_document']->expired_at)->format('d
                                                    M Y') }}</span>
                                            </div>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td
                                class="hidden whitespace-nowrap px-4 py-4 text-sm text-gray-900 dark:text-gray-300 sm:table-cell sm:px-6">
                                {{ $doc['uploaded_document']?->document_number ?? '-' }}
                            </td>

                            <td
                                class="hidden whitespace-nowrap px-4 py-4 text-sm text-gray-900 dark:text-gray-300 md:table-cell md:px-6">
                                @if($doc['uploaded_document']?->expired_at)
                                {{ \Carbon\Carbon::parse($doc['uploaded_document']->expired_at)->format('d M Y') }}
                                @else
                                -
                                @endif
                            </td>

                            <td
                                class="hidden whitespace-nowrap px-4 py-4 text-sm text-gray-900 dark:text-gray-300 lg:table-cell lg:px-6">
                                {{ $doc['uploaded_by']?->name ?? '-' }}
                            </td>

                            <td class="px-4 py-4 sm:px-6">
                                @if($doc['is_uploaded'])
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-success-100 px-2 py-1 text-xs font-medium text-success-700 dark:bg-success-900/30 dark:text-success-400 sm:gap-1.5 sm:px-3 sm:py-1.5">
                                    <x-heroicon-o-check-circle class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                                    <span class="sm:hidden">OK</span>
                                    <span class="hidden sm:inline">Uploaded</span>
                                </span>
                                @else
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-warning-100 px-2 py-1 text-xs font-medium text-warning-700 dark:bg-warning-900/30 dark:text-warning-400 sm:gap-1.5 sm:px-3 sm:py-1.5">
                                    <x-heroicon-o-clock class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                                    <span>Pending</span>
                                </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-right sm:px-6">
                                <div class="flex items-center justify-end gap-1 sm:gap-2">
                                    @if($doc['is_uploaded'])
                                    <x-filament::icon-button icon="heroicon-o-eye" size="xs"
                                        wire:click="previewDocuments({{ $doc['uploaded_document']->id }})"
                                        tooltip="Preview" />

                                    <x-filament::icon-button icon="heroicon-o-arrow-down-tray" size="xs"
                                        class="hidden sm:inline-flex"
                                        wire:click="downloadDocument({{ $doc['uploaded_document']->id }})"
                                        tooltip="Download" />

                                    <x-filament::icon-button icon="heroicon-o-arrow-up-tray" size="xs" color="warning"
                                        wire:click="openUploadModal({{ $currentClient->id }}, {{ $doc['sop_id'] }}, false)"
                                        tooltip="Re-upload" />
                                    @else
                                    <x-filament::button size="xs"
                                        wire:click="openUploadModal({{ $currentClient->id }}, {{ $doc['sop_id'] }}, false)">
                                        <span class="sm:hidden">Up</span>
                                        <span class="hidden sm:inline">Upload</span>
                                    </x-filament::button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6"
                                class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400 sm:px-6">
                                Tidak ada dokumen legal yang diperlukan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Additional Documents Table --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div
                class="border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/50 sm:px-6 sm:py-4">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white sm:text-base">Dokumen Tambahan</h4>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300 sm:px-6">
                                Dokumen</th>
                            <th scope="col"
                                class="hidden px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300 sm:table-cell sm:px-6">
                                Upload</th>
                            <th scope="col"
                                class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300 sm:px-6">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        @forelse($additionalDocuments as $doc)
                        <tr class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-4 py-4 sm:px-6">
                                <div class="flex items-start gap-3">
                                    <x-heroicon-o-document
                                        class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400 sm:h-5 sm:w-5" />
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{
                                            $doc->original_filename }}</p>
                                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-400 sm:hidden">
                                            {{ $doc->created_at->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td
                                class="hidden whitespace-nowrap px-4 py-4 text-sm text-gray-900 dark:text-gray-300 sm:table-cell sm:px-6">
                                {{ $doc->created_at->format('d M Y, H:i') }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-right sm:px-6">
                                <div class="flex items-center justify-end gap-1 sm:gap-2">
                                    <x-filament::icon-button icon="heroicon-o-eye" size="xs"
                                        wire:click="previewDocuments({{ $doc->id }})" tooltip="Preview" />

                                    <x-filament::icon-button icon="heroicon-o-arrow-down-tray" size="xs"
                                        class="hidden sm:inline-flex" wire:click="downloadDocument({{ $doc->id }})"
                                        tooltip="Download" />

                                    <x-filament::icon-button icon="heroicon-o-trash" size="xs" color="danger"
                                        wire:click="deleteDocumentConfirm({{ $doc->id }})" tooltip="Hapus" />
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3"
                                class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400 sm:px-6">
                                Belum ada dokumen tambahan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    @endif

    {{-- Upload Modal --}}
    <x-filament::modal id="upload-document-modal" width="5xl">
        <x-slot name="heading">
            Upload Dokumen
        </x-slot>

        <form wire:submit="uploadDocument">
            {{ $this->form }}

            <div class="mt-6 flex justify-end gap-3">
                <x-filament::button color="gray" wire:click="closeUploadModal" type="button">
                    Batal
                </x-filament::button>

                <x-filament::button type="submit">
                    Upload
                </x-filament::button>
            </div>
        </form>
    </x-filament::modal>

    {{-- Preview Modal --}}
    <x-filament::modal id="preview-document-modal" width="7xl">
        <x-slot name="heading">
            Preview Dokumen
        </x-slot>

        @if($previewDocument)
        <div class="space-y-4">
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama File</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $previewDocument->original_filename }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Upload Oleh</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $previewDocument->user->name ?? '-' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div
                class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                @php
                $extension = strtolower(pathinfo($previewDocument->file_path, PATHINFO_EXTENSION));
                $imageable = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                $isPdf = $extension === 'pdf';
                @endphp

                @if($imageable)
                <img src="{{ asset('storage/' . $previewDocument->file_path) }}"
                    alt="{{ $previewDocument->original_filename }}" class="mx-auto max-h-[1000px] w-auto">
                @elseif($isPdf)
                <iframe src="{{ asset('storage/' . $previewDocument->file_path) }}" class="h-[1000px] w-full"
                    frameborder="0"></iframe>
                @else
                <div class="flex flex-col items-center justify-center p-12">
                    <x-heroicon-o-document class="mb-4 h-16 w-16 text-gray-400" />
                    <p class="mb-4 text-center text-gray-600 dark:text-gray-400">
                        Preview tidak tersedia untuk tipe file ini
                    </p>
                    <x-filament::button wire:click="downloadDocument({{ $previewDocument->id }})">
                        Download File
                    </x-filament::button>
                </div>
                @endif
            </div>
        </div>
        @endif
    </x-filament::modal>

    {{-- Delete Confirmation Modal --}}
    <x-filament::modal id="confirm-delete-modal" width="md">
        <x-slot name="heading">
            Konfirmasi Hapus
        </x-slot>

        <p class="text-sm text-gray-600 dark:text-gray-400">
            Apakah Anda yakin ingin menghapus dokumen ini? Tindakan ini tidak dapat dibatalkan.
        </p>

        <div class="mt-6 flex justify-end gap-3">
            <x-filament::button color="gray" wire:click="closeDeleteModal">
                Batal
            </x-filament::button>

            <x-filament::button color="danger" wire:click="deleteDocument">
                Hapus
            </x-filament::button>
        </div>
    </x-filament::modal>
</div>