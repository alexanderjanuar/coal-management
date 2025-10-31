<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Update tax_compensations untuk support berbagai tipe pajak
     */
    public function up(): void
    {
        Schema::table('tax_compensations', function (Blueprint $table) {
            // Tambah tax_type untuk menentukan jenis pajak yang dikompensasi
            $table->enum('tax_type', ['ppn', 'pph', 'bupot'])
                  ->default('ppn')
                  ->after('target_tax_report_id')
                  ->comment('Jenis pajak yang dikompensasi');
            
            // Index untuk performa query by tax_type
            $table->index('tax_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_compensations', function (Blueprint $table) {
            $table->dropIndex(['tax_type']);
            $table->dropColumn('tax_type');
        });
    }
};