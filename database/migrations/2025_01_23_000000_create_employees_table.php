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
            $table->integer('tk')->default(0)->comment('TK status - number of dependents for single person (0-3)');
            $table->integer('k')->default(0)->comment('K status - number of dependents for married person (0-3)');
            $table->enum('marital_status', ['single', 'married'])->default('single')->comment('Marital status to determine TK or K application');
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
