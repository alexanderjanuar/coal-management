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
        Schema::create('client_document_requirements', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('client_id')
                  ->constrained()
                  ->cascadeOnDelete()
                  ->comment('Client this requirement belongs to');
            
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->comment('Admin user who created this requirement');
            
            // Requirement details
            $table->string('name')
                  ->comment('Name/title of the required document (e.g., KTP_Direktur.pdf)');
            
            $table->text('description')
                  ->nullable()
                  ->comment('Detailed description of what this requirement is for');
            
            $table->enum('category', [
                'legal',        // Legal documents (KTP, NPWP, etc)
                'financial',    // Financial docs (bank statements, etc)
                'operational',  // Operational docs (licenses, permits)
                'compliance',   // Compliance documents
                'other'         // Other categories
            ])->default('other')
              ->comment('Category of the requirement');
            
            // Status and metadata
            $table->boolean('is_required')
                  ->default(true)
                  ->comment('Whether this is mandatory or optional');
            
            $table->enum('status', [
                'pending',      // Waiting for document upload
                'fulfilled',    // Document uploaded and approved
                'waived'        // Admin waived this requirement
            ])->default('pending')
              ->comment('Current status of the requirement');
            
            // Optional due date
            $table->date('due_date')
                  ->nullable()
                  ->comment('When this document should be submitted by');
            
            // Admin notes
            $table->text('admin_notes')
                  ->nullable()
                  ->comment('Internal notes for admins');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['client_id', 'status'], 'req_client_status_idx');
            $table->index(['client_id', 'category'], 'req_client_category_idx');
            $table->index('status', 'req_status_idx');
            $table->index('due_date', 'req_due_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_document_requirements');
    }
};