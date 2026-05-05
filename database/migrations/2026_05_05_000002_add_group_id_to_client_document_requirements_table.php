<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_document_requirements', function (Blueprint $table) {
            $table->foreignId('group_id')
                  ->nullable()
                  ->after('client_id')
                  ->constrained('client_requirement_groups')
                  ->nullOnDelete()
                  ->comment('Grup kebutuhan dokumen, e.g. SPD2K, SPT Tahunan');

            $table->index(['group_id', 'status'], 'req_group_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('client_document_requirements', function (Blueprint $table) {
            $table->dropIndex('req_group_status_idx');
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });
    }
};
