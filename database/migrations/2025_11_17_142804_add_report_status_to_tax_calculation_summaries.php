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
        Schema::table('tax_calculation_summaries', function (Blueprint $table) {
            // Add report status fields for each tax type
            $table->enum('report_status', ['Belum Lapor', 'Sudah Lapor'])
                  ->default('Belum Lapor')
                  ->after('status_final')
                  ->comment('Status pelaporan pajak');
            
            $table->date('reported_at')
                  ->nullable()
                  ->after('report_status')
                  ->comment('Tanggal pelaporan');
            
            // Add index for performance
            $table->index('report_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_calculation_summaries', function (Blueprint $table) {
            $table->dropIndex(['report_status']);
            $table->dropColumn(['report_status', 'reported_at']);
        });
    }
};