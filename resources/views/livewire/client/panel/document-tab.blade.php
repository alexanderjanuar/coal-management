<div class="w-full space-y-4 antialiased text-slate-900 dark:text-slate-100"
    x-data="{ mounted: false }"
    x-init="setTimeout(() => mounted = true, 50)">

    @if ($clients->isEmpty())
    {{-- Empty State --}}
    <div class="flex flex-col items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-12 text-center shadow-sm"
        x-show="mounted" x-transition.opacity.duration.300ms>
        <div class="w-14 h-14 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center mb-4">
            <x-heroicon-o-document-text class="h-6 w-6 text-slate-400" />
        </div>
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Tidak Ada Klien</h3>
        <p class="mt-1 text-sm text-slate-400">Anda belum memiliki akses. Hubungi administrator.</p>
    </div>
    @else
    @if($selectedClientId && $currentClient)

    @php
        // --- Stats (unfiltered) ---
        $statValidLegal    = $checklist->filter(fn($d) => ($d['uploaded_document']?->status ?? null) === 'valid')->count();
        $statValidRequired = $requiredAdditionalDocuments->filter(fn($r) => $r->getLatestDocument()?->status === 'valid')->count();
        $statValidExtra    = $this->filteredAdditionalDocs->where('status', 'valid')->count();
        $statValid         = $statValidLegal + $statValidRequired + $statValidExtra;

        $statNeedLegal    = $checklist->filter(fn($d) => !$d['is_uploaded'])->count();
        $statNeedRequired = $requiredAdditionalDocuments->filter(fn($r) => !$r->getLatestDocument())->count();
        $statNeedUpload   = $statNeedLegal + $statNeedRequired;

        $statPendingLegal    = $checklist->filter(fn($d) => ($d['uploaded_document']?->status ?? null) === 'pending_review')->count();
        $statPendingRequired = $requiredAdditionalDocuments->filter(fn($r) => $r->getLatestDocument()?->status === 'pending_review')->count();
        $statPendingExtra    = $this->filteredAdditionalDocs->where('status', 'pending_review')->count();
        $statPending         = $statPendingLegal + $statPendingRequired + $statPendingExtra;

        $statTotalCore  = $checklist->count() + $requiredAdditionalDocuments->count();
        $statTotal      = $statTotalCore + $this->filteredAdditionalDocs->count();
        $completePct    = $statTotal > 0 ? round($statValid / $statTotal * 100) : 0;
    @endphp

    <div x-show="mounted" x-transition.opacity.duration.300ms>

        {{-- ── MAIN PANEL ───────────────────────────────────────── --}}
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">

            {{-- Stats header --}}
            <div class="flex items-center gap-0 divide-x divide-slate-100 dark:divide-slate-800 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2 px-4 py-3 min-w-0">
                    <span class="text-xl font-bold tabular-nums text-slate-900 dark:text-white">{{ $statTotal }}</span>
                    <span class="text-xs text-slate-400">dokumen</span>
                </div>
                <div class="flex items-center gap-2 px-4 py-3 min-w-0">
                    <span class="h-2 w-2 rounded-full bg-cyan-500 flex-shrink-0"></span>
                    <span class="text-sm font-semibold tabular-nums text-slate-700 dark:text-slate-300">{{ $statValid }}</span>
                    <span class="text-xs text-slate-400">valid</span>
                </div>
                <div class="flex items-center gap-2 px-4 py-3 min-w-0">
                    <span class="h-2 w-2 rounded-full bg-amber-400 flex-shrink-0"></span>
                    <span class="text-sm font-semibold tabular-nums text-slate-700 dark:text-slate-300">{{ $statPending }}</span>
                    <span class="text-xs text-slate-400">review</span>
                </div>
                <div class="flex items-center gap-2 px-4 py-3 min-w-0">
                    <span class="h-2 w-2 rounded-full bg-rose-400 flex-shrink-0"></span>
                    <span class="text-sm font-semibold tabular-nums text-slate-700 dark:text-slate-300">{{ $statNeedUpload }}</span>
                    <span class="text-xs text-slate-400">perlu upload</span>
                </div>
                <div class="flex-1 px-4 py-3 flex items-center gap-2.5">
                    <div class="flex-1 h-1 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full rounded-full bg-cyan-500 transition-all duration-700" style="width: {{ $completePct }}%"></div>
                    </div>
                    <span class="text-[11px] font-semibold tabular-nums text-slate-400 flex-shrink-0">{{ $completePct }}%</span>
                </div>
            </div>

            {{-- Toolbar --}}
            <div class="flex flex-col sm:flex-row gap-2 px-4 py-3 border-b border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900">
                {{-- Search --}}
                <div class="relative flex-1">
                    <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" />
                    <input type="text" wire:model.live.debounce.300ms="searchQuery"
                        placeholder="Cari nama dokumen..."
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 pl-9 pr-8 py-2 text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 focus:outline-none transition-colors">
                    @if($searchQuery)
                    <button wire:click="$set('searchQuery', '')"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 p-0.5 rounded text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        <x-heroicon-o-x-mark class="h-3.5 w-3.5" />
                    </button>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    {{-- Status filter --}}
                    <select wire:model.live="statusFilter"
                        class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 pl-3 pr-8 text-sm text-slate-700 dark:text-slate-300 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 focus:outline-none transition-colors">
                        <option value="all">Semua Status</option>
                        <option value="valid">Valid</option>
                        <option value="pending">Dalam Review</option>
                        <option value="not_uploaded">Belum Upload</option>
                        <option value="expired">Kadaluarsa</option>
                        <option value="rejected">Ditolak</option>
                    </select>

                    @if($searchQuery || $statusFilter !== 'all')
                    <button wire:click="resetFilters"
                        class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                        title="Reset Filter">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                    </button>
                    @endif

                    {{-- Add button --}}
                    <button type="button"
                        wire:click="openUploadModal({{ $currentClient->id }}, null, true)"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-cyan-500 hover:bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm shadow-cyan-200 dark:shadow-none transition-colors focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2">
                        <x-heroicon-o-plus class="h-4 w-4" />
                        <span class="hidden sm:inline">Tambah</span>
                    </button>
                </div>
            </div>

            {{-- Active filter tags --}}
            @if($searchQuery || $statusFilter !== 'all')
            <div class="flex flex-wrap items-center gap-2 px-4 py-2 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/40">
                <span class="text-[11px] text-slate-400">Filter:</span>
                @if($searchQuery)
                <span class="inline-flex items-center gap-1 rounded-full border border-cyan-200 dark:border-cyan-800 bg-cyan-50 dark:bg-cyan-900/20 px-2 py-0.5 text-[11px] font-medium text-cyan-700 dark:text-cyan-400">
                    "{{ $searchQuery }}"
                    <button wire:click="$set('searchQuery', '')" class="hover:text-cyan-900 dark:hover:text-cyan-200">
                        <x-heroicon-o-x-mark class="h-3 w-3" />
                    </button>
                </span>
                @endif
                @if($statusFilter !== 'all')
                <span class="inline-flex items-center gap-1 rounded-full border border-cyan-200 dark:border-cyan-800 bg-cyan-50 dark:bg-cyan-900/20 px-2 py-0.5 text-[11px] font-medium text-cyan-700 dark:text-cyan-400">
                    {{ ucfirst(str_replace('_', ' ', $statusFilter)) }}
                    <button wire:click="$set('statusFilter', 'all')" class="hover:text-cyan-900 dark:hover:text-cyan-200">
                        <x-heroicon-o-x-mark class="h-3 w-3" />
                    </button>
                </span>
                @endif
            </div>
            @endif

            {{-- ═══ SECTION 1: LEGAL WAJIB ═══ --}}
            <div>
                {{-- Section header --}}
                <div class="sticky top-0 z-10 flex items-center gap-2 px-4 py-2.5 bg-cyan-50/70 dark:bg-cyan-900/10 backdrop-blur border-b border-cyan-100 dark:border-cyan-900/30">
                    <x-heroicon-o-shield-check class="h-3.5 w-3.5 text-cyan-500 flex-shrink-0" />
                    <span class="text-[11px] font-bold uppercase tracking-widest text-cyan-700 dark:text-cyan-400">Dokumen Legal Wajib</span>
                    <div class="flex-1 h-px bg-cyan-200/60 dark:bg-cyan-800/40"></div>
                    <span class="inline-flex items-center rounded-full bg-cyan-100 dark:bg-cyan-900/30 px-2 py-0.5 text-[10px] font-bold text-cyan-600 dark:text-cyan-400 tabular-nums">{{ $this->filteredChecklist->count() }}</span>
                </div>

                @forelse($this->filteredChecklist as $doc)
                @php
                    $isUploaded   = $doc['is_uploaded'];
                    $uploadedDoc  = $doc['uploaded_document'] ?? null;
                    $statusBadge  = $isUploaded ? $uploadedDoc->status_badge : null;
                    $isExpired    = $uploadedDoc?->expired_at && \Carbon\Carbon::parse($uploadedDoc->expired_at)->isPast();
                @endphp
                <div class="group flex items-center gap-3 px-4 py-3.5 border-b border-slate-50 dark:border-slate-800/40 hover:bg-slate-50/70 dark:hover:bg-slate-800/20 transition-colors">
                    {{-- Left accent + icon --}}
                    <div class="flex items-center gap-2.5 flex-shrink-0">
                        <div class="w-0.5 h-9 rounded-full {{ $isUploaded ? 'bg-cyan-400' : 'bg-slate-200 dark:bg-slate-700' }} transition-colors duration-300"></div>
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center border transition-colors duration-300
                            {{ $isUploaded
                                ? 'bg-cyan-50 dark:bg-cyan-900/20 border-cyan-100 dark:border-cyan-900/40'
                                : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700' }}">
                            @if($isUploaded)
                                <x-heroicon-o-document-check class="h-4 w-4 text-cyan-500" />
                            @else
                                <x-heroicon-o-document class="h-4 w-4 text-slate-400" />
                            @endif
                        </div>
                    </div>

                    {{-- Name + meta --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $doc['name'] }}</span>
                            @if($doc['is_required'])
                                <span class="inline-flex rounded px-1.5 py-0.5 text-[10px] font-bold bg-cyan-500 text-white leading-none tracking-wide">Wajib</span>
                            @endif
                        </div>
                        <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] text-slate-400 dark:text-slate-500">
                            @if($uploadedDoc?->document_number)
                                <span class="tabular-nums">No. {{ $uploadedDoc->document_number }}</span>
                            @endif
                            @if($uploadedDoc?->expired_at)
                                <span class="{{ $isExpired ? 'text-rose-400' : '' }}">
                                    · s.d. {{ \Carbon\Carbon::parse($uploadedDoc->expired_at)->format('d M Y') }}
                                    @if($isExpired) · <span class="font-semibold">Kadaluarsa</span> @endif
                                </span>
                            @endif
                            @if($uploadedDoc?->admin_notes)
                                <span class="text-amber-400 dark:text-amber-500">· {{ Str::limit($uploadedDoc->admin_notes, 55) }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Status badge (tablet+) --}}
                    <div class="flex-shrink-0 hidden sm:block">
                        @if($isUploaded)
                            <span class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-[11px] font-semibold {{ $statusBadge['class'] }}">
                                <x-dynamic-component :component="$statusBadge['icon']" class="h-3 w-3" />
                                {{ $statusBadge['text'] }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-md border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-2 py-1 text-[11px] font-medium text-slate-400 dark:text-slate-500">
                                <x-heroicon-o-minus-circle class="h-3 w-3" />
                                Belum Upload
                            </span>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex-shrink-0 flex items-center gap-1">
                        @if($isUploaded)
                            <button wire:click="previewDocuments({{ $uploadedDoc->id }})"
                                class="p-1.5 rounded-md text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                                title="Lihat">
                                <x-heroicon-o-eye class="h-4 w-4" />
                            </button>
                            <button wire:click="downloadDocument({{ $uploadedDoc->id }})"
                                class="p-1.5 rounded-md text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                                title="Unduh">
                                <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                            </button>
                            <button wire:click="openUploadModal({{ $currentClient->id }}, {{ $doc['sop_id'] }}, false)"
                                class="p-1.5 rounded-md text-slate-400 hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors"
                                title="Upload Ulang">
                                <x-heroicon-o-arrow-path class="h-4 w-4" />
                            </button>
                        @else
                            <button wire:click="openUploadModal({{ $currentClient->id }}, {{ $doc['sop_id'] }}, false)"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-cyan-500 hover:bg-cyan-600 px-2.5 py-1.5 text-[11px] font-semibold text-white shadow-sm shadow-cyan-200 dark:shadow-none transition-colors focus:outline-none">
                                <x-heroicon-o-arrow-up-tray class="h-3.5 w-3.5" />
                                Upload
                            </button>
                        @endif
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center py-10 text-center">
                    <x-heroicon-o-document class="h-7 w-7 text-slate-200 dark:text-slate-700 mb-2" />
                    <p class="text-xs text-slate-400">Tidak ada dokumen legal wajib</p>
                </div>
                @endforelse
            </div>

            {{-- ═══ SECTION 2: TAMBAHAN DIBUTUHKAN ═══ --}}
            @if($requiredAdditionalDocuments->isNotEmpty())
            <div>
                <div class="sticky top-0 z-10 flex items-center gap-2 px-4 py-2.5 bg-amber-50/70 dark:bg-amber-900/10 backdrop-blur border-b border-amber-100 dark:border-amber-900/30 border-t border-t-slate-100 dark:border-t-slate-800">
                    <x-heroicon-o-exclamation-circle class="h-3.5 w-3.5 text-amber-500 flex-shrink-0" />
                    <span class="text-[11px] font-bold uppercase tracking-widest text-amber-700 dark:text-amber-400">Dokumen Tambahan Dibutuhkan</span>
                    <div class="flex-1 h-px bg-amber-200/60 dark:bg-amber-800/40"></div>
                    <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-900/30 px-2 py-0.5 text-[10px] font-bold text-amber-600 dark:text-amber-400 tabular-nums">{{ $this->filteredRequirements->count() }}</span>
                </div>

                @foreach($this->filteredRequirements as $requirement)
                @php
                    $latestDoc      = $requirement->getLatestDocument();
                    $reqStatusBadge = $latestDoc ? $latestDoc->status_badge : $requirement->status_badge;
                    $catColors      = match($requirement->category ?? 'other') {
                        'legal'       => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                        'financial'   => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400',
                        'operational' => 'bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400',
                        'compliance'  => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-900/20 dark:text-cyan-400',
                        default       => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                    };
                @endphp
                <div class="group flex items-center gap-3 px-4 py-3.5 border-b border-slate-50 dark:border-slate-800/40 hover:bg-slate-50/70 dark:hover:bg-slate-800/20 transition-colors">
                    <div class="flex items-center gap-2.5 flex-shrink-0">
                        <div class="w-0.5 h-9 rounded-full {{ $latestDoc ? 'bg-amber-400' : 'bg-slate-200 dark:bg-slate-700' }} transition-colors duration-300"></div>
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center border transition-colors duration-300
                            {{ $latestDoc
                                ? 'bg-amber-50 dark:bg-amber-900/20 border-amber-100 dark:border-amber-900/40'
                                : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700' }}">
                            <x-heroicon-o-document-text class="h-4 w-4 {{ $latestDoc ? 'text-amber-500' : 'text-slate-400' }}" />
                        </div>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $requirement->name }}</span>
                            @if($requirement->is_required)
                                <span class="inline-flex rounded px-1.5 py-0.5 text-[10px] font-bold bg-amber-500 text-white leading-none">Wajib</span>
                            @endif
                            @if($requirement->isOverdue())
                                <span class="inline-flex rounded px-1.5 py-0.5 text-[10px] font-bold bg-rose-500 text-white leading-none">Terlambat</span>
                            @endif
                            <span class="inline-flex rounded px-1.5 py-0.5 text-[10px] font-medium leading-none {{ $catColors }}">{{ strtoupper($requirement->category ?? 'OTHER') }}</span>
                        </div>
                        <div class="mt-0.5 flex flex-wrap items-center gap-x-2 text-[11px] text-slate-400 dark:text-slate-500">
                            @if($requirement->due_date)
                                <span class="{{ $requirement->isOverdue() ? 'text-rose-400' : '' }}">Tenggat {{ $requirement->due_date->format('d M Y') }}</span>
                            @endif
                            @if($requirement->description)
                                <span class="text-slate-300 dark:text-slate-600">·</span>
                                <span>{{ Str::limit($requirement->description, 60) }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex-shrink-0 hidden sm:block">
                        <span class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-[11px] font-semibold {{ $reqStatusBadge['class'] }}">
                            <x-dynamic-component :component="$reqStatusBadge['icon']" class="h-3 w-3" />
                            {{ $reqStatusBadge['text'] }}
                        </span>
                    </div>

                    <div class="flex-shrink-0 flex items-center gap-1">
                        @if($latestDoc)
                            <button wire:click="previewDocuments({{ $latestDoc->id }})"
                                class="p-1.5 rounded-md text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                                title="Lihat">
                                <x-heroicon-o-eye class="h-4 w-4" />
                            </button>
                            @if($latestDoc->status !== 'valid')
                            <button wire:click="openUploadModal({{ $currentClient->id }}, null, false, {{ $requirement->id }})"
                                class="p-1.5 rounded-md text-slate-400 hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors"
                                title="Upload Ulang">
                                <x-heroicon-o-arrow-path class="h-4 w-4" />
                            </button>
                            @endif
                        @else
                            <button wire:click="openUploadModal({{ $currentClient->id }}, null, false, {{ $requirement->id }})"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 px-2.5 py-1.5 text-[11px] font-semibold text-white shadow-sm transition-colors focus:outline-none">
                                <x-heroicon-o-arrow-up-tray class="h-3.5 w-3.5" />
                                Upload
                            </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- ═══ SECTION 3: DOKUMEN TAMBAHAN LAINNYA ═══ --}}
            <div>
                <div class="sticky top-0 z-10 flex items-center gap-2 px-4 py-2.5 bg-slate-50/80 dark:bg-slate-800/30 backdrop-blur border-b border-slate-100 dark:border-slate-800 border-t border-t-slate-100 dark:border-t-slate-800">
                    <x-heroicon-o-paper-clip class="h-3.5 w-3.5 text-slate-400 flex-shrink-0" />
                    <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Dokumen Lainnya</span>
                    <div class="flex-1 h-px bg-slate-200 dark:bg-slate-700"></div>
                    <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5 text-[10px] font-bold text-slate-500 dark:text-slate-400 tabular-nums">{{ $this->filteredAdditionalDocs->count() }}</span>
                    <button type="button"
                        wire:click="openUploadModal({{ $currentClient->id }}, null, true)"
                        class="inline-flex items-center gap-1 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 px-2 py-1 text-[11px] font-semibold text-slate-600 dark:text-slate-300 transition-colors">
                        <x-heroicon-o-plus class="h-3 w-3" />
                        Tambah
                    </button>
                </div>

                @forelse($this->filteredAdditionalDocs as $doc)
                @php $docStatusBadge = $doc->status_badge; @endphp
                <div class="group flex items-center gap-3 px-4 py-3.5 border-b border-slate-50 dark:border-slate-800/40 hover:bg-slate-50/70 dark:hover:bg-slate-800/20 transition-colors">
                    <div class="flex items-center gap-2.5 flex-shrink-0">
                        <div class="w-0.5 h-9 rounded-full bg-slate-200 dark:bg-slate-700"></div>
                        <div class="w-9 h-9 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center">
                            <x-heroicon-o-document class="h-4 w-4 text-slate-400" />
                        </div>
                    </div>

                    <div class="min-w-0 flex-1">
                        <span class="block text-sm font-medium text-slate-800 dark:text-slate-200 truncate">{{ $doc->description ?? $doc->original_filename }}</span>
                        <div class="mt-0.5 flex flex-wrap items-center gap-x-2 text-[11px] text-slate-400 dark:text-slate-500">
                            @if($doc->description && $doc->original_filename !== $doc->description)
                                <span class="truncate max-w-[180px]">{{ $doc->original_filename }}</span>
                                <span class="text-slate-300 dark:text-slate-600">·</span>
                            @endif
                            <span>{{ $doc->created_at->format('d M Y') }}</span>
                            @if($doc->admin_notes)
                                <span class="text-amber-400 dark:text-amber-500">· {{ Str::limit($doc->admin_notes, 40) }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex-shrink-0 hidden sm:block">
                        <span class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-[11px] font-semibold {{ $docStatusBadge['class'] }}">
                            <x-dynamic-component :component="$docStatusBadge['icon']" class="h-3 w-3" />
                            {{ $docStatusBadge['text'] }}
                        </span>
                    </div>

                    <div class="flex-shrink-0 flex items-center gap-1">
                        <button wire:click="previewDocuments({{ $doc->id }})"
                            class="p-1.5 rounded-md text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                            title="Lihat">
                            <x-heroicon-o-eye class="h-4 w-4" />
                        </button>
                        <button wire:click="downloadDocument({{ $doc->id }})"
                            class="p-1.5 rounded-md text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                            title="Unduh">
                            <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                        </button>
                        @if($doc->status !== 'valid')
                        <button wire:click="deleteDocumentConfirm({{ $doc->id }})"
                            class="p-1.5 rounded-md text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors"
                            title="Hapus">
                            <x-heroicon-o-trash class="h-4 w-4" />
                        </button>
                        @endif
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center py-12 text-center px-4">
                    <div class="w-12 h-12 rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-700 flex items-center justify-center mb-3">
                        <x-heroicon-o-arrow-up-tray class="h-5 w-5 text-slate-300 dark:text-slate-600" />
                    </div>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Belum ada dokumen tambahan</p>
                    <p class="mt-1 text-xs text-slate-400">Upload dokumen pendukung yang mungkin diperlukan</p>
                    <button type="button"
                        wire:click="openUploadModal({{ $currentClient->id }}, null, true)"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg border border-dashed border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2 text-sm font-medium text-slate-500 dark:text-slate-400 hover:border-cyan-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-cyan-50 dark:hover:bg-cyan-900/10 transition-colors">
                        <x-heroicon-o-plus class="h-4 w-4" />
                        Upload Dokumen Pertama
                    </button>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    @endif
    @endif

    {{-- ── UPLOAD MODAL ──────────────────────────────────── --}}
    <x-filament::modal id="upload-document-modal" width="5xl">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="rounded-xl bg-cyan-50 dark:bg-cyan-900/30 p-2.5">
                    <x-heroicon-o-cloud-arrow-up class="h-6 w-6 text-cyan-600 dark:text-cyan-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        @if($selectedRequirementId) Upload untuk Persyaratan @else Upload Dokumen @endif
                    </h3>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Pilih file yang akan diupload</p>
                </div>
            </div>
        </x-slot>

        <form wire:submit="uploadDocument">
            {{ $this->form }}
            <div class="mt-6 flex justify-end gap-3">
                <x-filament::button color="gray" wire:click="closeUploadModal" type="button">Batal</x-filament::button>
                <x-filament::button type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="uploadDocument">Upload</span>
                    <span wire:loading wire:target="uploadDocument">Mengupload...</span>
                </x-filament::button>
            </div>
        </form>
    </x-filament::modal>

    {{-- ── PREVIEW MODAL ─────────────────────────────────── --}}
    <x-filament::modal id="preview-document-modal" width="7xl">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="rounded-xl bg-slate-100 dark:bg-slate-700 p-2.5">
                    <x-heroicon-o-document-text class="h-6 w-6 text-slate-600 dark:text-slate-300" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Preview Dokumen</h3>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Pratinjau file dokumen</p>
                </div>
            </div>
        </x-slot>

        @if($previewDocument)
        <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-5">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama File</dt>
                        <dd class="mt-1.5 text-sm font-medium text-gray-900 dark:text-white">{{ $previewDocument->original_filename }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Upload Oleh</dt>
                        <dd class="mt-1.5 text-sm font-medium text-gray-900 dark:text-white">{{ $previewDocument->user->name ?? '-' }}</dd>
                    </div>
                    @if($previewDocument->document_number)
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Nomor Dokumen</dt>
                        <dd class="mt-1.5 text-sm font-medium text-gray-900 dark:text-white">{{ $previewDocument->document_number }}</dd>
                    </div>
                    @endif
                    @if($previewDocument->expired_at)
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Kadaluarsa</dt>
                        <dd class="mt-1.5 text-sm font-medium text-gray-900 dark:text-white">{{ $previewDocument->expired_at->format('d M Y') }}</dd>
                    </div>
                    @endif
                    @if($previewDocument->admin_notes)
                    <div class="col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Catatan Admin</dt>
                        <dd class="mt-1.5 rounded-lg bg-amber-50 dark:bg-amber-900/20 p-3 text-sm text-amber-800 dark:text-amber-200">{{ $previewDocument->admin_notes }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                @php
                    $extension = strtolower(pathinfo($previewDocument->file_path, PATHINFO_EXTENSION));
                    $imageable = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                    $isPdf     = $extension === 'pdf';
                @endphp
                @if($imageable)
                    <img src="{{ asset('storage/' . $previewDocument->file_path) }}" alt="{{ $previewDocument->original_filename }}" class="mx-auto max-h-[1000px] w-auto">
                @elseif($isPdf)
                    <iframe src="{{ asset('storage/' . $previewDocument->file_path) }}" class="h-[600px] sm:h-[1000px] w-full" frameborder="0"></iframe>
                @else
                    <div class="flex flex-col items-center justify-center p-16">
                        <div class="rounded-full bg-gray-100 dark:bg-gray-700 p-4">
                            <x-heroicon-o-document class="h-12 w-12 text-gray-400" />
                        </div>
                        <p class="mt-4 text-sm font-medium text-gray-600 dark:text-gray-400">Preview tidak tersedia untuk tipe file ini</p>
                        <x-filament::button wire:click="downloadDocument({{ $previewDocument->id }})" class="mt-4">Download File</x-filament::button>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <x-slot name="footerActions">
            <x-filament::button color="gray" wire:click="closePreviewModal">Tutup</x-filament::button>
            @if($previewDocument)
            <x-filament::button wire:click="downloadDocument({{ $previewDocument->id }})" icon="heroicon-o-arrow-down-tray">Download</x-filament::button>
            @endif
        </x-slot>
    </x-filament::modal>

    {{-- ── DELETE CONFIRM MODAL ──────────────────────────── --}}
    <x-filament::modal id="confirm-delete-modal" width="md">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="rounded-xl bg-red-100 dark:bg-red-900/30 p-2.5">
                    <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Konfirmasi Hapus</h3>
            </div>
        </x-slot>

        <p class="text-sm text-gray-600 dark:text-gray-400">Apakah Anda yakin ingin menghapus dokumen ini? Tindakan ini tidak dapat dibatalkan.</p>

        <div class="mt-6 flex justify-end gap-3">
            <x-filament::button color="gray" wire:click="closeDeleteModal">Batal</x-filament::button>
            <x-filament::button color="danger" wire:click="deleteDocument">Hapus</x-filament::button>
        </div>
    </x-filament::modal>
</div>
