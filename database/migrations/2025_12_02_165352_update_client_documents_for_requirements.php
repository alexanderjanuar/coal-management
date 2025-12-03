<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration does 3 things:
     * 1. Adds requirement_id foreign key
     * 2. Removes old is_template column
     * 3. Removes old linked_requirement_id column
     */
    public function up(): void
    {
        Schema::table('client_documents', function (Blueprint $table) {
            // ============================================
            // STEP 1: Add new requirement_id column
            // ============================================
            $table->foreignId('requirement_id')
                  ->nullable()
                  ->after('sop_legal_document_id')
                  ->constrained('client_document_requirements')
                  ->nullOnDelete()
                  ->comment('Requirement that this document fulfills');
            
            // Add index for better query performance
            $table->index(['client_id', 'requirement_id'], 'client_docs_client_req_idx');
            
            // ============================================
            // STEP 2: Remove old is_template column (if exists)
            // ============================================
            if (Schema::hasColumn('client_documents', 'is_template')) {
                $table->dropColumn('is_template');
            }
            
            // ============================================
            // STEP 3: Remove old linked_requirement_id column (if exists)
            // ============================================
            if (Schema::hasColumn('client_documents', 'linked_requirement_id')) {
                // Drop foreign key constraint first (if exists)
                try {
                    $table->dropForeign(['linked_requirement_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, that's ok
                    // Continue with dropping column
                }
                
                // Drop the column
                $table->dropColumn('linked_requirement_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Restores the old structure
     */
    public function down(): void
    {
        Schema::table('client_documents', function (Blueprint $table) {
            // ============================================
            // STEP 1: Remove requirement_id
            // ============================================
            $table->dropForeign(['requirement_id']);
            $table->dropIndex('client_docs_client_req_idx');
            $table->dropColumn('requirement_id');
            
            // ============================================
            // STEP 2: Restore is_template column
            // ============================================
            $table->boolean('is_template')
                  ->default(false)
                  ->after('user_id')
                  ->comment('Whether this is a template/requirement placeholder');
            
            // ============================================
            // STEP 3: Restore linked_requirement_id column
            // ============================================
            $table->foreignId('linked_requirement_id')
                  ->nullable()
                  ->after('sop_legal_document_id')
                  ->constrained('client_documents')
                  ->nullOnDelete()
                  ->comment('Link to requirement document template that this document fulfills');
        });
    }
};