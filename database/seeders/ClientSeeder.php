<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectStep;
use App\Models\Task;
use App\Models\RequiredDocument;
use App\Models\SubmittedDocument;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        Client::factory()
            ->count(10)
            ->has(
                Project::factory()
                    ->count(2)
                    ->state(function (array $attributes, Client $client) {
                        return [
                            'client_id' => $client->id,
                        ];
                    })
                    ->afterCreating(function (Project $project) {
                        // Create 4 steps for each project
                        for ($i = 1; $i <= 4; $i++) {
                            $step = ProjectStep::factory()->create([
                                'project_id' => $project->id,
                                'order' => $i,
                                'name' => match($i) {
                                    1 => 'Client Registration',
                                    2 => 'Document Collection',
                                    3 => 'Shipping Planning',
                                    4 => 'Execution',
                                },
                            ]);

                            // Create 2-3 tasks for each step
                            Task::factory()
                                ->count(rand(2, 3))
                                ->create([
                                    'project_step_id' => $step->id,
                                ]);

                            // Create 1-2 required documents for each step
                            RequiredDocument::factory()
                                ->count(rand(1, 2))
                                ->has(
                                    SubmittedDocument::factory()
                                        ->count(rand(0, 1)) // Some documents might not be submitted yet
                                )
                                ->create([
                                    'project_step_id' => $step->id,
                                ]);
                        }
                    })
            )
            ->create();
    }
}