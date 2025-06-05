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

        Schema::table('income_taxes', function (Blueprint $table) {
            // First add the new column
            $table->string('bukti_setor')->nullable()->after('file_path');
        });

        Schema::table('bupots', function (Blueprint $table) {
            // First add the new column
            $table->string('bukti_setor')->nullable()->after('file_path');
        });

        Schema::table('invoices', function (Blueprint $table) {
            // First add the new column
            $table->string('bukti_setor')->nullable()->after('file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('income_taxes', function (Blueprint $table) {
            $table->dropColumn('bukti_setor');
        });

        Schema::table('bupots', function (Blueprint $table) {
            $table->dropColumn('bukti_setor');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('bukti_setor');
        });
    }
};