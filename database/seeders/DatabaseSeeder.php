<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Progress;
use App\Models\Task;
use App\Models\Document;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Client::factory()
            ->count(10)
            ->has(
                Progress::factory()
                    ->count(3)
                    ->has(
                        Task::factory()
                            ->count(4)
                            ->has(
                                Document::factory()
                                    ->count(2)
                            )
                    )
            )
            ->create();
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
