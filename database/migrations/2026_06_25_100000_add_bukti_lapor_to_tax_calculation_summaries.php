<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_calculation_summaries', function (Blueprint $table) {
            // Bukti lapor SPT (file dari Coretax) + nomor bukti (NTTE/BPE) per jenis pajak per masa.
            $table->string('bukti_lapor')->nullable()->after('bukti_bayar');
            $table->string('nomor_bukti_lapor')->nullable()->after('bukti_lapor');
        });
    }

    public function down(): void
    {
        Schema::table('tax_calculation_summaries', function (Blueprint $table) {
            $table->dropColumn(['bukti_lapor', 'nomor_bukti_lapor']);
        });
    }
};
