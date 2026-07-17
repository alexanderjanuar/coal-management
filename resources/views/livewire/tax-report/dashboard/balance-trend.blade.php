<section class="tp-panel" aria-labelledby="tp-trend-heading">

    <div class="tp-panel-header flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-5 py-3.5">
        <div class="flex flex-wrap items-baseline gap-x-2">
            <h2 id="tp-trend-heading" class="tp-panel-title">Saldo akhir</h2>
            <span style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);">{{ $rangeLabel }}</span>
        </div>

        <p style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);">
            {{ $service->periodLabel($selected) }}:
            <span class="tp-num font-semibold" style="color: var(--tp-text);">
                {{ $this->formatCurrency($selectedValue) }}
            </span>
            {{ $this->describeBalance($selectedValue) }}
        </p>
    </div>

    @unless ($hasData)
        <div class="px-5 py-10 text-center">
            <p style="font-size: var(--tp-size-sm); color: var(--tp-text);">
                Tidak ada saldo tercatat pada 12 bulan terakhir
            </p>
            <p class="mx-auto mt-1 max-w-md" style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);">
                Semua laporan pada rentang ini berstatus Nihil, atau perhitungannya belum dijalankan.
            </p>
        </div>
    @else
        <div class="px-5 pb-2 pt-5">
            {{--
                Garis nol di tengah. Batang ke atas berarti kurang bayar, ke bawah
                berarti lebih bayar. Tanda dibaca dari posisi, bukan dari warna.
            --}}
            <div class="relative flex items-stretch gap-[3px]" style="height: 8rem;">

                {{-- Garis nol duduk di posisi nol yang sebenarnya. Kalau seluruh
                     jendela positif, ia jatuh ke dasar dan batang memakai penuh. --}}
                <div class="pointer-events-none absolute inset-x-0"
                     style="top: {{ $zeroLine }}%; height: 1px; background: var(--tp-mark);"></div>

                @foreach ($points as $point)
                    @php
                        $isPositive = $point['value'] >= 0;
                        // --tp-mark, bukan --tp-border-strong: yang terakhir itu
                        // warna garis pemisah (kontras 1.6:1) dan membuat batang
                        // chart terlihat pudar seperti placeholder.
                        $barColor = $point['is_selected'] ? 'var(--tp-accent)' : 'var(--tp-mark)';
                        $title = $point['full_label'] . ': ' . $this->formatCurrency($point['value']) . ' ' . $this->describeBalance($point['value']);
                    @endphp

                    <button type="button"
                            wire:click="selectPeriod('{{ $point['period'] }}')"
                            class="tp-focus group relative min-w-0 flex-1"
                            title="{{ $title }}"
                            aria-label="{{ $title }}"
                            aria-current="{{ $point['is_selected'] ? 'true' : 'false' }}">

                        {{-- Latar hover selebar kolom, supaya sasaran kliknya
                             tetap besar walau batangnya pendek. --}}
                        <span class="absolute inset-0 rounded-sm opacity-0 transition-opacity group-hover:opacity-100"
                              style="background: var(--tp-surface-sunken);"></span>

                        @if ($point['height'] > 0)
                            <span class="absolute inset-x-0 rounded-[2px]"
                                  style="{{ $isPositive
                                            ? 'bottom: ' . (100 - $zeroLine) . '%;'
                                            : 'top: ' . $zeroLine . '%;' }}
                                         height: {{ $point['height'] }}%;
                                         background: {{ $barColor }};"></span>
                        @else
                            {{-- Nol tetap perlu jejak, kalau tidak bulan kosong dan
                                 bulan nihil terlihat sama persis. --}}
                            <span class="absolute inset-x-0 rounded-full"
                                  style="top: calc({{ $zeroLine }}% - 1px); height: 2px; background: {{ $point['is_selected'] ? 'var(--tp-accent)' : 'var(--tp-mark-quiet)' }};"></span>
                        @endif
                    </button>
                @endforeach
            </div>

            <div class="mt-2 flex items-stretch gap-[3px]">
                @foreach ($points as $point)
                    <span class="min-w-0 flex-1 text-center"
                          style="font-size: var(--tp-size-2xs);
                                 color: {{ $point['is_selected'] ? 'var(--tp-accent-text)' : 'var(--tp-text-muted)' }};
                                 font-weight: {{ $point['is_selected'] ? '600' : '400' }};">
                        {{ $point['label'] }}
                        @if ($point['is_january'])
                            <span class="tp-num block" style="color: var(--tp-text-faint); font-size: 0.625rem;">
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $point['period'])->year }}
                            </span>
                        @endif
                    </span>
                @endforeach
            </div>
        </div>

        <p class="px-5 pb-3.5 pt-1" style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);">
            {{-- Menjelaskan arah "ke bawah" saat tidak ada satu pun nilai negatif
                 hanya membuat pembaca mencari sesuatu yang tidak ada. --}}
            @if ($hasNegative)
                Batang ke atas berarti kurang bayar, ke bawah berarti lebih bayar.
            @else
                Seluruh bulan pada rentang ini kurang bayar atau nihil.
            @endif
            Klik satu bulan untuk berpindah ke periodenya.
        </p>
    @endunless
</section>
