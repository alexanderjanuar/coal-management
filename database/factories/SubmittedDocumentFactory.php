<?php

namespace Database\Factories;

use App\Models\RequiredDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubmittedDocument>
 */
class SubmittedDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'required_document_id' => RequiredDocument::factory(),
            'user_id' => User::factory(),

            'rejection_reason' => fake()->optional(0.3)->paragraph(),
        ];
    }
}
