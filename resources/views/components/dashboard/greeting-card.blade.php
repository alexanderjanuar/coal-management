{{-- Simplified Accordion Welcome Card --}}
<div class="mb-4" x-data="{ showDetails: false }">
    <div
        class="relative overflow-hidden bg-white dark:from-gray-800 dark:to-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">

        <div class="relative p-6">
            {{-- Simplified Greeting Section --}}
            <div class="mb-4">
                <div class="flex items-center gap-6">
                    {{-- Enhanced Avatar Section --}}
                    <div class="flex-shrink-0">
                        <div class="relative">
                            <div
                                class="w-16 h-16 rounded-full overflow-hidden border-2 border-blue-200 dark:border-blue-600 shadow-md">
                                @if(auth()->user()->avatar_url || auth()->user()->avatar_path)
                                <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}"
                                    class="w-full h-full object-cover">
                                @else
                                <div
                                    class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-500 to-indigo-600">
                                    <span class="text-xl font-bold text-white">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </span>
                                </div>
                                @endif
                            </div>

                            {{-- Online Status --}}
                            <div
                                class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full">
                            </div>
                        </div>
                    </div>

                    {{-- Greeting Content --}}
                    <div class="flex-1">
                        @php
                        $hour = now()->format('H');
                        $greeting = match(true) {
                        $hour < 12=> 'Selamat Pagi',
                            $hour < 15=> 'Selamat Siang',
                                $hour < 18=> 'Selamat Sore',
                                    default => 'Selamat Malam'
                                    };
                                    @endphp

                                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                                        {{ $greeting }}, {{ explode(' ', auth()->user()->name)[0] }}!
                                    </h1>

                                    <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-300">
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <span>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                                        </div>
                                        <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <span>{{ now()->format('H:i') }} WIB</span>
                                        </div>
                                    </div>
                    </div>
                </div>

                {{-- Quick Stats Cards --}}
                @php
                $dashboardStats = $this->getDashboardStats();

                // Document message
                $documentMessage = match(true) {
                    $dashboardStats['submitted_documents'] == 0 => "Belum ada dokumen yang disubmit",
                    $dashboardStats['approved_documents'] == $dashboardStats['submitted_documents'] => "Semua dokumen sudah disetujui - excellent!",
                    $dashboardStats['rejected_documents'] > 0 => $dashboardStats['approved_documents'] . " disetujui, " . $dashboardStats['rejected_documents'] . " ditolak, " . $dashboardStats['pending_documents'] . " pending",
                    default => $dashboardStats['approved_documents'] . " disetujui dari " . $dashboardStats['submitted_documents'] . " dokumen yang disubmit"
                };

                $taskMessage = match(true) {
                    $dashboardStats['today_tasks'] == 0 => "Tidak ada task aktif dalam periode ini",
                    $dashboardStats['completed_tasks_today'] == $dashboardStats['today_tasks'] => "Semua task dalam periode ini sudah selesai - kerja yang bagus!",
                    $dashboardStats['incomplete_tasks_today'] == 0 => "Tidak ada task yang belum selesai",
                    default => $dashboardStats['completed_tasks_today'] . " selesai, " . $dashboardStats['incomplete_tasks_today'] . " belum selesai"
                };
                @endphp

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Document Card (mengganti Proyek Card) --}}
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-2">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            @if($dashboardStats['pending_documents'] > 0)
                                <div class="px-2 py-1 bg-orange-100 dark:bg-orange-900 text-orange-600 dark:text-orange-400 text-xs font-medium rounded">
                                    {{ $dashboardStats['pending_documents'] }} pending
                                </div>
                            @elseif($dashboardStats['rejected_documents'] > 0)
                                <div class="px-2 py-1 bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400 text-xs font-medium rounded">
                                    {{ $dashboardStats['rejected_documents'] }} ditolak
                                </div>
                            @endif
                        </div>
                        <div class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $dashboardStats['approved_documents'] }}/{{ $dashboardStats['submitted_documents'] }}
                        </div>
                        <div class="text-sm font-medium text-blue-600 dark:text-blue-400 mb-1">Dokumen Saya</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">{{ $documentMessage }}</div>
                        
                        @if($dashboardStats['submitted_documents'] > 0)
                            <div class="mt-2">
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1">
                                    <div class="bg-blue-500 h-1 rounded-full" 
                                        style="width: {{ ($dashboardStats['approved_documents'] / $dashboardStats['submitted_documents']) * 100 }}%">
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Task Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-2">
                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            @if($dashboardStats['incomplete_tasks_today'] > 0)
                                <div class="px-2 py-1 bg-orange-100 dark:bg-orange-900 text-orange-600 dark:text-orange-400 text-xs font-medium rounded">
                                    {{ $dashboardStats['incomplete_tasks_today'] }} belum selesai
                                </div>
                            @endif
                        </div>
                        <div class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $dashboardStats['completed_tasks_today'] }}/{{ $dashboardStats['today_tasks'] }}
                        </div>
                        <div class="text-sm font-medium text-green-600 dark:text-green-400 mb-1">Task Aktif</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">{{ $taskMessage }}</div>

                        @if($dashboardStats['today_tasks'] > 0)
                            <div class="mt-2">
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1">
                                    <div class="bg-green-500 h-1 rounded-full" 
                                        style="width: {{ ($dashboardStats['completed_tasks_today'] / $dashboardStats['today_tasks']) * 100 }}%">
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Completed Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-2">
                            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $dashboardStats['completed_projects'] }}</div>
                        <div class="text-sm font-medium text-purple-600 dark:text-purple-400 mb-1">Proyek Selesai</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            @if($dashboardStats['completed_projects'] >= 10)
                                {{ $dashboardStats['completed_projects'] }} proyek selesai - pencapaian yang membanggakan!
                            @elseif($dashboardStats['completed_projects'] > 0)
                                {{ $dashboardStats['completed_projects'] }} proyek berhasil diselesaikan
                            @else
                                Setiap pencapaian dimulai dari langkah pertama
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>