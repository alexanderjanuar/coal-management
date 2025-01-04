<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Progress>
 */
class ProgressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'status' => fake()->randomElement(['done', 'in progress', 'draft', 'canceled', 'delayed']),
            'start_time' => fake()->time(),
            'end_time' => fake()->time(),
        ];
    }
}
