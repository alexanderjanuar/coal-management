<section class="tp-panel" aria-labelledby="tp-progress-heading">

    <div class="tp-panel-header flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-5 py-3.5">
        <h2 id="tp-progress-heading" class="tp-panel-title">Kelengkapan periode</h2>
        <p style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);">
            {{ $service->periodLabel($periodDate) }}
        </p>
    </div>

    @unless ($hasData)
        <div class="px-5 py-8 text-center">
            <p style="font-size: var(--tp-size-sm); color: var(--tp-text-muted);">
                Belum ada data untuk periode {{ $service->periodLabel($periodDate) }}
            </p>
        </div>
    @else
        <div class="px-5 py-1.5">
            @foreach ($rows as $row)
                @php
                    $hasRow = $row['total'] > 0;
                    $remaining = $row['total'] - $row['done'];
                @endphp

                <div class="flex items-center gap-3 py-2.5 @unless($loop->last) border-b @endunless"
                     style="border-color: var(--tp-border);">

                    {{-- Titik identitas ikut di label, bukan hanya di bar: pada 0%
                         bar-nya kosong dan pemetaan warnanya akan hilang persis
                         saat baris itu paling perlu dikenali. --}}
                    <span class="flex w-24 shrink-0 items-center gap-2 font-medium"
                          style="font-size: var(--tp-size-sm); color: var(--tp-text);">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full"
                              style="background: {{ $row['color'] }};"
                              aria-hidden="true"></span>
                        {{ $row['label'] }}
                    </span>

                    {{--
                        Isi bar memakai warna identitas jenis pajaknya, bukan nada
                        urgensi. Kelengkapan adalah status, bukan alarm; urgensi
                        sudah ditangani tulang punggung tenggat di atas.
                        role=img dengan aria-label supaya pembaca layar tetap
                        mendapat angkanya tanpa harus menafsirkan bar.
                    --}}
                    <span class="h-1.5 min-w-0 flex-1 overflow-hidden rounded-full"
                          style="background: var(--tp-surface-sunken); box-shadow: inset 0 0 0 1px var(--tp-border);"
                          role="img"
                          aria-label="{{ $row['label'] }}: {{ $row['done'] }} dari {{ $row['total'] }} {{ $row['verb'] }}">
                        @if ($hasRow)
                            {{-- Tanpa transisi: width adalah properti layout, dan
                                 menganimasikannya memaksa reflow. Bar ini penanda
                                 status, bukan momen gerak. --}}
                            <span class="block h-full rounded-full"
                                  style="width: {{ $row['percent'] }}%; background: {{ $row['color'] }};"></span>
                        @endif
                    </span>

                    <span class="tp-num w-28 shrink-0 text-right"
                          style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);">
                        @if ($hasRow)
                            {{ $row['done'] }} dari {{ $row['total'] }}
                        @else
                            Tidak ada
                        @endif
                    </span>

                    {{-- Kolom dibiarkan kosong saat tidak ada data. Sel di sebelahnya
                         sudah berbunyi "Tidak ada", jadi penanda apa pun di sini
                         hanya mengulang. --}}
                    <span class="tp-num w-11 shrink-0 text-right font-semibold"
                          style="font-size: var(--tp-size-sm); color: var(--tp-text);">
                        {{ $hasRow ? $row['percent'] . '%' : '' }}
                    </span>
                </div>
            @endforeach
        </div>

        <p class="px-5 pb-3.5 pt-1" style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);">
            Baris pembayaran mengabaikan laporan berstatus Nihil, karena tidak ada yang harus dibayar.
        </p>
    @endunless
</section>
