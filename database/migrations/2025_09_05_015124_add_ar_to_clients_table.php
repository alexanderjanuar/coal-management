<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{   
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Menambahkan foreign key untuk Account Representative
            $table->foreignId('ar_id')
                  ->nullable()
                  ->after('pic_id')
                  ->constrained('account_representatives')
                  ->nullOnDelete()
                  ->comment('Account Representative yang menangani client ini');
            
            // Hapus kolom account_representative dan ar_phone_number lama jika tidak digunakan lagi
            $table->dropColumn(['account_representative', 'ar_phone_number','KPP']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['ar_id']);
            $table->dropColumn('ar_id');
            
            // Tambahkan kembali kolom lama
            $table->string('account_representative')->nullable();
            $table->string('ar_phone_number')->nullable();
            $table->string('KPP')->nullable();

        });
    }
};
