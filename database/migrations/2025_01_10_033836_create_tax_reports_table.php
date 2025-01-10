<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tax_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained();
            $table->string('month');
            $table->decimal('ppn', 15, 2)->nullable();
            $table->decimal('pph_21', 15, 2)->nullable(); 
            $table->decimal('pph_unifikasi', 15, 2)->nullable();
            $table->enum('status', ['PKP', 'NON-PKP']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_reports');
    }
};
