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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained();
            $table->foreignId('sop_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('priority', ['urgent','normal','low'])->default('normal');
            $table->enum('type', ['single','monthly','yearly']);
            $table->date('due_date');
            $table->enum('status', ['draft','analysis', 'in_progress', 'completed', 'review', 'completed (Not Payed Yet)','canceled'])->default('draft');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
