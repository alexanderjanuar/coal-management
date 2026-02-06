<div class="space-y-6">
    @if(!$hasAccess)
    {{-- No Access State --}}
    <div class="flex flex-col items-center justify-center rounded-2xl border border-gray-200 bg-gradient-to-br from-gray-50 to-gray-100 p-12 dark:border-gray-700 dark:from-gray-800 dark:to-gray-900">
        <div class="rounded-full bg-white p-4 shadow-sm dark:bg-gray-800">
            <x-heroicon-o-lock-closed class="h-16 w-16 text-gray-400" />
        </div>
        <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-white">Akses Tidak Tersedia</h3>
        <p class="mt-2 max-w-md text-center text-sm text-gray-600 dark:text-gray-400">
            Anda tidak memiliki akses ke data Bupot ini.
        </p>
    </div>
    @elseif(!$taxReport)
    {{-- No Data State --}}
    <div class="flex flex-col items-center justify-center rounded-2xl border border-gray-200 bg-gradient-to-br from-gray-50 to-gray-100 p-12 dark:border-gray-700 dark:from-gray-800 dark:to-gray-900">
        <div class="rounded-full bg-white p-4 shadow-sm dark:bg-gray-800">
            <x-heroicon-o-receipt-percent class="h-16 w-16 text-gray-400" />
        </div>
        <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-white">Data Bupot Tidak Tersedia</h3>
        <p class="mt-2 max-w-md text-center text-sm text-gray-600 dark:text-gray-400">
            Belum ada data Bupot untuk periode ini.
        </p>
    </div>
    @else

    {{-- Header Status --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Detail PPh Unifikasi (Bupot)</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Periode {{ $this->periodName }} • {{ $this->clientName }}
            </p>
        </div>

        {{-- Status Badge --}}
        <div class="rounded-xl {{ $reportStatus === 'Sudah Lapor' ? 'bg-gradient-to-br from-green-50 to-green-100 ring-1 ring-green-200 dark:from-green-500/10 dark:to-green-600/10 dark:ring-green-500/20' : 'bg-gradient-to-br from-orange-50 to-orange-100 ring-1 ring-orange-200 dark:from-orange-500/10 dark:to-orange-600/10 dark:ring-orange-500/20' }} px-5 py-3 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $reportStatus === 'Sudah Lapor' ? 'bg-green-500' : 'bg-orange-500' }} shadow-lg">
                    @if($reportStatus === 'Sudah Lapor')
                    <x-heroicon-m-check-circle class="h-5 w-5 text-white" />
                    @else
                    <x-heroicon-m-exclamation-circle class="h-5 w-5 text-white" />
                    @endif
                </div>
                <div>
                    <span class="block text-xs font-medium {{ $reportStatus === 'Sudah Lapor' ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">Status Bupot</span>
                    <span class="block text-sm font-bold {{ $reportStatus === 'Sudah Lapor' ? 'text-green-700 dark:text-green-300' : 'text-orange-700 dark:text-orange-300' }}">
                        {{ $reportStatus }}
                        @if($reportedAt)
                        <span class="font-normal text-xs">({{ \Carbon\Carbon::parse($reportedAt)->format('d M Y') }})</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid gap-4 sm:gap-6 md:grid-cols-2">
        {{-- Total Bupot Card --}}
        <div class="rounded-lg bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 dark:from-orange-900/20 dark:to-orange-800/20 dark:border-orange-700 overflow-hidden">
            <div class="px-5 py-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-500 shadow-lg">
                        <x-heroicon-o-receipt-percent class="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-orange-600 dark:text-orange-400">Total Jumlah Bupot</p>
                        <p class="text-2xl font-bold text-orange-900 dark:text-orange-100">{{ $bupotCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Amount Card --}}
        <div class="rounded-lg bg-gradient-to-br from-emerald-50 to-emerald-100 border border-emerald-200 dark:from-emerald-900/20 dark:to-emerald-800/20 dark:border-emerald-700 overflow-hidden">
            <div class="px-5 py-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500 shadow-lg">
                        <x-heroicon-o-banknotes class="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Total Nominal Bupot</p>
                        <p class="text-2xl font-bold text-emerald-900 dark:text-emerald-100">Rp {{ number_format($totalBupotAmount, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bupot Table --}}
    <div class="rounded-lg bg-white border border-gray-200 dark:bg-gray-900 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Daftar Bukti Potong</h3>
        </div>

        @if(count($bupots) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Nama Perusahaan
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            NPWP
                        </th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Tipe Bupot
                        </th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Jenis PPh
                        </th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Masa Pajak
                        </th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            DPP
                        </th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Jumlah PPh
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-800">
                    @foreach($bupots as $bupot)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            {{ $bupot['company_name'] ?? '-' }}
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400 font-mono">
                            {{ $bupot['npwp'] ?? '-' }}
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center">
                            <span class="inline-flex items-center rounded-md bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                {{ $bupot['bupot_type'] ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center">
                            <span class="inline-flex items-center rounded-md bg-orange-100 px-2 py-1 text-xs font-medium text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">
                                {{ $bupot['pph_type'] ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-600 dark:text-gray-400">
                            {{ $bupot['tax_period'] ?? '-' }}
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-700 dark:text-gray-300">
                            Rp {{ number_format($bupot['dpp'] ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900 dark:text-white">
                            Rp {{ number_format($bupot['bupot_amount'] ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-sm font-bold text-gray-900 dark:text-white">
                            TOTAL
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($totalBupotAmount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        {{-- Empty State --}}
        <div class="flex flex-col items-center justify-center py-12">
            <div class="rounded-full bg-gray-100 p-4 dark:bg-gray-800">
                <x-heroicon-o-document-text class="h-8 w-8 text-gray-400" />
            </div>
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                Belum ada data bukti potong untuk periode ini.
            </p>
        </div>
        @endif
    </div>

    {{-- Info Note --}}
    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/50">
        <div class="flex gap-3">
            <div class="flex-shrink-0">
                <x-heroicon-o-information-circle class="h-5 w-5 text-gray-400" />
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-300 mb-1">Catatan</h4>
                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                    Data bupot PPh Unifikasi yang ditampilkan adalah bukti potong yang telah diinput oleh tim kami untuk periode pelaporan ini.
                </p>
            </div>
        </div>
    </div>

    @endif
</div>
