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
            // Add dpp_nilai_lainnya field after the existing dpp field
            $table->decimal('dpp_nilai_lainnya', 15, 2)
                  ->default(0)
                  ->after('dpp')
                  ->comment('DPP Nilai Lainnya - Additional DPP value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('dpp_nilai_lainnya');
        });
    }
};