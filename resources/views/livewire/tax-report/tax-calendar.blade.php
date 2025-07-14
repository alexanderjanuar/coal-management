<div
    class="w-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden transition-colors duration-200">
    <div class="p-4 md:p-6">
        <!-- Calendar Section -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center">
                    <button wire:click="goToPreviousMonth"
                        class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    <h2 class="text-xl font-medium px-4 text-gray-900 dark:text-white">{{
                        $currentDate->translatedFormat('F Y') }}</h2>
                    <button wire:click="goToNextMonth"
                        class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <div class="flex items-center">
                    <div class="p-2 bg-amber-100 dark:bg-amber-900/50 rounded-lg mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600 dark:text-amber-400"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Kalender Pajak</h2>
                </div>
            </div>

            <!-- Calendar Header -->
            <div class="grid grid-cols-7 gap-1 border-b border-gray-200 dark:border-gray-600 pb-3 mb-3">
                @foreach(['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $day)
                <div class="text-center text-gray-500 dark:text-gray-400 font-medium text-sm py-2">
                    {{ $day }}
                </div>
                @endforeach
            </div>

            <!-- Calendar Grid -->
            <div class="grid grid-cols-7 gap-1">
                @foreach($calendarDays as $day)
                <div wire:key="day-{{ $day['date'] }}" class="
                                relative flex flex-col items-center h-16 w-full text-lg font-medium 
                                cursor-pointer transition-all duration-200 rounded-lg group
                                {{ !$day['isCurrentMonth'] ? 'text-gray-300 dark:text-gray-600' : 'text-gray-700 dark:text-gray-200' }}
                                {{ $day['isToday'] ? 'ring-2 ring-blue-400 dark:ring-blue-500 bg-blue-50 dark:bg-blue-900/20' : '' }}
                                {{ $selectedDate === $day['date'] ? 'bg-amber-100 dark:bg-amber-900/30 ring-2 ring-amber-400 dark:ring-amber-500' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50' }}
                            " wire:click="selectDate('{{ $day['date'] }}')">

                    <!-- Day Number -->
                    <span class="mt-1 {{ $day['isToday'] ? 'text-blue-600 dark:text-blue-400 font-bold' : '' }}">
                        {{ $day['day'] }}
                    </span>

                    <!-- Pending Clients Count -->
                    @if($day['pendingClientsCount'] > 0)
                    <div class="absolute bottom-2 flex items-center justify-center">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-semibold 
                                   bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300 
                                   ring-1 ring-red-200 dark:ring-red-800">
                            {{ $day['pendingClientsCount'] }}
                        </span>
                    </div>
                    @endif

                    <!-- Event Indicator -->
                    @if($day['hasEvent'])
                    <div class="absolute bottom-1 left-1/2 transform -translate-x-1/2">
                        <div
                            class="h-1.5 w-8 bg-gradient-to-r from-yellow-400 to-amber-500 dark:from-yellow-500 dark:to-amber-600 rounded-full">
                        </div>
                    </div>
                    @endif

                    <!-- Hover Effect Overlay -->
                    <div
                        class="absolute inset-0 bg-gray-100 dark:bg-gray-600 opacity-0 group-hover:opacity-10 rounded-lg transition-opacity duration-200">
                    </div>
                </div>
                @endforeach
            </div>
            <!-- Tax Event Modal -->
            @if($isModalOpen)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
                aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-black bg-opacity-50 dark:bg-opacity-70 transition-opacity"
                        aria-hidden="true"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div
                        class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <div class="flex items-center mb-4">
                                        <div class="p-2 bg-amber-100 dark:bg-amber-900/50 rounded-lg mr-3">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white"
                                                id="modal-title">
                                                Detail Jadwal Pajak
                                            </h3>
                                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                                                {{ Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-4 space-y-4 max-h-96 overflow-y-auto">
                                        @if(count($selectedEvents) > 0)
                                        @foreach($selectedEvents as $event)
                                        <div
                                            class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50">
                                            <div class="flex items-start">
                                                <div
                                                    class="p-2 rounded-lg mr-3 {{ $event['priority'] === 'high' ? 'bg-red-100 dark:bg-red-900/50' : ($event['priority'] === 'medium' ? 'bg-yellow-100 dark:bg-yellow-900/50' : 'bg-blue-100 dark:bg-blue-900/50') }}">
                                                    @if($event['type'] === 'payment')
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="h-4 w-4 {{ $event['priority'] === 'high' ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400' }}"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                                    </svg>
                                                    @else
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="h-4 w-4 {{ $event['priority'] === 'high' ? 'text-red-600 dark:text-red-400' : ($event['priority'] === 'medium' ? 'text-yellow-600 dark:text-yellow-400' : 'text-blue-600 dark:text-blue-400') }}"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    @endif
                                                </div>
                                                <div class="flex-1">
                                                    <h4 class="font-semibold text-gray-900 dark:text-white text-sm">{{
                                                        $event['title'] }}</h4>
                                                    <p class="text-gray-600 dark:text-gray-300 mt-1 text-sm">{{
                                                        $event['description'] }}</p>

                                                    @if(isset($event['actionLink']))
                                                    <div class="mt-3">
                                                        <a href="{{ $event['actionLink'] }}"
                                                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg transition-colors duration-200
                                                                   {{ $event['type'] === 'payment' ? 'text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/50 hover:bg-red-200 dark:hover:bg-red-900/70' : 'text-blue-700 dark:text-blue-300 bg-blue-100 dark:bg-blue-900/50 hover:bg-blue-200 dark:hover:bg-blue-900/70' }}">
                                                            {{ $event['actionText'] }}
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1"
                                                                viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd"
                                                                    d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        </a>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                        @else
                                        <div class="text-center py-8">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <p class="text-gray-500 dark:text-gray-400 mt-2">Tidak ada jadwal pajak
                                                untuk tanggal ini.</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" wire:click="closeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-blue-400 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Pending Clients Modal -->
            @if($isClientModalOpen)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
                aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-black bg-opacity-50 dark:bg-opacity-70 transition-opacity"
                        aria-hidden="true"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div
                        class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <div class="flex justify-between items-center mb-6">
                                        <div class="flex items-center">
                                            <div class="p-2 bg-red-100 dark:bg-red-900/50 rounded-lg mr-3">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="h-6 w-6 text-red-600 dark:text-red-400" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white"
                                                    id="modal-title">
                                                    Klien yang Belum {{ $pendingClients['reportType'] }}
                                                </h3>
                                                <p class="text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ $pendingClients['date'] }}
                                                </p>
                                            </div>
                                        </div>
                                        <span
                                            class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            {{ count($pendingClients['clients']) }} Klien
                                        </span>
                                    </div>

                                    <div
                                        class="mt-4 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-600">
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                                <thead class="bg-gray-50 dark:bg-gray-700">
                                                    <tr>
                                                        <th scope="col"
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                            Nama Klien
                                                        </th>
                                                        <th scope="col"
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                            NPWP
                                                        </th>
                                                        <th scope="col"
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                            Status
                                                        </th>
                                                        @if(strpos($pendingClients['reportType'], 'Setor PPh dan PPN')
                                                        !== false)
                                                        <th scope="col"
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                            Jumlah Tagihan
                                                        </th>
                                                        @elseif(strpos($pendingClients['reportType'], 'PPh 21') !==
                                                        false)
                                                        <th scope="col"
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                            Jumlah Karyawan
                                                        </th>
                                                        @elseif(strpos($pendingClients['reportType'], 'PPN') !== false)
                                                        <th scope="col"
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                            Jumlah Transaksi
                                                        </th>
                                                        @endif
                                                        <th scope="col"
                                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                            Aksi
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody
                                                    class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                                    @foreach($pendingClients['clients'] as $client)
                                                    <tr
                                                        class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <div class="flex items-center">
                                                                <div class="flex-shrink-0 h-8 w-8">
                                                                    <div
                                                                        class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/50 dark:to-purple-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-semibold text-sm">
                                                                        {{ strtoupper(substr($client['name'], 0, 1)) }}
                                                                    </div>
                                                                </div>
                                                                <div class="ml-3">
                                                                    <div
                                                                        class="text-sm font-medium text-gray-900 dark:text-white">
                                                                        {{ $client['name'] }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <div
                                                                class="text-sm text-gray-500 dark:text-gray-400 font-mono">
                                                                {{ $client['NPWP'] }}</div>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <span
                                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300">
                                                                {{ $client['status'] }}
                                                            </span>
                                                        </td>
                                                        @if(strpos($pendingClients['reportType'], 'Setor PPh dan PPN')
                                                        !== false)
                                                        <td
                                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-medium">
                                                            Rp {{ number_format($client['dueAmount'], 0, ',', '.') }}
                                                        </td>
                                                        @elseif(strpos($pendingClients['reportType'], 'PPh 21') !==
                                                        false)
                                                        <td
                                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                            {{ $client['employees'] }} orang
                                                        </td>
                                                        @elseif(strpos($pendingClients['reportType'], 'PPN') !== false)
                                                        <td
                                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                            {{ $client['transaksiCount'] }} transaksi
                                                        </td>
                                                        @endif
                                                        <td
                                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                            <div class="flex justify-end space-x-2">
                                                                <a href="#"
                                                                    class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200">
                                                                    Detail
                                                                </a>
                                                                <a href="#"
                                                                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors duration-200">
                                                                    @if(strpos($pendingClients['reportType'], 'Setor')
                                                                    !== false)
                                                                    Bayar
                                                                    @else
                                                                    Lapor
                                                                    @endif
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button"
                                class="ml-3 inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-blue-400 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-5 5-5-5h5v-5a7.5 7.5 0 01-15 0V7a2 2 0 012-2h3m9 11V7a2 2 0 00-2-2H9m0 0V3h6v2H9V3z" />
                                </svg>
                                Kirim Pengingat
                            </button>
                            <button type="button" wire:click="closeClientModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-blue-400 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Tax Schedule Section -->
        <div>
            <div class="flex items-center mb-6">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Jadwal Pajak Bulan Ini</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $currentDate->translatedFormat('F Y') }}</p>
                </div>
            </div>

            @php
            $currentMonthEvents = $this->getTaxSchedule();
            @endphp

            @if(count($currentMonthEvents) > 0)
            <div class="space-y-4">
                @foreach($currentMonthEvents as $event)
                <div
                    class="group relative overflow-hidden rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 p-4 hover:shadow-md dark:hover:shadow-gray-900/20 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <!-- Date Badge -->
                        <div class="flex-shrink-0">
                            <div
                                class="flex items-center justify-center w-16 h-16 rounded-xl text-white text-xl font-bold shadow-lg
                                        {{ $event['priority'] === 'high' ? 'bg-gradient-to-br from-red-500 to-red-600' : ($event['priority'] === 'medium' ? 'bg-gradient-to-br from-yellow-500 to-amber-600' : 'bg-gradient-to-br from-blue-500 to-blue-600') }}">
                                {{ Carbon\Carbon::parse($event['date'])->day }}
                            </div>
                        </div>

                        <!-- Event Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200">
                                        {{ $event['title'] }}
                                    </h3>
                                    <p class="text-gray-600 dark:text-gray-300 mt-1 text-sm leading-relaxed">
                                        {{ $event['description'] }}
                                    </p>

                                    <!-- Priority Badge -->
                                    <div class="mt-3 flex items-center gap-2">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                   {{ $event['priority'] === 'high' ? 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300' : ($event['priority'] === 'medium' ? 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300' : 'bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300') }}">
                                            {{ $event['priority'] === 'high' ? 'Prioritas Tinggi' : ($event['priority']
                                            === 'medium' ? 'Prioritas Sedang' : 'Prioritas Normal') }}
                                        </span>

                                        @if($event['type'] === 'payment')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                            </svg>
                                            Pembayaran
                                        </span>
                                        @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/50 text-purple-800 dark:text-purple-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Pelaporan
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <div class="flex-shrink-0 ml-4">
                                    @if(isset($event['actionLink']))
                                    <a href="{{ $event['actionLink'] }}"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 transform hover:scale-105
                                               {{ $event['type'] === 'payment' ? 'text-white bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 shadow-red-500/25' : 'text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 shadow-blue-500/25' }} shadow-lg">
                                        {{ $event['actionText'] }}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                    @endif
                                </div>
                            </div>

                            <!-- Pending Clients Info -->
                            @if(isset($event['date']))
                            @php
                            $eventDate = Carbon\Carbon::parse($event['date']);
                            $clientCount = $this->getPendingClientsCount($eventDate);
                            @endphp

                            @if($clientCount > 0)
                            <div
                                class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-amber-600 dark:text-amber-400 mr-2" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                        </svg>
                                        <span class="text-sm font-medium text-amber-800 dark:text-amber-300">
                                            {{ $clientCount }} klien belum melakukan {{ strtolower($event['title']) }}
                                        </span>
                                    </div>
                                    <button wire:click="selectDate('{{ $event['date'] }}')"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/50 hover:bg-amber-200 dark:hover:bg-amber-900/70 rounded-lg transition-colors duration-200">
                                        Lihat Detail
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            @endif
                            @endif
                        </div>
                    </div>

                    <!-- Hover Effect Overlay -->
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-purple-500/5 dark:from-blue-400/5 dark:to-purple-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-lg">
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div
                class="text-center py-12 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Tidak ada jadwal pajak</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tidak ada jadwal pajak untuk bulan ini.</p>
            </div>
            @endif
        </div>
    </div>
</div>