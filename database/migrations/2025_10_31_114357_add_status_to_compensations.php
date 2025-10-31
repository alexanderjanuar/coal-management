<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_compensations', function (Blueprint $table) {
            // Status kompensasi
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])
                  ->default('pending')
                  ->after('amount_compensated');
            
            // Tipe kompensasi (otomatis atau manual)
            $table->enum('type', ['auto', 'manual'])
                  ->default('manual')
                  ->after('status');
            
            // User yang melakukan/approve kompensasi
            $table->foreignId('created_by')->nullable()
                  ->after('type')
                  ->constrained('users')
                  ->nullOnDelete();
            
            $table->foreignId('approved_by')->nullable()
                  ->after('created_by')
                  ->constrained('users')
                  ->nullOnDelete();
            
            // Tanggal approval
            $table->timestamp('approved_at')->nullable()
                  ->after('approved_by');
            
            // Alasan rejection (jika ditolak)
            $table->text('rejection_reason')->nullable()
                  ->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('tax_compensations', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'status',
                'type',
                'created_by',
                'approved_by',
                'approved_at',
                'rejection_reason'
            ]);
        });
    }
};