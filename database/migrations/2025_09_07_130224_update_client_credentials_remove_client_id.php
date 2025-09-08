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
        Schema::table('client_credentials', function (Blueprint $table) {
            // Drop foreign key constraint dan kolom client_id
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
            
            // Drop index yang menggunakan client_id
            $table->dropIndex(['client_id', 'credential_type']);
            $table->dropIndex(['client_id', 'is_active']);
            
            // Tambah index baru tanpa client_id
            $table->index(['credential_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_credentials', function (Blueprint $table) {
            // Tambahkan kembali kolom client_id
            $table->foreignId('client_id')
                  ->after('id')
                  ->constrained()
                  ->onDelete('cascade');
            
            // Tambahkan kembali index
            $table->index(['client_id', 'credential_type']);
            $table->index(['client_id', 'is_active']);
            
            // Drop index yang baru
            $table->dropIndex(['credential_type', 'is_active']);
        });
    }
};