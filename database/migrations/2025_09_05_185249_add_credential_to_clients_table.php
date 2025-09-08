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
        Schema::table('clients', function (Blueprint $table) {
            // Tambah foreign key ke client_credentials
            $table->foreignId('credential_id')
                  ->nullable()
                  ->after('ar_id')
                  ->constrained('client_credentials')
                  ->nullOnDelete()
                  ->comment('Reference to client credential');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['credential_id']);
            $table->dropColumn('credential_id');
        });
    }
};