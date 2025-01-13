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
    protected $documents = [
        [
            'name' => 'Sales and Purchase Contract',
            'description' => 'Original signed contract between buyer and seller.'
        ],
        [
            'name' => 'Letter of Credit',
            'description' => 'Original LC document from buyer\'s bank.'
        ],
        [
            'name' => 'Certificate of Origin',
            'description' => 'Official certificate showing origin of coal cargo.'
        ],
        [
            'name' => 'Bill of Lading',
            'description' => 'Original bill of lading for the shipment.'
        ],
        [
            'name' => 'Draft Survey Report',
            'description' => 'Official survey report showing cargo quantity.'
        ],
        [
            'name' => 'Quality Analysis Certificate',
            'description' => 'Laboratory analysis report of coal quality parameters.'
        ],
        [
            'name' => 'Export License',
            'description' => 'Government-issued export permit.'
        ],
        [
            'name' => 'Vessel Nomination',
            'description' => 'Official vessel nomination from buyer.'
        ],
        [
            'name' => 'Statement of Facts',
            'description' => 'Detailed record of loading operations and timelines.'
        ],
        [
            'name' => 'Insurance Certificate',
            'description' => 'Cargo insurance documentation.'
        ]
    ];

    public function definition(): array
    {
        $document = $this->faker->randomElement($this->documents);
        return [
            'project_step_id' => ProjectStep::factory(),
            'name' => $document['name'],
            'description' => $document['description'],
            'status' => $this->faker->randomElement(['pending_review', 'approved', 'rejected']),
            'is_required' => $this->faker->boolean(80),
        ];
    }
}
