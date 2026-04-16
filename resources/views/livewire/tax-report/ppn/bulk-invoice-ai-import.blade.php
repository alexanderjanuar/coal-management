<div class="p-1">

    {{-- ═══════════════════════════════════════════════════════
         STEP: UPLOAD
    ════════════════════════════════════════════════════════ --}}
    @if ($step === 'upload')

        {{-- Header --}}
        <div class="mb-5 flex items-start gap-3">
            <div class="p-2 rounded-lg bg-primary-100 dark:bg-primary-900/30 shrink-0">
                <x-heroicon-o-sparkles class="w-5 h-5 text-primary-600 dark:text-primary-400" />
            </div>
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Upload Berkas Faktur</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Pilih 1–10 file faktur (PDF/gambar). AI akan membaca dan mengekstrak data setiap dokumen secara otomatis.
                </p>
            </div>
        </div>

        {{-- Validation errors --}}
        @error('uploadedFiles')
            <div class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 flex items-start gap-2">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-red-500 mt-0.5 shrink-0" />
                <p class="text-sm text-red-700 dark:text-red-300">{{ $message }}</p>
            </div>
        @enderror

        {{-- File Upload Zone --}}
        <div class="mb-5"
            x-data="{
                isDragging: false,
                handleDrop(e) {
                    this.isDragging = false;
                    const input = $refs.fileInput;
                    const dt = new DataTransfer();
                    // merge existing + dropped files (cap at 10)
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
                        ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                        : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/50 hover:border-primary-400 dark:hover:border-primary-500'"
                    class="border-2 border-dashed rounded-xl p-8 text-center transition-colors"
                >
                    <x-heroicon-o-cloud-arrow-up
                        :class="isDragging ? 'text-primary-500' : 'text-gray-400 dark:text-gray-500'"
                        class="w-10 h-10 mx-auto mb-3 transition-colors"
                    />
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Klik untuk memilih file, atau seret ke sini
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        PDF · JPG · PNG · WEBP — maks. 10MB per file · maks. 10 file
                    </p>

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

            {{-- Upload progress --}}
            <div wire:loading wire:target="uploadedFiles" class="mt-2 flex items-center gap-2 text-sm text-primary-600 dark:text-primary-400">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Mengunggah berkas ke server…
            </div>
        </div>

        {{-- File List Preview --}}
        @if (count($items) > 0)
            <div class="mb-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ count($items) }} file siap diproses
                    </h3>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300">
                        Maks. 10 file
                    </span>
                </div>

                <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                    @foreach ($items as $index => $item)
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm">
                            {{-- Icon by type --}}
                            @php
                                $ext = strtolower(pathinfo($item['filename'], PATHINFO_EXTENSION));
                            @endphp
                            <div class="shrink-0 w-8 h-8 rounded-lg flex items-center justify-center
                                {{ $ext === 'pdf' ? 'bg-red-100 dark:bg-red-900/30' : 'bg-blue-100 dark:bg-blue-900/30' }}">
                                @if ($ext === 'pdf')
                                    <x-heroicon-o-document class="w-4 h-4 text-red-600 dark:text-red-400" />
                                @else
                                    <x-heroicon-o-photo class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ $item['filename'] }}</p>
                                <p class="text-xs text-gray-400 uppercase">{{ strtoupper($ext) }}</p>
                            </div>

                            {{-- Status badge --}}
                            @php
                                $statusConfig = [
                                    'idle'       => ['bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300', 'Menunggu'],
                                    'processing' => ['bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400', 'Memproses…'],
                                    'completed'  => ['bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400', 'Selesai'],
                                    'error'      => ['bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400', 'Error'],
                                    'saved'      => ['bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400', 'Tersimpan'],
                                ];
                                [$badgeCls, $badgeLabel] = $statusConfig[$item['status']] ?? $statusConfig['idle'];
                            @endphp
                            <span class="shrink-0 text-xs px-2 py-0.5 rounded-full font-medium {{ $badgeCls }}">
                                {{ $badgeLabel }}
                            </span>

                            {{-- Remove button --}}
                            <button
                                type="button"
                                wire:click="removeItem({{ $index }})"
                                class="shrink-0 p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 hover:text-red-500 transition-colors"
                                title="Hapus file ini"
                            >
                                <x-heroicon-o-x-mark class="w-4 h-4" />
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Process Button --}}
            <div class="flex items-center justify-between gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    @if (count($items) === 1)
                        AI akan memproses 1 dokumen faktur.
                    @else
                        AI akan memproses {{ count($items) }} dokumen secara berurutan. Proses mungkin memerlukan beberapa saat.
                    @endif
                </p>
                <button
                    type="button"
                    wire:click="processAll"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 disabled:opacity-60 text-white text-sm font-semibold transition-colors shadow-sm"
                >
                    <span wire:loading.remove wire:target="processAll" class="inline-flex items-center gap-2">
                        <x-heroicon-o-sparkles class="w-4 h-4" />
                        Proses dengan AI
                    </span>
                    <span wire:loading wire:target="processAll" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Sedang Memproses…
                    </span>
                </button>
            </div>
        @else
            <div class="text-center py-8">
                <x-heroicon-o-document-arrow-up class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-3" />
                <p class="text-sm text-gray-500 dark:text-gray-400">Pilih berkas faktur di atas untuk memulai</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Bisa 1 file (faktur tunggal) atau hingga 10 file sekaligus</p>
            </div>
        @endif

    @endif {{-- end step upload --}}


    {{-- ═══════════════════════════════════════════════════════
         STEP: REVIEW
    ════════════════════════════════════════════════════════ --}}
    @if ($step === 'review')

        @php
            $totalItems     = count($items);
            $completedCount = $this->getCompletedCount();
            $errorCount     = $this->getErrorCount();
            $savedCount     = $this->getSavedCount();
            $readyToSave    = $this->getSelectedCompletedCount();
        @endphp

        {{-- Flash success --}}
        @if ($flashSuccess)
            <div class="mb-4 p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 flex items-start gap-2">
                <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 shrink-0 mt-0.5" />
                <p class="text-sm text-green-700 dark:text-green-300">{{ $flashSuccess }}</p>
            </div>
        @endif

        {{-- Flash errors (clean, no raw SQL) --}}
        @if (count($flashErrors) > 0)
            <div class="mb-4 rounded-xl border border-red-200 dark:border-red-800 overflow-hidden">
                <div class="flex items-center gap-2 px-4 py-2.5 bg-red-50 dark:bg-red-900/30">
                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-red-500 shrink-0" />
                    <p class="text-sm font-semibold text-red-700 dark:text-red-300">
                        {{ count($flashErrors) }} faktur gagal disimpan
                    </p>
                </div>
                <div class="divide-y divide-red-100 dark:divide-red-900">
                    @foreach ($flashErrors as $err)
                        <div class="px-4 py-3 bg-white dark:bg-gray-900 flex items-start gap-3">
                            <span class="shrink-0 text-xs font-bold text-red-400 w-5 text-right mt-0.5">#{{ $err['index'] }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate">
                                    {{ $err['filename'] }}
                                    @if (!empty($err['number']) && $err['number'] !== '-')
                                        <span class="text-gray-400"> · {{ $err['number'] }}</span>
                                    @endif
                                </p>
                                <p class="mt-0.5 text-xs text-red-600 dark:text-red-400">{{ $err['message'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Summary pill bar --}}
        <div class="mb-4 flex items-center gap-2 flex-wrap">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                <x-heroicon-o-document-duplicate class="w-3.5 h-3.5" /> {{ $totalItems }} total
            </span>
            @if ($completedCount)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
                    <x-heroicon-o-check-circle class="w-3.5 h-3.5" /> {{ $completedCount }} siap simpan
                </span>
            @endif
            @if ($errorCount)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">
                    <x-heroicon-o-x-circle class="w-3.5 h-3.5" /> {{ $errorCount }} error
                </span>
            @endif
            @if ($savedCount)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">
                    <x-heroicon-o-archive-box class="w-3.5 h-3.5" /> {{ $savedCount }} tersimpan
                </span>
            @endif
        </div>

        {{-- ── Item cards ─────────────────────────────────────────────── --}}
        <div class="space-y-3 mb-4 max-h-[58vh] overflow-y-auto pr-1">
            @foreach ($items as $index => $item)
                @php
                    $isCompleted = $item['status'] === 'completed';
                    $isError     = $item['status'] === 'error';
                    $isSaved     = $item['status'] === 'saved';
                    $isSelected  = $item['selected'] ?? true;
                    $f           = $item['form'];
                    $is12        = ($f['ppn_percentage'] ?? '11') === '12';
                    $isMasuk     = ($f['type'] ?? '') === 'Faktur Masuk';

                    // Card border / accent colour
                    $accent = match ($item['status']) {
                        'completed' => ['border-green-300 dark:border-green-700',  'bg-green-50 dark:bg-green-900/20'],
                        'error'     => ['border-red-300 dark:border-red-700',      'bg-red-50 dark:bg-red-900/20'],
                        'saved'     => ['border-blue-300 dark:border-blue-700',    'bg-blue-50 dark:bg-blue-900/20'],
                        default     => ['border-gray-200 dark:border-gray-700',    'bg-gray-50 dark:bg-gray-800'],
                    };
                    [$borderCls, $headerBg] = $accent;

                    // Status badge
                    [$badgeCls, $badgeLabel] = match ($item['status']) {
                        'completed'  => ['bg-green-100 dark:bg-green-800/60 text-green-700 dark:text-green-300', 'AI Selesai'],
                        'error'      => ['bg-red-100 dark:bg-red-800/60 text-red-700 dark:text-red-300', 'Error'],
                        'saved'      => ['bg-blue-100 dark:bg-blue-800/60 text-blue-700 dark:text-blue-300', 'Tersimpan'],
                        'processing' => ['bg-yellow-100 dark:bg-yellow-800/60 text-yellow-700 dark:text-yellow-300', 'Memproses…'],
                        default      => ['bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300', 'Menunggu'],
                    };

                    // Shared input classes
                    $inp  = 'w-full text-xs px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition';
                    $inpRo = 'w-full text-xs px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 cursor-default';
                    $sel  = 'w-full text-xs px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-primary-500';
                    $pfx  = 'shrink-0 px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 border border-r-0 border-gray-300 dark:border-gray-600 rounded-l-lg select-none';
                @endphp

                <div class="rounded-xl border {{ $borderCls }} overflow-hidden shadow-sm {{ !$isSelected && $isCompleted ? 'opacity-55' : '' }}">

                    {{-- ── Card header ── --}}
                    <div class="{{ $headerBg }} px-3 py-2 flex items-center gap-2.5">

                        {{-- Checkbox / icon --}}
                        @if ($isCompleted)
                            <input type="checkbox" wire:click="toggleSelection({{ $index }})" @checked($isSelected)
                                class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 shrink-0">
                        @elseif ($isSaved)
                            <x-heroicon-o-check-circle class="w-4 h-4 text-blue-500 shrink-0" />
                        @elseif ($isError)
                            <x-heroicon-o-x-circle class="w-4 h-4 text-red-500 shrink-0" />
                        @else
                            <x-heroicon-o-clock class="w-4 h-4 text-gray-400 shrink-0" />
                        @endif

                        {{-- File name + sub-info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-800 dark:text-gray-100 truncate leading-tight">
                                <span class="text-gray-400 dark:text-gray-500 font-normal">#{{ $index + 1 }}</span>
                                {{ $item['filename'] }}
                            </p>
                            @if (!empty($f['invoice_number']))
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate leading-tight mt-0.5">
                                    {{ $f['invoice_number'] }}
                                    @if (!empty($f['company_name']))
                                        · {{ $f['company_name'] }}
                                    @endif
                                </p>
                            @endif
                            @if ($isError)
                                <p class="text-[11px] text-red-500 dark:text-red-400 truncate mt-0.5">{{ $item['error'] }}</p>
                            @endif
                        </div>

                        {{-- Status badge --}}
                        <span class="shrink-0 text-[11px] px-2 py-0.5 rounded-full font-medium {{ $badgeCls }}">
                            {{ $badgeLabel }}
                        </span>

                        {{-- Actions --}}
                        @if ($isError)
                            <button type="button" wire:click="processItem({{ $index }})"
                                class="shrink-0 inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-orange-100 dark:bg-orange-900/30 hover:bg-orange-200 dark:hover:bg-orange-900/50 text-orange-700 dark:text-orange-400 text-[11px] font-medium transition-colors">
                                <x-heroicon-o-arrow-path class="w-3 h-3" /> Ulang
                            </button>
                        @endif
                        @if (!$isSaved)
                            <button type="button" wire:click="removeItem({{ $index }})"
                                class="shrink-0 p-1 rounded hover:bg-black/10 dark:hover:bg-white/10 text-gray-400 hover:text-red-500 transition-colors" title="Hapus">
                                <x-heroicon-o-x-mark class="w-4 h-4" />
                            </button>
                        @endif
                    </div>

                    {{-- ── Card body (completed / saved) ── --}}
                    @if ($isCompleted || $isSaved)
                        <div class="p-3 bg-white dark:bg-gray-900 grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-0 divide-y md:divide-y-0 md:divide-x divide-gray-100 dark:divide-gray-800">

                            {{-- ╔════════════════════════════════╗
                                 ║  LEFT — Identitas Faktur       ║
                                 ╚════════════════════════════════╝ --}}
                            <div class="py-2 md:py-0 md:pr-4 space-y-2.5">
                                <p class="text-[10px] uppercase tracking-wider font-semibold text-gray-400 dark:text-gray-500">Identitas Faktur</p>

                                {{-- Nomor Faktur --}}
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Nomor Faktur <span class="text-red-400">*</span></label>
                                    <input type="text" value="{{ $f['invoice_number'] }}"
                                        @if ($isCompleted) wire:change="updateField({{ $index }}, 'invoice_number', $event.target.value)" @else readonly @endif
                                        placeholder="010.000-00.00000000"
                                        class="{{ $isCompleted ? $inp : $inpRo }}">
                                </div>

                                {{-- Row: Tanggal + Jenis --}}
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Tanggal</label>
                                        <input type="date" value="{{ $f['invoice_date'] }}"
                                            @if ($isCompleted) wire:change="updateField({{ $index }}, 'invoice_date', $event.target.value)" @else readonly @endif
                                            class="{{ $isCompleted ? $inp : $inpRo }}">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Jenis</label>
                                        @if ($isCompleted)
                                            <select wire:change="updateField({{ $index }}, 'type', $event.target.value)" class="{{ $sel }}">
                                                <option value="Faktur Keluaran" @selected($f['type'] === 'Faktur Keluaran')>Keluaran</option>
                                                <option value="Faktur Masuk"    @selected($f['type'] === 'Faktur Masuk')>Masuk</option>
                                            </select>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-[11px] font-medium
                                                {{ $f['type'] === 'Faktur Keluaran' ? 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300' : 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300' }}">
                                                {{ $f['type'] === 'Faktur Keluaran' ? 'Keluaran' : 'Masuk' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Nama Perusahaan --}}
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Nama Perusahaan <span class="text-red-400">*</span></label>
                                    <input type="text" value="{{ $f['company_name'] }}"
                                        @if ($isCompleted) wire:change="updateField({{ $index }}, 'company_name', $event.target.value)" @else readonly @endif
                                        placeholder="PT. ..."
                                        class="{{ $isCompleted ? $inp : $inpRo }}">
                                </div>

                                {{-- NPWP --}}
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">NPWP</label>
                                    <input type="text" value="{{ $f['npwp'] }}"
                                        @if ($isCompleted) wire:change="updateField({{ $index }}, 'npwp', $event.target.value)" @else readonly @endif
                                        placeholder="00.000.000.0-000.000"
                                        class="{{ $isCompleted ? $inp : $inpRo }}">
                                </div>

                                {{-- Keterkaitan bisnis (Faktur Masuk only) --}}
                                @if ($isMasuk)
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Keterkaitan Bisnis</label>
                                        @if ($isCompleted)
                                            <select wire:change="updateField({{ $index }}, 'is_business_related', $event.target.value)" class="{{ $sel }}">
                                                <option value="1" @selected($f['is_business_related'])>Terkait Bisnis Utama</option>
                                                <option value="0" @selected(!$f['is_business_related'])>Tidak Terkait</option>
                                            </select>
                                        @else
                                            <span class="text-[11px] text-gray-600 dark:text-gray-400">
                                                {{ $f['is_business_related'] ? 'Terkait Bisnis Utama' : 'Tidak Terkait' }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            {{-- ╔════════════════════════════════╗
                                 ║  RIGHT — Nilai Pajak           ║
                                 ╚════════════════════════════════╝ --}}
                            <div class="py-2 md:py-0 md:pl-4 space-y-2.5">
                                <p class="text-[10px] uppercase tracking-wider font-semibold text-gray-400 dark:text-gray-500">Nilai Pajak</p>

                                {{-- Tarif PPN --}}
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Tarif PPN</label>
                                    @if ($isCompleted)
                                        <select wire:change="updateField({{ $index }}, 'ppn_percentage', $event.target.value)" class="{{ $sel }}">
                                            <option value="11" @selected(!$is12)>11%</option>
                                            <option value="12" @selected($is12)>12%</option>
                                        </select>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-[11px] font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                            {{ $f['ppn_percentage'] }}%
                                        </span>
                                    @endif
                                </div>

                                {{-- DPP Nilai Lainnya (tarif 12% only) --}}
                                @if ($is12)
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">DPP Nilai Lainnya</label>
                                        <div class="flex">
                                            <span class="{{ $pfx }}">Rp</span>
                                            <input type="number" step="1" value="{{ $f['dpp_nilai_lainnya'] ?? 0 }}"
                                                @if ($isCompleted) wire:change="updateField({{ $index }}, 'dpp_nilai_lainnya', $event.target.value)" @else readonly @endif
                                                class="flex-1 text-xs px-2.5 py-1.5 rounded-r-lg border border-gray-300 dark:border-gray-600 {{ $isCompleted ? 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-primary-500' : 'bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 cursor-default' }}">
                                        </div>
                                    </div>
                                @endif

                                {{-- DPP --}}
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        DPP @if ($is12) <span class="font-normal text-gray-400">(otomatis)</span> @endif
                                    </label>
                                    <div class="flex">
                                        <span class="{{ $pfx }}">Rp</span>
                                        <input type="number" step="1" value="{{ $f['dpp'] ?? 0 }}"
                                            @if ($isCompleted && !$is12) wire:change="updateField({{ $index }}, 'dpp', $event.target.value)" @else readonly @endif
                                            class="flex-1 text-xs px-2.5 py-1.5 rounded-r-lg border border-gray-300 dark:border-gray-600 {{ ($isCompleted && !$is12) ? 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-primary-500' : 'bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 cursor-default' }}">
                                    </div>
                                </div>

                                {{-- PPN (always read-only) --}}
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        PPN <span class="font-normal text-gray-400">(otomatis)</span>
                                    </label>
                                    <div class="flex">
                                        <span class="{{ $pfx }}">Rp</span>
                                        <input type="number" step="1" value="{{ $f['ppn'] ?? 0 }}" readonly
                                            class="flex-1 text-xs px-2.5 py-1.5 rounded-r-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 cursor-default font-medium">
                                    </div>
                                </div>

                                {{-- Storage path hint --}}
                                @if ($isSaved || $isCompleted)
                                    <div class="pt-1 border-t border-gray-100 dark:border-gray-800 flex items-start gap-1.5">
                                        <x-heroicon-o-folder class="w-3.5 h-3.5 text-gray-300 dark:text-gray-600 shrink-0 mt-0.5" />
                                        <p class="text-[10px] text-gray-400 dark:text-gray-500 leading-tight break-all">
                                            @if (!empty($f['client_type']))
                                                Tipe: <strong class="text-gray-600 dark:text-gray-400">{{ $f['client_type'] }}</strong> ·
                                            @endif
                                            File akan disimpan ke folder
                                            <strong class="text-gray-600 dark:text-gray-400">{{ $f['type'] === 'Faktur Keluaran' ? 'Penjualan' : 'Pembelian' }}/{{ $f['type'] }}</strong>
                                        </p>
                                    </div>
                                @endif
                            </div>

                        </div>{{-- end grid --}}
                    @elseif ($isError)
                        <div class="px-4 py-3 bg-white dark:bg-gray-900">
                            <p class="text-xs text-red-600 dark:text-red-400">{{ $item['error'] }}</p>
                            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                Pastikan dokumen berupa faktur pajak yang valid (PDF/gambar jelas), lalu klik Ulang.
                            </p>
                        </div>
                    @endif

                </div>{{-- end card --}}
            @endforeach
        </div>{{-- end items --}}

        {{-- ── Footer actions ── --}}
        <div class="flex items-center justify-between gap-3 pt-3 border-t border-gray-200 dark:border-gray-700">
            <button type="button" wire:click="goBackToUpload"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <x-heroicon-o-arrow-left class="w-4 h-4" /> Kembali & Tambah File
            </button>

            <div class="flex items-center gap-3">
                @if ($readyToSave > 0)
                    <p class="text-sm text-gray-500 dark:text-gray-400 hidden sm:block">
                        <strong class="text-gray-800 dark:text-gray-200">{{ $readyToSave }}</strong> faktur siap disimpan
                    </p>
                @endif

                <button type="button" wire:click="saveAll" wire:loading.attr="disabled"
                    @disabled($readyToSave === 0)
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold transition-colors shadow-sm">
                    <span wire:loading.remove wire:target="saveAll" class="inline-flex items-center gap-2">
                        <x-heroicon-o-cloud-arrow-up class="w-4 h-4" /> Simpan Semua ({{ $readyToSave }})
                    </span>
                    <span wire:loading wire:target="saveAll" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Menyimpan…
                    </span>
                </button>
            </div>
        </div>

    @endif {{-- end step review --}}

</div>
