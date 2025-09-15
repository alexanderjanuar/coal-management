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
            // Tambah field DJP account dan password
            $table->string('djp_account')->nullable()
                  ->after('core_tax_password')
                  ->comment('DJP (Direktorat Jenderal Pajak) account username');
            
            $table->string('djp_password')->nullable()
                  ->after('djp_account')
                  ->comment('DJP (Direktorat Jenderal Pajak) account password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_credentials', function (Blueprint $table) {
            $table->dropColumn(['djp_account', 'djp_password']);
        });
    }
};