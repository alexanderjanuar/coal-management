<?php

namespace Database\Factories;

use App\Models\ProjectStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RequiredDocument>
 */
class RequiredDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_step_id' => ProjectStep::factory(),
            'name' => fake()->sentence(2),
            'description' => fake()->paragraph(),
            'is_required' => fake()->boolean(80), // 80% chance of being required
        ];
    }
}
