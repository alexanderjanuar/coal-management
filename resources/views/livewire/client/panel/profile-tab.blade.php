<div class="space-y-4 sm:space-y-6" x-data="{ mounted: false, showCredentials: @entangle('showCredentials') }" x-init="setTimeout(() => mounted = true, 100)">

    @if($clients->isEmpty())
    {{-- No Client Warning --}}
    <div class="relative overflow-hidden rounded-xl sm:rounded-2xl border border-amber-200/50 bg-gradient-to-br from-amber-50 via-white to-orange-50 p-5 sm:p-8 shadow-sm dark:border-amber-900/30 dark:from-amber-950/20 dark:via-gray-900 dark:to-orange-950/20"
        x-show="mounted" x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100">
        <div class="relative flex flex-col sm:flex-row items-start gap-4 sm:gap-5">
            <div class="flex-shrink-0">
                <div class="flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-xl sm:rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 shadow-lg shadow-amber-500/25">
                    <x-heroicon-o-exclamation-triangle class="h-6 w-6 sm:h-7 sm:w-7 text-white" />
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100">
                    Akun Belum Terhubung
                </h3>
                <p class="mt-2 text-xs sm:text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                    Akun Anda belum terhubung dengan data klien. Silakan hubungi administrator.
                </p>
            </div>
        </div>
    </div>
    @else

    {{-- Client Selector --}}
    @if($clients->count() > 1)
    <div class="relative overflow-hidden rounded-xl sm:rounded-2xl border border-gray-200/60 bg-white/80 p-4 sm:p-5 shadow-sm backdrop-blur-sm dark:border-gray-700/60 dark:bg-gray-800/80"
        x-show="mounted" x-transition:enter="transition ease-out duration-400"
        x-transition:enter-start="opacity-0 transform -translate-y-3"
        x-transition:enter-end="opacity-100 transform translate-y-0">
        <div class="flex flex-col sm:flex-row sm:flex-wrap items-start sm:items-center gap-3 sm:gap-4">
            <div class="flex items-center gap-2 sm:gap-2.5 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400">
                <div class="flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                    <x-heroicon-o-building-office-2 class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                </div>
                <span>Pilih Perusahaan:</span>
            </div>
            <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                @foreach($clients as $client)
                <button wire:click="selectClient({{ $client->id }})"
                    class="group relative rounded-lg sm:rounded-xl px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium transition-all duration-300
                    {{ $selectedClientId === $client->id
                        ? 'bg-primary-600 text-white shadow-lg shadow-primary-500/25 hover:bg-primary-700'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                    }}">
                    {{ $client->name }}
                </button>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($selectedClient)
    {{-- Profile Header Card --}}
    <div class="relative overflow-hidden rounded-xl sm:rounded-2xl border border-gray-200/60 bg-gradient-to-br from-white via-gray-50/50 to-primary-50/30 shadow-sm dark:border-gray-700/60 dark:from-gray-800 dark:via-gray-800/50 dark:to-primary-900/10"
        x-show="mounted" x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 transform -translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0">

        {{-- Decorative Elements --}}
        <div class="absolute right-0 top-0 -mr-20 -mt-20 h-64 w-64 rounded-full bg-gradient-to-br from-primary-400/10 to-transparent blur-3xl"></div>
        <div class="absolute left-0 bottom-0 -ml-20 -mb-20 h-48 w-48 rounded-full bg-gradient-to-tr from-cyan-400/5 to-transparent blur-2xl"></div>

        <div class="relative p-5 sm:p-8">
            <div class="flex flex-col lg:flex-row items-start gap-5 sm:gap-8">
                {{-- Logo & Basic Info --}}
                <div class="flex items-start gap-4 sm:gap-6 w-full lg:w-auto">
                    @if($selectedClient->logo)
                    <div class="relative flex-shrink-0">
                        <img src="{{ Storage::url($selectedClient->logo) }}" alt="{{ $selectedClient->name }}"
                            class="h-16 w-16 sm:h-24 sm:w-24 rounded-xl sm:rounded-2xl object-cover shadow-xl ring-4 ring-white dark:ring-gray-700">
                        <div class="absolute -bottom-1 -right-1 h-4 w-4 sm:h-5 sm:w-5 rounded-full border-2 border-white
                            {{ $selectedClient->status === 'Active' ? 'bg-emerald-500' : 'bg-gray-400' }} dark:border-gray-800"></div>
                    </div>
                    @else
                    <div class="flex h-16 w-16 sm:h-24 sm:w-24 flex-shrink-0 items-center justify-center rounded-xl sm:rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 shadow-xl shadow-primary-500/25">
                        <x-heroicon-o-building-office-2 class="h-8 w-8 sm:h-12 sm:w-12 text-white" />
                    </div>
                    @endif

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                            <h1 class="text-xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                                {{ $selectedClient->name }}
                            </h1>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 sm:px-3 sm:py-1 text-[10px] sm:text-xs font-semibold
                                {{ $selectedClient->status === 'Active'
                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400'
                                    : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                {{ $selectedClient->status }}
                            </span>
                        </div>

                        <div class="mt-2 sm:mt-3 flex flex-wrap items-center gap-2 sm:gap-4">
                            @if($selectedClient->client_type)
                            <span class="inline-flex items-center gap-1 sm:gap-1.5 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                                <x-heroicon-o-tag class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                {{ $selectedClient->formatted_client_type }}
                            </span>
                            @endif

                            @if($selectedClient->pkp_status)
                            <span class="inline-flex items-center gap-1 sm:gap-1.5 rounded-md px-2 py-0.5 text-[10px] sm:text-xs font-medium
                                {{ $selectedClient->pkp_status === 'PKP'
                                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400'
                                    : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                {{ $selectedClient->pkp_status }}
                            </span>
                            @endif
                        </div>

                        @if($selectedClient->email)
                        <p class="mt-2 flex items-center gap-1.5 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                            <x-heroicon-o-envelope class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                            {{ $selectedClient->email }}
                        </p>
                        @endif
                    </div>
                </div>

                {{-- Refresh Button --}}
                <div class="lg:ml-auto flex-shrink-0">
                    <button wire:click="refresh"
                        class="group flex h-9 w-9 sm:h-11 sm:w-11 items-center justify-center rounded-lg sm:rounded-xl bg-white/80 text-gray-500 shadow-sm ring-1 ring-gray-200/60 backdrop-blur-sm transition-all duration-300 hover:bg-primary-50 hover:text-primary-600 hover:ring-primary-200 dark:bg-gray-700/80 dark:ring-gray-600/60 dark:hover:bg-primary-900/30 dark:hover:text-primary-400"
                        title="Refresh Data">
                        <x-heroicon-o-arrow-path class="h-4 w-4 sm:h-5 sm:w-5 transition-transform duration-500 group-hover:rotate-180" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid gap-4 sm:gap-6 lg:grid-cols-2">

        {{-- Company Information Card --}}
        <div class="overflow-hidden rounded-xl sm:rounded-2xl border border-gray-200/60 bg-white shadow-sm dark:border-gray-700/50 dark:bg-gray-900"
            x-show="mounted" x-transition:enter="transition ease-out duration-500 delay-100"
            x-transition:enter-start="opacity-0 transform translate-y-6"
            x-transition:enter-end="opacity-100 transform translate-y-0">

            {{-- Header --}}
            <div class="border-b border-gray-200/60 bg-gradient-to-r from-gray-50 to-white px-4 py-3 sm:px-6 sm:py-4 dark:border-gray-700/50 dark:from-gray-800/80 dark:to-gray-900">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-lg sm:rounded-xl bg-primary-600 shadow-md shadow-primary-500/20 dark:bg-primary-500">
                        <x-heroicon-o-building-office class="h-4 w-4 sm:h-5 sm:w-5 text-white" />
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-gray-50">Informasi Perusahaan</h3>
                        <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">Data umum perusahaan</p>
                    </div>
                </div>
            </div>

            {{-- Content --}}
            <div class="p-4 sm:p-6">
                <dl class="space-y-4 sm:space-y-5">
                    {{-- NPWP --}}
                    <div class="flex items-start gap-3 sm:gap-4">
                        <div class="flex h-9 w-9 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                            <x-heroicon-o-identification class="h-4 w-4 sm:h-5 sm:w-5 text-gray-500 dark:text-gray-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <dt class="text-[10px] sm:text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">NPWP</dt>
                            <dd class="mt-0.5 text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100">
                                {{ $selectedClient->NPWP ?: '-' }}
                            </dd>
                        </div>
                    </div>

                    {{-- EFIN --}}
                    <div class="flex items-start gap-3 sm:gap-4">
                        <div class="flex h-9 w-9 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                            <x-heroicon-o-key class="h-4 w-4 sm:h-5 sm:w-5 text-gray-500 dark:text-gray-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <dt class="text-[10px] sm:text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">EFIN</dt>
                            <dd class="mt-0.5 text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100">
                                {{ $selectedClient->EFIN ?: '-' }}
                            </dd>
                        </div>
                    </div>

                    {{-- Address --}}
                    <div class="flex items-start gap-3 sm:gap-4">
                        <div class="flex h-9 w-9 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                            <x-heroicon-o-map-pin class="h-4 w-4 sm:h-5 sm:w-5 text-gray-500 dark:text-gray-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <dt class="text-[10px] sm:text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Alamat</dt>
                            <dd class="mt-0.5 text-sm sm:text-base text-gray-900 dark:text-gray-100 leading-relaxed">
                                {{ $selectedClient->adress ?: '-' }}
                            </dd>
                        </div>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Contacts Card --}}
        <div class="overflow-hidden rounded-xl sm:rounded-2xl border border-gray-200/60 bg-white shadow-sm dark:border-gray-700/50 dark:bg-gray-900"
            x-show="mounted" x-transition:enter="transition ease-out duration-500 delay-150"
            x-transition:enter-start="opacity-0 transform translate-y-6"
            x-transition:enter-end="opacity-100 transform translate-y-0">

            {{-- Header --}}
            <div class="border-b border-gray-200/60 bg-gradient-to-r from-gray-50 to-white px-4 py-3 sm:px-6 sm:py-4 dark:border-gray-700/50 dark:from-gray-800/80 dark:to-gray-900">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-lg sm:rounded-xl bg-cyan-600 shadow-md shadow-cyan-500/20 dark:bg-cyan-500">
                        <x-heroicon-o-users class="h-4 w-4 sm:h-5 sm:w-5 text-white" />
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-gray-50">Kontak Penanggung Jawab</h3>
                        <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">PIC dan Account Representative</p>
                    </div>
                </div>
            </div>

            {{-- Content --}}
            <div class="p-4 sm:p-6 space-y-4 sm:space-y-5">
                {{-- PIC --}}
                @if($selectedClient->pic)
                <div class="flex items-start gap-3 sm:gap-4 rounded-xl bg-gray-50 p-3 sm:p-4 dark:bg-gray-800/50">
                    <div class="flex h-10 w-10 sm:h-12 sm:w-12 flex-shrink-0 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40">
                        <x-heroicon-o-user class="h-5 w-5 sm:h-6 sm:w-6 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] sm:text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Person In Charge</span>
                        </div>
                        <p class="mt-1 text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100">
                            {{ $selectedClient->pic->name }}
                        </p>
                        @if($selectedClient->pic->nik)
                        <p class="mt-0.5 flex items-center gap-1 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                            <x-heroicon-o-identification class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                            NIK: {{ $selectedClient->pic->nik }}
                        </p>
                        @endif
                    </div>
                </div>
                @else
                <div class="flex items-center gap-3 rounded-xl bg-gray-50 p-3 sm:p-4 dark:bg-gray-800/50">
                    <div class="flex h-10 w-10 sm:h-12 sm:w-12 flex-shrink-0 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700">
                        <x-heroicon-o-user class="h-5 w-5 sm:h-6 sm:w-6 text-gray-400 dark:text-gray-500" />
                    </div>
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">PIC belum ditentukan</p>
                    </div>
                </div>
                @endif

                {{-- Account Representative --}}
                @if($selectedClient->accountRepresentative)
                <div class="flex items-start gap-3 sm:gap-4 rounded-xl bg-gray-50 p-3 sm:p-4 dark:bg-gray-800/50">
                    <div class="flex h-10 w-10 sm:h-12 sm:w-12 flex-shrink-0 items-center justify-center rounded-full bg-cyan-100 dark:bg-cyan-900/40">
                        <x-heroicon-o-briefcase class="h-5 w-5 sm:h-6 sm:w-6 text-cyan-600 dark:text-cyan-400" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] sm:text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Account Representative</span>
                        </div>
                        <p class="mt-1 text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100">
                            {{ $selectedClient->accountRepresentative->name }}
                        </p>
                        @if($selectedClient->accountRepresentative->kpp)
                        <p class="mt-0.5 flex items-center gap-1 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                            <x-heroicon-o-building-library class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                            {{ $selectedClient->accountRepresentative->kpp }}
                        </p>
                        @endif
                        @if($selectedClient->accountRepresentative->phone_number)
                        <p class="mt-0.5 flex items-center gap-1 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                            <x-heroicon-o-phone class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                            {{ $selectedClient->accountRepresentative->phone_number }}
                        </p>
                        @endif
                    </div>
                </div>
                @else
                <div class="flex items-center gap-3 rounded-xl bg-gray-50 p-3 sm:p-4 dark:bg-gray-800/50">
                    <div class="flex h-10 w-10 sm:h-12 sm:w-12 flex-shrink-0 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700">
                        <x-heroicon-o-briefcase class="h-5 w-5 sm:h-6 sm:w-6 text-gray-400 dark:text-gray-500" />
                    </div>
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Account Representative belum ditentukan</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Contract Status Card --}}
    <div class="overflow-hidden rounded-xl sm:rounded-2xl border border-gray-200/60 bg-white shadow-sm dark:border-gray-700/50 dark:bg-gray-900"
        x-show="mounted" x-transition:enter="transition ease-out duration-500 delay-200"
        x-transition:enter-start="opacity-0 transform translate-y-6"
        x-transition:enter-end="opacity-100 transform translate-y-0">

        {{-- Header --}}
        <div class="border-b border-gray-200/60 bg-gradient-to-r from-gray-50 to-white px-4 py-3 sm:px-6 sm:py-4 dark:border-gray-700/50 dark:from-gray-800/80 dark:to-gray-900">
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-lg sm:rounded-xl bg-emerald-600 shadow-md shadow-emerald-500/20 dark:bg-emerald-500">
                    <x-heroicon-o-document-check class="h-4 w-4 sm:h-5 sm:w-5 text-white" />
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-gray-50">Status Kontrak Layanan</h3>
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">Layanan pajak yang aktif</p>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-4 sm:p-6">
            <div class="grid gap-3 sm:gap-4 grid-cols-2 lg:grid-cols-4">
                @foreach($contractStatus as $contract)
                <div class="relative overflow-hidden rounded-xl p-3 sm:p-4 transition-all duration-300
                    {{ $contract['active']
                        ? 'bg-gradient-to-br from-emerald-50 to-emerald-100/50 ring-1 ring-emerald-200 dark:from-emerald-950/40 dark:to-emerald-900/20 dark:ring-emerald-800/50'
                        : 'bg-gray-50 ring-1 ring-gray-200 dark:bg-gray-800/50 dark:ring-gray-700' }}">

                    <div class="flex items-start justify-between">
                        <div class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-lg
                            {{ $contract['active']
                                ? 'bg-emerald-200 dark:bg-emerald-900/60'
                                : 'bg-gray-200 dark:bg-gray-700' }}">
                            @if($contract['icon'] === 'document-currency-dollar')
                            <x-heroicon-o-document-currency-dollar class="h-4 w-4 sm:h-5 sm:w-5 {{ $contract['active'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500' }}" />
                            @elseif($contract['icon'] === 'banknotes')
                            <x-heroicon-o-banknotes class="h-4 w-4 sm:h-5 sm:w-5 {{ $contract['active'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500' }}" />
                            @elseif($contract['icon'] === 'document-check')
                            <x-heroicon-o-document-check class="h-4 w-4 sm:h-5 sm:w-5 {{ $contract['active'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500' }}" />
                            @else
                            <x-heroicon-o-building-office class="h-4 w-4 sm:h-5 sm:w-5 {{ $contract['active'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500' }}" />
                            @endif
                        </div>

                        @if($contract['active'])
                        <span class="flex h-5 w-5 sm:h-6 sm:w-6 items-center justify-center rounded-full bg-emerald-500 text-white">
                            <x-heroicon-s-check class="h-3 w-3 sm:h-4 sm:w-4" />
                        </span>
                        @else
                        <span class="flex h-5 w-5 sm:h-6 sm:w-6 items-center justify-center rounded-full bg-gray-300 dark:bg-gray-600">
                            <x-heroicon-s-minus class="h-3 w-3 sm:h-4 sm:w-4 text-gray-500 dark:text-gray-400" />
                        </span>
                        @endif
                    </div>

                    <div class="mt-3">
                        <h4 class="text-sm sm:text-base font-bold {{ $contract['active'] ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ $contract['name'] }}
                        </h4>
                        <p class="mt-0.5 text-[10px] sm:text-xs {{ $contract['active'] ? 'text-gray-600 dark:text-gray-400' : 'text-gray-400 dark:text-gray-500' }}">
                            {{ $contract['description'] }}
                        </p>
                    </div>

                    <div class="mt-2">
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[9px] sm:text-[10px] font-semibold
                            {{ $contract['active']
                                ? 'bg-emerald-200 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300'
                                : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                            {{ $contract['active'] ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Credentials Card --}}
    @if($selectedClient->clientCredential)
    <div class="overflow-hidden rounded-xl sm:rounded-2xl border border-gray-200/60 bg-white shadow-sm dark:border-gray-700/50 dark:bg-gray-900"
        x-show="mounted" x-transition:enter="transition ease-out duration-500 delay-250"
        x-transition:enter-start="opacity-0 transform translate-y-6"
        x-transition:enter-end="opacity-100 transform translate-y-0">

        {{-- Header --}}
        <div class="border-b border-gray-200/60 bg-gradient-to-r from-gray-50 to-white px-4 py-3 sm:px-6 sm:py-4 dark:border-gray-700/50 dark:from-gray-800/80 dark:to-gray-900">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-lg sm:rounded-xl bg-amber-600 shadow-md shadow-amber-500/20 dark:bg-amber-500">
                        <x-heroicon-o-lock-closed class="h-4 w-4 sm:h-5 sm:w-5 text-white" />
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-gray-50">Kredensial Akun</h3>
                        <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">Informasi akses sistem perpajakan</p>
                    </div>
                </div>

                <button wire:click="toggleCredentials"
                    class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs sm:text-sm font-medium transition-all duration-200
                    {{ $showCredentials
                        ? 'bg-amber-100 text-amber-700 hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-400 dark:hover:bg-amber-900/60'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-gray-600' }}">
                    @if($showCredentials)
                    <x-heroicon-o-eye-slash class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                    <span class="hidden sm:inline">Sembunyikan</span>
                    @else
                    <x-heroicon-o-eye class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                    <span class="hidden sm:inline">Tampilkan</span>
                    @endif
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-4 sm:p-6">
            <div class="grid gap-4 sm:gap-6 lg:grid-cols-3">
                {{-- Core Tax --}}
                <div class="rounded-xl bg-gray-50 p-4 sm:p-5 dark:bg-gray-800/50">
                    <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                        <div class="flex h-8 w-8 sm:h-9 sm:w-9 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/40">
                            <x-heroicon-o-server class="h-4 w-4 sm:h-5 sm:w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h4 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100">Core Tax</h4>
                    </div>

                    <dl class="space-y-3">
                        <div>
                            <dt class="text-[10px] sm:text-xs font-medium text-gray-500 dark:text-gray-400">User ID</dt>
                            <dd class="mt-0.5 text-sm font-mono text-gray-900 dark:text-gray-100">
                                @if($selectedClient->clientCredential->core_tax_user_id)
                                    @if($showCredentials)
                                        {{ $selectedClient->clientCredential->core_tax_user_id }}
                                    @else
                                        {{ $this->maskString($selectedClient->clientCredential->core_tax_user_id) }}
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[10px] sm:text-xs font-medium text-gray-500 dark:text-gray-400">Password</dt>
                            <dd class="mt-0.5 text-sm font-mono text-gray-900 dark:text-gray-100">
                                @if($selectedClient->clientCredential->core_tax_password)
                                    @if($showCredentials)
                                        {{ $selectedClient->clientCredential->core_tax_password }}
                                    @else
                                        ********
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- DJP --}}
                <div class="rounded-xl bg-gray-50 p-4 sm:p-5 dark:bg-gray-800/50">
                    <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                        <div class="flex h-8 w-8 sm:h-9 sm:w-9 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/40">
                            <x-heroicon-o-globe-alt class="h-4 w-4 sm:h-5 sm:w-5 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <h4 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100">DJP Online</h4>
                    </div>

                    <dl class="space-y-3">
                        <div>
                            <dt class="text-[10px] sm:text-xs font-medium text-gray-500 dark:text-gray-400">Username</dt>
                            <dd class="mt-0.5 text-sm font-mono text-gray-900 dark:text-gray-100">
                                @if($selectedClient->clientCredential->djp_account)
                                    @if($showCredentials)
                                        {{ $selectedClient->clientCredential->djp_account }}
                                    @else
                                        {{ $this->maskString($selectedClient->clientCredential->djp_account) }}
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[10px] sm:text-xs font-medium text-gray-500 dark:text-gray-400">Password</dt>
                            <dd class="mt-0.5 text-sm font-mono text-gray-900 dark:text-gray-100">
                                @if($selectedClient->clientCredential->djp_password)
                                    @if($showCredentials)
                                        {{ $selectedClient->clientCredential->djp_password }}
                                    @else
                                        ********
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Email --}}
                <div class="rounded-xl bg-gray-50 p-4 sm:p-5 dark:bg-gray-800/50">
                    <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                        <div class="flex h-8 w-8 sm:h-9 sm:w-9 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/40">
                            <x-heroicon-o-envelope class="h-4 w-4 sm:h-5 sm:w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <h4 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100">Email Terdaftar</h4>
                    </div>

                    <dl class="space-y-3">
                        <div>
                            <dt class="text-[10px] sm:text-xs font-medium text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="mt-0.5 text-sm font-mono text-gray-900 dark:text-gray-100 break-all">
                                @if($selectedClient->clientCredential->email)
                                    @if($showCredentials)
                                        {{ $selectedClient->clientCredential->email }}
                                    @else
                                        {{ $this->maskString($selectedClient->clientCredential->email, 4) }}
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[10px] sm:text-xs font-medium text-gray-500 dark:text-gray-400">Password</dt>
                            <dd class="mt-0.5 text-sm font-mono text-gray-900 dark:text-gray-100">
                                @if($selectedClient->clientCredential->email_password)
                                    @if($showCredentials)
                                        {{ $selectedClient->clientCredential->email_password }}
                                    @else
                                        ********
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Security Notice --}}
            <div class="mt-4 sm:mt-6 flex items-start gap-2 sm:gap-3 rounded-lg bg-amber-50 p-3 sm:p-4 dark:bg-amber-950/30">
                <x-heroicon-o-shield-exclamation class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0 text-amber-600 dark:text-amber-400" />
                <div>
                    <p class="text-xs sm:text-sm font-medium text-amber-800 dark:text-amber-300">Informasi Sensitif</p>
                    <p class="mt-0.5 text-[10px] sm:text-xs text-amber-700 dark:text-amber-400">
                        Kredensial ini bersifat rahasia. Jangan bagikan kepada pihak yang tidak berwenang.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Additional Contacts --}}
    @if($selectedClient->contacts && $selectedClient->contacts->count() > 0)
    <div class="overflow-hidden rounded-xl sm:rounded-2xl border border-gray-200/60 bg-white shadow-sm dark:border-gray-700/50 dark:bg-gray-900"
        x-show="mounted" x-transition:enter="transition ease-out duration-500 delay-300"
        x-transition:enter-start="opacity-0 transform translate-y-6"
        x-transition:enter-end="opacity-100 transform translate-y-0">

        {{-- Header --}}
        <div class="border-b border-gray-200/60 bg-gradient-to-r from-gray-50 to-white px-4 py-3 sm:px-6 sm:py-4 dark:border-gray-700/50 dark:from-gray-800/80 dark:to-gray-900">
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-lg sm:rounded-xl bg-purple-600 shadow-md shadow-purple-500/20 dark:bg-purple-500">
                    <x-heroicon-o-phone class="h-4 w-4 sm:h-5 sm:w-5 text-white" />
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-gray-50">Kontak Tambahan</h3>
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">Kontak lain perusahaan</p>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-4 sm:p-6">
            <div class="grid gap-3 sm:gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($selectedClient->contacts as $contact)
                <div class="flex items-start gap-3 rounded-xl bg-gray-50 p-3 sm:p-4 dark:bg-gray-800/50">
                    <div class="flex h-9 w-9 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900/40">
                        <x-heroicon-o-user class="h-4 w-4 sm:h-5 sm:w-5 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $contact->name }}</p>
                        @if($contact->position)
                        <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">{{ $contact->position }}</p>
                        @endif
                        @if($contact->phone)
                        <p class="mt-1 flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                            <x-heroicon-o-phone class="h-3 w-3" />
                            {{ $contact->phone }}
                        </p>
                        @endif
                        @if($contact->email)
                        <p class="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400 truncate">
                            <x-heroicon-o-envelope class="h-3 w-3 flex-shrink-0" />
                            {{ $contact->email }}
                        </p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @endif
    @endif
</div>
