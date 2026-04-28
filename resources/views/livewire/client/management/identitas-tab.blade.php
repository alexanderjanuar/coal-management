<div class="space-y-6">
    {{-- Informasi Perusahaan --}}
    <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10">
        <div class="mb-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Informasi Perusahaan</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Profil dasar dan bentuk usaha client.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <x-client-management.field-icon name="building" />
                    Nama Legal Perusahaan
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $client->name ?? '-' }}
                </div>
            </div>

            <div>
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <x-client-management.field-icon name="tag" />
                    Nama Brand/Dagang
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $client->name ?? '-' }}
                </div>
            </div>

            <div>
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <x-client-management.field-icon name="briefcase" />
                    Bentuk Usaha
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $client->client_type ?? '-' }}
                </div>
            </div>

            <div>
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <x-client-management.field-icon name="chart" />
                    Bidang Usaha
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    -
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <x-client-management.field-icon name="calendar" />
                    Tanggal Berdiri
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $client->created_at ? $client->created_at->format('d/m/Y') : '-' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Informasi Kontak --}}
    <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10">
        <div class="mb-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Informasi Kontak</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Kanal komunikasi resmi perusahaan.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <x-client-management.field-icon name="mail" />
                    Email Perusahaan
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $client->email ?? '-' }}
                </div>
            </div>

            <div>
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <x-client-management.field-icon name="phone" />
                    Telepon Kantor
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    -
                </div>
            </div>

            <div>
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <x-client-management.field-icon name="device" />
                    Telepon Mobile
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    -
                </div>
            </div>

            <div>
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <x-client-management.field-icon name="globe" />
                    Website
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    -
                </div>
            </div>
        </div>
    </div>

    {{-- Alamat --}}
    <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10">
        <div class="mb-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Alamat</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Alamat legal dan wilayah administrasi.</p>
        </div>

        <div class="space-y-6">
            <div>
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <x-client-management.field-icon name="map" />
                    Alamat Lengkap
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $client->adress ?? '-' }}
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div>
                    <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <x-client-management.field-icon name="city" />
                        Kota/Kabupaten
                    </label>
                    <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                        -
                    </div>
                </div>

                <div>
                    <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <x-client-management.field-icon name="map" />
                        Provinsi
                    </label>
                    <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                        -
                    </div>
                </div>

                <div>
                    <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <x-client-management.field-icon name="hash" />
                        Kode Pos
                    </label>
                    <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                        -
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Perpajakan --}}
    <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10">
        <div class="mb-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Informasi Perpajakan</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">NPWP, EFIN, dan status PKP client.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <x-client-management.field-icon name="receipt" />
                    Nomor NPWP
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 font-mono text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $this->getFormattedNpwp() }}
                </div>
            </div>

            <div>
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <x-client-management.field-icon name="calendar" />
                    Tanggal Terdaftar
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $client->created_at ? $client->created_at->format('d/m/Y') : '-' }}
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <x-client-management.field-icon name="building" />
                    Kantor Pelayanan Pajak (KPP)
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $client->accountRepresentative?->kpp ?? '-' }}
                </div>
            </div>

            <div>
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <x-client-management.field-icon name="key" />
                    EFIN
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    <div class="flex flex-wrap items-center gap-2">
                        <span>{{ $client->EFIN ?? '-' }}</span>
                        @if($this->hasEfin())
                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-300">
                            Terdaftar
                        </span>
                        @else
                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-300">
                            Belum Terdaftar
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <div>
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <x-client-management.field-icon name="receipt" />
                    Jenis Perpajakan
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $this->getPkpStatusLabel() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Account Representative --}}
    <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10">
        <div class="mb-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Account Representative</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Kontak AR dan kantor KPP terkait.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <x-client-management.field-icon name="user" />
                    Nama AR
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $client->accountRepresentative?->name ?? $client->account_representative ?? '-' }}
                </div>
            </div>

            <div>
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <x-client-management.field-icon name="phone" />
                    Telepon AR
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    @if($client->accountRepresentative?->phone_number || $client->ar_phone_number)
                    <a href="tel:{{ $client->accountRepresentative?->phone_number ?? $client->ar_phone_number }}" class="font-medium underline underline-offset-2">
                        {{ $client->accountRepresentative?->phone_number ?? $client->ar_phone_number }}
                    </a>
                    @else
                    -
                    @endif
                </div>
            </div>

            @if($client->accountRepresentative?->email)
            <div class="md:col-span-2">
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <x-client-management.field-icon name="mail" />
                    Email AR
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    <a href="mailto:{{ $client->accountRepresentative->email }}" class="font-medium underline underline-offset-2">
                        {{ $client->accountRepresentative->email }}
                    </a>
                </div>
            </div>
            @endif

            @if($client->accountRepresentative?->KPP)
            <div class="md:col-span-2">
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <x-client-management.field-icon name="building" />
                    Kantor KPP Account Representative
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $client->accountRepresentative->KPP }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
