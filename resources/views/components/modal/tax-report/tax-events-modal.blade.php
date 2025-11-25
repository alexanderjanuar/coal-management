<x-filament::modal id="tax-events-modal" width="3xl">
    <x-slot name="heading">
        <div class="flex items-center space-x-3">
            <div class="flex-shrink-0">
                <div
                    class="p-3 bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/50 dark:to-orange-900/50 rounded-xl shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-600 dark:text-amber-400"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                    Detail Jadwal Pajak
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    @if($selectedDate)
                    {{ Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}
                    @endif
                </p>
            </div>
            <div class="flex-shrink-0">
                <span
                    class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ count($selectedEvents) }} {{ count($selectedEvents) === 1 ? 'Jadwal' : 'Jadwal' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2">
        @if(count($selectedEvents) > 0)
        @foreach($selectedEvents as $index => $event)
        <div class="group relative overflow-hidden rounded-xl border-2 
                {{ $event['priority'] === 'high' ? 'border-red-200 dark:border-red-800' : 
                   ($event['priority'] === 'medium' ? 'border-yellow-200 dark:border-yellow-800' : 
                    'border-blue-200 dark:border-blue-800') }}
                bg-white dark:bg-gray-800 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">

            <!-- Animated Priority Indicator Bar -->
            <div class="absolute top-0 left-0 w-full h-1.5 overflow-hidden">
                <div class="absolute inset-0 
                        {{ $event['priority'] === 'high' ? 'bg-gradient-to-r from-red-500 via-red-600 to-red-500' : 
                           ($event['priority'] === 'medium' ? 'bg-gradient-to-r from-yellow-500 via-orange-500 to-yellow-500' : 
                            'bg-gradient-to-r from-blue-500 via-indigo-600 to-blue-500') }}
                        animate-pulse">
                </div>
            </div>

            <div class="p-6">
                <div class="flex items-start space-x-4">
                    <!-- Enhanced Icon with Glow Effect -->
                    <div class="flex-shrink-0">
                        <div
                            class="relative p-3.5 rounded-xl transition-all duration-300
                                {{ $event['priority'] === 'high' ? 'bg-red-50 dark:bg-red-900/20 ring-2 ring-red-200 dark:ring-red-800 group-hover:ring-red-300 dark:group-hover:ring-red-700' : 
                                   ($event['priority'] === 'medium' ? 'bg-yellow-50 dark:bg-yellow-900/20 ring-2 ring-yellow-200 dark:ring-yellow-800 group-hover:ring-yellow-300 dark:group-hover:ring-yellow-700' : 
                                    'bg-blue-50 dark:bg-blue-900/20 ring-2 ring-blue-200 dark:ring-blue-800 group-hover:ring-blue-300 dark:group-hover:ring-blue-700') }}">

                            <!-- Glow Effect -->
                            <div class="absolute inset-0 rounded-xl opacity-50 blur-xl 
                                    {{ $event['priority'] === 'high' ? 'bg-red-400' : 
                                       ($event['priority'] === 'medium' ? 'bg-yellow-400' : 'bg-blue-400') }}
                                    group-hover:opacity-70 transition-opacity">
                            </div>

                            @if($event['type'] === 'payment')
                            <svg xmlns="http://www.w3.org/2000/svg" class="relative h-6 w-6 
                                        {{ $event['priority'] === 'high' ? 'text-red-600 dark:text-red-400' : 
                                           ($event['priority'] === 'medium' ? 'text-yellow-600 dark:text-yellow-400' : 
                                            'text-blue-600 dark:text-blue-400') }}" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                            @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="relative h-6 w-6 
                                        {{ $event['priority'] === 'high' ? 'text-red-600 dark:text-red-400' : 
                                           ($event['priority'] === 'medium' ? 'text-yellow-600 dark:text-yellow-400' : 
                                            'text-blue-600 dark:text-blue-400') }}" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            @endif
                        </div>
                    </div>

                    <!-- Enhanced Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h4 class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ $event['title'] }}
                                    </h4>

                                    <!-- Enhanced Priority Badge -->
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide
                                            {{ $event['priority'] === 'high' ? 'bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg shadow-red-500/30' : 
                                               ($event['priority'] === 'medium' ? 'bg-gradient-to-r from-yellow-500 to-orange-500 text-white shadow-lg shadow-yellow-500/30' : 
                                                'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-500/30') }}">
                                        @if($event['priority'] === 'high')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        @endif
                                        {{ $event['priority'] === 'high' ? 'URGENT' : ($event['priority'] === 'medium' ?
                                        'PENTING' : 'NORMAL') }}
                                    </span>
                                </div>

                                <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed mb-4">
                                    {{ $event['description'] }}
                                </p>

                                <!-- Event Type Badge -->
                                <div
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium
                                        {{ $event['type'] === 'payment' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800' : 
                                           'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800' }}">
                                    @if($event['type'] === 'payment')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path
                                            d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    @endif
                                    {{ $event['type'] === 'payment' ? 'Pembayaran' : 'Pelaporan' }}
                                </div>
                            </div>
                        </div>

                        <!-- Enhanced Action Button -->
                        @if(isset($event['actionLink']) && $event['actionLink'])
                        <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <a href="{{ $event['actionLink'] }}" class="inline-flex items-center justify-center w-full px-5 py-3 text-sm font-bold rounded-xl transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus:ring-4 focus:ring-offset-2 shadow-lg
                                          {{ $event['type'] === 'payment' ? 
                                             'text-white bg-gradient-to-r from-red-500 via-red-600 to-red-500 hover:from-red-600 hover:via-red-700 hover:to-red-600 focus:ring-red-500/50 shadow-red-500/30' : 
                                             'text-white bg-gradient-to-r from-blue-500 via-indigo-600 to-blue-500 hover:from-blue-600 hover:via-indigo-700 hover:to-blue-600 focus:ring-blue-500/50 shadow-blue-500/30' }}
                                          group">
                                <span>{{ $event['actionText'] }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 ml-2 transition-transform group-hover:translate-x-1"
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

            <!-- Decorative Corner Element -->
            <div class="absolute -bottom-6 -right-6 w-24 h-24 rounded-full opacity-10
                    {{ $event['priority'] === 'high' ? 'bg-red-500' : 
                       ($event['priority'] === 'medium' ? 'bg-yellow-500' : 'bg-blue-500') }}">
            </div>
        </div>
        @endforeach
        @else
        <!-- Enhanced Empty State -->
        <div class="text-center py-16">
            <div class="relative mx-auto h-32 w-32 mb-6">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded-full animate-pulse">
                </div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 dark:text-gray-500"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Tidak Ada Jadwal</h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto">Tidak ada jadwal pajak yang terdaftar untuk
                tanggal yang dipilih.</p>
        </div>
        @endif
    </div>

    @if(count($selectedEvents) > 0)
    <x-slot name="footerActions">
        <div class="w-full">
            <x-filament::button color="gray" outlined wire:click="$dispatch('close-modal', { id: 'tax-events-modal' })"
                class="w-full">
                Tutup
            </x-filament::button>
        </div>
    </x-slot>
    @endif
</x-filament::modal>