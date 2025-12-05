<div class="space-y-6" x-data="{ mounted: false }" x-init="setTimeout(() => mounted = true, 100)">
    @if ($clients->isEmpty())
    {{-- Empty State --}}
    <div
        class="flex flex-col items-center justify-center rounded-2xl border border-gray-200 bg-gradient-to-br from-gray-50 to-gray-100 p-12 dark:border-gray-700 dark:from-gray-800 dark:to-gray-900">
        <div class="rounded-full bg-white p-4 shadow-sm dark:bg-gray-800">
            <x-heroicon-o-document-text class="h-16 w-16 text-gray-400" />
        </div>
        <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-white">Tidak Ada Client</h3>
        <p class="mt-2 max-w-md text-center text-sm text-gray-600 dark:text-gray-400">
            Anda belum memiliki akses ke client manapun. Hubungi administrator untuk mendapatkan akses.
        </p>
    </div>
    @else
    {{-- Client Selector --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
        x-show="mounted" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0">
        <div class="p-5">
            <div
                class="mb-3 flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                <x-heroicon-o-building-office-2 class="h-4 w-4" />
                <span>Pilih Perusahaan</span>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($clients as $client)
                <button wire:click="selectClient({{ $client->id }})" class="group relative overflow-hidden rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                        {{ $selectedClientId === $client->id 
                            ? 'bg-primary-600 text-white shadow-lg shadow-primary-500/30 hover:bg-primary-700 dark:bg-primary-500 dark:shadow-primary-400/20' 
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
    <div class="space-y-6" x-show="mounted" x-transition:enter="transition ease-out duration-300 delay-100"
        x-transition:enter-start="opacity-0 transform translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0">

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            {{-- Uploaded --}}
            <div
                class="group rounded-xl border border-gray-200 bg-white p-4 transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Terupload</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['uploaded'] ?? 0 }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-emerald-50 p-2 dark:bg-emerald-900/20">
                        <x-heroicon-o-check-circle class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                </div>
            </div>

            {{-- Valid --}}
            <div
                class="group rounded-xl border border-gray-200 bg-white p-4 transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Valid
                        </p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['valid'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-lg bg-blue-50 p-2 dark:bg-blue-900/20">
                        <x-heroicon-o-document-check class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            {{-- Pending Review --}}
            <div
                class="group rounded-xl border border-gray-200 bg-white p-4 transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Review
                        </p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['pending_review'] ??
                            0 }}</p>
                    </div>
                    <div class="rounded-lg bg-amber-50 p-2 dark:bg-amber-900/20">
                        <x-heroicon-o-clock class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                    </div>
                </div>
            </div>

            {{-- Completion --}}
            <div
                class="group rounded-xl border border-gray-200 bg-white p-4 transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Progress
                        </p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{
                            $stats['completion_percentage'] ?? 0 }}%</p>
                    </div>
                    <div class="rounded-lg bg-indigo-50 p-2 dark:bg-indigo-900/20">
                        <x-heroicon-o-chart-bar class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
            <div class="relative h-3 bg-gradient-to-r from-gray-700 to-gray-900 transition-all duration-700 ease-out dark:from-gray-300 dark:to-gray-100"
                style="width: {{ $stats['completion_percentage'] ?? 0 }}%">
                <div
                    class="absolute inset-0 animate-shimmer bg-gradient-to-r from-transparent via-white/20 to-transparent">
                </div>
            </div>
        </div>

        {{-- Unified Documents Table --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/50">
                <h4 class="text-base font-semibold text-gray-900 dark:text-white">Semua Dokumen</h4>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola dokumen legal, persyaratan, dan tambahan
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900/50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Dokumen
                            </th>
                            <th
                                class="hidden px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 sm:table-cell">
                                Info
                            </th>
                            <th
                                class="hidden px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 md:table-cell">
                                Tenggat / Kadaluarsa
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        {{-- SECTION 1: Legal Documents --}}
                        <tr class="bg-gradient-to-r from-blue-50 to-transparent dark:from-blue-900/10">
                            <td colspan="5" class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-1 w-1 rounded-full bg-blue-500"></div>
                                    <span
                                        class="text-xs font-bold uppercase tracking-wider text-blue-900 dark:text-blue-300">Dokumen
                                        Legal Wajib</span>
                                    <div
                                        class="h-px flex-1 bg-gradient-to-r from-blue-200 to-transparent dark:from-blue-800">
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @forelse($checklist as $doc)
                        <tr class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="mt-1 rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                                        <x-heroicon-o-document class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $doc['name'] }}
                                        </p>
                                        @if($doc['description'])
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $doc['description']
                                            }}</p>
                                        @endif
                                        @if($doc['is_required'])
                                        <span
                                            class="mt-2 inline-flex items-center gap-1 rounded-md bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700 shadow-sm ring-2 ring-red-600/30 dark:bg-red-900/40 dark:text-red-300 dark:ring-red-400/40">
                                            <x-heroicon-o-exclamation-circle class="h-3.5 w-3.5" />
                                            WAJIB
                                        </span>
                                        @endif

                                        {{-- Mobile: Show number and expiry --}}
                                        @if($doc['is_uploaded'])
                                        <div class="mt-2 space-y-1 text-xs text-gray-500 dark:text-gray-400 sm:hidden">
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
                                class="hidden whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300 sm:table-cell">
                                @if($doc['uploaded_document']?->document_number)
                                <div class="flex items-center gap-1.5 text-xs">
                                    <x-heroicon-o-hashtag class="h-3.5 w-3.5 text-gray-400" />
                                    <span>{{ $doc['uploaded_document']->document_number }}</span>
                                </div>
                                @else
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <td
                                class="hidden whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300 md:table-cell">
                                @if($doc['uploaded_document']?->expired_at)
                                <div class="flex items-center gap-1.5 text-xs">
                                    <x-heroicon-o-calendar class="h-3.5 w-3.5 text-gray-400" />
                                    <span>{{ \Carbon\Carbon::parse($doc['uploaded_document']->expired_at)->format('d M
                                        Y') }}</span>
                                </div>
                                @else
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                @if($doc['is_uploaded'])
                                @php
                                $docStatus = $doc['uploaded_document'];
                                $statusBadge = $docStatus->status_badge;
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold shadow-md {{ $statusBadge['class'] }}">
                                    <x-dynamic-component :component="$statusBadge['icon']" class="h-4 w-4" />
                                    {{ $statusBadge['text'] }}
                                </span>
                                @if($docStatus->admin_notes)
                                <div
                                    class="mt-2 rounded-lg bg-amber-50 p-2 text-xs text-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                                    <span class="font-medium">Catatan:</span> {{ $docStatus->admin_notes }}
                                </div>
                                @endif
                                @else
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-gray-200 px-3 py-1.5 text-xs font-bold text-gray-700 shadow-md dark:bg-gray-700 dark:text-gray-200">
                                    <x-heroicon-o-clock class="h-4 w-4" />
                                    BELUM UPLOAD
                                </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($doc['is_uploaded'])
                                    <x-filament::icon-button icon="heroicon-o-eye" size="sm"
                                        wire:click="previewDocuments({{ $doc['uploaded_document']->id }})"
                                        tooltip="Preview" />

                                    <x-filament::icon-button icon="heroicon-o-arrow-down-tray" size="sm"
                                        class="hidden sm:inline-flex"
                                        wire:click="downloadDocument({{ $doc['uploaded_document']->id }})"
                                        tooltip="Download" />

                                    <x-filament::icon-button icon="heroicon-o-arrow-up-tray" size="sm" color="warning"
                                        wire:click="openUploadModal({{ $currentClient->id }}, {{ $doc['sop_id'] }}, false)"
                                        tooltip="Re-upload" />
                                    @else
                                    <x-filament::button size="sm"
                                        wire:click="openUploadModal({{ $currentClient->id }}, {{ $doc['sop_id'] }}, false)">
                                        Upload
                                    </x-filament::button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <div class="rounded-full bg-gray-100 p-3 dark:bg-gray-700">
                                        <x-heroicon-o-document class="h-6 w-6 text-gray-400" />
                                    </div>
                                    <p class="mt-3 text-sm font-medium text-gray-500 dark:text-gray-400">Tidak ada
                                        dokumen legal yang diperlukan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse

                        {{-- SECTION 2: Required Additional Documents --}}
                        @if($requiredAdditionalDocuments->isNotEmpty())
                        <tr class="bg-gradient-to-r from-amber-50 to-transparent dark:from-amber-900/10">
                            <td colspan="5" class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-1 w-1 rounded-full bg-amber-500"></div>
                                    <span
                                        class="text-xs font-bold uppercase tracking-wider text-amber-900 dark:text-amber-300">Dokumen
                                        Tambahan yang Dibutuhkan</span>
                                    <div
                                        class="h-px flex-1 bg-gradient-to-r from-amber-200 to-transparent dark:from-amber-800">
                                    </div>
                                    <span
                                        class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                        {{ $requiredAdditionalDocuments->count() }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @foreach($requiredAdditionalDocuments as $requirement)
                        <tr class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="mt-1 rounded-lg bg-amber-100 p-2 dark:bg-amber-900/30">
                                        <x-heroicon-o-document-text
                                            class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{
                                            $requirement->name }}</p>
                                        @if($requirement->description)
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{
                                            $requirement->description }}</p>
                                        @endif
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            @if($requirement->is_required)
                                            <span
                                                class="inline-flex items-center gap-1 rounded-md bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700 shadow-sm ring-2 ring-red-600/30 dark:bg-red-900/40 dark:text-red-300 dark:ring-red-400/40">
                                                <x-heroicon-o-exclamation-circle class="h-3.5 w-3.5" />
                                                WAJIB
                                            </span>
                                            @endif
                                            @if($requirement->isOverdue())
                                            <span
                                                class="inline-flex items-center gap-1 rounded-md bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700 shadow-sm ring-2 ring-red-600/30 dark:bg-red-900/40 dark:text-red-300 dark:ring-red-400/40">
                                                <x-heroicon-o-clock class="h-3.5 w-3.5" />
                                                TERLAMBAT
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="hidden whitespace-nowrap px-6 py-4 sm:table-cell">
                                <span
                                    class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold shadow-sm ring-2 ring-inset
                                    {{ $requirement->category === 'legal' ? 'bg-blue-100 text-blue-700 ring-blue-600/30 dark:bg-blue-900/40 dark:text-blue-300 dark:ring-blue-400/40' : '' }}
                                    {{ $requirement->category === 'financial' ? 'bg-emerald-100 text-emerald-700 ring-emerald-600/30 dark:bg-emerald-900/40 dark:text-emerald-300 dark:ring-emerald-400/40' : '' }}
                                    {{ $requirement->category === 'operational' ? 'bg-purple-100 text-purple-700 ring-purple-600/30 dark:bg-purple-900/40 dark:text-purple-300 dark:ring-purple-400/40' : '' }}
                                    {{ $requirement->category === 'compliance' ? 'bg-amber-100 text-amber-700 ring-amber-600/30 dark:bg-amber-900/40 dark:text-amber-300 dark:ring-amber-400/40' : '' }}
                                    {{ $requirement->category === 'other' ? 'bg-gray-100 text-gray-700 ring-gray-600/30 dark:bg-gray-700 dark:text-gray-200 dark:ring-gray-400/40' : '' }}">
                                    {{ strtoupper($requirement->category) }}
                                </span>
                            </td>

                            <td class="hidden whitespace-nowrap px-6 py-4 text-sm md:table-cell">
                                @if($requirement->due_date)
                                <div
                                    class="flex items-center gap-1.5 text-xs @if($requirement->isOverdue()) text-red-600 dark:text-red-400 font-bold @else text-gray-600 dark:text-gray-300 @endif">
                                    <x-heroicon-o-calendar class="h-3.5 w-3.5" />
                                    <span>{{ $requirement->due_date->format('d M Y') }}</span>
                                </div>
                                @else
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                @php $latestDoc = $requirement->getLatestDocument(); @endphp
                                @if($latestDoc)
                                @php $statusBadge = $latestDoc->status_badge; @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold shadow-md {{ $statusBadge['class'] }}">
                                    <x-dynamic-component :component="$statusBadge['icon']" class="h-4 w-4" />
                                    {{ $statusBadge['text'] }}
                                </span>
                                @else
                                @php $reqStatusBadge = $requirement->status_badge; @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold shadow-md {{ $reqStatusBadge['class'] }}">
                                    <x-dynamic-component :component="$reqStatusBadge['icon']" class="h-4 w-4" />
                                    {{ $reqStatusBadge['text'] }}
                                </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @php $latestDoc = $requirement->getLatestDocument(); @endphp
                                    @if($latestDoc)
                                    <x-filament::icon-button icon="heroicon-o-eye" size="sm"
                                        wire:click="previewDocuments({{ $latestDoc->id }})" tooltip="Lihat" />
                                    @if($latestDoc->status !== 'valid')
                                    <x-filament::icon-button icon="heroicon-o-arrow-up-tray" size="sm" color="warning"
                                        wire:click="openUploadModal({{ $currentClient->id }}, null, false, {{ $requirement->id }})"
                                        tooltip="Upload Ulang" />
                                    @endif
                                    @else
                                    <x-filament::button size="sm"
                                        wire:click="openUploadModal({{ $currentClient->id }}, null, false, {{ $requirement->id }})">
                                        Upload
                                    </x-filament::button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @endif

                        {{-- SECTION 3: Additional Documents --}}
                        <tr class="bg-gradient-to-r from-gray-50 to-transparent dark:from-gray-700/30">
                            <td colspan="5" class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-1 w-1 rounded-full bg-gray-500"></div>
                                    <span
                                        class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Dokumen
                                        Tambahan Lainnya</span>
                                    <div
                                        class="h-px flex-1 bg-gradient-to-r from-gray-200 to-transparent dark:from-gray-700">
                                    </div>
                                    <span
                                        class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-bold text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        {{ $additionalDocuments->count() }}
                                    </span>
                                    <x-filament::button size="xs"
                                        wire:click="openUploadModal({{ $currentClient->id }}, null, true)"
                                        icon="heroicon-o-plus">
                                        Tambah
                                    </x-filament::button>
                                </div>
                            </td>
                        </tr>
                        @forelse($additionalDocuments as $doc)
                        <tr class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="mt-1 rounded-lg bg-gray-100 p-2 dark:bg-gray-700">
                                        <x-heroicon-o-document class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{
                                            $doc->description ?? $doc->original_filename }}</p>
                                        @if($doc->description && $doc->original_filename !== $doc->description)
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{
                                            $doc->original_filename }}</p>
                                        @endif
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 sm:hidden">
                                            {{ $doc->created_at->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td
                                class="hidden whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300 sm:table-cell">
                                <div class="flex items-center gap-1.5 text-xs">
                                    <x-heroicon-o-clock class="h-3.5 w-3.5 text-gray-400" />
                                    <span>{{ $doc->created_at->format('d M Y') }}</span>
                                </div>
                            </td>

                            <td class="hidden px-6 py-4 md:table-cell">
                                <span class="text-gray-400 text-sm">-</span>
                            </td>

                            <td class="px-6 py-4">
                                @php $statusBadge = $doc->status_badge; @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold shadow-md {{ $statusBadge['class'] }}">
                                    <x-dynamic-component :component="$statusBadge['icon']" class="h-4 w-4" />
                                    {{ $statusBadge['text'] }}
                                </span>
                                @if($doc->admin_notes)
                                <div
                                    class="mt-2 rounded-lg bg-amber-50 p-2 text-xs text-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                                    <span class="font-medium">Catatan:</span> {{ $doc->admin_notes }}
                                </div>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-filament::icon-button icon="heroicon-o-eye" size="sm"
                                        wire:click="previewDocuments({{ $doc->id }})" tooltip="Preview" />

                                    <x-filament::icon-button icon="heroicon-o-arrow-down-tray" size="sm"
                                        class="hidden sm:inline-flex" wire:click="downloadDocument({{ $doc->id }})"
                                        tooltip="Download" />

                                    @if($doc->status !== 'valid')
                                    <x-filament::icon-button icon="heroicon-o-trash" size="sm" color="danger"
                                        wire:click="deleteDocumentConfirm({{ $doc->id }})" tooltip="Hapus" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <div class="rounded-full bg-gray-100 p-3 dark:bg-gray-700">
                                        <x-heroicon-o-document class="h-6 w-6 text-gray-400" />
                                    </div>
                                    <p class="mt-3 text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada
                                        dokumen tambahan</p>
                                </div>
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
            <div class="flex items-center gap-3">
                <div class="rounded-xl bg-gray-100 p-2.5 dark:bg-gray-700">
                    <x-heroicon-o-cloud-arrow-up class="h-6 w-6 text-gray-700 dark:text-gray-300" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        @if($selectedRequirementId)
                        Upload untuk Persyaratan
                        @else
                        Upload Dokumen
                        @endif
                    </h3>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Pilih file yang akan diupload</p>
                </div>
            </div>
        </x-slot>

        <form wire:submit="uploadDocument">
            {{ $this->form }}

            <div class="mt-6 flex justify-end gap-3">
                <x-filament::button color="gray" wire:click="closeUploadModal" type="button">
                    Batal
                </x-filament::button>

                <x-filament::button type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="uploadDocument">Upload</span>
                    <span wire:loading wire:target="uploadDocument">Mengupload...</span>
                </x-filament::button>
            </div>
        </form>
    </x-filament::modal>

    {{-- Preview Modal --}}
    <x-filament::modal id="preview-document-modal" width="7xl">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="rounded-xl bg-gray-100 p-2.5 dark:bg-gray-700">
                    <x-heroicon-o-document-text class="h-6 w-6 text-gray-700 dark:text-gray-300" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Preview Dokumen</h3>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Pratinjau file dokumen</p>
                </div>
            </div>
        </x-slot>

        @if($previewDocument)
        <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-900">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama
                            File</dt>
                        <dd class="mt-1.5 text-sm font-medium text-gray-900 dark:text-white">{{
                            $previewDocument->original_filename }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Upload
                            Oleh</dt>
                        <dd class="mt-1.5 text-sm font-medium text-gray-900 dark:text-white">{{
                            $previewDocument->user->name ?? '-' }}</dd>
                    </div>
                    @if($previewDocument->document_number)
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Nomor
                            Dokumen</dt>
                        <dd class="mt-1.5 text-sm font-medium text-gray-900 dark:text-white">{{
                            $previewDocument->document_number }}</dd>
                    </div>
                    @endif
                    @if($previewDocument->expired_at)
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Kadaluarsa</dt>
                        <dd class="mt-1.5 text-sm font-medium text-gray-900 dark:text-white">{{
                            $previewDocument->expired_at->format('d M Y') }}</dd>
                    </div>
                    @endif
                    @if($previewDocument->admin_notes)
                    <div class="col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Catatan
                            Admin</dt>
                        <dd
                            class="mt-1.5 rounded-lg bg-amber-50 p-3 text-sm text-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                            {{ $previewDocument->admin_notes }}
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            <div
                class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
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
                <div class="flex flex-col items-center justify-center p-16">
                    <div class="rounded-full bg-gray-100 p-4 dark:bg-gray-700">
                        <x-heroicon-o-document class="h-12 w-12 text-gray-400" />
                    </div>
                    <p class="mt-4 text-sm font-medium text-gray-600 dark:text-gray-400">Preview tidak tersedia untuk
                        tipe file ini</p>
                    <x-filament::button wire:click="downloadDocument({{ $previewDocument->id }})" class="mt-4">
                        Download File
                    </x-filament::button>
                </div>
                @endif
            </div>
        </div>
        @endif

        <x-slot name="footerActions">
            <x-filament::button color="gray" wire:click="closePreviewModal">
                Tutup
            </x-filament::button>
            @if($previewDocument)
            <x-filament::button wire:click="downloadDocument({{ $previewDocument->id }})"
                icon="heroicon-o-arrow-down-tray">
                Download
            </x-filament::button>
            @endif
        </x-slot>
    </x-filament::modal>

    {{-- Delete Confirmation Modal --}}
    <x-filament::modal id="confirm-delete-modal" width="md">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="rounded-xl bg-red-100 p-2.5 dark:bg-red-900/30">
                    <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Konfirmasi Hapus</h3>
            </div>
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
</div>