<div class="space-y-6">
    {{-- Kontak Person --}}
    <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Kontak Person</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">PIC dan kontak operasional client.</p>
            </div>
            @if(isset($client->contacts) && $client->contacts->count() > 1)
            <span class="inline-flex w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
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
                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900 dark:text-green-300">
                    Utama
                </span>
                @endif
            </div>
            @endif

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <x-client-management.field-icon name="user" />
                        Nama Kontak
                    </label>
                    <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                        {{ $contact->name }}
                    </div>
                </div>

                <div>
                    <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <x-client-management.field-icon name="briefcase" />
                        Jabatan
                    </label>
                    <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                        {{ $contact->position ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <x-client-management.field-icon name="mail" />
                        Email
                    </label>
                    <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                        {{ $contact->email ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <x-client-management.field-icon name="phone" />
                        Telepon
                    </label>
                    <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                        {{ $contact->phone ?? $contact->mobile ?? '-' }}
                    </div>
                </div>
            </div>

            @if($client->contacts->count() > 1 && !$loop->last)
            <hr class="my-6 border-gray-200 dark:border-gray-700">
            @endif
        </div>
        @empty
        <div class="rounded-lg bg-gray-50 px-4 py-8 text-center dark:bg-gray-900/50">
            <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada kontak person yang ditambahkan</p>
        </div>
        @endforelse
        @else
        <div class="rounded-lg bg-gray-50 px-4 py-8 text-center dark:bg-gray-900/50">
            <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada kontak person yang ditambahkan</p>
        </div>
        @endif
    </div>

    {{-- Perusahaan Terkait / Afiliasi --}}
    <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Perusahaan Terkait / Afiliasi</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Relasi bisnis, kepemilikan, dan NPWP afiliasi.</p>
            </div>
            <button wire:click="openAffiliateModal"
                class="inline-flex w-fit items-center rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-700">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Afiliasi
            </button>
        </div>

        @if($client->affiliates && $client->affiliates->count() > 0)
        <div class="overflow-x-auto border-b border-gray-200 dark:border-gray-700">
            <div class="min-w-[760px]">
                <div class="grid grid-cols-12 gap-4 border-b border-gray-200 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    <div class="col-span-3">Nama Perusahaan</div>
                    <div class="col-span-2">Hubungan</div>
                    <div class="col-span-2">Kepemilikan</div>
                    <div class="col-span-3">NPWP</div>
                    <div class="col-span-2">Aksi</div>
                </div>

                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($client->affiliates as $affiliate)
                    <div class="grid grid-cols-12 gap-4 py-4">
                        <div class="col-span-3">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $affiliate->company_name }}</div>
                            @if($affiliate->notes)
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($affiliate->notes, 50) }}</div>
                            @endif
                        </div>

                        <div class="col-span-2">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium 
                                {{ $affiliate->getRelationshipBadgeColor() === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                   ($affiliate->getRelationshipBadgeColor() === 'primary' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                   ($affiliate->getRelationshipBadgeColor() === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                   'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200')) }}">
                                {{ $affiliate->relationship_type }}
                            </span>
                        </div>

                        <div class="col-span-2 text-sm text-gray-900 dark:text-white">
                            {{ $affiliate->ownership_percentage ? number_format($affiliate->ownership_percentage, 0) . '%' : '-' }}
                        </div>

                        <div class="col-span-3 font-mono text-sm text-gray-900 dark:text-white">
                            {{ $affiliate->npwp ? $affiliate->formatted_npwp : '-' }}
                        </div>

                        <div class="col-span-2">
                            <button wire:click="editAffiliate({{ $affiliate->id }})"
                                class="inline-flex items-center rounded-md bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-white dark:ring-gray-600 dark:hover:bg-gray-600">
                                <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Lihat
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @else
        <div class="rounded-lg bg-gray-50 p-8 text-center dark:bg-gray-900/50">
            <svg class="mx-auto mb-4 h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <h3 class="mb-2 text-sm font-medium text-gray-900 dark:text-white">Belum ada perusahaan afiliasi</h3>
            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Mulai dengan menambahkan perusahaan afiliasi pertama Anda.</p>
            <button wire:click="openAffiliateModal"
                class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-700">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Afiliasi
            </button>
        </div>
        @endif
    </div>

    <x-filament::modal id="affiliate-modal" width="2xl">
        <x-slot name="heading">
            {{ $editingAffiliateId ? 'Edit Perusahaan Afiliasi' : 'Tambah Perusahaan Afiliasi' }}
        </x-slot>

        <div class="space-y-6">
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nama Perusahaan <span class="text-red-500">*</span>
                </label>
                <input wire:model="affiliateCompanyName" type="text" placeholder="Masukkan nama perusahaan"
                    class="block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" />
                @error('affiliateCompanyName')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Tipe Hubungan <span class="text-red-500">*</span>
                </label>
                <select wire:model="affiliateRelationshipType"
                    class="block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                    @foreach($this->relationshipTypes as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('affiliateRelationshipType')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Persentase Kepemilikan (%)</label>
                <input wire:model="affiliateOwnershipPercentage" type="number" step="0.01" min="0" max="100" placeholder="0.00"
                    class="block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" />
                <p class="text-xs text-gray-500 dark:text-gray-400">Opsional. Masukkan persentase kepemilikan (0-100)</p>
                @error('affiliateOwnershipPercentage')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NPWP Perusahaan Afiliasi</label>
                <input wire:model="affiliateNpwp" type="text" placeholder="Masukkan NPWP (opsional)" maxlength="20"
                    class="block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" />
                <p class="text-xs text-gray-500 dark:text-gray-400">Opsional. Format: 15 digit tanpa tanda baca</p>
                @error('affiliateNpwp')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Client Terkait</label>
                <select wire:model="affiliateAffiliatedClientId"
                    class="block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                    <option value="">Pilih client (opsional)</option>
                    @foreach($this->availableClients as $clientId => $clientName)
                    <option value="{{ $clientId }}">{{ $clientName }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 dark:text-gray-400">Opsional. Pilih jika perusahaan afiliasi juga terdaftar sebagai client</p>
                @error('affiliateAffiliatedClientId')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                <textarea wire:model="affiliateNotes" rows="3" placeholder="Catatan tambahan tentang afiliasi ini..."
                    class="block w-full resize-none rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"></textarea>
                <p class="text-xs text-gray-500 dark:text-gray-400">Opsional. Tambahkan catatan atau informasi tambahan</p>
                @error('affiliateNotes')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button wire:click="closeAffiliateModal" color="gray">
                Batal
            </x-filament::button>

            <x-filament::button wire:click="saveAffiliate" wire:loading.attr="disabled" wire:target="saveAffiliate">
                <span wire:loading.remove wire:target="saveAffiliate">
                    {{ $editingAffiliateId ? 'Perbarui' : 'Simpan' }}
                </span>
                <span wire:loading wire:target="saveAffiliate">
                    Menyimpan...
                </span>
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</div>
