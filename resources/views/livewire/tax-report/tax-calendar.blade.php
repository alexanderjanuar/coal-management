<div class="w-full border border-gray-200 bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="p-4 md:p-6">
        <!-- Calendar Section -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center">
                    <button wire:click="goToPreviousMonth"
                        class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    <h2 class="text-xl font-medium px-4">{{ $currentDate->translatedFormat('F Y') }}</h2>
                    <button wire:click="goToNextMonth"
                        class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <h2 class="text-lg font-bold text-gray-900">Kalender Pajak</h2>
            </div>

            <div class="grid grid-cols-7 gap-1 border-b border-gray-200 pb-2 mb-2">
                @foreach(['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $day)
                <div class="text-center text-gray-500 font-medium">
                    {{ $day }}
                </div>
                @endforeach
            </div>

            <div class="grid grid-cols-7 gap-1">
                @foreach($calendarDays as $day)
                <div wire:key="day-{{ $day['date'] }}" class="
                                relative flex flex-col items-center h-16 w-full text-lg font-medium 
                                cursor-pointer transition-colors rounded-md
                                {{ !$day['isCurrentMonth'] ? 'text-gray-300' : '' }}
                                {{ $day['isToday'] ? 'border border-blue-400' : '' }}
                                {{ $selectedDate === $day['date'] ? 'bg-amber-50' : 'hover:bg-gray-50' }}
                            " wire:click="selectDate('{{ $day['date'] }}')">
                    <span class="mt-1">{{ $day['day'] }}</span>
                    
                    <!-- Pending Clients Count -->
                    @if($day['pendingClientsCount'] > 0)
                    <span class="absolute bottom-3 text-xs font-semibold text-amber-600">
                        {{ $day['pendingClientsCount'] }} Klien
                    </span>
                    @endif
                    
                    @if($day['hasEvent'])
                    <span
                        class="absolute bottom-2 left-1/2 transform -translate-x-1/2 h-1 w-10 bg-yellow-500 rounded-full"></span>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- Tax Event Modal -->
            @if($isModalOpen)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
                aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-black bg-opacity-80 transition-opacity" aria-hidden="true"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-xl font-semibold text-gray-900" id="modal-title">
                                        Detail Jadwal Pajak
                                    </h3>
                                    <p class="text-gray-500 mt-1">
                                        {{ Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}
                                    </p>

                                    <div class="mt-4 space-y-4">
                                        @if(count($selectedEvents) > 0)
                                        @foreach($selectedEvents as $event)
                                        <div class="border-b border-gray-100 pb-4 last:border-0">
                                            <h3 class="font-medium text-gray-900">{{ $event['title'] }}</h3>
                                            <p class="text-gray-600 mt-1">{{ $event['description'] }}</p>

                                            @if(isset($event['actionLink']))
                                            <div class="mt-3">
                                                <a href="{{ $event['actionLink'] }}"
                                                    class="text-blue-600 hover:text-blue-800 flex items-center gap-1 text-sm font-medium">
                                                    {{ $event['actionText'] }}
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </a>
                                            </div>
                                            @endif
                                        </div>
                                        @endforeach
                                        @else
                                        <p class="text-gray-500">Tidak ada jadwal pajak untuk tanggal ini.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" wire:click="closeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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
                    <div class="fixed inset-0 bg-black bg-opacity-80 transition-opacity" aria-hidden="true"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <div class="flex justify-between items-center mb-4">
                                        <div>
                                            <h3 class="text-xl font-semibold text-gray-900" id="modal-title">
                                                Klien yang Belum {{ $pendingClients['reportType'] }}
                                            </h3>
                                            <p class="text-gray-500 mt-1">
                                                {{ $pendingClients['date'] }}
                                            </p>
                                        </div>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                            {{ count($pendingClients['clients']) }} Klien
                                        </span>
                                    </div>

                                    <div class="mt-4 overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Nama Klien
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        NPWP
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Status
                                                    </th>
                                                    @if(strpos($pendingClients['reportType'], 'Setor PPh dan PPN') !== false)
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Jumlah Tagihan
                                                    </th>
                                                    @elseif(strpos($pendingClients['reportType'], 'PPh 21') !== false)
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Jumlah Karyawan
                                                    </th>
                                                    @elseif(strpos($pendingClients['reportType'], 'PPN') !== false)
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Jumlah Transaksi
                                                    </th>
                                                    @endif
                                                    <th scope="col"
                                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Aksi
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($pendingClients['clients'] as $client)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $client['name'] }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-500">{{ $client['NPWP'] }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span
                                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                            {{ $client['status'] }}
                                                        </span>
                                                    </td>
                                                    @if(strpos($pendingClients['reportType'], 'Setor PPh dan PPN') !== false)
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        Rp {{ number_format($client['dueAmount'], 0, ',', '.') }}
                                                    </td>
                                                    @elseif(strpos($pendingClients['reportType'], 'PPh 21') !== false)
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $client['employees'] }} orang
                                                    </td>
                                                    @elseif(strpos($pendingClients['reportType'], 'PPN') !== false)
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $client['transaksiCount'] }} transaksi
                                                    </td>
                                                    @endif
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                        <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">Detail</a>
                                                        <a href="#" class="text-green-600 hover:text-green-900">
                                                            @if(strpos($pendingClients['reportType'], 'Setor') !== false)
                                                            Bayar
                                                            @else
                                                            Lapor
                                                            @endif
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button"
                                class="ml-3 inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Kirim Pengingat
                            </button>
                            <button type="button" wire:click="closeClientModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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
            <h2 class="text-xl font-semibold mb-4">Jadwal pajak bulan ini</h2>
            @php
            $currentMonthEvents = $this->getTaxSchedule();
            @endphp

            @if(count($currentMonthEvents) > 0)
            <div class="space-y-4">
                @foreach($currentMonthEvents as $event)
                <div class="flex gap-4">
                    <div
                        class="flex items-center justify-center w-16 h-16 bg-yellow-500 text-white text-2xl font-bold rounded-md">
                        {{ Carbon\Carbon::parse($event['date'])->day }}
                    </div>
                    <div class="flex flex-col justify-between py-1">
                        <p class="text-gray-800">{{ $event['description'] }}</p>
                        
                        @if(isset($event['date']))
                            @php
                                $eventDate = Carbon\Carbon::parse($event['date']);
                                $clientCount = $this->getPendingClientsCount($eventDate);
                            @endphp
                            
                            @if($clientCount > 0)
                                <div class="text-sm text-amber-600 font-medium mt-1">
                                    {{ $clientCount }} klien belum melaporkan
                                </div>
                            @endif
                        @endif
                        
                        @if(isset($event['actionLink']))
                        <a href="#"
                            class="text-blue-600 hover:text-blue-800 flex items-center gap-1 mt-1">
                            Lihat Klien
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500">Tidak ada jadwal pajak untuk bulan ini.</p>
            @endif
        </div>
    </div>
</div>