<div class="space-y-6">
    {{-- Header Section --}}
    <div
        class="flex items-center justify-between rounded-2xl bg-gradient-to-r from-gray-50 to-gray-100 p-6 dark:from-gray-800 dark:to-gray-900">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Riwayat Komunikasi & Catatan
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Dokumentasi seluruh interaksi dengan klien {{ $client->name }}
            </p>
        </div>

        <x-filament::button wire:click="openCreateModal" icon="heroicon-o-plus" size="lg">
            Tambah Catatan
        </x-filament::button>
    </div>

    {{-- Stats Cards (Optional) --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
        @php
        $stats = [
        ['label' => 'Total Komunikasi', 'value' => $communications->count(), 'icon' =>
        'heroicon-o-chat-bubble-left-right'],
        ['label' => 'Meeting', 'value' => $communications->where('type', 'meeting')->count(), 'icon' =>
        'heroicon-o-calendar'],
        ['label' => 'Email', 'value' => $communications->where('type', 'email')->count(), 'icon' =>
        'heroicon-o-envelope'],
        ['label' => 'Telepon', 'value' => $communications->where('type', 'phone')->count(), 'icon' =>
        'heroicon-o-phone'],
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
                    <x-filament::icon :icon="$stat['icon']" class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Communication Timeline --}}
    <div class="space-y-4">
        @forelse($communications as $index => $communication)
        <div class="group relative rounded-xl bg-white shadow-sm transition-all hover:shadow-md dark:bg-gray-800">
            {{-- Timeline Line --}}
            @if(!$loop->last)
            <div class="absolute left-[52px] top-[60px] h-full w-px bg-gray-200 dark:bg-gray-700"></div>
            @endif

            <div class="p-6">
                <div class="flex gap-4">
                    {{-- Icon with Timeline Dot --}}
                    <div class="relative flex-shrink-0">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-xl bg-gray-900 text-white shadow-lg dark:bg-white dark:text-gray-900">
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
                                        class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        {{ $communication->type_label }}
                                    </span>
                                </div>

                                <div
                                    class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <span class="flex items-center gap-1.5">
                                        <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                                        {{ $communication->communication_date->format('d M Y') }}
                                    </span>

                                    @if($communication->communication_time)
                                    <span class="flex items-center gap-1.5">
                                        <x-filament::icon icon="heroicon-o-clock" class="h-4 w-4" />
                                        {{ \Carbon\Carbon::parse($communication->communication_time)->format('H:i') }}
                                    </span>
                                    @endif

                                    <span class="flex items-center gap-1.5">
                                        <x-filament::icon icon="heroicon-o-user" class="h-4 w-4" />
                                        {{ $communication->user->name }}
                                    </span>
                                </div>
                            </div>

                            {{-- Actions (Show on Hover) --}}
                            <div class="flex gap-2 opacity-0 transition-opacity group-hover:opacity-100">
                                <x-filament::icon-button wire:click="openEditModal({{ $communication->id }})"
                                    icon="heroicon-o-pencil" label="Edit" color="gray" size="sm" />

                                <x-filament::icon-button wire:click="deleteConfirm({{ $communication->id }})"
                                    icon="heroicon-o-trash" label="Hapus" color="danger" size="sm" />
                            </div>
                        </div>

                        @if($communication->description)
                        <div class="mt-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-900/50">
                            <p class="text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                                {{ $communication->description }}
                            </p>
                        </div>
                        @endif

                        @if($communication->notes)
                        <div class="mt-3 border-l-4 border-gray-900 pl-4 dark:border-gray-100">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                Catatan:
                            </p>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                {{ $communication->notes }}
                            </p>
                        </div>
                        @endif
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
                Mulai dokumentasikan komunikasi Anda dengan klien ini untuk tracking yang lebih baik.
            </p>
            <div class="mt-8">
                <x-filament::button wire:click="openCreateModal" icon="heroicon-o-plus" size="lg">
                    Tambah Catatan Pertama
                </x-filament::button>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Pagination (if needed) --}}
    @if($communications->count() > 10)
    <div class="flex justify-center">
        <x-filament::button color="gray" outlined icon="heroicon-o-arrow-down">
            Muat Lebih Banyak
        </x-filament::button>
    </div>
    @endif

    {{-- Create/Edit Modal --}}
    <x-filament::modal id="communication-modal" width="3xl">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-900 text-white dark:bg-white dark:text-gray-900">
                    <x-filament::icon icon="heroicon-o-pencil-square" class="h-5 w-5" />
                </div>
                <span>{{ $editingId ? 'Edit Catatan Komunikasi' : 'Tambah Catatan Komunikasi' }}</span>
            </div>
        </x-slot>

        <form wire:submit="saveCommunication">
            {{ $this->form }}

            <x-slot name="footer">
                <div class="flex gap-3 justify-end">
                    <x-filament::button color="gray" wire:click="closeModal" type="button" outlined>
                        Batal
                    </x-filament::button>

                    <x-filament::button type="submit" icon="heroicon-o-check" wire:click="saveCommunication">
                        {{ $editingId ? 'Perbarui' : 'Simpan' }}
                    </x-filament::button>
                </div>
            </x-slot>
        </form>
    </x-filament::modal>

    {{-- Delete Confirmation Modal --}}
    <x-filament::modal id="delete-communication-modal" width="md">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                    <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5" />
                </div>
                <span>Konfirmasi Hapus</span>
            </div>
        </x-slot>

        <div class="py-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Apakah Anda yakin ingin menghapus catatan komunikasi ini?
            </p>
            <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                Tindakan ini tidak dapat dibatalkan.
            </p>
        </div>

        <x-slot name="footer">
            <div class="flex gap-3 justify-end">
                <x-filament::button color="gray" wire:click="closeDeleteModal" outlined>
                    Batal
                </x-filament::button>

                <x-filament::button color="danger" wire:click="deleteCommunication" icon="heroicon-o-trash">
                    Hapus
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</div>