{{-- resources/views/livewire/client/panel/overview-tab.blade.php --}}
{{--
Aesthetic: Vercel Web Interface Guidelines Strict Compliance
Guidelines: Minimalist, standard sizing, tabular-nums, professional monochrome palette, native-feeling interactive
states, zero oversized components, strict focus visibility.
--}}
<div class="w-full space-y-8 pb-12 antialiased text-slate-900 dark:text-slate-100" x-data="{
        mounted: false 
    }" x-init="setTimeout(() => mounted = true, 50)">

    @if($clients->isEmpty())
        {{-- Empty State: Unlinked Account --}}
        <div class="rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm"
            x-show="mounted" x-transition.opacity.duration.300ms>
            <div class="flex items-start gap-4">
                <div class="mt-1 flex-shrink-0">
                    <x-heroicon-o-exclamation-circle class="h-5 w-5 text-amber-500" />
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold">Account Not Linked</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Your account is not currently associated with any company profiles. An administrator must grant you
                        access before you can view the dashboard.
                    </p>
                    <div class="mt-4">
                        <a href="mailto:admin@example.com"
                            class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 dark:focus-visible:ring-white transition-colors">
                            Contact Administrator
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else

        {{-- HEADER SECTION --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4" x-show="mounted"
            x-transition.opacity.duration.300ms>

            <div class="flex items-center gap-4">
                @if($selectedClient && $selectedClient->logo)
                    <div
                        class="flex-shrink-0 h-10 w-10 rounded-md overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 flex items-center justify-center">
                        <img src="{{ Storage::url($selectedClient->logo) }}" alt="{{ $selectedClient->name }}"
                            class="max-h-full max-w-full object-contain">
                    </div>
                @else
                    <div
                        class="flex-shrink-0 h-10 w-10 rounded-md border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 flex items-center justify-center text-slate-400">
                        <x-heroicon-o-building-office-2 class="h-5 w-5" />
                    </div>
                @endif

                <div>
                    <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                        {{ $selectedClient ? $selectedClient->name : 'Dasbor' }}
                        @if($selectedClient)
                            <span class="inline-flex items-center rounded-full bg-cyan-50 dark:bg-cyan-900/20 px-2.5 py-0.5 text-xs font-semibold text-cyan-700 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-800">
                                Aktif
                            </span>
                        @endif
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Ringkasan Manajemen & Operasional</p>
                </div>
            </div>

            <button wire:click="refresh"
                class="inline-flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-1.5 text-sm font-medium text-slate-600 dark:text-slate-300 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 focus-visible:ring-offset-2 transition-colors disabled:opacity-50">
                <x-heroicon-o-arrow-path class="mr-1.5 h-4 w-4 text-slate-400" />
                Segarkan
            </button>
        </div>


        {{-- DOCUMENT ALERTS - Inline, Clean Banners --}}
        <div class="space-y-3" x-show="mounted" x-transition.opacity.duration.300ms>
            {{-- Rejected Alert --}}
            @if($rejectedDocuments->count() > 0)
                <div
                    class="rounded-md border-l-4 border-red-500 bg-red-50/50 dark:bg-red-900/10 p-4 ring-1 ring-inset ring-red-500/20">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <x-heroicon-s-x-circle class="h-5 w-5 text-red-500" />
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Documents Rejected</h3>
                            <div class="mt-2 text-sm text-red-700 dark:text-red-400/90 space-y-3">
                                @foreach($rejectedDocuments->take(3) as $doc)
                                    <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-4">
                                        <span class="font-medium min-w-[120px]">{{ $doc['name'] }}:</span>
                                        <span
                                            class="text-slate-900 dark:text-slate-100 bg-white/50 dark:bg-black/20 px-2 py-0.5 rounded text-xs border border-red-200 dark:border-red-800 shadow-sm">{{ $doc['admin_notes'] ?? 'Please review and re-upload' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>


        {{-- STATS GRID --}}
        @php
            $docUploaded = $allDocumentsChecklist->where('is_uploaded', true)->count();
            $docTotal    = $allDocumentsChecklist->count();
            $docPercentage = $docTotal > 0 ? round(($docUploaded / $docTotal) * 100) : 0;
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4" x-show="mounted" x-transition.opacity.duration.300ms>

            {{-- Proyek Aktif --}}
            <div class="group rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm cursor-pointer transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 hover:border-slate-300 dark:hover:border-slate-700">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">Proyek Aktif</span>
                    <div class="w-9 h-9 rounded-full bg-white dark:bg-slate-900 shadow-md flex items-center justify-center ring-1 ring-slate-100 dark:ring-slate-800 transition-transform duration-200 group-hover:scale-110">
                        <x-heroicon-o-folder class="h-4 w-4 text-cyan-500" />
                    </div>
                </div>
                <div class="flex items-end gap-1.5">
                    <span class="text-3xl font-bold text-slate-900 dark:text-white tabular-nums leading-none">{{ $projectStats['active'] }}</span>
                    <span class="text-lg font-light text-slate-300 dark:text-slate-600 pb-0.5">/</span>
                    <span class="text-lg font-semibold text-slate-400 dark:text-slate-500 tabular-nums pb-0.5">{{ $projectStats['total'] }}</span>
                </div>
                <p class="mt-3 text-xs text-slate-400 dark:text-slate-500">proyek sedang berjalan</p>
            </div>

            {{-- Proyek Selesai --}}
            <div class="group rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm cursor-pointer transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 hover:border-slate-300 dark:hover:border-slate-700">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">Selesai</span>
                    <div class="w-9 h-9 rounded-full bg-white dark:bg-slate-900 shadow-md flex items-center justify-center ring-1 ring-slate-100 dark:ring-slate-800 transition-transform duration-200 group-hover:scale-110">
                        <x-heroicon-o-check-circle class="h-4 w-4 text-cyan-500" />
                    </div>
                </div>
                <div class="flex items-end gap-1.5">
                    <span class="text-3xl font-bold text-slate-900 dark:text-white tabular-nums leading-none">{{ $projectStats['completed'] }}</span>
                    <span class="text-lg font-light text-slate-300 dark:text-slate-600 pb-0.5">/</span>
                    <span class="text-lg font-semibold text-slate-400 dark:text-slate-500 tabular-nums pb-0.5">{{ $projectStats['total'] }}</span>
                </div>
                <p class="mt-3 text-xs text-slate-400 dark:text-slate-500">
                    proyek berhasil diselesaikan
                    @if($projectStats['pending'] > 0)
                        &bull; <span class="tabular-nums">{{ $projectStats['pending'] }} tertunda</span>
                    @endif
                </p>
            </div>

            {{-- Laporan Pajak --}}
            <div class="group rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm cursor-pointer transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 hover:border-slate-300 dark:hover:border-slate-700">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">Laporan Pajak</span>
                    <div class="w-9 h-9 rounded-full bg-white dark:bg-slate-900 shadow-md flex items-center justify-center ring-1 ring-slate-100 dark:ring-slate-800 transition-transform duration-200 group-hover:scale-110">
                        <x-heroicon-o-chart-bar class="h-4 w-4 text-cyan-500" />
                    </div>
                </div>
                <div class="flex items-end gap-1.5">
                    <span class="text-3xl font-bold text-slate-900 dark:text-white tabular-nums leading-none">{{ $taxReportStats['reported'] }}</span>
                    <span class="text-lg font-light text-slate-300 dark:text-slate-600 pb-0.5">/</span>
                    <span class="text-lg font-semibold text-slate-400 dark:text-slate-500 tabular-nums pb-0.5">{{ $taxReportStats['total'] }}</span>
                </div>
                <div class="mt-3 flex items-center gap-2">
                    <div class="h-1.5 flex-1 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full bg-cyan-500 transition-all duration-500 rounded-full"
                            style="width: {{ $taxReportStats['completion_percentage'] }}%"></div>
                    </div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 tabular-nums">{{ $taxReportStats['completion_percentage'] }}%</span>
                </div>
                <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">laporan sudah dilaporkan</p>
            </div>

            {{-- Dokumen --}}
            <div class="group rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm cursor-pointer transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 hover:border-slate-300 dark:hover:border-slate-700">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">Dokumen</span>
                    <div class="w-9 h-9 rounded-full bg-white dark:bg-slate-900 shadow-md flex items-center justify-center ring-1 ring-slate-100 dark:ring-slate-800 transition-transform duration-200 group-hover:scale-110">
                        <x-heroicon-o-document-duplicate class="h-4 w-4 text-cyan-500" />
                    </div>
                </div>
                <div class="flex items-end gap-1.5">
                    <span class="text-3xl font-bold text-slate-900 dark:text-white tabular-nums leading-none">{{ $docUploaded }}</span>
                    <span class="text-lg font-light text-slate-300 dark:text-slate-600 pb-0.5">/</span>
                    <span class="text-lg font-semibold text-slate-400 dark:text-slate-500 tabular-nums pb-0.5">{{ $docTotal }}</span>
                </div>
                <div class="mt-3 flex items-center gap-2">
                    <div class="h-1.5 flex-1 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full bg-cyan-500 transition-all duration-500 rounded-full"
                            style="width: {{ $docPercentage }}%"></div>
                    </div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 tabular-nums">{{ $docPercentage }}%</span>
                </div>
                <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">dokumen telah diunggah</p>
            </div>
        </div>


        {{-- MAIN CONTENT: 8/2 LAYOUT --}}
        <div class="grid grid-cols-1 lg:grid-cols-10 gap-6" x-show="mounted" x-transition.opacity.duration.300ms>

            {{-- COL SPAN 7: DOKUMEN WAJIB --}}
            @php
                $uploadedCount = $allDocumentsChecklist->where('is_uploaded', true)->count();
                $totalCount    = $allDocumentsChecklist->count();
                $headerPct     = $totalCount > 0 ? round(($uploadedCount / $totalCount) * 100) : 0;
                $uploaded      = $allDocumentsChecklist->where('is_uploaded', true);
            @endphp
            <div class="lg:col-span-7 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex flex-col h-[600px] overflow-hidden"
                x-data="{
                    activeSection: 'pending',
                    setTab(tab) {
                        this.activeSection = tab;
                        this.$nextTick(() => this.updateIndicator());
                    },
                    updateIndicator() {
                        const indicator = this.$refs.docIndicator;
                        const btn = this.$refs['docTab_' + this.activeSection];
                        if (indicator && btn) {
                            indicator.style.left  = btn.offsetLeft + 'px';
                            indicator.style.width = btn.offsetWidth + 'px';
                        }
                    }
                }"
                x-init="$nextTick(() => updateIndicator()); window.addEventListener('resize', () => updateIndicator())">

                {{-- HEADER --}}
                <div class="flex-shrink-0 px-5 pt-4 pb-0">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        {{-- Title + subtitle --}}
                        <div>
                            <div class="flex items-center gap-2">
                                <div class="w-1 h-4 rounded-full bg-cyan-500"></div>
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Dokumen Wajib</h2>
                            </div>
                            <p class="mt-1 ml-3 text-xs text-slate-400 dark:text-slate-500">
                                {{ $uploadedCount }} dari {{ $totalCount }} dokumen telah diunggah
                            </p>
                        </div>

                        {{-- Circular progress --}}
                        <div class="flex-shrink-0 flex items-center gap-3">
                            <div class="relative w-10 h-10">
                                <svg class="w-10 h-10 -rotate-90" viewBox="0 0 36 36">
                                    <circle cx="18" cy="18" r="15" fill="none" class="stroke-slate-100 dark:stroke-slate-800" stroke-width="3"/>
                                    <circle cx="18" cy="18" r="15" fill="none" class="stroke-cyan-500" stroke-width="3"
                                        stroke-dasharray="{{ round($headerPct * 94.2 / 100, 1) }} 94.2"
                                        stroke-linecap="round"/>
                                </svg>
                                <span class="absolute inset-0 flex items-center justify-center text-[9px] font-bold text-slate-700 dark:text-slate-300 tabular-nums">{{ $headerPct }}%</span>
                            </div>
                            {{-- Mini stats --}}
                            <div class="text-right">
                                <div class="text-xs font-semibold text-slate-900 dark:text-white tabular-nums">{{ $pendingDocuments->count() }}</div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 leading-tight">perlu<br>diunggah</div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab strip --}}
                    <div class="relative border-b border-slate-100 dark:border-slate-800">
                        {{-- Sliding indicator --}}
                        <div x-ref="docIndicator"
                            class="absolute bottom-0 h-0.5 bg-cyan-500 transition-all duration-200 ease-in-out"
                            style="will-change: left, width;"></div>

                        <div class="flex gap-1">
                            <button x-ref="docTab_pending" @click="setTab('pending')"
                                :class="activeSection === 'pending'
                                    ? 'text-cyan-600 dark:text-cyan-400'
                                    : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'"
                                class="flex items-center gap-1.5 px-1 pb-2.5 text-xs font-medium transition-colors duration-150 focus-visible:outline-none">
                                <x-heroicon-o-arrow-up-tray class="h-3.5 w-3.5" />
                                Perlu Diunggah
                                @if($pendingDocuments->count() > 0)
                                    <span class="inline-flex items-center rounded-full bg-cyan-500 px-1.5 py-0.5 text-[10px] font-bold text-white tabular-nums">{{ $pendingDocuments->count() }}</span>
                                @endif
                            </button>
                            <button x-ref="docTab_uploaded" @click="setTab('uploaded')"
                                :class="activeSection === 'uploaded'
                                    ? 'text-cyan-600 dark:text-cyan-400'
                                    : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'"
                                class="flex items-center gap-1.5 px-1 pb-2.5 text-xs font-medium transition-colors duration-150 focus-visible:outline-none">
                                <x-heroicon-o-archive-box class="h-3.5 w-3.5" />
                                Arsip
                                @if($uploaded->count() > 0)
                                    <span class="inline-flex items-center rounded-full bg-slate-200 dark:bg-slate-700 px-1.5 py-0.5 text-[10px] font-bold text-slate-600 dark:text-slate-300 tabular-nums">{{ $uploaded->count() }}</span>
                                @endif
                            </button>
                        </div>
                    </div>
                </div>

                {{-- BODY --}}
                <div class="flex-1 overflow-y-auto">

                    {{-- PERLU DIUNGGAH --}}
                    <div x-show="activeSection === 'pending'" x-cloak>
                        <ul class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse($pendingDocuments as $doc)
                                @php
                                    $isOverdue  = $doc['due_date'] && \Carbon\Carbon::parse($doc['due_date'])->isPast();
                                    $accentLine = $doc['is_required'] ? 'border-l-4 border-l-cyan-400' : 'border-l-4 border-l-slate-200 dark:border-l-slate-700';
                                    $typeLabel  = $doc['type'] === 'requirement' ? 'Administratif' : 'Standar Legal';
                                    $typeBadge  = $doc['type'] === 'requirement'
                                        ? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                                        : 'bg-cyan-50 text-cyan-600 dark:bg-cyan-900/20 dark:text-cyan-400';
                                @endphp
                                <li class="group {{ $accentLine }} px-5 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                    <div class="flex items-center gap-4">

                                        {{-- Doc icon --}}
                                        <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center">
                                            <x-heroicon-o-document-text class="h-4 w-4 text-slate-400 dark:text-slate-500" />
                                        </div>

                                        {{-- Info --}}
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $doc['name'] }}</h4>
                                                <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-medium {{ $typeBadge }}">{{ $typeLabel }}</span>
                                                @if($doc['is_required'])
                                                    <span class="inline-flex items-center rounded-md bg-cyan-500 px-1.5 py-0.5 text-[10px] font-bold text-white">Wajib</span>
                                                @endif
                                            </div>
                                            @if($doc['due_date'])
                                                <p class="mt-1 flex items-center gap-1 text-xs {{ $isOverdue ? 'text-slate-600 dark:text-slate-400 font-medium' : 'text-slate-400' }}">
                                                    <x-heroicon-o-calendar class="h-3 w-3 flex-shrink-0" />
                                                    {{ $isOverdue ? 'Lewat tenggat' : 'Tenggat' }}
                                                    {{ \Carbon\Carbon::parse($doc['due_date'])->format('d M Y') }}
                                                </p>
                                            @else
                                                <p class="mt-1 text-xs text-slate-400">Tidak ada tenggat</p>
                                            @endif
                                        </div>

                                        {{-- Upload button --}}
                                        <button class="flex-shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-cyan-500 hover:bg-cyan-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm shadow-cyan-200 dark:shadow-none transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 focus-visible:ring-offset-2">
                                            <x-heroicon-o-arrow-up-tray class="h-3.5 w-3.5" />
                                            Unggah
                                        </button>
                                    </div>
                                </li>
                            @empty
                                <li class="flex flex-col items-center justify-center py-16 text-center px-6">
                                    <div class="w-14 h-14 rounded-full bg-cyan-50 dark:bg-cyan-900/20 flex items-center justify-center mb-3">
                                        <x-heroicon-o-check-circle class="h-7 w-7 text-cyan-500" />
                                    </div>
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Semua dokumen selesai</p>
                                    <p class="mt-1 text-xs text-slate-400">Tidak ada dokumen yang perlu diunggah.</p>
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    {{-- ARSIP --}}
                    <div x-show="activeSection === 'uploaded'" x-cloak>
                        <ul class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse($uploaded as $doc)
                                @php
                                    $statusStyle = match($doc['status']) {
                                        'valid'          => ['badge' => 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-900/20 dark:text-cyan-400 dark:border-cyan-800', 'label' => 'Valid', 'icon' => 'heroicon-o-check-circle', 'line' => 'border-l-4 border-l-cyan-400'],
                                        'pending_review' => ['badge' => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700', 'label' => 'Ditinjau', 'icon' => 'heroicon-o-clock', 'line' => 'border-l-4 border-l-slate-300 dark:border-l-slate-600'],
                                        default          => ['badge' => 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-500 dark:border-slate-700', 'label' => 'Ditandai', 'icon' => 'heroicon-o-flag', 'line' => 'border-l-4 border-l-slate-300 dark:border-l-slate-600'],
                                    };
                                @endphp
                                <li class="group {{ $statusStyle['line'] }} px-5 py-3.5 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                    <div class="flex items-center gap-4">

                                        {{-- Doc icon --}}
                                        <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center">
                                            <x-heroicon-o-document-check class="h-4 w-4 text-slate-400 dark:text-slate-500" />
                                        </div>

                                        {{-- Info --}}
                                        <div class="min-w-0 flex-1">
                                            <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200 truncate" title="{{ $doc['name'] }}">{{ $doc['name'] }}</h4>
                                            <div class="mt-1 flex items-center gap-2 text-xs text-slate-400 tabular-nums">
                                                <span class="inline-flex items-center gap-1 rounded-md border px-1.5 py-0.5 text-[10px] font-semibold {{ $statusStyle['badge'] }}">
                                                    <x-dynamic-component :component="$statusStyle['icon']" class="h-3 w-3" />
                                                    {{ $statusStyle['label'] }}
                                                </span>
                                                @if($doc['uploaded_at'])
                                                    <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                                                    <span>{{ $doc['uploaded_at']->format('d M Y') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Download --}}
                                        @if($doc['file_path'] && $doc['uploaded_document'])
                                            <button wire:click="downloadDocument({{ $doc['uploaded_document']->id }})"
                                                class="flex-shrink-0 inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-2.5 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors focus-visible:outline-none"
                                                title="Unduh Berkas">
                                                <x-heroicon-o-arrow-down-tray class="h-3.5 w-3.5" />
                                                Unduh
                                            </button>
                                        @endif
                                    </div>
                                </li>
                            @empty
                                <li class="flex flex-col items-center justify-center py-16 text-center px-6">
                                    <div class="w-14 h-14 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center mb-3">
                                        <x-heroicon-o-archive-box class="h-7 w-7 text-slate-400" />
                                    </div>
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Arsip kosong</p>
                                    <p class="mt-1 text-xs text-slate-400">Belum ada dokumen yang diunggah.</p>
                                </li>
                            @endforelse
                        </ul>
                    </div>

                </div>
            </div>

            {{-- COL SPAN 3: AKTIVITAS TERBARU (VERTICAL TIMELINE) --}}
            <div class="lg:col-span-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex flex-col h-[600px] overflow-hidden">
                <div class="border-b border-slate-200 dark:border-slate-800 px-4 py-4 flex items-center gap-2 flex-shrink-0">
                    <div class="w-1 h-4 rounded-full bg-cyan-500"></div>
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Aktivitas</h2>
                </div>

                <div class="flex-1 overflow-y-auto px-4 py-4">
                    @forelse($recentActivities as $activity)
                        @php
                            $isLast = $loop->last;

                            $typeMap = match(true) {
                                str_contains($activity->action, 'upload')   => [
                                    'label'   => 'Unggah',
                                    'icon'    => 'heroicon-o-arrow-up-tray',
                                    'dot'     => 'bg-cyan-50 dark:bg-cyan-900/20 border-cyan-200 dark:border-cyan-800',
                                    'icon_color' => 'text-cyan-500',
                                    'badge'   => 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-900/20 dark:text-cyan-400 dark:border-cyan-800',
                                ],
                                str_contains($activity->action, 'created')  => [
                                    'label'   => 'Dibuat',
                                    'icon'    => 'heroicon-o-plus-circle',
                                    'dot'     => 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700',
                                    'icon_color' => 'text-slate-500 dark:text-slate-400',
                                    'badge'   => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
                                ],
                                str_contains($activity->action, 'approved') => [
                                    'label'   => 'Disetujui',
                                    'icon'    => 'heroicon-o-check-circle',
                                    'dot'     => 'bg-cyan-50 dark:bg-cyan-900/20 border-cyan-200 dark:border-cyan-800',
                                    'icon_color' => 'text-cyan-600 dark:text-cyan-400',
                                    'badge'   => 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-900/20 dark:text-cyan-400 dark:border-cyan-800',
                                ],
                                str_contains($activity->action, 'rejected') => [
                                    'label'   => 'Ditolak',
                                    'icon'    => 'heroicon-o-x-circle',
                                    'dot'     => 'bg-slate-100 dark:bg-slate-800 border-slate-300 dark:border-slate-600',
                                    'icon_color' => 'text-slate-500',
                                    'badge'   => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
                                ],
                                str_contains($activity->action, 'updated')  => [
                                    'label'   => 'Diperbarui',
                                    'icon'    => 'heroicon-o-pencil-square',
                                    'dot'     => 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700',
                                    'icon_color' => 'text-slate-400',
                                    'badge'   => 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-500 dark:border-slate-700',
                                ],
                                str_contains($activity->action, 'deleted')  => [
                                    'label'   => 'Dihapus',
                                    'icon'    => 'heroicon-o-trash',
                                    'dot'     => 'bg-slate-100 dark:bg-slate-800 border-slate-300 dark:border-slate-600',
                                    'icon_color' => 'text-slate-500',
                                    'badge'   => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
                                ],
                                str_contains($activity->action, 'report')   => [
                                    'label'   => 'Laporan',
                                    'icon'    => 'heroicon-o-document-chart-bar',
                                    'dot'     => 'bg-cyan-50 dark:bg-cyan-900/20 border-cyan-200 dark:border-cyan-800',
                                    'icon_color' => 'text-cyan-500',
                                    'badge'   => 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-900/20 dark:text-cyan-400 dark:border-cyan-800',
                                ],
                                default => [
                                    'label'   => 'Aktivitas',
                                    'icon'    => 'heroicon-o-bolt',
                                    'dot'     => 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700',
                                    'icon_color' => 'text-slate-400',
                                    'badge'   => 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-500 dark:border-slate-700',
                                ],
                            };

                            $isToday     = $activity->created_at->isToday();
                            $isYesterday = $activity->created_at->isYesterday();
                            $timeLabel   = $isToday
                                ? $activity->created_at->format('H:i')
                                : ($isYesterday ? 'Kemarin' : $activity->created_at->format('d M'));
                        @endphp
                        <div class="relative flex gap-3 {{ $isLast ? '' : 'pb-5' }}">
                            {{-- Timeline line --}}
                            @unless($isLast)
                                <div class="absolute left-[13px] top-6 bottom-0 w-px bg-slate-100 dark:bg-slate-800"></div>
                            @endunless

                            {{-- Icon dot --}}
                            <div class="flex-shrink-0 w-7 h-7 rounded-full border flex items-center justify-center z-10 {{ $typeMap['dot'] }}">
                                <x-dynamic-component :component="$typeMap['icon']" class="h-3.5 w-3.5 {{ $typeMap['icon_color'] }}" />
                            </div>

                            {{-- Content --}}
                            <div class="min-w-0 flex-1 pt-0.5">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="inline-flex items-center rounded-md border px-1.5 py-0.5 text-[10px] font-semibold {{ $typeMap['badge'] }}">
                                        {{ $typeMap['label'] }}
                                    </span>
                                    <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500 tabular-nums">{{ $timeLabel }}</span>
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-400 leading-snug line-clamp-2">
                                    {{ $activity->description }}
                                </p>
                                @if($activity->user)
                                    <p class="mt-0.5 text-[10px] text-slate-400 dark:text-slate-500 truncate">{{ $activity->user->name }}</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full text-center py-8">
                            <x-heroicon-o-clock class="h-8 w-8 text-slate-300 dark:text-slate-700 mb-2" />
                            <p class="text-xs text-slate-400">Belum ada aktivitas.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    @endif
</div>