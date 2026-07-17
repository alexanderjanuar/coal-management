@php
    $deadlines = app(\App\Services\TaxDeadlineService::class);
    $periodLabel = $this->periodLabel($deadlines);
    $isCurrent = $this->isCurrentPeriod($deadlines);
    $canForward = $this->canGoForward($deadlines);
    $activeCount = $this->activeFilterCount();
@endphp

<div>
    <div class="flex flex-col gap-4 pb-4 lg:flex-row lg:items-end lg:justify-between">

        <div>
            <h1 class="font-bold" style="font-size: var(--tp-size-xl); letter-spacing: -0.025em; color: var(--tp-text);">
                Laporan Pajak
            </h1>
            <p class="mt-1" style="font-size: var(--tp-size-sm); color: var(--tp-text-muted);">
                Periode {{ $periodLabel }}, jatuh tempo di
                {{ $deadlines->monthName($deadlines->anchorFor($this->periodDate())) }}
                {{ $deadlines->anchorFor($this->periodDate())->year }}
            </p>
        </div>

        {{-- Stepper periode. Satu-satunya kontrol waktu di halaman ini. --}}
        <div class="flex items-center gap-1.5">
            <div class="flex items-center rounded" style="border: 1px solid var(--tp-border); background: var(--tp-surface);">
                <button type="button"
                        wire:click="previousPeriod"
                        class="tp-btn tp-btn-ghost rounded-none rounded-l"
                        style="padding: 0.375rem 0.5rem;"
                        aria-label="Periode sebelumnya">
                    <x-heroicon-o-chevron-left class="h-4 w-4" aria-hidden="true" />
                </button>

                <span class="tp-num min-w-[8.5rem] px-2 text-center font-medium"
                      style="font-size: var(--tp-size-sm); color: var(--tp-text);"
                      aria-live="polite">
                    {{ $periodLabel }}
                </span>

                <button type="button"
                        wire:click="nextPeriod"
                        @disabled(! $canForward)
                        class="tp-btn tp-btn-ghost rounded-none rounded-r"
                        style="padding: 0.375rem 0.5rem;"
                        aria-label="Periode berikutnya"
                        @if (! $canForward) title="Periode setelah {{ $periodLabel }} belum jatuh tempo" @endif>
                    <x-heroicon-o-chevron-right class="h-4 w-4" aria-hidden="true" />
                </button>
            </div>

            @unless ($isCurrent)
                <button type="button" wire:click="goToCurrentPeriod" class="tp-btn">
                    Periode berjalan
                </button>
            @endunless
        </div>
    </div>

    {{-- Filter. Empat select biasa di dalam satu toolbar, tanpa dropdown bertingkat. --}}
    <div class="tp-toolbar">
        <span class="tp-label tp-label-quiet mr-1 hidden sm:inline">Saring</span>

        <label class="sr-only" for="tp-filter-client">Klien</label>
        <select id="tp-filter-client"
                wire:model.live="clientId"
                class="tp-select max-w-[14rem]"
                data-active="{{ $this->clientId ? 'true' : 'false' }}">
            <option value="">Semua klien</option>
            @foreach ($this->clientOptions() as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>

        <label class="sr-only" for="tp-filter-type">Jenis pajak</label>
        <select id="tp-filter-type"
                wire:model.live="taxType"
                class="tp-select"
                data-active="{{ $this->taxType ? 'true' : 'false' }}">
            <option value="">Semua jenis</option>
            <option value="ppn">PPN</option>
            <option value="pph">PPh</option>
            <option value="bupot">Bupot</option>
        </select>

        <label class="sr-only" for="tp-filter-report">Status lapor</label>
        <select id="tp-filter-report"
                wire:model.live="reportStatus"
                class="tp-select"
                data-active="{{ $this->reportStatus ? 'true' : 'false' }}">
            <option value="">Semua status lapor</option>
            <option value="Belum Lapor">Belum Lapor</option>
            <option value="Sudah Lapor">Sudah Lapor</option>
        </select>

        <label class="sr-only" for="tp-filter-payment">Status bayar</label>
        <select id="tp-filter-payment"
                wire:model.live="paymentStatus"
                class="tp-select"
                data-active="{{ $this->paymentStatus ? 'true' : 'false' }}">
            <option value="">Semua status bayar</option>
            <option value="Kurang Bayar">Kurang Bayar</option>
            <option value="Lebih Bayar">Lebih Bayar</option>
            <option value="Nihil">Nihil</option>
        </select>

        @if ($activeCount > 0)
            <button type="button" wire:click="resetFilters" class="tp-btn tp-btn-ghost">
                <x-heroicon-o-x-mark class="h-3.5 w-3.5" aria-hidden="true" />
                Hapus {{ $activeCount }} filter
            </button>
        @endif

        <span class="ml-auto flex items-center gap-1.5"
              style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);"
              wire:loading
              wire:target="previousPeriod, nextPeriod, goToCurrentPeriod, clientId, taxType, reportStatus, paymentStatus">
            <x-heroicon-o-arrow-path class="h-3.5 w-3.5 animate-spin" aria-hidden="true" />
            Memuat
        </span>
    </div>
</div>
