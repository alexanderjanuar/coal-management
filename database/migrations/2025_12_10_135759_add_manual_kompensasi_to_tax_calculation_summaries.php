<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan field manual_kompensasi untuk memungkinkan user menambahkan
     * kompensasi custom yang akan ditambahkan ke perhitungan pajak.
     */
    public function up(): void
    {
        Schema::table('tax_calculation_summaries', function (Blueprint $table) {
            // Manual kompensasi field - kompensasi tambahan yang diinput manual
            $table->decimal('manual_kompensasi', 15, 2)
                  ->default(0)
                  ->after('kompensasi_terpakai')
                  ->comment('Kompensasi manual yang ditambahkan oleh user untuk perhitungan');
            
            // Catatan untuk manual kompensasi
            $table->text('manual_kompensasi_notes')
                  ->nullable()
                  ->after('manual_kompensasi')
                  ->comment('Catatan atau alasan untuk kompensasi manual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_calculation_summaries', function (Blueprint $table) {
            $table->dropColumn(['manual_kompensasi', 'manual_kompensasi_notes']);
        });
    }
};