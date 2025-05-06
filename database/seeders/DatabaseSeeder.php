<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Progress;
use App\Models\Task;
use App\Models\Document;
use App\Models\Project;
use App\Models\ProjectStep;
use App\Models\RequiredDocument;
use App\Models\SubmittedDocument;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        // User::factory()->create([
        //     'name' => 'Admin User',
        //     'email' => 'admin@gmail.com',
        //     'password' => Hash::make('admin123'),
        // ]);

        // Create clients with complete project structure
        // Client::factory(5)->create()->each(function ($client) {
        //     Project::factory(2)->create([
        //         'client_id' => $client->id,
        //     ])->each(function ($project) {
        //         // Create 4 steps for each project
        //         for ($i = 1; $i <= 4; $i++) {
        //             $step = ProjectStep::factory()->create([
        //                 'project_id' => $project->id,
        //                 'order' => $i,
        //                 'name' => "Step $i",
        //             ]);

        //             // Create 2-4 tasks for each step
        //             Task::factory(fake()->numberBetween(2, 4))->create([
        //                 'project_step_id' => $step->id,
        //             ]);

        //             // Create 1-3 required documents for each step
        //             RequiredDocument::factory(fake()->numberBetween(1, 3))->create([
        //                 'project_step_id' => $step->id,
        //             ]);
        //         }
        //     });
        // });
    }
}
