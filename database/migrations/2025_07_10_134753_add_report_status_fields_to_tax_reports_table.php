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
        Schema::table('tax_reports', function (Blueprint $table) {
            // Status laporan PPN
            $table->enum('ppn_report_status', ['Belum Lapor', 'Sudah Lapor'])
                  ->default('Belum Lapor')
                  ->after('kompensasi_notes')
                  ->comment('Status pelaporan PPN bulanan');
            
            // Status laporan PPh
            $table->enum('pph_report_status', ['Belum Lapor', 'Sudah Lapor'])
                  ->default('Belum Lapor')
                  ->after('ppn_report_status')
                  ->comment('Status pelaporan PPh bulanan');
            
            // Status laporan Bupot
            $table->enum('bupot_report_status', ['Belum Lapor', 'Sudah Lapor'])
                  ->default('Belum Lapor')
                  ->after('pph_report_status')
                  ->comment('Status pelaporan Bupot bulanan');
            
            // Tanggal pelaporan (opsional)
            $table->date('ppn_reported_at')->nullable()
                  ->after('bupot_report_status')
                  ->comment('Tanggal pelaporan PPN');
            
            $table->date('pph_reported_at')->nullable()
                  ->after('ppn_reported_at')
                  ->comment('Tanggal pelaporan PPh');
            
            $table->date('bupot_reported_at')->nullable()
                  ->after('pph_reported_at')
                  ->comment('Tanggal pelaporan Bupot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_reports', function (Blueprint $table) {
            $table->dropColumn([
                'ppn_report_status',
                'pph_report_status', 
                'bupot_report_status',
                'ppn_reported_at',
                'pph_reported_at',
                'bupot_reported_at'
            ]);
        });
    }
};