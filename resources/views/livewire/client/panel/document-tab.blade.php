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

    @php
        $ungroupedReqs = $this->filteredRequirements->filter(fn($r) => is_null($r->group_id));
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

            {{-- ═══ SECTION 2: GRUP PERSYARATAN ═══ --}}
            @if(count($groups) > 0)
            <div>
                <div class="sticky top-0 z-10 flex items-center gap-2 px-4 py-2.5 bg-violet-50/70 dark:bg-violet-900/10 backdrop-blur border-b border-violet-100 dark:border-violet-900/30 border-t border-t-slate-100 dark:border-t-slate-800">
                    <x-heroicon-o-folder-open class="h-3.5 w-3.5 text-violet-500 flex-shrink-0" />
                    <span class="text-[11px] font-bold uppercase tracking-widest text-violet-700 dark:text-violet-400">Grup Persyaratan</span>
                    <div class="flex-1 h-px bg-violet-200/60 dark:bg-violet-800/40"></div>
                    <span class="inline-flex items-center rounded-full bg-violet-100 dark:bg-violet-900/30 px-2 py-0.5 text-[10px] font-bold text-violet-600 dark:text-violet-400 tabular-nums">{{ count($groups) }}</span>
                </div>

                @foreach(collect($groups) as $group)
                @php
                    $groupReqs  = $this->filteredRequirements->where('group_id', $group['id']);
                    $groupTotal = $groupReqs->count();
                    $groupDone  = $groupReqs->filter(function ($requirement) {
                        $latestDocument = $requirement->getLatestDocument();

                        return $latestDocument
                            ? $latestDocument->status === 'valid'
                            : $requirement->status === 'fulfilled';
                    })->count();
                    $groupPct   = $groupTotal > 0 ? round($groupDone / $groupTotal * 100) : 0;
                    $isOverdueGroup = $group['due_date'] && \Carbon\Carbon::parse($group['due_date'])->isPast();
                @endphp
                <div
                    x-data="{ open: true }"
                    x-bind:class="{ 'bg-violet-50/25 dark:bg-violet-900/10': open }"
                    class="border-b border-slate-50 transition-colors duration-200 dark:border-slate-800/40"
                >
                    {{-- Group header --}}
                    <button type="button" @click="open = !open"
                        x-bind:aria-expanded="open.toString()"
                        x-bind:class="{ 'bg-violet-50/80 dark:bg-violet-900/20': open }"
                        class="relative w-full flex items-center gap-3 px-4 py-3 hover:bg-violet-50/50 dark:hover:bg-violet-900/5 transition-colors text-left">
                        <span x-show="open" x-transition.opacity class="absolute inset-y-2 left-0 w-1 rounded-r-full bg-violet-500"></span>
                        <div
                            x-bind:class="{ 'border-violet-300 bg-violet-100 shadow-sm shadow-violet-100 dark:border-violet-700 dark:bg-violet-900/40 dark:shadow-none': open }"
                            class="w-7 h-7 rounded-lg bg-violet-100 dark:bg-violet-900/30 border border-violet-200 dark:border-violet-800/50 flex items-center justify-center flex-shrink-0 transition-colors"
                        >
                            <x-heroicon-o-folder-open x-show="open" class="h-3.5 w-3.5 text-violet-500" />
                            <x-heroicon-o-folder x-show="!open" class="h-3.5 w-3.5 text-violet-500" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span x-bind:class="{ 'text-violet-900 dark:text-violet-100': open }" class="text-sm font-semibold text-slate-800 dark:text-slate-200 transition-colors">{{ $group['name'] }}</span>
                                @if($group['year'])
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400">{{ $group['year'] }}</span>
                                @endif
                                @if($isOverdueGroup)
                                    <span class="inline-flex rounded px-1.5 py-0.5 text-[10px] font-bold bg-rose-500 text-white">Terlambat</span>
                                @elseif($group['due_date'])
                                    <span class="text-[11px] text-slate-400">Tenggat {{ \Carbon\Carbon::parse($group['due_date'])->format('d M Y') }}</span>
                                @endif
                            </div>
                            <div class="mt-1.5 flex items-center gap-2">
                                <div class="h-1 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden w-24">
                                    <div class="h-full rounded-full bg-violet-400 transition-all duration-500" style="width: {{ $groupPct }}%"></div>
                                </div>
                                <span class="text-[10px] text-slate-400 tabular-nums">{{ $groupDone }}/{{ $groupTotal }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="text-[11px] font-bold tabular-nums text-violet-600 dark:text-violet-400">{{ $groupPct }}%</span>
                            <x-heroicon-o-chevron-down class="h-4 w-4 text-slate-400 transition-transform duration-200" ::class="{ 'rotate-180': !open }" />
                        </div>
                    </button>

                    {{-- Group requirements --}}
                    <div x-show="open" x-transition.opacity.duration.150ms class="border-t border-violet-100/50 dark:border-violet-900/20">
                        @forelse($groupReqs as $requirement)
                        @php
                            $latestDoc      = $requirement->getLatestDocument();
                            $reqStatusBadge = $latestDoc ? $latestDoc->status_badge : $requirement->status_badge;
                        @endphp
                        <div class="group flex items-center gap-3 pl-8 pr-4 py-3 border-b border-slate-50 dark:border-slate-800/40 hover:bg-slate-50/70 dark:hover:bg-slate-800/20 transition-colors">
                            <div class="flex items-center gap-2.5 flex-shrink-0">
                                <div class="w-0.5 h-8 rounded-full {{ $latestDoc ? 'bg-violet-400' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center border
                                    {{ $latestDoc ? 'bg-violet-50 dark:bg-violet-900/20 border-violet-100 dark:border-violet-900/40' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700' }}">
                                    <x-heroicon-o-document-text class="h-3.5 w-3.5 {{ $latestDoc ? 'text-violet-500' : 'text-slate-400' }}" />
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $requirement->name }}</span>
                                    @if($requirement->is_required)
                                        <span class="inline-flex rounded px-1.5 py-0.5 text-[10px] font-bold bg-violet-500 text-white leading-none">Wajib</span>
                                    @endif
                                    @if($requirement->isOverdue())
                                        <span class="inline-flex rounded px-1.5 py-0.5 text-[10px] font-bold bg-rose-500 text-white leading-none">Terlambat</span>
                                    @endif
                                </div>
                                <div class="mt-0.5 flex flex-wrap items-center gap-x-2 text-[11px] text-slate-400 dark:text-slate-500">
                                    @if($requirement->due_date)
                                        <span class="{{ $requirement->isOverdue() ? 'text-rose-400' : '' }}">Tenggat {{ $requirement->due_date->format('d M Y') }}</span>
                                    @endif
                                    @if($requirement->description)
                                        <span class="text-slate-300 dark:text-slate-600">·</span>
                                        <span>{{ Str::limit($requirement->description, 50) }}</span>
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
                                        class="p-1.5 rounded-md text-slate-400 hover:text-violet-500 hover:bg-violet-50 dark:hover:bg-violet-900/20 transition-colors"
                                        title="Upload Ulang">
                                        <x-heroicon-o-arrow-path class="h-4 w-4" />
                                    </button>
                                    @endif
                                @else
                                    <button wire:click="openUploadModal({{ $currentClient->id }}, null, false, {{ $requirement->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-violet-500 hover:bg-violet-600 px-2.5 py-1.5 text-[11px] font-semibold text-white shadow-sm transition-colors focus:outline-none">
                                        <x-heroicon-o-arrow-up-tray class="h-3.5 w-3.5" />
                                        Upload
                                    </button>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="flex items-center gap-2 pl-8 pr-4 py-4 text-[12px] text-slate-400">
                            <x-heroicon-o-inbox class="h-4 w-4" />
                            Tidak ada persyaratan dalam grup ini
                        </div>
                        @endforelse
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- ═══ SECTION 3: TAMBAHAN DIBUTUHKAN (TANPA GRUP) ═══ --}}
            @if($ungroupedReqs->isNotEmpty())
            <div>
                <div class="sticky top-0 z-10 flex items-center gap-2 px-4 py-2.5 bg-amber-50/70 dark:bg-amber-900/10 backdrop-blur border-b border-amber-100 dark:border-amber-900/30 border-t border-t-slate-100 dark:border-t-slate-800">
                    <x-heroicon-o-exclamation-circle class="h-3.5 w-3.5 text-amber-500 flex-shrink-0" />
                    <span class="text-[11px] font-bold uppercase tracking-widest text-amber-700 dark:text-amber-400">Persyaratan Lainnya</span>
                    <div class="flex-1 h-px bg-amber-200/60 dark:bg-amber-800/40"></div>
                    <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-900/30 px-2 py-0.5 text-[10px] font-bold text-amber-600 dark:text-amber-400 tabular-nums">{{ $ungroupedReqs->count() }}</span>
                </div>

                @foreach($ungroupedReqs as $requirement)
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
        @if($previewDocument)
        @php
            $extension = strtolower(pathinfo($previewDocument->file_path ?? '', PATHINFO_EXTENSION));
            $imageable = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            $isPdf     = $extension === 'pdf';
            $statusBadge = $previewDocument->status_badge;
            $documentDisplayName = $previewDocument->requirement?->name
                ?? $previewDocument->sopLegalDocument?->name
                ?? $previewDocument->description
                ?? $previewDocument->original_filename;
            $documentDisplayLabel = $previewDocument->requirement
                ? 'Persyaratan'
                : ($previewDocument->sopLegalDocument ? 'Jenis Dokumen' : 'Dokumen');
        @endphp

        <div x-data="{ showSidebar: window.innerWidth >= 1024 }" class="space-y-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-start gap-3">
                    <div class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-50 ring-1 ring-cyan-100 dark:bg-cyan-900/40 dark:ring-cyan-800 sm:flex">
                        @if($isPdf)
                        <x-heroicon-o-document-text class="h-6 w-6 text-cyan-600 dark:text-cyan-400" />
                        @elseif($imageable)
                        <x-heroicon-o-photo class="h-6 w-6 text-cyan-600 dark:text-cyan-400" />
                        @else
                        <x-heroicon-o-document class="h-6 w-6 text-cyan-600 dark:text-cyan-400" />
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
                    @if(auth()->user()?->hasAnyRole(['project-manager', 'direktur', 'super-admin', 'verificator', 'staff']))
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
                    @else
                    <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold shadow-sm {{ $statusBadge['class'] }}">
                        <x-dynamic-component :component="$statusBadge['icon']" class="h-3.5 w-3.5" />
                        {{ $statusBadge['text'] }}
                    </span>
                    @endif
                    <button
                        @click="showSidebar = !showSidebar"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-600 shadow-sm transition hover:border-cyan-500 hover:bg-cyan-50 hover:text-cyan-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-cyan-500 dark:hover:bg-cyan-900/20 dark:hover:text-cyan-400"
                        :class="{ 'border-cyan-500 bg-cyan-50 text-cyan-600 dark:border-cyan-500 dark:bg-cyan-900/20 dark:text-cyan-400': showSidebar }"
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
                        @if($imageable)
                        <img
                            src="{{ asset('storage/' . $previewDocument->file_path) }}"
                            alt="{{ $documentDisplayName }}"
                            class="max-h-full max-w-full rounded-lg object-contain shadow-sm"
                        />
                        @elseif($isPdf)
                        <iframe
                            src="{{ asset('storage/' . $previewDocument->file_path) }}"
                            class="h-full w-full rounded-lg bg-white"
                            title="{{ $documentDisplayName }}"
                            frameborder="0"
                        ></iframe>
                        @else
                        <div class="px-6 py-12 text-center">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                <x-heroicon-o-document class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                            </div>
                            <p class="mt-4 text-sm font-medium text-gray-700 dark:text-gray-200">Preview tidak tersedia untuk tipe file ini</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Silakan download file untuk melihat dokumen lengkap.</p>
                            <x-filament::button wire:click="downloadDocument({{ $previewDocument->id }})" class="mt-4" icon="heroicon-o-arrow-down-tray">
                                Download File
                            </x-filament::button>
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
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Upload Oleh</dt>
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
                        </dl>

                        @if($previewDocument->admin_notes)
                        <div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-700">
                            <dt class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Catatan Admin</dt>
                            <dd class="rounded-lg bg-amber-50 p-3 text-sm text-amber-800 dark:bg-amber-900/20 dark:text-amber-200">{{ $previewDocument->admin_notes }}</dd>
                        </div>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
        @endif

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
