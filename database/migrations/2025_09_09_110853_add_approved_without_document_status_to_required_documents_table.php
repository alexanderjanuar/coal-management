<?php

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
        // Update enum status untuk menambahkan status 'approved_without_document'
        DB::statement("ALTER TABLE required_documents MODIFY COLUMN status ENUM(
            'draft', 
            'uploaded', 
            'pending_review', 
            'approved', 
            'rejected',
            'approved_without_document'
        ) DEFAULT 'draft' COMMENT 'Status dokumen yang diperlukan'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan enum ke status asli
        DB::statement("ALTER TABLE required_documents MODIFY COLUMN status ENUM(
            'draft', 
            'uploaded', 
            'pending_review', 
            'approved', 
            'rejected'
        ) DEFAULT 'draft'");
    }
};