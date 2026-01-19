<div class="space-y-6">
    {{-- Header Section --}}
    <div
        class="flex items-center justify-between rounded-2xl bg-gradient-to-r from-gray-50 to-gray-100 p-6 dark:from-gray-800 dark:to-gray-900">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Tim Konsultan
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Kelola tim konsultan untuk setiap project {{ $client->name }}
            </p>
        </div>

        <x-filament::button wire:click="openAssignModal" icon="heroicon-o-plus" size="lg">
            Assign Konsultan
        </x-filament::button>
    </div>

    {{-- Projects List --}}
    <div class="space-y-6">
        @forelse($projects as $project)
        <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-800">
            {{-- Project Header --}}
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $project->name }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ $project->userProjects->count() }} Anggota Tim
                        </p>
                    </div>

                    <x-filament::button wire:click="openAssignModal({{ $project->id }})" icon="heroicon-o-user-plus"
                        color="gray" size="sm" outlined>
                        Tambah Anggota
                    </x-filament::button>
                </div>
            </div>

            {{-- Team Members --}}
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($project->userProjects as $membership)
                <div class="group px-6 py-5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-900/30">
                    <div class="flex items-start gap-4">
                        {{-- Avatar --}}
                        <div class="flex-shrink-0">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-900 text-white text-sm font-semibold dark:bg-white dark:text-gray-900">
                                {{ $membership->initials }}
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">
                                        {{ $membership->user->name }}
                                    </h4>

                                    @if($membership->role)
                                    <p class="mt-1 text-sm font-medium text-gray-600 dark:text-gray-400">
                                        {{ $membership->role }}
                                    </p>
                                    @endif

                                    @if($membership->specializations)
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        @foreach(is_array($membership->specializations) ? $membership->specializations :
                                        [$membership->specializations] as $spec)
                                        <span
                                            class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                            {{ $spec }}
                                        </span>
                                        @endforeach
                                    </div>
                                    @endif

                                    @if($membership->assigned_date)
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                                        Sejak {{ $membership->assigned_date->format('d M Y') }}
                                    </p>
                                    @endif
                                </div>

                                {{-- Actions --}}
                                <div class="flex gap-2 opacity-0 transition-opacity group-hover:opacity-100">
                                    <x-filament::icon-button wire:click="openEditModal({{ $membership->id }})"
                                        icon="heroicon-o-pencil" label="Edit" color="gray" size="sm" />

                                    <x-filament::icon-button wire:click="deleteConfirm({{ $membership->id }})"
                                        icon="heroicon-o-trash" label="Hapus" color="danger" size="sm" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-12 text-center">
                    <x-filament::icon icon="heroicon-o-users" class="mx-auto h-10 w-10 text-gray-400" />
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                        Belum ada anggota tim untuk project ini
                    </p>
                    <div class="mt-4">
                        <x-filament::button wire:click="openAssignModal({{ $project->id }})" icon="heroicon-o-user-plus"
                            size="sm" color="gray" outlined>
                            Tambah Anggota Pertama
                        </x-filament::button>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
        @empty
        <div class="rounded-2xl bg-white p-16 text-center shadow-sm dark:bg-gray-800">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                <x-filament::icon icon="heroicon-o-briefcase" class="h-10 w-10 text-gray-400 dark:text-gray-500" />
            </div>
            <h3 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">
                Belum ada project
            </h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Client ini belum memiliki project. Buat project terlebih dahulu untuk menambahkan tim.
            </p>
        </div>
        @endforelse
    </div>

    {{-- Assign/Edit Modal --}}
    <x-filament::modal id="assign-member-modal" width="4xl">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-900 text-white dark:bg-white dark:text-gray-900">
                    <x-filament::icon icon="heroicon-o-user-plus" class="h-5 w-5" />
                </div>
                <span>{{ $editingId ? 'Edit Anggota Tim' : 'Assign Konsultan' }}</span>
            </div>
        </x-slot>

        <form wire:submit="saveMember">
            {{ $this->form }}

            <x-slot name="footer">
                <div class="flex gap-3 justify-end">
                    <x-filament::button color="gray" wire:click="closeModal" type="button" outlined>
                        Batal
                    </x-filament::button>

                    <x-filament::button type="submit" wire:click="saveMember" icon="heroicon-o-check">
                        {{ $editingId ? 'Perbarui' : 'Assign' }}
                    </x-filament::button>
                </div>
            </x-slot>
        </form>
    </x-filament::modal>

    {{-- Delete Confirmation Modal --}}
    <x-filament::modal id="delete-member-modal" width="xl">
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
                Apakah Anda yakin ingin menghapus anggota tim ini dari project?
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

                <x-filament::button color="danger" wire:click="deleteMember" icon="heroicon-o-trash">
                    Hapus
                </x-filament::button>
        </x-slot>
    </x-filament::modal>
</div>