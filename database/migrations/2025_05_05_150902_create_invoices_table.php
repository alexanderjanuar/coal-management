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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->date('invoice_date')->nullable();
            $table->foreignId('tax_report_id')->constrained()->onDelete('cascade');
            $table->string('npwp');
            $table->string('company_name');
            $table->enum('type', ['Faktur Keluaran', 'Faktur Masuk'])->nullable();
            $table->decimal('dpp', 15, 2)->default(0);
            $table->decimal('ppn', 15, 2)->default(0);
            $table->string('ppn_percentage');
            $table->text('notes')->nullable();
            $table->string('file_path');
            $table->boolean('nihil')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
