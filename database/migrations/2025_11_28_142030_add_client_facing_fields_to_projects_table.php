<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Deliverable/Result files (JSON array of file paths)
            $table->json('deliverable_files')
                  ->nullable()
                  ->after('description')
                  ->comment('Files to be delivered to client');
            
            // Final result/output description
            $table->text('result_notes')
                  ->nullable()
                  ->after('deliverable_files')
                  ->comment('Notes about the final result/deliverable');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'deliverable_files',
                'result_notes',
            ]);
        });
    }
};