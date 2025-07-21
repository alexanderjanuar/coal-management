<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 rounded-lg p-6 border border-blue-200 dark:border-gray-600">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Faktur Asli</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $originalInvoice->invoice_number }}</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Revisi</div>
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $revisions->count() }}</div>
            </div>
        </div>

        <!-- Original Invoice Details -->
        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Perusahaan</div>
                <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $originalInvoice->company_name }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Jenis</div>
                <div class="mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $originalInvoice->type === 'Faktur Keluaran' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                        {{ $originalInvoice->type }}
                    </span>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">DPP</div>
                <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">Rp {{ number_format($originalInvoice->dpp, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- Timeline Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h4 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Riwayat Revisi
            </h4>
        </div>

        <div class="p-6">
            <!-- Original Invoice Timeline Item -->
            <div class="relative">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    Faktur Asli Dibuat
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Original
                                    </span>
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $originalInvoice->invoice_number }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-900 dark:text-white font-medium">Rp {{ number_format($originalInvoice->ppn, 0, ',', '.') }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $originalInvoice->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        @if($originalInvoice->notes)
                            <div class="mt-2 text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 rounded p-2">
                                {{ Str::limit($originalInvoice->notes, 100) }}
                            </div>
                        @endif
                    </div>
                </div>

                @if($revisions->count() > 0)
                    <div class="absolute left-5 top-10 w-0.5 h-8 bg-gray-200 dark:bg-gray-600"></div>
                @endif
            </div>

            <!-- Revision Timeline Items -->
            @foreach($revisions as $index => $revision)
                <div class="relative mt-8">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        Revisi #{{ $revision->revision_number }}
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            Revisi
                                        </span>
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $revision->invoice_number }}</p>
                                    @if($revision->revision_reason)
                                        <p class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                                            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $revision->revision_reason }}
                                        </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-900 dark:text-white font-medium">Rp {{ number_format($revision->ppn, 0, ',', '.') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $revision->created_at->format('d M Y, H:i') }}</p>
                                    @if($revision->created_by)
                                        @php
                                            $user = \App\Models\User::find($revision->created_by);
                                        @endphp
                                        <p class="text-xs text-gray-500 dark:text-gray-400">oleh {{ $user?->name ?? 'Unknown' }}</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Comparison with Previous Version -->
                            @php
                                $previousVersion = $index === 0 ? $originalInvoice : $revisions[$index - 1];
                                $dppChanged = $revision->dpp != $previousVersion->dpp;
                                $ppnChanged = $revision->ppn != $previousVersion->ppn;
                                $companyChanged = $revision->company_name != $previousVersion->company_name;
                            @endphp

                            @if($dppChanged || $ppnChanged || $companyChanged)
                                <div class="mt-3 bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                    <h5 class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                        Perubahan dari Versi Sebelumnya
                                    </h5>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                                        @if($dppChanged)
                                            <div class="bg-white dark:bg-gray-800 rounded p-2 border border-gray-200 dark:border-gray-600">
                                                <div class="text-gray-500 dark:text-gray-400">DPP</div>
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-red-600 dark:text-red-400 line-through">Rp {{ number_format($previousVersion->dpp, 0, ',', '.') }}</span>
                                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                                    </svg>
                                                    <span class="text-green-600 dark:text-green-400 font-medium">Rp {{ number_format($revision->dpp, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        @endif
                                        @if($ppnChanged)
                                            <div class="bg-white dark:bg-gray-800 rounded p-2 border border-gray-200 dark:border-gray-600">
                                                <div class="text-gray-500 dark:text-gray-400">PPN</div>
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-red-600 dark:text-red-400 line-through">Rp {{ number_format($previousVersion->ppn, 0, ',', '.') }}</span>
                                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                                    </svg>
                                                    <span class="text-green-600 dark:text-green-400 font-medium">Rp {{ number_format($revision->ppn, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        @endif
                                        @if($companyChanged)
                                            <div class="bg-white dark:bg-gray-800 rounded p-2 border border-gray-200 dark:border-gray-600">
                                                <div class="text-gray-500 dark:text-gray-400">Perusahaan</div>
                                                <div class="space-y-1">
                                                    <div class="text-red-600 dark:text-red-400 line-through">{{ Str::limit($previousVersion->company_name, 20) }}</div>
                                                    <div class="text-green-600 dark:text-green-400 font-medium">{{ Str::limit($revision->company_name, 20) }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if($revision->notes)
                                <div class="mt-2 text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 rounded p-2">
                                    {{ Str::limit($revision->notes, 100) }}
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($index < $revisions->count() - 1)
                        <div class="absolute left-5 top-10 w-0.5 h-8 bg-gray-200 dark:bg-gray-600"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Summary Section -->
    <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-gray-800 dark:to-gray-700 rounded-lg p-6 border border-green-200 dark:border-gray-600">
        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Ringkasan
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600 text-center">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $revisions->count() + 1 }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Versi</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600 text-center">
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $revisions->last()?->created_at?->format('d M Y') ?? $originalInvoice->created_at->format('d M Y') }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Versi Terakhir</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600 text-center">
                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                    @php
                        $latestRevision = $revisions->last() ?? $originalInvoice;
                    @endphp
                    Rp {{ number_format($latestRevision->ppn, 0, ',', '.') }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">PPN Terkini</div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex justify-end space-x-3">
        <button type="button" onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Cetak Riwayat
        </button>
    </div>
</div>

<style>
    @media print {
        .space-y-6 > *:not(:last-child) {
            page-break-inside: avoid;
        }
    }
</style>