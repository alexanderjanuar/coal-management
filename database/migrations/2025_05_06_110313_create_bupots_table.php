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
        Schema::create('bupots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_report_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tax_period')->nullable();
            $table->string('npwp');
            $table->string('company_name');
            $table->string('bupot_percentage');
            $table->enum('bupot_type',["Bupot Masukan","Bupot Keluaran"]);
            $table->text('notes')->nullable();
            $table->decimal('dpp', 15, 2)->default(0);
            $table->enum('pph_type',["PPh 21","PPh 23"]);
            $table->decimal('bupot_amount', 15, 2)->default(0);
            $table->string('file_path');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bupots');
    }
};
