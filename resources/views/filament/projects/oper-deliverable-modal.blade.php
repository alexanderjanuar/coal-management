@php
    $formatSize = function ($bytes) {
        if (! $bytes) return null;
        return $bytes >= 1048576
            ? number_format($bytes / 1048576, 1) . ' MB'
            : number_format($bytes / 1024, 0) . ' KB';
    };
    $selectedCount = count($this->operFileIndices);
    $target = $this->operTarget;

    // Label tujuan terpilih untuk ringkasan.
    $selLabel = 'dokumen umum';
    if (str_starts_with($target, 'sop:')) {
        $sid = (int) substr($target, 4);
        $selLabel = 'Dokumen Legal — ' . (optional($sopDocs->firstWhere('id', $sid))->name ?? 'terpilih');
    } elseif (str_starts_with($target, 'req:')) {
        $rid = (int) substr($target, 4);
        $selLabel = 'Persyaratan — ' . (optional($requirements->firstWhere('id', $rid))->name ?? 'terpilih');
    }

    $reqGroups = $requirements->groupBy(fn ($r) => $r->group?->name ?? 'Tanpa Grup');
@endphp

<style>
    .oper-wrap { display: flex; flex-direction: column; gap: 1.5rem; }
    .oper-head { display: flex; align-items: center; gap: .5rem; margin-bottom: .75rem; }
    .oper-head--between { justify-content: space-between; }
    .oper-num {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.5rem; height: 1.5rem; border-radius: 9999px;
        font-size: .7rem; font-weight: 700; background: #cffafe; color: #0e7490;
    }
    .dark .oper-num { background: #164e63; color: #a5f3fc; }
    .oper-h3 { font-size: .875rem; font-weight: 600; color: #111827; }
    .dark .oper-h3 { color: #f3f4f6; }
    .oper-count { font-size: .75rem; font-weight: 500; color: #6b7280; }
    .dark .oper-count { color: #9ca3af; }

    .oper-subhead {
        font-size: .6875rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase;
        color: #6b7280; margin: 1rem 0 .5rem;
    }
    .dark .oper-subhead { color: #9ca3af; }
    .oper-subhead:first-child { margin-top: 0; }
    .oper-grouplabel { font-size: .75rem; font-weight: 600; color: #4b5563; margin: .625rem 0 .375rem; }
    .dark .oper-grouplabel { color: #d1d5db; }

    .oper-grid { display: grid; grid-template-columns: 1fr; gap: .75rem; }
    @media (min-width: 640px) { .oper-grid { grid-template-columns: 1fr 1fr; } }

    .oper-card {
        position: relative; display: flex; align-items: flex-start; gap: .75rem;
        text-align: left; width: 100%; padding: 1rem;
        border: 2px solid #e5e7eb; border-radius: .75rem; background: #ffffff;
        cursor: pointer; transition: all .15s ease;
    }
    .dark .oper-card { border-color: #374151; background: rgba(31,41,55,.35); }
    .oper-card:hover { border-color: #67e8f9; background: #f9fafb; }
    .dark .oper-card:hover { border-color: #155e75; background: rgba(31,41,55,.7); }

    .oper-card.is-cyan { border-color: #06b6d4; background: #ecfeff; box-shadow: 0 0 0 3px rgba(6,182,212,.18); }
    .dark .oper-card.is-cyan { border-color: #22d3ee; background: rgba(8,51,68,.55); box-shadow: 0 0 0 3px rgba(34,211,238,.22); }
    .oper-card.is-emerald { border-color: #10b981; background: #ecfdf5; box-shadow: 0 0 0 3px rgba(16,185,129,.18); }
    .dark .oper-card.is-emerald { border-color: #34d399; background: rgba(2,44,34,.55); box-shadow: 0 0 0 3px rgba(52,211,153,.22); }
    .oper-card--row { align-items: center; padding: .875rem; }

    .oper-ico {
        flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center;
        width: 2.25rem; height: 2.25rem; border-radius: .5rem; background: #f3f4f6; color: #9ca3af;
    }
    .dark .oper-ico { background: #374151; color: #d1d5db; }
    .oper-ico svg { width: 1.25rem; height: 1.25rem; }
    .oper-ico.is-amber { background: #fef3c7; color: #d97706; }
    .dark .oper-ico.is-amber { background: rgba(120,53,15,.5); color: #fcd34d; }
    .oper-ico.is-indigo { background: #e0e7ff; color: #4f46e5; }
    .dark .oper-ico.is-indigo { background: rgba(49,46,129,.5); color: #a5b4fc; }
    .oper-ico.on-cyan { background: #06b6d4; color: #ffffff; }
    .oper-ico.on-emerald { background: #10b981; color: #ffffff; }

    .oper-body { flex: 1; min-width: 0; }
    .oper-title { display: block; font-size: .875rem; font-weight: 600; color: #111827; }
    .dark .oper-title { color: #f3f4f6; }
    .oper-title--trunc { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .oper-sub { display: block; font-size: .75rem; color: #6b7280; margin-top: .125rem; }
    .dark .oper-sub { color: #9ca3af; }
    .oper-meta { display: block; font-size: .6875rem; color: #6b7280; margin-top: .25rem; }
    .dark .oper-meta { color: #9ca3af; }

    .oper-badges { display: flex; flex-wrap: wrap; gap: .375rem; margin-top: .375rem; }
    .oper-badge { display: inline-flex; align-items: center; padding: .0625rem .375rem; border-radius: .25rem; font-size: .625rem; font-weight: 600; }
    .b-req { background: #fee2e2; color: #b91c1c; }
    .dark .b-req { background: rgba(127,29,29,.5); color: #fca5a5; }
    .b-opt { background: #f3f4f6; color: #4b5563; }
    .dark .b-opt { background: #374151; color: #d1d5db; }
    .b-cat { background: #ecfeff; color: #0e7490; }
    .dark .b-cat { background: rgba(8,51,68,.5); color: #67e8f9; }
    .b-group { background: #f5f3ff; color: #6d28d9; }
    .dark .b-group { background: rgba(46,16,101,.5); color: #c4b5fd; }
    .b-done { background: #d1fae5; color: #047857; }
    .dark .b-done { background: rgba(6,78,59,.5); color: #6ee7b7; }

    .oper-check { position: absolute; top: .5rem; right: .5rem; color: #06b6d4; }
    .dark .oper-check { color: #22d3ee; }
    .oper-check svg { width: 1.25rem; height: 1.25rem; }

    .oper-box {
        flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center;
        width: 1.25rem; height: 1.25rem; border-radius: .375rem; border: 2px solid #d1d5db; color: transparent;
    }
    .dark .oper-box { border-color: #4b5563; }
    .oper-box.is-on { background: #10b981; border-color: #10b981; color: #ffffff; }
    .oper-box svg { width: .875rem; height: .875rem; }

    .oper-empty { font-size: .75rem; color: #6b7280; margin-top: .5rem; }
    .dark .oper-empty { color: #9ca3af; }

    .oper-summary { display: flex; align-items: flex-start; gap: .5rem; padding: .75rem; border-radius: .5rem; background: #f9fafb; border: 1px solid #e5e7eb; }
    .dark .oper-summary { background: rgba(31,41,55,.6); border-color: #374151; }
    .oper-summary svg { width: 1rem; height: 1rem; flex-shrink: 0; margin-top: .125rem; color: #06b6d4; }
    .oper-summary-text { font-size: .75rem; color: #4b5563; }
    .dark .oper-summary-text { color: #d1d5db; }
</style>

@php
    // Ikon SVG reusable
    $svgDoc = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
    $svgShield = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>';
    $svgClip = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>';
    $svgCheck = '<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.5 7.5a1 1 0 01-1.42 0l-3.5-3.5a1 1 0 111.42-1.42l2.79 2.79 6.79-6.79a1 1 0 011.42 0z" clip-rule="evenodd"/></svg>';
@endphp

<div class="oper-wrap">
    {{-- ============ 1. TUJUAN DOKUMEN ============ --}}
    <div>
        <div class="oper-head">
            <span class="oper-num">1</span>
            <h3 class="oper-h3">Tujuan dokumen</h3>
        </div>

        {{-- 1a. Dokumen Umum --}}
        <div class="oper-subhead">Dokumen Umum</div>
        <div class="oper-grid">
            @php $on = $target === 'general'; @endphp
            <button type="button" wire:click="selectOperTarget('general')" class="oper-card {{ $on ? 'is-cyan' : '' }}">
                <span class="oper-ico {{ $on ? 'on-cyan' : '' }}">{!! $svgDoc !!}</span>
                <span class="oper-body">
                    <span class="oper-title">Dokumen Umum</span>
                    <span class="oper-sub">Simpan sebagai dokumen tambahan klien, tanpa memenuhi slot tertentu.</span>
                </span>
                @if ($on)<span class="oper-check">{!! $svgCheck !!}</span>@endif
            </button>
        </div>

        {{-- 1b. Dokumen Legal Wajib (SOP) --}}
        @if ($sopDocs->isNotEmpty())
            <div class="oper-subhead">Dokumen Legal Wajib</div>
            <div class="oper-grid">
                @foreach ($sopDocs as $sop)
                    @php $on = $target === 'sop:' . $sop->id; $done = in_array($sop->id, $uploadedSopIds); @endphp
                    <button type="button" wire:click="selectOperTarget('sop:{{ $sop->id }}')" class="oper-card {{ $on ? 'is-cyan' : '' }}">
                        <span class="oper-ico {{ $on ? 'on-cyan' : 'is-indigo' }}">{!! $svgShield !!}</span>
                        <span class="oper-body">
                            <span class="oper-title oper-title--trunc">{{ $sop->name }}</span>
                            <span class="oper-badges">
                                @if ($sop->is_required)
                                    <span class="oper-badge b-req">Wajib</span>
                                @else
                                    <span class="oper-badge b-opt">Opsional</span>
                                @endif
                                @if ($sop->category)
                                    <span class="oper-badge b-cat">{{ ucfirst($sop->category) }}</span>
                                @endif
                                @if ($done)
                                    <span class="oper-badge b-done">Sudah ada</span>
                                @endif
                            </span>
                        </span>
                        @if ($on)<span class="oper-check">{!! $svgCheck !!}</span>@endif
                    </button>
                @endforeach
            </div>
        @endif

        {{-- 1c. Persyaratan (grup) --}}
        @if ($requirements->isNotEmpty())
            <div class="oper-subhead">Persyaratan</div>
            @foreach ($reqGroups as $groupName => $reqs)
                <div class="oper-grouplabel">{{ $groupName }}</div>
                <div class="oper-grid">
                    @foreach ($reqs as $req)
                        @php $on = $target === 'req:' . $req->id; @endphp
                        <button type="button" wire:click="selectOperTarget('req:{{ $req->id }}')" class="oper-card {{ $on ? 'is-cyan' : '' }}">
                            <span class="oper-ico {{ $on ? 'on-cyan' : 'is-amber' }}">{!! $svgClip !!}</span>
                            <span class="oper-body">
                                <span class="oper-title oper-title--trunc">{{ $req->name }}</span>
                                <span class="oper-badges">
                                    @if ($req->is_required)
                                        <span class="oper-badge b-req">Wajib</span>
                                    @else
                                        <span class="oper-badge b-opt">Opsional</span>
                                    @endif
                                    @if ($req->category)
                                        <span class="oper-badge b-cat">{{ ucfirst($req->category) }}</span>
                                    @endif
                                </span>
                                @if ($req->due_date)
                                    <span class="oper-meta">Tenggat: {{ $req->due_date->format('d M Y') }}</span>
                                @endif
                            </span>
                            @if ($on)<span class="oper-check">{!! $svgCheck !!}</span>@endif
                        </button>
                    @endforeach
                </div>
            @endforeach
        @endif

        @if ($sopDocs->isEmpty() && $requirements->isEmpty())
            <p class="oper-empty">Klien belum punya dokumen legal/persyaratan yang bisa dipetakan. File akan disimpan sebagai dokumen umum.</p>
        @endif
    </div>

    {{-- ============ 2. FILE DELIVERABLE ============ --}}
    <div>
        <div class="oper-head oper-head--between">
            <div class="oper-head" style="margin-bottom:0;">
                <span class="oper-num">2</span>
                <h3 class="oper-h3">Pilih file deliverable</h3>
            </div>
            <span class="oper-count">{{ $selectedCount }} dipilih</span>
        </div>

        <div class="oper-grid">
            @foreach ($files as $index => $file)
                @php
                    $name = $file['name'] ?? basename($file['path'] ?? 'File');
                    $size = $formatSize($file['size'] ?? null);
                    $picked = in_array($index, $this->operFileIndices);
                @endphp
                <button type="button" wire:click="toggleOperFile({{ $index }})" class="oper-card oper-card--row {{ $picked ? 'is-emerald' : '' }}">
                    <span class="oper-ico {{ $picked ? 'on-emerald' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </span>
                    <span class="oper-body">
                        <span class="oper-title oper-title--trunc">{{ $name }}</span>
                        @if ($size)<span class="oper-sub">{{ $size }}</span>@endif
                    </span>
                    <span class="oper-box {{ $picked ? 'is-on' : '' }}">
                        @if ($picked){!! $svgCheck !!}@endif
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ============ RINGKASAN ============ --}}
    <div class="oper-summary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="oper-summary-text">
            <strong>{{ $selectedCount }}</strong> file akan <strong>disalin</strong> ke
            <strong>{{ $selLabel }}</strong>@if ($target !== 'general') (otomatis ditandai terisi/<em>fulfilled</em>)@endif.
            File deliverable di proyek tetap utuh.
        </p>
    </div>
</div>
