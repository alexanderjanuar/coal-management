@php
    use App\Livewire\Projects\ProjectDashboard;
    $timeRanges = ProjectDashboard::TIME_RANGES;

    $k = $this->kpis;
    $statusDist = $this->statusDistribution;
    $picWorkload = $this->picWorkload;
    $sopDistribution = $this->sopDistribution;
    $sopSamples = $this->sopSamples;
    $trend = $this->completionTrend;
    $timeLabel = $this->timeRangeLabel;
    $timeSummary = $this->dateRangeSummary;

    // Donut math
    $donutSize = 220;
    $donutStroke = 32;
    $donutRadius = ($donutSize / 2) - ($donutStroke / 2);
    $donutCircumference = 2 * M_PI * $donutRadius;
    $donutOffset = 0;
    // Precompute segments for Alpine binding
    $donutSegments = [];
    $cumulative = 0;
    foreach ($statusDist['rows'] as $i => $row) {
        $dash = $statusDist['total'] > 0 ? ($row['count'] / $statusDist['total']) * $donutCircumference : 0;
        $donutSegments[] = $row + [
            'index'  => $i,
            'dash'   => $dash,
            'gap'    => $donutCircumference - $dash,
            'offset' => -$cumulative,
        ];
        $cumulative += $dash;
    }

    // Per-SOP donut math — same dimensions as the main "Distribusi Status"
    // donut so the carousel slide visually mirrors that widget.
    $sopDonutSize = $donutSize;
    $sopDonutStroke = $donutStroke;
    $sopDonutRadius = $donutRadius;
    $sopDonutCirc = $donutCircumference;
    foreach ($sopDistribution['rows'] as &$sop) {
        $cum = 0;
        $sop['donutSegments'] = [];
        foreach ($sop['segments'] as $idx => $seg) {
            $dash = $sop['total'] > 0 ? ($seg['count'] / $sop['total']) * $sopDonutCirc : 0;
            $sop['donutSegments'][] = $seg + [
                'index'  => $idx,
                'dash'   => $dash,
                'gap'    => $sopDonutCirc - $dash,
                'offset' => -$cum,
            ];
            $cum += $dash;
        }
    }
    unset($sop);

    // For infinite loop: prepend last SOP and append first SOP as cloned
    // peek slides. When the user scrolls onto a clone, the JS silently jumps
    // the scroll position back to the real counterpart — preserving native
    // scroll-snap physics while giving the perception of an endless carousel.
    $sopRows = $sopDistribution['rows'];
    $totalSop = count($sopRows);
    $loopRows = [];
    if ($totalSop > 1) {
        $loopRows[] = ['__realIdx' => $totalSop - 1, '__isClone' => true] + $sopRows[$totalSop - 1];
        foreach ($sopRows as $i => $sop) {
            $loopRows[] = ['__realIdx' => $i, '__isClone' => false] + $sop;
        }
        $loopRows[] = ['__realIdx' => 0, '__isClone' => true] + $sopRows[0];
    } elseif ($totalSop === 1) {
        $loopRows[] = ['__realIdx' => 0, '__isClone' => false] + $sopRows[0];
    }
@endphp

