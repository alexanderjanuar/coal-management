<div
    class="h-full flex flex-col bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <!-- Header -->
    <div class="flex-shrink-0 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-red-50 dark:bg-red-900/20">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Top Klien Belum Lapor
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $this->getFilterSummary() }}
                    </p>
                </div>
            </div>

            <button wire:click="loadTopUnreportedClients"
                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                wire:loading.attr="disabled" title="Refresh data">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    wire:loading.class="animate-spin">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>
    </div>

    @if($loading)
    <!-- Loading State -->
    <div class="flex-1 overflow-y-auto px-6 py-4">
        <div class="space-y-6">
            @for($i = 0; $i < 5; $i++) <div class="animate-pulse">
                <div class="flex items-center gap-4 mb-3">
                    <div class="w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded-lg flex-shrink-0"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
                    </div>
                </div>
                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
                <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded"></div>
        </div>
        @endfor
    </div>
</div>

@elseif(count($topClients) > 0)
<!-- Client List -->
<div class="flex-1 overflow-y-auto px-6 py-4">
    <div class="space-y-6">
        @foreach($topClients as $index => $client)
        <div class="group">
            <!-- Client Header -->
            <div class="flex items-center gap-3 mb-3">
                <!-- Rank -->
                <div
                    class="flex-shrink-0 w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                    <span class="text-sm font-bold text-gray-600 dark:text-gray-400">
                        {{ $index + 1 }}
                    </span>
                </div>

                <!-- Client Info -->
                <div class="flex-1 min-w-0">
                    <a href="{{ $this->getClientUrl($client->client_id) }}" class="group/link block">
                        <h4
                            class="text-sm font-semibold text-gray-900 dark:text-white group-hover/link:text-blue-600 dark:group-hover/link:text-blue-400 transition-colors truncate">
                            {{ $client->client_name }}
                        </h4>
                        <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            <span>{{ $client->month }}</span>
                            <span>•</span>
                            <span>{{ $client->total_invoices }} Faktur</span>
                        </div>
                    </a>
                </div>

                <!-- Action Button -->
                <a href="{{ $this->getTaxReportUrl($client->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors">
                    Lihat
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Amount -->
            <div class="mb-3">
                <div class="flex items-baseline justify-between mb-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Peredaran Bruto
                    </span>
                    <div class="text-right">
                        <div class="text-base font-bold text-gray-900 dark:text-white">
                            {{ $this->formatCurrency($client->total_peredaran_bruto) }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $this->formatCurrencyShort($client->total_peredaran_bruto) }}
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                @php
                $maxAmount = $topClients->first()->total_peredaran_bruto;
                $percentage = $maxAmount > 0 ? ($client->total_peredaran_bruto / $maxAmount) * 100 : 0;
                @endphp
                <div class="h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-gray-600 dark:bg-gray-500 rounded-full transition-all duration-500"
                        style="width: {{ $percentage }}%"></div>
                </div>
            </div>

            <!-- Status Badges -->
            <div class="flex flex-wrap gap-2">
                @if($client->ppn_report_status === 'Belum Lapor')
                <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    PPN
                </span>
                @endif

                @if($client->pph_report_status === 'Belum Lapor')
                <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    PPh
                </span>
                @endif

                @if($client->bupot_report_status === 'Belum Lapor')
                <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    PPh Unifikasi
                </span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Footer Summary -->
<div class="flex-shrink-0 px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between">
        <span class="text-sm text-gray-600 dark:text-gray-400">
            Total {{ count($topClients) }} klien
        </span>
        <span class="text-sm font-semibold text-gray-900 dark:text-white">
            {{ $this->formatCurrency($topClients->sum('total_peredaran_bruto')) }}
        </span>
    </div>
</div>

@else
<!-- Empty State -->
<div class="flex-1 flex items-center justify-center p-12">
    <div class="text-center max-w-sm">
        <div
            class="mx-auto w-16 h-16 bg-green-50 dark:bg-green-900/20 rounded-full flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
            Semua Sudah Lapor!
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Tidak ada klien dengan laporan pajak yang belum dilaporkan
        </p>
    </div>
</div>
@endif
</div>