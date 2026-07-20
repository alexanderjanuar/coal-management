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
                        style="padding: 0 0.5rem;"
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
                        style="padding: 0 0.5rem;"
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

    {{--
        Filter memakai komponen Filament Forms, jadi select-nya berperilaku sama
        persis dengan kontrol di seluruh panel admin (dropdown searchable, bukan
        select native).

        Tanpa container: filter bukan objek tersendiri, ia bagian dari kepala
        halaman. Membungkusnya dengan panel berbingkai membuatnya terbaca sejajar
        dengan section data di bawahnya, padahal ia hanya kontrol.
    --}}
    <div class="pb-2">
        {{ $this->form }}

        @if ($activeCount > 0)
            <div class="mt-3 flex items-center gap-3">
                <button type="button" wire:click="resetFilters" class="tp-btn tp-btn-ghost">
                    <x-heroicon-o-x-mark class="h-3.5 w-3.5" aria-hidden="true" />
                    Hapus {{ $activeCount }} filter
                </button>

                <span wire:loading
                      wire:target="resetFilters, previousPeriod, nextPeriod, goToCurrentPeriod"
                      class="flex items-center gap-1.5"
                      style="font-size: var(--tp-size-xs); color: var(--tp-text-muted);">
                    <x-heroicon-o-arrow-path class="h-3.5 w-3.5 animate-spin" aria-hidden="true" />
                    Memuat
                </span>
            </div>
        @endif
    </div>
</div>
