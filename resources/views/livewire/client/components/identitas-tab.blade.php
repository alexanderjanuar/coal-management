<div class="space-y-6">
    {{-- Informasi Perusahaan --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="mb-6 text-base font-semibold text-gray-900 dark:text-white">Informasi Perusahaan</h3>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            {{-- Nama Legal Perusahaan --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nama Legal Perusahaan
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $client->name ?? '-' }}
                </div>
            </div>

            {{-- Nama Brand/Dagang --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nama Brand/Dagang
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $client->name ?? '-' }}
                </div>
            </div>

            {{-- Bentuk Usaha --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Bentuk Usaha
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $client->client_type ?? '-' }}
                </div>
            </div>

            {{-- Bidang Usaha --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Bidang Usaha
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    -
                </div>
            </div>

            {{-- Tanggal Berdiri --}}
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Tanggal Berdiri
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $client->created_at ? $client->created_at->format('d/m/Y') : '-' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Informasi Kontak --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="mb-6 text-base font-semibold text-gray-900 dark:text-white">Informasi Kontak</h3>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            {{-- Email Perusahaan --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Email Perusahaan
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $client->email ?? '-' }}
                </div>
            </div>

            {{-- Telepon Kantor --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Telepon Kantor
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    -
                </div>
            </div>

            {{-- Telepon Mobile --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Telepon Mobile
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    -
                </div>
            </div>

            {{-- Website --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Website
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    -
                </div>
            </div>
        </div>
    </div>

    {{-- Alamat --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="mb-6 text-base font-semibold text-gray-900 dark:text-white">Alamat</h3>

        <div class="space-y-6">
            {{-- Alamat Lengkap --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Alamat Lengkap
                </label>
                <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    {{ $client->adress ?? '-' }}
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                {{-- Kota/Kabupaten --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Kota/Kabupaten
                    </label>
                    <div
                        class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                        -
                    </div>
                </div>

                {{-- Provinsi --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Provinsi
                    </label>
                    <div
                        class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                        -
                    </div>
                </div>

                {{-- Kode Pos --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Kode Pos
                    </label>
                    <div
                        class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                        -
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Perusahaan Terkait / Afiliasi --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="mb-6 text-base font-semibold text-gray-900 dark:text-white">Perusahaan Terkait / Afiliasi</h3>

        @if($client->affiliates->count() > 0)
        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Nama Perusahaan
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Hubungan
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Kepemilikan
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            NPWP
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @foreach($client->affiliates as $affiliate)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $affiliate->company_name }}
                            </div>
                            @if($affiliate->notes)
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ Str::limit($affiliate->notes, 50) }}
                            </div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="text-sm text-gray-900 dark:text-white">
                                {{ $affiliate->relationship_type }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="text-sm text-gray-900 dark:text-white">
                                {{ $affiliate->formatted_ownership }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $affiliate->formatted_npwp }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-center">
                            @if($affiliate->affiliated_client_id)
                            <a href="{{ route('filament.admin.resources.clients.view', $affiliate->affiliated_client_id) }}"
                                class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
                                Lihat
                            </a>
                            @else
                            <span class="text-sm text-gray-400 dark:text-gray-500">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Tombol Tambah Perusahaan Terkait --}}
        <div class="mt-4">
            <button type="button" wire:click="openAffiliateModal"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition-colors">
                <svg class="mr-1.5 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Perusahaan Terkait
            </button>
        </div>
        @else
        <div
            class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center dark:border-gray-700 dark:bg-gray-900">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                Belum ada perusahaan terkait
            </p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                Tambahkan informasi anak perusahaan, afiliasi, atau perusahaan induk
            </p>

            {{-- Tombol Tambah Perusahaan Terkait --}}
            <div class="mt-6">
                <button type="button" wire:click="openAffiliateModal"
                    class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition-colors">
                    <svg class="mr-1.5 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Perusahaan Terkait
                </button>
            </div>
        </div>
        @endif
    </div>

    {{-- Kontak Person --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <div class="mb-6 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Kontak Person</h3>
            @if(isset($client->contacts) && $client->contacts->count() > 1)
            <span
                class="rounded-full bg-primary-100 px-3 py-1 text-xs font-medium text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                {{ $client->contacts->count() }} Kontak
            </span>
            @endif
        </div>

        @if(isset($client->contacts))
        @forelse($client->contacts as $contact)
        <div class="mb-6 last:mb-0">
            @if($client->contacts->count() > 1)
            <div class="mb-4 flex items-center gap-2">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    {{ ucfirst($contact->type) }}
                </span>
                @if($contact->type === 'primary')
                <span
                    class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900 dark:text-green-300">
                    Utama
                </span>
                @endif
            </div>
            @endif

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                {{-- Nama Kontak --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Kontak
                    </label>
                    <div
                        class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                        {{ $contact->name }}
                    </div>
                </div>

                {{-- Jabatan --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Jabatan
                    </label>
                    <div
                        class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                        {{ $contact->position ?? '-' }}
                    </div>
                </div>

                {{-- Email --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Email
                    </label>
                    <div
                        class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                        {{ $contact->email ?? '-' }}
                    </div>
                </div>

                {{-- Telepon --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Telepon
                    </label>
                    <div
                        class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                        {{ $contact->phone ?? $contact->mobile ?? '-' }}
                    </div>
                </div>
            </div>

            @if($client->contacts->count() > 1 && !$loop->last)
            <hr class="my-6 border-gray-200 dark:border-gray-700">
            @endif
        </div>
        @empty
        <div class="rounded-lg bg-gray-50 px-4 py-8 text-center dark:bg-gray-900">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Belum ada kontak person yang ditambahkan
            </p>
        </div>
        @endforelse
        @else
        <div class="rounded-lg bg-gray-50 px-4 py-8 text-center dark:bg-gray-900">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Belum ada kontak person yang ditambahkan
            </p>
        </div>
        @endif
    </div>

    {{-- Modal Form Perusahaan Terkait / Afiliasi --}}
    @if($showAffiliateModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" x-data x-init="document.body.style.overflow = 'hidden'"
        x-destroy="document.body.style.overflow = 'auto'">
        <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Background overlay --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeAffiliateModal">
            </div>

            {{-- Modal panel --}}
            <div
                class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle">
                <form wire:submit.prevent="saveAffiliate">
                    {{-- Header --}}
                    <div class="bg-white px-6 py-4 dark:bg-gray-800">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $editingAffiliateId ? 'Edit Perusahaan Terkait' : 'Tambah Perusahaan Terkait' }}
                            </h3>
                            <button type="button" wire:click="closeAffiliateModal"
                                class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="space-y-6 px-6 py-4">
                        {{-- Nama Perusahaan --}}
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nama Perusahaan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="affiliateCompanyName"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="Masukkan nama perusahaan">
                            @error('affiliateCompanyName')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            {{-- Hubungan --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Hubungan <span class="text-red-500">*</span>
                                </label>
                                <select wire:model="affiliateRelationshipType"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="Anak Perusahaan">Anak Perusahaan</option>
                                    <option value="Afiliasi">Afiliasi</option>
                                    <option value="Perusahaan Induk">Perusahaan Induk</option>
                                    <option value="Sister Company">Sister Company</option>
                                    <option value="Joint Venture">Joint Venture</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                                @error('affiliateRelationshipType')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Kepemilikan --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Kepemilikan (%)
                                </label>
                                <input type="number" wire:model="affiliateOwnershipPercentage" min="0" max="100"
                                    step="0.01"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="0-100">
                                @error('affiliateOwnershipPercentage')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- NPWP --}}
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                NPWP
                            </label>
                            <input type="text" wire:model="affiliateNpwp" maxlength="20"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="XX.XXX.XXX.X-XXX.XXX">
                            @error('affiliateNpwp')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Catatan --}}
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Catatan
                            </label>
                            <textarea wire:model="affiliateNotes" rows="3"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="Catatan tambahan (opsional)"></textarea>
                            @error('affiliateNotes')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="bg-gray-50 px-6 py-4 dark:bg-gray-900">
                        <div class="flex justify-end space-x-3">
                            <button type="button" wire:click="closeAffiliateModal"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                Batal
                            </button>
                            <button type="submit"
                                class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                {{ $editingAffiliateId ? 'Perbarui' : 'Simpan' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>