<?php
// database/migrations/xxxx_xx_xx_create_tax_compensations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_compensations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_tax_report_id')->constrained('tax_reports')->onDelete('cascade');
            $table->foreignId('target_tax_report_id')->constrained('tax_reports')->onDelete('cascade');
            $table->decimal('amount_compensated', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_compensations');
    }
};