<div>
    <div class="overflow-x-auto pb-2 ">
        <div class="flex space-x-4 ">
            {{-- Today's Weather --}}
            <div class="bg-blue-400 rounded-lg p-4 w-64 text-white">
                <div class="text-xl font-bold text-white">Today</div>
                <div class="text-xs mb-2 text-white">{{ $currentWeather['date'] }}</div>
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-5xl font-bold text-white">{{ $currentWeather['temp'] }}°</div>
                        <div class="text-sm">{{ $currentWeather['condition'] }}</div>
                    </div>
                    <div class="mt-2">
                        <img 
                            src="{{ asset('images/' . $currentWeather['icon']) }}" 
                            alt="Weather icon" 
                            class="w-12 h-12"
                        />
                    </div>
                </div>
            </div>

            {{-- Forecast --}}
            @foreach($forecast as $day)
                <div class="bg-gray-100 rounded-lg p-4 w-24">
                    <div class="text-sm text-center font-medium mb-2">{{ $day['day'] }}</div>
                    <div class="flex justify-center mb-2">
                        <img 
                            src="{{ asset('images/' . $day['icon']) }}" 
                            alt="Weather icon" 
                            class="w-8 h-8"
                        />
                    </div>
                    <div class="text-center">
                        <span class="text-sm font-medium">{{ $day['temp'] }}°</span>
                        <span class="text-xs text-gray-500 ml-1">{{ $day['temp_min'] }}°</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>