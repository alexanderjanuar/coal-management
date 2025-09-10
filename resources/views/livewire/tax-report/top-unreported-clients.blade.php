<div class="bg-white dark:bg-[#181717] rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 h-full max-h-[800px] flex flex-col">
    <!-- Header - Fixed -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Klien Peredaran PPN Tertinggi</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Belum melaporkan pajak</p>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                <span class="text-xs font-medium text-red-600 dark:text-red-400">Perlu Perhatian</span>
            </div>
        </div>
    </div>

    @if($loading)
        <!-- Loading State -->
        <div class="flex-1 overflow-y-auto px-6 py-4">
            <div class="space-y-3">
                @for($i = 0; $i < 5; $i++)
                <div class="animate-pulse">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                        <div class="flex-1 space-y-2">
                            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
        </div>
    @elseif(count($topClients) > 0)
        <!-- Scrollable Client List -->
        <div class="flex-1 overflow-y-auto px-6 py-2">
            <div class="space-y-3">
                @foreach($topClients as $index => $client)
                <div class="relative group hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition-all duration-200 p-3 -mx-3">
                    <div class="flex items-start justify-between ml-4">
                        <div class="flex-1">
                            <!-- Client Info -->
                            <a href="{{ $this->getClientUrl($client->client_id) }}" class="flex items-center space-x-3 group/client hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg p-2 -m-2 transition-colors duration-150">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-red-100 to-orange-100 dark:from-red-900/50 dark:to-orange-900/50 flex items-center justify-center group-hover/client:scale-110 transition-transform duration-150">
                                    <span class="text-lg font-bold text-red-600 dark:text-red-400">
                                        {{ strtoupper(substr($client->client_name, 0, 1)) }}
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate group-hover/client:text-blue-600 dark:group-hover/client:text-blue-400 transition-colors duration-150">
                                        {{ $client->client_name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $client->month }} • {{ $client->total_invoices }} Faktur
                                    </p>
                                </div>
                            </a>

                            <!-- Peredaran Bruto -->
                            <div class="mt-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Peredaran Bruto:</span>
                                    <div class="text-right">
                                        <span class="text-sm font-bold text-gray-900 dark:text-white block">
                                            {{ $this->formatCurrency($client->total_peredaran_bruto) }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 italic" title="{{ $this->formatCurrencyText($client->total_peredaran_bruto) }}">
                                            {{ $this->formatCurrencyTextShort($client->total_peredaran_bruto) }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Progress Bar -->
                                @php
                                    $maxAmount = $topClients->first()->total_peredaran_bruto;
                                    $percentage = $maxAmount > 0 ? ($client->total_peredaran_bruto / $maxAmount) * 100 : 0;
                                @endphp
                                <div class="mt-2">
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-gradient-to-r from-red-500 to-orange-500 h-2 rounded-full transition-all duration-500" 
                                             style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Badges -->
                            <div class="mt-3 flex flex-wrap gap-1">
                                @if($client->ppn_report_status === 'Belum Lapor')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300">
                                        <x-heroicon-m-exclamation-triangle class="w-3 h-3 mr-1" />
                                        PPN
                                    </span>
                                @endif
                                
                                @if($client->pph_report_status === 'Belum Lapor')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300">
                                        <x-heroicon-m-exclamation-triangle class="w-3 h-3 mr-1" />
                                        PPh
                                    </span>
                                @endif
                                
                                @if($client->bupot_report_status === 'Belum Lapor')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300">
                                        <x-heroicon-m-exclamation-triangle class="w-3 h-3 mr-1" />
                                        Bupot
                                    </span>
                                @endif
                            </div>

                            <!-- Urgency Indicator -->
                            @php $unreportedCount = $this->getUnreportedCount($client); @endphp
                            <div class="mt-2 flex items-center justify-between">
                                <div class="flex items-center space-x-1">
                                    @for($i = 0; $i < $unreportedCount; $i++)
                                        <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                    @endfor
                                    @for($i = $unreportedCount; $i < 3; $i++)
                                        <div class="w-2 h-2 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
                                    @endfor
                                    <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $unreportedCount }}/3 belum lapor
                                    </span>
                                </div>
                                
                                <!-- Action Button -->
                                <a href="{{ $this->getTaxReportUrl($client->id) }}" 
                                   class="inline-flex items-center px-2 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-md transition-colors duration-150 shadow-sm">
                                    <x-heroicon-m-eye class="w-3 h-3 mr-1" />
                                    Lihat
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Summary Footer - Fixed at bottom -->
        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex-shrink-0">
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <div class="flex items-center">
                    <x-heroicon-m-exclamation-triangle class="w-4 h-4 mr-1 text-red-500" />
                    <span>{{ count($topClients) }} klien membutuhkan perhatian</span>
                </div>
                <span class="font-medium">Total: {{ $this->formatCurrency($topClients->sum('total_peredaran_bruto')) }}</span>
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="flex-1 flex items-center justify-center px-6 py-8">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/50">
                    <x-heroicon-o-check-circle class="h-6 w-6 text-green-600 dark:text-green-400" />
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Semua Sudah Lapor!</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Tidak ada klien dengan laporan pajak yang tertunda.
                </p>
                <div class="mt-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300">
                        <x-heroicon-m-check-circle class="w-3 h-3 mr-1" />
                        Semua Up to Date
                    </span>
                </div>
            </div>
        </div>
    @endif

    <!-- Refresh Button - Fixed at bottom -->
    <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
        <div class="flex justify-center">
            <button 
                wire:click="loadTopUnreportedClients"
                class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-colors duration-150"
                wire:loading.attr="disabled">
                <x-heroicon-m-arrow-path class="w-3 h-3 mr-1" wire:loading.class="animate-spin" />
                <span wire:loading.remove>Refresh Data</span>
                <span wire:loading>Memuat...</span>
            </button>
        </div>
    </div>
</div>