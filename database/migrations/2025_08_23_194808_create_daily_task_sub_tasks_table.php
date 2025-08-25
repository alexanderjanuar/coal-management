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
        Schema::create('daily_task_subtasks', function (Blueprint $table) {
            $table->id();
            
            // Parent task relationship
            $table->foreignId('daily_task_id')->constrained()->onDelete('cascade');
            
            // Subtask details
            $table->string('title');
            
            // Status tracking
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            
            $table->timestamps();

            // Index for better performance
            $table->index(['daily_task_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_task_subtasks');
    }
};