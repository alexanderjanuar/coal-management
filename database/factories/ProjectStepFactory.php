<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectStep>
 */
class ProjectStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $steps = [
        [
            'name' => 'Commercial Documentation',
            'description' => 'Preparation and management of all commercial and contractual documents including sales contract, LC, and export licenses.'
        ],
        [
            'name' => 'Quality Control Setup',
            'description' => 'Establishment of quality control procedures, sampling protocols, and laboratory coordination for coal analysis.'
        ],
        [
            'name' => 'Vessel Nomination & Planning',
            'description' => 'Management of vessel nomination process, schedule coordination, and berthing arrangements.'
        ],
        [
            'name' => 'Loading Operations',
            'description' => 'Supervision of loading operations including draft surveys, quality sampling, and cargo documentation.'
        ],
        [
            'name' => 'Shipping Documentation',
            'description' => 'Preparation and processing of all shipping documents including B/L, certificates, and export documentation.'
        ]
    ];

    public function definition(): array
    {
        // Remove unique() and let the order determine the step
        return [
            'project_id' => Project::factory(),
            'name' => $this->steps[$this->faker->numberBetween(0, count($this->steps) - 1)]['name'],
            'description' => $this->steps[$this->faker->numberBetween(0, count($this->steps) - 1)]['description'],
            'order' => $this->faker->numberBetween(1, 5),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'waiting_for_documents', 'completed']),
        ];
    }
}
