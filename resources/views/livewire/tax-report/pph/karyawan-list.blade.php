<div>
    {{-- Statistics Cards --}}
    <div class="mb-6 grid gap-4 sm:gap-6 md:grid-cols-2 xl:grid-cols-4">

        {{-- Total Karyawan Card --}}
        <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-700">
            <div class="flex items-start justify-between mb-3">
                <span class="text-sm text-gray-600 dark:text-gray-400">Total Karyawan</span>
                <div class="rounded-lg bg-blue-50 p-1.5 dark:bg-blue-500/10">
                    <x-filament::icon icon="heroicon-o-user-group" class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                {{ $totalKaryawan }}
            </p>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    Semua Status
                </span>
            </div>
        </div>

        {{-- Karyawan Aktif Card --}}
        <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-700">
            <div class="flex items-start justify-between mb-3">
                <span class="text-sm text-gray-600 dark:text-gray-400">Karyawan Aktif</span>
                <div class="rounded-lg bg-green-50 p-1.5 dark:bg-green-500/10">
                    <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4 text-green-600 dark:text-green-400" />
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                {{ $activeKaryawan }}
            </p>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    Status Aktif
                </span>
            </div>
        </div>

        {{-- Karyawan Tidak Aktif Card --}}
        <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-700">
            <div class="flex items-start justify-between mb-3">
                <span class="text-sm text-gray-600 dark:text-gray-400">Tidak Aktif</span>
                <div class="rounded-lg bg-red-50 p-1.5 dark:bg-red-500/10">
                    <x-filament::icon icon="heroicon-o-x-circle" class="h-4 w-4 text-red-600 dark:text-red-400" />
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                {{ $inactiveKaryawan }}
            </p>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    Status Tidak Aktif
                </span>
            </div>
        </div>

        {{-- Total Gaji Card --}}
        <div class="rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 p-6 shadow-lg text-white">
            <div class="flex items-start justify-between mb-3">
                <span class="text-sm text-blue-100">Total Gaji</span>
                <div class="rounded-lg bg-white/20 p-1.5 backdrop-blur-sm">
                    <x-filament::icon icon="heroicon-o-banknotes" class="h-4 w-4 text-white" />
                </div>
            </div>
            <p class="text-2xl font-bold mb-2">
                Rp {{ number_format($totalGaji, 0, ',', '.') }}
            </p>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-xs text-blue-100">
                    <span class="w-2 h-2 rounded-full bg-white"></span>
                    Karyawan Aktif
                </span>
            </div>
        </div>

    </div>

    {{-- Table Section --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="p-6">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Karyawan</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Kelola data karyawan untuk {{ $client?->name ?? 'klien ini' }}
                    </p>
                </div>
            </div>

            {{ $this->table }}
        </div>
    </div>

    {{-- Info Section --}}
    <div class="mt-6 rounded-xl bg-blue-50 p-4 border border-blue-100 dark:bg-blue-500/5 dark:border-blue-500/20">
        <div class="flex gap-3">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-medium text-blue-900 dark:text-blue-300 mb-1">Informasi Status PTKP
                </h4>
                <p class="text-xs text-blue-700 dark:text-blue-400 leading-relaxed">
                    TK = Tidak Kawin (Belum Menikah) • K = Kawin (Menikah) • Angka setelah TK/K menunjukkan jumlah tanggungan (0-3)
                </p>
            </div>
        </div>
    </div>
</div>