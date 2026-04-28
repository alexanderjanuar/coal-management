{{-- resources/views/livewire/client/components/dokumen-tab.blade.php --}}

<div class="space-y-6">
    <style>
        .document-row-missing {
            background-color: rgba(249, 250, 251, 0.88);
            background-image: repeating-linear-gradient(
                135deg,
                rgba(148, 163, 184, 0.18) 0,
                rgba(148, 163, 184, 0.18) 1px,
                transparent 1px,
                transparent 10px
            );
        }

        .document-row-missing:hover {
            border-color: rgba(8, 145, 178, 0.45);
            background-color: rgba(236, 254, 255, 0.72);
        }

        .document-row-missing::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background: rgba(107, 114, 128, 0.08);
            transition: background-color 180ms ease;
        }

        .document-row-missing:hover::before {
            background: rgba(8, 145, 178, 0.10);
        }

        .document-row-missing > * {
            position: relative;
            z-index: 1;
        }

        .dark .document-row-missing {
            background-color: rgba(17, 24, 39, 0.48);
            background-image: repeating-linear-gradient(
                135deg,
                rgba(148, 163, 184, 0.13) 0,
                rgba(148, 163, 184, 0.13) 1px,
                transparent 1px,
                transparent 10px
            );
        }

        .dark .document-row-missing:hover {
            border-color: rgba(34, 211, 238, 0.42);
            background-color: rgba(22, 78, 99, 0.28);
        }

        .dark .document-row-missing::before {
            background: rgba(75, 85, 99, 0.18);
        }

        .dark .document-row-missing:hover::before {
            background: rgba(34, 211, 238, 0.12);
        }
    </style>

    @php
        $legalDocuments = collect($checklist);
        $additionalDocumentsCollection = collect($additionalDocuments);
        $requiredDocumentsCollection = collect($requiredAdditionalDocuments);

        $validLegal = $legalDocuments->filter(fn ($item) => ($item['uploaded_document']?->status ?? null) === 'valid')->count();
        $validRequired = $requiredDocumentsCollection->filter(fn ($requirement) => $requirement->getLatestDocument()?->status === 'valid')->count();
        $validAdditional = $additionalDocumentsCollection->where('status', 'valid')->count();
        $validTotal = $validLegal + $validRequired + $validAdditional;

        $needUploadLegal = $legalDocuments->filter(fn ($item) => ! $item['is_uploaded'])->count();
        $needUploadRequired = $requiredDocumentsCollection->filter(fn ($requirement) => ! $requirement->getLatestDocument())->count();
        $needUploadTotal = $needUploadLegal + $needUploadRequired;

        $pendingLegal = $legalDocuments->filter(fn ($item) => ($item['uploaded_document']?->status ?? null) === 'pending_review')->count();
        $pendingRequired = $requiredDocumentsCollection->filter(fn ($requirement) => $requirement->getLatestDocument()?->status === 'pending_review')->count();
        $pendingAdditional = $additionalDocumentsCollection->where('status', 'pending_review')->count();
        $pendingTotal = $pendingLegal + $pendingRequired + $pendingAdditional;

        $totalDocuments = $legalDocuments->count() + $requiredDocumentsCollection->count() + $additionalDocumentsCollection->count();
        $completePct = $totalDocuments > 0 ? round(($validTotal / $totalDocuments) * 100) : 0;
    @endphp

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col divide-y divide-slate-100 border-b border-slate-100 dark:divide-slate-800 dark:border-slate-800 lg:flex-row lg:divide-x lg:divide-y-0">
            <div class="flex items-center gap-2 px-4 py-3">
                <span class="text-xl font-bold tabular-nums text-slate-900 dark:text-white">{{ $totalDocuments }}</span>
                <span class="text-xs text-slate-400">dokumen</span>
            </div>
            <div class="flex items-center gap-2 px-4 py-3">
                <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                <span class="text-sm font-semibold tabular-nums text-slate-700 dark:text-slate-300">{{ $validTotal }}</span>
                <span class="text-xs text-slate-400">valid</span>
            </div>
            <div class="flex items-center gap-2 px-4 py-3">
                <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                <span class="text-sm font-semibold tabular-nums text-slate-700 dark:text-slate-300">{{ $pendingTotal }}</span>
                <span class="text-xs text-slate-400">review</span>
            </div>
            <div class="flex items-center gap-2 px-4 py-3">
                <span class="h-2 w-2 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                <span class="text-sm font-semibold tabular-nums text-slate-700 dark:text-slate-300">{{ $needUploadTotal }}</span>
                <span class="text-xs text-slate-400">perlu upload</span>
            </div>
            <div class="flex min-w-0 flex-1 items-center gap-2.5 px-4 py-3">
                <div class="h-1 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-full rounded-full bg-cyan-500 transition-all duration-700" style="width: {{ $completePct }}%"></div>
                </div>
                <span class="shrink-0 text-[11px] font-semibold tabular-nums text-slate-400">{{ $completePct }}%</span>
            </div>
        </div>

        <div class="flex flex-col gap-2 border-b border-slate-100 bg-white px-4 py-3 dark:border-slate-800 dark:bg-slate-900 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Dokumen Client</h3>
                <p class="text-xs text-slate-400">Kelola dokumen legal, persyaratan tambahan, dan dokumen pendukung.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-filament::button wire:click="openRequirementModal" size="sm" color="gray" icon="heroicon-o-clipboard-document-list">
                    Tambah Persyaratan
                </x-filament::button>
                <x-filament::button wire:click="openUploadModal(null, true)" size="sm" icon="heroicon-o-plus">
                    Tambah Dokumen
                </x-filament::button>
            </div>
        </div>

        <div>
            <div class="flex items-center gap-2 border-b border-cyan-100 bg-cyan-50/70 px-4 py-2.5 backdrop-blur dark:border-cyan-900/30 dark:bg-cyan-900/10">
                <x-heroicon-o-shield-check class="h-3.5 w-3.5 shrink-0 text-cyan-500" />
                <span class="text-[11px] font-bold uppercase tracking-widest text-cyan-700 dark:text-cyan-400">Dokumen Legal Wajib</span>
                <div class="h-px flex-1 bg-cyan-200/60 dark:bg-cyan-800/40"></div>
                <span class="inline-flex items-center rounded-full bg-cyan-100 px-2 py-0.5 text-[10px] font-bold tabular-nums text-cyan-600 dark:bg-cyan-900/30 dark:text-cyan-400">{{ $legalDocuments->count() }}</span>
            </div>

            @forelse($legalDocuments as $item)
            @php
                $doc = $item['uploaded_document'] ?? null;
                $isUploaded = $item['is_uploaded'] && $doc;
                $isExpired = $isUploaded && $doc->expired_at && \Carbon\Carbon::parse($doc->expired_at)->isPast();
                $statusBadge = $isUploaded ? $doc->status_badge : null;
            @endphp
            <div class="group relative flex flex-col gap-3 overflow-hidden border-b border-slate-50 px-4 py-3.5 transition-colors hover:bg-slate-50/70 dark:border-slate-800/40 dark:hover:bg-slate-800/20 sm:flex-row sm:items-center {{ ! $isUploaded ? 'document-row-missing border-b-slate-100 dark:border-b-slate-800' : '' }}">
                <div class="flex min-w-0 flex-1 items-start gap-3">
                    <div class="flex shrink-0 items-center gap-2.5">
                        <div class="h-9 w-0.5 rounded-full {{ $isUploaded ? 'bg-cyan-400' : 'bg-slate-300 dark:bg-slate-700' }}"></div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg border {{ $isUploaded ? 'border-cyan-100 bg-cyan-50 dark:border-cyan-900/40 dark:bg-cyan-900/20' : 'border-slate-200 bg-white/70 dark:border-slate-700 dark:bg-slate-800/70' }}">
                            @if($isUploaded)
                            <x-heroicon-o-document-check class="h-4 w-4 text-cyan-500" />
                            @else
                            <x-heroicon-o-document class="h-4 w-4 text-slate-400" />
                            @endif
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-sm font-medium {{ $isUploaded ? 'text-slate-800 dark:text-slate-200' : 'text-slate-600 dark:text-slate-400' }}">{{ $item['name'] }}</span>
                            @if($item['is_required'])
                            <span class="inline-flex rounded bg-cyan-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">Wajib</span>
                            @endif
                        </div>
                        <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] text-slate-400 dark:text-slate-500">
                            @if($item['description'])
                            <span>{{ \Illuminate\Support\Str::limit($item['description'], 78) }}</span>
                            @endif
                            @if($doc?->document_number)
                            <span>No. {{ $doc->document_number }}</span>
                            @endif
                            @if($doc?->expired_at)
                            <span class="{{ $isExpired ? 'text-rose-500 dark:text-rose-400' : '' }}">s.d. {{ \Carbon\Carbon::parse($doc->expired_at)->format('d M Y') }}</span>
                            @endif
                            @if($doc?->admin_notes)
                            <span class="text-amber-500 dark:text-amber-400">{{ \Illuminate\Support\Str::limit($doc->admin_notes, 55) }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 sm:justify-end">
                    @if($isUploaded)
                    <span class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-[11px] font-semibold {{ $statusBadge['class'] }}">
                        <x-dynamic-component :component="$statusBadge['icon']" class="h-3 w-3" />
                        {{ $statusBadge['text'] }}
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] font-medium text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                        <x-heroicon-o-minus-circle class="h-3 w-3" />
                        Belum Upload
                    </span>
                    @endif

                    <div class="flex items-center gap-1">
                        @if($isUploaded)
                        <x-filament::icon-button icon="heroicon-o-eye" color="gray" size="sm" tooltip="Preview & Review"
                            wire:click="previewDocuments({{ $doc->id }})" />
                        <x-filament::icon-button icon="heroicon-o-arrow-down-tray" color="gray" size="sm" tooltip="Download"
                            wire:click="downloadDocument({{ $doc->id }})" />
                        <x-filament::icon-button icon="heroicon-o-arrow-path" color="gray" size="sm" tooltip="Upload Ulang"
                            wire:click="openUploadModal({{ $item['sop_id'] }}, false)" />
                        <x-filament::icon-button icon="heroicon-o-trash" color="gray" size="sm" tooltip="Hapus"
                            wire:click="deleteDocumentConfirm({{ $doc->id }})" />
                        @else
                        <x-filament::button wire:click="openUploadModal({{ $item['sop_id'] }}, false)" size="sm" icon="heroicon-o-arrow-up-tray">
                            Upload
                        </x-filament::button>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="px-4 py-10 text-center text-sm text-slate-400">Tidak ada dokumen legal wajib.</div>
            @endforelse
        </div>

        <div>
            <div class="flex items-center gap-2 border-b border-t border-amber-100 bg-amber-50/70 px-4 py-2.5 backdrop-blur dark:border-amber-900/30 dark:bg-amber-900/10">
                <x-heroicon-o-exclamation-circle class="h-3.5 w-3.5 shrink-0 text-amber-500" />
                <span class="text-[11px] font-bold uppercase tracking-widest text-amber-700 dark:text-amber-400">Dokumen Tambahan Dibutuhkan</span>
                <div class="h-px flex-1 bg-amber-200/60 dark:bg-amber-800/40"></div>
                <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold tabular-nums text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">{{ $requiredDocumentsCollection->count() }}</span>
            </div>

            @forelse($requiredDocumentsCollection as $requirement)
            @php
                $latestDoc = $requirement->getLatestDocument();
                $statusBadge = $requirement->status_badge;
                $docStatusBadge = $latestDoc?->status_badge;
                $displayStatusBadge = $latestDoc ? $docStatusBadge : $statusBadge;
                $isMissingUpload = $requirement->status === 'pending' && ! $latestDoc;
                $categoryClass = match($requirement->category ?? 'other') {
                    'legal' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                    'financial' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400',
                    'operational' => 'bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400',
                    'compliance' => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-900/20 dark:text-cyan-400',
                    default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                };
            @endphp
            <div class="group relative flex flex-col gap-3 overflow-hidden border-b border-slate-50 px-4 py-3.5 transition-colors hover:bg-slate-50/70 dark:border-slate-800/40 dark:hover:bg-slate-800/20 sm:flex-row sm:items-center {{ $isMissingUpload ? 'document-row-missing border-b-slate-100 dark:border-b-slate-800' : '' }}">
                <div class="flex min-w-0 flex-1 items-start gap-3">
                    <div class="flex shrink-0 items-center gap-2.5">
                        <div class="h-9 w-0.5 rounded-full {{ $latestDoc ? 'bg-amber-400' : 'bg-slate-300 dark:bg-slate-700' }}"></div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg border {{ $latestDoc ? 'border-amber-100 bg-amber-50 dark:border-amber-900/40 dark:bg-amber-900/20' : 'border-slate-200 bg-white/70 dark:border-slate-700 dark:bg-slate-800/70' }}">
                            <x-heroicon-o-document-text class="h-4 w-4 {{ $latestDoc ? 'text-amber-500' : 'text-slate-400' }}" />
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-sm font-medium {{ $isMissingUpload ? 'text-slate-600 dark:text-slate-400' : 'text-slate-800 dark:text-slate-200' }}">{{ $requirement->name }}</span>
                            @if($requirement->is_required)
                            <span class="inline-flex rounded bg-amber-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">Wajib</span>
                            @endif
                            @if($requirement->isOverdue())
                            <span class="inline-flex rounded bg-rose-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">Terlambat</span>
                            @endif
                            <span class="inline-flex rounded px-1.5 py-0.5 text-[10px] font-medium leading-none {{ $categoryClass }}">{{ strtoupper($requirement->category ?? 'OTHER') }}</span>
                        </div>
                        <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] text-slate-400 dark:text-slate-500">
                            @if($requirement->due_date)
                            <span class="{{ $requirement->isOverdue() ? 'text-rose-500 dark:text-rose-400' : '' }}">Tenggat {{ $requirement->due_date->format('d M Y') }}</span>
                            @endif
                            @if($requirement->description)
                            <span>{{ \Illuminate\Support\Str::limit($requirement->description, 72) }}</span>
                            @endif
                            @if($latestDoc?->admin_notes)
                            <span class="text-amber-500 dark:text-amber-400">{{ \Illuminate\Support\Str::limit($latestDoc->admin_notes, 55) }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 sm:justify-end">
                    <span class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-[11px] font-semibold {{ $displayStatusBadge['class'] }}">
                        <x-dynamic-component :component="$displayStatusBadge['icon']" class="h-3 w-3" />
                        {{ $displayStatusBadge['text'] }}
                    </span>

                    <div class="flex items-center gap-1">
                        @if($latestDoc)
                        <x-filament::icon-button icon="heroicon-o-eye" color="gray" size="sm" tooltip="Preview & Review"
                            wire:click="previewDocuments({{ $latestDoc->id }})" />
                        @endif
                        @if($requirement->status === 'pending')
                        <x-filament::button wire:click="openUploadModal(null, false, {{ $requirement->id }})" size="sm" icon="heroicon-o-arrow-up-tray">
                            Upload
                        </x-filament::button>
                        <x-filament::icon-button icon="heroicon-o-minus-circle" color="gray" size="sm" tooltip="Kecualikan"
                            wire:click="waiveRequirement({{ $requirement->id }})"
                            wire:confirm="Yakin ingin mengecualikan persyaratan ini?" />
                        @endif
                        <x-filament::icon-button icon="heroicon-o-trash" color="gray" size="sm" tooltip="Hapus Persyaratan"
                            wire:click="deleteRequirement({{ $requirement->id }})"
                            wire:confirm="Yakin ingin menghapus persyaratan ini?" />
                    </div>
                </div>
            </div>
            @empty
            <div class="px-4 py-10 text-center">
                <p class="mb-3 text-sm text-slate-400">Belum ada dokumen tambahan yang dibutuhkan.</p>
                <x-filament::button wire:click="openRequirementModal" size="sm" color="gray" icon="heroicon-o-plus">
                    Tambah Persyaratan Pertama
                </x-filament::button>
            </div>
            @endforelse
        </div>

        <div>
            <div class="flex items-center gap-2 border-b border-t border-slate-100 bg-slate-50/80 px-4 py-2.5 backdrop-blur dark:border-slate-800 dark:bg-slate-800/30">
                <x-heroicon-o-paper-clip class="h-3.5 w-3.5 shrink-0 text-slate-400" />
                <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Dokumen Tambahan</span>
                <div class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></div>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold tabular-nums text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $additionalDocumentsCollection->count() }}</span>
                <x-filament::button wire:click="openUploadModal(null, true)" size="xs" color="gray" icon="heroicon-o-plus">
                    Tambah
                </x-filament::button>
            </div>

            @forelse($additionalDocumentsCollection as $doc)
            @php
                $statusBadge = $doc->status_badge;
                $isExpired = $doc->expired_at && \Carbon\Carbon::parse($doc->expired_at)->isPast();
            @endphp
            <div class="group flex flex-col gap-3 border-b border-slate-50 px-4 py-3.5 transition-colors hover:bg-slate-50/70 dark:border-slate-800/40 dark:hover:bg-slate-800/20 sm:flex-row sm:items-center">
                <div class="flex min-w-0 flex-1 items-start gap-3">
                    <div class="flex shrink-0 items-center gap-2.5">
                        <div class="h-9 w-0.5 rounded-full bg-slate-200 dark:bg-slate-700"></div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                            <x-heroicon-o-document class="h-4 w-4 text-slate-400" />
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-medium text-slate-800 dark:text-slate-200">{{ $doc->description ?? $doc->original_filename }}</span>
                        <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] text-slate-400 dark:text-slate-500">
                            @if($doc->description && $doc->original_filename !== $doc->description)
                            <span class="max-w-[220px] truncate">{{ $doc->original_filename }}</span>
                            @endif
                            <span>{{ $doc->created_at->format('d M Y') }}</span>
                            @if($doc->expired_at)
                            <span class="{{ $isExpired ? 'text-rose-500 dark:text-rose-400' : '' }}">s.d. {{ \Carbon\Carbon::parse($doc->expired_at)->format('d M Y') }}</span>
                            @endif
                            @if($doc->admin_notes)
                            <span class="text-amber-500 dark:text-amber-400">{{ \Illuminate\Support\Str::limit($doc->admin_notes, 45) }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 sm:justify-end">
                    <span class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-[11px] font-semibold {{ $statusBadge['class'] }}">
                        <x-dynamic-component :component="$statusBadge['icon']" class="h-3 w-3" />
                        {{ $statusBadge['text'] }}
                    </span>
                    <div class="flex items-center gap-1">
                        <x-filament::icon-button icon="heroicon-o-eye" color="gray" size="sm" tooltip="Preview & Review"
                            wire:click="previewDocuments({{ $doc->id }})" />
                        <x-filament::icon-button icon="heroicon-o-arrow-down-tray" color="gray" size="sm" tooltip="Download"
                            wire:click="downloadDocument({{ $doc->id }})" />
                        <x-filament::icon-button icon="heroicon-o-trash" color="gray" size="sm" tooltip="Hapus"
                            wire:click="deleteDocumentConfirm({{ $doc->id }})" />
                    </div>
                </div>
            </div>
            @empty
            <div class="px-4 py-12 text-center">
                <p class="mb-3 text-sm text-slate-400">Belum ada dokumen tambahan.</p>
                <x-filament::button wire:click="openUploadModal(null, true)" size="sm" color="gray" icon="heroicon-o-plus">
                    Tambah Dokumen Pertama
                </x-filament::button>
            </div>
            @endforelse
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
                    @if($isRequirementMode)
                    Tambah Persyaratan Dokumen
                    @elseif($selectedRequirementId)
                    Upload untuk Persyaratan
                    @elseif($isAdditionalDocument)
                    Upload Dokumen Tambahan
                    @else
                    Upload Dokumen Legal
                    @endif
                </span>
            </div>
        </x-slot>

        <form wire:submit="uploadDocument" class="space-y-6">
            {{ $this->form }}

            <x-slot name="footerActions">
                <x-filament::button color="gray" wire:click="closeUploadModal" type="button">Batal</x-filament::button>
                <x-filament::button type="submit" wire:click="uploadDocument" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="uploadDocument">
                        {{ $isRequirementMode ? 'Tambah Persyaratan' : 'Upload Dokumen' }}
                    </span>
                    <span wire:loading wire:target="uploadDocument">
                        {{ $isRequirementMode ? 'Menambahkan...' : 'Mengupload...' }}
                    </span>
                </x-filament::button>
            </x-slot>
        </form>
    </x-filament::modal>

    <!-- Delete Confirmation Modal -->
    <x-filament::modal id="confirm-delete-modal" width="md">
        <x-slot name="heading">Konfirmasi Hapus</x-slot>
        <x-slot name="description">Apakah Anda yakin ingin menghapus dokumen ini? Tindakan ini tidak dapat dibatalkan.
        </x-slot>
        <x-slot name="footer">
            <div class="flex gap-3 justify-end">
                <x-filament::button color="gray" wire:click="closeDeleteModal">Batal</x-filament::button>
                <x-filament::button color="danger" wire:click="deleteDocument">Hapus</x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

    <!-- Combined Preview & Review Modal -->
    <x-filament::modal id="preview-document-modal" width="5xl">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="bg-primary-100 dark:bg-primary-900/30 p-2 rounded-lg">
                    <x-heroicon-o-document-text class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                </div>
                <span class="text-lg font-semibold">Preview & Review Dokumen</span>
            </div>
        </x-slot>

        @if($previewDocument)
        <div class="space-y-4">
            <!-- Document Information -->
            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama File</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $previewDocument->original_filename }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nomor Dokumen</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $previewDocument->document_number ??
                            '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Diupload Oleh</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $previewDocument->user->name ?? '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Upload</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{
                            $previewDocument->created_at->format('d M Y, H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1">
                            @php $statusBadge = $previewDocument->status_badge; @endphp
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg shadow-sm {{ $statusBadge['class'] }}">
                                <x-dynamic-component :component="$statusBadge['icon']" class="w-3.5 h-3.5" />
                                {{ $statusBadge['text'] }}
                            </span>
                        </dd>
                    </div>
                    @if($previewDocument->expired_at)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kadaluarsa</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{
                            $previewDocument->expired_at->format('d M Y') }}</dd>
                    </div>
                    @endif
                    @if($previewDocument->reviewed_by)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Direview Oleh</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $previewDocument->reviewer->name ?? '-' }}
                            @if($previewDocument->reviewed_at)
                            <span class="text-gray-400">• {{ $previewDocument->reviewed_at->format('d M Y, H:i')
                                }}</span>
                            @endif
                        </dd>
                    </div>
                    @endif
                </dl>

                @if($previewDocument->admin_notes)
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Catatan Admin</dt>
                    <dd class="text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-800 p-3 rounded-lg">{{
                        $previewDocument->admin_notes }}</dd>
                </div>
                @endif
            </div>

            <!-- Review Section - Only show if document is pending_review -->
            @if($previewDocument->status === 'pending_review')
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                <h4 class="text-sm font-medium text-amber-900 dark:text-amber-300 mb-3">Review Dokumen</h4>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Catatan Review (Opsional untuk Setujui, Wajib untuk Tolak)
                        </label>
                        <textarea wire:model="reviewNotes" rows="3"
                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Tambahkan catatan review..."></textarea>
                    </div>
                    <div class="flex gap-2">
                        <x-filament::button color="success" wire:click="quickApprove({{ $previewDocument->id }})"
                            icon="heroicon-o-check-circle" size="sm">
                            Setujui
                        </x-filament::button>
                        <x-filament::button color="danger" wire:click="quickReject({{ $previewDocument->id }})"
                            icon="heroicon-o-x-circle" size="sm">
                            Tolak
                        </x-filament::button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Document Preview -->
            @if($previewDocument->file_path)
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                @php
                $extension = pathinfo($previewDocument->file_path, PATHINFO_EXTENSION);
                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                $isPdf = strtolower($extension) === 'pdf';
                @endphp

                @if($isImage)
                <img src="{{ Storage::disk('public')->url($previewDocument->file_path) }}"
                    alt="{{ $previewDocument->original_filename }}" class="w-full h-auto">
                @elseif($isPdf)
                <iframe src="{{ Storage::disk('public')->url($previewDocument->file_path) }}"
                    class="w-full h-96"></iframe>
                @else
                <div class="p-8 text-center">
                    <x-heroicon-o-document class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                    <p class="text-gray-500 dark:text-gray-400">Preview tidak tersedia untuk tipe file ini</p>
                    <x-filament::button wire:click="downloadDocument({{ $previewDocument->id }})" class="mt-4"
                        icon="heroicon-o-arrow-down-tray">
                        Download File
                    </x-filament::button>
                </div>
                @endif
            </div>
            @endif
        </div>
        @endif

        <x-slot name="footerActions">
            <x-filament::button color="gray" wire:click="closePreviewModal">Tutup</x-filament::button>
            @if($previewDocument)
            <x-filament::button wire:click="downloadDocument({{ $previewDocument->id }})"
                icon="heroicon-o-arrow-down-tray">
                Download
            </x-filament::button>
            @endif
        </x-slot>
    </x-filament::modal>
</div>
