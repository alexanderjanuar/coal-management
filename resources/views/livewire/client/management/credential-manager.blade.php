@php $isEdit = $mode === 'edit'; @endphp

<div>
    {{-- ============ Header: identity + mode switch ============ --}}
    <div class="flex items-start justify-between gap-3 pb-5">
        <div class="flex items-center gap-3">
            @if ($clientLogo)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($clientLogo) }}" alt="{{ $clientName }}"
                     class="h-11 w-11 rounded-full object-cover ring-2 ring-white dark:ring-gray-800">
            @else
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-base font-bold text-white ring-2 ring-white dark:ring-gray-800">
                    {{ \Illuminate\Support\Str::substr($clientName, 0, 1) }}
                </div>
            @endif
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $clientName }}</h3>
                <p class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                    @if ($isEdit)
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-amber-500"></span> Mode ubah
                    @else
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Mode lihat
                    @endif
                </p>
            </div>
        </div>

        @unless ($isEdit)
            <button type="button" wire:click="enableEdit"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-primary-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-primary-500">
                <x-heroicon-m-pencil-square class="h-4 w-4" />
                Ubah
            </button>
        @endunless
    </div>

    {{-- ============ Kredensial Klien ============ --}}
    <section class="border-t border-gray-200 py-5 dark:border-gray-700">
        <div class="mb-4 flex items-center gap-2">
            <x-heroicon-m-identification class="h-5 w-5 text-emerald-500" />
            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Kredensial Klien</h4>
        </div>

        <div class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
            <x-cred-field label="Core Tax User ID" :mode="$mode" wire="clientCred.core_tax_user_id" :value="$clientCred['core_tax_user_id']" placeholder="mis. 0012345678" />
            <x-cred-field label="Core Tax Password" type="password" :mode="$mode" wire="clientCred.core_tax_password" :value="$clientCred['core_tax_password']" />
            <x-cred-field label="DJP Online Account" :mode="$mode" wire="clientCred.djp_account" :value="$clientCred['djp_account']" />
            <x-cred-field label="DJP Online Password" type="password" :mode="$mode" wire="clientCred.djp_password" :value="$clientCred['djp_password']" />
            <x-cred-field label="Email" type="email" :mode="$mode" wire="clientCred.email" :value="$clientCred['email']" :error="$errors->first('clientCred.email')" />
            <x-cred-field label="Email Password" type="password" :mode="$mode" wire="clientCred.email_password" :value="$clientCred['email_password']" />
        </div>
    </section>

    {{-- ============ Kredensial Aplikasi ============ --}}
    <section class="border-t border-gray-200 py-5 dark:border-gray-700">
        <div class="mb-4 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <x-heroicon-m-squares-2x2 class="h-5 w-5 text-blue-500" />
                <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Kredensial Aplikasi</h4>
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-gray-700 dark:text-gray-300">{{ count($appCreds) }}</span>
            </div>
            @if ($isEdit)
                <button type="button" wire:click="addAppCred"
                        class="inline-flex items-center gap-1 rounded-lg border border-primary-200 bg-primary-50 px-2.5 py-1.5 text-xs font-medium text-primary-700 transition hover:bg-primary-100 dark:border-primary-800/50 dark:bg-primary-900/20 dark:text-primary-300">
                    <x-heroicon-m-plus class="h-4 w-4" /> Tambah aplikasi
                </button>
            @endif
        </div>

        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($appCreds as $i => $cred)
                <div wire:key="appcred-{{ $i }}" class="py-4 first:pt-0 last:pb-0">
                    {{-- App name row --}}
                    <div class="mb-3 flex items-center justify-between gap-2">
                        @if ($isEdit)
                            <select wire:model="appCreds.{{ $i }}.application_id"
                                    class="w-full max-w-xs rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-900 shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                <option value="">Pilih aplikasi…</option>
                                @foreach ($applicationOptions as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            <div class="flex shrink-0 items-center gap-3">
                                <label class="inline-flex cursor-pointer items-center gap-1.5 text-xs text-gray-600 dark:text-gray-300">
                                    <input type="checkbox" wire:model="appCreds.{{ $i }}.is_active"
                                           class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-900">
                                    Aktif
                                </label>
                                <button type="button" wire:click="removeAppCred({{ $i }})"
                                        class="rounded-md p-1.5 text-danger-500 transition hover:bg-danger-50 dark:hover:bg-danger-900/20" title="Hapus">
                                    <x-heroicon-m-trash class="h-4 w-4" />
                                </button>
                            </div>
                        @else
                            <span class="flex items-center gap-2.5">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-sm font-bold uppercase text-blue-600 dark:bg-blue-900/30 dark:text-blue-300">
                                    {{ \Illuminate\Support\Str::substr($applicationOptions[$cred['application_id']] ?? '?', 0, 1) }}
                                </span>
                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    {{ $applicationOptions[$cred['application_id']] ?? 'Tanpa Nama' }}
                                </span>
                            </span>
                            @if ($cred['is_active'])
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span> Nonaktif
                                </span>
                            @endif
                        @endif
                    </div>

                    @error("appCreds.{$i}.application_id")
                        <p class="mb-2 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror

                    {{-- Credential pair --}}
                    <div class="grid grid-cols-1 gap-x-8 gap-y-4 pl-0 sm:grid-cols-2 sm:pl-6">
                        <x-cred-field label="Username" :mode="$mode" wire="appCreds.{{ $i }}.username" :value="$cred['username']" :error="$errors->first('appCreds.' . $i . '.username')" />
                        <x-cred-field label="Password" type="password" :mode="$mode" wire="appCreds.{{ $i }}.password" :value="$cred['password']" :error="$errors->first('appCreds.' . $i . '.password')" />

                        @if ($isEdit || filled($cred['activation_code']))
                            <x-cred-field label="Kode Aktivasi" :mode="$mode" wire="appCreds.{{ $i }}.activation_code" :value="$cred['activation_code']" />
                        @endif
                        @if ($isEdit || filled($cred['account_period']))
                            <div class="space-y-1.5">
                                <label class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Berlaku Hingga</label>
                                @if ($isEdit)
                                    <input type="date" wire:model="appCreds.{{ $i }}.account_period"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                @else
                                    <p class="font-mono text-sm text-gray-900 dark:text-gray-100">
                                        {{ \Illuminate\Support\Carbon::parse($cred['account_period'])->format('d M Y') }}
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-10 text-center">
                    <x-heroicon-o-key class="mx-auto h-9 w-9 text-gray-300 dark:text-gray-600" />
                    <p class="mt-2 text-sm text-gray-500">Belum ada kredensial aplikasi.</p>
                    @if ($isEdit)
                        <p class="text-xs text-gray-400">Klik “Tambah aplikasi” untuk menambahkan.</p>
                    @endif
                </div>
            @endforelse
        </div>
    </section>

    {{-- ============ Kredensial PIC ============ --}}
    @if ($hasPic)
        <section class="border-t border-gray-200 py-5 dark:border-gray-700">
            <div class="mb-4 flex items-center gap-2">
                <x-heroicon-m-user-circle class="h-5 w-5 text-indigo-500" />
                <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Kredensial PIC</h4>
                @if (filled($pic['name']))
                    <span class="text-xs text-gray-400">· {{ $pic['name'] }}</span>
                @endif
            </div>

            @if ($isEdit)
                <p class="mb-4 flex items-start gap-2 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:bg-amber-900/10 dark:text-amber-400">
                    <x-heroicon-m-exclamation-triangle class="mt-px h-4 w-4 shrink-0" />
                    PIC dipakai bersama oleh semua klien yang ditanganinya — perubahan berlaku untuk semua.
                </p>
            @endif

            <div class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
                <x-cred-field label="NIK" :mode="$mode" wire="pic.nik" :value="$pic['nik']" :error="$errors->first('pic.nik')" />
                <x-cred-field label="Password" type="password" :mode="$mode" wire="pic.password" :value="$pic['password']" />
            </div>
        </section>
    @endif

    {{-- ============ Edit action bar ============ --}}
    @if ($isEdit)
        <div class="sticky bottom-0 mt-2 flex items-center justify-end gap-2 border-t border-gray-200 bg-white/95 py-3 backdrop-blur dark:border-gray-700 dark:bg-gray-900/95">
            <button type="button" wire:click="cancelEdit"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                Batal
            </button>
            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 disabled:cursor-not-allowed disabled:opacity-60">
                <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <x-heroicon-m-check wire:loading.remove wire:target="save" class="h-4 w-4" />
                Simpan
            </button>
        </div>
    @endif
</div>
