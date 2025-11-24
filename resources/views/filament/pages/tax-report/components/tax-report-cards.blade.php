<div class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden dark:divide-white/10 dark:bg-gray-900">
    <!-- Cards Grid -->
    <div class="p-6">
        @if ($records->isEmpty())
        <!-- Empty State -->
        <div class="flex flex-col items-center justify-center py-16">
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                <x-heroicon-o-document-text class="h-10 w-10 text-gray-400" />
            </div>
            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
                Belum Ada Laporan Pajak
            </h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Laporan pajak akan muncul di sini setelah Anda membuatnya.
            </p>
            <div class="mt-6">
                {{ $this->getTable()->getEmptyStateActions() }}
            </div>
        </div>
        @else
        <!-- Cards Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($records as $record)
            @php
            $ppnSummary = $record->taxCalculationSummaries->firstWhere('tax_type', 'ppn');
            $pphSummary = $record->taxCalculationSummaries->firstWhere('tax_type', 'pph');
            $bupotSummary = $record->taxCalculationSummaries->firstWhere('tax_type', 'bupot');

            $paymentStatus = $ppnSummary?->status_final ?? 'N/A';

            // Payment status variables
            $ppnBayarStatus = $ppnSummary?->bayar_status ?? 'Belum Bayar';
            $pphBayarStatus = $pphSummary?->bayar_status ?? 'Belum Bayar';
            $bupotBayarStatus = $bupotSummary?->bayar_status ?? 'Belum Bayar';

            $allPaid = ($ppnBayarStatus === 'Sudah Bayar') &&
            ($pphBayarStatus === 'Sudah Bayar') &&
            ($bupotBayarStatus === 'Sudah Bayar');

            $statusConfig = match($paymentStatus) {
            'Lebih Bayar' => [
            'gradient' => 'from-emerald-500/90 to-emerald-600/90',
            'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300'
            ],
            'Kurang Bayar' => [
            'gradient' => 'from-amber-500/90 to-amber-600/90',
            'badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300'
            ],
            'Nihil' => [
            'gradient' => 'from-slate-500/90 to-slate-600/90',
            'badge' => 'bg-slate-100 text-slate-700 dark:bg-slate-800/50 dark:text-slate-300'
            ],
            default => [
            'gradient' => 'from-gray-500/90 to-gray-600/90',
            'badge' => 'bg-gray-100 text-gray-700 dark:bg-gray-800/50 dark:text-gray-300'
            ],
            };

            $allReported = ($ppnSummary?->report_status === 'Sudah Lapor') &&
            ($pphSummary?->report_status === 'Sudah Lapor') &&
            ($bupotSummary?->report_status === 'Sudah Lapor');

            $ppnReported = $ppnSummary?->report_status === 'Sudah Lapor';
            $pphReported = $pphSummary?->report_status === 'Sudah Lapor';
            $bupotReported = $bupotSummary?->report_status === 'Sudah Lapor';
            @endphp

            <div wire:key="tax-report-{{ $record->id }}"
                class="group relative flex flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200/60 transition-all duration-300 hover:shadow-md hover:ring-gray-300/60 dark:bg-gray-900 dark:ring-white/5 dark:hover:ring-white/10">

                <!-- Header -->
                <div class="relative bg-gradient-to-br {{ $statusConfig['gradient'] }} p-5 text-white">
                    <!-- Payment Status Badge (Top Left) -->
                    @if($allPaid)
                    <div class="absolute left-4 top-4">
                        <div
                            class="flex items-center gap-1.5 rounded-full bg-emerald-500/90 px-2.5 py-1 backdrop-blur-sm">
                            <x-heroicon-s-banknotes class="h-3.5 w-3.5" />
                            <span class="text-xs font-semibold">Sudah Bayar</span>
                        </div>
                    </div>
                    @endif

                    <!-- Completion Badge (Top Right) -->
                    @if($allReported)
                    <div class="absolute right-4 top-4">
                        <div class="flex items-center gap-1.5 rounded-full bg-white/20 px-2.5 py-1 backdrop-blur-sm">
                            <x-heroicon-s-check-badge class="h-3.5 w-3.5" />
                            <span class="text-xs font-semibold">Lengkap</span>
                        </div>
                    </div>
                    @endif

                    <!-- Client Info -->
                    <div class="mb-3">
                        <a href="{{ \App\Filament\Resources\TaxReportResource::getUrl('view', ['record' => $record]) }}"
                            class="group/link block">
                            <h3
                                class="text-base font-semibold leading-tight transition-transform group-hover/link:translate-x-1">
                                {{ Str::limit($record->client->name, 30) }}
                            </h3>
                        </a>
                    </div>

                    <!-- Period & Status Badge with Indicators -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2 text-white/90">
                                <x-heroicon-m-calendar-days class="h-4 w-4" />
                                <span class="text-sm font-medium">{{ $record->month }}</span>
                            </div>
                            <!-- Mini Indicators with Payment Status -->
                            <div class="flex items-center gap-1">
                                <!-- PPN Indicator -->
                                <div class="relative group/indicator">
                                    <div class="h-1.5 w-1.5 rounded-full {{ $ppnReported ? 'bg-emerald-300' : 'bg-white/30' }}"
                                        title="PPN {{ $ppnReported ? 'Sudah Lapor' : 'Belum Lapor' }}"></div>
                                    @if($ppnReported && $ppnBayarStatus === 'Sudah Bayar')
                                    <div class="absolute -top-0.5 -right-0.5 h-1 w-1 rounded-full bg-amber-300 ring-1 ring-white/20"
                                        title="Sudah Bayar"></div>
                                    @endif
                                </div>
                                <!-- PPh Indicator -->
                                <div class="relative group/indicator">
                                    <div class="h-1.5 w-1.5 rounded-full {{ $pphReported ? 'bg-emerald-300' : 'bg-white/30' }}"
                                        title="PPh {{ $pphReported ? 'Sudah Lapor' : 'Belum Lapor' }}"></div>
                                    @if($pphReported && $pphBayarStatus === 'Sudah Bayar')
                                    <div class="absolute -top-0.5 -right-0.5 h-1 w-1 rounded-full bg-amber-300 ring-1 ring-white/20"
                                        title="Sudah Bayar"></div>
                                    @endif
                                </div>
                                <!-- Bupot Indicator -->
                                <div class="relative group/indicator">
                                    <div class="h-1.5 w-1.5 rounded-full {{ $bupotReported ? 'bg-emerald-300' : 'bg-white/30' }}"
                                        title="PPh Unifikasi {{ $bupotReported ? 'Sudah Lapor' : 'Belum Lapor' }}">
                                    </div>
                                    @if($bupotReported && $bupotBayarStatus === 'Sudah Bayar')
                                    <div class="absolute -top-0.5 -right-0.5 h-1 w-1 rounded-full bg-amber-300 ring-1 ring-white/20"
                                        title="Sudah Bayar"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="rounded-lg px-2.5 py-1 text-xs font-semibold {{ $statusConfig['badge'] }}">
                            {{ $paymentStatus }}
                        </div>
                    </div>
                </div>

                <!-- Report Status Grid with Payment Status -->
                <div
                    class="grid grid-cols-3 divide-x divide-gray-100 border-b border-gray-100 dark:divide-white/5 dark:border-white/5">
                    <!-- PPN with Payment Status -->
                    <div
                        class="group/status flex flex-col items-center gap-2 py-3.5 transition-colors hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                        <div class="relative">
                            @if($ppnReported)
                            <x-heroicon-s-check-circle class="h-5 w-5 text-emerald-500/80" />
                            @else
                            <x-heroicon-o-clock
                                class="h-5 w-5 text-gray-400 transition-transform group-hover/status:scale-110" />
                            @endif

                            @if($ppnReported && $ppnBayarStatus === 'Sudah Bayar')
                            <div class="absolute -bottom-0.5 -right-0.5">
                                <x-heroicon-s-banknotes class="h-3 w-3 text-amber-500" />
                            </div>
                            @endif
                        </div>

                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">PPN</span>

                        @if($ppnSummary?->reported_at)
                        <span class="text-[10px] text-gray-500 dark:text-gray-400">
                            {{ \Carbon\Carbon::parse($ppnSummary->reported_at)->format('d M') }}
                        </span>
                        @else
                        <span class="text-[10px] text-gray-400 dark:text-gray-500">
                            Belum Lapor
                        </span>
                        @endif

                        @if($ppnReported)
                        <div class="flex items-center gap-1">
                            <div
                                class="h-1 w-1 rounded-full {{ $ppnBayarStatus === 'Sudah Bayar' ? 'bg-emerald-500' : 'bg-amber-500' }}">
                            </div>
                            <span
                                class="text-[9px] {{ $ppnBayarStatus === 'Sudah Bayar' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                {{ $ppnBayarStatus === 'Sudah Bayar' ? 'Paid' : 'Unpaid' }}
                            </span>
                        </div>
                        @endif
                    </div>

                    <!-- PPh with Payment Status -->
                    <div
                        class="group/status flex flex-col items-center gap-2 py-3.5 transition-colors hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                        <div class="relative">
                            @if($pphReported)
                            <x-heroicon-s-check-circle class="h-5 w-5 text-emerald-500/80" />
                            @else
                            <x-heroicon-o-clock
                                class="h-5 w-5 text-gray-400 transition-transform group-hover/status:scale-110" />
                            @endif

                            @if($pphReported && $pphBayarStatus === 'Sudah Bayar')
                            <div class="absolute -bottom-0.5 -right-0.5">
                                <x-heroicon-s-banknotes class="h-3 w-3 text-amber-500" />
                            </div>
                            @endif
                        </div>

                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">PPh</span>

                        @if($pphSummary?->reported_at)
                        <span class="text-[10px] text-gray-500 dark:text-gray-400">
                            {{ \Carbon\Carbon::parse($pphSummary->reported_at)->format('d M') }}
                        </span>
                        @else
                        <span class="text-[10px] text-gray-400 dark:text-gray-500">
                            Belum Lapor
                        </span>
                        @endif

                        @if($pphReported)
                        <div class="flex items-center gap-1">
                            <div
                                class="h-1 w-1 rounded-full {{ $pphBayarStatus === 'Sudah Bayar' ? 'bg-emerald-500' : 'bg-amber-500' }}">
                            </div>
                            <span
                                class="text-[9px] {{ $pphBayarStatus === 'Sudah Bayar' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                {{ $pphBayarStatus === 'Sudah Bayar' ? 'Paid' : 'Unpaid' }}
                            </span>
                        </div>
                        @endif
                    </div>

                    <!-- PPh Unifikasi (Bupot) with Payment Status -->
                    <div
                        class="group/status flex flex-col items-center gap-2 py-3.5 transition-colors hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                        <div class="relative">
                            @if($bupotReported)
                            <x-heroicon-s-check-circle class="h-5 w-5 text-emerald-500/80" />
                            @else
                            <x-heroicon-o-clock
                                class="h-5 w-5 text-gray-400 transition-transform group-hover/status:scale-110" />
                            @endif

                            @if($bupotReported && $bupotBayarStatus === 'Sudah Bayar')
                            <div class="absolute -bottom-0.5 -right-0.5">
                                <x-heroicon-s-banknotes class="h-3 w-3 text-amber-500" />
                            </div>
                            @endif
                        </div>

                        <span
                            class="text-[10px] font-semibold text-center leading-tight text-gray-700 dark:text-gray-300">PPh<br>Unifikasi</span>

                        @if($bupotSummary?->reported_at)
                        <span class="text-[10px] text-gray-500 dark:text-gray-400">
                            {{ \Carbon\Carbon::parse($bupotSummary->reported_at)->format('d M') }}
                        </span>
                        @else
                        <span class="text-[10px] text-gray-400 dark:text-gray-500">
                            Belum Lapor
                        </span>
                        @endif

                        @if($bupotReported)
                        <div class="flex items-center gap-1">
                            <div
                                class="h-1 w-1 rounded-full {{ $bupotBayarStatus === 'Sudah Bayar' ? 'bg-emerald-500' : 'bg-amber-500' }}">
                            </div>
                            <span
                                class="text-[9px] {{ $bupotBayarStatus === 'Sudah Bayar' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                {{ $bupotBayarStatus === 'Sudah Bayar' ? 'Paid' : 'Unpaid' }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="flex-1 space-y-3 p-4">
                    <!-- Main Amount -->
                    @if($ppnSummary && abs($ppnSummary->saldo_final) > 0)
                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Saldo PPN</span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">
                                Rp {{ number_format(abs($ppnSummary->saldo_final), 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    @endif

                </div>

                <!-- Footer -->
                <div
                    class="flex items-center justify-between border-t border-gray-100 bg-gray-50/50 px-4 py-3 dark:border-white/5 dark:bg-gray-800/30">
                    <!-- Creator Info -->
                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br {{ $statusConfig['gradient'] }} text-xs font-semibold text-white shadow-sm">
                            {{ substr($record->createdBy?->name ?? 'S', 0, 1) }}
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                {{ Str::limit($record->createdBy?->name ?? 'System', 15) }}
                            </span>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400">
                                {{ $record->created_at->format('d M Y') }}
                            </span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-1">
                        <!-- View Button -->
                        <a href="{{ \App\Filament\Resources\TaxReportResource::getUrl('view', ['record' => $record]) }}"
                            class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-500 transition-all hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                            title="Lihat Detail">
                            <x-heroicon-o-eye class="h-4 w-4" />
                        </a>

                        <!-- Edit Button -->
                        <button wire:click="mountTableAction('edit', '{{ $record->id }}')"
                            class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-500 transition-all hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                            title="Edit">
                            <x-heroicon-o-pencil class="h-4 w-4" />
                        </button>

                        <!-- Actions Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false"
                                class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-500 transition-all hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                title="More Actions">
                                <x-heroicon-o-ellipsis-vertical class="h-4 w-4" />
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute bottom-full right-0 z-[100] mb-2 w-56 origin-bottom-right rounded-lg bg-white shadow-xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10"
                                @click.away="open = false" style="display: none;" x-cloak>
                                <div class="py-1">
                                    <!-- Update PPN Status -->
                                    <button wire:click="mountTableAction('update_ppn_status', '{{ $record->id }}')"
                                        @click="open = false"
                                        class="flex w-full items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 transition-colors hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/50">
                                        <x-heroicon-o-document-check class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                        <span>Update Status PPN</span>
                                    </button>

                                    <!-- Update PPh Status -->
                                    <button wire:click="mountTableAction('update_pph_status', '{{ $record->id }}')"
                                        @click="open = false"
                                        class="flex w-full items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 transition-colors hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/50">
                                        <x-heroicon-o-receipt-percent
                                            class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                        <span>Update Status PPh</span>
                                    </button>

                                    <!-- Update Bupot Status -->
                                    <button wire:click="mountTableAction('update_bupot_status', '{{ $record->id }}')"
                                        @click="open = false"
                                        class="flex w-full items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 transition-colors hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/50">
                                        <x-heroicon-o-document-text class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                        <span>Update Status PPh Unifikasi</span>
                                    </button>

                                    <div class="my-1 h-px bg-gray-100 dark:bg-gray-700"></div>

                                    <!-- Update Bayar Status -->
                                    <button wire:click="mountTableAction('update_bayar_status', '{{ $record->id }}')"
                                        @click="open = false"
                                        class="flex w-full items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 transition-colors hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/50">
                                        <x-heroicon-o-banknotes class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                        <span>Update Status Bayar</span>
                                    </button>

                                    <div class="my-1 h-px bg-gray-100 dark:bg-gray-700"></div>

                                    <!-- Delete -->
                                    <button wire:click="mountTableAction('delete', '{{ $record->id }}')"
                                        @click="open = false"
                                        class="flex w-full items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/50">
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                        <span>Delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Payment Status Legend -->
        <div class="mt-6 flex flex-wrap items-center justify-center gap-4 text-xs text-gray-600 dark:text-gray-400">
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1">
                    <div class="h-1.5 w-1.5 rounded-full bg-emerald-300"></div>
                    <span>Sudah Lapor</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1">
                    <x-heroicon-s-banknotes class="h-3.5 w-3.5 text-amber-500" />
                    <span>Sudah Bayar</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1">
                    <div class="h-1.5 w-1.5 rounded-full bg-white/30 ring-1 ring-gray-300 dark:ring-gray-600"></div>
                    <span>Belum Lapor</span>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>