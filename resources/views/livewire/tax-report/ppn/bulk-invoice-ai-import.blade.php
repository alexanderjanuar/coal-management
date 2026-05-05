<div>

    {{-- ══════════════════════════════════════════════════════
         STEP 1 — UPLOAD
    ══════════════════════════════════════════════════════ --}}
    @if ($step === 'upload')

        {{-- Page header --}}
        <div class="px-6 pt-6 pb-5 border-b border-gray-100 dark:border-gray-800 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center shrink-0">
                <x-heroicon-o-sparkles class="w-5 h-5 text-primary-600 dark:text-primary-400" />
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Upload Berkas Faktur</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    Pilih 1–10 file faktur (PDF atau gambar). AI akan membaca dan mengekstrak data setiap dokumen secara otomatis.
                </p>
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">

            {{-- Validation error --}}
            @error('uploadedFiles')
                <div class="p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 flex items-start gap-3">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
                    <p class="text-sm text-red-700 dark:text-red-300">{{ $message }}</p>
                </div>
            @enderror

            {{-- Drop Zone --}}
            <div
                x-data="{
                    isDragging: false,
                    handleDrop(e) {
                        this.isDragging = false;
                        const input = $refs.fileInput;
                        const dt = new DataTransfer();
                        const all = [...(input.files ?? []), ...e.dataTransfer.files].slice(0, 10);
                        all.forEach(f => dt.items.add(f));
                        input.files = dt.files;
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }"
            >
                <label
                    class="block cursor-pointer"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop($event)"
                >
                    <div
                        :class="isDragging
                            ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 scale-[1.01]'
                            : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/60 hover:border-primary-400 hover:bg-primary-50/50 dark:hover:border-primary-600'"
                        class="border-2 border-dashed rounded-2xl p-10 text-center transition-all duration-150"
                    >
                        <div class="w-14 h-14 rounded-2xl bg-white dark:bg-gray-700 shadow-sm flex items-center justify-center mx-auto mb-4">
                            <x-heroicon-o-cloud-arrow-up
                                :class="isDragging ? 'text-primary-500' : 'text-gray-400 dark:text-gray-400'"
                                class="w-7 h-7 transition-colors"
                            />
                        </div>
                        <p class="text-base font-semibold text-gray-700 dark:text-gray-200">
                            Klik untuk memilih file
                        </p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                            atau seret & lepas file di sini
                        </p>
                        <div class="mt-4 inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-xs text-gray-500 dark:text-gray-400">
                            <span>PDF</span><span class="text-gray-300">·</span>
                            <span>JPG</span><span class="text-gray-300">·</span>
                            <span>PNG</span><span class="text-gray-300">·</span>
                            <span>WEBP</span>
                            <span class="text-gray-300">—</span>
                            <span>maks. 10 MB · 10 file</span>
                        </div>
                        <input
                            x-ref="fileInput"
                            type="file"
                            multiple
                            accept=".pdf,.jpg,.jpeg,.png,.webp"
                            class="sr-only"
                            wire:model="uploadedFiles"
                        >
                    </div>
                </label>

                <div wire:loading wire:target="uploadedFiles" class="mt-3 flex items-center gap-2 text-sm text-primary-600 dark:text-primary-400">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Mengunggah ke server…
                </div>
            </div>

            {{-- File list --}}
            @if (count($items) > 0)
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            {{ count($items) }} file dipilih
                        </p>
                        <span class="text-xs px-3 py-1 rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 font-medium">
                            Maks. 10 file
                        </span>
                    </div>

                    <div class="space-y-2 max-h-56 overflow-y-auto pr-1 rounded-xl">
                        @foreach ($items as $index => $item)
                            @php $ext = strtolower(pathinfo($item['filename'], PATHINFO_EXTENSION)); @endphp
                            <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm">
                                <div class="shrink-0 w-9 h-9 rounded-lg flex items-center justify-center
                                    {{ $ext === 'pdf' ? 'bg-red-100 dark:bg-red-900/40' : 'bg-blue-100 dark:bg-blue-900/40' }}">
                                    @if ($ext === 'pdf')
                                        <x-heroicon-o-document class="w-5 h-5 text-red-500 dark:text-red-400" />
                                    @else
                                        <x-heroicon-o-photo class="w-5 h-5 text-blue-500 dark:text-blue-400" />
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ $item['filename'] }}</p>
                                    <p class="text-xs text-gray-400 uppercase tracking-wide mt-0.5">{{ strtoupper($ext) }}</p>
                                </div>
                                <button type="button" wire:click="removeItem({{ $index }})"
                                    class="shrink-0 w-7 h-7 flex items-center justify-center rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 text-gray-300 hover:text-red-500 dark:hover:text-red-400 transition-colors">
                                    <x-heroicon-o-x-mark class="w-4 h-4" />
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4 pt-2 border-t border-gray-100 dark:border-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        AI akan memproses <strong class="text-gray-700 dark:text-gray-300">{{ count($items) }}</strong> dokumen secara berurutan
                    </p>
                    <button type="button" wire:click="processAll" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 active:bg-primary-800 disabled:opacity-60 disabled:cursor-not-allowed text-white text-sm font-semibold transition-colors shadow-sm">
                        <span wire:loading.remove wire:target="processAll" class="inline-flex items-center gap-2">
                            <x-heroicon-o-sparkles class="w-4 h-4" />
                            Proses dengan AI
                        </span>
                        <span wire:loading wire:target="processAll" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Sedang Memproses…
                        </span>
                    </button>
                </div>

            @else
                <div class="text-center py-8">
                    <x-heroicon-o-document-arrow-up class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-3" />
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Pilih berkas faktur untuk memulai</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Bisa 1 file (faktur tunggal) atau hingga 10 file sekaligus</p>
                </div>
            @endif

        </div>

    @endif {{-- end upload --}}


    {{-- ══════════════════════════════════════════════════════
         STEP 2 — REVIEW & EDIT
    ══════════════════════════════════════════════════════ --}}
    @if ($step === 'review')

        @php
            $totalItems     = count($items);
            $completedCount = $this->getCompletedCount();
            $errorCount     = $this->getErrorCount();
            $savedCount     = $this->getSavedCount();
            $readyToSave    = $this->getSelectedCompletedCount();
        @endphp

        {{-- Page header --}}
        <div class="px-6 pt-6 pb-4 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Periksa & Konfirmasi Data</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        Koreksi data yang perlu diperbaiki, lalu simpan faktur yang sudah benar.
                    </p>
                </div>

                {{-- Summary chips --}}
                <div class="flex items-center gap-2 flex-wrap shrink-0">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                        <x-heroicon-o-document-duplicate class="w-3.5 h-3.5" />
                        {{ $totalItems }} file
                    </span>
                    @if ($completedCount)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
                            <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                            {{ $completedCount }} siap
                        </span>
                    @endif
                    @if ($errorCount)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">
                            <x-heroicon-o-x-circle class="w-3.5 h-3.5" />
                            {{ $errorCount }} error
                        </span>
                    @endif
                    @if ($savedCount)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">
                            <x-heroicon-o-archive-box class="w-3.5 h-3.5" />
                            {{ $savedCount }} tersimpan
                        </span>
                    @endif
                </div>
            </div>

            {{-- Flash success --}}
            @if ($flashSuccess)
                <div class="mt-4 p-3 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 flex items-center gap-3">
                    <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 shrink-0" />
                    <p class="text-sm font-medium text-green-700 dark:text-green-300">{{ $flashSuccess }}</p>
                </div>
            @endif

            {{-- Flash errors --}}
            @if (count($flashErrors) > 0)
                <div class="mt-4 rounded-xl border border-red-200 dark:border-red-800 overflow-hidden">
                    <div class="flex items-center gap-2 px-4 py-2.5 bg-red-50 dark:bg-red-900/30">
                        <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-red-500 shrink-0" />
                        <p class="text-sm font-semibold text-red-700 dark:text-red-300">
                            {{ count($flashErrors) }} faktur gagal disimpan
                        </p>
                    </div>
                    <div class="divide-y divide-red-100 dark:divide-red-900/60">
                        @foreach ($flashErrors as $err)
                            <div class="px-4 py-3 bg-white dark:bg-gray-900 flex items-start gap-3">
                                <span class="shrink-0 text-xs font-bold text-red-400 mt-0.5">#{{ $err['index'] }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">
                                        {{ $err['filename'] }}
                                        @if (!empty($err['number']) && $err['number'] !== '-')
                                            <span class="text-gray-400 font-normal"> · {{ $err['number'] }}</span>
                                        @endif
                                    </p>
                                    <p class="mt-0.5 text-xs text-red-600 dark:text-red-400">{{ $err['message'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- ── Item list ─────────────────────────────────────────── --}}
        <div class="px-6 py-5 space-y-4 max-h-[62vh] overflow-y-auto">

            @foreach ($items as $index => $item)
                @php
                    $isCompleted = $item['status'] === 'completed';
                    $isError     = $item['status'] === 'error';
                    $isSaved     = $item['status'] === 'saved';
                    $isSelected  = $item['selected'] ?? true;
                    $f           = $item['form'];
                    $is12        = ($f['ppn_percentage'] ?? '11') === '12';
                    $isMasuk     = ($f['type'] ?? '') === 'Faktur Masuk';

                    [$badgeCls, $badgeLabel] = match ($item['status']) {
                        'completed'  => ['bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300', 'AI Selesai'],
                        'error'      => ['bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400', 'Error'],
                        'saved'      => ['bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300', 'Tersimpan'],
                        'processing' => ['bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300', 'Memproses…'],
                        default      => ['bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400', 'Menunggu'],
                    };

                    $inp   = 'w-full text-sm px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition placeholder-gray-300 dark:placeholder-gray-600';
                    $inpRo = 'w-full text-sm px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40 text-gray-500 dark:text-gray-400 select-none';
                    $sel   = 'w-full text-sm px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition cursor-pointer';
                @endphp

                <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden
                    {{ !$isSelected && $isCompleted ? 'opacity-50' : '' }}">

                    {{-- ── Card header ─────────────────────────────── --}}
                    <div class="px-5 py-3.5 flex items-center gap-3 border-b border-gray-100 dark:border-gray-800">

                        {{-- Selection / status icon --}}
                        @if ($isCompleted)
                            <input type="checkbox"
                                wire:click="toggleSelection({{ $index }})"
                                @checked($isSelected)
                                class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 shrink-0 cursor-pointer">
                        @elseif ($isSaved)
                            <x-heroicon-o-check-circle class="w-5 h-5 text-primary-500 dark:text-primary-400 shrink-0" />
                        @elseif ($isError)
                            <x-heroicon-o-x-circle class="w-5 h-5 text-red-500 shrink-0" />
                        @else
                            <x-heroicon-o-clock class="w-5 h-5 text-gray-300 dark:text-gray-600 shrink-0" />
                        @endif

                        {{-- File info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline gap-2">
                                <span class="text-xs font-medium text-gray-400 dark:text-gray-500 shrink-0 tabular-nums">#{{ $index + 1 }}</span>
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $item['filename'] }}</p>
                            </div>
                            @if (!empty($f['invoice_number']))
                                <p class="text-xs text-gray-400 dark:text-gray-500 truncate mt-0.5 font-mono">
                                    {{ $f['invoice_number'] }}
                                    @if (!empty($f['company_name']))
                                        <span class="font-sans">· {{ $f['company_name'] }}</span>
                                    @endif
                                </p>
                            @endif
                        </div>

                        {{-- Status badge --}}
                        <span class="shrink-0 inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full font-medium {{ $badgeCls }}">
                            @if ($item['status'] === 'processing')
                                <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                            @endif
                            {{ $badgeLabel }}
                        </span>

                        {{-- Actions --}}
                        @if ($isError)
                            <button type="button" wire:click="processItem({{ $index }})"
                                class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/40 text-amber-700 dark:text-amber-400 text-xs font-medium transition-colors">
                                <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                                Coba Ulang
                            </button>
                        @endif
                        @if (!$isSaved)
                            <button type="button" wire:click="removeItem({{ $index }})"
                                class="shrink-0 w-8 h-8 flex items-center justify-center rounded-lg text-gray-300 dark:text-gray-600 hover:text-red-400 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <x-heroicon-o-x-mark class="w-4 h-4" />
                            </button>
                        @endif
                    </div>

                    {{-- ── Body ────────────────────────────────────── --}}
                    @if ($isCompleted || $isSaved)

                        {{-- Faktur type row --}}
                        <div class="px-5 py-3 flex items-center justify-between gap-4 bg-gray-50/60 dark:bg-gray-800/30 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-2 h-2 rounded-full shrink-0 {{ $isMasuk ? 'bg-amber-400 dark:bg-amber-500' : 'bg-primary-500 dark:bg-primary-400' }}"></span>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    {{ $isMasuk ? 'Faktur Masukan' : 'Faktur Keluaran' }}
                                </span>
                                <span class="text-xs text-gray-400 dark:text-gray-500 truncate hidden sm:block">
                                    — {{ $isMasuk ? 'client sebagai pembeli, perusahaan diisi supplier' : 'client sebagai penjual, perusahaan diisi pembeli' }}
                                </span>
                            </div>
                            @if ($isCompleted)
                                <select
                                    wire:change="updateField({{ $index }}, 'type', $event.target.value)"
                                    class="shrink-0 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 cursor-pointer transition">
                                    <option value="Faktur Keluaran" @selected(!$isMasuk)>Faktur Keluaran</option>
                                    <option value="Faktur Masuk"    @selected($isMasuk)>Faktur Masukan</option>
                                </select>
                            @endif
                        </div>

                        {{-- ── Two-column form ─────────────────────── --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-gray-100 dark:divide-gray-800">

                            {{-- LEFT — Identitas --}}
                            <div class="px-6 pt-6 pb-6 space-y-5">

                                {{-- Section label --}}
                                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                    Identitas Faktur
                                </p>

                                {{-- Nomor Faktur --}}
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                                        Nomor Faktur <span class="text-red-400">*</span>
                                    </label>
                                    <input type="text"
                                        value="{{ $f['invoice_number'] }}"
                                        @if ($isCompleted) wire:change="updateField({{ $index }}, 'invoice_number', $event.target.value)" @else readonly @endif
                                        placeholder="010.000-00.00000000"
                                        class="{{ $isCompleted ? $inp : $inpRo }} font-mono tracking-wide">
                                </div>

                                {{-- Tanggal + Tipe Client --}}
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Tanggal</label>
                                        <input type="date"
                                            value="{{ $f['invoice_date'] }}"
                                            @if ($isCompleted) wire:change="updateField({{ $index }}, 'invoice_date', $event.target.value)" @else readonly @endif
                                            class="{{ $isCompleted ? $inp : $inpRo }}">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Tipe Client</label>
                                        <input type="text" value="{{ $f['client_type'] ?? '—' }}" readonly class="{{ $inpRo }}">
                                    </div>
                                </div>

                                {{-- Nama perusahaan --}}
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                                        {{ $isMasuk ? 'Nama Supplier' : 'Nama Pembeli' }}
                                        <span class="text-red-400">*</span>
                                    </label>
                                    <input type="text"
                                        value="{{ $f['company_name'] }}"
                                        @if ($isCompleted) wire:change="updateField({{ $index }}, 'company_name', $event.target.value)" @else readonly @endif
                                        placeholder="PT. ..."
                                        class="{{ $isCompleted ? $inp : $inpRo }}">
                                </div>

                                {{-- NPWP --}}
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">NPWP</label>
                                    <input type="text"
                                        value="{{ $f['npwp'] }}"
                                        @if ($isCompleted) wire:change="updateField({{ $index }}, 'npwp', $event.target.value)" @else readonly @endif
                                        placeholder="00.000.000.0-000.000"
                                        class="{{ $isCompleted ? $inp : $inpRo }} font-mono tracking-wide">
                                </div>

                                {{-- Keterkaitan bisnis --}}
                                @if ($isMasuk)
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Keterkaitan Bisnis</label>
                                        @if ($isCompleted)
                                            <select wire:change="updateField({{ $index }}, 'is_business_related', $event.target.value)" class="{{ $sel }}">
                                                <option value="1" @selected($f['is_business_related'])>✅ Terkait Aktivitas Bisnis Utama</option>
                                                <option value="0" @selected(!$f['is_business_related'])>⚠️ Tidak Terkait Bisnis (Personal/Non-Operasional)</option>
                                            </select>
                                        @else
                                            <p class="{{ $inpRo }}">
                                                {{ $f['is_business_related'] ? '✅ Terkait Aktivitas Bisnis Utama' : '⚠️ Tidak Terkait Bisnis' }}
                                            </p>
                                        @endif
                                    </div>
                                @endif

                            </div>

                            {{-- RIGHT — Nilai Pajak --}}
                            <div class="px-6 pt-6 pb-6 space-y-5">

                                {{-- Section label --}}
                                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                    Nilai Pajak
                                </p>

                                {{-- Tarif PPN --}}
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Tarif PPN</label>
                                    @if ($isCompleted)
                                        <select wire:change="updateField({{ $index }}, 'ppn_percentage', $event.target.value)" class="{{ $sel }}">
                                            <option value="11" @selected(!$is12)>11%</option>
                                            <option value="12" @selected($is12)>12%</option>
                                        </select>
                                    @else
                                        <div class="{{ $inpRo }} font-semibold">{{ $f['ppn_percentage'] }}%</div>
                                    @endif
                                </div>

                                {{-- DPP Nilai Lainnya (12% only) --}}
                                @if ($is12)
                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">DPP Nilai Lainnya</label>
                                        @if ($isCompleted)
                                            <div
                                                x-data="{
                                                    raw: '{{ $f['dpp_nilai_lainnya'] ?? 0 }}',
                                                    display: '',
                                                    init() { this.display = new Intl.NumberFormat('id-ID').format(parseInt(this.raw)||0) },
                                                    onFocus() { this.display = this.raw },
                                                    onBlur() {
                                                        const n = parseInt(String(this.display).replace(/[^\d]/g,''))||0;
                                                        this.raw = String(n);
                                                        this.display = new Intl.NumberFormat('id-ID').format(n);
                                                        $wire.updateField({{ $index }}, 'dpp_nilai_lainnya', this.raw);
                                                    }
                                                }"
                                                class="flex rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden focus-within:border-primary-500 focus-within:ring-1 focus-within:ring-primary-500 transition"
                                            >
                                                <span class="shrink-0 px-3 flex items-center text-sm text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800/60 border-r border-gray-300 dark:border-gray-600 select-none">Rp</span>
                                                <input type="text" x-model="display" @focus="onFocus()" @blur="onBlur()"
                                                    class="flex-1 min-w-0 text-sm px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 outline-none tabular-nums">
                                            </div>
                                        @else
                                            <div class="flex rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                                <span class="shrink-0 px-3 flex items-center text-sm text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800/40 border-r border-gray-200 dark:border-gray-700 select-none">Rp</span>
                                                <div class="flex-1 px-3 py-2 text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/40 tabular-nums">
                                                    {{ number_format($f['dpp_nilai_lainnya'] ?? 0, 0, ',', '.') }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                {{-- DPP --}}
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                                        DPP — Dasar Pengenaan Pajak
                                        @if ($is12)
                                            <span class="text-gray-400 dark:text-gray-500 font-normal"> · otomatis</span>
                                        @endif
                                    </label>
                                    @if ($isCompleted && !$is12)
                                        <div
                                            x-data="{
                                                raw: '{{ $f['dpp'] ?? 0 }}',
                                                display: '',
                                                init() { this.display = new Intl.NumberFormat('id-ID').format(parseInt(this.raw)||0) },
                                                onFocus() { this.display = this.raw },
                                                onBlur() {
                                                    const n = parseInt(String(this.display).replace(/[^\d]/g,''))||0;
                                                    this.raw = String(n);
                                                    this.display = new Intl.NumberFormat('id-ID').format(n);
                                                    $wire.updateField({{ $index }}, 'dpp', this.raw);
                                                }
                                            }"
                                            class="flex rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden focus-within:border-primary-500 focus-within:ring-1 focus-within:ring-primary-500 transition"
                                        >
                                            <span class="shrink-0 px-3 flex items-center text-sm text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800/60 border-r border-gray-300 dark:border-gray-600 select-none">Rp</span>
                                            <input type="text" x-model="display" @focus="onFocus()" @blur="onBlur()"
                                                class="flex-1 min-w-0 text-sm px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 outline-none tabular-nums">
                                        </div>
                                    @else
                                        <div class="flex rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                            <span class="shrink-0 px-3 flex items-center text-sm text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800/40 border-r border-gray-200 dark:border-gray-700 select-none">Rp</span>
                                            <div class="flex-1 px-3 py-2 text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/40 tabular-nums">
                                                {{ number_format($f['dpp'] ?? 0, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- PPN — result, highlighted --}}
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                                        PPN — dihitung otomatis
                                    </label>
                                    <div class="flex items-center gap-3 px-4 py-2.5 rounded-lg border border-primary-200 dark:border-primary-800/50 bg-primary-50/60 dark:bg-primary-900/10">
                                        <span class="text-xs font-medium text-primary-500 dark:text-primary-400 shrink-0">Rp</span>
                                        <span class="text-sm font-semibold text-primary-700 dark:text-primary-300 tabular-nums flex-1">
                                            {{ number_format($f['ppn'] ?? 0, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Storage path hint --}}
                                <div class="pt-2 border-t border-gray-100 dark:border-gray-800 flex items-start gap-2">
                                    <x-heroicon-o-folder class="w-4 h-4 text-gray-300 dark:text-gray-600 shrink-0 mt-0.5" />
                                    <p class="text-xs text-gray-400 dark:text-gray-500 leading-relaxed">
                                        Disimpan ke <strong class="text-gray-500 dark:text-gray-400 font-medium">
                                            {{ $isMasuk ? 'Pembelian' : 'Penjualan' }}/{{ $f['type'] }}
                                        </strong>
                                        @if (!empty($f['client_type']))
                                            · Tipe: <strong class="text-gray-500 dark:text-gray-400 font-medium">{{ $f['client_type'] }}</strong>
                                        @endif
                                    </p>
                                </div>

                            </div>

                        </div>{{-- end 2-col --}}

                    @elseif ($isError)
                        <div class="px-6 py-5">
                            <div class="flex items-start gap-3">
                                <x-heroicon-o-exclamation-circle class="w-5 h-5 text-red-400 shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-sm font-semibold text-red-600 dark:text-red-400">{{ $item['error'] }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Pastikan dokumen berupa faktur pajak yang valid dan terbaca jelas (PDF atau gambar resolusi cukup), lalu klik <strong>Coba Ulang</strong>.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>{{-- end card --}}
            @endforeach

        </div>{{-- end list --}}

        {{-- ── Sticky footer ──────────────────────────────────── --}}
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-900/80 backdrop-blur flex items-center justify-between gap-4">
            <button type="button" wire:click="goBackToUpload"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Kembali
            </button>

            <div class="flex items-center gap-4">
                @if ($readyToSave > 0)
                    <p class="text-sm text-gray-500 dark:text-gray-400 hidden sm:block">
                        <strong class="text-gray-800 dark:text-gray-200">{{ $readyToSave }}</strong> faktur siap disimpan
                    </p>
                @endif

                <button type="button" wire:click="saveAll" wire:loading.attr="disabled"
                    @disabled($readyToSave === 0)
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-sm transition-colors shadow-sm
                        {{ $readyToSave > 0
                            ? 'bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white'
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed' }}">
                    <span wire:loading.remove wire:target="saveAll" class="inline-flex items-center gap-2">
                        <x-heroicon-o-cloud-arrow-up class="w-4 h-4" />
                        Simpan {{ $readyToSave > 0 ? "({$readyToSave})" : 'Semua' }}
                    </span>
                    <span wire:loading wire:target="saveAll" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Menyimpan…
                    </span>
                </button>
            </div>
        </div>

    @endif {{-- end review --}}

</div>
