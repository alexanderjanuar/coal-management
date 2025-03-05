<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('submitted_documents', function (Blueprint $table) {
            // Add status column as enum after file_path
            $table->enum('status', ['uploaded', 'pending_review', 'approved', 'rejected'])
                ->default('uploaded')
                ->after('file_path');

            // Add notes column after rejection_reason
            $table->text('notes')->nullable()->after('rejection_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submitted_documents', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('notes');
        });
    }
};