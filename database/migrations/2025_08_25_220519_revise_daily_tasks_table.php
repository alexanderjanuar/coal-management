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
        Schema::table('daily_tasks', function (Blueprint $table) {
            // Add new column
            $table->date('start_task_date')->nullable()->after('task_date');
            
            // Remove old columns
            $table->dropColumn([
                'start_time',
                'end_time', 
                'estimated_hours',
                'actual_hours',
                'notes'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_tasks', function (Blueprint $table) {
            // Add back old columns
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('estimated_hours')->nullable();
            $table->integer('actual_hours')->nullable();
            $table->text('notes')->nullable();
            
            // Remove new column
            $table->dropColumn('start_task_date');
        });
    }
};