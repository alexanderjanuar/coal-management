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
            <div class="relative flex items-stretch gap-1" style="height: 11rem;">

                {{-- Garis nol duduk di posisi nol yang sebenarnya. Kalau seluruh
                     jendela positif, ia jatuh ke dasar dan batang memakai penuh. --}}
                <div class="pointer-events-none absolute inset-x-0"
                     style="top: {{ $zeroLine }}%; height: 1px; background: var(--tp-mark);"></div>

                @foreach ($points as $point)
                    @php
                        $isPositive = $point['value'] >= 0;
                        $label = $point['full_label'] . ': ' . $this->formatCurrency($point['value']) . ' ' . $this->describeBalance($point['value']);

                        // Perataan tooltip dihitung dari indeks, bukan diukur di
                        // browser: tooltip yang ditengahkan pada batang pertama
                        // atau terakhir akan meluber melewati tepi panel dan
                        // terpotong overflow:hidden.
                        $tipAlign = match (true) {
                            $loop->index <= 1 => 'start',
                            $loop->index >= $loop->count - 2 => 'end',
                            default => 'center',
                        };
                    @endphp

                    <button type="button"
                            wire:click="selectPeriod('{{ $point['period'] }}')"
                            class="tp-bar"
                            aria-label="{{ $label }}"
                            aria-current="{{ $point['is_selected'] ? 'true' : 'false' }}">

                        <span class="tp-bar-hit" aria-hidden="true"></span>

                        @if ($point['height'] > 0)
                            <span class="tp-bar-fill"
                                  style="{{ $isPositive
                                            ? 'bottom: ' . (100 - $zeroLine) . '%;'
                                            : 'top: ' . $zeroLine . '%;' }}
                                         height: {{ $point['height'] }}%;"
                                  aria-hidden="true"></span>
                        @else
                            {{-- Nol tetap perlu jejak, kalau tidak bulan kosong dan
                                 bulan nihil terlihat sama persis. --}}
                            <span class="tp-bar-fill"
                                  style="top: calc({{ $zeroLine }}% - 1px); height: 2px;"
                                  aria-hidden="true"></span>
                        @endif

                        {{-- Tooltip. aria-hidden karena aria-label tombol sudah
                             menyampaikan isi yang sama ke pembaca layar. --}}
                        <span class="tp-tip" data-align="{{ $tipAlign }}" aria-hidden="true">
                            <span class="tp-tip-title">{{ $point['full_label'] }}</span>
                            <span class="tp-tip-meta block">
                                <span class="tp-num font-semibold">{{ $this->formatCurrency($point['value']) }}</span>
                                {{ $this->describeBalance($point['value']) }}
                            </span>
                            @unless ($point['is_selected'])
                                <span class="tp-tip-hint block">Klik untuk pindah ke periode ini</span>
                            @endunless
                        </span>
                    </button>
                @endforeach
            </div>

            {{-- gap harus sama persis dengan baris batang di atas, kalau tidak
                 labelnya bergeser dan tidak lagi menunjuk batang yang benar. --}}
            <div class="mt-2 flex items-stretch gap-1">
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

        {{-- pb longgar: tooltip batang muncul di bawah kolomnya dan panel ini
             memakai overflow:hidden, jadi ruang bawah yang mepet akan memotongnya. --}}
        <p class="px-5 pb-6 pt-1" style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);">
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
