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
        Schema::create('daily_task_assignments', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('daily_task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Assignment details - simple many-to-many relationship
            
            $table->timestamps();

            // Ensure unique assignment per task and user
            $table->unique(['daily_task_id', 'user_id']);
            
            // Indexes for better performance
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_task_assignments');
    }
};