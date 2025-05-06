<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('npwp')->nullable(); // NPWP
            $table->string('position')->nullable();
            $table->decimal('salary', 15, 2)->nullable(); 
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('type', ['Harian', 'Karyawan Tetap'])->default('Harian');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
