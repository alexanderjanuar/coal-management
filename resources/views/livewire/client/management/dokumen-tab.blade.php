{{-- resources/views/livewire/client/management/dokumen-tab.blade.php --}}

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
        .document-row-missing:hover::before { background: rgba(8, 145, 178, 0.10); }
        .document-row-missing > * { position: relative; z-index: 1; }
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
        .dark .document-row-missing::before { background: rgba(75, 85, 99, 0.18); }
        .dark .document-row-missing:hover::before { background: rgba(34, 211, 238, 0.12); }
    </style>

    @php
        $legalDocuments              = collect($checklist);
        $additionalDocumentsCollection = collect($additionalDocuments);
        $requiredDocumentsCollection = collect($requiredAdditionalDocuments);
        $groupsCollection            = collect($groups);

        // Separate grouped vs ungrouped requirements
        $ungroupedReqs = $requiredDocumentsCollection->filter(fn ($r) => ! $r->group_id);

        // Stats
        $validLegal      = $legalDocuments->filter(fn ($i) => ($i['uploaded_document']?->status ?? null) === 'valid')->count();
        $validRequired   = $requiredDocumentsCollection->filter(fn ($r) => $r->getLatestDocument()?->status === 'valid')->count();
        $validAdditional = $additionalDocumentsCollection->where('status', 'valid')->count();
        $validTotal      = $validLegal + $validRequired + $validAdditional;

        $needUploadLegal     = $legalDocuments->filter(fn ($i) => ! $i['is_uploaded'])->count();
        $needUploadRequired  = $requiredDocumentsCollection->filter(fn ($r) => ! $r->getLatestDocument())->count();
        $needUploadTotal     = $needUploadLegal + $needUploadRequired;

        $pendingLegal      = $legalDocuments->filter(fn ($i) => ($i['uploaded_document']?->status ?? null) === 'pending_review')->count();
        $pendingRequired   = $requiredDocumentsCollection->filter(fn ($r) => $r->getLatestDocument()?->status === 'pending_review')->count();
        $pendingAdditional = $additionalDocumentsCollection->where('status', 'pending_review')->count();
        $pendingTotal      = $pendingLegal + $pendingRequired + $pendingAdditional;

        $totalDocuments = $legalDocuments->count() + $requiredDocumentsCollection->count() + $additionalDocumentsCollection->count();
        $completePct    = $totalDocuments > 0 ? round(($validTotal / $totalDocuments) * 100) : 0;
    @endphp

    {{-- ── Card wrapper ─────────────────────────────────────────────────────── --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

        {{-- Stats bar --}}
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

        {{-- Header + action buttons --}}
        <div class="flex flex-col gap-2 border-b border-slate-100 bg-white px-4 py-3 dark:border-slate-800 dark:bg-slate-900 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Dokumen Client</h3>
                <p class="text-xs text-slate-400">Kelola dokumen legal, grup persyaratan, dan dokumen pendukung.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-filament::button wire:click="openCreateGroupModal" size="sm" color="gray" icon="heroicon-o-folder-plus">
                    Buat Grup
                </x-filament::button>
                <x-filament::button wire:click="openRequirementModal" size="sm" color="gray" icon="heroicon-o-clipboard-document-list">
                    Tambah Persyaratan
                </x-filament::button>
                <x-filament::button wire:click="openUploadModal(null, true)" size="sm" icon="heroicon-o-plus">
                    Tambah Dokumen
                </x-filament::button>
            </div>
        </div>

        {{-- ── Section 1: Dokumen Legal Wajib (cyan) ───────────────────────── --}}
        <div>
            <div class="flex items-center gap-2 border-b border-cyan-100 bg-cyan-50/70 px-4 py-2.5 backdrop-blur dark:border-cyan-900/30 dark:bg-cyan-900/10">
                <x-heroicon-o-shield-check class="h-3.5 w-3.5 shrink-0 text-cyan-500" />
                <span class="text-[11px] font-bold uppercase tracking-widest text-cyan-700 dark:text-cyan-400">Dokumen Legal Wajib</span>
                <div class="h-px flex-1 bg-cyan-200/60 dark:bg-cyan-800/40"></div>
                <span class="inline-flex items-center rounded-full bg-cyan-100 px-2 py-0.5 text-[10px] font-bold tabular-nums text-cyan-600 dark:bg-cyan-900/30 dark:text-cyan-400">{{ $legalDocuments->count() }}</span>
            </div>

            @forelse($legalDocuments as $item)
            @php
                $doc         = $item['uploaded_document'] ?? null;
                $isUploaded  = $item['is_uploaded'] && $doc;
                $isExpired   = $isUploaded && $doc->expired_at && \Carbon\Carbon::parse($doc->expired_at)->isPast();
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
                        <x-filament::icon-button icon="heroicon-o-eye" color="gray" size="sm" tooltip="Preview & Review" wire:click="previewDocuments({{ $doc->id }})" />
                        <x-filament::icon-button icon="heroicon-o-arrow-down-tray" color="gray" size="sm" tooltip="Download" wire:click="downloadDocument({{ $doc->id }})" />
                        <x-filament::icon-button icon="heroicon-o-arrow-path" color="gray" size="sm" tooltip="Upload Ulang" wire:click="openUploadModal({{ $item['sop_id'] }}, false)" />
                        <x-filament::icon-button icon="heroicon-o-trash" color="gray" size="sm" tooltip="Hapus" wire:click="deleteDocumentConfirm({{ $doc->id }})" />
                        @else
                        <x-filament::button wire:click="openUploadModal({{ $item['sop_id'] }}, false)" size="sm" icon="heroicon-o-arrow-up-tray">Upload</x-filament::button>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="px-4 py-10 text-center text-sm text-slate-400">Tidak ada dokumen legal wajib.</div>
            @endforelse
        </div>

        {{-- ── Section 2: Grup Persyaratan (violet) ────────────────────────── --}}
        <div>
            <div class="flex items-center gap-2 border-b border-t border-violet-100 bg-violet-50/70 px-4 py-2.5 backdrop-blur dark:border-violet-900/30 dark:bg-violet-900/10">
                <x-heroicon-o-folder-open class="h-3.5 w-3.5 shrink-0 text-violet-500" />
                <span class="text-[11px] font-bold uppercase tracking-widest text-violet-700 dark:text-violet-400">Grup Persyaratan</span>
                <div class="h-px flex-1 bg-violet-200/60 dark:bg-violet-800/40"></div>
                <span class="inline-flex items-center rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-bold tabular-nums text-violet-600 dark:bg-violet-900/30 dark:text-violet-400">{{ $groupsCollection->count() }}</span>
                <x-filament::button wire:click="openCreateGroupModal" size="xs" color="gray" icon="heroicon-o-folder-plus">
                    Buat Grup
                </x-filament::button>
            </div>

            @forelse($groupsCollection as $group)
            @php
                $groupReqs      = collect($group->requirements);
                $groupTotal     = $groupReqs->count();
                $groupFulfilled = $groupReqs->filter(function ($requirement) {
                    $latestDocument = $requirement->getLatestDocument();

                    return $latestDocument
                        ? $latestDocument->status === 'valid'
                        : $requirement->status === 'fulfilled';
                })->count();
                $groupPct       = $groupTotal > 0 ? round(($groupFulfilled / $groupTotal) * 100) : 0;
                $groupIsOverdue = $group->status === 'active' && $group->due_date && $group->due_date->isPast();
                $groupStatusClass = match($group->status) {
                    'active'    => 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
                    'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                    default     => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                };
                $groupStatusLabel = match($group->status) {
                    'active'    => 'Aktif',
                    'completed' => 'Selesai',
                    'cancelled' => 'Dibatalkan',
                    default     => $group->status,
                };
            @endphp

            <div
                x-data="{ open: true }"
                x-bind:class="{ 'bg-violet-50/25 dark:bg-violet-900/10': open }"
                class="border-b border-slate-100 transition-colors duration-200 dark:border-slate-800"
            >
                {{-- Group header --}}
                <div @click="open = !open"
                     x-bind:aria-expanded="open.toString()"
                     x-bind:class="{ 'bg-violet-50/80 dark:bg-violet-900/20': open }"
                     class="relative flex cursor-pointer items-center gap-3 px-4 py-3 transition-colors hover:bg-violet-50/40 dark:hover:bg-violet-900/10">
                    <span x-show="open" x-transition.opacity class="absolute inset-y-2 left-0 w-1 rounded-r-full bg-violet-500"></span>
                    <x-heroicon-o-chevron-right
                        class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200"
                        x-bind:class="{ 'text-violet-500': open }"
                        x-bind:style="{ transform: open ? 'rotate(90deg)' : 'rotate(0deg)' }" />

                    <div
                        x-bind:class="{ 'border-violet-300 bg-violet-100 shadow-sm shadow-violet-100 dark:border-violet-700 dark:bg-violet-900/40 dark:shadow-none': open }"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-violet-200 bg-violet-50 transition-colors dark:border-violet-800/50 dark:bg-violet-900/20"
                    >
                        <x-heroicon-o-folder-open class="h-4 w-4 text-violet-500" x-show="open" />
                        <x-heroicon-o-folder class="h-4 w-4 text-violet-400" x-show="!open" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span x-bind:class="{ 'text-violet-900 dark:text-violet-100': open }" class="text-sm font-semibold text-slate-800 transition-colors dark:text-slate-200">{{ $group->name }}</span>
                            @if($group->year)
                            <span class="inline-flex rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold leading-none text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $group->year }}</span>
                            @endif
                            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $groupStatusClass }}">{{ $groupStatusLabel }}</span>
                            @if($groupIsOverdue)
                            <span class="inline-flex rounded bg-rose-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">Terlambat</span>
                            @endif
                        </div>
                        <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] text-slate-400 dark:text-slate-500">
                            @if($group->due_date)
                            <span class="{{ $groupIsOverdue ? 'text-rose-500 dark:text-rose-400' : '' }}">Tenggat {{ $group->due_date->format('d M Y') }}</span>
                            @endif
                            @if($group->description)
                            <span>{{ \Illuminate\Support\Str::limit($group->description, 60) }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Progress --}}
                    <div class="hidden shrink-0 items-center gap-2 sm:flex">
                        <div class="h-1 w-20 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-full rounded-full bg-violet-500 transition-all duration-500" style="width: {{ $groupPct }}%"></div>
                        </div>
                        <span class="text-[11px] font-semibold tabular-nums text-slate-400">{{ $groupFulfilled }}/{{ $groupTotal }}</span>
                    </div>

                    {{-- Actions (stop propagation so click doesn't toggle) --}}
                    <div class="flex shrink-0 items-center gap-1" @click.stop>
                        <x-filament::icon-button icon="heroicon-o-plus" color="gray" size="sm" tooltip="Tambah Persyaratan ke Grup"
                            wire:click="openRequirementModalForGroup({{ $group->id }})" />
                        <x-filament::icon-button icon="heroicon-o-pencil-square" color="gray" size="sm" tooltip="Edit Grup"
                            wire:click="openEditGroupModal({{ $group->id }})" />
                        <x-filament::icon-button icon="heroicon-o-trash" color="gray" size="sm" tooltip="Hapus Grup"
                            wire:click="deleteGroup({{ $group->id }})"
                            wire:confirm="Yakin ingin menghapus grup ini? Persyaratan di dalamnya tidak akan terhapus." />
                    </div>
                </div>

                {{-- Requirements inside group --}}
                <div x-show="open" class="border-t border-violet-100/60 dark:border-violet-900/20">
                    @forelse($groupReqs as $requirement)
                    @php
                        $latestDoc         = $requirement->getLatestDocument();
                        $statusBadge       = $requirement->status_badge;
                        $docStatusBadge    = $latestDoc?->status_badge;
                        $displayStatusBadge = $latestDoc ? $docStatusBadge : $statusBadge;
                        $isMissingUpload   = $requirement->status === 'pending' && ! $latestDoc;
                        $categoryClass     = match($requirement->category ?? 'other') {
                            'legal'       => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                            'financial'   => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400',
                            'operational' => 'bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400',
                            'compliance'  => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-900/20 dark:text-cyan-400',
                            default       => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                        };
                    @endphp
                    <div class="group relative flex flex-col gap-3 overflow-hidden border-b border-slate-50 py-3.5 pl-14 pr-4 transition-colors hover:bg-slate-50/70 dark:border-slate-800/40 dark:hover:bg-slate-800/20 sm:flex-row sm:items-center {{ $isMissingUpload ? 'document-row-missing border-b-slate-100 dark:border-b-slate-800' : '' }}">
                        <div class="flex min-w-0 flex-1 items-start gap-3">
                            <div class="flex shrink-0 items-center gap-2.5">
                                <div class="h-9 w-0.5 rounded-full {{ $latestDoc ? 'bg-violet-400' : 'bg-slate-300 dark:bg-slate-700' }}"></div>
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg border {{ $latestDoc ? 'border-violet-100 bg-violet-50 dark:border-violet-900/40 dark:bg-violet-900/20' : 'border-slate-200 bg-white/70 dark:border-slate-700 dark:bg-slate-800/70' }}">
                                    <x-heroicon-o-document-text class="h-4 w-4 {{ $latestDoc ? 'text-violet-500' : 'text-slate-400' }}" />
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="text-sm font-medium {{ $isMissingUpload ? 'text-slate-600 dark:text-slate-400' : 'text-slate-800 dark:text-slate-200' }}">{{ $requirement->name }}</span>
                                    @if($requirement->is_required)
                                    <span class="inline-flex rounded bg-violet-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">Wajib</span>
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
                                @if($isMissingUpload)
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
                    <div class="px-14 py-6 text-center">
                        <p class="mb-3 text-sm text-slate-400">Grup ini belum memiliki persyaratan.</p>
                        <x-filament::button wire:click="openRequirementModalForGroup({{ $group->id }})" size="sm" color="gray" icon="heroicon-o-plus">
                            Tambah Persyaratan ke Grup
                        </x-filament::button>
                    </div>
                    @endforelse
                </div>
            </div>

            @empty
            <div class="px-4 py-10 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-violet-50 dark:bg-violet-900/20">
                    <x-heroicon-o-folder-plus class="h-6 w-6 text-violet-400" />
                </div>
                <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Belum ada grup persyaratan</p>
                <p class="mt-1 text-xs text-slate-400">Buat grup untuk mengorganisir persyaratan seperti SPD2K atau SPT Tahunan</p>
                <x-filament::button wire:click="openCreateGroupModal" size="sm" color="gray" icon="heroicon-o-folder-plus" class="mt-4">
                    Buat Grup Pertama
                </x-filament::button>
            </div>
            @endforelse
        </div>

        {{-- ── Section 3: Persyaratan Lainnya / Ungrouped (amber) ─────────── --}}
        <div>
            <div class="flex items-center gap-2 border-b border-t border-amber-100 bg-amber-50/70 px-4 py-2.5 backdrop-blur dark:border-amber-900/30 dark:bg-amber-900/10">
                <x-heroicon-o-exclamation-circle class="h-3.5 w-3.5 shrink-0 text-amber-500" />
                <span class="text-[11px] font-bold uppercase tracking-widest text-amber-700 dark:text-amber-400">Persyaratan Lainnya</span>
                <div class="h-px flex-1 bg-amber-200/60 dark:bg-amber-800/40"></div>
                <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold tabular-nums text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">{{ $ungroupedReqs->count() }}</span>
            </div>

            @forelse($ungroupedReqs as $requirement)
            @php
                $latestDoc         = $requirement->getLatestDocument();
                $statusBadge       = $requirement->status_badge;
                $docStatusBadge    = $latestDoc?->status_badge;
                $displayStatusBadge = $latestDoc ? $docStatusBadge : $statusBadge;
                $isMissingUpload   = $requirement->status === 'pending' && ! $latestDoc;
                $categoryClass     = match($requirement->category ?? 'other') {
                    'legal'       => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                    'financial'   => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400',
                    'operational' => 'bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400',
                    'compliance'  => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-900/20 dark:text-cyan-400',
                    default       => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
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
                        @if($isMissingUpload)
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
            <div class="px-4 py-8 text-center">
                <p class="text-sm text-slate-400">
                    @if($groupsCollection->count() > 0)
                        Semua persyaratan sudah diorganisir dalam grup.
                    @else
                        Belum ada persyaratan tambahan.
                    @endif
                </p>
                <x-filament::button wire:click="openRequirementModal" size="sm" color="gray" icon="heroicon-o-plus" class="mt-3">
                    Tambah Persyaratan
                </x-filament::button>
            </div>
            @endforelse
        </div>

        {{-- ── Section 4: Dokumen Tambahan (slate) ─────────────────────────── --}}
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
                $isExpired   = $doc->expired_at && \Carbon\Carbon::parse($doc->expired_at)->isPast();
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
                        <x-filament::icon-button icon="heroicon-o-eye" color="gray" size="sm" tooltip="Preview & Review" wire:click="previewDocuments({{ $doc->id }})" />
                        <x-filament::icon-button icon="heroicon-o-arrow-down-tray" color="gray" size="sm" tooltip="Download" wire:click="downloadDocument({{ $doc->id }})" />
                        <x-filament::icon-button icon="heroicon-o-trash" color="gray" size="sm" tooltip="Hapus" wire:click="deleteDocumentConfirm({{ $doc->id }})" />
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

    </div>{{-- end card --}}

    {{-- ── Modals ──────────────────────────────────────────────────────────── --}}

    {{-- Upload / Requirement Modal --}}
    <x-filament::modal id="upload-document-modal" width="5xl">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-primary-100 p-2 dark:bg-primary-900/30">
                    <x-heroicon-o-cloud-arrow-up class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                </div>
                <span class="text-lg font-semibold">
                    @if($isRequirementMode) Tambah Persyaratan Dokumen
                    @elseif($selectedRequirementId) Upload untuk Persyaratan
                    @elseif($isAdditionalDocument) Upload Dokumen Tambahan
                    @else Upload Dokumen Legal
                    @endif
                </span>
            </div>
        </x-slot>

        <form wire:submit="uploadDocument" class="space-y-6">
            {{ $this->form }}
            <x-slot name="footerActions">
                <x-filament::button color="gray" wire:click="closeUploadModal" type="button">Batal</x-filament::button>
                <x-filament::button type="submit" wire:click="uploadDocument" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="uploadDocument">{{ $isRequirementMode ? 'Tambah Persyaratan' : 'Upload Dokumen' }}</span>
                    <span wire:loading wire:target="uploadDocument">{{ $isRequirementMode ? 'Menambahkan...' : 'Mengupload...' }}</span>
                </x-filament::button>
            </x-slot>
        </form>
    </x-filament::modal>

    {{-- Group Create / Edit Modal --}}
    <x-filament::modal id="group-modal" width="2xl">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-violet-100 p-2 dark:bg-violet-900/30">
                    <x-heroicon-o-folder-plus class="h-5 w-5 text-violet-600 dark:text-violet-400" />
                </div>
                <span class="text-lg font-semibold">
                    {{ $editingGroupId ? 'Edit Grup Persyaratan' : 'Buat Grup Persyaratan' }}
                </span>
            </div>
        </x-slot>

        <div class="space-y-4">
            {{-- Name --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nama Grup <span class="text-red-500">*</span>
                </label>
                <input wire:model="groupName" type="text"
                    placeholder="e.g. SPD2K, SPT Tahunan 2024, Persiapan Audit"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white sm:text-sm" />
                @error('groupName')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                {{-- Year --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tahun</label>
                    <input wire:model="groupYear" type="number" placeholder="{{ now()->year }}" min="2000" max="2100"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white sm:text-sm" />
                    @error('groupYear')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                {{-- Status --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select wire:model="groupStatus"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white sm:text-sm">
                        <option value="active">Aktif</option>
                        <option value="completed">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
            </div>

            {{-- Due Date --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tenggat Waktu <span class="text-slate-400 font-normal">(opsional)</span></label>
                <input wire:model="groupDueDate" type="date"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white sm:text-sm" />
                @error('groupDueDate')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi <span class="text-slate-400 font-normal">(opsional)</span></label>
                <textarea wire:model="groupDescription" rows="2"
                    placeholder="Jelaskan tujuan atau konteks grup ini..."
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white sm:text-sm"></textarea>
            </div>

            {{-- Notes --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan Internal <span class="text-slate-400 font-normal">(opsional)</span></label>
                <textarea wire:model="groupNotes" rows="2"
                    placeholder="Catatan internal untuk tim admin..."
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white sm:text-sm"></textarea>
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button color="gray" wire:click="closeGroupModal" type="button">Batal</x-filament::button>
            <x-filament::button wire:click="saveGroup">
                {{ $editingGroupId ? 'Simpan Perubahan' : 'Buat Grup' }}
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    {{-- Delete Confirmation Modal --}}
    <x-filament::modal id="confirm-delete-modal" width="md">
        <x-slot name="heading">Konfirmasi Hapus</x-slot>
        <x-slot name="description">Apakah Anda yakin ingin menghapus dokumen ini? Tindakan ini tidak dapat dibatalkan.</x-slot>
        <x-slot name="footer">
            <div class="flex justify-end gap-3">
                <x-filament::button color="gray" wire:click="closeDeleteModal">Batal</x-filament::button>
                <x-filament::button color="danger" wire:click="deleteDocument">Hapus</x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

    {{-- Preview & Review Modal --}}
    <x-filament::modal id="preview-document-modal" width="7xl">
        @if($previewDocument)
        @php
            $extension = pathinfo($previewDocument->file_path ?? '', PATHINFO_EXTENSION);
            $isImage   = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            $isPdf     = strtolower($extension) === 'pdf';
            $statusBadge = $previewDocument->status_badge;
            $documentDisplayName = $previewDocument->requirement?->name
                ?? $previewDocument->sopLegalDocument?->name
                ?? $previewDocument->description
                ?? $previewDocument->original_filename;
            $documentDisplayLabel = $previewDocument->requirement
                ? 'Persyaratan'
                : ($previewDocument->sopLegalDocument ? 'Jenis Dokumen' : 'Dokumen');
        @endphp

        <div
            x-data="{ showSidebar: window.innerWidth >= 1024 }"
            class="space-y-4"
        >
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-start gap-3">
                    <div class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-50 ring-1 ring-primary-100 dark:bg-primary-900/40 dark:ring-primary-800 sm:flex">
                        @if($isPdf)
                        <x-heroicon-o-document-text class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                        @elseif($isImage)
                        <x-heroicon-o-photo class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                        @else
                        <x-heroicon-o-document class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="truncate text-lg font-semibold leading-tight text-gray-900 dark:text-white sm:text-xl">{{ $documentDisplayName }}</h3>
                        </div>
                        <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400 sm:text-sm">
                            File asli: {{ $previewDocument->original_filename }} · Upload {{ $previewDocument->created_at->diffForHumans() }} oleh {{ $previewDocument->user->name ?? '-' }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-filament::dropdown placement="bottom-end">
                        <x-slot name="trigger">
                            <x-filament::button size="sm" :color="$previewDocument->status_color">
                                <div class="flex items-center gap-2">
                                    <x-dynamic-component :component="$statusBadge['icon']" class="h-4 w-4" />
                                    <span>{{ $previewDocument->status_label }}</span>
                                    <x-heroicon-m-chevron-down class="h-4 w-4" />
                                </div>
                            </x-filament::button>
                        </x-slot>
                        <x-filament::dropdown.list>
                            <x-filament::dropdown.list.item wire:click="updatePreviewDocumentStatus({{ $previewDocument->id }}, 'pending_review')" icon="heroicon-o-eye" color="warning">
                                Menunggu Review
                            </x-filament::dropdown.list.item>
                            <x-filament::dropdown.list.item wire:click="updatePreviewDocumentStatus({{ $previewDocument->id }}, 'valid')" icon="heroicon-o-check-circle" color="success">
                                Valid
                            </x-filament::dropdown.list.item>
                            <x-filament::dropdown.list.item wire:click="updatePreviewDocumentStatus({{ $previewDocument->id }}, 'rejected')" icon="heroicon-o-x-circle" color="danger">
                                Ditolak
                            </x-filament::dropdown.list.item>
                        </x-filament::dropdown.list>
                    </x-filament::dropdown>
                    <button
                        @click="showSidebar = !showSidebar"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-600 shadow-sm transition hover:border-primary-500 hover:bg-primary-50 hover:text-primary-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-primary-500 dark:hover:bg-primary-900/20 dark:hover:text-primary-400"
                        :class="{ 'border-primary-500 bg-primary-50 text-primary-600 dark:border-primary-500 dark:bg-primary-900/20 dark:text-primary-400': showSidebar }"
                    >
                        <x-heroicon-o-bars-3 x-show="!showSidebar" class="h-4 w-4" />
                        <x-heroicon-o-x-mark x-show="showSidebar" class="h-4 w-4" />
                        <span x-text="showSidebar ? 'Tutup Detail' : 'Detail'"></span>
                    </button>
                    <button
                        x-on:click="$dispatch('close-modal', { id: 'preview-document-modal' })"
                        wire:click="closePreviewModal"
                        type="button"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-50 hover:text-gray-600 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                    >
                        <span class="sr-only">Tutup</span>
                        <x-heroicon-m-x-mark class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <div
                class="flex flex-col gap-4 lg:flex-row"
                style="height: clamp(28rem, 68vh, 48rem); min-height: 28rem;"
            >
                <div class="min-w-0 flex-1 rounded-xl bg-gray-50 p-2 ring-1 ring-gray-200 transition-all duration-300 dark:bg-gray-900 dark:ring-gray-700 sm:p-4">
                    <div class="flex h-full items-center justify-center overflow-hidden rounded-lg bg-white dark:bg-gray-950">
                    @if($previewDocument->file_path && $isImage)
                    <img
                        src="{{ Storage::disk('public')->url($previewDocument->file_path) }}"
                        alt="{{ $documentDisplayName }}"
                        class="max-h-full max-w-full rounded-lg object-contain shadow-sm"
                    />
                    @elseif($previewDocument->file_path && $isPdf)
                    <iframe
                        src="{{ Storage::disk('public')->url($previewDocument->file_path) }}"
                        class="h-full w-full rounded-lg bg-white"
                        title="{{ $documentDisplayName }}"
                    ></iframe>
                    @else
                    <div class="px-6 py-12 text-center">
                        <x-heroicon-o-document class="mx-auto mb-4 h-16 w-16 text-gray-400" />
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Preview tidak tersedia untuk tipe file ini</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Silakan download file untuk melihat dokumen lengkap.</p>
                    </div>
                    @endif
                    </div>
                </div>

                <aside
                    x-show="showSidebar"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-x-6"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 translate-x-6"
                    class="min-h-0 w-full shrink-0 space-y-4 overflow-y-auto rounded-xl bg-gray-50/70 p-2 dark:bg-gray-900/50 lg:w-[400px]"
                >
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Informasi Dokumen</h4>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $documentDisplayName }}</p>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold shadow-sm {{ $statusBadge['class'] }}">
                            <x-dynamic-component :component="$statusBadge['icon']" class="h-3.5 w-3.5" />
                            {{ $statusBadge['text'] }}
                        </span>
                    </div>

                    <dl class="space-y-3">
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/70">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $documentDisplayLabel }}</dt>
                            <dd class="mt-1 break-words text-sm font-semibold text-gray-900 dark:text-white">{{ $documentDisplayName }}</dd>
                            <dd class="mt-1 break-words text-xs text-gray-500 dark:text-gray-400">File asli: {{ $previewDocument->original_filename }}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-1 xl:grid-cols-2">
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Diupload Oleh</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $previewDocument->user->name ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal Upload</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $previewDocument->created_at->format('d M Y, H:i') }}</dd>
                            </div>
                        </div>
                        @if($previewDocument->document_number)
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Nomor Dokumen</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $previewDocument->document_number }}</dd>
                        </div>
                        @endif
                        @if($previewDocument->expired_at)
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Kadaluarsa</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $previewDocument->expired_at->format('d M Y') }}</dd>
                        </div>
                        @endif
                        @if($previewDocument->reviewed_by)
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Direview Oleh</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $previewDocument->reviewer->name ?? '-' }}
                                @if($previewDocument->reviewed_at)
                                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $previewDocument->reviewed_at->format('d M Y, H:i') }}</span>
                                @endif
                            </dd>
                        </div>
                        @endif
                    </dl>

                    @if($previewDocument->admin_notes)
                    <div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-700">
                        <dt class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Catatan Admin</dt>
                        <dd class="rounded-lg bg-gray-50 p-3 text-sm text-gray-900 dark:bg-gray-800 dark:text-white">{{ $previewDocument->admin_notes }}</dd>
                    </div>
                    @endif
                </div>

                @if($previewDocument->status === 'pending_review')
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                    <h4 class="text-sm font-semibold text-amber-900 dark:text-amber-300">Review Dokumen</h4>
                    <p class="mt-1 text-xs text-amber-800/80 dark:text-amber-200/80">Catatan wajib saat menolak dokumen.</p>
                    <div class="mt-4 space-y-3">
                        <textarea wire:model="reviewNotes" rows="4"
                            class="block w-full rounded-lg border-amber-200 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-amber-900 dark:bg-gray-900 dark:text-white"
                            placeholder="Tambahkan catatan review..."></textarea>
                        <div class="grid grid-cols-2 gap-2">
                            <x-filament::button color="success" wire:click="quickApprove({{ $previewDocument->id }})" icon="heroicon-o-check-circle" size="sm">
                                Setujui
                            </x-filament::button>
                            <x-filament::button color="danger" wire:click="quickReject({{ $previewDocument->id }})" icon="heroicon-o-x-circle" size="sm">
                                Tolak
                            </x-filament::button>
                        </div>
                    </div>
                </div>
                @endif
                </aside>
            </div>
        </div>
        @endif

    </x-filament::modal>

</div>
