<section class="tp-panel" aria-labelledby="tp-progress-heading">

    <div class="tp-panel-header flex flex-wrap items-center justify-between gap-x-4 gap-y-2 px-5 py-3">
        <h2 id="tp-progress-heading" class="tp-panel-title">Kelengkapan periode</h2>

        <div class="flex items-center gap-3">
            <span style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);">
                {{ $service->periodLabel($periodDate) }}
            </span>

            {{-- Pemilih bentuk tampilan. Dua bentuk menjawab pertanyaan berbeda:
                 baris progres untuk "seberapa jauh tiap jenis", kolom untuk
                 membandingkan keempatnya sekaligus. --}}
            <div class="tp-seg" role="group" aria-label="Bentuk tampilan">
                <button type="button"
                        wire:click="setView('bar')"
                        aria-pressed="{{ $view === 'bar' ? 'true' : 'false' }}"
                        title="Tampilan baris">
                    <x-heroicon-o-bars-3-bottom-left class="h-4 w-4" aria-hidden="true" />
                    <span class="sr-only">Tampilan baris progres</span>
                </button>
                <button type="button"
                        wire:click="setView('kolom')"
                        aria-pressed="{{ $view === 'kolom' ? 'true' : 'false' }}"
                        title="Tampilan kolom">
                    <x-heroicon-o-chart-bar class="h-4 w-4" aria-hidden="true" />
                    <span class="sr-only">Tampilan kolom</span>
                </button>
            </div>
        </div>
    </div>

    @unless ($hasData)
        <div class="px-5 py-8 text-center">
            <p style="font-size: var(--tp-size-sm); color: var(--tp-text-muted);">
                Belum ada data untuk periode {{ $service->periodLabel($periodDate) }}
            </p>
        </div>
    @else

        @php
            /*
             * "Tidak ada aktivitas" adalah keadaan ketiga, bukan nol persen.
             *
             * PPh Unifikasi hanya wajib dilaporkan bila masa itu ada pemotongan.
             * Menampilkannya sebagai 0% membuatnya terbaca seperti pekerjaan
             * tertunggak, padahal justru tidak ada yang perlu dikerjakan.
             */
            $isIdle = fn (array $row) => $row['total'] === 0 && ($row['idle'] ?? 0) > 0;

            $tipFor = function (array $row) use ($isIdle) {
                if ($isIdle($row)) {
                    return $row['idle'] . ' klien tanpa aktivitas ' . $row['label']
                        . ' pada periode ini, jadi tidak ada SPT yang wajib dilaporkan';
                }

                if ($row['total'] === 0) {
                    return 'Tidak ada kewajiban ' . $row['label'] . ' pada periode ini';
                }

                return $row['done'] . ' dari ' . $row['total'] . ' ' . $row['verb']
                    . ($row['remaining'] > 0 ? ', ' . $row['remaining'] . ' tersisa' : '');
            };
        @endphp

        @if ($view === 'bar')
            {{-- Bentuk baris: menjawab "seberapa jauh tiap jenis" --}}
            <div class="px-5 py-1.5">
                @foreach ($rows as $row)
                    @php $hasRow = $row['total'] > 0; @endphp

                    <{{ $row['filterable'] ? 'button' : 'div' }}
                        @if ($row['filterable'])
                            type="button"
                            wire:click="toggleTaxType('{{ $row['key'] }}')"
                            aria-pressed="{{ $row['active'] ? 'true' : 'false' }}"
                        @endif
                        class="tp-prow {{ $row['filterable'] ? '' : 'tp-prow-static' }} @unless($loop->last) border-b @endunless"
                        style="border-color: var(--tp-border);">

                        {{-- Titik identitas ikut di label, bukan hanya di bar: pada 0%
                             bar-nya kosong dan pemetaan warnanya akan hilang persis
                             saat baris itu paling perlu dikenali. --}}
                        <span class="flex w-28 shrink-0 items-center gap-2 font-medium"
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
                        --}}
                        @if ($isIdle($row))
                            {{-- Bar kosong tetap terbaca "0% selesai". Label
                                 menggantikannya supaya tidak tertukar dengan
                                 pekerjaan yang tertinggal. --}}
                            <span class="min-w-0 flex-1"
                                  style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);"
                                  aria-hidden="true">
                                Tidak ada aktivitas, tidak wajib lapor
                            </span>
                        @else
                            <span class="h-1.5 min-w-0 flex-1 overflow-hidden rounded-full"
                                  style="background: var(--tp-surface-sunken); box-shadow: inset 0 0 0 1px var(--tp-border);"
                                  aria-hidden="true">
                                @if ($hasRow)
                                    {{-- Tanpa transisi: width adalah properti layout, dan
                                         menganimasikannya memaksa reflow. Bar ini penanda
                                         status, bukan momen gerak. --}}
                                    <span class="block h-full rounded-full"
                                          style="width: {{ $row['percent'] }}%; background: {{ $row['color'] }};"></span>
                                @endif
                            </span>
                        @endif

                        <span class="tp-num w-28 shrink-0 text-right"
                              style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);"
                              aria-hidden="true">
                            @if ($hasRow)
                                {{ $row['done'] }} dari {{ $row['total'] }}
                            @elseif ($isIdle($row))
                                {{ $row['idle'] }} klien
                            @else
                                Tidak ada
                            @endif
                        </span>

                        {{-- Kolom persen diganti label saat masa itu memang tidak
                             menimbulkan kewajiban. Menulis 0% di sana keliru: bukan
                             berarti tertinggal, melainkan tidak ada yang perlu
                             dilaporkan. --}}
                        <span class="tp-num w-11 shrink-0 text-right font-semibold"
                              style="font-size: var(--tp-size-sm); color: var(--tp-text);"
                              aria-hidden="true">
                            {{ $hasRow ? $row['percent'] . '%' : '' }}
                        </span>

                        <span class="sr-only">
                            {{ $row['label'] }}: {{ $tipFor($row) }}.
                            @if ($row['filterable'])
                                {{ $row['active'] ? 'Sedang disaring. Klik untuk lepas.' : 'Klik untuk saring dashboard ke jenis ini.' }}
                            @endif
                        </span>
                    </{{ $row['filterable'] ? 'button' : 'div' }}>
                @endforeach
            </div>
        @else
            {{-- Bentuk kolom: menjawab "bagaimana keempatnya dibandingkan" --}}
            <div class="px-5 pb-2 pt-5">
                <div class="flex items-end gap-3" style="height: 9rem;">
                    @foreach ($rows as $row)
                        @php $hasRow = $row['total'] > 0; @endphp

                        <{{ $row['filterable'] ? 'button' : 'div' }}
                            @if ($row['filterable'])
                                type="button"
                                wire:click="toggleTaxType('{{ $row['key'] }}')"
                                aria-pressed="{{ $row['active'] ? 'true' : 'false' }}"
                            @endif
                            class="tp-col {{ $row['filterable'] ? '' : 'tp-col-static' }}"
                            style="--tp-col-color: {{ $row['color'] }};">

                            <span class="tp-num tp-col-value" aria-hidden="true">
                                {{ $hasRow ? $row['percent'] . '%' : '—' }}
                            </span>

                            {{-- Track penuh digambar di belakang isian: tanpa itu
                                 batang 0% tidak punya bentuk sama sekali dan tidak
                                 bisa dibedakan dari kolom yang tidak ada datanya.

                                 Saat tidak ada aktivitas, track-nya digaris putus
                                 putus: bentuknya sendiri yang mengatakan "kolom ini
                                 memang kosong karena tidak wajib", bukan "nol dari
                                 sekian yang seharusnya dikerjakan". --}}
                            <span class="tp-col-track {{ $isIdle($row) ? 'tp-col-track-idle' : '' }}" aria-hidden="true">
                                @if ($hasRow)
                                    <span class="tp-col-fill" style="height: {{ max($row['percent'], 1.5) }}%;"></span>
                                @endif
                            </span>

                            <span class="tp-col-label" aria-hidden="true">{{ $row['label'] }}</span>
                            <span class="tp-num tp-col-count" aria-hidden="true">
                                @if ($hasRow)
                                    {{ $row['done'] }}/{{ $row['total'] }}
                                @elseif ($isIdle($row))
                                    Tanpa aktivitas
                                @else
                                    Tidak ada
                                @endif
                            </span>

                            <span class="sr-only">
                                {{ $row['label'] }}: {{ $tipFor($row) }}.
                                @if ($row['filterable'])
                                    {{ $row['active'] ? 'Sedang disaring. Klik untuk lepas.' : 'Klik untuk saring dashboard ke jenis ini.' }}
                                @endif
                            </span>
                        </{{ $row['filterable'] ? 'button' : 'div' }}>
                    @endforeach
                </div>
            </div>
        @endif

        <p class="px-5 pb-3.5 pt-2" style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);">
            Baris pembayaran mengabaikan laporan berstatus Nihil, karena tidak ada yang harus dibayar.
            Klik satu jenis pajak untuk menyaring seluruh dashboard.
        </p>
    @endunless
</section>
