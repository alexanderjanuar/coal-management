<x-filament::page>
    <div class="space-y-6" x-data="{ activeTab: 'invoices' }">

        {{-- Header Section - Compact & Minimalist --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-800">
            <div class="flex items-stretch">
                {{-- Month Emphasis Section - Compact --}}
                <div
                    class="flex w-32 flex-col items-center justify-center border-r border-gray-200 bg-gradient-to-br from-gray-50 to-gray-100 p-4 dark:border-gray-700 dark:from-gray-900 dark:to-gray-800">
                    <div class="text-center">
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                            Periode
                        </p>
                        <h3 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $record->month }}
                        </h3>
                        <p class="mt-1 text-xs font-medium text-gray-600 dark:text-gray-400">
                            {{ $record->created_at->format('Y') }}
                        </p>
                    </div>
                </div>

                {{-- Content Section - Compact --}}
                <div class="flex flex-1 items-center justify-between p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                            <x-filament::icon icon="heroicon-o-building-office-2"
                                class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">
                                {{ $record->client->name }}
                            </h2>
                            <div class="mt-0.5 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <div class="flex items-center gap-1">
                                    <x-filament::icon icon="heroicon-o-calendar" class="h-3 w-3" />
                                    <span>{{ $record->created_at->format('d M Y') }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <x-filament::icon icon="heroicon-o-clock" class="h-3 w-3" />
                                    <span>{{ $record->created_at->format('H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Status Section - Compact --}}
                    @if($record->payment_status)
                    <div class="inline-flex items-center gap-2 rounded-lg bg-green-50 px-3 py-1.5 dark:bg-green-900/20">
                        <span class="relative flex h-1.5 w-1.5">
                            <span
                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-green-500"></span>
                        </span>
                        <span class="text-xs font-semibold text-green-700 dark:text-green-400">
                            {{ $record->payment_status }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="rounded-2xl bg-white p-2 shadow-sm dark:bg-gray-800">
            <div class="flex gap-2">
                {{-- Invoices Tab --}}
                <button @click="activeTab = 'invoices'" :class="activeTab === 'invoices' 
                        ? 'bg-primary-600 text-white shadow-md' 
                        : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700'"
                    class="flex flex-1 items-center justify-center gap-2 rounded-xl px-6 py-3 text-sm font-semibold transition-all duration-200">
                    <x-filament::icon icon="heroicon-o-document-text" class="h-5 w-5" />
                    <span>PPN</span>
                    <span :class="activeTab === 'invoices' 
                            ? 'bg-primary-500 text-white' 
                            : 'bg-gray-200 text-gray-600 dark:bg-gray-600 dark:text-gray-300'"
                        class="rounded-full px-2 py-0.5 text-xs font-bold">
                        {{ $record->invoices->count() }}
                    </span>
                </button>

                {{-- PPh Tab --}}
                <button @click="activeTab = 'pph'" :class="activeTab === 'pph' 
                        ? 'bg-primary-600 text-white shadow-md' 
                        : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700'"
                    class="flex flex-1 items-center justify-center gap-2 rounded-xl px-6 py-3 text-sm font-semibold transition-all duration-200">
                    <x-filament::icon icon="heroicon-o-banknotes" class="h-5 w-5" />
                    <span>PPh</span>
                    <span :class="activeTab === 'pph' 
                            ? 'bg-primary-500 text-white' 
                            : 'bg-gray-200 text-gray-600 dark:bg-gray-600 dark:text-gray-300'"
                        class="rounded-full px-2 py-0.5 text-xs font-bold">
                        {{ $record->incomeTaxs->count() }}
                    </span>
                </button>

                {{-- Bupot Tab --}}
                <button @click="activeTab = 'bupot'" :class="activeTab === 'bupot' 
                        ? 'bg-primary-600 text-white shadow-md' 
                        : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700'"
                    class="flex flex-1 items-center justify-center gap-2 rounded-xl px-6 py-3 text-sm font-semibold transition-all duration-200">
                    <x-filament::icon icon="heroicon-o-receipt-percent" class="h-5 w-5" />
                    <span>PPh Unifikasi</span>
                    <span :class="activeTab === 'bupot' 
                            ? 'bg-primary-500 text-white' 
                            : 'bg-gray-200 text-gray-600 dark:bg-gray-600 dark:text-gray-300'"
                        class="rounded-full px-2 py-0.5 text-xs font-bold">
                        {{ $record->bupots->count() }}
                    </span>
                </button>
            </div>
        </div>

        {{-- Tab Content --}}
        <div class="relative">
            {{-- Invoices Content --}}
            <div x-show="activeTab === 'invoices'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95">
                @livewire('tax-report.components.tax-report-invoices', ['taxReportId' => $record->id])
            </div>

            {{-- PPh Content --}}
            <div x-show="activeTab === 'pph'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95">
                @livewire('tax-report.components.tax-report-pph', ['taxReportId' => $record->id])
            </div>

            {{-- Bupot Content --}}
            <div x-show="activeTab === 'bupot'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95">
                @livewire('tax-report.components.tax-report-bupot', ['taxReportId' => $record->id])
            </div>
        </div>

    </div>
</x-filament::page>