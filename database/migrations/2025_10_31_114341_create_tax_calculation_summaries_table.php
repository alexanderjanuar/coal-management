<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel ini menyimpan summary perhitungan untuk setiap tipe pajak (PPN, PPh, Bupot)
     * Ini menghindari field explosion di tax_reports table
     */
    public function up(): void
    {
        Schema::create('tax_calculation_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_report_id')->constrained()->cascadeOnDelete();
            
            // Tipe pajak: 'ppn', 'pph', 'bupot'
            $table->enum('tax_type', ['ppn', 'pph', 'bupot'])->comment('Jenis pajak');
            
            // Perhitungan dasar
            $table->decimal('pajak_masuk', 15, 2)->default(0)->comment('Kredit pajak masuk');
            $table->decimal('pajak_keluar', 15, 2)->default(0)->comment('Pajak keluaran/terutang');
            $table->decimal('selisih', 15, 2)->default(0)->comment('Selisih (keluar - masuk)');
            
            // Status pembayaran
            $table->enum('status', ['Lebih Bayar', 'Kurang Bayar', 'Nihil'])
                  ->nullable()
                  ->comment('Status pembayaran pajak');
            
            // Kompensasi
            $table->decimal('kompensasi_diterima', 15, 2)->default(0)
                  ->comment('Total kompensasi yang diterima dari masa sebelumnya');
            
            $table->decimal('kompensasi_tersedia', 15, 2)->default(0)
                  ->comment('Jumlah lebih bayar yang bisa dikompensasi ke masa depan');
            
            $table->decimal('kompensasi_terpakai', 15, 2)->default(0)
                  ->comment('Jumlah yang sudah dikompensasi ke masa depan');
            
            // Saldo akhir setelah kompensasi
            $table->decimal('saldo_final', 15, 2)->default(0)
                  ->comment('Saldo akhir setelah dikurangi/ditambah kompensasi');
            
            $table->enum('status_final', ['Lebih Bayar', 'Kurang Bayar', 'Nihil'])
                  ->nullable()
                  ->comment('Status final setelah kompensasi');
            
            // Metadata
            $table->text('notes')->nullable()->comment('Catatan perhitungan');
            $table->timestamp('calculated_at')->nullable()->comment('Terakhir dikalkulasi');
            $table->foreignId('calculated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            // Unique constraint: satu tax_report hanya punya satu summary per tax_type
            $table->unique(['tax_report_id', 'tax_type']);
            
            // Indexes untuk performa
            $table->index('tax_type');
            $table->index('status');
            $table->index('status_final');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_calculation_summaries');
    }
};