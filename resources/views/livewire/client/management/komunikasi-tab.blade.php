<div class="space-y-6">
    {{-- Header Section --}}
    <div class="rounded-2xl bg-gradient-to-r from-gray-50 to-gray-100 p-6 dark:from-gray-800 dark:to-gray-900">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Riwayat Komunikasi & Catatan
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Dokumentasi seluruh interaksi dengan klien {{ $client->name }}
                </p>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
        @php
        $stats = [
            ['label' => 'Total Komunikasi', 'value' => $communications->count(), 'icon' => 'heroicon-o-chat-bubble-left-right', 'color' => 'text-blue-600 dark:text-blue-400'],
            ['label' => 'Meeting', 'value' => $communications->where('type', 'meeting')->count(), 'icon' => 'heroicon-o-calendar', 'color' => 'text-green-600 dark:text-green-400'],
            ['label' => 'Email', 'value' => $communications->where('type', 'email')->count(), 'icon' => 'heroicon-o-envelope', 'color' => 'text-purple-600 dark:text-purple-400'],
            ['label' => 'Telepon', 'value' => $communications->where('type', 'phone')->count(), 'icon' => 'heroicon-o-phone', 'color' => 'text-orange-600 dark:text-orange-400'],
        ];
        @endphp

        @foreach($stats as $stat)
        <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        {{ $stat['label'] }}
                    </p>
                    <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $stat['value'] }}
                    </p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                    <x-filament::icon :icon="$stat['icon']" class="h-6 w-6 {{ $stat['color'] }}" />
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Communication Timeline (Read-Only) --}}
    <div class="space-y-4">
        @forelse($communications as $index => $communication)
        <div wire:click="openDetailModal({{ $communication->id }})"
            class="group relative cursor-pointer rounded-xl bg-white shadow-sm transition-all hover:shadow-lg hover:scale-[1.01] dark:bg-gray-800">
            {{-- Timeline Line --}}
            @if(!$loop->last)
            <div class="absolute left-[52px] top-[60px] h-full w-px bg-gray-200 dark:bg-gray-700"></div>
            @endif

            <div class="p-6">
                <div class="flex gap-4">
                    {{-- Icon with Timeline Dot --}}
                    <div class="relative flex-shrink-0">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-gray-900 to-gray-700 text-white shadow-md transition-transform group-hover:scale-110 dark:from-white dark:to-gray-100 dark:text-gray-900">
                            <x-filament::icon :icon="$communication->type_icon" class="h-7 w-7" />
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $communication->title }}
                                    </h3>

                                    {{-- Type Badge --}}
                                    <span
                                        class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        {{ $communication->type_label }}
                                    </span>
                                </div>

                                <div
                                    class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <span class="flex items-center gap-1.5">
                                        <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                                        {{ $communication->communication_date->format('d M Y') }}
                                    </span>

                                    @if($communication->communication_time_start)
                                    <span class="flex items-center gap-1.5">
                                        <x-filament::icon icon="heroicon-o-clock" class="h-4 w-4" />
                                        {{ \Carbon\Carbon::parse($communication->communication_time_start)->format('H:i') }}
                                        @if($communication->communication_time_end)
                                            - {{ \Carbon\Carbon::parse($communication->communication_time_end)->format('H:i') }}
                                        @endif
                                    </span>
                                    @endif

                                    <span class="flex items-center gap-1.5">
                                        <x-filament::icon icon="heroicon-o-user" class="h-4 w-4" />
                                        {{ $communication->user->name }}
                                    </span>

                                    @if(!empty($communication->attachments) && is_array($communication->attachments))
                                    <span class="flex items-center gap-1.5 text-blue-600 dark:text-blue-400">
                                        <x-filament::icon icon="heroicon-o-paper-clip" class="h-4 w-4" />
                                        {{ count($communication->attachments) }} file
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Preview Description --}}
                        @if($communication->description)
                        <div class="mt-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-900/50">
                            <div class="prose prose-sm max-w-none text-gray-700 dark:text-gray-300 line-clamp-3">
                                {!! $communication->description !!}
                            </div>
                        </div>
                        @endif

                        {{-- View Detail Button --}}
                        <div class="mt-4">
                            <span
                                class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                Lihat Detail
                                <x-filament::icon icon="heroicon-o-arrow-right" class="h-4 w-4" />
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="rounded-2xl bg-white p-16 text-center shadow-sm dark:bg-gray-800">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                <x-filament::icon icon="heroicon-o-chat-bubble-left-right"
                    class="h-10 w-10 text-gray-400 dark:text-gray-500" />
            </div>
            <h3 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">
                Belum ada catatan komunikasi
            </h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Belum ada komunikasi yang tercatat dengan klien ini.
            </p>
        </div>
        @endforelse
    </div>

    {{-- Detail Modal (Read-Only) --}}
    <x-filament::modal id="detail-communication-modal" width="4xl">
        @if($viewingCommunication)
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-gray-900 to-gray-700 text-white shadow-md dark:from-white dark:to-gray-100 dark:text-gray-900">
                    <x-filament::icon :icon="$viewingCommunication->type_icon" class="h-5 w-5" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold">{{ $viewingCommunication->title }}</h3>
                    <p class="text-sm font-normal text-gray-600 dark:text-gray-400">
                        Detail Komunikasi
                    </p>
                </div>
            </div>
        </x-slot>

        <div class="space-y-6">
            {{-- Info Section --}}
            <div class="grid grid-cols-2 gap-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-900/50">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Jenis</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $viewingCommunication->type_label }}
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Tanggal & Waktu</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $viewingCommunication->communication_date->format('d M Y') }}
                        @if($viewingCommunication->communication_time_start)
                        • {{ \Carbon\Carbon::parse($viewingCommunication->communication_time_start)->format('H:i') }}
                        @if($viewingCommunication->communication_time_end)
                        - {{ \Carbon\Carbon::parse($viewingCommunication->communication_time_end)->format('H:i') }}
                        @endif
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Dibuat Oleh</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $viewingCommunication->user->name }}
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Tanggal Dibuat</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $viewingCommunication->created_at->format('d M Y H:i') }}
                    </p>
                </div>
            </div>

            {{-- Description --}}
            @if($viewingCommunication->description)
            <div>
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Deskripsi</h4>
                <div
                    class="prose prose-sm max-w-none rounded-lg bg-white p-4 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    {!! $viewingCommunication->description !!}
                </div>
            </div>
            @endif

            {{-- Attachments --}}
            @if(!empty($viewingCommunication->attachments) && is_array($viewingCommunication->attachments))
            <div>
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">File Lampiran</h4>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach($viewingCommunication->attachments as $file)
                    <div
                        class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                        <div
                            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                            <x-filament::icon icon="heroicon-o-document"
                                class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                {{ basename($file) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @if(\Storage::disk('public')->exists($file))
                                {{ number_format(\Storage::disk('public')->size($file) / 1024, 2) }} KB
                                @else
                                File tidak ditemukan
                                @endif
                            </p>
                        </div>
                        <a href="{{ \Storage::disk('public')->url($file) }}" download target="_blank"
                            class="flex-shrink-0">
                            <x-filament::icon-button icon="heroicon-o-arrow-down-tray" label="Download" color="gray"
                                size="sm" />
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Notes --}}
            @if($viewingCommunication->notes)
            <div>
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Catatan Tambahan</h4>
                <div
                    class="prose prose-sm max-w-none rounded-lg border-l-4 border-gray-900 bg-gray-50 p-4 text-gray-700 dark:border-gray-100 dark:bg-gray-900/50 dark:text-gray-300">
                    {!! $viewingCommunication->notes !!}
                </div>
            </div>
            @endif
        </div>

        <x-slot name="footer">
            <div class="flex justify-end">
                <x-filament::button color="gray" wire:click="closeDetailModal">
                    Tutup
                </x-filament::button>
            </div>
        </x-slot>
        @endif
    </x-filament::modal>
</div>