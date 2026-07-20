@php
    $toneColor = fn (string $tone) => match ($tone) {
        'overdue' => 'var(--tp-overdue)',
        'due' => 'var(--tp-due)',
        default => 'var(--tp-text-muted)',
    };

    $toneDot = fn (string $tone) => match ($tone) {
        'overdue' => 'var(--tp-overdue)',
        'due' => 'var(--tp-due)',
        default => 'var(--tp-mark)',
    };

@endphp

<section class="tp-panel" aria-labelledby="tp-spine-heading">

    <div class="tp-panel-header flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-6 py-3.5">
        <h2 id="tp-spine-heading" class="tp-panel-title">
            Tenggat {{ $service->monthName($anchor) }} {{ $anchor->year }}
        </h2>

        <p style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);">
            @if ($totalOutstanding === 0)
                Tidak ada tunggakan untuk periode {{ $service->periodLabel($periodDate) }}
            @else
                Menagih periode {{ $service->periodLabel($periodDate) }}
            @endif
        </p>
    </div>

    {{--
        Garis waktu bulan tenggat. Menjawab "seberapa dekat" secara spasial,
        yang tidak bisa dilakukan sederet angka.
    --}}
    <div class="px-6 pb-4 pt-4">
        {{--
            Bulan digambar sebagai track terukur, bukan rel rambut yang melayang.
            Kosakatanya sama persis dengan bar kelengkapan di bawah: dasar cekung
            dengan border, isi solid untuk bagian yang sudah lewat. Versi
            sebelumnya memakai garis 3px dengan sisa bulan berkontras 1,5:1,
            jadi tidak ada satu pun tepi yang bisa dipegang mata.
        --}}
        {{--
            Tanpa aria-hidden: penanda tenggat di sini adalah tombol sungguhan.
            Elemen fokusabel di dalam aria-hidden bisa dijangkau keyboard tapi
            tidak terlihat pembaca layar, dan itu jebakan aksesibilitas.
            Bagian yang murni dekoratif (track, garis hari ini) tetap ditandai
            aria-hidden satu per satu.
        --}}
        <div class="relative" style="height: 3.75rem;">

            {{--
                Penanda hari ini: garis tinggi yang menembus dari label sampai
                melewati track. Bentuknya sengaja berbeda dari tenggat (garis vs
                lollipop), jadi "sekarang" dan "jatuh tempo" tidak pernah tertukar
                meski warnanya diabaikan.
            --}}
            @if ($todayPosition !== null)
                <span class="absolute top-0 whitespace-nowrap font-semibold"
                      style="left: {{ $todayPosition }}%; transform: translateX(-50%); font-size: var(--tp-size-2xs); letter-spacing: 0.04em; color: var(--tp-accent-text);"
                      aria-hidden="true">
                    Hari ini
                </span>

                {{-- Garis berdiri sendiri hanya digambar kalau hari ini TIDAK
                     berimpit dengan tenggat. Saat berimpit, pin-nya sendiri yang
                     ditandai (lihat --tp-pin-today di bawah), jadi tidak ada dua
                     tanda yang bertumpuk di satu titik. --}}
                @unless ($todayKey)
                    <div class="absolute rounded-full"
                         style="left: {{ $todayPosition }}%; transform: translateX(-50%); top: 0.95rem; height: 1.95rem; width: 3px; background: var(--tp-accent);"
                         aria-hidden="true"></div>
                @endunless
            @endif

            {{--
                Track: tanggal 1 sampai hari terakhir bulan tenggat. Ramping (4px)
                supaya penanda lollipop di atasnya yang jadi subjek, bukan relnya.
                Bagian yang sudah lewat memakai nada aksen; sisanya netral pucat.
            --}}
            <div class="absolute inset-x-0 overflow-hidden rounded-full"
                 style="top: 2.35rem; height: 0.25rem; background: var(--tp-track);"
                 aria-hidden="true">
                @if ($todayPosition !== null)
                    <div class="h-full rounded-full" style="width: {{ $todayPosition }}%; background: var(--tp-track-fill);"></div>
                @elseif ($isPast)
                    <div class="h-full w-full" style="background: var(--tp-track-fill);"></div>
                @endif
            </div>

            {{--
                Tenggat digambar sebagai lollipop yang bisa diklik: titik di ujung
                tangkai, berdiri DI ATAS track. Mengkliknya menyorot kewajiban itu
                di daftar triase, aksi yang sama persis dengan tombol "Sorot di
                daftar" di ruas bawah.

                Isi titik membawa status: terisi berarti masih ada tunggakan,
                berlubang berarti bersih. Statusnya terbaca dari bentuk, bukan warna.
            --}}
            @foreach ($deadlines as $deadline)
                @php
                    $pinFocused = $focused === $deadline['key'];
                    $pinIsToday = $todayKey === $deadline['key'];
                    $pinColor = $toneDot($deadline['tone']);
                    $verbPin = $deadline['key'] === \App\Services\TaxDeadlineService::PAYMENT
                        ? 'belum bayar'
                        : 'belum lapor';

                    // Perataan tooltip ditentukan dari posisi pin, bukan diukur di
                    // browser: tooltip yang ditengahkan pada pin ~97% akan meluber
                    // melewati tepi panel dan terpotong overflow:hidden.
                    $tipAlign = match (true) {
                        $deadline['position'] >= 70 => 'end',
                        $deadline['position'] <= 15 => 'start',
                        default => 'center',
                    };
                @endphp

                <button type="button"
                        wire:click="focus('{{ $deadline['key'] }}')"
                        aria-pressed="{{ $pinFocused ? 'true' : 'false' }}"
                        @if ($pinIsToday) data-today="true" @endif
                        class="tp-pin"
                        style="left: {{ $deadline['position'] }}%;
                               top: 0.95rem;
                               --tp-pin-color: {{ $pinColor }};
                               --tp-pin-fill: {{ $deadline['outstanding'] > 0 ? $pinColor : 'var(--tp-surface)' }};
                               --tp-pin-label: {{ $deadline['tone'] === 'neutral' ? 'var(--tp-text-muted)' : $toneColor($deadline['tone']) }};">
                    <span class="tp-pin-dot" aria-hidden="true"></span>
                    <span class="tp-pin-stem" aria-hidden="true"></span>
                    <span class="tp-num tp-pin-day" aria-hidden="true">{{ $deadline['date']->day }}</span>

                    {{-- Tooltip. aria-hidden karena isinya sudah disampaikan teks
                         sr-only di bawah; tanpa itu pembaca layar mendengar hal
                         yang sama dua kali. --}}
                    <span class="tp-tip" data-align="{{ $tipAlign }}" aria-hidden="true">
                        <span class="tp-tip-title">{{ $deadline['label'] }}</span>
                        <span class="tp-tip-meta block">
                            <span class="tp-num">{{ $deadline['date']->day }}</span>
                            {{ $service->monthName($anchor) }}
                            <span aria-hidden="true">&middot;</span>
                            {{ $service->humanDistance($deadline['days_remaining']) }}
                        </span>
                        <span class="tp-tip-meta block">
                            @if ($deadline['outstanding'] > 0)
                                <span class="tp-num font-semibold">{{ $deadline['outstanding'] }}</span>
                                klien {{ $verbPin }}
                            @else
                                Tidak ada tunggakan
                            @endif
                        </span>
                        @if ($deadline['outstanding'] > 0)
                            <span class="tp-tip-hint block">
                                {{ $pinFocused ? 'Klik untuk hapus sorotan' : 'Klik untuk sorot di daftar' }}
                            </span>
                        @endif
                    </span>

                    {{-- Teks untuk pembaca layar: tanggal telanjang tidak cukup
                         menjelaskan apa yang terjadi kalau tombol ini ditekan. --}}
                    <span class="sr-only">
                        {{ $deadline['label'] }},
                        {{ $deadline['date']->day }} {{ $service->monthName($anchor) }},
                        {{ $service->humanDistance($deadline['days_remaining']) }},
                        {{ $deadline['outstanding'] > 0
                            ? $deadline['outstanding'] . ' klien ' . $verbPin . '. Sorot di daftar.'
                            : 'tidak ada tunggakan.' }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    {{--
        Tiga ruas dipisah garis 1px di dalam satu panel, bukan tiga kartu
        terpisah: kewajibannya adalah satu rangkaian waktu, bukan tiga hal sejenis.
    --}}
    <div class="grid grid-cols-1 sm:grid-cols-3" style="border-top: 1px solid var(--tp-border);">
        @foreach ($deadlines as $index => $deadline)
            @php
                $isFocused = $focused === $deadline['key'];
                $hasOutstanding = $deadline['outstanding'] > 0;
                $verb = $deadline['key'] === \App\Services\TaxDeadlineService::PAYMENT ? 'belum bayar' : 'belum lapor';
            @endphp

            {{--
                Tanpa latar bertint per nada. Untuk periode historis ketiga tenggat
                otomatis lewat, sehingga tiga blok berwarna hanya jadi genangan dan
                warnanya berhenti membawa informasi. Nada tetap terbaca lewat angka,
                label, dan titik di garis waktu. Latar hanya dipakai untuk seleksi,
                yang memang cuma satu pada satu waktu.
            --}}
            <div class="px-6 py-5 transition-colors
                        @if ($index > 0) border-t sm:border-l sm:border-t-0 @endif"
                 style="border-color: var(--tp-border);
                        background: {{ $isFocused ? 'var(--tp-accent-bg)' : 'transparent' }};">

                <p class="tp-label" style="color: {{ $toneColor($deadline['tone']) }};">
                    <span class="tp-num">{{ $deadline['date']->day }}</span>
                    {{ strtoupper($service->monthName($anchor)) }}
                    <span aria-hidden="true">&middot;</span>
                    {{ $service->humanDistance($deadline['days_remaining']) }}
                </p>

                {{--
                    Nama kewajibannya, jadi ia berdiri sejajar dengan angkanya.
                    Sebelumnya 14px muted tanpa weight, terjepit antara meta 11px
                    dan angka 40px, sehingga namanya tenggelam.

                    Tetap Plus Jakarta Sans. Font display untuk label UI adalah
                    larangan register product, dan acuan Stripe yang dipilih di
                    PRODUCT.md juga tidak memakainya: alat kepatuhan pajak yang
                    berganti font untuk satu label terasa berkostum, bukan terpercaya.
                --}}
                <p class="mt-1 font-semibold"
                   style="font-size: var(--tp-size-md); letter-spacing: -0.01em; color: var(--tp-text);">
                    {{ $deadline['label'] }}
                </p>

                {{--
                    Jumlah tunggakan adalah subjek ruas ini, jadi ia yang paling
                    besar. Sebelumnya ia 20px dan tenggelam di antara teks 13-16px
                    di sekitarnya, sehingga tidak ada yang menahan mata.
                --}}
                <p class="mt-2 flex items-baseline gap-2">
                    @if ($hasOutstanding)
                        <span class="tp-num font-bold"
                              style="font-size: var(--tp-size-2xl); line-height: 0.95; letter-spacing: -0.03em; color: {{ $toneColor($deadline['tone']) }};">
                            {{ $deadline['outstanding'] }}
                        </span>
                        <span style="font-size: var(--tp-size-sm); color: var(--tp-text-muted);">
                            klien {{ $verb }}
                        </span>
                    @else
                        <span class="flex items-center gap-2" style="color: var(--tp-text-muted);">
                            <x-heroicon-o-check class="h-6 w-6 shrink-0" aria-hidden="true" />
                            <span style="font-size: var(--tp-size-base);">Tidak ada tunggakan</span>
                        </span>
                    @endif
                </p>

                <p class="mt-3" style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);">
                    {{ $deadline['detail'] }}
                </p>

                {{-- Tombol "Sorot di daftar" dan "Kirim pengingat" pindah ke panel
                     "Perlu ditindak": aksinya bekerja pada daftar itu, jadi lebih
                     masuk akal berada di sana ketimbang diulang tiga kali di sini.
                     Pin di garis waktu di atas tetap bisa diklik untuk menyorot. --}}
            </div>
        @endforeach
    </div>
</section>
