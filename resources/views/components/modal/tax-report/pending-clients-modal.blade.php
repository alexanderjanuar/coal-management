<x-filament::modal id="pending-clients-modal" width="7xl">
    <x-slot name="heading">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div
                        class="relative p-3 bg-gradient-to-br from-red-100 to-rose-100 dark:from-red-900/50 dark:to-rose-900/50 rounded-xl shadow-sm">
                        <!-- Glow Effect -->
                        <div class="absolute inset-0 bg-red-500 rounded-xl opacity-20 blur-xl animate-pulse"></div>

                        <svg xmlns="http://www.w3.org/2000/svg" class="relative h-6 w-6 text-red-600 dark:text-red-400"
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
                        <div
                            class="flex items-center space-x-2 px-3 py-1.5 rounded-full bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                            <div class="h-2 w-2 bg-red-500 rounded-full animate-pulse shadow-lg shadow-red-500/50">
                            </div>
                            <span class="text-xs font-bold text-red-600 dark:text-red-400 uppercase tracking-wide">Perlu
                                Tindakan</span>
                        </div>
                        <span
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-bold bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg shadow-red-500/30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            {{ count($pendingClients['clients'] ?? []) }}
                        </span>
                    </div>
                    <div class="flex items-center space-x-2 mt-2">
                        <span
                            class="inline-flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 px-2.5 py-1 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ $pendingClients['reportType'] ?? '' }}
                        </span>
                        <span class="text-gray-300 dark:text-gray-600">•</span>
                        <span
                            class="inline-flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 px-2.5 py-1 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ $pendingClients['date'] ?? '' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    @if(isset($pendingClients['clients']) && count($pendingClients['clients']) > 0)
    <!-- Enhanced Summary Cards with Animations -->
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

        <!-- Total Klien Card -->
        <div
            class="group relative overflow-hidden bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-5 border-2 border-blue-200 dark:border-blue-800 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <!-- Animated Background -->
            <div
                class="absolute inset-0 bg-gradient-to-br from-blue-400/10 to-indigo-400/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            </div>

            <div class="relative flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div
                        class="p-3 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-1">Total
                            Klien</p>
                        <p class="text-3xl font-black text-blue-700 dark:text-blue-300 tabular-nums">{{ $totalClients }}
                        </p>
                    </div>
                </div>
                <div class="text-blue-200 dark:text-blue-800/50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                </div>
            </div>
        </div>

        @if($totalAmount > 0)
        <!-- Total Amount Card -->
        <div
            class="group relative overflow-hidden bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-5 border-2 border-green-200 dark:border-green-800 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div
                class="absolute inset-0 bg-gradient-to-br from-green-400/10 to-emerald-400/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            </div>

            <div class="relative flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div
                        class="p-3 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg shadow-green-500/30 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-green-600 dark:text-green-400 uppercase tracking-wide mb-1">
                            Total Peredaran</p>
                        <p class="text-xl font-black text-green-700 dark:text-green-300 tabular-nums">Rp {{
                            number_format($totalAmount, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="text-green-200 dark:text-green-800/50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Average Amount Card -->
        <div
            class="group relative overflow-hidden bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/20 rounded-xl p-5 border-2 border-amber-200 dark:border-amber-800 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div
                class="absolute inset-0 bg-gradient-to-br from-amber-400/10 to-yellow-400/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            </div>

            <div class="relative flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div
                        class="p-3 bg-gradient-to-br from-amber-500 to-yellow-600 rounded-xl shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wide mb-1">
                            Rata-rata</p>
                        <p class="text-xl font-black text-amber-700 dark:text-amber-300 tabular-nums">Rp {{
                            number_format($avgAmount, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="text-amber-200 dark:text-amber-800/50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z" />
                    </svg>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Enhanced Clients Table -->
    <div
        class="overflow-hidden rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800">
                    <tr>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            <div class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span>Klien</span>
                            </div>
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            <div class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                </svg>
                                <span>NPWP</span>
                            </div>
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            <div class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Status</span>
                            </div>
                        </th>
                        @if(isset($pendingClients['reportType']) && strpos($pendingClients['reportType'], 'Setor PPh dan
                        PPN') !== false)
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            <div class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                                <span>Peredaran Bruto</span>
                            </div>
                        </th>
                        @elseif(isset($pendingClients['reportType']) && strpos($pendingClients['reportType'], 'PPh 21')
                        !== false)
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            <div class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>Karyawan</span>
                            </div>
                        </th>
                        @elseif(isset($pendingClients['reportType']) && strpos($pendingClients['reportType'], 'PPN') !==
                        false)
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            <div class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span>Transaksi</span>
                            </div>
                        </th>
                        @endif
                        <th scope="col"
                            class="px-6 py-4 text-right text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            <div class="flex items-center justify-end space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>Aksi</span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                    @foreach($pendingClients['clients'] as $index => $client)
                    <tr
                        class="hover:bg-gradient-to-r hover:from-gray-50 hover:to-transparent dark:hover:from-gray-700/30 dark:hover:to-transparent transition-all duration-200 group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="relative flex-shrink-0 h-12 w-12">
                                    @if(isset($client['logo']) && $client['logo'])
                                    <img class="h-12 w-12 rounded-xl object-cover ring-2 ring-white dark:ring-gray-800 shadow-md group-hover:ring-4 group-hover:ring-indigo-200 dark:group-hover:ring-indigo-800 transition-all"
                                        src="{{ asset('storage/' . $client['logo']) }}" alt="{{ $client['name'] }}"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/50 dark:to-purple-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-black text-base ring-2 ring-white dark:ring-gray-800 shadow-md group-hover:ring-4 group-hover:ring-indigo-200 dark:group-hover:ring-indigo-800 transition-all"
                                        style="display: none;">
                                        {{ strtoupper(substr($client['name'], 0, 2)) }}
                                    </div>
                                    @else
                                    <div
                                        class="h-12 w-12 rounded-xl bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/50 dark:to-purple-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-black text-base ring-2 ring-white dark:ring-gray-800 shadow-md group-hover:ring-4 group-hover:ring-indigo-200 dark:group-hover:ring-indigo-800 transition-all">
                                        {{ strtoupper(substr($client['name'], 0, 2)) }}
                                    </div>
                                    @endif

                                    <!-- Hover Indicator -->
                                    <div
                                        class="absolute -top-1 -right-1 h-4 w-4 bg-indigo-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <a href="{{ $this->getClientUrl($client['id']) }}"
                                        class="text-sm font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer group-hover:underline decoration-2 underline-offset-2">
                                        {{ $client['name'] }}
                                    </a>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">ID: #{{ $client['id'] }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div
                                class="inline-flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-300 font-mono bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                </svg>
                                <span>{{ $client['NPWP'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-bold bg-gradient-to-r from-yellow-100 to-orange-100 dark:from-yellow-900/50 dark:to-orange-900/50 text-yellow-800 dark:text-yellow-300 border-2 border-yellow-200 dark:border-yellow-800 shadow-sm">
                                <div
                                    class="h-2 w-2 bg-yellow-500 rounded-full mr-2 animate-pulse shadow-lg shadow-yellow-500/50">
                                </div>
                                {{ $client['status'] }}
                            </span>
                        </td>
                        @if(isset($pendingClients['reportType']) && (strpos($pendingClients['reportType'], 'Setor PPh
                        dan PPN') !== false || strpos($pendingClients['reportType'], 'PPN') !== false))
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-black text-gray-900 dark:text-white tabular-nums">
                                Rp {{ number_format($client['dueAmount'] ?? 0, 0, ',', '.') }}
                            </div>
                            @if(($client['dueAmount'] ?? 0) > 1000000)
                            <div class="flex items-center mt-1 text-xs font-medium text-green-600 dark:text-green-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                                Nilai Besar
                            </div>
                            @endif
                        </td>
                        @elseif(isset($pendingClients['reportType']) && strpos($pendingClients['reportType'], 'PPh 21')
                        !== false)
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div
                                class="flex items-center space-x-2 bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded-lg border border-blue-200 dark:border-blue-800">
                                <span class="text-sm font-black text-gray-900 dark:text-white tabular-nums">{{
                                    $client['employees'] ?? 0 }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">orang</span>
                            </div>
                        </td>
                        @elseif(isset($pendingClients['reportType']) && strpos($pendingClients['reportType'], 'Lapor SPT
                        Masa PPN') !== false)
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div
                                class="flex items-center space-x-2 bg-purple-50 dark:bg-purple-900/20 px-3 py-2 rounded-lg border border-purple-200 dark:border-purple-800">
                                <span class="text-sm font-black text-gray-900 dark:text-white tabular-nums">{{
                                    $client['transaksiCount'] ?? 0 }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">transaksi</span>
                            </div>
                        </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <x-filament::button size="sm" color="gray"
                                href="{{ $this->getTaxReportUrl($client['tax_report_id']) }}" tag="a"
                                icon="heroicon-o-arrow-right-circle"
                                class="hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg group/button">
                                <span class="group-hover/button:underline">Lihat Detail</span>
                            </x-filament::button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <!-- Enhanced Empty State -->
    <div class="text-center py-16">
        <div class="relative mx-auto h-32 w-32 mb-6">
            <div
                class="absolute inset-0 bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/20 dark:to-emerald-900/20 rounded-full animate-pulse">
            </div>
            <div class="absolute inset-0 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-green-500 dark:text-green-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Semua Klien Sudah Melaporkan</h3>
        <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">Tidak ada klien yang tertunggak untuk periode ini.
            Semua kewajiban pajak telah terpenuhi.</p>
        <div class="mt-6">
            <span
                class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                100% Kepatuhan
            </span>
        </div>
    </div>
    @endif

    <x-slot name="footerActions">
        <div class="flex items-center justify-between w-full">
            @if(isset($pendingClients['clients']) && count($pendingClients['clients']) > 0)
            <div class="flex items-center space-x-3">
                <x-filament::button color="success" icon="heroicon-o-paper-airplane" wire:click="sendMassReminder"
                    class="hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl group/send">
                    <span class="group-hover/send:translate-x-0.5 transition-transform inline-block">Kirim Pengingat
                        Massal</span>
                </x-filament::button>

                <x-filament::button color="gray" outlined icon="heroicon-o-document-arrow-down"
                    wire:click="exportPendingClients" class="hover:scale-105 transition-all duration-200">
                    Export Data
                </x-filament::button>
            </div>
            @endif

            <x-filament::button color="gray" outlined
                wire:click="$dispatch('close-modal', { id: 'pending-clients-modal' })">
                Tutup
            </x-filament::button>
        </div>
    </x-slot>
</x-filament::modal>