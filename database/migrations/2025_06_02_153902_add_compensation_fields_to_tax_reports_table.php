<?php
// database/migrations/xxxx_xx_xx_add_compensation_fields_to_tax_reports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_reports', function (Blueprint $table) {
            // Amount compensated from previous months
            $table->decimal('ppn_dikompensasi_dari_masa_sebelumnya', 15, 2)
                  ->default(0)
                  ->after('invoice_tax_status');
            
            // Amount available for future compensation (when lebih bayar)
            $table->decimal('ppn_lebih_bayar_dibawa_ke_masa_depan', 15, 2)
                  ->default(0)
                  ->after('ppn_dikompensasi_dari_masa_sebelumnya');
            
            // Amount already used from this report for future compensations
            $table->decimal('ppn_sudah_dikompensasi', 15, 2)
                  ->default(0)
                  ->after('ppn_lebih_bayar_dibawa_ke_masa_depan');
            
            // Notes about compensation
            $table->text('kompensasi_notes')->nullable()
                  ->after('ppn_sudah_dikompensasi');
        });
    }

    public function down(): void
    {
        Schema::table('tax_reports', function (Blueprint $table) {
            $table->dropColumn([
                'ppn_dikompensasi_dari_masa_sebelumnya',
                'ppn_lebih_bayar_dibawa_ke_masa_depan', 
                'ppn_sudah_dikompensasi',
                'kompensasi_notes'
            ]);
        });
    }
};