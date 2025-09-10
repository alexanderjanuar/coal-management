<x-filament::modal id="tax-events-modal" width="2xl">
    <x-slot name="heading">
        <div class="flex items-center space-x-3">
            <div class="flex-shrink-0">
                <div class="p-3 bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/50 dark:to-orange-900/50 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
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
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ count($selectedEvents) }} Event
                </span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4 max-h-96 overflow-y-auto">
        @if(count($selectedEvents) > 0)
            @foreach($selectedEvents as $index => $event)
            <div class="group relative overflow-hidden rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 hover:shadow-lg transition-all duration-300">
                <!-- Priority Indicator Bar -->
                <div class="absolute top-0 left-0 w-full h-1 
                    {{ $event['priority'] === 'high' ? 'bg-gradient-to-r from-red-500 to-red-600' : 
                       ($event['priority'] === 'medium' ? 'bg-gradient-to-r from-yellow-500 to-orange-500' : 
                        'bg-gradient-to-r from-blue-500 to-indigo-500') }}">
                </div>
                
                <div class="p-5">
                    <div class="flex items-start space-x-4">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <div class="p-3 rounded-xl 
                                {{ $event['priority'] === 'high' ? 'bg-red-50 dark:bg-red-900/20 ring-1 ring-red-200 dark:ring-red-800' : 
                                   ($event['priority'] === 'medium' ? 'bg-yellow-50 dark:bg-yellow-900/20 ring-1 ring-yellow-200 dark:ring-yellow-800' : 
                                    'bg-blue-50 dark:bg-blue-900/20 ring-1 ring-blue-200 dark:ring-blue-800') }}">
                                @if($event['type'] === 'payment')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 
                                        {{ $event['priority'] === 'high' ? 'text-red-600 dark:text-red-400' : 
                                           ($event['priority'] === 'medium' ? 'text-yellow-600 dark:text-yellow-400' : 
                                            'text-blue-600 dark:text-blue-400') }}" 
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 
                                        {{ $event['priority'] === 'high' ? 'text-red-600 dark:text-red-400' : 
                                           ($event['priority'] === 'medium' ? 'text-yellow-600 dark:text-yellow-400' : 
                                            'text-blue-600 dark:text-blue-400') }}" 
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                                        {{ $event['title'] }}
                                    </h4>
                                    <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
                                        {{ $event['description'] }}
                                    </p>
                                </div>
                                
                                <!-- Priority Badge -->
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium ml-3
                                    {{ $event['priority'] === 'high' ? 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300' : 
                                       ($event['priority'] === 'medium' ? 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300' : 
                                        'bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300') }}">
                                    {{ $event['priority'] === 'high' ? 'Tinggi' : ($event['priority'] === 'medium' ? 'Sedang' : 'Rendah') }}
                                </span>
                            </div>
                            
                            <!-- Action Button -->
                            @if(isset($event['actionLink']) && $event['actionLink'])
                            <div class="mt-4">
                                <a href="{{ $event['actionLink'] }}" 
                                   class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2
                                          {{ $event['type'] === 'payment' ? 
                                             'text-white bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 focus:ring-red-500 shadow-lg shadow-red-500/25' : 
                                             'text-white bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 focus:ring-blue-500 shadow-lg shadow-blue-500/25' }}">
                                    <span>{{ $event['actionText'] }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <div class="text-center py-12">
                <div class="mx-auto h-24 w-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Tidak Ada Jadwal</h3>
                <p class="text-gray-500 dark:text-gray-400">Tidak ada jadwal pajak untuk tanggal ini.</p>
            </div>
        @endif
    </div>
</x-filament::modal>