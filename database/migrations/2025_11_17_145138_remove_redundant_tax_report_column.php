<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Remove redundant columns from tax_reports table since we now use:
     * - tax_calculation_summaries for calculations and status
     * - tax_compensations for compensation tracking
     */
    public function up(): void
    {
        Schema::table('tax_reports', function (Blueprint $table) {
            // Drop report status columns (moved to tax_calculation_summaries)
            $table->dropColumn([
                'ppn_report_status',
                'pph_report_status',
                'bupot_report_status',
                'ppn_reported_at',
                'pph_reported_at',
                'bupot_reported_at',
            ]);
            
            // Drop compensation columns (moved to tax_compensations and tax_calculation_summaries)
            $table->dropColumn([
                'ppn_dikompensasi_dari_masa_sebelumnya',
                'ppn_lebih_bayar_dibawa_ke_masa_depan',
                'ppn_sudah_dikompensasi',
                'kompensasi_notes',
            ]);
            
            // Drop payment status column (moved to tax_calculation_summaries)
            $table->dropColumn('invoice_tax_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_reports', function (Blueprint $table) {
            // Restore report status columns
            $table->enum('ppn_report_status', ['Belum Lapor', 'Sudah Lapor'])
                  ->default('Belum Lapor');
            $table->enum('pph_report_status', ['Belum Lapor', 'Sudah Lapor'])
                  ->default('Belum Lapor');
            $table->enum('bupot_report_status', ['Belum Lapor', 'Sudah Lapor'])
                  ->default('Belum Lapor');
            $table->date('ppn_reported_at')->nullable();
            $table->date('pph_reported_at')->nullable();
            $table->date('bupot_reported_at')->nullable();
            
            // Restore compensation columns
            $table->decimal('ppn_dikompensasi_dari_masa_sebelumnya', 15, 2)->default(0);
            $table->decimal('ppn_lebih_bayar_dibawa_ke_masa_depan', 15, 2)->default(0);
            $table->decimal('ppn_sudah_dikompensasi', 15, 2)->default(0);
            $table->text('kompensasi_notes')->nullable();
            
            // Restore payment status
            $table->enum('invoice_tax_status', ['Lebih Bayar', 'Kurang Bayar', 'Nihil'])->nullable();
        });
    }
};