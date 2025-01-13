<?php

namespace Database\Factories;

use App\Models\ProjectStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $tasks = [
        [
            'title' => 'Contract Review and Signing',
            'description' => 'Review and finalize the coal sales contract with all specifications and terms.'
        ],
        [
            'title' => 'Letter of Credit Processing',
            'description' => 'Coordinate with banks for LC opening and verification of terms.'
        ],
        [
            'title' => 'Quality Specification Review',
            'description' => 'Review and confirm coal quality specifications against contract requirements.'
        ],
        [
            'title' => 'Vessel Schedule Coordination',
            'description' => 'Coordinate vessel arrival schedule with all parties including agent and port authority.'
        ],
        [
            'title' => 'Draft Survey Arrangement',
            'description' => 'Arrange initial and final draft surveys with appointed surveyor.'
        ],
        [
            'title' => 'Loading Supervision',
            'description' => 'Supervise loading operations and coordinate with stevedores.'
        ],
        [
            'title' => 'Documentation Processing',
            'description' => 'Process and prepare all required shipping and export documents.'
        ],
        [
            'title' => 'Quality Monitoring',
            'description' => 'Monitor coal quality parameters during loading operations.'
        ]
    ];

    public function definition(): array
    {
        $task = $this->faker->randomElement($this->tasks);
        return [
            'project_step_id' => ProjectStep::factory(),
            'title' => $task['title'],
            'description' => $task['description'],
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed', 'blocked']),
            'requires_document' => $this->faker->boolean(),
        ];
    }
}
