@php
    /*
     * Kolom klien sengaja tidak melar. Dengan 2.2fr, di layar lebar nama klien
     * yang pendek menyisakan ratusan piksel kosong sebelum kolom berikutnya,
     * dan mata harus melompati jurang untuk menghubungkan baris yang sama.
     * Batas 26rem menjaga jarak baca tetap dekat.
     */
    $cols = 'grid-cols-[minmax(0,1fr)_auto] lg:grid-cols-[minmax(0,26rem)_minmax(0,1fr)_8rem_9rem_1rem]';

    // Dipakai garis waktu tenggat di bawah header panel ini.
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

<section class="tp-panel" aria-labelledby="tp-triage-heading">

    <div class="tp-panel-header flex flex-wrap items-center justify-between gap-x-4 gap-y-2 px-5 py-3.5">
        <div class="flex flex-wrap items-center gap-2">
            <h2 id="tp-triage-heading" class="tp-panel-title">Perlu ditindak</h2>

            @if ($total > 0)
                <span class="tp-num" style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);">
                    {{ $total }} klien
                </span>
            @endif

            @if ($focusedDeadline)
                <button type="button"
                        wire:click="clearFocus"
                        class="tp-chip tp-focus"
                        data-tone="{{ $focusedDeadline['tone'] }}"
                        style="cursor: pointer;">
                    Disorot: {{ $focusedDeadline['label'] }}
                    <x-heroicon-o-x-mark class="h-3 w-3" aria-hidden="true" />
                </button>
            @endif
        </div>

        <p style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);">
            Tenggat terdekat lebih dulu
        </p>
    </div>

    {{--
        Bilah aksi. Sebelumnya pasangan tombol ini diulang di tiap ruas panel
        tenggat, tiga kali. Di sini ia muncul sekali, tepat di atas daftar yang
        memang jadi sasarannya.

        Hanya tenggat yang punya tunggakan yang ditampilkan: menyorot kewajiban
        yang sudah beres akan selalu menghasilkan daftar kosong.
    --}}
    @php
        $actionable = collect($deadlines)->filter(fn ($d) => $d['outstanding'] > 0);
    @endphp

    @if ($actionable->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2 px-5 py-3"
             style="border-bottom: 1px solid var(--tp-border);">
            <span class="tp-label tp-label-quiet mr-1">Sorot</span>

            @foreach ($actionable as $action)
                @php $isOn = $focused === $action['key']; @endphp
                <button type="button"
                        wire:click="toggleFocus('{{ $action['key'] }}')"
                        aria-pressed="{{ $isOn ? 'true' : 'false' }}"
                        class="tp-btn {{ $isOn ? 'tp-btn-primary' : '' }}">
                    {{ $action['short'] }}
                    <span class="tp-count">{{ $action['outstanding'] }}</span>

                    {{-- Angka telanjang tidak menjelaskan apa yang dihitung.
                         Pembaca layar mendapat kalimat utuhnya di sini. --}}
                    <span class="sr-only">
                        , {{ $action['outstanding'] }} klien
                        {{ $action['key'] === \App\Services\TaxDeadlineService::PAYMENT ? 'belum bayar' : 'belum lapor' }}
                    </span>
                </button>
            @endforeach

            @if ($focusedDeadline && $focusedDeadline['outstanding'] > 0)
                @php
                    $verbTip = $focusedDeadline['key'] === \App\Services\TaxDeadlineService::PAYMENT
                        ? 'belum bayar'
                        : 'belum lapor';
                @endphp

                {{-- Pengingat keluar dari aplikasi: notifikasi ke setiap project
                     manager plus banner untuk semua pengguna. Satu klik saja
                     tidak boleh cukup untuk memicunya. --}}
                <button type="button"
                        wire:click="requestReminder"
                        wire:confirm="Kirim pengingat ke seluruh project manager dan pasang banner untuk semua pengguna?&#10;&#10;{{ $focusedDeadline['outstanding'] }} klien {{ $verbTip }} {{ $focusedDeadline['short'] }} periode {{ $service->periodLabel($periodDate) }}."
                        wire:loading.attr="disabled"
                        wire:target="requestReminder"
                        class="tp-btn tp-btn-ghost ml-auto">
                    <x-heroicon-o-bell-alert class="h-3.5 w-3.5" aria-hidden="true" />
                    <span wire:loading.remove wire:target="requestReminder">Kirim pengingat</span>
                    <span wire:loading wire:target="requestReminder">Mengirim</span>
                </button>
            @endif
        </div>
    @endif


    @if ($error)
        {{-- Error harus terlihat. Daftar kosong palsu di surface tenggat lebih
             berbahaya daripada pesan gagal. --}}
        <div class="flex flex-wrap items-center gap-3 px-5 py-4"
             role="alert"
             style="background: var(--tp-overdue-bg); border-bottom: 1px solid var(--tp-overdue-border);">
            <x-heroicon-o-exclamation-triangle class="h-4 w-4 shrink-0" style="color: var(--tp-overdue);" aria-hidden="true" />
            <p style="font-size: var(--tp-size-sm); color: var(--tp-overdue);">{{ $error }}</p>
            <button type="button" wire:click="retry" class="tp-btn ml-auto">Muat ulang</button>
        </div>
    @endif

    @if ($rows->isEmpty() && ! $error)
        <div class="px-5 py-10 text-center">
            @if (! $periodHasReports)
                <x-heroicon-o-document-plus class="mx-auto h-6 w-6" style="color: var(--tp-text-faint);" aria-hidden="true" />
                <p class="mt-3 font-medium" style="font-size: var(--tp-size-sm); color: var(--tp-text);">
                    Belum ada laporan pajak untuk periode {{ $service->periodLabel($periodDate) }}
                </p>
                <p class="mx-auto mt-1 max-w-md" style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);">
                    Daftar ini terisi sendiri begitu laporan periode tersebut dibuat. Buat lewat menu Laporan Pajak,
                    atau pindah ke periode lain lewat pemilih di atas.
                </p>
            @elseif ($this->reportStatus === 'Sudah Lapor')
                <p class="font-medium" style="font-size: var(--tp-size-sm); color: var(--tp-text);">
                    Tidak ada yang perlu ditindak
                </p>
                <p class="mx-auto mt-1 max-w-md" style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);">
                    Daftar ini hanya memuat kewajiban yang belum beres, jadi filter status "Sudah Lapor"
                    memang tidak menyisakan apa pun di sini.
                </p>
            @elseif ($focusedDeadline)
                <x-heroicon-o-check-circle class="mx-auto h-6 w-6" style="color: var(--tp-text-faint);" aria-hidden="true" />
                <p class="mt-3 font-medium" style="font-size: var(--tp-size-sm); color: var(--tp-text);">
                    Tidak ada tunggakan untuk {{ $focusedDeadline['label'] }}
                </p>
                <button type="button" wire:click="clearFocus" class="tp-btn mt-3">Tampilkan semua kewajiban</button>
            @elseif ($hasSecondaryFilters)
                <p class="font-medium" style="font-size: var(--tp-size-sm); color: var(--tp-text);">
                    Tidak ada yang cocok dengan filter ini
                </p>
                <p class="mt-1" style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);">
                    Periode {{ $service->periodLabel($periodDate) }} punya laporan, tapi tidak ada yang lolos filter aktif.
                </p>
            @else
                <x-heroicon-o-check-circle class="mx-auto h-6 w-6" style="color: var(--tp-text-faint);" aria-hidden="true" />
                <p class="mt-3 font-medium" style="font-size: var(--tp-size-sm); color: var(--tp-text);">
                    Seluruh kewajiban periode {{ $service->periodLabel($periodDate) }} sudah beres
                </p>
                <p class="mt-1" style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);">
                    Tidak ada laporan tertunggak dan tidak ada pembayaran tertunda.
                </p>
            @endif
        </div>
    @else
        {{-- Kepala kolom. Hanya di layar lebar; di layar sempit baris menumpuk
             dan label kolom kehilangan artinya. --}}
        <div class="hidden lg:grid {{ $cols }} gap-4 px-5 py-2"
             style="border-bottom: 1px solid var(--tp-border); background: var(--tp-surface-sunken);"
             aria-hidden="true">
            <span class="tp-label tp-label-quiet">Klien</span>
            <span class="tp-label tp-label-quiet">Tertunggak</span>
            <span class="tp-label tp-label-quiet">Tenggat</span>
            <span class="tp-label tp-label-quiet text-right">Saldo akhir</span>
            <span></span>
        </div>

        <ul role="list">
            @foreach ($rows as $row)
                @php
                    $soonest = $row['obligations']->sortBy('days_remaining')->first();
                    $tone = $soonest['tone'] ?? 'neutral';
                    $toneColor = match ($tone) {
                        'overdue' => 'var(--tp-overdue)',
                        'due' => 'var(--tp-due)',
                        default => 'var(--tp-text-muted)',
                    };
                @endphp

                <li style="border-bottom: 1px solid var(--tp-border);">
                    <a href="{{ $this->taxReportUrl($row['tax_report_id']) }}"
                       class="tp-row tp-focus grid {{ $cols }} items-center gap-x-4 gap-y-1.5 px-5 py-3">

                        <span class="min-w-0">
                            <span class="block truncate font-medium"
                                  style="font-size: var(--tp-size-base); color: var(--tp-text);"
                                  title="{{ $row['client_name'] }}">
                                {{ $row['client_name'] }}
                            </span>
                            {{-- Di layar sempit kolom tenggat hilang, jadi jaraknya ikut ke sini. --}}
                            <span class="tp-num mt-0.5 block lg:hidden"
                                  style="font-size: var(--tp-size-xs); color: {{ $toneColor }};">
                                {{ $service->humanDistance($row['urgency']) }}
                            </span>
                        </span>

                        {{--
                            Titik pada chip menandai JENIS pajak, bukan urgensi.
                            Urgensi sudah dinyatakan sekali oleh kolom Tenggat;
                            mengulangnya di tiap chip membuat baris jadi konfeti
                            merah pada periode historis, dan tidak ada lagi yang
                            menonjol. Warna di sini justru menambah dimensi lain
                            yang memang dipindai mata: PPN, PPh, atau Bupot.
                        --}}
                        <span class="col-start-1 row-start-2 flex flex-wrap gap-1.5 lg:col-start-2 lg:row-start-1">
                            @foreach ($row['obligations'] as $obligation)
                                <span class="tp-chip"
                                      @if ($obligation['type']) data-type="{{ $obligation['type'] }}" @endif
                                      title="{{ $obligation['label'] }} {{ strtolower($obligation['action']) }}">
                                    {{ $obligation['label'] }}
                                </span>
                            @endforeach
                        </span>

                        <span class="tp-num hidden lg:block"
                              style="font-size: var(--tp-size-sm); color: {{ $toneColor }}; font-weight: {{ $tone === 'neutral' ? '400' : '500' }};">
                            {{ $service->humanDistance($row['urgency']) }}
                        </span>

                        <span class="col-start-2 row-start-1 text-right lg:col-start-4">
                            <span class="tp-num block"
                                  style="font-size: var(--tp-size-sm); color: {{ $row['value'] == 0 ? 'var(--tp-text-muted)' : 'var(--tp-text)' }};"
                                  title="{{ $this->formatCurrencyFull($row['value']) }} &middot; {{ $this->describeBalance($row['value']) }}">
                                {{ $this->formatCurrency($row['value']) }}
                            </span>
                            @if ($row['value'] != 0)
                                <span class="block" style="font-size: var(--tp-size-2xs); color: var(--tp-text-muted);">
                                    {{ $this->describeBalance($row['value']) }}
                                </span>
                            @endif
                        </span>

                        <x-heroicon-o-chevron-right class="hidden h-4 w-4 lg:block"
                                                    style="color: var(--tp-text-faint);"
                                                    aria-hidden="true" />
                    </a>
                </li>
            @endforeach
        </ul>

        @if ($hiddenCount > 0 || $showAll)
            <div class="px-5 py-2.5">
                <button type="button" wire:click="toggleShowAll" class="tp-btn tp-btn-ghost w-full">
                    @if ($showAll)
                        Ringkas jadi {{ \App\Livewire\TaxReport\Dashboard\TriageList::PREVIEW_LIMIT }} teratas
                    @else
                        Tampilkan {{ $hiddenCount }} klien lainnya
                    @endif
                </button>
            </div>
        @endif
    @endif
</section>
