<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('position')->nullable()->comment('Jabatan');
            $table->string('email')->nullable();
            $table->string('phone')->nullable()->comment('Telepon');
            $table->string('mobile')->nullable()->comment('HP/WhatsApp');
            $table->enum('type', ['primary', 'secondary', 'billing', 'technical'])->default('primary')
                  ->comment('Tipe kontak: utama, sekunder, billing, teknis');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable()->comment('Catatan tambahan');
            $table->timestamps();

            // Indexes
            $table->index(['client_id', 'is_active']);
            $table->index(['client_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_contacts');
    }
};