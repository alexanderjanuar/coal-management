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
        <div class="relative" style="height: 3.75rem;" aria-hidden="true">

            {{--
                Penanda hari ini: garis tinggi yang menembus dari label sampai
                melewati track. Bentuknya sengaja berbeda dari tenggat (garis vs
                lollipop), jadi "sekarang" dan "jatuh tempo" tidak pernah tertukar
                meski warnanya diabaikan.
            --}}
            @if ($todayPosition !== null)
                <span class="absolute top-0 whitespace-nowrap font-semibold"
                      style="left: {{ $todayPosition }}%; transform: translateX(-50%); font-size: var(--tp-size-2xs); letter-spacing: 0.04em; color: var(--tp-accent-text);">
                    Hari ini
                </span>
                <div class="absolute rounded-full"
                     style="left: {{ $todayPosition }}%; transform: translateX(-50%); top: 0.95rem; height: 2.05rem; width: 3px; background: var(--tp-accent);"></div>
            @endif

            {{--
                Tenggat digambar sebagai lollipop: titik di ujung tangkai, berdiri
                DI ATAS track. Versi sebelumnya menaruh titik 12px di tengah track
                8px, jadi titiknya tidak pernah menjorok dan justru tenggelam ke
                dalam relnya. Yang berlubang bahkan hampir tak terbedakan dari track.

                Isi titik tetap membawa status: terisi berarti masih ada tunggakan,
                berlubang berarti bersih. Statusnya terbaca dari bentuk, bukan warna.
            --}}
            @foreach ($deadlines as $deadline)
                <span class="absolute rounded-full"
                      style="left: {{ $deadline['position'] }}%;
                             top: 0.95rem;
                             transform: translateX(-50%);
                             height: 0.75rem;
                             width: 0.75rem;
                             border: 2px solid {{ $toneDot($deadline['tone']) }};
                             background: {{ $deadline['outstanding'] > 0 ? $toneDot($deadline['tone']) : 'var(--tp-surface)' }};"></span>

                <span class="absolute"
                      style="left: {{ $deadline['position'] }}%;
                             transform: translateX(-50%);
                             top: 1.65rem;
                             height: 0.5rem;
                             width: 2px;
                             background: {{ $toneDot($deadline['tone']) }};"></span>
            @endforeach

            {{-- Track: tanggal 1 sampai hari terakhir bulan tenggat --}}
            <div class="absolute inset-x-0 overflow-hidden rounded-full"
                 style="top: 2.15rem; height: 0.625rem; background: var(--tp-surface-sunken); border: 1px solid var(--tp-border);">
                @if ($todayPosition !== null)
                    <div class="h-full" style="width: {{ $todayPosition }}%; background: var(--tp-mark);"></div>
                @elseif ($isPast)
                    <div class="h-full w-full" style="background: var(--tp-mark);"></div>
                @endif
            </div>

            @foreach ($deadlines as $deadline)
                <span class="tp-num absolute whitespace-nowrap font-semibold"
                      style="left: {{ $deadline['position'] }}%;
                             top: 2.95rem;
                             transform: translateX(-50%);
                             font-size: var(--tp-size-xs);
                             color: {{ $deadline['tone'] === 'neutral' ? 'var(--tp-text-muted)' : $toneColor($deadline['tone']) }};">
                    {{ $deadline['date']->day }}
                </span>
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

                @if ($hasOutstanding)
                    <div class="mt-2.5 flex flex-wrap gap-1.5">
                        {{--
                            Aksi primer ruas ini. Diberi aksen penuh karena tanpa
                            satu pun elemen yang menyatakan mana yang harus
                            dikerjakan, halaman ini kehilangan titik fokusnya dan
                            aksen jadi nyaris tak terpakai. Saat sorotan sedang
                            aktif ia turun jadi sekunder: mematikan sorotan bukan
                            aksi yang perlu diarahkan.
                        --}}
                        <button type="button"
                                wire:click="focus('{{ $deadline['key'] }}')"
                                aria-pressed="{{ $isFocused ? 'true' : 'false' }}"
                                class="tp-btn {{ $isFocused ? '' : 'tp-btn-primary' }}"
                                style="{{ $isFocused ? 'border-color: var(--tp-accent-border); color: var(--tp-accent-text);' : '' }}">
                            {{ $isFocused ? 'Hapus sorotan' : 'Sorot di daftar' }}
                        </button>

                        {{--
                            Pengingat keluar dari aplikasi: notifikasi ke setiap
                            project manager plus banner untuk semua pengguna.
                            Satu klik saja tidak boleh cukup untuk memicunya.
                        --}}
                        <button type="button"
                                wire:click="sendReminder('{{ $deadline['key'] }}')"
                                wire:confirm="Kirim pengingat ke seluruh project manager dan pasang banner untuk semua pengguna?&#10;&#10;{{ $deadline['outstanding'] }} klien belum {{ $verb }} {{ $deadline['short'] }} periode {{ $service->periodLabel($periodDate) }}."
                                wire:loading.attr="disabled"
                                wire:target="sendReminder('{{ $deadline['key'] }}')"
                                class="tp-btn tp-btn-ghost">
                            <span wire:loading.remove wire:target="sendReminder('{{ $deadline['key'] }}')">Kirim pengingat</span>
                            <span wire:loading wire:target="sendReminder('{{ $deadline['key'] }}')">Mengirim</span>
                        </button>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</section>
