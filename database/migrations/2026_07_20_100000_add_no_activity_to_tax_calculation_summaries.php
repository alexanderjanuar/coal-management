<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda "masa ini tidak ada aktivitas, jadi tidak ada SPT yang dilaporkan".
 *
 * Berlaku untuk PPh Unifikasi dan PPh Badan, yang hanya wajib dilaporkan bila
 * masa itu memang ada pemotongan. PPN dan PPh 21 wajib tiap masa meski nihil,
 * jadi penanda ini tidak dipakai di sana.
 *
 * Kenapa kolom tersendiri dan bukan nilai baru pada report_status: ada 107
 * tempat di aplikasi yang memeriksa report_status, dan menambah nilai ketiga
 * berarti mengaudit semuanya. Dengan kolom terpisah, masa nihil tetap berstatus
 * "Sudah Lapor" sehingga seluruh perhitungan yang ada terus bekerja apa adanya,
 * dan hanya tempat yang perlu membedakan yang membaca kolom ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_calculation_summaries', function (Blueprint $table) {
            $table->boolean('no_activity')
                ->default(false)
                ->after('report_status')
                ->comment('Masa tanpa aktivitas: ditandai selesai tanpa SPT.');

            $table->foreignId('no_activity_by')
                ->nullable()
                ->after('no_activity')
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Siapa yang menyatakan masa ini tanpa aktivitas.');

            $table->timestamp('no_activity_at')
                ->nullable()
                ->after('no_activity_by');
        });
    }

    public function down(): void
    {
        Schema::table('tax_calculation_summaries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('no_activity_by');
            $table->dropColumn(['no_activity', 'no_activity_at']);
        });
    }
};
