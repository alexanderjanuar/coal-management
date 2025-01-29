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
        Schema::create('project_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('order');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('low');
            $table->date('start_date')->default(now());
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'waiting_for_documents', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_steps');
    }
};
