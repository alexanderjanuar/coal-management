<div class="space-y-6">
    {{-- Informasi NPWP --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="mb-6 text-base font-semibold text-gray-900 dark:text-white">Informasi NPWP</h3>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            {{-- Nomor NPWP --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nomor NPWP
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm font-mono text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $this->getFormattedNpwp() }}
                </div>
            </div>

            {{-- Tanggal Terdaftar --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Tanggal Terdaftar
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $client->created_at ? $client->created_at->format('d/m/Y') : '-' }}
                </div>
            </div>

            {{-- Kantor Pelayanan Pajak (KPP) --}}
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Kantor Pelayanan Pajak (KPP)
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $client->accountRepresentative?->kpp ?? '-' }}
                </div>
            </div>

            {{-- EFIN --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    EFIN
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    <div class="flex items-center gap-2">
                        {{ $client->EFIN ?? '-' }}
                        @if($this->hasEfin())
                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-300">
                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Terdaftar
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-300">
                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                Belum Terdaftar
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Jenis Perpajakan --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Jenis Perpajakan
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $this->getPkpStatusLabel() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Status PKP --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="mb-6 text-base font-semibold text-gray-900 dark:text-white">Status PKP</h3>

        <div class="space-y-4">
            {{-- Status Pengusaha Kena Pajak (PKP) --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Status Pengusaha Kena Pajak (PKP)
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-900 dark:text-gray-100">
                            Apakah perusahaan terdaftar sebagai PKP?
                        </span>
                        <div class="flex items-center">
                            @if($client->pkp_status == 'PKP')
                                <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800 dark:bg-green-900 dark:text-green-300">
                                    <svg class="mr-1.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Ya
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    <svg class="mr-1.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                    Tidak
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Informasi Account Representative --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="mb-6 text-base font-semibold text-gray-900 dark:text-white">Informasi Account Representative</h3>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            {{-- Nama AR --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nama AR
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $client->accountRepresentative?->name ?? $client->account_representative ?? '-' }}
                </div>
            </div>

            {{-- Telepon AR --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Telepon AR
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    @if($client->accountRepresentative?->phone_number || $client->ar_phone_number)
                        <a href="tel:{{ $client->accountRepresentative?->phone_number ?? $client->ar_phone_number }}" 
                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            {{ $client->accountRepresentative?->phone_number ?? $client->ar_phone_number }}
                        </a>
                    @else
                        -
                    @endif
                </div>
            </div>

            {{-- Email AR (jika ada) --}}
            @if($client->accountRepresentative?->email)
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Email AR
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    <a href="mailto:{{ $client->accountRepresentative->email }}" 
                       class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                        {{ $client->accountRepresentative->email }}
                    </a>
                </div>
            </div>
            @endif

            {{-- Kantor KPP AR (jika ada) --}}
            @if($client->accountRepresentative?->KPP)
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
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