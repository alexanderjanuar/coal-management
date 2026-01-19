<div class="space-y-6">
    {{-- Header Section --}}
    <div
        class="flex items-center justify-between rounded-2xl bg-gradient-to-r from-gray-50 to-gray-100 p-6 dark:from-gray-800 dark:to-gray-900">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Data Karyawan
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Kelola data karyawan untuk {{ $client->name }}
            </p>
        </div>

        <x-filament::button wire:click="openCreateModal" icon="heroicon-o-plus" size="lg">
            Tambah Karyawan
        </x-filament::button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
        @php
        $statsConfig = [
        ['label' => 'Total Karyawan', 'value' => $stats['total'] ?? 0, 'icon' => 'heroicon-o-users', 'color' =>
        'text-blue-600 dark:text-blue-400'],
        ['label' => 'Karyawan Aktif', 'value' => $stats['active'] ?? 0, 'icon' => 'heroicon-o-check-circle', 'color' =>
        'text-green-600 dark:text-green-400'],
        ['label' => 'Tidak Aktif', 'value' => $stats['inactive'] ?? 0, 'icon' => 'heroicon-o-x-circle', 'color' =>
        'text-gray-600 dark:text-gray-400'],
        ['label' => 'Karyawan Tetap', 'value' => $stats['tetap'] ?? 0, 'icon' => 'heroicon-o-briefcase', 'color' =>
        'text-purple-600 dark:text-purple-400'],
        ];
        @endphp

        @foreach($statsConfig as $stat)
        <div class="rounded-xl bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:bg-gray-800">
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

    {{-- Employees Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Nama & NPWP
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Jabatan
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Tipe
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Status PTKP
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Gaji
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Status
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse($employees as $employee)
                    <tr
                        class="group transition-all duration-200 hover:bg-gradient-to-r hover:from-gray-50 hover:to-transparent dark:hover:from-gray-700/30 dark:hover:to-transparent hover:shadow-sm">
                        {{-- Nama & NPWP --}}
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-md transition-transform group-hover:scale-110">
                                    <x-filament::icon icon="heroicon-o-user" class="h-6 w-6" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">
                                        {{ $employee->name }}
                                    </p>
                                    @if($employee->npwp)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $employee->npwp }}
                                    </p>
                                    @else
                                    <p class="text-xs italic text-gray-400 dark:text-gray-500">
                                        NPWP belum diisi
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Jabatan --}}
                        <td class="whitespace-nowrap px-6 py-4">
                            @if($employee->position)
                            <div
                                class="inline-flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-900/50">
                                <x-filament::icon icon="heroicon-o-briefcase"
                                    class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $employee->position }}
                                </span>
                            </div>
                            @else
                            <span class="text-sm italic text-gray-400 dark:text-gray-500">-</span>
                            @endif
                        </td>

                        {{-- Tipe --}}
                        <td class="whitespace-nowrap px-6 py-4">
                            @php
                            $typeConfig = [
                            'Karyawan Tetap' => [
                            'class' => 'bg-purple-100 text-purple-700 ring-2 ring-purple-200 dark:bg-purple-900/40
                            dark:text-purple-300 dark:ring-purple-800',
                            'icon' => 'heroicon-o-check-badge'
                            ],
                            'Harian' => [
                            'class' => 'bg-blue-100 text-blue-700 ring-2 ring-blue-200 dark:bg-blue-900/40
                            dark:text-blue-300 dark:ring-blue-800',
                            'icon' => 'heroicon-o-calendar-days'
                            ]
                            ];
                            $typeConf = $typeConfig[$employee->type] ?? $typeConfig['Harian'];
                            @endphp

                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold shadow-sm {{ $typeConf['class'] }}">
                                <x-filament::icon :icon="$typeConf['icon']" class="h-4 w-4" />
                                {{ $employee->type }}
                            </span>
                        </td>

                        {{-- Status PTKP --}}
                        <td class="whitespace-nowrap px-6 py-4">
                            @php
                            $ptkpLabel = $employee->marital_status === 'single' ? 'TK/' . $employee->tk : 'K/' .
                            $employee->k;
                            $ptkpDescription = $employee->marital_status === 'single' ? 'Belum Menikah' : 'Menikah';
                            @endphp

                            <div x-data="{ ptkpTooltip: false }" class="relative inline-block">
                                <span @mouseenter="ptkpTooltip = true" @mouseleave="ptkpTooltip = false"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3.5 py-1.5 text-xs font-semibold text-gray-700 shadow-sm ring-2 ring-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600">
                                    <x-filament::icon
                                        :icon="$employee->marital_status === 'married' ? 'heroicon-o-user-group' : 'heroicon-o-user'"
                                        class="h-4 w-4" />
                                    {{ $ptkpLabel }}
                                </span>

                                {{-- Tooltip --}}
                                <div x-show="ptkpTooltip" x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 transform scale-95"
                                    x-transition:enter-end="opacity-100 transform scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 transform scale-100"
                                    x-transition:leave-end="opacity-0 transform scale-95"
                                    class="absolute bottom-full left-1/2 z-50 mb-2 -translate-x-1/2 whitespace-nowrap rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg dark:bg-gray-100 dark:text-gray-900"
                                    style="display: none;">
                                    {{ $ptkpDescription }} - {{ $employee->marital_status === 'single' ? $employee->tk :
                                    $employee->k }} Tanggungan
                                    <div
                                        class="absolute left-1/2 top-full h-2 w-2 -translate-x-1/2 rotate-45 transform bg-gray-900 dark:bg-gray-100">
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Gaji --}}
                        <td class="whitespace-nowrap px-6 py-4">
                            @if($employee->salary)
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                Rp {{ number_format($employee->salary, 0, ',', '.') }}
                            </p>
                            @else
                            <span class="text-sm italic text-gray-400 dark:text-gray-500">-</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="whitespace-nowrap px-6 py-4">
                            @php
                            $statusConfig = [
                            'active' => [
                            'class' => 'bg-green-100 text-green-700 ring-2 ring-green-200 dark:bg-green-900/40
                            dark:text-green-300 dark:ring-green-800',
                            'icon' => 'heroicon-o-check-circle',
                            'label' => 'Aktif'
                            ],
                            'inactive' => [
                            'class' => 'bg-gray-100 text-gray-700 ring-2 ring-gray-200 dark:bg-gray-700
                            dark:text-gray-300 dark:ring-gray-600',
                            'icon' => 'heroicon-o-x-circle',
                            'label' => 'Tidak Aktif'
                            ]
                            ];
                            $statusConf = $statusConfig[$employee->status] ?? $statusConfig['active'];
                            @endphp

                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold shadow-sm {{ $statusConf['class'] }}">
                                <x-filament::icon :icon="$statusConf['icon']" class="h-4 w-4" />
                                {{ $statusConf['label'] }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="whitespace-nowrap px-6 py-4 text-right">
                            <div
                                class="flex items-center justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100">
                                <x-filament::icon-button wire:click="openEditModal({{ $employee->id }})"
                                    icon="heroicon-o-pencil" label="Edit" color="gray" size="sm" />

                                <x-filament::icon-button wire:click="deleteConfirm({{ $employee->id }})"
                                    icon="heroicon-o-trash" label="Hapus" color="danger" size="sm" />
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div
                                class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                                <x-filament::icon icon="heroicon-o-users"
                                    class="h-10 w-10 text-gray-400 dark:text-gray-500" />
                            </div>
                            <h3 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">
                                Belum ada data karyawan
                            </h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Tambahkan karyawan untuk mulai mengelola data pegawai klien ini.
                            </p>
                            <div class="mt-8">
                                <x-filament::button wire:click="openCreateModal" icon="heroicon-o-plus" size="lg">
                                    Tambah Karyawan Pertama
                                </x-filament::button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <x-filament::modal id="employee-modal" width="3xl">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-white dark:bg-blue-500">
                    <x-filament::icon icon="heroicon-o-user-plus" class="h-5 w-5" />
                </div>
                <span>{{ $editingId ? 'Edit Data Karyawan' : 'Tambah Karyawan Baru' }}</span>
            </div>
        </x-slot>

        <form wire:submit="saveEmployee">
            {{ $this->form }}

            <x-slot name="footer">
                <div class="flex gap-3 justify-end">
                    <x-filament::button color="gray" wire:click="closeModal" type="button" outlined>
                        Batal
                    </x-filament::button>

                    <x-filament::button type="submit" icon="heroicon-o-check" wire:click="saveEmployee">
                        {{ $editingId ? 'Perbarui' : 'Simpan' }}
                    </x-filament::button>
                </div>
            </x-slot>
        </form>
    </x-filament::modal>

    {{-- Delete Confirmation Modal --}}
    <x-filament::modal id="delete-employee-modal" width="md">
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
                Apakah Anda yakin ingin menghapus data karyawan ini?
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

                <x-filament::button color="danger" wire:click="deleteEmployee" icon="heroicon-o-trash">
                    Hapus
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</div>