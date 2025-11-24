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
            // Add bayar status field
            $table->enum('bayar_status', ['Belum Bayar', 'Sudah Bayar'])
                  ->default('Belum Bayar')
                  ->after('report_status')
                  ->comment('Status pembayaran pajak');
            
            // Add tanggal bayar (optional)
            $table->date('bayar_at')
                  ->nullable()
                  ->after('bayar_status')
                  ->comment('Tanggal pembayaran');
            
            // Add bukti bayar (optional - bisa path file atau nomor NTPN)
            $table->string('bukti_bayar')
                  ->nullable()
                  ->after('bayar_at')
                  ->comment('Nomor NTPN atau path file bukti bayar');
            
            // Add index for performance
            $table->index('bayar_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_calculation_summaries', function (Blueprint $table) {
            $table->dropIndex(['bayar_status']);
            $table->dropColumn(['bayar_status', 'bayar_at', 'bukti_bayar']);
        });
    }
};