<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_documents', function (Blueprint $table) {
            // Link ke SOP (opsional)
            $table->foreignId('sop_legal_document_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('sop_legal_documents')
                  ->nullOnDelete()
                  ->comment('Link ke template SOP (opsional)');
        });
    }

    public function down(): void
    {
        Schema::table('client_documents', function (Blueprint $table) {
            $table->dropForeign(['sop_legal_document_id']);
            $table->dropColumn('sop_legal_document_id');
        });
    }
};