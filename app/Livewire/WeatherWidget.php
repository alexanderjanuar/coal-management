<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;


class WeatherWidget extends Component
{
    public $currentWeather;
    public $forecast;

    public function mount()
    {
        $this->currentWeather = [
            'date' => Carbon::now()->format('d F Y'),
            'temp' => 31,
            'condition' => 'Overcast Clouds',
            'icon' => 'cloudy.png'
        ];

        $this->forecast = [
            [
                'day' => 'Wed',
                'temp' => 32,
                'temp_min' => 20,
                'icon' => 'cloudy.png'
            ],
            [
                'day' => 'Thu',
                'temp' => 31,
                'temp_min' => 27,
                'icon' => 'rainy-day.png'
            ],
            [
                'day' => 'Fri',
                'temp' => 30,
                'temp_min' => 25,
                'icon' => 'sun.png'
            ]
        ];
    }

    public function render()
    {
        return view('livewire.weather-widget');
    }
}