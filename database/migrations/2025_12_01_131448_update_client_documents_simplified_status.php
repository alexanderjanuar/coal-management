<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('client_documents', function (Blueprint $table) {
            // Make file_path nullable so documents can be created as requirements first
            $table->string('file_path')->nullable()->change();
            
            // Make user_id nullable since requirements are created before upload
            $table->foreignId('user_id')->nullable()->change();
            
            // Update status enum to cover full lifecycle
            // First, we need to modify the existing status column
            \DB::statement("ALTER TABLE client_documents MODIFY COLUMN status ENUM(
                'required',
                'pending_review', 
                'valid',
                'expired',
                'rejected'
            ) DEFAULT 'required' COMMENT 'Document lifecycle status'");
            
            // Add admin notes field
            if (!Schema::hasColumn('client_documents', 'admin_notes')) {
                $table->text('admin_notes')
                      ->nullable()
                      ->after('status')
                      ->comment('Admin notes about document review, rejection reasons, etc.');
            }
            
            // Add reviewed_by field to track who reviewed
            if (!Schema::hasColumn('client_documents', 'reviewed_by')) {
                $table->foreignId('reviewed_by')
                      ->nullable()
                      ->after('admin_notes')
                      ->constrained('users')
                      ->nullOnDelete()
                      ->comment('Admin who reviewed the document');
            }
            
            // Add reviewed_at timestamp
            if (!Schema::hasColumn('client_documents', 'reviewed_at')) {
                $table->timestamp('reviewed_at')
                      ->nullable()
                      ->after('reviewed_by')
                      ->comment('When document was reviewed');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_documents', function (Blueprint $table) {
            $table->string('file_path')->nullable(false)->change();
            $table->foreignId('user_id')->nullable(false)->change();
            
            // Revert status to original enum
            \DB::statement("ALTER TABLE client_documents MODIFY COLUMN status ENUM(
                'valid',
                'expired',
                'pending',
                'rejected'
            ) DEFAULT 'valid'");
            
            if (Schema::hasColumn('client_documents', 'admin_notes')) {
                $table->dropColumn('admin_notes');
            }
            
            if (Schema::hasColumn('client_documents', 'reviewed_by')) {
                $table->dropForeign(['reviewed_by']);
                $table->dropColumn('reviewed_by');
            }
            
            if (Schema::hasColumn('client_documents', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }
        });
    }
};