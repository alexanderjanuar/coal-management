<div>
    {{-- Summary Cards with Flow --}}
    <div class="mb-8">
        <div class="relative">
            <div class="grid gap-6 lg:grid-cols-3">

                {{-- Card 1: Sebelum Kompensasi --}}
                <div
                    class="relative rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
                    <div class="mb-6">
                        <div class="flex items-center gap-3 mb-2">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                                <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">1</span>
                            </div>
                            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                Sebelum Kompensasi
                            </h3>
                        </div>
                    </div>

                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format(abs($saldoSebelumKompensasi), 0, ',', '.') }}
                        </p>
                        <div class="mt-3 flex items-center gap-2">
                            @if($statusSebelum === 'Lebih Bayar')
                            <div class="h-2 w-2 rounded-full bg-green-500"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $statusSebelum }}</span>
                            @elseif($statusSebelum === 'Kurang Bayar')
                            <div class="h-2 w-2 rounded-full bg-orange-500"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $statusSebelum }}</span>
                            @else
                            <div class="h-2 w-2 rounded-full bg-gray-400"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $statusSebelum }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Arrow --}}
                    <div class="absolute -right-3 top-1/2 z-10 hidden -translate-y-1/2 lg:block">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-900 dark:bg-white">
                            <x-filament::icon icon="heroicon-m-plus"
                                class="h-3.5 w-3.5 text-white dark:text-gray-900" />
                        </div>
                    </div>
                </div>

                {{-- Card 2: Kompensasi Diterima --}}
                <div
                    class="relative rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
                    <div class="mb-6">
                        <div class="flex items-center gap-3 mb-2">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                                <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">2</span>
                            </div>
                            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                Kompensasi Diterima
                            </h3>
                        </div>
                    </div>

                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($kompensasiDiterima, 0, ',', '.') }}
                        </p>
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                            {{ $receivedCompensations->count() }} transaksi diterima
                        </p>
                    </div>

                    {{-- Arrow --}}
                    <div class="absolute -right-3 top-1/2 z-10 hidden -translate-y-1/2 lg:block">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-900 dark:bg-white">
                            <x-filament::icon icon="heroicon-m-equals"
                                class="h-3.5 w-3.5 text-white dark:text-gray-900" />
                        </div>
                    </div>
                </div>

                {{-- Card 3: Setelah Kompensasi --}}
                <div class="relative rounded-xl border-2 
                    {{ $statusSetelah === 'Lebih Bayar' ? 'border-green-500 bg-green-50 dark:border-green-600 dark:bg-green-500/5' : 
                       ($statusSetelah === 'Kurang Bayar' ? 'border-orange-500 bg-orange-50 dark:border-orange-600 dark:bg-orange-500/5' : 
                       'border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-900') }} 
                    p-6">
                    <div class="mb-6">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg 
                                {{ $statusSetelah === 'Lebih Bayar' ? 'bg-green-100 dark:bg-green-500/20' : 
                                   ($statusSetelah === 'Kurang Bayar' ? 'bg-orange-100 dark:bg-orange-500/20' : 
                                   'bg-gray-100 dark:bg-gray-800') }}">
                                <x-filament::icon icon="heroicon-m-check" class="h-4 w-4 
                                    {{ $statusSetelah === 'Lebih Bayar' ? 'text-green-700 dark:text-green-400' : 
                                       ($statusSetelah === 'Kurang Bayar' ? 'text-orange-700 dark:text-orange-400' : 
                                       'text-gray-600 dark:text-gray-400') }}" />
                            </div>
                            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                Hasil Akhir
                            </h3>
                        </div>
                    </div>

                    <div>
                        <p class="text-2xl font-bold 
                            {{ $statusSetelah === 'Lebih Bayar' ? 'text-green-700 dark:text-green-400' : 
                               ($statusSetelah === 'Kurang Bayar' ? 'text-orange-700 dark:text-orange-400' : 
                               'text-gray-900 dark:text-white') }}">
                            Rp {{ number_format(abs($saldoSetelahKompensasi), 0, ',', '.') }}
                        </p>
                        <div class="mt-3 flex items-center gap-2">
                            @if($statusSetelah === 'Lebih Bayar')
                            <div class="h-2 w-2 rounded-full bg-green-500"></div>
                            <span class="text-sm font-medium text-green-700 dark:text-green-400">{{ $statusSetelah
                                }}</span>
                            @elseif($statusSetelah === 'Kurang Bayar')
                            <div class="h-2 w-2 rounded-full bg-orange-500"></div>
                            <span class="text-sm font-medium text-orange-700 dark:text-orange-400">{{ $statusSetelah
                                }}</span>
                            @else
                            <div class="h-2 w-2 rounded-full bg-gray-400"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $statusSetelah }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Compensation Available - Only if Lebih Bayar --}}
            @if($statusSetelah === 'Lebih Bayar' && $kompensasiTersedia > 0)
            <div class="mt-6 rounded-xl border border-green-200 bg-white p-6 dark:border-green-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            Saldo Tersedia untuk Dikompensasi
                        </p>
                        <p class="mt-2 text-2xl font-bold text-green-700 dark:text-green-400">
                            Rp {{ number_format($kompensasiTersedia, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <x-filament::icon icon="heroicon-m-arrow-right" class="inline h-4 w-4" />
                            Dapat dikompensasi
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Next Month Section --}}
    @if($statusSetelah === 'Lebih Bayar' && $kompensasiTersedia > 0)
    @if($nextMonthExists)
    <div class="mb-8 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
        <div class="flex items-start gap-6">
            <div class="flex-shrink-0">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
                    <x-filament::icon icon="heroicon-o-calendar-days"
                        class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                </div>
            </div>

            <div class="flex-1">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Periode Berikutnya
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $this->nextMonthInfo['month'] }} {{ $this->nextMonthInfo['year'] }} • {{
                        $this->compensationTypeDescription }}
                    </p>
                </div>

                <div class="mb-6 grid grid-cols-3 gap-4">
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $this->nextMonthInfo['status'] }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Saldo Target</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $this->nextMonthInfo['formatted_saldo'] }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-green-50 p-4 dark:bg-green-500/10">
                        <p class="text-xs text-green-600 dark:text-green-400">Dapat Dikompensasi</p>
                        <p class="mt-1 text-sm font-semibold text-green-700 dark:text-green-300">
                            Rp {{ number_format($maxCompensationAmount, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                @if($this->canCreateCompensation)
                <x-filament::button wire:click="openCompensationModal" icon="heroicon-m-arrow-right-circle" size="md">
                    Kompensasi ke {{ $this->nextMonthInfo['month'] }}
                </x-filament::button>
                @endif
            </div>
        </div>
    </div>
    @else
    <div
        class="mb-8 rounded-xl border border-orange-200 bg-orange-50 p-6 text-center dark:border-orange-800 dark:bg-orange-500/5">
        <x-filament::icon icon="heroicon-o-exclamation-triangle"
            class="mx-auto h-12 w-12 text-orange-600 dark:text-orange-400 mb-3" />
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Periode Berikutnya Belum Ada</h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Buat laporan pajak untuk bulan berikutnya untuk menggunakan kompensasi
        </p>
    </div>
    @endif
    @endif

    {{-- Tabs Section --}}
    <div x-data="{ activeTab: 'received' }" class="space-y-6">

        {{-- Tab Navigation --}}
        <div class="border-b border-gray-200 dark:border-gray-800">
            <nav class="-mb-px flex space-x-8">
                <button @click="activeTab = 'received'"
                    :class="activeTab === 'received' ? 'border-gray-900 text-gray-900 dark:border-white dark:text-white' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400'"
                    class="flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors">
                    <x-filament::icon icon="heroicon-o-arrow-down-circle" class="h-5 w-5" />
                    <span>Diterima</span>
                    <span
                        :class="activeTab === 'received' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-100 text-gray-600 dark:bg-gray-800'"
                        class="ml-2 rounded-full px-2.5 py-0.5 text-xs font-medium">
                        {{ $receivedCompensations->count() }}
                    </span>
                </button>

                <button @click="activeTab = 'given'"
                    :class="activeTab === 'given' ? 'border-gray-900 text-gray-900 dark:border-white dark:text-white' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400'"
                    class="flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors">
                    <x-filament::icon icon="heroicon-o-arrow-right-circle" class="h-5 w-5" />
                    <span>Diberikan</span>
                    <span
                        :class="activeTab === 'given' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-100 text-gray-600 dark:bg-gray-800'"
                        class="ml-2 rounded-full px-2.5 py-0.5 text-xs font-medium">
                        {{ $givenCompensations->count() }}
                    </span>
                </button>
            </nav>
        </div>

        {{-- Received Compensations Tab --}}
        <div x-show="activeTab === 'received'" x-transition>
            @if($receivedCompensations->isEmpty())
            <div
                class="rounded-xl border border-gray-200 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-900">
                <x-filament::icon icon="heroicon-o-inbox" class="mx-auto h-12 w-12 text-gray-400 mb-3" />
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Belum Ada Kompensasi Diterima</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Periode ini belum menerima kompensasi dari periode sebelumnya
                </p>
            </div>
            @else
            <div class="space-y-4">
                @foreach($receivedCompensations as $compensation)
                <div
                    class="rounded-xl border border-gray-200 bg-white p-6 transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
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
                class="rounded-xl border border-gray-200 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-900">
                <x-filament::icon icon="heroicon-o-inbox" class="mx-auto h-12 w-12 text-gray-400 mb-3" />
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Belum Ada Kompensasi Diberikan</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Periode ini belum memberikan kompensasi ke periode berikutnya
                </p>
            </div>
            @else
            <div class="space-y-4">
                @foreach($givenCompensations as $compensation)
                <div
                    class="rounded-xl border border-gray-200 bg-white p-6 transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex items-start justify-between">
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
                        <div class="ml-4 flex flex-col gap-2">
                            <x-filament::button wire:click="openApproveModal({{ $compensation->id }})" color="success"
                                size="sm" icon="heroicon-m-check">
                                Setujui
                            </x-filament::button>
                            <x-filament::button wire:click="openRejectModal({{ $compensation->id }})" color="danger"
                                size="sm" icon="heroicon-m-x-mark">
                                Tolak
                            </x-filament::button>
                            <x-filament::button wire:click="cancelCompensation({{ $compensation->id }})"
                                wire:confirm="Yakin ingin membatalkan kompensasi ini?" color="gray" size="sm"
                                icon="heroicon-m-trash">
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

    {{-- Modals remain the same - they are already clean --}}
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

        <div class="space-y-6">
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
                    Catatan (Opsional)
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
                        <ul class="space-y-1">
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

    <x-filament::modal id="approve-compensation-modal" width="md">
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-green-100 p-2 dark:bg-green-500/20">
                    <x-filament::icon icon="heroicon-o-check-circle"
                        class="h-6 w-6 text-green-600 dark:text-green-400" />
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
            <x-filament::button color="gray"
                x-on:click="$dispatch('close-modal', { id: 'approve-compensation-modal' })">
                Batal
            </x-filament::button>

            <x-filament::button color="success" wire:click="approveCompensation">
                Ya, Setujui
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

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