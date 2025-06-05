<?php
// Combined Migration: Update invoice file_path and tax_reports client_id constraints
// File: database/migrations/xxxx_xx_xx_update_invoice_and_tax_report_constraints.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Make file_path nullable in invoices table
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('file_path')->nullable()->change();
        });

        // 2. Update client_id foreign key in tax_reports table to cascade on delete
        Schema::table('tax_reports', function (Blueprint $table) {
            // Get existing foreign key constraint name for client_id
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'tax_reports' 
                AND COLUMN_NAME = 'client_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            // Drop existing foreign key if it exists
            if (!empty($foreignKeys)) {
                $constraintName = $foreignKeys[0]->CONSTRAINT_NAME;
                $table->dropForeign($constraintName);
            } else {
                // Fallback: try to drop using standard naming convention
                try {
                    $table->dropForeign(['client_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist or have different name
                    // This is acceptable, we'll create a new one
                }
            }
            
            // Add new foreign key constraint with cascade
            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Make file_path not nullable in invoices table
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('file_path')->nullable(false)->change();
        });

        // 2. Remove cascade from client_id foreign key in tax_reports table
        Schema::table('tax_reports', function (Blueprint $table) {
            // Drop the cascade foreign key constraint
            $table->dropForeign(['client_id']);
            
            // Recreate the original foreign key constraint (without cascade)
            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients');
        });
    }
};