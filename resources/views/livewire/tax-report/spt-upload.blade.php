<div>
    <div class="space-y-4">
        @unless($this->type)
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Upload SPT Masa</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Unggah berkas SPT/BPE dari Coretax untuk tiap jenis pajak yang dikontrak. Setelah diunggah,
                    status jenis tersebut otomatis menjadi <strong>Sudah Lapor</strong> &amp; <strong>Sudah Bayar</strong>.
                </p>
            </div>
        @endunless

        @forelse($this->rows as $row)
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800/50">

                {{-- Header --}}
                <div class="flex flex-col gap-3 border-b border-gray-100 p-4 dark:border-gray-700/60 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold text-gray-900 dark:text-white">SPT {{ $row['label'] }}</span>
                            <x-filament::badge :color="$row['reported'] ? 'success' : 'warning'" size="sm">
                                {{ $row['reported'] ? 'Sudah Lapor' : 'Belum Lapor' }}
                            </x-filament::badge>
                            @if($row['reported'])
                                <x-filament::badge :color="$row['paid'] ? 'success' : 'gray'" size="sm">
                                    {{ $row['paid'] ? 'Sudah Bayar' : 'Belum Bayar' }}
                                </x-filament::badge>
                            @endif
                        </div>
                        @if($row['fileUrl'])
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                @if($row['reportedAt'])Lapor: {{ $row['reportedAt'] }}@endif
                                @if($row['nomor']) &middot; No. {{ $row['nomor'] }}@endif
                            </p>
                        @endif
                    </div>

                    <div class="flex flex-shrink-0 items-center gap-2">
                        @if($row['fileUrl'])
                            <x-filament::button tag="a" href="{{ $row['fileUrl'] }}" target="_blank"
                                size="sm" color="gray" icon="heroicon-m-arrow-top-right-on-square">
                                Buka
                            </x-filament::button>
                            <x-filament::button size="sm" color="gray" icon="heroicon-m-arrow-up-tray"
                                wire:click="mountAction('uploadSpt', { type: '{{ $row['type'] }}' })">
                                Ganti
                            </x-filament::button>
                            <x-filament::button size="sm" color="danger" icon="heroicon-m-trash"
                                wire:click="mountAction('removeSpt', { type: '{{ $row['type'] }}' })">
                                Hapus
                            </x-filament::button>
                        @else
                            <x-filament::button size="sm" color="primary" icon="heroicon-m-arrow-up-tray"
                                wire:click="mountAction('uploadSpt', { type: '{{ $row['type'] }}' })">
                                Upload SPT
                            </x-filament::button>
                        @endif
                    </div>
                </div>

                {{-- Preview / empty state --}}
                @if($row['fileUrl'])
                    <div class="bg-gray-50 p-3 dark:bg-gray-900/40">
                        @if($row['isImage'])
                            <div class="flex max-h-[600px] justify-center overflow-auto rounded-lg border border-gray-200 bg-white p-2 dark:border-gray-700 dark:bg-gray-800">
                                <img src="{{ $row['fileUrl'] }}" alt="SPT {{ $row['label'] }}" class="max-w-full" />
                            </div>
                        @else
                            <iframe src="{{ $row['fileUrl'] }}#toolbar=1&view=FitH"
                                class="h-[600px] w-full rounded-lg border border-gray-200 bg-white dark:border-gray-700"
                                title="Preview SPT {{ $row['label'] }}" loading="lazy"></iframe>
                        @endif
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center gap-2 px-4 py-10 text-center">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500">
                            <x-filament::icon icon="heroicon-o-document-arrow-up" class="h-6 w-6" />
                        </div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Belum ada berkas SPT</p>
                        <p class="max-w-sm text-xs text-gray-500 dark:text-gray-400">
                            Unggah SPT/BPE {{ $row['label'] }} dari Coretax untuk menandai masa ini sudah lapor &amp; bayar.
                        </p>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                Klien ini belum memiliki kontrak jenis pajak ini, jadi belum ada SPT yang perlu diunggah.
            </div>
        @endforelse
    </div>

    <x-filament-actions::modals />
</div>
