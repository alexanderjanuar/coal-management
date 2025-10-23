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
        Schema::create('client_affiliates', function (Blueprint $table) {
            $table->id();
            
            // Foreign key ke client utama
            $table->foreignId('client_id')
                  ->constrained()
                  ->cascadeOnDelete()
                  ->comment('Client utama yang memiliki afiliasi');
            
            // Informasi perusahaan afiliasi
            $table->string('company_name')->comment('Nama perusahaan afiliasi');
            
            // Tipe hubungan dengan enum
            $table->enum('relationship_type', [
                'Anak Perusahaan',
                'Afiliasi',
                'Perusahaan Induk',
                'Sister Company',
                'Joint Venture',
                'Lainnya'
            ])->default('Afiliasi')->comment('Jenis hubungan dengan perusahaan utama');
            
            // Persentase kepemilikan
            $table->decimal('ownership_percentage', 5, 2)
                  ->nullable()
                  ->comment('Persentase kepemilikan (0-100)');
            
            // NPWP perusahaan afiliasi
            $table->string('npwp', 20)
                  ->nullable()
                  ->comment('NPWP perusahaan afiliasi');
            
            // Optional: Link ke client lain jika afiliasi juga adalah client
            $table->foreignId('affiliated_client_id')
                  ->nullable()
                  ->constrained('clients')
                  ->nullOnDelete()
                  ->comment('ID client jika perusahaan afiliasi juga terdaftar sebagai client');
            
            // Informasi tambahan
            $table->text('notes')->nullable()->comment('Catatan tambahan tentang afiliasi');
            
            // Status
            $table->enum('status', ['active', 'inactive'])
                  ->default('active')
                  ->comment('Status afiliasi');
            
            $table->timestamps();
            
            // Indexes untuk performa
            $table->index(['client_id', 'status'], 'client_affiliates_client_status_index');
            $table->index('relationship_type', 'client_affiliates_relationship_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_affiliates');
    }
};