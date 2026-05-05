<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_requirement_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->string('name')->comment('e.g. SPD2K, SPT Tahunan 2024');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('year')->nullable()->comment('Tahun terkait, jika berlaku');
            $table->date('due_date')->nullable()->comment('Deadline keseluruhan grup');

            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['client_id', 'status'], 'req_groups_client_status_idx');
            $table->index(['client_id', 'year'], 'req_groups_client_year_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_requirement_groups');
    }
};
