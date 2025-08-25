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
        Schema::create('daily_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Optional relationship to existing projects
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            
            // Who created this task
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            // Task properties
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            
            // Scheduling
            $table->date('task_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            
            // Time tracking
            $table->integer('estimated_hours')->nullable();
            $table->integer('actual_hours')->nullable();
            
            // Additional notes
            $table->text('notes')->nullable();
            
            $table->timestamps();

            // Indexes for better performance
            $table->index(['task_date', 'status']);
            $table->index(['project_id', 'task_date']);
            $table->index(['created_by', 'task_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_tasks');
    }
};