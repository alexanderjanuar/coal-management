<div class="space-y-5" wire:key="kontrak-{{ $client->id }}">
    @php
        $contracts = [
            ['label' => 'PPN',           'on' => (bool) $client->ppn_contract],
            ['label' => 'PPh 21',        'on' => (bool) $client->pph_contract],
            ['label' => 'PPh Unifikasi', 'on' => (bool) $client->bupot_contract],
            ['label' => 'PPh Badan',     'on' => (bool) $client->pph_badan_contract],
        ];
        $hasFile = (bool) $client->contract_file;
    @endphp

    {{-- ── Header + aksi dokumen ── --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Dokumen Kontrak</h3>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Berkas perjanjian layanan pajak klien.</p>
        </div>

        @if($hasFile)
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ $this->contractUrl }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5h5v5m0-5L10 14M5 7v12h12"/></svg>
                    Buka
                </a>
                <a href="{{ $this->contractUrl }}" download
                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14"/></svg>
                    Unduh
                </a>
                <button type="button" wire:click="remove" wire:confirm="Hapus dokumen kontrak ini?"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-white px-3 py-1.5 text-xs font-medium text-rose-600 shadow-sm transition hover:bg-rose-50 dark:border-rose-500/30 dark:bg-gray-900 dark:text-rose-400 dark:hover:bg-rose-500/10">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-7 0v12a1 1 0 001 1h6a1 1 0 001-1V7"/></svg>
                    Hapus
                </button>
            </div>
        @endif
    </div>

    {{-- ── Jenis kontrak (read-only; diubah di Edit Profil) ── --}}
    <div class="flex flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-3 dark:border-gray-800 dark:bg-gray-900">
        <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Jenis kontrak</span>
        @foreach($contracts as $c)
            @if($c['on'])
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/25">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>{{ $c['label'] }}
                </span>
            @else
                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-400 dark:bg-gray-800 dark:text-gray-500">{{ $c['label'] }}</span>
            @endif
        @endforeach
        <a href="{{ $this->editUrl() }}" class="ml-auto inline-flex items-center gap-1 text-xs font-medium text-indigo-600 transition hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
            Ubah
            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>

    {{-- ── Frame dokumen ── --}}
    @if($hasFile)
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-950/40">
            <div class="flex items-center gap-2 border-b border-gray-200 bg-white px-4 py-2.5 dark:border-gray-800 dark:bg-gray-900">
                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 4h8a2 2 0 012 2v12a2 2 0 01-2 2H8a2 2 0 01-2-2V6a2 2 0 012-2z M9 9h6M9 13h6M9 17h4"/></svg>
                <span class="truncate text-xs font-medium text-gray-700 dark:text-gray-300">{{ $this->fileName }}</span>
            </div>

            @if($this->isPdf)
                <iframe src="{{ $this->contractUrl }}#view=FitH" title="Dokumen Kontrak"
                        class="w-full bg-white" style="height: 72vh; min-height: 460px;"></iframe>
            @elseif($this->isImage)
                <div class="overflow-auto p-4" style="max-height: 72vh;">
                    <img src="{{ $this->contractUrl }}" alt="Dokumen Kontrak" class="mx-auto rounded-lg shadow-sm">
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Format ini tidak dapat ditampilkan langsung.</p>
                    <a href="{{ $this->contractUrl }}" download class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Unduh dokumen</a>
                </div>
            @endif
        </div>
    @endif

    {{-- ── Unggah / Ganti dokumen ── --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6">
        <div class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">
            {{ $hasFile ? 'Ganti Dokumen Kontrak' : 'Unggah Dokumen Kontrak' }}
        </div>

        <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-6 py-8 text-center transition hover:border-indigo-400 hover:bg-indigo-50/40 dark:border-gray-700 dark:bg-gray-800/40 dark:hover:border-indigo-500/50">
            <input type="file" wire:model="upload" accept="application/pdf,image/*" class="hidden">
            <svg class="h-9 w-9 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L8 8m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
            <span class="mt-3 text-sm font-medium text-gray-700 dark:text-gray-200">Klik untuk pilih file</span>
            <span class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">PDF atau gambar (jpg, png, webp) &middot; maks 10 MB</span>
        </label>

        {{-- Status unggah --}}
        <div wire:loading wire:target="upload" class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
            <svg class="h-4 w-4 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
            Mengunggah file...
        </div>

        @error('upload')
            <p class="mt-3 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror

        {{-- File terpilih + simpan --}}
        @if($upload && ! $errors->has('upload'))
            <div class="mt-4 flex flex-col gap-3 rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-800/40 sm:flex-row sm:items-center sm:justify-between" wire:loading.remove wire:target="upload">
                <div class="flex min-w-0 items-center gap-2.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 4h8a2 2 0 012 2v12a2 2 0 01-2 2H8a2 2 0 01-2-2V6a2 2 0 012-2z"/></svg>
                    </span>
                    <span class="truncate text-sm text-gray-700 dark:text-gray-200">{{ $upload->getClientOriginalName() }}</span>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <button type="button" wire:click="$set('upload', null)"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-500 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">
                        Batal
                    </button>
                    <button type="button" wire:click="save" wire:target="save" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-3.5 py-1.5 text-xs font-medium text-white shadow-sm transition hover:bg-gray-700 disabled:opacity-60 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-gray-200">
                        <span wire:loading.remove wire:target="save">{{ $hasFile ? 'Ganti Dokumen' : 'Simpan' }}</span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
