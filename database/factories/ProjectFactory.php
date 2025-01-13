<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $projectTypes = [
        'Coal Barging Operation - Alpha Site',
        'Coal Barging Operation - Beta Site',
        'Coal Shipment Management - East Port',
        'Coal Loading Operation - South Terminal',
        'Coal Transport Management - West Port'
    ];

    protected $descriptions = [
        'Management of coal barging operations including quality control, vessel loading, and documentation for export shipment.',
        'Complete coal transport operation from stockpile to mother vessel, including quality assurance and shipping documentation.',
        'End-to-end coal shipment project including pre-loading preparation, quality monitoring, and export documentation.',
        'Comprehensive coal loading operation with full documentation and quality control procedures.',
        'Integrated coal transport project covering stockpile management through vessel loading and export.'
    ];

    public function definition(): array
    {
        $index = $this->faker->numberBetween(0, count($this->projectTypes) - 1);
        return [
            'client_id' => Client::factory(),
            'name' => $this->projectTypes[$index],
            'description' => $this->descriptions[$index],
            'status' => $this->faker->randomElement(['draft', 'in_progress', 'completed', 'on_hold', 'canceled']),
        ];
    }
}
