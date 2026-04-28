<div class="w-full space-y-4 antialiased text-slate-900 dark:text-slate-100"
    x-data="{ mounted: false }"
    x-init="setTimeout(() => mounted = true, 50)">

    @if($clients->isEmpty())
    <div class="flex flex-col items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-12 text-center shadow-sm"
        x-show="mounted" x-transition.opacity.duration.300ms>
        <div class="w-14 h-14 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center mb-4">
            <x-heroicon-o-building-office-2 class="h-6 w-6 text-slate-400" />
        </div>
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Akun Belum Terhubung</h3>
        <p class="mt-1 text-sm text-slate-400">Akun Anda belum terhubung dengan data klien. Hubungi administrator.</p>
    </div>
    @else
    @if($selectedClient)

    <div class="space-y-4" x-show="mounted" x-transition.opacity.duration.300ms>

        {{-- ── IDENTITY HEADER ────────────────────────────────── --}}
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                @if($selectedClient->logo)
                    <img src="{{ Storage::url($selectedClient->logo) }}" alt="{{ $selectedClient->name }}"
                        class="h-10 w-10 rounded-lg object-cover border border-slate-200 dark:border-slate-700 flex-shrink-0">
                @else
                    <div class="h-10 w-10 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center flex-shrink-0">
                        <x-heroicon-o-building-office-2 class="h-5 w-5 text-slate-400" />
                    </div>
                @endif
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-base font-semibold text-slate-900 dark:text-white truncate">{{ $selectedClient->name }}</h1>
                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold flex-shrink-0
                            {{ $selectedClient->status === 'Active'
                                ? 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-900/20 dark:text-cyan-400 dark:border-cyan-800'
                                : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' }}">
                            {{ $selectedClient->status }}
                        </span>
                    </div>
                    <p class="mt-0.5 text-xs text-slate-400 flex flex-wrap items-center gap-x-1.5">
                        @if($selectedClient->client_type)
                            <span>{{ $selectedClient->formatted_client_type }}</span>
                        @endif
                        @if($selectedClient->pkp_status)
                            <span class="text-slate-300 dark:text-slate-600">·</span>
                            <span>{{ $selectedClient->pkp_status }}</span>
                        @endif
                        @if($selectedClient->email)
                            <span class="text-slate-300 dark:text-slate-600">·</span>
                            <span>{{ $selectedClient->email }}</span>
                        @endif
                    </p>
                </div>
            </div>
            <button wire:click="refresh"
                class="flex-shrink-0 p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                title="Segarkan">
                <x-heroicon-o-arrow-path class="h-4 w-4" />
            </button>
        </div>

        {{-- ── TOP ROW: 2-COLUMN ───────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- PANEL: INFORMASI PERUSAHAAN --}}
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
                <div class="flex items-center gap-2 px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-1 h-4 rounded-full bg-slate-300 dark:bg-slate-600"></div>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Informasi Perusahaan</span>
                </div>
                <div class="divide-y divide-slate-50 dark:divide-slate-800/60">
                    @php
                        $infoRows = [
                            ['label' => 'NPWP',   'value' => $selectedClient->NPWP,   'mono' => true],
                            ['label' => 'EFIN',   'value' => $selectedClient->EFIN,   'mono' => true],
                            ['label' => 'Alamat', 'value' => $selectedClient->adress, 'mono' => false],
                            ['label' => 'Email',  'value' => $selectedClient->email,  'mono' => false],
                        ];
                    @endphp
                    @foreach($infoRows as $row)
                    <div class="flex items-start gap-4 px-4 py-3">
                        <span class="text-xs text-slate-400 dark:text-slate-500 w-16 flex-shrink-0 pt-0.5">{{ $row['label'] }}</span>
                        <span class="text-sm {{ $row['mono'] ? 'font-mono' : '' }} {{ $row['value'] ? 'text-slate-800 dark:text-slate-200' : 'text-slate-300 dark:text-slate-600' }}">
                            {{ $row['value'] ?: '—' }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- PANEL: PENANGGUNG JAWAB --}}
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
                <div class="flex items-center gap-2 px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-1 h-4 rounded-full bg-cyan-400"></div>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Penanggung Jawab</span>
                </div>
                <div class="divide-y divide-slate-50 dark:divide-slate-800/60">

                    {{-- PIC --}}
                    <div class="flex items-start gap-4 px-4 py-4">
                        <span class="text-xs text-slate-400 dark:text-slate-500 w-16 flex-shrink-0 pt-0.5">PIC</span>
                        @if($selectedClient->pic)
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $selectedClient->pic->name }}</p>
                            @if($selectedClient->pic->nik)
                            <p class="mt-0.5 text-xs text-slate-400 tabular-nums">NIK {{ $selectedClient->pic->nik }}</p>
                            @endif
                        </div>
                        @else
                        <span class="text-sm text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </div>

                    {{-- Account Representative --}}
                    <div class="flex items-start gap-4 px-4 py-4">
                        <span class="text-xs text-slate-400 dark:text-slate-500 w-16 flex-shrink-0 pt-0.5 leading-tight">AR</span>
                        @if($selectedClient->accountRepresentative)
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $selectedClient->accountRepresentative->name }}</p>
                            <div class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-slate-400">
                                @if($selectedClient->accountRepresentative->kpp)
                                    <span>{{ $selectedClient->accountRepresentative->kpp }}</span>
                                @endif
                                @if($selectedClient->accountRepresentative->phone_number)
                                    <span>{{ $selectedClient->accountRepresentative->phone_number }}</span>
                                @endif
                            </div>
                        </div>
                        @else
                        <span class="text-sm text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── PANEL: STATUS KONTRAK ───────────────────────────── --}}
        @php $activeCount = collect($contractStatus)->where('active', true)->count(); @endphp
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
            <div class="flex items-center gap-2 px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                <div class="w-1 h-4 rounded-full bg-slate-300 dark:bg-slate-600"></div>
                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Status Kontrak Layanan</span>
                <div class="flex-1"></div>
                <span class="text-xs text-slate-400 tabular-nums">{{ $activeCount }} dari {{ count($contractStatus) }} aktif</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-50 dark:divide-slate-800/60">
                @foreach($contractStatus as $contract)
                <div class="flex items-start gap-3 px-4 py-4">
                    <div class="flex-shrink-0 mt-0.5 w-2 h-2 rounded-full {{ $contract['active'] ? 'bg-cyan-500' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm {{ $contract['active'] ? 'font-medium text-slate-800 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500' }}">
                            {{ $contract['name'] }}
                        </p>
                        @if(!empty($contract['description']))
                        <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500 leading-snug">{{ $contract['description'] }}</p>
                        @endif
                        <span class="mt-1.5 inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold
                            {{ $contract['active']
                                ? 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-900/20 dark:text-cyan-400 dark:border-cyan-800'
                                : 'bg-slate-50 text-slate-400 border-slate-200 dark:bg-slate-800 dark:border-slate-700' }}">
                            {{ $contract['active'] ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── PANEL: KREDENSIAL AKUN ──────────────────────────── --}}
        <div class="rounded-xl border border-amber-200 dark:border-amber-900/40 bg-white dark:bg-slate-900 shadow-sm overflow-hidden"
            x-data="{
                copiedField: null,
                copy(field, text) {
                    navigator.clipboard.writeText(text).then(() => {
                        this.copiedField = field;
                        setTimeout(() => this.copiedField = null, 1500);
                    });
                }
            }">
            <div class="flex items-center gap-2 px-4 py-3 border-b border-amber-100 dark:border-amber-900/30 bg-amber-50/40 dark:bg-amber-900/10">
                <div class="w-1 h-4 rounded-full bg-amber-400"></div>
                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Kredensial Akun</span>
                <div class="flex-1"></div>
                <button wire:click="openCredentialModal"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium transition-colors
                        {{ $selectedClient->clientCredential
                            ? 'text-amber-700 dark:text-amber-400 bg-amber-100 dark:bg-amber-900/30 hover:bg-amber-200 dark:hover:bg-amber-800/50'
                            : 'text-white bg-amber-500 hover:bg-amber-600 active:bg-amber-700' }}">
                    @if($selectedClient->clientCredential)
                        <x-heroicon-o-pencil-square class="h-3 w-3" />
                        Perbarui
                    @else
                        <x-heroicon-o-plus class="h-3 w-3" />
                        Tambah Kredensial
                    @endif
                </button>
            </div>

            @if($selectedClient->clientCredential)
            @php $cred = $selectedClient->clientCredential; @endphp

            {{-- Security notice --}}
            <div class="flex items-center gap-2 px-4 py-2 bg-amber-50/30 dark:bg-amber-900/5 border-b border-amber-50 dark:border-amber-900/20">
                <x-heroicon-o-shield-exclamation class="h-3.5 w-3.5 text-amber-400 flex-shrink-0" />
                <p class="text-[11px] text-amber-600 dark:text-amber-500">Informasi sensitif — jangan bagikan kepada pihak yang tidak berwenang.</p>
            </div>

            {{-- Three credential groups side by side --}}
            <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-100 dark:divide-slate-800">

                {{-- Core Tax --}}
                <div class="px-4 py-4 space-y-4">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Core Tax</p>

                    @php
                        $coreTaxFields = [
                            ['key' => 'core_tax_user', 'label' => 'User ID',  'value' => $cred->core_tax_user_id,  'is_password' => false],
                            ['key' => 'core_tax_pass', 'label' => 'Password', 'value' => $cred->core_tax_password, 'is_password' => true],
                        ];
                    @endphp
                    @foreach($coreTaxFields as $f)
                    <div>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500">{{ $f['label'] }}</p>
                        <div class="mt-1 flex items-center gap-2">
                            @if($f['value'])
                                <p class="text-sm font-mono text-slate-800 dark:text-slate-200 flex-1 break-all select-all">{{ $f['value'] }}</p>
                                <button @click="copy('{{ $f['key'] }}', '{{ addslashes($f['value']) }}')"
                                    class="flex-shrink-0 p-1 rounded text-slate-300 hover:text-slate-500 dark:text-slate-600 dark:hover:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                    title="Salin">
                                    <x-heroicon-o-clipboard-document class="h-3.5 w-3.5" x-show="copiedField !== '{{ $f['key'] }}'"/>
                                    <x-heroicon-o-check class="h-3.5 w-3.5 text-cyan-500" x-show="copiedField === '{{ $f['key'] }}'"/>
                                </button>
                            @else
                                <span class="text-sm text-slate-300 dark:text-slate-600">—</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- DJP Online --}}
                <div class="px-4 py-4 space-y-4">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">DJP Online</p>

                    @php
                        $djpFields = [
                            ['key' => 'djp_user', 'label' => 'Username', 'value' => $cred->djp_account,  'is_password' => false],
                            ['key' => 'djp_pass', 'label' => 'Password', 'value' => $cred->djp_password, 'is_password' => true],
                        ];
                    @endphp
                    @foreach($djpFields as $f)
                    <div>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500">{{ $f['label'] }}</p>
                        <div class="mt-1 flex items-center gap-2">
                            @if($f['value'])
                                <p class="text-sm font-mono text-slate-800 dark:text-slate-200 flex-1 break-all select-all">{{ $f['value'] }}</p>
                                <button @click="copy('{{ $f['key'] }}', '{{ addslashes($f['value']) }}')"
                                    class="flex-shrink-0 p-1 rounded text-slate-300 hover:text-slate-500 dark:text-slate-600 dark:hover:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                    title="Salin">
                                    <x-heroicon-o-clipboard-document class="h-3.5 w-3.5" x-show="copiedField !== '{{ $f['key'] }}'"/>
                                    <x-heroicon-o-check class="h-3.5 w-3.5 text-cyan-500" x-show="copiedField === '{{ $f['key'] }}'"/>
                                </button>
                            @else
                                <span class="text-sm text-slate-300 dark:text-slate-600">—</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Email --}}
                <div class="px-4 py-4 space-y-4">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Email Terdaftar</p>

                    @php
                        $emailFields = [
                            ['key' => 'email_addr', 'label' => 'Email',    'value' => $cred->email,          'is_password' => false, 'email' => true],
                            ['key' => 'email_pass', 'label' => 'Password', 'value' => $cred->email_password, 'is_password' => true,  'email' => false],
                        ];
                    @endphp
                    @foreach($emailFields as $f)
                    <div>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500">{{ $f['label'] }}</p>
                        <div class="mt-1 flex items-center gap-2">
                            @if($f['value'])
                                <p class="text-sm font-mono text-slate-800 dark:text-slate-200 flex-1 break-all select-all">{{ $f['value'] }}</p>
                                <button @click="copy('{{ $f['key'] }}', '{{ addslashes($f['value']) }}')"
                                    class="flex-shrink-0 p-1 rounded text-slate-300 hover:text-slate-500 dark:text-slate-600 dark:hover:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                    title="Salin">
                                    <x-heroicon-o-clipboard-document class="h-3.5 w-3.5" x-show="copiedField !== '{{ $f['key'] }}'"/>
                                    <x-heroicon-o-check class="h-3.5 w-3.5 text-cyan-500" x-show="copiedField === '{{ $f['key'] }}'"/>
                                </button>
                            @else
                                <span class="text-sm text-slate-300 dark:text-slate-600">—</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            @else
            {{-- Empty state --}}
            <div class="flex flex-col items-center justify-center py-10 px-4 text-center">
                <div class="w-10 h-10 rounded-full bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center mb-3">
                    <x-heroicon-o-key class="h-5 w-5 text-amber-400" />
                </div>
                <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Belum Ada Kredensial</p>
                <p class="mt-1 text-xs text-slate-400 max-w-xs">Tambahkan kredensial akun untuk Core Tax, DJP Online, dan Email terdaftar.</p>
            </div>
            @endif
        </div>

        {{-- ── PANEL: KONTAK TAMBAHAN ──────────────────────────── --}}
        @if($selectedClient->contacts && $selectedClient->contacts->count() > 0)
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
            <div class="flex items-center gap-2 px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                <div class="w-1 h-4 rounded-full bg-slate-300 dark:bg-slate-600"></div>
                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Kontak Tambahan</span>
                <div class="flex-1"></div>
                <span class="text-xs text-slate-400 tabular-nums">{{ $selectedClient->contacts->count() }}</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-slate-50 dark:divide-slate-800/60">
                @foreach($selectedClient->contacts as $contact)
                <div class="flex items-start gap-3 px-4 py-4">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center">
                        <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase">{{ substr($contact->name, 0, 1) }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $contact->name }}</p>
                        @if($contact->position)
                        <p class="text-xs text-slate-400 mt-0.5">{{ $contact->position }}</p>
                        @endif
                        <div class="mt-1 space-y-0.5 text-xs text-slate-400">
                            @if($contact->phone)
                                <p>{{ $contact->phone }}</p>
                            @endif
                            @if($contact->email)
                                <p class="truncate">{{ $contact->email }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>{{-- end space-y-4 --}}

    @endif
    @endif

    {{-- ── SLIDE-OVER: EDIT / TAMBAH KREDENSIAL ───────────── --}}
    <div
        x-data="{ open: $wire.entangle('showCredentialModal') }"
        x-show="open"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-hidden"
        style="display: none;">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-slate-900/60 dark:bg-black/70 backdrop-blur-sm"
            @click="$wire.closeCredentialModal()"></div>

        {{-- Panel --}}
        <div class="absolute inset-y-0 right-0 flex max-w-full pointer-events-none">
            <div
                x-show="open"
                x-transition:enter="transform transition ease-in-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="pointer-events-auto w-screen max-w-md flex flex-col bg-white dark:bg-slate-900 shadow-2xl border-l border-slate-200 dark:border-slate-800">

                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex-shrink-0">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-1 h-4 rounded-full bg-amber-400 flex-shrink-0"></div>
                        <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                            {{ $selectedClient?->clientCredential ? 'Perbarui Kredensial' : 'Tambah Kredensial' }}
                        </h2>
                        @if($selectedClient?->name)
                        <span class="text-xs text-slate-400 dark:text-slate-500 truncate">— {{ $selectedClient->name }}</span>
                        @endif
                    </div>
                    <button wire:click="closeCredentialModal" type="button"
                        class="flex-shrink-0 p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors ml-2">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                    </button>
                </div>

                {{-- Security notice --}}
                <div class="flex items-center gap-2 px-5 py-2.5 bg-amber-50 dark:bg-amber-900/10 border-b border-amber-100 dark:border-amber-900/20 flex-shrink-0">
                    <x-heroicon-o-shield-exclamation class="h-3.5 w-3.5 text-amber-500 flex-shrink-0" />
                    <p class="text-[11px] text-amber-700 dark:text-amber-500">Data tersimpan aman. Jangan bagikan kepada pihak yang tidak berwenang.</p>
                </div>

                {{-- Form Body --}}
                <div class="flex-1 overflow-y-auto px-5 py-5 space-y-6">

                    {{-- Core Tax --}}
                    <div class="space-y-3">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Core Tax</p>

                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">User ID</label>
                            <input wire:model="credCoreTaxUserId" type="text" autocomplete="off"
                                class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm font-mono text-slate-800 dark:text-slate-200 placeholder-slate-300 dark:placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-amber-400/60 focus:border-amber-400 transition"
                                placeholder="Masukkan User ID">
                            @error('credCoreTaxUserId')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div x-data="{ show: false }">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Password</label>
                            <div class="relative">
                                <input wire:model="credCoreTaxPassword" :type="show ? 'text' : 'password'" autocomplete="new-password"
                                    class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 pr-9 text-sm font-mono text-slate-800 dark:text-slate-200 placeholder-slate-300 dark:placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-amber-400/60 focus:border-amber-400 transition"
                                    placeholder="Masukkan Password">
                                <button type="button" @click="show = !show"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 p-0.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                                    <x-heroicon-o-eye class="h-4 w-4" x-show="!show" />
                                    <x-heroicon-o-eye-slash class="h-4 w-4" x-show="show" style="display:none" />
                                </button>
                            </div>
                            @error('credCoreTaxPassword')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="h-px bg-slate-100 dark:bg-slate-800"></div>

                    {{-- DJP Online --}}
                    <div class="space-y-3">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">DJP Online</p>

                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Username</label>
                            <input wire:model="credDjpAccount" type="text" autocomplete="off"
                                class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm font-mono text-slate-800 dark:text-slate-200 placeholder-slate-300 dark:placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-amber-400/60 focus:border-amber-400 transition"
                                placeholder="Masukkan Username DJP">
                            @error('credDjpAccount')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div x-data="{ show: false }">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Password</label>
                            <div class="relative">
                                <input wire:model="credDjpPassword" :type="show ? 'text' : 'password'" autocomplete="new-password"
                                    class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 pr-9 text-sm font-mono text-slate-800 dark:text-slate-200 placeholder-slate-300 dark:placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-amber-400/60 focus:border-amber-400 transition"
                                    placeholder="Masukkan Password DJP">
                                <button type="button" @click="show = !show"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 p-0.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                                    <x-heroicon-o-eye class="h-4 w-4" x-show="!show" />
                                    <x-heroicon-o-eye-slash class="h-4 w-4" x-show="show" style="display:none" />
                                </button>
                            </div>
                            @error('credDjpPassword')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="h-px bg-slate-100 dark:bg-slate-800"></div>

                    {{-- Email Terdaftar --}}
                    <div class="space-y-3">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Email Terdaftar</p>

                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Email</label>
                            <input wire:model="credEmail" type="email" autocomplete="off"
                                class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm font-mono text-slate-800 dark:text-slate-200 placeholder-slate-300 dark:placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-amber-400/60 focus:border-amber-400 transition"
                                placeholder="nama@domain.com">
                            @error('credEmail')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div x-data="{ show: false }">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Password</label>
                            <div class="relative">
                                <input wire:model="credEmailPassword" :type="show ? 'text' : 'password'" autocomplete="new-password"
                                    class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 pr-9 text-sm font-mono text-slate-800 dark:text-slate-200 placeholder-slate-300 dark:placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-amber-400/60 focus:border-amber-400 transition"
                                    placeholder="Masukkan Password Email">
                                <button type="button" @click="show = !show"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 p-0.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                                    <x-heroicon-o-eye class="h-4 w-4" x-show="!show" />
                                    <x-heroicon-o-eye-slash class="h-4 w-4" x-show="show" style="display:none" />
                                </button>
                            </div>
                            @error('credEmailPassword')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/80 flex-shrink-0">
                    <button wire:click="closeCredentialModal" type="button"
                        class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button wire:click="saveCredentials" type="button"
                        wire:loading.attr="disabled"
                        wire:target="saveCredentials"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 active:bg-amber-700 disabled:opacity-60 disabled:cursor-not-allowed rounded-lg transition-colors">
                        <svg wire:loading wire:target="saveCredentials"
                            class="animate-spin h-3.5 w-3.5 text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="saveCredentials">Simpan Kredensial</span>
                        <span wire:loading wire:target="saveCredentials" style="display:none">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
