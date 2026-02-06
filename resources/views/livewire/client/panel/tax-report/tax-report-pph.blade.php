<div class="space-y-6">
    @if(!$hasAccess)
    {{-- No Access State --}}
    <div class="flex flex-col items-center justify-center rounded-2xl border border-gray-200 bg-gradient-to-br from-gray-50 to-gray-100 p-12 dark:border-gray-700 dark:from-gray-800 dark:to-gray-900">
        <div class="rounded-full bg-white p-4 shadow-sm dark:bg-gray-800">
            <x-heroicon-o-lock-closed class="h-16 w-16 text-gray-400" />
        </div>
        <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-white">Akses Tidak Tersedia</h3>
        <p class="mt-2 max-w-md text-center text-sm text-gray-600 dark:text-gray-400">
            Anda tidak memiliki akses ke data PPh ini.
        </p>
    </div>
    @elseif(!$taxReport)
    {{-- No Data State --}}
    <div class="flex flex-col items-center justify-center rounded-2xl border border-gray-200 bg-gradient-to-br from-gray-50 to-gray-100 p-12 dark:border-gray-700 dark:from-gray-800 dark:to-gray-900">
        <div class="rounded-full bg-white p-4 shadow-sm dark:bg-gray-800">
            <x-heroicon-o-banknotes class="h-16 w-16 text-gray-400" />
        </div>
        <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-white">Data PPh Tidak Tersedia</h3>
        <p class="mt-2 max-w-md text-center text-sm text-gray-600 dark:text-gray-400">
            Belum ada data PPh untuk periode ini.
        </p>
    </div>
    @else

    {{-- Header Status --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Detail Laporan PPh</h2>
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
                    <span class="block text-xs font-medium {{ $reportStatus === 'Sudah Lapor' ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">Status PPh</span>
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

    {{-- PPh Summary Cards --}}
    <div class="grid gap-4 sm:gap-6 md:grid-cols-2 xl:grid-cols-4">

        {{-- PPh 21 Card --}}
        <div class="rounded-lg bg-white border border-gray-200 dark:bg-gray-900 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">PPh 21</h3>
                    <span class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-xs font-medium text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                        Karyawan
                    </span>
                </div>
            </div>
            <div class="px-5 py-4">
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pajak Terutang</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($pph21Total, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">DPP</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                Rp {{ number_format($pph21Bruto, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm mt-2">
                            <span class="text-gray-600 dark:text-gray-400">Bukti Potong</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ $pph21Count }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PPh 23 Card --}}
        <div class="rounded-lg bg-white border border-gray-200 dark:bg-gray-900 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">PPh 23</h3>
                    <span class="inline-flex items-center rounded-md bg-blue-100 px-2 py-1 text-xs font-medium text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        Jasa/Dividen
                    </span>
                </div>
            </div>
            <div class="px-5 py-4">
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pajak Terutang</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($pph23Total, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">DPP</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                Rp {{ number_format($pph23Bruto, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm mt-2">
                            <span class="text-gray-600 dark:text-gray-400">Bukti Potong</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ $pph23Count }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PPh 4(2) Card --}}
        <div class="rounded-lg bg-white border border-gray-200 dark:bg-gray-900 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">PPh 4(2)</h3>
                    <span class="inline-flex items-center rounded-md bg-amber-100 px-2 py-1 text-xs font-medium text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                        Final
                    </span>
                </div>
            </div>
            <div class="px-5 py-4">
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pajak Terutang</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($pph42Total, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">DPP</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                Rp {{ number_format($pph42Bruto, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm mt-2">
                            <span class="text-gray-600 dark:text-gray-400">Bukti Potong</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ $pph42Count }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total PPh Card --}}
        <div class="rounded-lg bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 dark:from-purple-900/20 dark:to-purple-800/20 dark:border-purple-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-purple-200 dark:border-purple-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-purple-900 dark:text-purple-100">Total PPh</h3>
                    <span class="inline-flex items-center rounded-md bg-white px-2 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-200 dark:bg-purple-800 dark:text-purple-200 dark:ring-purple-600">
                        Keseluruhan
                    </span>
                </div>
            </div>
            <div class="px-5 py-4">
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-purple-600 dark:text-purple-400 mb-1">Total Pajak</p>
                        <p class="text-xl font-bold text-purple-900 dark:text-purple-100">
                            Rp {{ number_format($totalPph, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="pt-3 border-t border-purple-200 dark:border-purple-700">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-purple-600 dark:text-purple-400">Total DPP</span>
                            <span class="font-medium text-purple-900 dark:text-purple-100">
                                Rp {{ number_format($pph21Bruto + $pph23Bruto + $pph42Bruto, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm mt-2">
                            <span class="text-purple-600 dark:text-purple-400">Total Bukti Potong</span>
                            <span class="font-medium text-purple-900 dark:text-purple-100">
                                {{ $totalBuktiPotong }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Calculation Details Table --}}
    <div class="rounded-lg bg-white border border-gray-200 dark:bg-gray-900 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Rincian Perhitungan</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Jenis PPh
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            DPP
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Pajak Terutang
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Jumlah
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-800">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            PPh 21
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                            Rp {{ number_format($pph21Bruto, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900 dark:text-white">
                            Rp {{ number_format($pph21Total, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">
                            {{ $pph21Count }}
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            PPh 23
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                            Rp {{ number_format($pph23Bruto, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900 dark:text-white">
                            Rp {{ number_format($pph23Total, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">
                            {{ $pph23Count }}
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            PPh 4(2)
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                            Rp {{ number_format($pph42Bruto, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900 dark:text-white">
                            Rp {{ number_format($pph42Total, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">
                            {{ $pph42Count }}
                        </td>
                    </tr>
                    <tr class="bg-gray-50 dark:bg-gray-800">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                            TOTAL
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($pph21Bruto + $pph23Bruto + $pph42Bruto, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($totalPph, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-bold text-gray-900 dark:text-white">
                            {{ $totalBuktiPotong }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Info Note --}}
    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/50">
        <div class="flex gap-3">
            <div class="flex-shrink-0">
                <x-heroicon-o-information-circle class="h-5 w-5 text-gray-400" />
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-300 mb-1">Catatan Perhitungan</h4>
                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                    Data dihitung berdasarkan bukti potong yang telah diinput. PPh 21 untuk karyawan, PPh 23 untuk jasa/dividen, dan PPh 4(2) untuk pajak final.
                </p>
            </div>
        </div>
    </div>

    @endif
</div>
