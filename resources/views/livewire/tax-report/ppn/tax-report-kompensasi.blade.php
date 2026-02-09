<div class="space-y-8">

    {{-- ============================================
    SECTION 1: CALCULATION SUMMARY
    ============================================ --}}
    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
            Ringkasan Perhitungan Kompensasi
        </h2>

        {{-- Four-Step Calculation Cards --}}
        <div class="grid gap-4 lg:grid-cols-4">

            {{-- Card 1: Saldo Awal --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                <div class="mb-2 flex items-center gap-2">
                    <span
                        class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold dark:bg-gray-800">1</span>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Saldo Awal</span>
                </div>
                <p class="text-xl font-bold text-gray-900 dark:text-white">
                    Rp {{ number_format(abs($saldoSebelumKompensasi), 0, ',', '.') }}
                </p>
                <div class="mt-2 flex items-center gap-1.5">
                    <div
                        class="h-1.5 w-1.5 rounded-full {{ $statusSebelum === 'Lebih Bayar' ? 'bg-green-500' : ($statusSebelum === 'Kurang Bayar' ? 'bg-orange-500' : 'bg-gray-400') }}">
                    </div>
                    <span class="text-xs text-gray-600 dark:text-gray-400">{{ $statusSebelum }}</span>
                </div>
            </div>

            {{-- Card 2: Kompensasi Diterima --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                <div class="mb-2 flex items-center gap-2">
                    <span
                        class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold dark:bg-gray-800">2</span>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Kompensasi Diterima</span>
                </div>
                <p class="text-xl font-bold text-gray-900 dark:text-white">
                    Rp {{ number_format($kompensasiDiterima, 0, ',', '.') }}
                </p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ $receivedCompensations->count() }} transaksi
                </p>
            </div>

            {{-- Card 3: Kompensasi Manual --}}
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-500/10">
                <div class="mb-2 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span
                            class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold text-blue-600 dark:bg-blue-500/20 dark:text-blue-400">3</span>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Kompensasi Manual</span>
                    </div>
                    <button wire:click="openManualKompensasiForm"
                        class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                        title="Edit Kompensasi Manual">
                        <x-filament::icon icon="heroicon-m-pencil-square" class="h-4 w-4" />
                    </button>
                </div>
                <p class="text-xl font-bold text-blue-700 dark:text-blue-400">
                    Rp {{ number_format($manualKompensasi, 0, ',', '.') }}
                </p>
                <p class="mt-2 text-xs text-gray-600 dark:text-gray-400 truncate" title="{{ $manualKompensasiNotes }}">
                    {{ $manualKompensasiNotes ?: 'Tidak ada catatan' }}
                </p>
            </div>

            {{-- Card 4: Saldo Akhir --}}
            <div
                class="rounded-lg border-2 p-4 {{ $statusSetelah === 'Lebih Bayar' ? 'border-green-500 bg-green-50 dark:border-green-600 dark:bg-green-500/10' : ($statusSetelah === 'Kurang Bayar' ? 'border-orange-500 bg-orange-50 dark:border-orange-600 dark:bg-orange-500/10' : 'border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-900') }}">
                <div class="mb-2 flex items-center gap-2">
                    <span
                        class="flex h-6 w-6 items-center justify-center rounded-full {{ $statusSetelah === 'Lebih Bayar' ? 'bg-green-100 dark:bg-green-500/20' : ($statusSetelah === 'Kurang Bayar' ? 'bg-orange-100 dark:bg-orange-500/20' : 'bg-gray-100 dark:bg-gray-800') }} text-xs font-semibold">
                        <x-filament::icon icon="heroicon-m-check"
                            class="h-3.5 w-3.5 {{ $statusSetelah === 'Lebih Bayar' ? 'text-green-700 dark:text-green-400' : ($statusSetelah === 'Kurang Bayar' ? 'text-orange-700 dark:text-orange-400' : 'text-gray-600 dark:text-gray-400') }}" />
                    </span>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Saldo Akhir</span>
                </div>
                <p
                    class="text-xl font-bold {{ $statusSetelah === 'Lebih Bayar' ? 'text-green-700 dark:text-green-400' : ($statusSetelah === 'Kurang Bayar' ? 'text-orange-700 dark:text-orange-400' : 'text-gray-900 dark:text-white') }}">
                    Rp {{ number_format(abs($saldoSetelahKompensasi), 0, ',', '.') }}
                </p>
                <div class="mt-2 flex items-center gap-1.5">
                    <div
                        class="h-1.5 w-1.5 rounded-full {{ $statusSetelah === 'Lebih Bayar' ? 'bg-green-500' : ($statusSetelah === 'Kurang Bayar' ? 'bg-orange-500' : 'bg-gray-400') }}">
                    </div>
                    <span
                        class="text-xs font-medium {{ $statusSetelah === 'Lebih Bayar' ? 'text-green-700 dark:text-green-400' : ($statusSetelah === 'Kurang Bayar' ? 'text-orange-700 dark:text-orange-400' : 'text-gray-600 dark:text-gray-400') }}">
                        {{ $statusSetelah }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Calculation Formula --}}
        @if($manualKompensasi > 0)
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-3 dark:border-blue-800 dark:bg-blue-500/5">
            <div class="flex items-center gap-2 text-sm">
                <x-filament::icon icon="heroicon-o-calculator" class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                <span class="font-medium text-gray-700 dark:text-gray-300">Formula:</span>
                <span class="text-gray-600 dark:text-gray-400">
                    {{ number_format(abs($saldoSebelumKompensasi), 0, ',', '.') }}
                    - {{ number_format($kompensasiDiterima, 0, ',', '.') }}
                    - {{ number_format($manualKompensasi, 0, ',', '.') }}
                    = <strong>{{ number_format(abs($saldoSetelahKompensasi), 0, ',', '.') }}</strong>
                </span>
            </div>
        </div>
        @endif
    </div>

    {{-- ============================================
    SECTION 2: NEXT PERIOD COMPENSATION
    ============================================ --}}
    @if($statusSetelah === 'Lebih Bayar' && $kompensasiTersedia > 0)
    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
            Kompensasi ke Periode Berikutnya
        </h2>

        @if($nextMonthExists)
        {{-- Next Month Card --}}
        <div
            class="rounded-xl border border-gray-200 bg-gradient-to-br from-gray-50 to-white p-6 dark:border-gray-700 dark:from-gray-900 dark:to-gray-800">
            {{-- Header --}}
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/20">
                        <x-filament::icon icon="heroicon-o-calendar-days" class="h-7 w-7 text-white" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $this->nextMonthInfo['month'] }} {{ $this->nextMonthInfo['year'] }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Periode target kompensasi
                        </p>
                    </div>
                </div>
            </div>

            {{-- Info Grid --}}
            <div class="mb-6 grid gap-4 md:grid-cols-3">
                {{-- Status Card --}}
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex items-center gap-2 mb-2">
                        <x-filament::icon icon="heroicon-o-clipboard-document-check" class="h-4 w-4 text-gray-400" />
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Status Periode</span>
                    </div>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ $this->nextMonthInfo['status'] }}
                    </p>
                </div>

                {{-- Current Balance Card --}}
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex items-center gap-2 mb-2">
                        <x-filament::icon icon="heroicon-o-banknotes" class="h-4 w-4 text-gray-400" />
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Saldo Saat Ini</span>
                    </div>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ $this->nextMonthInfo['formatted_saldo'] }}
                    </p>
                </div>

                {{-- Available to Compensate Card --}}
                <div
                    class="rounded-lg border-2 border-green-200 bg-gradient-to-br from-green-50 to-emerald-50 p-4 dark:border-green-800 dark:from-green-950 dark:to-emerald-950">
                    <div class="flex items-center gap-2 mb-2">
                        <x-filament::icon icon="heroicon-o-arrow-trending-up"
                            class="h-4 w-4 text-green-600 dark:text-green-400" />
                        <span class="text-xs font-medium text-green-700 dark:text-green-300">Dapat Dikompensasi</span>
                    </div>
                    <p class="text-lg font-bold text-green-700 dark:text-green-300">
                        Rp {{ number_format($maxCompensationAmount, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- Description Box --}}
            <div class="mb-6 rounded-lg border border-blue-100 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950/30">
                <div class="flex gap-3">
                    <x-filament::icon icon="heroicon-o-information-circle"
                        class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                    <div class="text-sm text-blue-900 dark:text-blue-100">
                        <p class="font-medium mb-1">{{ $this->compensationTypeDescription }}</p>
                        <p class="text-xs text-blue-700 dark:text-blue-300">
                            Kompensasi akan mengurangi pajak terutang periode {{ $this->nextMonthInfo['month'] }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Action Button --}}
            @if($this->canCreateCompensation)
            <div class="flex justify-end">
                <x-filament::button wire:click="openCompensationModal" icon="heroicon-m-arrow-right-circle" size="lg">
                    Buat Kompensasi ke {{ $this->nextMonthInfo['month'] }}
                </x-filament::button>
            </div>
            @endif
        </div>
        @else
        {{-- Next Month Not Created Yet --}}
        <div
            class="rounded-xl border-2 border-dashed border-orange-200 bg-orange-50/50 p-8 text-center dark:border-orange-800 dark:bg-orange-950/20">
            <div
                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900/30">
                <x-filament::icon icon="heroicon-o-calendar-days"
                    class="h-8 w-8 text-orange-600 dark:text-orange-400" />
            </div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                Periode Berikutnya Belum Dibuat
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Buat laporan pajak untuk bulan berikutnya terlebih dahulu untuk dapat menggunakan kompensasi lebih bayar
            </p>
            <div class="inline-flex items-center gap-2 text-xs text-orange-700 dark:text-orange-300">
                <x-filament::icon icon="heroicon-m-arrow-right" class="h-4 w-4" />
                <span>Anda dapat membuat periode baru di menu Laporan Pajak</span>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ============================================
    SECTION 3: COMPENSATION HISTORY
    ============================================ --}}
    <div class="space-y-4" x-data="{ activeTab: 'received' }">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
            Riwayat Kompensasi
        </h2>

        {{-- Tab Navigation --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex gap-6">
                <button @click="activeTab = 'received'"
                    :class="activeTab === 'received' ? 'border-gray-900 text-gray-900 dark:border-white dark:text-white' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                    class="flex items-center gap-2 border-b-2 px-1 py-3 text-sm font-medium">
                    <x-filament::icon icon="heroicon-o-arrow-down-circle" class="h-5 w-5" />
                    <span>Diterima</span>
                    <span
                        :class="activeTab === 'received' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-100 text-gray-600 dark:bg-gray-800'"
                        class="rounded-full px-2 py-0.5 text-xs font-medium">
                        {{ $receivedCompensations->count() }}
                    </span>
                </button>

                <button @click="activeTab = 'given'"
                    :class="activeTab === 'given' ? 'border-gray-900 text-gray-900 dark:border-white dark:text-white' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                    class="flex items-center gap-2 border-b-2 px-1 py-3 text-sm font-medium">
                    <x-filament::icon icon="heroicon-o-arrow-right-circle" class="h-5 w-5" />
                    <span>Diberikan</span>
                    <span
                        :class="activeTab === 'given' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-100 text-gray-600 dark:bg-gray-800'"
                        class="rounded-full px-2 py-0.5 text-xs font-medium">
                        {{ $givenCompensations->count() }}
                    </span>
                </button>
            </nav>
        </div>

        {{-- Received Compensations Tab --}}
        <div x-show="activeTab === 'received'" x-transition>
            @if($receivedCompensations->isEmpty())
            <div
                class="rounded-lg border border-gray-200 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-900">
                <x-filament::icon icon="heroicon-o-inbox" class="mx-auto h-12 w-12 text-gray-400 mb-3" />
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Belum Ada Kompensasi Diterima</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Periode ini belum menerima kompensasi dari periode sebelumnya
                </p>
            </div>
            @else
            <div class="space-y-3">
                @foreach($receivedCompensations as $compensation)
                <div
                    class="rounded-lg border border-gray-200 bg-white p-5 hover:shadow-md transition-shadow dark:border-gray-700 dark:bg-gray-900">
                    <div class="mb-3 flex items-center gap-3">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Dari: <span class="font-medium text-gray-900 dark:text-white">{{
                                $compensation->sourceTaxReport->month }}</span>
                        </span>
                        <span class="text-xs text-gray-400">•</span>
                        <span class="flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                            <x-filament::icon icon="heroicon-m-check-circle" class="h-3.5 w-3.5" />
                            Disetujui
                        </span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        Rp {{ number_format($compensation->amount_compensated, 0, ',', '.') }}
                    </p>
                    @if($compensation->notes)
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ $compensation->notes }}
                    </p>
                    @endif
                    <div class="mt-3 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $compensation->created_at->format('d M Y H:i') }}</span>
                        @if($compensation->approvedBy)
                        <span>•</span>
                        <span>{{ $compensation->approvedBy->name }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Given Compensations Tab --}}
        <div x-show="activeTab === 'given'" x-transition>
            @if($givenCompensations->isEmpty())
            <div
                class="rounded-lg border border-gray-200 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-900">
                <x-filament::icon icon="heroicon-o-inbox" class="mx-auto h-12 w-12 text-gray-400 mb-3" />
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Belum Ada Kompensasi Diberikan</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Periode ini belum memberikan kompensasi ke periode berikutnya
                </p>
            </div>
            @else
            <div class="space-y-3">
                @foreach($givenCompensations as $compensation)
                <div
                    class="rounded-lg border border-gray-200 bg-white p-5 hover:shadow-md transition-shadow dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="mb-3 flex items-center gap-3">
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    Ke: <span class="font-medium text-gray-900 dark:text-white">{{
                                        $compensation->targetTaxReport->month }}</span>
                                </span>
                                <span class="text-xs text-gray-400">•</span>
                                @if($compensation->status === 'pending')
                                <span class="flex items-center gap-1 text-xs text-orange-600 dark:text-orange-400">
                                    <x-filament::icon icon="heroicon-m-clock" class="h-3.5 w-3.5" />
                                    Menunggu
                                </span>
                                @elseif($compensation->status === 'approved')
                                <span class="flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                                    <x-filament::icon icon="heroicon-m-check-circle" class="h-3.5 w-3.5" />
                                    Disetujui
                                </span>
                                @elseif($compensation->status === 'rejected')
                                <span class="flex items-center gap-1 text-xs text-red-600 dark:text-red-400">
                                    <x-filament::icon icon="heroicon-m-x-circle" class="h-3.5 w-3.5" />
                                    Ditolak
                                </span>
                                @endif
                            </div>

                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                Rp {{ number_format($compensation->amount_compensated, 0, ',', '.') }}
                            </p>

                            @if($compensation->notes)
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ $compensation->notes }}
                            </p>
                            @endif

                            @if($compensation->status === 'rejected' && $compensation->rejection_reason)
                            <div
                                class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-500/10">
                                <p class="text-xs font-medium text-red-900 dark:text-red-200">Alasan Penolakan:</p>
                                <p class="mt-1 text-sm text-red-700 dark:text-red-300">{{
                                    $compensation->rejection_reason }}</p>
                            </div>
                            @endif

                            @if($compensation->targetTaxReport->ppnSummary)
                            <div
                                class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800">
                                <p class="mb-2 text-xs font-medium text-gray-700 dark:text-gray-300">Info Periode
                                    Target:</p>
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
                                        <p class="font-semibold text-gray-900 dark:text-white">
                                            {{ $compensation->targetTaxReport->ppnSummary->status_final }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Saldo</p>
                                        <p class="font-semibold text-gray-900 dark:text-white">
                                            Rp {{
                                            number_format(abs($compensation->targetTaxReport->ppnSummary->saldo_final),
                                            0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="mt-3 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ $compensation->created_at->format('d M Y H:i') }}</span>
                                @if($compensation->approvedBy)
                                <span>•</span>
                                <span>{{ $compensation->approvedBy->name }}</span>
                                @endif
                            </div>
                        </div>

                        @if($compensation->status === 'pending')
                        <div class="flex flex-col gap-2">
                            <x-filament::button wire:click="openApproveModal({{ $compensation->id }})" color="success"
                                size="sm" icon="heroicon-m-check">
                                Setujui
                            </x-filament::button>
                            <x-filament::button wire:click="openRejectModal({{ $compensation->id }})" color="danger"
                                size="sm" icon="heroicon-m-x-mark">
                                Tolak
                            </x-filament::button>
                            <x-filament::button wire:click="cancelCompensation({{ $compensation->id }})"
                                wire:confirm="Yakin ingin membatalkan?" color="gray" size="sm" icon="heroicon-m-trash">
                                Batal
                            </x-filament::button>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- ============================================
    MODALS
    ============================================ --}}

    {{-- Manual Kompensasi Modal --}}
    <x-filament::modal id="manual-kompensasi-modal" width="2xl" :close-by-clicking-away="false">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-500/20">
                    <x-filament::icon icon="heroicon-o-pencil-square"
                        class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                </div>
                <span>Edit Kompensasi Manual</span>
            </div>
        </x-slot>

        <div class="space-y-5">
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-700 dark:bg-blue-500/10">
                <div class="flex gap-3">
                    <x-filament::icon icon="heroicon-o-information-circle"
                        class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        <p class="font-semibold mb-1">Tentang Kompensasi Manual:</p>
                        <ul class="space-y-1 list-disc list-inside text-xs">
                            <li>Digunakan untuk kompensasi yang tidak tercatat di sistem</li>
                            <li>Akan mengurangi saldo pajak terutang periode ini</li>
                            <li>Masukkan jumlah 0 untuk menghapus kompensasi manual</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Jumlah Kompensasi Manual <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">Rp</span>
                    <input type="number" wire:model.live="tempManualKompensasi"
                        class="block w-full rounded-lg border-gray-300 pl-10 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        placeholder="0" min="0" step="1000" x-data x-on:focus="$el.type = 'number'" x-on:blur="
                            if ($el.value) {
                                let val = parseInt($el.value);
                                $el.type = 'text';
                                $el.value = new Intl.NumberFormat('id-ID').format(val);
                                setTimeout(() => { $el.type = 'number'; $el.value = val; }, 100);
                            }
                        ">
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Nilai saat ini: Rp {{ number_format($manualKompensasi, 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                    @if($tempManualKompensasi > 0)
                    Preview: Rp {{ number_format($tempManualKompensasi, 0, ',', '.') }}
                    @endif
                </p>
                @error('tempManualKompensasi')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Catatan <span class="text-xs text-gray-500">(Opsional)</span>
                </label>
                <textarea wire:model="tempManualKompensasiNotes" rows="4"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    placeholder="Jelaskan alasan atau sumber kompensasi manual ini..."></textarea>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Catatan ini akan membantu untuk audit dan pelacakan
                </p>
                @error('tempManualKompensasiNotes')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            @if($tempManualKompensasi > 0)
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Preview Perhitungan:</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Saldo Awal:</span>
                        <span>Rp {{ number_format(abs($saldoSebelumKompensasi), 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Kompensasi Diterima:</span>
                        <span>- Rp {{ number_format($kompensasiDiterima, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-blue-600 dark:text-blue-400">
                        <span>Kompensasi Manual:</span>
                        <span>- Rp {{ number_format($tempManualKompensasi, 0, ',', '.') }}</span>
                    </div>
                    <div class="border-t border-gray-300 dark:border-gray-600 pt-2 mt-2"></div>

                    @php
                    $predictedSaldo = $saldoSebelumKompensasi - $kompensasiDiterima - $tempManualKompensasi;
                    $predictedStatus = $predictedSaldo > 0 ? 'Kurang Bayar' : ($predictedSaldo < 0 ? 'Lebih Bayar'
                        : 'Nihil' ); $statusColor=$predictedStatus==='Lebih Bayar'
                        ? 'text-green-600 dark:text-green-400' : ($predictedStatus==='Kurang Bayar'
                        ? 'text-orange-600 dark:text-orange-400' : 'text-gray-600 dark:text-gray-400' );
                        $bgColor=$predictedStatus==='Lebih Bayar' ? 'bg-green-50 dark:bg-green-500/10' :
                        ($predictedStatus==='Kurang Bayar' ? 'bg-orange-50 dark:bg-orange-500/10'
                        : 'bg-gray-50 dark:bg-gray-800' ); $borderColor=$predictedStatus==='Lebih Bayar'
                        ? 'border-green-200 dark:border-green-800' : ($predictedStatus==='Kurang Bayar'
                        ? 'border-orange-200 dark:border-orange-800' : 'border-gray-200 dark:border-gray-700' ); @endphp
                        <div class="rounded-lg border p-3 {{ $borderColor }} {{ $bgColor }}">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-gray-900 dark:text-white">Saldo Akhir:</span>
                                <div class="flex items-center gap-1.5">
                                    <div
                                        class="h-1.5 w-1.5 rounded-full {{ $predictedStatus === 'Lebih Bayar' ? 'bg-green-500' : ($predictedStatus === 'Kurang Bayar' ? 'bg-orange-500' : 'bg-gray-400') }}">
                                    </div>
                                    <span class="text-xs font-medium {{ $statusColor }}">{{ $predictedStatus }}</span>
                                </div>
                            </div>
                            <span class="font-bold {{ $statusColor }}">
                                Rp {{ number_format(abs($predictedSaldo), 0, ',', '.') }}
                            </span>
                        </div>
                </div>

                {{-- Status Change Indicator --}}
                @if($statusSetelah !== $predictedStatus)
                <div
                    class="mt-3 rounded-lg border border-blue-200 bg-blue-50 p-3 dark:border-blue-700 dark:bg-blue-500/10">
                    <div class="flex items-start gap-2">
                        <x-filament::icon icon="heroicon-o-arrow-path"
                            class="h-4 w-4 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                        <div class="text-xs text-blue-900 dark:text-blue-200">
                            <span class="font-semibold">Status akan berubah:</span>
                            <span class="text-blue-700 dark:text-blue-300">{{ $statusSetelah }}</span>
                            <x-filament::icon icon="heroicon-m-arrow-right" class="inline h-3 w-3 mx-1" />
                            <span class="font-semibold {{ $statusColor }}">{{ $predictedStatus }}</span>
                        </div>
                    </div>
                </div>
                @else
                <div class="mt-3 rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex items-start gap-2">
                        <x-filament::icon icon="heroicon-o-check-circle"
                            class="h-4 w-4 text-gray-600 dark:text-gray-400 flex-shrink-0 mt-0.5" />
                        <p class="text-xs text-gray-700 dark:text-gray-300">
                            Status tetap: <span class="font-semibold">{{ $statusSetelah }}</span>
                        </p>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif
</div>

<x-slot name="footerActions">
    <x-filament::button color="gray" wire:click="cancelManualKompensasiEdit">
        Batal
    </x-filament::button>
    <x-filament::button color="primary" wire:click="saveManualKompensasi" icon="heroicon-m-check">
        Simpan
    </x-filament::button>
</x-slot>
</x-filament::modal>

{{-- Create Compensation Modal --}}
<x-filament::modal id="compensation-modal" width="2xl" :close-by-clicking-away="false">
    <x-slot name="heading">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-gray-100 p-2 dark:bg-gray-800">
                <x-filament::icon icon="heroicon-o-arrow-right-circle"
                    class="h-6 w-6 text-gray-600 dark:text-gray-400" />
            </div>
            <span>{{ $this->compensationTypeLabel }}</span>
        </div>
    </x-slot>

    <div class="space-y-5">
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Periode Saat Ini</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $taxReport->month }}</p>
                    <span class="mt-1 text-xs text-green-600 dark:text-green-400">{{ $statusSetelah }}</span>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Periode Target</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $this->nextMonthInfo['month'] ?? '-' }}
                    </p>
                    <span class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $this->nextMonthInfo['status'] ?? '-' }}
                    </span>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Jumlah Kompensasi <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">Rp</span>
                <input type="number" wire:model.live="compensationAmount"
                    class="block w-full rounded-lg border-gray-300 pl-10 shadow-sm focus:border-gray-900 focus:ring-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    placeholder="0" min="0" max="{{ $maxCompensationAmount }}">
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Maksimal: Rp {{ number_format($maxCompensationAmount, 0, ',', '.') }}
            </p>
            @error('compensationAmount')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Catatan <span class="text-xs text-gray-500">(Opsional)</span>
            </label>
            <textarea wire:model="compensationNotes" rows="3"
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                placeholder="Tambahkan catatan jika diperlukan..."></textarea>
        </div>

        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex gap-3">
                <x-filament::icon icon="heroicon-o-information-circle"
                    class="h-5 w-5 text-gray-600 dark:text-gray-400 flex-shrink-0 mt-0.5" />
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    <p class="font-semibold mb-1">Ringkasan:</p>
                    <ul class="space-y-1 text-xs">
                        <li>{{ $this->compensationTypeDescription }}</li>
                        <li>Jumlah: <strong>Rp {{ number_format($compensationAmount, 0, ',', '.') }}</strong></li>
                        <li>Target: <strong>{{ $this->nextMonthInfo['month'] ?? '-' }}</strong></li>
                        <li>Memerlukan approval sebelum berlaku</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="footerActions">
        <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'compensation-modal' })">
            Batal
        </x-filament::button>
        <x-filament::button wire:click="createCompensation" :disabled="$compensationAmount <= 0"
            icon="heroicon-m-arrow-right-circle">
            Buat Kompensasi
        </x-filament::button>
    </x-slot>
</x-filament::modal>

{{-- Approve Modal --}}
<x-filament::modal id="approve-compensation-modal" width="md">
    <x-slot name="heading">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-green-100 p-2 dark:bg-green-500/20">
                <x-filament::icon icon="heroicon-o-check-circle" class="h-6 w-6 text-green-600 dark:text-green-400" />
            </div>
            <span>Setujui Kompensasi</span>
        </div>
    </x-slot>

    <div class="py-4">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Apakah Anda yakin ingin menyetujui kompensasi ini? Saldo pajak akan diperbarui secara otomatis.
        </p>
    </div>

    <x-slot name="footerActions">
        <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'approve-compensation-modal' })">
            Batal
        </x-filament::button>
        <x-filament::button color="success" wire:click="approveCompensation">
            Ya, Setujui
        </x-filament::button>
    </x-slot>
</x-filament::modal>

{{-- Reject Modal --}}
<x-filament::modal id="reject-compensation-modal" width="md">
    <x-slot name="heading">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-red-100 p-2 dark:bg-red-500/20">
                <x-filament::icon icon="heroicon-o-x-circle" class="h-6 w-6 text-red-600 dark:text-red-400" />
            </div>
            <span>Tolak Kompensasi</span>
        </div>
    </x-slot>

    <div class="space-y-4">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Berikan alasan penolakan untuk kompensasi ini:
        </p>
        <div>
            <textarea wire:model="rejectionReason" rows="4"
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                placeholder="Masukkan alasan penolakan (minimal 10 karakter)..."></textarea>
            @error('rejectionReason')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <x-slot name="footerActions">
        <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'reject-compensation-modal' })">
            Batal
        </x-filament::button>
        <x-filament::button color="danger" wire:click="rejectCompensation">
            Tolak Kompensasi
        </x-filament::button>
    </x-slot>
</x-filament::modal>

</div>