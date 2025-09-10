<x-filament::modal id="pending-clients-modal" width="7xl">
    <x-slot name="heading">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div
                        class="p-3 bg-gradient-to-br from-red-100 to-rose-100 dark:from-red-900/50 dark:to-rose-900/50 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center space-x-3">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            Klien Tertunggak
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="h-2 w-2 bg-red-500 rounded-full animate-pulse"></div>
                            <span class="text-sm font-medium text-red-600 dark:text-red-400">Memerlukan Tindakan</span>
                        </div>
                        <div class="flex items-center">
                            <span
                                class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                {{ count($pendingClients['clients'] ?? []) }} Klien
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 mt-1">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $pendingClients['reportType'] ?? '' }}
                        </span>
                        <span class="text-gray-300 dark:text-gray-600">•</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $pendingClients['date'] ?? '' }}
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </x-slot>

    @if(isset($pendingClients['clients']) && count($pendingClients['clients']) > 0)
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        @php
        $totalClients = count($pendingClients['clients']);
        $totalAmount = 0;
        $avgAmount = 0;

        if(isset($pendingClients['reportType']) && strpos($pendingClients['reportType'], 'Setor PPh dan PPN') !== false)
        {
        $totalAmount = collect($pendingClients['clients'])->sum('dueAmount');
        $avgAmount = $totalAmount / $totalClients;
        }
        @endphp

        <div
            class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Klien</p>
                    <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $totalClients }}</p>
                </div>
            </div>
        </div>

        @if($totalAmount > 0)
        <div
            class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 border border-green-200 dark:border-green-800">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 dark:bg-green-900/50 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 dark:text-green-400"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-600 dark:text-green-400">Total Peredaran Bruto</p>
                    <p class="text-lg font-bold text-green-700 dark:text-green-300">Rp {{ number_format($totalAmount, 0,
                        ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div
            class="bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/20 rounded-xl p-4 border border-amber-200 dark:border-amber-800">
            <div class="flex items-center">
                <div class="p-2 bg-amber-100 dark:bg-amber-900/50 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600 dark:text-amber-400"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-amber-600 dark:text-amber-400">Rata-rata Peredaran</p>
                    <p class="text-lg font-bold text-amber-700 dark:text-amber-300">Rp {{ number_format($avgAmount, 0,
                        ',', '.') }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Clients Table -->
    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Klien
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            NPWP
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status
                        </th>
                        @if(isset($pendingClients['reportType']) && strpos($pendingClients['reportType'], 'Setor PPh dan
                        PPN') !== false)
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Peredaran Bruto
                        </th>
                        @elseif(isset($pendingClients['reportType']) && strpos($pendingClients['reportType'], 'PPh 21')
                        !== false)
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Jumlah Karyawan
                        </th>
                        @elseif(isset($pendingClients['reportType']) && strpos($pendingClients['reportType'], 'PPN') !==
                        false)
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Jumlah Transaksi
                        </th>
                        @endif
                        <th scope="col"
                            class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                    @foreach($pendingClients['clients'] as $index => $client)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-all duration-200 group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if(isset($client['logo']) && $client['logo'])
                                    <img class="h-10 w-10 rounded-full object-cover ring-2 ring-white dark:ring-gray-800 shadow-sm"
                                        src="{{ asset('storage/' . $client['logo']) }}" alt="{{ $client['name'] }}"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/50 dark:to-purple-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm ring-2 ring-white dark:ring-gray-800 shadow-sm"
                                        style="display: none;">
                                        {{ strtoupper(substr($client['name'], 0, 2)) }}
                                    </div>
                                    @else
                                    <div
                                        class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/50 dark:to-purple-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm ring-2 ring-white dark:ring-gray-800 shadow-sm">
                                        {{ strtoupper(substr($client['name'], 0, 2)) }}
                                    </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <a href="{{ $this->getClientUrl($client['id']) }}"
                                        class="text-sm font-semibold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer">
                                        {{ $client['name'] }}
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div
                                class="text-sm text-gray-600 dark:text-gray-300 font-mono bg-gray-50 dark:bg-gray-700 px-2 py-1 rounded">
                                {{ $client['NPWP'] }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-semibold bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-800">
                                <div class="h-1.5 w-1.5 bg-yellow-500 rounded-full mr-2 animate-pulse"></div>
                                {{ $client['status'] }}
                            </span>
                        </td>
                        @if(isset($pendingClients['reportType']) && strpos($pendingClients['reportType'], 'Setor PPh dan
                        PPN') !== false)
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900 dark:text-white">
                                Rp {{ number_format($client['dueAmount'] ?? 0, 0, ',', '.') }}
                            </div>
                            @if(($client['dueAmount'] ?? 0) > 1000000)
                            <div class="text-xs text-green-500 dark:text-green-400">Peredaran Besar</div>
                            @endif
                        </td>
                        @elseif(isset($pendingClients['reportType']) && strpos($pendingClients['reportType'], 'PPh 21')
                        !== false)
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $client['employees']
                                    ?? 0 }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">orang</span>
                            </div>
                        </td>
                        @elseif(isset($pendingClients['reportType']) && strpos($pendingClients['reportType'], 'PPN') !==
                        false)
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                    $client['transaksiCount'] ?? 0 }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">transaksi</span>
                            </div>
                        </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <x-filament::button size="xs" color="gray"
                                href="{{ $this->getTaxReportUrl($client['tax_report_id']) }}" tag="a"
                                icon="heroicon-o-eye" class="hover:scale-105 transition-transform">
                                Detail
                            </x-filament::button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <!-- Empty State -->
    <div class="text-center py-12">
        <div class="mx-auto h-24 w-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400 dark:text-gray-500" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Semua Klien Sudah Melaporkan</h3>
        <p class="text-gray-500 dark:text-gray-400">Tidak ada klien yang tertunggak untuk tanggal ini.</p>
    </div>
    @endif

    <x-slot name="footerActions">
        @if(isset($pendingClients['clients']) && count($pendingClients['clients']) > 0)
        <x-filament::button color="success" icon="heroicon-o-paper-airplane" wire:click="sendMassReminder"
            class="hover:scale-105 transition-all duration-200 shadow-lg">
            Kirim Pengingat Massal
        </x-filament::button>
        @endif
    </x-slot>
</x-filament::modal>