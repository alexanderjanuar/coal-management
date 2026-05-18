<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Convert projects.status from a fixed ENUM to a VARCHAR(60) so
     * arbitrary status keys (from the new project_statuses table) can be stored.
     *
     * Existing values are preserved. The historical value
     * 'completed (Not Payed Yet)' is normalized to the slug-safe key
     * 'completed_not_paid' to match the seed.
     */
    public function up(): void
    {
        // Step 1: widen the column type. Raw SQL because Doctrine DBAL doesn't
        // cleanly support converting from MySQL's ENUM type.
        DB::statement("ALTER TABLE projects MODIFY status VARCHAR(60) NOT NULL DEFAULT 'draft'");

        // Step 2: rename the legacy compound value to its new slug key
        DB::table('projects')
            ->where('status', 'completed (Not Payed Yet)')
            ->update(['status' => 'completed_not_paid']);
    }

    public function down(): void
    {
        // Reverse the rename first, then narrow back to the original ENUM
        DB::table('projects')
            ->where('status', 'completed_not_paid')
            ->update(['status' => 'completed (Not Payed Yet)']);

        DB::statement("
            ALTER TABLE projects
            MODIFY status ENUM(
                'draft',
                'analysis',
                'in_progress',
                'completed',
                'review',
                'completed (Not Payed Yet)',
                'canceled'
            ) NOT NULL DEFAULT 'draft'
        ");
    }
};
