@php
    $npwp       = $this->getFormattedNpwp();
    $statusKey  = $client->status ?? null;
    $pkpKey     = $client->pkp_status ?? null;
    $isBadan    = $client->client_type === 'Badan';
    $logoUrl    = $this->getLogoUrl();

    // Account Representative
    $arName     = $client->accountRepresentative?->name ?? $client->account_representative;
    $arPhone    = $client->accountRepresentative?->phone_number ?? $client->ar_phone_number;
    $arEmail    = $client->accountRepresentative?->email;
    $arKpp      = $client->accountRepresentative?->KPP ?? $client->accountRepresentative?->kpp;
    $arWhatsApp = $this->whatsAppUrl($arPhone);

    // PIC
    $pic        = $client->pic;

    // Kontak person utama (telepon/HP hidup di relasi contacts, bukan di tabel clients)
    $contact    = $this->primaryContact();
    $cName      = $contact?->name;
    $cPos       = $contact?->position;
    $cPhone     = $contact?->phone;
    $cMobile    = $contact?->mobile;
    $cWa        = $this->whatsAppUrl($cMobile ?: $cPhone);

    // ── Laporan kelengkapan ──────────────────────────────────────────
    $comp  = $this->completeness();
    $g     = $comp['groups'];

    // Geometri ring donat
    $r      = 52;
    $circ   = 2 * M_PI * $r;
    $offset = $circ * (1 - $comp['pct'] / 100);

    // Palet tier — tertahan, bukan traffic-light norak
    $tierHex = fn (int $p) => $p >= 80 ? '#10b981' : ($p >= 50 ? '#f59e0b' : '#f43f5e');
    $tierTxt = fn (int $p) => $p >= 80 ? 'Lengkap' : ($p >= 50 ? 'Perlu Dilengkapi' : 'Belum Lengkap');
    $ringHex = $tierHex($comp['pct']);
@endphp

