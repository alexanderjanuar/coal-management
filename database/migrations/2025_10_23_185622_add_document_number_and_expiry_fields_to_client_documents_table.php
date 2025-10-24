<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_documents', function (Blueprint $table) {
            // Add document number field
            $table->string('document_number')->nullable()->after('original_filename');
            
            // Add expiry date field
            $table->date('expired_at')->nullable()->after('document_number');
            
            // Add document category for better organization
            $table->string('document_category')->nullable()->after('expired_at');
            
            // Add status field
            $table->enum('status', ['valid', 'expired', 'pending', 'rejected'])->default('valid')->after('document_category');
        });
    }

    public function down(): void
    {
        Schema::table('client_documents', function (Blueprint $table) {
            $table->dropColumn(['document_number', 'expired_at', 'document_category', 'status']);
        });
    }
};