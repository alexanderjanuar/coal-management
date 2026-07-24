<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_task_subtasks', function (Blueprint $table) {
            $table->unsignedInteger('order')->default(0)->after('status');
        });

        // Backfill: urutan awal mengikuti id (urutan pembuatan) per task,
        // supaya tiap subtask punya posisi berbeda sebelum drag pertama.
        $taskIds = DB::table('daily_task_subtasks')->distinct()->pluck('daily_task_id');
        foreach ($taskIds as $taskId) {
            $subIds = DB::table('daily_task_subtasks')
                ->where('daily_task_id', $taskId)
                ->orderBy('id')
                ->pluck('id');
            foreach ($subIds as $i => $sid) {
                DB::table('daily_task_subtasks')->where('id', $sid)->update(['order' => $i + 1]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('daily_task_subtasks', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
