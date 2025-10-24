<div class="space-y-4 sm:space-y-8">
    <!-- Progress Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-5">
        <!-- Header - Stack on mobile, side by side on desktop -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
            <div class="flex-1 min-w-0">
                <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100 truncate">Kelengkapan
                    Dokumen Legal</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $stats['uploaded'] ?? 0 }} dari {{ count($checklist) }} dokumen terupload
                </p>
            </div>

            <!-- Stats - Wrap on mobile -->
            <div class="flex items-center gap-2 sm:gap-4 flex-wrap">
                <!-- Mini Stats -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="flex items-center gap-1.5">
                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">Valid: <span
                                class="font-semibold text-gray-900 dark:text-gray-100">{{ $stats['valid'] ?? 0
                                }}</span></span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">Expired: <span
                                class="font-semibold text-gray-900 dark:text-gray-100">{{ $stats['expired'] ?? 0
                                }}</span></span>
                    </div>
                </div>

                <!-- Percentage Badge -->
                <div class="px-2.5 sm:px-3 py-1 sm:py-1.5 bg-primary-100 dark:bg-primary-900/30 rounded-lg">
                    <span class="text-base sm:text-lg font-bold text-primary-600 dark:text-primary-400">{{
                        $stats['completion_percentage'] ?? 0 }}%</span>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="relative">
            <div class="h-2.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-primary-500 to-primary-600 transition-all duration-700 ease-out rounded-full relative overflow-hidden"
                    style="width: {{ $stats['completion_percentage'] ?? 0 }}%">
                    <!-- Animated shimmer effect -->
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
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">Dokumen Legal Wajib
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5 sm:mt-1">
                        {{ $stats['uploaded'] ?? 0 }} dari {{ count($checklist) }} dokumen terupload
                    </p>
                </div>
            </div>
        </div>

        <!-- Table Container with horizontal scroll on mobile -->
        <div class="overflow-x-auto">
            <!-- Desktop Table View -->
            <table class="hidden md:table w-full">
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
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $item['name'] }}
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
                            <div class="flex items-center gap-2">
                                <span>{{ $item['uploaded_document']->user->name ?? '-' }}</span>
                                <span class="text-gray-400 text-xs">
                                    • {{ $item['uploaded_document']->created_at->diffForHumans() }}
                                </span>
                            </div>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($item['is_uploaded'])
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 text-xs font-semibold rounded-md border-2 border-green-200 dark:border-green-700 shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                Uploaded
                            </span>
                            @else
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300 text-xs font-semibold rounded-md border-2 border-yellow-200 dark:border-yellow-700 shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                Pending
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <div class="flex items-center justify-end gap-1">
                                @if($item['is_uploaded'] && $item['uploaded_document'])
                                <x-filament::icon-button icon="heroicon-o-eye" color="purple" label="Preview" size="xs"
                                    tooltip="Preview"
                                    wire:click="previewDocument({{ $item['uploaded_document']->id }})" />

                                <x-filament::icon-button icon="heroicon-o-arrow-down-tray" color="info" label="Download"
                                    size="xs" tooltip="Download"
                                    wire:click="downloadDocument({{ $item['uploaded_document']->id }})" />

                                <x-filament::icon-button icon="heroicon-o-trash" color="danger" label="Hapus" size="xs"
                                    tooltip="Hapus" wire:click="deleteDocument({{ $item['uploaded_document']->id }})"
                                    wire:confirm="Apakah Anda yakin ingin menghapus dokumen ini?" />

                                <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>
                                @endif

                                <x-filament::button wire:click="openUploadModal({{ $item['sop_id'] }}, false)" size="xs"
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
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Tidak ada dokumen legal wajib</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Mobile Card View -->
            <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($checklist as $item)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <!-- Document Name -->
                    <div class="font-medium text-gray-900 dark:text-gray-100 mb-3">
                        {{ $item['name'] }}
                    </div>

                    <!-- Status Badge -->
                    <div class="mb-3">
                        @if($item['is_uploaded'])
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 text-xs font-semibold rounded-md border-2 border-green-200 dark:border-green-700 shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Uploaded
                        </span>
                        @else
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300 text-xs font-semibold rounded-md border-2 border-yellow-200 dark:border-yellow-700 shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Pending
                        </span>
                        @endif
                    </div>

                    <!-- Document Details Grid -->
                    @if($item['is_uploaded'] && $item['uploaded_document'])
                    <div class="space-y-2 mb-3 text-sm">
                        @if($item['uploaded_document']->document_number)
                        <div class="flex items-start gap-2">
                            <span class="text-gray-500 dark:text-gray-400 min-w-[80px]">Nomor:</span>
                            <span class="text-gray-900 dark:text-gray-100 flex-1">{{
                                $item['uploaded_document']->document_number }}</span>
                        </div>
                        @endif

                        @if($item['uploaded_document']->expired_at)
                        <div class="flex items-start gap-2">
                            <span class="text-gray-500 dark:text-gray-400 min-w-[80px]">Kadaluarsa:</span>
                            <span
                                class="flex-1 @if(\Carbon\Carbon::parse($item['uploaded_document']->expired_at)->isPast()) text-red-600 dark:text-red-400 font-medium @else text-gray-900 dark:text-gray-100 @endif">
                                {{ \Carbon\Carbon::parse($item['uploaded_document']->expired_at)->format('d M Y') }}
                            </span>
                        </div>
                        @endif

                        <div class="flex items-start gap-2">
                            <span class="text-gray-500 dark:text-gray-400 min-w-[80px]">Upload:</span>
                            <span class="text-gray-900 dark:text-gray-100 flex-1">
                                {{ $item['uploaded_document']->user->name ?? '-' }}
                                <span class="text-gray-400 text-xs block mt-0.5">
                                    {{ $item['uploaded_document']->created_at->diffForHumans() }}
                                </span>
                            </span>
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($item['is_uploaded'] && $item['uploaded_document'])
                        <x-filament::icon-button icon="heroicon-o-eye" color="purple" label="Preview" size="xs"
                            tooltip="Preview" wire:click="previewDocument({{ $item['uploaded_document']->id }})" />

                        <x-filament::icon-button icon="heroicon-o-arrow-down-tray" color="info" label="Download"
                            size="xs" tooltip="Download"
                            wire:click="downloadDocument({{ $item['uploaded_document']->id }})" />

                        <x-filament::icon-button icon="heroicon-o-trash" color="danger" label="Hapus" size="xs"
                            tooltip="Hapus" wire:click="deleteDocument({{ $item['uploaded_document']->id }})"
                            wire:confirm="Apakah Anda yakin ingin menghapus dokumen ini?" />
                        @endif

                        <x-filament::button wire:click="openUploadModal({{ $item['sop_id'] }}, false)" size="xs"
                            icon="heroicon-o-arrow-up-tray" class="flex-1 sm:flex-none">
                            {{ $item['is_uploaded'] ? 'Re-upload' : 'Upload' }}
                        </x-filament::button>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Tidak ada dokumen legal wajib</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Dokumen Tambahan Section -->
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
        <!-- Header -->
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">Dokumen Tambahan
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5 sm:mt-1">
                        {{ count($additionalDocuments) }} dokumen
                    </p>
                </div>
                <x-filament::button wire:click="openUploadModal(null, true)" size="sm" icon="heroicon-o-plus"
                    color="purple" class="w-full sm:w-auto">
                    Tambah Dokumen
                </x-filament::button>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <!-- Desktop Table View -->
            <table class="hidden md:table w-full">
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
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($additionalDocuments as $doc)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $doc->description ?? $doc->original_filename }}
                            </div>
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
                            <div class="flex items-center gap-2">
                                <span>{{ $doc->user->name ?? '-' }}</span>
                                <span class="text-gray-400 text-xs">
                                    • {{ $doc->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <div class="flex items-center justify-end gap-1">
                                <x-filament::icon-button icon="heroicon-o-eye" color="purple" label="Preview" size="xs"
                                    tooltip="Preview" wire:click="previewDocument({{ $doc->id }})" />

                                <x-filament::icon-button icon="heroicon-o-arrow-down-tray" color="info" label="Download"
                                    size="xs" tooltip="Download" wire:click="downloadDocument({{ $doc->id }})" />

                                <x-filament::icon-button icon="heroicon-o-trash" color="danger" label="Hapus" size="xs"
                                    tooltip="Hapus" wire:click="deleteDocument({{ $doc->id }})"
                                    wire:confirm="Apakah Anda yakin ingin menghapus dokumen ini?" />
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                </path>
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mb-3">Belum ada dokumen tambahan</p>
                            <x-filament::button wire:click="openUploadModal(null, true)" size="sm"
                                icon="heroicon-o-plus" color="purple">
                                Tambah Dokumen Pertama
                            </x-filament::button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Mobile Card View -->
            <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($additionalDocuments as $doc)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <!-- Document Name -->
                    <div class="font-medium text-gray-900 dark:text-gray-100 mb-3">
                        {{ $doc->description ?? $doc->original_filename }}
                    </div>

                    <!-- Document Details -->
                    <div class="space-y-2 mb-3 text-sm">
                        @if($doc->document_number)
                        <div class="flex items-start gap-2">
                            <span class="text-gray-500 dark:text-gray-400 min-w-[80px]">Nomor:</span>
                            <span class="text-gray-900 dark:text-gray-100 flex-1">{{ $doc->document_number }}</span>
                        </div>
                        @endif

                        @if($doc->expired_at)
                        <div class="flex items-start gap-2">
                            <span class="text-gray-500 dark:text-gray-400 min-w-[80px]">Kadaluarsa:</span>
                            <span
                                class="flex-1 @if(\Carbon\Carbon::parse($doc->expired_at)->isPast()) text-red-600 dark:text-red-400 font-medium @else text-gray-900 dark:text-gray-100 @endif">
                                {{ \Carbon\Carbon::parse($doc->expired_at)->format('d M Y') }}
                            </span>
                        </div>
                        @endif

                        <div class="flex items-start gap-2">
                            <span class="text-gray-500 dark:text-gray-400 min-w-[80px]">Upload:</span>
                            <span class="text-gray-900 dark:text-gray-100 flex-1">
                                {{ $doc->user->name ?? '-' }}
                                <span class="text-gray-400 text-xs block mt-0.5">
                                    {{ $doc->created_at->diffForHumans() }}
                                </span>
                            </span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <x-filament::icon-button icon="heroicon-o-eye" color="purple" label="Preview" size="xs"
                            tooltip="Preview" wire:click="previewDocument({{ $doc->id }})" />

                        <x-filament::icon-button icon="heroicon-o-arrow-down-tray" color="info" label="Download"
                            size="xs" tooltip="Download" wire:click="downloadDocument({{ $doc->id }})" />

                        <x-filament::icon-button icon="heroicon-o-trash" color="danger" label="Hapus" size="xs"
                            tooltip="Hapus" wire:click="deleteDocument({{ $doc->id }})"
                            wire:confirm="Apakah Anda yakin ingin menghapus dokumen ini?" />
                    </div>
                </div>
                @empty
                <div class="p-8 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                        </path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-3">Belum ada dokumen tambahan</p>
                    <x-filament::button wire:click="openUploadModal(null, true)" size="sm" icon="heroicon-o-plus"
                        color="purple" class="w-full">
                        Tambah Dokumen Pertama
                    </x-filament::button>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <x-filament::modal id="upload-document-modal" width="5xl">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="bg-primary-100 dark:bg-primary-900/30 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                        </path>
                    </svg>
                </div>
                <span class="text-base sm:text-lg font-semibold">
                    {{ $isAdditionalDocument ? 'Upload Dokumen Tambahan' : 'Upload Dokumen Legal' }}
                </span>
            </div>
        </x-slot>

        <form wire:submit="uploadDocument" class="space-y-6">
            {{ $this->form }}

            <x-slot name="footer">
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3">
                    <x-filament::button color="gray" wire:click="closeUploadModal" type="button"
                        class="w-full sm:w-auto">
                        Batal
                    </x-filament::button>

                    <x-filament::button type="submit" wire:loading.attr="disabled" class="w-full sm:w-auto">
                        <span wire:loading.remove>Upload Dokumen</span>
                        <span wire:loading>Mengupload...</span>
                    </x-filament::button>
                </div>
            </x-slot>
        </form>
    </x-filament::modal>

    <!-- Preview Modal -->
    <x-filament::modal id="preview-document-modal" width="7xl">
        <x-slot name="heading">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="bg-purple-100 dark:bg-purple-900/30 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="text-base sm:text-lg font-semibold">Preview Dokumen</span>
                    @if($previewDocument)
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                        {{ $previewDocument->original_filename }}
                    </p>
                    @endif
                </div>
            </div>
        </x-slot>

        <div class="space-y-4">
            @if($previewDocument)
            @php
            $extension = strtolower(pathinfo($previewDocument->file_path, PATHINFO_EXTENSION));
            $filePath = asset('storage/' . $previewDocument->file_path);
            @endphp

            @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
            <!-- Image Preview -->
            <div
                class="bg-gray-100 dark:bg-gray-900 rounded-lg p-4 flex items-center justify-center min-h-[300px] sm:min-h-[500px]">
                <img src="{{ $filePath }}" alt="Document Preview"
                    class="max-w-full max-h-[500px] sm:max-h-[1000px] object-contain rounded-lg shadow-lg">
            </div>
            @elseif($extension === 'pdf')
            <!-- PDF Preview -->
            <div class="bg-gray-100 dark:bg-gray-900 rounded-lg overflow-hidden"
                style="height: 500px; sm:height: 1000px;">
                <iframe src="{{ $filePath }}" class="w-full h-full border-0" title="PDF Preview"></iframe>
            </div>
            @else
            <!-- Unsupported Format -->
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-8 sm:p-12 text-center">
                <svg class="w-16 sm:w-20 h-16 sm:h-20 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                <p class="text-gray-600 dark:text-gray-400 font-medium mb-2 text-sm sm:text-base">
                    Preview tidak tersedia untuk file {{ strtoupper($extension) }}
                </p>
                <p class="text-gray-500 dark:text-gray-500 text-xs sm:text-sm mb-4">
                    Silakan download file untuk melihat kontennya
                </p>
                <a href="{{ $filePath }}" download
                    class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download File
                </a>
            </div>
            @endif
            @endif
        </div>

        <x-slot name="footer">
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3">
                @if($previewDocument)
                <x-filament::button color="gray" tag="a" href="{{ asset('storage/' . $previewDocument->file_path) }}"
                    download class="w-full sm:w-auto">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download
                </x-filament::button>
                @endif

                <x-filament::button color="primary" wire:click="closePreviewModal" class="w-full sm:w-auto">
                    Tutup
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</div>