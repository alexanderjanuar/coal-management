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
        Schema::table('invoices', function (Blueprint $table) {
            // Field to indicate if this invoice is a revision
            $table->boolean('is_revision')
                  ->default(false)
                  ->after('bukti_setor')
                  ->comment('Indicates if this invoice is a revised version');
            
            // Reference to the original invoice that this revision is based on
            $table->foreignId('original_invoice_id')
                  ->nullable()
                  ->after('is_revision')
                  ->constrained('invoices')
                  ->nullOnDelete()
                  ->comment('Reference to the original invoice if this is a revision');
            
            // Revision number for tracking multiple revisions
            $table->unsignedInteger('revision_number')
                  ->default(0)
                  ->after('original_invoice_id')
                  ->comment('Revision number (0 = original, 1+ = revision versions)');
            
            // Reason for the revision
            $table->text('revision_reason')
                  ->nullable()
                  ->after('revision_number')
                  ->comment('Reason for creating this revision');
            

        });
        
        // Add index for better performance when querying revisions
        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['original_invoice_id', 'revision_number'], 'invoices_revision_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('invoices_revision_index');
            
            // Drop foreign key constraints
            $table->dropForeign(['original_invoice_id']);
            
            // Drop columns
            $table->dropColumn([
                'is_revision',
                'original_invoice_id',
                'revision_number',
                'revision_reason'
            ]);
        });
    }
};