<div class="pd-root">
    {{-- ============== TOP BAR: time-range filter ============== --}}
    <div class="pd-topbar">
        <div class="pd-topbar-meta">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="pd-topbar-meta-icon">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <span>{{ $timeSummary }}</span>
        </div>

        <div class="pd-time-picker" x-data="{ open: false }" @keydown.escape.window="open = false">
            <button type="button" @click="open = !open" class="pd-time-trigger" :class="open && 'is-open'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/>
                    <polyline points="12 7 12 12 15 14"/>
                </svg>
                <span class="pd-time-label">{{ $timeLabel }}</span>
                <svg class="pd-time-caret" :class="open && 'is-flipped'" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </button>

            <div x-show="open" @click.outside="open = false" x-cloak class="pd-time-menu" x-transition.opacity.duration.150ms>
                <div class="pd-time-menu-head">Pilih Periode</div>
                @foreach ($timeRanges as $key => $label)
                    <button type="button"
                            wire:click="setTimeRange('{{ $key }}')"
                            @click="open = false"
                            class="pd-time-option {{ $timeRange === $key ? 'is-active' : '' }}">
                        <span>{{ $label }}</span>
                        @if ($timeRange === $key)
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ============== ROW 1: Pipeline donut + PIC workload ============== --}}
    <section class="pd-row pd-row-2col">
        {{-- Interactive donut --}}
        <div class="pd-card" x-data="{ hovered: null }">
            <header class="pd-card-head">
                <h3 class="pd-card-title">Distribusi Status</h3>
                <span class="pd-card-meta">{{ $statusDist['total'] }} proyek</span>
            </header>

            @if ($statusDist['total'] === 0)
                <div class="pd-empty">Belum ada data proyek.</div>
            @else
                <div class="pd-donut-wrap">
                    <svg class="pd-donut" width="{{ $donutSize }}" height="{{ $donutSize }}" viewBox="0 0 {{ $donutSize }} {{ $donutSize }}">
                        <circle cx="{{ $donutSize / 2 }}" cy="{{ $donutSize / 2 }}" r="{{ $donutRadius }}"
                                fill="none" stroke="#eef0f3" stroke-width="{{ $donutStroke }}"/>
                        @foreach ($donutSegments as $seg)
                            <circle class="pd-donut-seg"
                                    cx="{{ $donutSize / 2 }}" cy="{{ $donutSize / 2 }}" r="{{ $donutRadius }}"
                                    fill="none"
                                    stroke="{{ $seg['color'] }}"
                                    stroke-width="{{ $donutStroke }}"
                                    stroke-dasharray="{{ $seg['dash'] }} {{ $seg['gap'] }}"
                                    stroke-dashoffset="{{ $seg['offset'] }}"
                                    transform="rotate(-90 {{ $donutSize / 2 }} {{ $donutSize / 2 }})"
                                    @mouseenter="hovered = {{ $seg['index'] }}"
                                    @mouseleave="hovered = null"
                                    :class="{ 'is-dim': hovered !== null && hovered !== {{ $seg['index'] }}, 'is-active': hovered === {{ $seg['index'] }} }">
                                <title>{{ $seg['label'] }}: {{ $seg['count'] }} ({{ $seg['percent'] }}%)</title>
                            </circle>
                        @endforeach
                    </svg>
                    <div class="pd-donut-center">
                        @php $segments = $donutSegments; @endphp
                        {{-- Default state: total --}}
                        <template x-if="hovered === null">
                            <div>
                                <div class="pd-donut-num">{{ $statusDist['total'] }}</div>
                                <div class="pd-donut-cap">total</div>
                            </div>
                        </template>
                        {{-- Hovered state: per-segment --}}
                        @foreach ($segments as $seg)
                            <template x-if="hovered === {{ $seg['index'] }}">
                                <div>
                                    <div class="pd-donut-num" style="color: {{ $seg['color'] }};">{{ $seg['count'] }}</div>
                                    <div class="pd-donut-cap" style="color: {{ $seg['color'] }};">{{ $seg['label'] }} · {{ $seg['percent'] }}%</div>
                                </div>
                            </template>
                        @endforeach
                    </div>
                </div>

                <ul class="pd-legend">
                    @foreach ($donutSegments as $seg)
                        <li class="pd-legend-item"
                            @mouseenter="hovered = {{ $seg['index'] }}"
                            @mouseleave="hovered = null"
                            :class="{ 'is-dim': hovered !== null && hovered !== {{ $seg['index'] }}, 'is-active': hovered === {{ $seg['index'] }} }">
                            <span class="pd-legend-dot" style="background: {{ $seg['color'] }};"></span>
                            <span class="pd-legend-label">{{ $seg['label'] }}</span>
                            <span class="pd-legend-count">{{ $seg['count'] }}</span>
                            <span class="pd-legend-pct">{{ $seg['percent'] }}%</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- PIC workload — stacked by status --}}
        <div class="pd-card" x-data="{ hoveredStatus: null }">
            <header class="pd-card-head">
                <h3 class="pd-card-title">Beban Kerja PIC</h3>
                <span class="pd-card-meta">10 teratas · proyek aktif</span>
            </header>

            @if (empty($picWorkload['rows']))
                <div class="pd-empty">Belum ada PIC dengan proyek aktif.</div>
            @else
                {{-- Status legend — hover a chip to highlight matching segments --}}
                <ul class="pd-stack-legend">
                    @foreach ($picWorkload['legend'] as $s)
                        <li class="pd-stack-legend-item"
                            @mouseenter="hoveredStatus = '{{ $s['key'] }}'"
                            @mouseleave="hoveredStatus = null"
                            :class="{ 'is-dim': hoveredStatus && hoveredStatus !== '{{ $s['key'] }}' }">
                            <span class="pd-stack-legend-dot" style="background: {{ $s['color'] }};"></span>
                            <span class="pd-stack-legend-label">{{ $s['label'] }}</span>
                        </li>
                    @endforeach
                </ul>

                <ul class="pd-bars">
                    @foreach ($picWorkload['rows'] as $row)
                        <li class="pd-bar-row">
                            <div class="pd-bar-info">
                                <span class="pd-bar-name" title="{{ $row['name'] }}">{{ $row['name'] }}</span>
                                <span class="pd-bar-count">{{ $row['total'] }}</span>
                            </div>
                            <div class="pd-bar-track">
                                <div class="pd-bar-stack" style="width: {{ $row['percent'] }}%;">
                                    @foreach ($row['segments'] as $seg)
                                        <div class="pd-bar-seg"
                                             style="width: {{ $seg['segPercent'] }}%; background: {{ $seg['color'] }};"
                                             title="{{ $seg['label'] }}: {{ $seg['count'] }} proyek"
                                             @mouseenter="hoveredStatus = '{{ $seg['status'] }}'"
                                             @mouseleave="hoveredStatus = null"
                                             :class="{ 'is-dim': hoveredStatus && hoveredStatus !== '{{ $seg['status'] }}' }">
                                            @if ($seg['segPercent'] >= 18)
                                                <span class="pd-bar-seg-count">{{ $seg['count'] }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>

    {{-- ============== ROW 3: SOP donut carousel + synced sample list ============== --}}
    {{-- x-data lifted to the section so BOTH cards share `active` — swiping the
         left donut carousel updates the right sample list in real time. --}}
    <section class="pd-row pd-row-2col pd-row-2col--top"
             x-data="{
                active: 0,
                hov: null,
                total: {{ $totalSop }},
                isLooping: {{ $totalSop > 1 ? 'true' : 'false' }},

                init() {
                    if (!this.isLooping) return;
                    // Skip past the prepended last-clone so the user starts on the
                    // real first slide without an initial flash.
                    this.$nextTick(() => this.scrollToDom(1, false));
                },

                /* Map real SOP index → DOM child index inside the looped track.
                   With clones, DOM[0] = clone-of-last, DOM[1..N] = reals, DOM[N+1] = clone-of-first. */
                domFor(realIdx) {
                    return this.isLooping ? realIdx + 1 : realIdx;
                },

                scrollToDom(domIdx, smooth = true) {
                    const track = this.$refs.track;
                    const slide = track?.children[domIdx];
                    if (!slide) return;
                    if (!smooth) {
                        const prev = track.style.scrollBehavior;
                        track.style.scrollBehavior = 'auto';
                        slide.scrollIntoView({ behavior: 'auto', inline: 'center', block: 'nearest' });
                        requestAnimationFrame(() => { track.style.scrollBehavior = prev || ''; });
                    } else {
                        slide.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                    }
                },

                go(realIdx) {
                    if (this.total <= 1) return;
                    // Normalize so negatives wrap to the end and overshoot wraps to start.
                    realIdx = ((realIdx % this.total) + this.total) % this.total;
                    // Smart routing: scroll INTO the clone in the direction the user
                    // expects, then the silent jump in syncFromScroll lands them on
                    // the real counterpart — so the wrap looks continuous.
                    if (this.isLooping && this.active === 0 && realIdx === this.total - 1) {
                        this.scrollToDom(0, true);             // visually slide LEFT toward last
                    } else if (this.isLooping && this.active === this.total - 1 && realIdx === 0) {
                        this.scrollToDom(this.total + 1, true); // visually slide RIGHT toward first
                    } else {
                        this.scrollToDom(this.domFor(realIdx), true);
                    }
                    this.active = realIdx;
                    this.hov = null;
                },

                syncFromScroll() {
                    const track = this.$refs.track;
                    if (!track) return;
                    const center = track.scrollLeft + track.clientWidth / 2;
                    let bestDom = 0, min = Infinity;
                    for (let i = 0; i < track.children.length; i++) {
                        const c = track.children[i];
                        const cCenter = c.offsetLeft + c.clientWidth / 2;
                        const d = Math.abs(cCenter - center);
                        if (d < min) { min = d; bestDom = i; }
                    }

                    let realIdx;
                    if (!this.isLooping) {
                        realIdx = bestDom;
                    } else if (bestDom === 0) {
                        // landed on clone-of-last → silently jump to real last
                        realIdx = this.total - 1;
                        this.scrollToDom(this.total, false);
                    } else if (bestDom === this.total + 1) {
                        // landed on clone-of-first → silently jump to real first
                        realIdx = 0;
                        this.scrollToDom(1, false);
                    } else {
                        realIdx = bestDom - 1;
                    }

                    if (realIdx !== this.active) {
                        this.active = realIdx;
                        this.hov = null;
                    }
                },
             }">
        {{-- SOP distribution — swipeable donut carousel.
             Note: no `x-data` on this card; it inherits from the section above. --}}
        <div class="pd-card">
            <header class="pd-card-head">
                <h3 class="pd-card-title">Distribusi SOP</h3>
                @if (!empty($sopDistribution['rows']))
                    <span class="pd-card-meta">
                        <span x-text="active + 1"></span>/<span x-text="total"></span> · {{ $sopDistribution['total'] }} proyek
                    </span>
                @endif
            </header>

            @if (empty($sopDistribution['rows']))
                <div class="pd-empty">Belum ada proyek yang terhubung ke SOP.</div>
            @else
                {{-- Donut-only carousel: active donut centered, adjacent donuts peek
                     as dimmed shadows on left/right. Legend and SOP name below
                     update reactively to whichever donut is active. --}}
                <div class="pd-sop-carousel-wrap">
                    @if ($totalSop > 1)
                        <button type="button" class="pd-sop-nav pd-sop-nav--prev"
                                @click="go(active - 1)"
                                aria-label="SOP sebelumnya">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                    @endif

                    <div class="pd-sop-carousel" x-ref="track" @scroll.debounce.150ms="syncFromScroll()">
                        @foreach ($loopRows as $row)
                            @php $realIdx = $row['__realIdx']; @endphp
                            <article class="pd-sop-slide"
                                     @if ($row['__isClone']) data-clone="true" @endif
                                     :class="{ 'is-active': active === {{ $realIdx }} }"
                                     @click="if (active !== {{ $realIdx }}) go({{ $realIdx }})">
                                <div class="pd-donut-wrap pd-sop-donut">
                                    <svg class="pd-donut" width="{{ $sopDonutSize }}" height="{{ $sopDonutSize }}" viewBox="0 0 {{ $sopDonutSize }} {{ $sopDonutSize }}">
                                        <circle cx="{{ $sopDonutSize / 2 }}" cy="{{ $sopDonutSize / 2 }}" r="{{ $sopDonutRadius }}"
                                                fill="none" stroke="#eef0f3" stroke-width="{{ $sopDonutStroke }}"/>
                                        @foreach ($row['donutSegments'] as $seg)
                                            <circle class="pd-donut-seg"
                                                    cx="{{ $sopDonutSize / 2 }}" cy="{{ $sopDonutSize / 2 }}" r="{{ $sopDonutRadius }}"
                                                    fill="none"
                                                    stroke="{{ $seg['color'] }}"
                                                    stroke-width="{{ $sopDonutStroke }}"
                                                    stroke-dasharray="{{ $seg['dash'] }} {{ $seg['gap'] }}"
                                                    stroke-dashoffset="{{ $seg['offset'] }}"
                                                    transform="rotate(-90 {{ $sopDonutSize / 2 }} {{ $sopDonutSize / 2 }})"
                                                    @mouseenter="if (active === {{ $realIdx }}) hov = {{ $seg['index'] }}"
                                                    @mouseleave="hov = null"
                                                    :class="{
                                                        'is-dim': active === {{ $realIdx }} && hov !== null && hov !== {{ $seg['index'] }},
                                                        'is-active': active === {{ $realIdx }} && hov === {{ $seg['index'] }}
                                                    }">
                                            </circle>
                                        @endforeach
                                    </svg>
                                    <div class="pd-donut-center">
                                        <template x-if="active !== {{ $realIdx }} || hov === null">
                                            <div>
                                                <div class="pd-donut-num">{{ $row['total'] }}</div>
                                                <div class="pd-donut-cap">proyek</div>
                                            </div>
                                        </template>
                                        @foreach ($row['donutSegments'] as $seg)
                                            <template x-if="active === {{ $realIdx }} && hov === {{ $seg['index'] }}">
                                                <div>
                                                    <div class="pd-donut-num" style="color: {{ $seg['color'] }};">{{ $seg['count'] }}</div>
                                                    <div class="pd-donut-cap" style="color: {{ $seg['color'] }};">{{ $seg['label'] }} · {{ $seg['segPercent'] }}%</div>
                                                </div>
                                            </template>
                                        @endforeach
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if ($totalSop > 1)
                        <button type="button" class="pd-sop-nav pd-sop-nav--next"
                                @click="go(active + 1)"
                                aria-label="SOP berikutnya">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    @endif
                </div>

                {{-- Active SOP name (single line below carousel). Server renders
                     first as active to avoid flash before Alpine boots. --}}
                <div class="pd-sop-name-wrap">
                    @foreach ($sopDistribution['rows'] as $i => $sop)
                        <h4 class="pd-sop-active-name {{ $i === 0 ? 'is-active' : '' }}"
                            :class="{ 'is-active': active === {{ $i }} }"
                            title="{{ $sop['name'] }}">{{ $sop['name'] }}</h4>
                    @endforeach
                </div>

                {{-- One legend per SOP, only the active one is displayed.
                     Sharing parent `hov` so legend hover dims donut slices too. --}}
                @foreach ($sopDistribution['rows'] as $i => $sop)
                    <ul class="pd-legend pd-sop-legend {{ $i === 0 ? 'is-active' : '' }}"
                        :class="{ 'is-active': active === {{ $i }} }">
                        @foreach ($sop['donutSegments'] as $seg)
                            <li class="pd-legend-item"
                                @mouseenter="hov = {{ $seg['index'] }}"
                                @mouseleave="hov = null"
                                :class="{ 'is-dim': hov !== null && hov !== {{ $seg['index'] }}, 'is-active': hov === {{ $seg['index'] }} }">
                                <span class="pd-legend-dot" style="background: {{ $seg['color'] }};"></span>
                                <span class="pd-legend-label">{{ $seg['label'] }}</span>
                                <span class="pd-legend-count">{{ $seg['count'] }}</span>
                                <span class="pd-legend-pct">{{ $seg['segPercent'] }}%</span>
                            </li>
                        @endforeach
                    </ul>
                @endforeach

                <div class="pd-sop-dots">
                    @foreach ($sopDistribution['rows'] as $i => $sop)
                        <button type="button" class="pd-sop-dot"
                                :class="{ 'is-active': active === {{ $i }} }"
                                @click="go({{ $i }})"
                                aria-label="Ke SOP {{ $i + 1 }}"></button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Sample projects — synced to the SOP carousel's `active` index.
             Renders one block per SOP, only the matching block is displayed.
             Server pre-flags index 0 as active to avoid flash before Alpine boots. --}}
        <div class="pd-card pd-sop-samples">
            <header class="pd-card-head">
                <h3 class="pd-card-title">Sampel Proyek</h3>
                @if ($totalSop > 0)
                    <span class="pd-card-meta">
                        SOP <span x-text="active + 1"></span>/<span x-text="total"></span>
                    </span>
                @endif
            </header>

            @if ($totalSop === 0)
                <div class="pd-empty">Belum ada SOP dengan proyek.</div>
            @else
                @foreach ($sopDistribution['rows'] as $i => $sop)
                    @php $samples = $sopSamples[$sop['id']] ?? collect(); @endphp
                    <div class="pd-sop-samples-block {{ $i === 0 ? 'is-active' : '' }}"
                         :class="{ 'is-active': active === {{ $i }} }">
                        <div class="pd-sop-samples-meta">
                            <span class="pd-sop-samples-name" title="{{ $sop['name'] }}">{{ $sop['name'] }}</span>
                            <span class="pd-sop-samples-count">{{ $samples->count() }} dari {{ $sop['total'] }}</span>
                        </div>

                        @if ($samples->isEmpty())
                            <div class="pd-empty">Tidak ada proyek pada SOP ini.</div>
                        @else
                            <ul class="pd-list">
                                @foreach ($samples as $project)
                                    @php
                                        $statusColor = $project->statusRecord?->color ?? '#94a3b8';
                                        $statusLabel = $project->statusRecord?->label ?? ucfirst($project->status);
                                        $dueRel = null;
                                        if ($project->due_date) {
                                            $daysDiff = today()->diffInDays($project->due_date, false);
                                            $dueRel = $daysDiff < 0
                                                ? abs($daysDiff) . 'h lewat'
                                                : ($daysDiff === 0 ? 'Hari ini' : ($daysDiff === 1 ? 'Besok' : "{$daysDiff} hari lagi"));
                                        }
                                    @endphp
                                    <li>
                                        <a href="{{ $this->projectUrl($project) }}" class="pd-list-row">
                                            <span class="pd-list-dot" style="background: {{ $statusColor }};"></span>
                                            <div class="pd-list-main">
                                                <div class="pd-list-name">{{ $project->name }}</div>
                                                <div class="pd-list-sub">{{ $project->client?->name ?? '—' }} · {{ $statusLabel }}</div>
                                            </div>
                                            <div class="pd-list-due">
                                                @if ($dueRel)
                                                    <span class="pd-list-due-rel {{ ($daysDiff ?? 1) < 0 ? 'pd-list-due-rel-late' : '' }}">{{ $dueRel }}</span>
                                                    <span class="pd-list-due-date">{{ $project->due_date->translatedFormat('j M') }}</span>
                                                @else
                                                    <span class="pd-list-due-rel pd-list-due-rel-muted">—</span>
                                                @endif
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            @if ($sop['total'] > $samples->count())
                                <a href="{{ $this->listUrl('group=sop') }}" class="pd-sop-samples-more">
                                    Lihat semua {{ $sop['total'] }} proyek →
                                </a>
                            @endif
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    </section>

    {{-- ============== ROW 4: Interactive completion trend ============== --}}
    <section class="pd-row">
        <div class="pd-card" x-data="{ hovered: null }">
            <header class="pd-card-head">
                <h3 class="pd-card-title">Tren Penyelesaian</h3>
                <span class="pd-card-meta">{{ $timeLabel }} · per {{ $trend['granularity'] === 'day' ? 'hari' : ($trend['granularity'] === 'week' ? 'minggu' : 'bulan') }}</span>
            </header>

            @php
                $chartW = 800;
                $chartH = 180;
                $padX = 32;
                $padY = 28;
                $innerW = $chartW - ($padX * 2);
                $innerH = $chartH - ($padY * 2);
                $n = count($trend['buckets']);
                $stepX = $n > 1 ? $innerW / ($n - 1) : 0;
                $points = [];
                if ($trend['max'] > 0) {
                    foreach ($trend['buckets'] as $i => $b) {
                        $x = $padX + ($stepX * $i);
                        $y = $padY + $innerH - (($b['count'] / $trend['max']) * $innerH);
                        $points[] = ['x' => round($x, 2), 'y' => round($y, 2), 'count' => $b['count'], 'label' => $b['label']];
                    }
                }
                $polyStr = collect($points)->map(fn ($p) => $p['x'].','.$p['y'])->implode(' ');
                $areaPath = empty($points) ? '' : 'M' . $points[0]['x'] . ',' . ($padY + $innerH) . ' L' . $polyStr . ' L' . end($points)['x'] . ',' . ($padY + $innerH) . ' Z';
            @endphp

            <div class="pd-chart-wrap">
                <svg class="pd-chart" viewBox="0 0 {{ $chartW }} {{ $chartH }}" preserveAspectRatio="none"
                     @mouseleave="hovered = null">
                    {{-- Gridlines (4 horizontal) --}}
                    @for ($i = 0; $i <= 3; $i++)
                        @php $y = $padY + ($innerH * $i / 3); @endphp
                        <line x1="{{ $padX }}" y1="{{ $y }}" x2="{{ $chartW - $padX }}" y2="{{ $y }}"
                              stroke="#eef0f3" stroke-width="1" stroke-dasharray="2 4"/>
                    @endfor

                    @if (!empty($points))
                        <path d="{{ $areaPath }}" fill="url(#pdTrendFill)" opacity="0.18"/>
                        <polyline points="{{ $polyStr }}" fill="none" stroke="#6366f1" stroke-width="2.2" stroke-linejoin="round" stroke-linecap="round"/>

                        {{-- Vertical guideline on hover --}}
                        @foreach ($points as $i => $p)
                            <line x1="{{ $p['x'] }}" y1="{{ $padY }}" x2="{{ $p['x'] }}" y2="{{ $padY + $innerH }}"
                                  stroke="#6366f1" stroke-width="1" stroke-dasharray="3 3" opacity="0"
                                  x-show="hovered === {{ $i }}" x-transition.opacity/>
                        @endforeach

                        {{-- Hover hit-areas (transparent wide rects) --}}
                        @foreach ($points as $i => $p)
                            @php
                                $hitW = $stepX > 0 ? $stepX : $innerW;
                                $hitX = $p['x'] - ($hitW / 2);
                            @endphp
                            <rect x="{{ $hitX }}" y="{{ $padY }}" width="{{ $hitW }}" height="{{ $innerH }}"
                                  fill="transparent"
                                  @mouseenter="hovered = {{ $i }}"
                                  style="cursor: crosshair;"/>
                        @endforeach

                        {{-- Points --}}
                        @foreach ($points as $i => $p)
                            <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="3.5" fill="#6366f1" stroke="white" stroke-width="2"
                                    :r="hovered === {{ $i }} ? 5.5 : 3.5"
                                    style="transition: r .12s;">
                                <title>{{ $p['label'] }}: {{ $p['count'] }} proyek</title>
                            </circle>
                        @endforeach
                    @endif

                    <defs>
                        <linearGradient id="pdTrendFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#6366f1"/>
                            <stop offset="100%" stop-color="#6366f1" stop-opacity="0"/>
                        </linearGradient>
                    </defs>

                    {{-- X-axis labels (skip if too many) --}}
                    @php
                        $every = $n <= 12 ? 1 : (int) ceil($n / 12);
                    @endphp
                    @foreach ($trend['buckets'] as $i => $b)
                        @if ($i % $every === 0 || $i === $n - 1)
                            @php $x = $padX + ($stepX * $i); @endphp
                            <text x="{{ $x }}" y="{{ $chartH - 6 }}" text-anchor="middle"
                                  fill="#94a3b8" font-size="10" font-family="Plus Jakarta Sans, system-ui, sans-serif" font-weight="500">
                                {{ $b['label'] }}
                            </text>
                        @endif
                    @endforeach

                    {{-- Hovered tooltip (positioned near point) --}}
                    @foreach ($points as $i => $p)
                        @php
                            $tooltipX = $p['x'];
                            $anchor = ($i < $n / 4) ? 'start' : ($i > 3 * $n / 4 ? 'end' : 'middle');
                            $shift = $anchor === 'start' ? 8 : ($anchor === 'end' ? -8 : 0);
                            $tooltipY = max($padY + 14, $p['y'] - 14);
                        @endphp
                        <g x-show="hovered === {{ $i }}" x-cloak>
                            <text x="{{ $p['x'] + $shift }}" y="{{ $tooltipY }}" text-anchor="{{ $anchor }}"
                                  fill="#0f172a" font-size="12" font-weight="700" font-family="Plus Jakarta Sans, system-ui, sans-serif">
                                {{ $p['count'] }}
                            </text>
                            <text x="{{ $p['x'] + $shift }}" y="{{ $tooltipY + 12 }}" text-anchor="{{ $anchor }}"
                                  fill="#64748b" font-size="10" font-family="Plus Jakarta Sans, system-ui, sans-serif">
                                {{ $p['label'] }}
                            </text>
                        </g>
                    @endforeach
                </svg>
            </div>

            <div class="pd-trend-summary">
                <div>
                    <span class="pd-trend-num">{{ $trend['total'] }}</span>
                    <span class="pd-trend-cap">total selesai · {{ $timeLabel }}</span>
                </div>
                @if (!empty($points))
                    @php $latest = end($trend['buckets']); @endphp
                    <div>
                        <span class="pd-trend-num">{{ $latest['count'] }}</span>
                        <span class="pd-trend-cap">{{ $trend['granularity'] === 'day' ? 'hari terakhir' : ($trend['granularity'] === 'week' ? 'minggu terakhir' : 'bulan terakhir') }} ({{ $latest['label'] }})</span>
                    </div>
                @endif
            </div>
        </div>
    </section>

    @once
        <style>
            [x-cloak] { display: none !important; }

            .pd-root {
                --pd-ink: #0f172a;
                --pd-muted: #64748b;
                --pd-subtle: #94a3b8;
                --pd-line: #eef0f3;
                --pd-line-strong: #d8dde3;
                --pd-bg: #ffffff;
                --pd-bg-soft: #f7f8fa;
                --pd-bg-hover: #f4f5f7;
                --pd-accent: #6366f1;
                --pd-accent-ink: #4f46e5;
                --pd-accent-soft: #eef2ff;
                --pd-danger: #dc2626;
                --pd-danger-soft: #fee2e2;
                font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
                color: var(--pd-ink);
                font-size: 13.5px;
                display: flex;
                flex-direction: column;
                gap: 18px;
            }

            /* Top bar */
            .pd-topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                flex-wrap: wrap;
            }
            .pd-topbar-meta {
                display: inline-flex; align-items: center; gap: 8px;
                font-size: 12.5px;
                color: var(--pd-muted);
                font-weight: 500;
            }
            .pd-topbar-meta-icon { color: var(--pd-subtle); }

            /* Time picker */
            .pd-time-picker { position: relative; }
            .pd-time-trigger {
                display: inline-flex; align-items: center; gap: 8px;
                height: 36px;
                padding: 0 12px 0 14px;
                background: var(--pd-bg);
                border: 0;
                border-radius: 9px;
                font: inherit; font-size: 13px; font-weight: 600;
                color: var(--pd-ink);
                cursor: pointer;
                box-shadow:
                    inset 0 0 0 1px var(--pd-line-strong),
                    0 1px 2px rgba(15, 23, 42, .04);
                transition: box-shadow .14s, transform .08s, color .12s;
            }
            .pd-time-trigger:hover {
                box-shadow:
                    inset 0 0 0 1px var(--pd-accent),
                    0 2px 6px rgba(99, 102, 241, .12);
                color: var(--pd-accent-ink);
            }
            .pd-time-trigger.is-open {
                box-shadow:
                    inset 0 0 0 1px var(--pd-accent),
                    0 0 0 3px rgba(99, 102, 241, .14);
                color: var(--pd-accent-ink);
            }
            .pd-time-trigger:active { transform: translateY(1px); }
            .pd-time-trigger > svg:first-child { color: var(--pd-accent); }
            .pd-time-caret { color: var(--pd-subtle); transition: transform .18s; }
            .pd-time-caret.is-flipped { transform: rotate(180deg); }

            .pd-time-menu {
                position: absolute; top: calc(100% + 6px); right: 0; z-index: 30;
                min-width: 200px;
                background: var(--pd-bg);
                border-radius: 12px;
                padding: 6px;
                box-shadow:
                    0 0 0 1px rgba(15, 23, 42, .04),
                    0 12px 32px rgba(15, 23, 42, .12);
            }
            .pd-time-menu-head {
                padding: 8px 10px 4px;
                font-size: 10.5px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .08em;
                color: var(--pd-subtle);
            }
            .pd-time-option {
                display: flex; align-items: center; justify-content: space-between;
                width: 100%;
                padding: 8px 10px;
                background: transparent;
                border: 0;
                border-radius: 8px;
                font: inherit; font-size: 12.5px; font-weight: 500;
                color: var(--pd-ink);
                cursor: pointer;
                text-align: left;
                transition: background .1s, color .1s;
            }
            .pd-time-option:hover { background: var(--pd-bg-hover); }
            .pd-time-option.is-active {
                background: var(--pd-accent-soft);
                color: var(--pd-accent-ink);
                font-weight: 600;
            }
            .pd-time-option.is-active svg { color: var(--pd-accent); }

            /* Rows / cards */
            .pd-row { display: block; }
            .pd-row-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
            /* Variant: don't stretch siblings — each card sizes to its own content.
               Used when one side has a scrollable, capped list (SOP distribution) so
               the unconstrained sibling doesn't drag the capped card taller and
               re-introduce dead whitespace. */
            .pd-row-2col--top { align-items: start; }
            @media (max-width: 900px) { .pd-row-2col { grid-template-columns: 1fr; } }
            .pd-card {
                background: var(--pd-bg);
                border: 1px solid var(--pd-line);
                border-radius: 12px;
                padding: 16px 18px 18px;
                /* Become a flex column so the inner bar list can distribute
                   itself across the available height. Grid stretches sibling
                   cards to match the tallest one — without this, shorter
                   cards leave dead whitespace at the bottom. */
                display: flex;
                flex-direction: column;
                /* CSS grid items default to min-width: auto (≈ intrinsic content
                   width). Without this, wide scrollable content like the SOP
                   carousel (10 slides × 220px) pushes the column past its 1fr
                   share and the card overflows its grid cell. */
                min-width: 0;
            }
            .pd-card-head {
                display: flex; align-items: center; justify-content: space-between;
                margin-bottom: 16px;
            }
            .pd-card-title { font-size: 13.5px; font-weight: 700; margin: 0; color: var(--pd-ink); letter-spacing: -.005em; }
            .pd-card-meta { font-size: 11.5px; color: var(--pd-muted); font-weight: 500; }
            .pd-card-meta-alert { color: var(--pd-danger); }
            .pd-empty { padding: 32px 8px; text-align: center; font-size: 12.5px; color: var(--pd-subtle); }

            /* Donut */
            .pd-donut-wrap {
                position: relative;
                display: flex; align-items: center; justify-content: center;
                margin: 4px 0 16px;
            }
            .pd-donut { display: block; cursor: default; }
            .pd-donut-seg {
                transition: opacity .15s, stroke-width .15s;
                cursor: pointer;
            }
            .pd-donut-seg.is-dim { opacity: .25; }
            .pd-donut-seg.is-active { stroke-width: 38; }
            .pd-donut-center {
                position: absolute; inset: 0;
                display: flex; flex-direction: column;
                align-items: center; justify-content: center;
                pointer-events: none;
                text-align: center;
            }
            .pd-donut-num {
                font-size: 38px; font-weight: 700;
                color: var(--pd-ink); letter-spacing: -.02em;
                transition: color .15s;
            }
            .pd-donut-cap {
                font-size: 11.5px; font-weight: 600;
                text-transform: uppercase; letter-spacing: .08em;
                color: var(--pd-subtle); margin-top: 4px;
                transition: color .15s;
            }

            /* Legend */
            .pd-legend { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 2px; }
            .pd-legend-item {
                display: grid;
                grid-template-columns: 12px 1fr auto auto;
                gap: 10px;
                align-items: center;
                font-size: 12px;
                padding: 5px 8px;
                margin: 0 -8px;
                border-radius: 6px;
                cursor: pointer;
                transition: background .12s, opacity .15s;
            }
            .pd-legend-item:hover { background: var(--pd-bg-hover); }
            .pd-legend-item.is-dim { opacity: .4; }
            .pd-legend-item.is-active { background: var(--pd-bg-hover); }
            .pd-legend-dot { width: 10px; height: 10px; border-radius: 3px; }
            .pd-legend-label { color: var(--pd-ink); font-weight: 500; }
            .pd-legend-count { color: var(--pd-ink); font-weight: 700; font-variant-numeric: tabular-nums; }
            .pd-legend-pct { color: var(--pd-muted); font-size: 11px; min-width: 36px; text-align: right; font-variant-numeric: tabular-nums; }

            /* Status legend above PIC bars */
            .pd-stack-legend {
                list-style: none; padding: 0; margin: 0 0 14px;
                display: flex; flex-wrap: wrap; gap: 6px 12px;
                padding-bottom: 12px;
                border-bottom: 1px solid var(--pd-line);
            }
            .pd-stack-legend-item {
                display: inline-flex; align-items: center; gap: 6px;
                font-size: 11.5px; font-weight: 500;
                color: var(--pd-ink);
                cursor: default;
                transition: opacity .15s;
            }
            .pd-stack-legend-item.is-dim { opacity: .3; }
            .pd-stack-legend-dot { width: 9px; height: 9px; border-radius: 3px; flex-shrink: 0; }
            .pd-stack-legend-label { color: var(--pd-muted); }

            /* PIC stacked bars */
            .pd-bars {
                list-style: none; padding: 0; margin: 0;
                display: flex; flex-direction: column; gap: 14px;
                /* Stretch to fill the card's remaining height (parent .pd-card is a
                   flex column) and distribute rows evenly so a short list doesn't
                   leave dead whitespace at the bottom when the sibling donut card
                   pulls the row taller. */
                flex: 1; min-height: 0;
                justify-content: space-around;
            }
            .pd-bar-row { display: flex; flex-direction: column; gap: 6px; }
            .pd-bar-info { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; }
            .pd-bar-name {
                font-size: 12.5px; font-weight: 500; color: var(--pd-ink);
                overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
                flex: 1; min-width: 0;
            }
            .pd-bar-count { font-size: 12px; font-weight: 700; color: var(--pd-ink); font-variant-numeric: tabular-nums; }
            .pd-bar-track {
                height: 16px;
                background: var(--pd-bg-soft);
                border-radius: 6px;
                overflow: hidden;
            }
            .pd-bar-stack {
                display: flex;
                height: 100%;
                border-radius: 6px;
                overflow: hidden;
                transition: width .35s ease;
            }
            .pd-bar-seg {
                position: relative;
                height: 100%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, .25);
                transition: opacity .15s;
                cursor: pointer;
                min-width: 0;
            }
            .pd-bar-seg.is-dim { opacity: .25; }
            .pd-bar-seg + .pd-bar-seg {
                box-shadow:
                    inset 0 1px 0 rgba(255, 255, 255, .25),
                    inset 1px 0 0 rgba(255, 255, 255, .35);
            }
            .pd-bar-seg-count {
                font-size: 10px;
                font-weight: 700;
                color: white;
                letter-spacing: .02em;
                line-height: 1;
                text-shadow: 0 1px 1px rgba(0, 0, 0, .15);
            }

            /* SOP carousel — same overall layout as Distribusi Status (donut +
               legend below), but the donut is a swipeable track with the
               previous/next SOPs peeking as dimmed shadows on the sides.
               The name + legend below update reactively to the active slide. */
            .pd-sop-carousel-wrap {
                position: relative;
                /* Bleed sideways into card padding so peeks have room to show */
                margin: 4px -18px 0;
            }
            .pd-sop-carousel {
                display: flex;
                gap: 0;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                scroll-behavior: smooth;
                /* Padding inline pulls first/last slide to viewport center.
                   220 / 2 = 110, so each side gets (viewportW - 220) / 2 of space. */
                padding: 8px calc(50% - 110px);
                scrollbar-width: none;
                -webkit-overflow-scrolling: touch;
                overscroll-behavior-x: contain;
            }
            .pd-sop-carousel::-webkit-scrollbar { display: none; }
            .pd-sop-slide {
                flex: 0 0 220px;
                scroll-snap-align: center;
                scroll-snap-stop: always;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0;
                cursor: pointer;
                user-select: none;
                transform: scale(.62);
                opacity: .28;
                transition: transform .35s cubic-bezier(.4, 0, .2, 1),
                            opacity .35s cubic-bezier(.4, 0, .2, 1);
            }
            .pd-sop-slide.is-active {
                transform: scale(1);
                opacity: 1;
                cursor: default;
            }
            .pd-sop-slide:not(.is-active):hover { opacity: .5; }
            /* Override the global pd-donut-seg cursor on non-active donuts so
               the whole slide reads as clickable, not the slice. */
            .pd-sop-slide:not(.is-active) .pd-donut-seg { cursor: pointer; pointer-events: none; }
            .pd-sop-donut { margin: 0; }

            /* Floating prev/next nav — vertically centered on the donut */
            .pd-sop-nav {
                position: absolute;
                top: 50%; transform: translateY(-50%);
                z-index: 2;
                width: 32px; height: 32px;
                border-radius: 50%;
                background: var(--pd-bg);
                border: 1px solid var(--pd-line);
                display: flex; align-items: center; justify-content: center;
                color: var(--pd-ink);
                cursor: pointer;
                box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
                transition: background .12s, opacity .15s, transform .12s;
                padding: 0;
            }
            .pd-sop-nav:hover:not(:disabled) {
                background: var(--pd-bg-hover);
                transform: translateY(-50%) scale(1.06);
            }
            .pd-sop-nav:disabled { opacity: .25; cursor: not-allowed; }
            .pd-sop-nav--prev { left: 6px; }
            .pd-sop-nav--next { right: 6px; }

            /* Active SOP name below the carousel — overlapping stack so the
               wrapper height stays constant as names change. */
            .pd-sop-name-wrap {
                position: relative;
                display: flex; align-items: center; justify-content: center;
                min-height: 22px;
                margin: 6px 0 14px;
            }
            .pd-sop-active-name {
                position: absolute;
                margin: 0;
                font-size: 14px;
                font-weight: 600;
                color: var(--pd-ink);
                text-align: center;
                letter-spacing: -.005em;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: calc(100% - 60px);
                opacity: 0;
                transition: opacity .25s ease;
                pointer-events: none;
            }
            .pd-sop-active-name.is-active { opacity: 1; pointer-events: auto; }

            /* Legend visibility — only the active SOP's legend renders.
               Render with display:none in CSS, server pre-flags first as active. */
            .pd-sop-legend { display: none; }
            .pd-sop-legend.is-active { display: flex; }

            /* Dot pagination */
            .pd-sop-dots {
                display: flex; justify-content: center; gap: 6px;
                margin-top: 14px;
                padding-top: 14px;
                border-top: 1px solid var(--pd-line);
                flex-wrap: wrap;
            }
            .pd-sop-dot {
                width: 6px; height: 6px;
                border-radius: 50%;
                background: var(--pd-line);
                border: 0; padding: 0;
                cursor: pointer;
                transition: background .15s, transform .2s;
            }
            .pd-sop-dot.is-active {
                background: var(--pd-accent);
                transform: scale(1.5);
            }
            .pd-sop-dot:hover:not(.is-active) { background: var(--pd-subtle); }

            /* Sample-projects card — one block per SOP, only the matching one shows.
               Tied to the section's shared `active` so swiping the carousel left
               cross-fades the list on the right. */
            .pd-sop-samples-block { display: none; }
            .pd-sop-samples-block.is-active {
                display: block;
                animation: pdSopSamplesFade .25s ease;
            }
            @keyframes pdSopSamplesFade {
                from { opacity: 0; transform: translateY(4px); }
                to   { opacity: 1; transform: translateY(0); }
            }
            .pd-sop-samples-meta {
                display: flex; align-items: baseline; justify-content: space-between;
                gap: 10px;
                padding-bottom: 10px;
                margin-bottom: 6px;
                border-bottom: 1px solid var(--pd-line);
            }
            .pd-sop-samples-name {
                font-size: 13px; font-weight: 600;
                color: var(--pd-ink);
                white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
                flex: 1; min-width: 0;
            }
            .pd-sop-samples-count {
                font-size: 11.5px; font-weight: 500;
                color: var(--pd-muted);
                font-variant-numeric: tabular-nums;
                white-space: nowrap;
            }
            .pd-sop-samples-more {
                display: block;
                padding: 10px 8px 4px;
                font-size: 12px; font-weight: 600;
                color: var(--pd-accent-ink);
                text-decoration: none;
                text-align: center;
                transition: color .12s;
            }
            .pd-sop-samples-more:hover { color: var(--pd-accent); }
            .pd-list-due-rel-muted { color: var(--pd-subtle); }

            /* List */
            .pd-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 2px; }
            .pd-list-row {
                display: grid;
                grid-template-columns: 8px 1fr auto;
                gap: 12px;
                align-items: center;
                padding: 9px 10px;
                border-radius: 8px;
                text-decoration: none;
                color: inherit;
                transition: background .12s;
            }
            .pd-list-row:hover { background: var(--pd-bg-hover); }
            .pd-list-dot { width: 8px; height: 8px; border-radius: 50%; box-shadow: 0 0 0 2px rgba(255, 255, 255, .8); }
            .pd-list-main { min-width: 0; }
            .pd-list-name {
                font-size: 13px; font-weight: 600; color: var(--pd-ink);
                overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
            }
            .pd-list-sub {
                font-size: 11.5px; color: var(--pd-muted); font-weight: 500; margin-top: 1px;
                overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
            }
            .pd-list-due { text-align: right; min-width: 90px; }
            .pd-list-due-rel { display: block; font-size: 11.5px; font-weight: 600; color: var(--pd-muted); }
            .pd-list-due-rel-late { color: var(--pd-danger); }
            .pd-list-due-date {
                display: block; font-size: 10.5px; color: var(--pd-subtle);
                margin-top: 1px; font-variant-numeric: tabular-nums;
            }

            /* Trend chart */
            .pd-chart-wrap { width: 100%; height: 180px; }
            .pd-chart { width: 100%; height: 100%; display: block; }
            .pd-trend-summary {
                display: flex; gap: 24px; margin-top: 14px;
                padding-top: 14px;
                border-top: 1px solid var(--pd-line);
            }
            .pd-trend-num {
                display: inline-block;
                font-size: 18px; font-weight: 700; color: var(--pd-ink);
                font-variant-numeric: tabular-nums;
            }
            .pd-trend-cap {
                display: inline-block; margin-left: 6px;
                font-size: 12px; color: var(--pd-muted); font-weight: 500;
            }
        </style>
    @endonce
</div>