<div class="space-y-5" wire:key="identitas-{{ $client->id }}">
    <style>
        @keyframes cmFlash {
            0%, 100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0); }
            25%      { box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.40); }
        }
        .cm-flash { animation: cmFlash 1.1s ease-out; }
    </style>

    {{-- ─────────────────  HERO  ───────────────── --}}
    <section class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        {{-- aksen gradien tipis di header --}}
        <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-indigo-50/70 to-transparent dark:from-indigo-500/5" aria-hidden="true"></div>

        <div class="relative flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between sm:p-7">
            <div class="flex min-w-0 items-center gap-4">
                <div class="shrink-0" x-data="{ failed: false }">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo {{ $client->name }}"
                             x-show="!failed" x-on:error="failed = true"
                             class="h-16 w-16 rounded-2xl object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                        <div x-show="failed" x-cloak
                             class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-900 text-lg font-semibold tracking-wide text-white shadow-sm dark:bg-gray-100 dark:text-gray-900">
                            {{ $this->getInitials() }}
                        </div>
                    @else
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-900 text-lg font-semibold tracking-wide text-white shadow-sm dark:bg-gray-100 dark:text-gray-900" aria-hidden="true">
                            {{ $this->getInitials() }}
                        </div>
                    @endif
                </div>

                <div class="min-w-0">
                    <h2 class="truncate text-xl font-semibold leading-tight text-gray-900 dark:text-white sm:text-2xl">
                        {{ $client->name ?? 'Tanpa Nama' }}
                    </h2>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        @if ($statusKey === 'Active')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/30">
                                <span class="h-1.5 w-1.5 rounded-full bg-green-500" aria-hidden="true"></span> Aktif
                            </span>
                        @elseif ($statusKey === 'Inactive')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-300 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-gray-400" aria-hidden="true"></span> Tidak Aktif
                            </span>
                        @endif

                        @if ($pkpKey === 'PKP')
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-600/20 dark:bg-indigo-500/10 dark:text-indigo-400 dark:ring-indigo-500/30">PKP</span>
                        @elseif ($pkpKey === 'Non-PKP')
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-300 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">Non-PKP</span>
                        @endif

                        @if ($client->client_type)
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $client->client_type }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <a href="{{ $this->getEditUrl() }}"
               class="inline-flex shrink-0 items-center gap-1.5 self-start rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:bg-gray-800 sm:self-center">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit Profil
            </a>
        </div>
    </section>

    {{-- ─────────────────  KELENGKAPAN DATA  ───────────────── --}}
    <section
        x-data="{
            pct: {{ $comp['pct'] }},
            shown: 0,
            off: {{ $circ }},
            mounted: false,
            init() {
                this.$nextTick(() => {
                    this.off = {{ $offset }};
                    this.mounted = true;
                    const start = performance.now();
                    const dur = 900;
                    const tick = (now) => {
                        const t = Math.min((now - start) / dur, 1);
                        const e = 1 - Math.pow(1 - t, 3);
                        this.shown = Math.round(this.pct * e);
                        if (t < 1) requestAnimationFrame(tick);
                    };
                    requestAnimationFrame(tick);
                });
            },
            flash(id) {
                const el = document.getElementById(id);
                if (!el) return;
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.classList.remove('cm-flash');
                void el.offsetWidth;
                el.classList.add('cm-flash');
                setTimeout(() => el.classList.remove('cm-flash'), 1200);
            },
        }"
        class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-7">

        {{-- glow tipis sesuai tier --}}
        <div class="pointer-events-none absolute -left-16 -top-16 h-48 w-48 rounded-full blur-3xl" style="background: {{ $ringHex }}1f;" aria-hidden="true"></div>

        <header class="relative mb-6 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" style="background: {{ $ringHex }}1a; color: {{ $ringHex }};">
                    <x-client-management.field-icon name="chart" style="color: currentColor; width: 18px; height: 18px;" />
                </span>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Kelengkapan Data</h3>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Seberapa lengkap profil klien ini terisi.</p>
                </div>
            </div>
            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold tabular-nums" style="color: {{ $ringHex }}; background: {{ $ringHex }}1a;">
                {{ $comp['filled'] }}/{{ $comp['total'] }} field
            </span>
        </header>

        <div class="relative flex flex-col items-center gap-7 sm:flex-row sm:gap-9">
            {{-- Ring donat --}}
            <div class="relative shrink-0" style="width: 8.5rem; height: 8.5rem;">
                <svg viewBox="0 0 120 120" class="h-full w-full -rotate-90">
                    <circle cx="60" cy="60" r="{{ $r }}" fill="none" stroke-width="9" class="stroke-gray-100 dark:stroke-gray-800" />
                    <circle cx="60" cy="60" r="{{ $r }}" fill="none" stroke-width="9" stroke-linecap="round"
                            stroke="{{ $ringHex }}" stroke-dasharray="{{ $circ }}"
                            :stroke-dashoffset="off"
                            style="transition: stroke-dashoffset 0.9s cubic-bezier(0.4, 0, 0.2, 1);" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-bold tabular-nums text-gray-900 dark:text-white" x-text="shown + '%'">{{ $comp['pct'] }}%</span>
                    <span class="mt-0.5 text-[11px] font-semibold uppercase tracking-wide" style="color: {{ $ringHex }};">{{ $tierTxt($comp['pct']) }}</span>
                </div>
            </div>

            {{-- Breakdown per-grup — klik untuk loncat ke section --}}
            <div class="w-full flex-1 space-y-1">
                @foreach ($g as $key => $grp)
                    @php $barHex = $tierHex($grp['pct']); @endphp
                    <button type="button" @click="flash('sec-{{ $key }}')"
                            class="group flex w-full items-center gap-3 rounded-lg px-2 py-2 text-left transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:hover:bg-gray-800/60">
                        <span class="w-32 shrink-0 truncate text-sm font-medium text-gray-700 dark:text-gray-300">{{ $grp['label'] }}</span>
                        <span class="hidden h-1.5 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800 sm:block">
                            <span class="block h-full rounded-full transition-all duration-700 ease-out"
                                  :style="{ width: (mounted ? {{ $grp['pct'] }} : 0) + '%', background: '{{ $barHex }}' }"></span>
                        </span>
                        <span class="ml-auto shrink-0 tabular-nums text-xs font-medium text-gray-500 dark:text-gray-400 sm:ml-0 sm:w-9 sm:text-right">{{ $grp['filled'] }}/{{ $grp['total'] }}</span>
                        <svg class="h-4 w-4 shrink-0 text-gray-300 opacity-0 transition group-hover:translate-x-0.5 group-hover:opacity-100 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </button>
                @endforeach
            </div>
        </div>

        @if (count($comp['missing']))
            <div class="relative mt-6 flex flex-wrap items-center gap-1.5 border-t border-gray-100 pt-5 dark:border-gray-800">
                <span class="mr-1 text-xs font-medium text-gray-500 dark:text-gray-400">Belum diisi:</span>
                @foreach ($comp['missing'] as $m)
                    <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/30">
                        <span class="h-1 w-1 rounded-full bg-amber-500" aria-hidden="true"></span> {{ $m }}
                    </span>
                @endforeach
            </div>
        @endif
    </section>

    {{-- ─────────────────  IDENTITAS LEGAL  ───────────────── --}}
    <x-cm-section id="sec-identitas" icon="building" color="indigo" title="Identitas Legal"
                  subtitle="Nama, bentuk usaha, dan logo perusahaan."
                  :filled="$g['identitas']['filled']" :total="$g['identitas']['total']">
        <dl class="grid grid-cols-1 gap-x-10 sm:grid-cols-2">
            <x-cm-flat-field icon="building"  label="Nama Legal" :value="$client->name" />
            <x-cm-flat-field icon="briefcase" label="Tipe Klien" :value="$client->client_type" />
            @if ($isBadan)
                <x-cm-flat-field icon="tag" label="Bentuk Badan" :value="$client->client_subtype" />
            @endif
            <x-cm-flat-field icon="calendar" label="Terdaftar" :value="$client->created_at?->translatedFormat('j M Y')" />

            <div class="flex items-start gap-2.5 py-2.5">
                <span class="mt-px shrink-0 text-gray-400 dark:text-gray-500" aria-hidden="true">
                    <x-client-management.field-icon name="document" class="h-4 w-4" />
                </span>
                <div class="min-w-0 flex-1">
                    <dt class="text-xs font-medium text-gray-400 dark:text-gray-500">Logo</dt>
                    <dd class="mt-1">
                        @if ($logoUrl)
                            <img src="{{ $logoUrl }}" alt="Logo {{ $client->name }}" class="h-10 w-10 rounded-lg object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                        @else
                            <span class="text-sm italic text-gray-400 dark:text-gray-600">Belum diunggah</span>
                        @endif
                    </dd>
                </div>
            </div>
        </dl>
    </x-cm-section>

    {{-- ─────────────────  PERPAJAKAN  ───────────────── --}}
    <x-cm-section id="sec-pajak" icon="receipt" color="emerald" title="Perpajakan"
                  subtitle="Identitas pajak: NPWP, EFIN, dan status PKP."
                  :filled="$g['pajak']['filled']" :total="$g['pajak']['total']">
        <dl class="grid grid-cols-1 gap-x-10 sm:grid-cols-2">
            <x-cm-flat-field icon="receipt" label="NPWP" :value="$npwp" mono copyable />
            <x-cm-flat-field icon="hash" label="EFIN" :value="$client->EFIN" mono optional />

            <div class="flex items-start gap-2.5 py-2.5">
                <span class="mt-px shrink-0 text-gray-400 dark:text-gray-500" aria-hidden="true">
                    <x-client-management.field-icon name="percent" class="h-4 w-4" />
                </span>
                <div class="min-w-0 flex-1">
                    <dt class="text-xs font-medium text-gray-400 dark:text-gray-500">Status PKP</dt>
                    <dd class="mt-1">
                        @if ($pkpKey === 'PKP')
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-600/20 dark:bg-indigo-500/10 dark:text-indigo-400 dark:ring-indigo-500/30">PKP — {{ $this->getPkpStatusLabel() }}</span>
                        @elseif ($pkpKey === 'Non-PKP')
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-300 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">Non-PKP</span>
                        @else
                            <span class="text-sm italic text-gray-400 dark:text-gray-600">Belum ditentukan</span>
                        @endif
                    </dd>
                </div>
            </div>
        </dl>
    </x-cm-section>

    {{-- ─────────────────  KONTAK & ALAMAT  ───────────────── --}}
    <x-cm-section id="sec-kontak" icon="mail" color="sky" title="Kontak & Alamat"
                  subtitle="Kanal komunikasi resmi dan domisili perusahaan."
                  :filled="$g['kontak']['filled']" :total="$g['kontak']['total']">
        <div class="grid grid-cols-1 gap-x-10 gap-y-2 sm:grid-cols-2">
            <div>
                @if ($cName)
                    <div class="flex items-center gap-2 py-2.5">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400" aria-hidden="true">
                            <x-client-management.field-icon name="user" class="h-3.5 w-3.5" />
                        </span>
                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $cName }}</div>
                            @if ($cPos)<div class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $cPos }}</div>@endif
                        </div>
                    </div>
                @endif

                <x-cm-flat-field icon="mail"   label="Email"         :value="$client->email" :href="$client->email ? 'mailto:'.$client->email : null" />
                <x-cm-flat-field icon="phone"  label="Telepon"       :value="$cPhone"  :href="$cPhone ? 'tel:'.$cPhone : null" />
                <x-cm-flat-field icon="device" label="HP / WhatsApp" :value="$cMobile" :href="$cWa" :ext="(bool) $cWa" />
            </div>

            <div class="py-2.5">
                <div class="flex items-center gap-1.5 text-xs font-medium text-gray-400 dark:text-gray-500">
                    <x-client-management.field-icon name="map" class="h-4 w-4" />
                    Alamat Domisili
                </div>
                @if ($client->adress)
                    <address class="mt-1.5 text-sm not-italic leading-relaxed text-gray-800 dark:text-gray-200">{{ $client->adress }}</address>
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($client->adress) }}"
                       target="_blank" rel="noopener"
                       class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-indigo-600 transition hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                        Buka di Google Maps
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M7 17L17 7M7 7h10v10"/>
                        </svg>
                    </a>
                @else
                    <p class="mt-1.5 text-sm italic text-gray-400 dark:text-gray-600">Belum diisi.</p>
                @endif
            </div>
        </div>
    </x-cm-section>

    {{-- ─────────────────  PENANGGUNG JAWAB  ───────────────── --}}
    <x-cm-section id="sec-pj" icon="user" color="amber" title="Penanggung Jawab"
                  subtitle="PIC internal dan Account Representative pajak."
                  :filled="$g['pj']['filled']" :total="$g['pj']['total']">
        <div class="grid grid-cols-1 gap-x-10 gap-y-5 sm:grid-cols-2">
            @if ($isBadan)
                <div>
                    <div class="mb-2 text-xs font-medium text-gray-400 dark:text-gray-500">Person In Charge</div>
                    @if ($pic)
                        <div class="flex items-center gap-3 rounded-xl bg-gray-50 p-3 dark:bg-gray-800/50">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-cyan-50 text-sm font-semibold uppercase text-cyan-700 dark:bg-cyan-500/10 dark:text-cyan-400">{{ mb_strtoupper(mb_substr($pic->name, 0, 1)) }}</div>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $pic->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">PIC internal Kisantra</div>
                            </div>
                        </div>
                    @else
                        <p class="text-sm italic text-gray-400 dark:text-gray-600">Belum ada PIC ditugaskan.</p>
                    @endif
                </div>
            @endif

            <div>
                <div class="mb-2 text-xs font-medium text-gray-400 dark:text-gray-500">Account Representative</div>
                @if (! $arName && ! $arPhone && ! $arEmail && ! $arKpp)
                    <p class="text-sm italic text-gray-400 dark:text-gray-600">Belum ada AR ditugaskan.</p>
                @else
                    <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-800/50">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-sm font-semibold uppercase text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400">{{ $arName ? mb_strtoupper(mb_substr($arName, 0, 1)) : 'A' }}</div>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $arName ?: 'Belum ditentukan' }}</div>
                                @if ($arKpp)<div class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">{{ $arKpp }}</div>@endif

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    @if ($arPhone)
                                        <a href="tel:{{ $arPhone }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
                                            Telepon
                                        </a>
                                    @endif
                                    @if ($arWhatsApp)
                                        <a href="{{ $arWhatsApp }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-2.5 py-1 text-xs font-medium text-white shadow-sm transition hover:bg-emerald-600">
                                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.5 14.4c-.3-.2-1.8-.9-2-1s-.5-.2-.7.2-.8 1-.9 1.2-.3.2-.6 0c-1-.5-1.7-.9-2.4-2-.2-.3.2-.3.5-1 .1-.2 0-.3 0-.5s-.7-1.7-1-2.3c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4s-1 1-1 2.4 1 2.8 1.2 3 2.1 3.2 5 4.4c.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.8-.7 2-1.4.3-.7.3-1.3.2-1.4-.1-.1-.3-.2-.6-.3M12 2C6.5 2 2 6.5 2 12c0 1.7.5 3.4 1.3 4.9L2 22l5.3-1.3c1.4.8 3 1.2 4.7 1.2 5.5 0 10-4.5 10-10S17.5 2 12 2"/></svg>
                                            WhatsApp
                                        </a>
                                    @endif
                                    @if ($arEmail)
                                        <a href="mailto:{{ $arEmail }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                            Email
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </x-cm-section>
</div>
