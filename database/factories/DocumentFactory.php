<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'document_path' => 'documents/' . fake()->uuid() . '.pdf',
            'task_id' => Task::factory(),
        ];
    }
}
