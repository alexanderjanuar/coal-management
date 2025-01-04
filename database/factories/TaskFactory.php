<?php

namespace Database\Factories;

use App\Models\Progress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{

    public function definition(): array
    {
        return [
            'progress_id' => Progress::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['done', 'in progress', 'draft', 'canceled', 'delayed']),
            'start_time' => fake()->time(),
            'end_time' => fake()->time(),
        ];
    }
}