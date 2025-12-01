<div class="space-y-6">
    <!-- Progress Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Kelengkapan Dokumen Legal</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $stats['uploaded'] ?? 0 }} dari {{ count($checklist) }} dokumen terupload
                </p>
            </div>
            <div class="flex items-center gap-4">
                <!-- Mini Stats -->
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5">
                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400">Valid: <span
                                class="font-semibold text-gray-900 dark:text-gray-100">{{ $stats['valid'] ?? 0
                                }}</span></span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400">Pending: <span
                                class="font-semibold text-gray-900 dark:text-gray-100">{{ $stats['pending_review'] ?? 0
                                }}</span></span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400">Expired: <span
                                class="font-semibold text-gray-900 dark:text-gray-100">{{ $stats['expired'] ?? 0
                                }}</span></span>
                    </div>
                </div>
                <!-- Percentage Badge -->
                <div class="px-3 py-1.5 bg-primary-100 dark:bg-primary-900/30 rounded-lg">
                    <span class="text-lg font-bold text-primary-600 dark:text-primary-400">{{
                        $stats['completion_percentage'] ?? 0 }}%</span>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="relative">
            <div class="h-2.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-primary-500 to-primary-600 transition-all duration-700 ease-out rounded-full relative overflow-hidden"
                    style="width: {{ $stats['completion_percentage'] ?? 0 }}%">
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .animate-shimmer {
            animation: shimmer 2s infinite;
        }
    </style>

    <!-- Dokumen Legal Wajib Section -->
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Dokumen Legal Wajib</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Dokumen yang wajib dilengkapi sesuai tipe client
                    </p>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Dokumen
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Nomor
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Kadaluarsa
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Upload Oleh
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($checklist as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-start gap-3">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $item['name'] }}
                                    </div>
                                    @if($item['description'])
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $item['description'] }}
                                    </p>
                                    @endif
                                    @if($item['is_required'])
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-xs font-medium rounded mt-1">
                                        <x-heroicon-o-exclamation-circle class="w-3 h-3" />
                                        Wajib
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            {{ $item['uploaded_document']->document_number ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($item['is_uploaded'] && $item['uploaded_document']->expired_at)
                            <span
                                class="@if(\Carbon\Carbon::parse($item['uploaded_document']->expired_at)->isPast()) text-red-600 dark:text-red-400 font-medium @else text-gray-700 dark:text-gray-200 @endif">
                                {{ \Carbon\Carbon::parse($item['uploaded_document']->expired_at)->format('d M Y') }}
                            </span>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            @if($item['is_uploaded'] && $item['uploaded_document'])
                            <div class="flex flex-col gap-1">
                                <span>{{ $item['uploaded_document']->user->name ?? '-' }}</span>
                                <span class="text-gray-400 text-xs">
                                    {{ $item['uploaded_document']->created_at->format('d M Y, H:i') }}
                                </span>
                            </div>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($item['is_uploaded'])
                            @php
                            $doc = $item['uploaded_document'];
                            $statusBadge = $doc->status_badge;
                            @endphp
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md {{ $statusBadge['class'] }}">
                                <x-dynamic-component :component="$statusBadge['icon']" class="w-3.5 h-3.5" />
                                {{ $statusBadge['text'] }}
                            </span>

                            @if($doc->admin_notes)
                            <div
                                class="mt-2 text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50 p-2 rounded">
                                <span class="font-medium">Catatan:</span> {{ $doc->admin_notes }}
                            </div>
                            @endif
                            @else
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-semibold rounded-md">
                                <x-heroicon-o-clock class="w-3.5 h-3.5" />
                                Belum Upload
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <div class="flex items-center justify-end gap-2">
                                @if($item['is_uploaded'] && $item['uploaded_document'])
                                @php $doc = $item['uploaded_document']; @endphp

                                <!-- Preview Button -->
                                <x-filament::icon-button icon="heroicon-o-eye" color="gray" size="sm" tooltip="Preview"
                                    wire:click="previewDocuments({{ $doc->id }})" />

                                <!-- Download Button -->
                                <x-filament::icon-button icon="heroicon-o-arrow-down-tray" size="sm" color="info"
                                    tooltip="Download" wire:click="downloadDocument({{ $doc->id }})" />

                                @if($doc->status === 'pending_review')
                                <!-- Approve Button -->
                                <x-filament::icon-button icon="heroicon-o-check-circle" color="success" size="sm"
                                    tooltip="Setujui" wire:click="openReviewModal({{ $doc->id }}, 'approve')" />

                                <!-- Reject Button -->
                                <x-filament::icon-button icon="heroicon-o-x-circle" color="danger" size="sm"
                                    tooltip="Tolak" wire:click="openReviewModal({{ $doc->id }}, 'reject')" />
                                @endif

                                <!-- Delete Button -->
                                <x-filament::icon-button icon="heroicon-o-trash" color="danger" size="sm"
                                    tooltip="Hapus" wire:click="deleteDocumentConfirm({{ $doc->id }})" />

                                <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>
                                @endif

                                <!-- Upload/Re-upload Button -->
                                <x-filament::button wire:click="openUploadModal({{ $item['sop_id'] }}, false)" size="sm"
                                    icon="heroicon-o-arrow-up-tray">
                                    {{ $item['is_uploaded'] ? 'Re-upload' : 'Upload' }}
                                </x-filament::button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Tidak ada dokumen legal yang diperlukan
                                untuk tipe client ini</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Dokumen Tambahan Section -->
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Dokumen Tambahan</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ count($additionalDocuments) }} dokumen tambahan
                    </p>
                </div>
                <x-filament::button wire:click="openUploadModal(null, true)" size="sm" icon="heroicon-o-plus">
                    Tambah Dokumen
                </x-filament::button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Nama Dokumen
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Nomor
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Kadaluarsa
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Upload Oleh
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($additionalDocuments as $doc)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $doc->description ?? $doc->original_filename }}
                            </div>
                            @if($doc->description && $doc->original_filename !== $doc->description)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $doc->original_filename }}
                            </p>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            {{ $doc->document_number ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($doc->expired_at)
                            <span
                                class="@if(\Carbon\Carbon::parse($doc->expired_at)->isPast()) text-red-600 dark:text-red-400 font-medium @else text-gray-700 dark:text-gray-200 @endif">
                                {{ \Carbon\Carbon::parse($doc->expired_at)->format('d M Y') }}
                            </span>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            <div class="flex flex-col gap-1">
                                <span>{{ $doc->user->name ?? '-' }}</span>
                                <span class="text-gray-400 text-xs">
                                    {{ $doc->created_at->format('d M Y, H:i') }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                            $statusBadge = $doc->status_badge;
                            @endphp
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md {{ $statusBadge['class'] }}">
                                <x-dynamic-component :component="$statusBadge['icon']" class="w-3.5 h-3.5" />
                                {{ $statusBadge['text'] }}
                            </span>

                            @if($doc->admin_notes)
                            <div
                                class="mt-2 text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50 p-2 rounded max-w-xs">
                                <span class="font-medium">Catatan:</span> {{ $doc->admin_notes }}
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <div class="flex items-center justify-end gap-2">
                                <!-- Preview Button -->
                                <x-filament::icon-button icon="heroicon-o-eye" color="gray" size="sm" tooltip="Preview"
                                    wire:click="previewDocuments({{ $doc->id }})" />

                                <!-- Download Button -->
                                <x-filament::icon-button icon="heroicon-o-arrow-down-tray" color="info" size="sm"
                                    tooltip="Download" wire:click="downloadDocument({{ $doc->id }})" />

                                @if($doc->status === 'pending_review')
                                <!-- Approve Button -->
                                <x-filament::icon-button icon="heroicon-o-check-circle" color="success" size="sm"
                                    tooltip="Setujui" wire:click="openReviewModal({{ $doc->id }}, 'approve')" />

                                <!-- Reject Button -->
                                <x-filament::icon-button icon="heroicon-o-x-circle" color="danger" size="sm"
                                    tooltip="Tolak" wire:click="openReviewModal({{ $doc->id }}, 'reject')" />
                                @endif

                                <!-- Delete Button -->
                                <x-filament::icon-button icon="heroicon-o-trash" color="danger" size="sm"
                                    tooltip="Hapus" wire:click="deleteDocumentConfirm({{ $doc->id }})" />
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                </path>
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mb-3">Belum ada dokumen tambahan</p>
                            <x-filament::button wire:click="openUploadModal(null, true)" size="sm"
                                icon="heroicon-o-plus">
                                Tambah Dokumen Pertama
                            </x-filament::button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Upload Modal -->
    <x-filament::modal id="upload-document-modal" width="5xl">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="bg-primary-100 dark:bg-primary-900/30 p-2 rounded-lg">
                    <x-heroicon-o-cloud-arrow-up class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                </div>
                <span class="text-lg font-semibold">
                    {{ $isAdditionalDocument ? 'Upload Dokumen Tambahan' : 'Upload Dokumen Legal' }}
                </span>
            </div>
        </x-slot>

        <form wire:submit="uploadDocument" class="space-y-6">
            {{ $this->form }}

            <x-slot name="footerActions">
                <x-filament::button color="gray" wire:click="closeUploadModal" type="button">
                    Batal
                </x-filament::button>

                <x-filament::button type="submit" wire:click="uploadDocument" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="uploadDocument">Upload Dokumen</span>
                    <span wire:loading wire:target="uploadDocument">Mengupload...</span>
                </x-filament::button>
            </x-slot>
        </form>
    </x-filament::modal>

    <!-- Review Modal -->
    <x-filament::modal id="review-document-modal" width="2xl">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                @if($reviewAction === 'approve')
                <div class="bg-success-100 dark:bg-success-900/30 p-2 rounded-lg">
                    <x-heroicon-o-check-circle class="w-6 h-6 text-success-600 dark:text-success-400" />
                </div>
                <span class="text-lg font-semibold">Setujui Dokumen</span>
                @else
                <div class="bg-danger-100 dark:bg-danger-900/30 p-2 rounded-lg">
                    <x-heroicon-o-x-circle class="w-6 h-6 text-danger-600 dark:text-danger-400" />
                </div>
                <span class="text-lg font-semibold">Tolak Dokumen</span>
                @endif
            </div>
        </x-slot>

        @if($documentToReview)
        <div class="space-y-4">
            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                <dl class="space-y-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama File</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $documentToReview->original_filename
                            }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Diupload Oleh</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $documentToReview->user->name ?? '-' }}
                            <span class="text-gray-400">• {{ $documentToReview->created_at->format('d M Y, H:i')
                                }}</span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ $reviewAction === 'approve' ? 'Catatan (Opsional)' : 'Alasan Penolakan *' }}
                </label>
                <textarea wire:model="reviewNotes" rows="4"
                    class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    placeholder="{{ $reviewAction === 'approve' ? 'Tambahkan catatan jika diperlukan...' : 'Jelaskan alasan penolakan dokumen ini...' }}"
                    {{ $reviewAction==='reject' ? 'required' : '' }}></textarea>
            </div>

            @if($reviewAction === 'approve')
            <div
                class="bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 rounded-lg p-3">
                <p class="text-sm text-success-700 dark:text-success-400">
                    Dokumen akan disetujui dan statusnya akan berubah menjadi "Valid"
                </p>
            </div>
            @else
            <div
                class="bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg p-3">
                <p class="text-sm text-danger-700 dark:text-danger-400">
                    Dokumen akan ditolak dan client harus mengupload ulang
                </p>
            </div>
            @endif
        </div>
        @endif

        <x-slot name="footerActions">
            <x-filament::button color="gray" wire:click="closeReviewModal">
                Batal
            </x-filament::button>

            <x-filament::button color="{{ $reviewAction === 'approve' ? 'success' : 'danger' }}"
                wire:click="submitReview" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="submitReview">
                    {{ $reviewAction === 'approve' ? 'Setujui' : 'Tolak' }}
                </span>
                <span wire:loading wire:target="submitReview">Memproses...</span>
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    {{-- Delete Confirmation Modal --}}
    <x-filament::modal id="confirm-delete-modal" width="md">
        <x-slot name="heading">
            Konfirmasi Hapus
        </x-slot>

        <x-slot name="description">
            Apakah Anda yakin ingin menghapus dokumen ini? Tindakan ini tidak dapat dibatalkan.
        </x-slot>

        <x-slot name="footer">
            <div class="flex gap-3 justify-end">
                <x-filament::button color="gray" wire:click="closeDeleteModal">
                    Batal
                </x-filament::button>

                <x-filament::button color="danger" wire:click="deleteDocument">
                    Hapus
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</div>