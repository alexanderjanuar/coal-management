<?php
// database/migrations/YYYY_MM_DD_add_required_additional_documents_to_client_documents.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_documents', function (Blueprint $table) {
            // Add field to mark if this is a required additional document template
            $table->boolean('is_template')
                  ->default(false)
                  ->after('sop_legal_document_id')
                  ->comment('If true, this is a template/requirement for client to upload');
            
            // Add description field for templates (what file is needed)
            $table->text('description')
                  ->nullable()
                  ->after('original_filename')
                  ->comment('Description of required document for templates');
        });
    }

    public function down(): void
    {
        Schema::table('client_documents', function (Blueprint $table) {
            $table->dropColumn(['is_template', 'description']);
        });
    }
};