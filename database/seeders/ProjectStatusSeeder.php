<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectStatusSeeder extends Seeder
{
    /**
     * Idempotent seeder for the canonical project statuses.
     *
     * Run via:  php artisan db:seed --class=ProjectStatusSeeder
     * The create_project_statuses_table migration already seeds these on
     * first migration; this seeder is for re-syncing labels/colors after
     * the table already exists.
     */
    public function run(): void
    {
        $now = now();

        $statuses = [
            ['key' => 'draft',              'label' => 'Draft',         'color' => '#64748b', 'shape' => 'empty',  'category' => 'not_started', 'sort_order' => 1, 'is_system' => true],
            ['key' => 'analysis',           'label' => 'Analysis',      'color' => '#9333ea', 'shape' => 'dashed', 'category' => 'active',      'sort_order' => 1, 'is_system' => false],
            ['key' => 'in_progress',        'label' => 'In Progress',   'color' => '#2563eb', 'shape' => 'half',   'category' => 'active',      'sort_order' => 2, 'is_system' => false],
            ['key' => 'review',             'label' => 'Review',        'color' => '#ca8a04', 'shape' => 'clock',  'category' => 'active',      'sort_order' => 3, 'is_system' => false],
            ['key' => 'completed',          'label' => 'Completed',     'color' => '#16a34a', 'shape' => 'check',  'category' => 'done',        'sort_order' => 1, 'is_system' => true],
            ['key' => 'completed_not_paid', 'label' => 'Done — Unpaid', 'color' => '#15803d', 'shape' => 'check',  'category' => 'done',        'sort_order' => 2, 'is_system' => false],
            ['key' => 'canceled',           'label' => 'Canceled',      'color' => '#dc2626', 'shape' => 'x',      'category' => 'closed',      'sort_order' => 1, 'is_system' => true],
        ];

        foreach ($statuses as $row) {
            DB::table('project_statuses')->updateOrInsert(
                ['key' => $row['key']],
                array_merge($row, ['updated_at' => $now, 'created_at' => $now]),
            );
        }
    }
}
