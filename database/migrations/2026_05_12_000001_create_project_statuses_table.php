<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('label');
            $table->string('color', 7);                  // hex
            $table->string('shape', 20);                 // empty | dashed | half | clock | check | x
            $table->enum('category', [
                'not_started',
                'active',
                'done',
                'closed',
            ]);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->index(['category', 'sort_order']);
        });

        // Seed the 7 existing project statuses so the app is never in an
        // inconsistent state immediately after migration.
        $now = now();
        DB::table('project_statuses')->insert([
            [
                'key' => 'draft', 'label' => 'Draft',
                'color' => '#64748b', 'shape' => 'empty',
                'category' => 'not_started', 'sort_order' => 1,
                'is_system' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'analysis', 'label' => 'Analysis',
                'color' => '#9333ea', 'shape' => 'dashed',
                'category' => 'active', 'sort_order' => 1,
                'is_system' => false,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'in_progress', 'label' => 'In Progress',
                'color' => '#2563eb', 'shape' => 'half',
                'category' => 'active', 'sort_order' => 2,
                'is_system' => false,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'review', 'label' => 'Review',
                'color' => '#ca8a04', 'shape' => 'clock',
                'category' => 'active', 'sort_order' => 3,
                'is_system' => false,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'completed', 'label' => 'Completed',
                'color' => '#16a34a', 'shape' => 'check',
                'category' => 'done', 'sort_order' => 1,
                'is_system' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'completed_not_paid', 'label' => 'Done — Unpaid',
                'color' => '#15803d', 'shape' => 'check',
                'category' => 'done', 'sort_order' => 2,
                'is_system' => false,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'key' => 'canceled', 'label' => 'Canceled',
                'color' => '#dc2626', 'shape' => 'x',
                'category' => 'closed', 'sort_order' => 1,
                'is_system' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('project_statuses');
    }
};
