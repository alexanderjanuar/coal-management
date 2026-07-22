@php
    $summary = $this->summary;
    $isNihil = $this->isNoActivity;
    $sudahLapor = $summary && $summary->report_status === 'Sudah Lapor' && ! $isNihil;
@endphp

<div>
    @if ($isNihil)
        {{-- Sudah dinyatakan tanpa aktivitas. Jejak siapa & kapan ditampilkan
             karena penandaan ini menyetel status selesai tanpa dokumen apa pun,
             jadi harus bisa ditelusuri. --}}
        <div class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3">
                <x-heroicon-o-minus-circle class="mt-0.5 h-5 w-5 shrink-0 text-gray-400" aria-hidden="true" />
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        Tidak ada aktivitas {{ $this->label }} pada masa ini
                    </p>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        Tidak ada SPT yang wajib dilaporkan, jadi masa ini tidak dihitung sebagai tunggakan.
                        @if ($summary?->no_activity_at)
                            Ditandai
                            @if ($summary->noActivityBy)
                                oleh {{ $summary->noActivityBy->name }}
                            @endif
                            pada {{ $summary->no_activity_at->translatedFormat('d M Y, H:i') }}.
                        @endif
                    </p>
                </div>
            </div>

            <div class="shrink-0">
                {{ $this->clearNoActivityAction }}
            </div>
        </div>
    @else
        {{-- Belum dinyatakan. Alur normalnya (unggah SPT) tetap tersedia di
             bawah; penandaan nihil disediakan sebagai aksi sekunder, bukan
             sebagai pertanyaan yang menghalangi. --}}
        <div class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3">
                <x-heroicon-o-question-mark-circle class="mt-0.5 h-5 w-5 shrink-0 text-gray-400" aria-hidden="true" />
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        Apakah ada aktivitas {{ $this->label }} pada masa ini?
                    </p>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        @if ($sudahLapor)
                            SPT masa ini sudah dilaporkan.
                        @else
                            <strong class="font-medium">Ada aktivitas:</strong> unggah SPT-nya di bawah setelah dilaporkan di Coretax.
                            <strong class="font-medium">Tidak ada:</strong> tandai supaya masa ini tidak terus terhitung sebagai tunggakan.
                        @endif
                    </p>
                </div>
            </div>

            @unless ($sudahLapor)
                <div class="shrink-0">
                    {{ $this->markNoActivityAction }}
                </div>
            @endunless
        </div>
    @endif

    <x-filament-actions::modals />
</div>
