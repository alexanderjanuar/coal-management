@php
    use Illuminate\Support\Str;

    $npwp        = $this->getFormattedNpwp();
    $statusKey   = $client->status ?? null;
    $pkpKey      = $client->pkp_status ?? null;
    $arPhone     = $client->accountRepresentative?->phone_number ?? $client->ar_phone_number;
    $arName      = $client->accountRepresentative?->name ?? $client->account_representative;
    $arEmail     = $client->accountRepresentative?->email;
    $arKpp       = $client->accountRepresentative?->KPP ?? $client->accountRepresentative?->kpp;
    $arWhatsApp  = $this->whatsAppUrl($arPhone);

    // Per-section completeness for the small "X/Y terisi" header chip.
    $profil = $this->completeness([
        $client->name, $client->name /* brand */, $client->client_type,
        null /* bidang_usaha */, $client->created_at,
    ]);
    $kontak = $this->completeness([
        $client->email, null /* telp_kantor */, null /* telp_mobile */,
        null /* website */, $client->adress,
    ]);
    $arBox = $this->completeness([$arName, $arPhone, $arEmail, $arKpp]);
@endphp

<div class="space-y-5" wire:key="identitas-{{ $client->id }}">

    {{-- ─────────────────  HERO SUMMARY  ───────────────── --}}
    <section class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 sm:p-8">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex flex-1 items-start gap-4 sm:gap-5">
                {{-- Monogram avatar — keeps the hero recognizable at a glance --}}
                <div
                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-gray-900 text-base font-semibold tracking-wide text-white dark:bg-gray-100 dark:text-gray-900 sm:h-16 sm:w-16 sm:text-lg"
                    aria-hidden="true">
                    {{ $this->getInitials() }}
                </div>

                <div class="min-w-0 flex-1">
                    <h2 class="truncate text-xl font-semibold leading-tight text-gray-900 dark:text-white sm:text-2xl">
                        {{ $client->name ?? 'Tanpa Nama' }}
                    </h2>

                    {{-- Status pills row — instant identity --}}
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        @if ($statusKey === 'Active')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/30">
                                <span class="h-1.5 w-1.5 rounded-full bg-green-500" aria-hidden="true"></span>
                                Aktif
                            </span>
                        @elseif ($statusKey === 'Inactive')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-300 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-gray-400" aria-hidden="true"></span>
                                Tidak Aktif
                            </span>
                        @endif

                        @if ($pkpKey === 'PKP')
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-600/20 dark:bg-indigo-500/10 dark:text-indigo-400 dark:ring-indigo-500/30">
                                PKP
                            </span>
                        @elseif ($pkpKey === 'Non-PKP')
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-300 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">
                                Non-PKP
                            </span>
                        @endif

                        @if ($client->client_type)
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                {{ $client->client_type }}
                            </span>
                        @endif
                    </div>

                    {{-- Key facts row: NPWP + Tanggal Berdiri --}}
                    <dl class="mt-5 grid grid-cols-1 gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 shrink-0">
                                NPWP
                            </dt>
                            <dd class="min-w-0 flex-1 truncate font-mono text-sm text-gray-900 dark:text-gray-100">
                                {{ $npwp ?? '—' }}
                            </dd>
                            @if ($npwp)
                                <button
                                    type="button"
                                    x-data="{ copied: false }"
                                    @click="navigator.clipboard.writeText('{{ $npwp }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                    class="shrink-0 rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                    aria-label="Salin nomor NPWP">
                                    <template x-if="!copied">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                            <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                                        </svg>
                                    </template>
                                    <template x-if="copied">
                                        <svg class="h-3.5 w-3.5 text-green-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                    </template>
                                </button>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 shrink-0">
                                Berdiri
                            </dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $client->created_at?->translatedFormat('j M Y') ?? '—' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Edit affordance --}}
            <a
                href="{{ $this->getEditUrl() }}"
                class="inline-flex shrink-0 items-center gap-1.5 self-start rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:bg-gray-800">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit Profil
            </a>
        </div>
    </section>

    {{-- ─────────────────  PROFIL PERUSAHAAN  ───────────────── --}}
    <section class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <header class="mb-5 flex items-baseline justify-between gap-3">
            <div class="min-w-0">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Profil Perusahaan</h3>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Identitas legal dan bentuk usaha.</p>
            </div>
            <span class="shrink-0 text-xs font-medium text-gray-400 dark:text-gray-500 tabular-nums">
                {{ $profil['filled'] }}/{{ $profil['total'] }} terisi
            </span>
        </header>

        <dl class="grid grid-cols-1 divide-y divide-gray-100 dark:divide-gray-800 sm:grid-cols-2 sm:divide-y-0 sm:gap-x-8">
            <x-cm-field label="Nama Legal" :value="$client->name" />
            <x-cm-field label="Nama Brand" :value="$client->name" />
            <x-cm-field label="Bentuk Usaha" :value="$client->client_type" />
            <x-cm-field label="Bidang Usaha" :value="null" />
            <x-cm-field label="Tanggal Berdiri" :value="$client->created_at?->translatedFormat('j M Y')" />
        </dl>
    </section>

    {{-- ─────────────────  KONTAK & ALAMAT  ───────────────── --}}
    <section class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <header class="mb-5 flex items-baseline justify-between gap-3">
            <div class="min-w-0">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Kontak &amp; Alamat</h3>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Kanal komunikasi resmi dan domisili perusahaan.</p>
            </div>
            <span class="shrink-0 text-xs font-medium text-gray-400 dark:text-gray-500 tabular-nums">
                {{ $kontak['filled'] }}/{{ $kontak['total'] }} terisi
            </span>
        </header>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 lg:gap-10">
            {{-- Kontak — clickable rows --}}
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @php
                    $contacts = [
                        ['icon' => 'mail',  'label' => 'Email',         'value' => $client->email, 'href' => $client->email ? "mailto:{$client->email}" : null],
                        ['icon' => 'phone', 'label' => 'Telepon Kantor','value' => null,           'href' => null],
                        ['icon' => 'device','label' => 'Telepon Mobile','value' => null,           'href' => null],
                        ['icon' => 'globe', 'label' => 'Website',       'value' => null,           'href' => null],
                    ];
                @endphp
                @foreach ($contacts as $c)
                    <li class="py-3 first:pt-0 last:pb-0">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400" aria-hidden="true">
                                <x-client-management.field-icon :name="$c['icon']" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $c['label'] }}</div>
                                @if ($c['value'])
                                    @if ($c['href'])
                                        <a href="{{ $c['href'] }}" class="block truncate text-sm font-medium text-gray-900 underline-offset-2 transition hover:text-indigo-600 hover:underline focus:outline-none focus-visible:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                            {{ $c['value'] }}
                                        </a>
                                    @else
                                        <div class="truncate text-sm text-gray-900 dark:text-gray-100">{{ $c['value'] }}</div>
                                    @endif
                                @else
                                    <div class="text-sm italic text-gray-400 dark:text-gray-600">Belum diisi</div>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            {{-- Alamat block --}}
            <div class="flex flex-col gap-3 rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    Alamat Domisili
                </div>
                @if ($client->adress)
                    <address class="text-sm not-italic leading-relaxed text-gray-800 dark:text-gray-200">
                        {{ $client->adress }}
                    </address>
                    <a
                        href="https://www.google.com/maps/search/?api=1&query={{ urlencode($client->adress) }}"
                        target="_blank" rel="noopener"
                        class="mt-auto inline-flex w-fit items-center gap-1 text-xs font-medium text-indigo-600 transition hover:text-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                        Buka di Google Maps
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M7 17L17 7M7 7h10v10"/>
                        </svg>
                    </a>
                @else
                    <p class="text-sm italic text-gray-400 dark:text-gray-600">Belum diisi.</p>
                @endif
            </div>
        </div>
    </section>

    {{-- ─────────────────  ACCOUNT REPRESENTATIVE  ───────────────── --}}
    <section class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <header class="mb-5 flex items-baseline justify-between gap-3">
            <div class="min-w-0">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Account Representative</h3>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Kontak AR dan KPP terkait.</p>
            </div>
            <span class="shrink-0 text-xs font-medium text-gray-400 dark:text-gray-500 tabular-nums">
                {{ $arBox['filled'] }}/{{ $arBox['total'] }} terisi
            </span>
        </header>

        @if (! $arName && ! $arPhone && ! $arEmail && ! $arKpp)
            <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-6 text-center dark:border-gray-700 dark:bg-gray-800/40">
                <p class="text-sm italic text-gray-500 dark:text-gray-400">
                    Belum ada Account Representative yang ditugaskan.
                </p>
            </div>
        @else
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-5">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-sm font-semibold uppercase text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400">
                    {{ $arName ? mb_strtoupper(mb_substr($arName, 0, 1)) : 'A' }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-base font-semibold text-gray-900 dark:text-white">
                        {{ $arName ?: 'Belum ditentukan' }}
                    </div>
                    @if ($arKpp)
                        <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            {{ $arKpp }}
                        </div>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if ($arPhone)
                        <a href="tel:{{ $arPhone }}"
                           class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                            </svg>
                            Telepon
                        </a>
                    @endif
                    @if ($arWhatsApp)
                        <a href="{{ $arWhatsApp }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-emerald-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-1">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M17.5 14.4c-.3-.2-1.8-.9-2-1s-.5-.2-.7.2-.8 1-.9 1.2-.3.2-.6 0c-1-.5-1.7-.9-2.4-2-.2-.3.2-.3.5-1 .1-.2 0-.3 0-.5s-.7-1.7-1-2.3c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4s-1 1-1 2.4 1 2.8 1.2 3 2.1 3.2 5 4.4c.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.8-.7 2-1.4.3-.7.3-1.3.2-1.4-.1-.1-.3-.2-.6-.3M12 2C6.5 2 2 6.5 2 12c0 1.7.5 3.4 1.3 4.9L2 22l5.3-1.3c1.4.8 3 1.2 4.7 1.2 5.5 0 10-4.5 10-10S17.5 2 12 2"/>
                            </svg>
                            WhatsApp
                        </a>
                    @endif
                    @if ($arEmail)
                        <a href="mailto:{{ $arEmail }}"
                           class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            Email
                        </a>
                    @endif
                </div>
            </div>

            @if ($arPhone || $arEmail)
                <dl class="mt-5 grid grid-cols-1 gap-x-8 gap-y-3 border-t border-gray-100 pt-5 text-sm sm:grid-cols-2 dark:border-gray-800">
                    @if ($arPhone)
                        <div class="flex items-center gap-2 min-w-0">
                            <dt class="shrink-0 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Telepon</dt>
                            <dd class="min-w-0 flex-1 truncate font-mono text-sm text-gray-900 dark:text-gray-100">{{ $arPhone }}</dd>
                        </div>
                    @endif
                    @if ($arEmail)
                        <div class="flex items-center gap-2 min-w-0">
                            <dt class="shrink-0 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Email</dt>
                            <dd class="min-w-0 flex-1 truncate text-sm text-gray-900 dark:text-gray-100">{{ $arEmail }}</dd>
                        </div>
                    @endif
                </dl>
            @endif
        @endif
    </section>
</div>
