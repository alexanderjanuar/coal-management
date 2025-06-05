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
            // Add status bayar column
            $table->enum('invoice_tax_status', ['Lebih Bayar', 'Kurang Bayar', 'Nihil'])
                ->nullable()
                ->comment('Status pembayaran PPN berdasarkan perbandingan faktur masuk dan keluar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_reports', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_tax_status',
            ]);
        });
    }
};