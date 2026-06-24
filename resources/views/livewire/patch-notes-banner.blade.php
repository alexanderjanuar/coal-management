@php
    $typeLabels = [
        'feature'     => 'Fitur Baru',
        'improvement' => 'Peningkatan',
        'fix'         => 'Perbaikan',
    ];
    $typeOrder = ['feature', 'improvement', 'fix'];
@endphp

<div>
@if ($visible && count($patches))
    <div class="pnb" wire:key="patch-banner">
        <span class="pnb-accentline" aria-hidden="true"></span>

        {{-- Header --}}
        <div class="pnb-top">
            <span class="pnb-kicker"><span class="pnb-kicker-dot"></span>Pembaruan Sistem</span>
            <button type="button" class="pnb-close" wire:click="dismiss" aria-label="Tutup">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Scrollable content --}}
        <div class="pnb-scroll">
            @foreach ($patches as $patch)
                <section class="pnb-patch">
                    <div class="pnb-titlerow">
                        <h3 class="pnb-title">{{ $patch['title'] }}</h3>
                        <span class="pnb-version">{{ $patch['version'] }}</span>
                        @if ($patch['released_at'])<span class="pnb-date">{{ $patch['released_at'] }}</span>@endif
                    </div>

                    @if ($patch['description'])
                        <p class="pnb-desc">{{ $patch['description'] }}</p>
                    @endif

                    @php $grouped = collect($patch['changes'])->groupBy('type'); $gi = 0; @endphp
                    <div class="pnb-timeline">
                        @foreach ($typeOrder as $type)
                            @php $items = $grouped->get($type); @endphp
                            @if ($items && count($items))
                                <div class="pnb-group pnb-group-{{ $type }}" style="--g: {{ $gi++ }};">
                                    <div class="pnb-ghead">
                                        <span class="pnb-glabel">{{ $typeLabels[$type] }}</span>
                                        <span class="pnb-gcount">{{ count($items) }}</span>
                                    </div>
                                    <ul class="pnb-gitems">
                                        @foreach ($items as $c)
                                            <li class="pnb-item">
                                                @if (! empty($c['area']))<span class="pnb-area">{{ $c['area'] }}</span>@endif
                                                <span class="pnb-item-text">{{ $c['text'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    <style>
        /* Light theme tokens */
        .pnb {
            --pnb-bg: #ffffff;
            --pnb-border: #e7e9ee;
            --pnb-ink: #0f172a;
            --pnb-muted: #5b6573;
            --pnb-subtle: #98a1b0;
            --pnb-line: #eef0f4;
            --pnb-dot: rgba(15,23,42,.04);
            --pnb-rail: #e3e6ec;
            --pnb-accent-ink: #0e7490;
            --pnb-accent-bg: #ecfeff;
            --pnb-accent-border: rgba(8,145,178,.22);
            --pnb-thumb: rgba(15,23,42,.16);

            position: relative;
            display: flex;
            flex-direction: column;
            margin-bottom: 1.5rem;
            max-height: 380px;
            border: 1px solid var(--pnb-border);
            border-radius: 16px;
            background-color: var(--pnb-bg);
            background-image: radial-gradient(var(--pnb-dot) 1px, transparent 1px);
            background-size: 18px 18px;
            background-position: -1px -1px;
            color: var(--pnb-ink);
            padding: 20px 22px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(15,23,42,.04), 0 12px 28px -18px rgba(15,23,42,.22);
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            animation: pnbIn .34s ease-out;
        }
        /* Dark theme — Filament toggles .dark on <html> */
        .dark .pnb {
            --pnb-bg: #161616;
            --pnb-border: #2b2b2b;
            --pnb-ink: #f3f4f6;
            --pnb-muted: #a1a1aa;
            --pnb-subtle: #6b7280;
            --pnb-line: #242424;
            --pnb-dot: rgba(255,255,255,.045);
            --pnb-rail: #2f2f2f;
            --pnb-accent-ink: #67e8f9;
            --pnb-accent-bg: rgba(8,51,68,.45);
            --pnb-accent-border: rgba(34,211,238,.28);
            --pnb-thumb: rgba(255,255,255,.16);
            box-shadow: 0 12px 28px -18px rgba(0,0,0,.75);
        }
        @keyframes pnbIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }

        /* Thin accent line across the top edge */
        .pnb-accentline {
            position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, #34d399 0%, #34d399 33%, #60a5fa 33%, #60a5fa 66%, #fbbf24 66%, #fbbf24 100%);
            opacity: .9;
        }

        /* Header */
        .pnb-top { flex-shrink: 0; display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
        .pnb-kicker { display: inline-flex; align-items: center; gap: 7px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: var(--pnb-subtle); }
        .pnb-kicker-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--pnb-accent-ink); box-shadow: 0 0 0 3px var(--pnb-accent-bg); }
        .pnb-close {
            display: inline-flex; align-items: center; justify-content: center;
            width: 28px; height: 28px; border: 0; border-radius: 8px;
            background: transparent; color: var(--pnb-subtle); cursor: pointer;
            transition: background .12s, color .12s;
        }
        .pnb-close svg { width: 16px; height: 16px; }
        .pnb-close:hover { background: var(--pnb-line); color: var(--pnb-ink); }

        /* Scroll region */
        .pnb-scroll { flex: 1; min-height: 0; overflow-y: auto; padding-right: 8px; }
        .pnb-scroll::-webkit-scrollbar { width: 7px; }
        .pnb-scroll::-webkit-scrollbar-thumb { background: var(--pnb-thumb); border-radius: 99px; }
        .pnb-scroll { scrollbar-width: thin; scrollbar-color: var(--pnb-thumb) transparent; }

        .pnb-patch + .pnb-patch { margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--pnb-line); }

        /* Title row */
        .pnb-titlerow { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
        .pnb-title { margin: 0; font-size: 18px; font-weight: 750; letter-spacing: -.01em; color: var(--pnb-ink); }
        .pnb-version {
            display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 6px;
            font-size: 11.5px; font-weight: 700; font-variant-numeric: tabular-nums;
            color: var(--pnb-accent-ink); background: var(--pnb-accent-bg);
            box-shadow: inset 0 0 0 1px var(--pnb-accent-border);
        }
        .pnb-date { font-size: 12px; color: var(--pnb-subtle); font-weight: 500; }
        .pnb-desc { margin: 8px 0 0; font-size: 13px; line-height: 1.55; color: var(--pnb-muted); max-width: 640px; }

        /* Timeline of grouped changes */
        .pnb-timeline { position: relative; margin-top: 20px; padding-left: 26px; display: flex; flex-direction: column; gap: 20px; }
        .pnb-timeline::before { content: ''; position: absolute; left: 7px; top: 5px; bottom: 5px; width: 1.5px; background: var(--pnb-rail); border-radius: 2px; }

        .pnb-group {
            position: relative;
            opacity: 0;
            animation: pnbUp .42s cubic-bezier(.2,.7,.3,1) both;
            animation-delay: calc(var(--g) * 90ms + .12s);
        }
        @keyframes pnbUp { from { opacity: 0; transform: translateY(7px); } to { opacity: 1; transform: translateY(0); } }
        /* Timeline node — colored per type, sits on the rail */
        .pnb-group::before {
            content: ''; position: absolute; left: -23.5px; top: 3px;
            width: 11px; height: 11px; border-radius: 50%;
            background: var(--acc);
            box-shadow: 0 0 0 4px var(--pnb-bg), 0 0 0 5px color-mix(in srgb, var(--acc) 30%, transparent);
        }

        /* Muted accents per type (node + label only) */
        .pnb-group-feature     { --acc: #34d399; --acc-ink: #059669; }
        .pnb-group-improvement { --acc: #60a5fa; --acc-ink: #2563eb; }
        .pnb-group-fix         { --acc: #fbbf24; --acc-ink: #d97706; }
        .dark .pnb-group-feature     { --acc: #34d399; --acc-ink: #6ee7b7; }
        .dark .pnb-group-improvement { --acc: #60a5fa; --acc-ink: #93c5fd; }
        .dark .pnb-group-fix         { --acc: #fbbf24; --acc-ink: #fcd34d; }

        .pnb-ghead { display: flex; align-items: center; gap: 8px; margin-bottom: 9px; }
        .pnb-glabel { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--acc-ink); }
        .pnb-gcount {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 18px; height: 17px; padding: 0 6px;
            font-size: 10.5px; font-weight: 700; line-height: 1;
            color: var(--pnb-muted); background: var(--pnb-line); border-radius: 99px;
        }

        .pnb-gitems { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 4px; }
        .pnb-item {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 5px 9px; margin: 0 -9px; border-radius: 7px;
            transition: background .12s;
        }
        .pnb-item:hover { background: var(--pnb-line); }
        .pnb-area {
            flex-shrink: 0; min-width: 62px; text-align: center;
            font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em;
            color: var(--pnb-muted);
            padding: 3px 8px; border-radius: 5px;
            background: var(--pnb-line);
            box-shadow: inset 0 0 0 1px var(--pnb-border);
            margin-top: 1px;
        }
        .pnb-item-text { flex: 1; min-width: 0; font-size: 13px; line-height: 1.55; color: var(--pnb-ink); }

        @media (max-width: 640px) {
            .pnb { padding: 16px; max-height: 66vh; }
            .pnb-title { font-size: 16px; }
            .pnb-timeline { padding-left: 22px; }
            .pnb-group::before { left: -19.5px; }
            .pnb-timeline::before { left: 6px; }
        }
    </style>
@endif
</div>
