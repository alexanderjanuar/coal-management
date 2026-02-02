<?php

namespace App\Livewire\Dashboard\Widgets;

use App\Models\DailyTask;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;

#[Lazy]
class DailyTaskWidget extends Component
{
    #[Computed]
    public function todayTasks()
    {
        $user = auth()->user();
        $today = today();

        return DailyTask::with(['project:id,name', 'assignedUsers:id,name,avatar_url'])
            ->select(['id', 'title', 'project_id', 'status', 'priority', 'task_date', 'start_task_date', 'created_by'])
            ->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('assignedUsers', function ($sub) use ($user) {
                        $sub->where('user_id', $user->id);
                    });
            })
            ->where(function ($dateQ) use ($today) {
                $dateQ->where(function ($q) use ($today) {
                    $q->where('start_task_date', '<=', $today)
                      ->where('task_date', '>=', $today);
                })->orWhere(function ($q) use ($today) {
                    $q->where('task_date', $today)
                      ->whereNull('start_task_date');
                });
            })
            ->orderByRaw("
                CASE
                    WHEN status = 'in_progress' THEN 0
                    WHEN status = 'pending' THEN 1
                    WHEN status = 'draft' THEN 2
                    WHEN status = 'completed' THEN 3
                    ELSE 4
                END
            ")
            ->limit(10)
            ->get()
            ->map(function ($task) {
                $assignees = $task->assignedUsers->map(fn ($u) => [
                    'name' => $u->name,
                    'avatar_url' => $u->avatar_url,
                    'initials' => strtoupper(substr($u->name, 0, 1)),
                ])->values();

                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'project' => $task->project?->name,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'assignees' => $assignees,
                ];
            });
    }

    #[Computed]
    public function taskStats()
    {
        $tasks = $this->todayTasks;

        return [
            'total' => $tasks->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'pending' => $tasks->whereIn('status', ['pending', 'draft'])->count(),
        ];
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-6">
            <div class="animate-pulse space-y-4">
                <div class="flex items-center justify-between">
                    <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-32"></div>
                    <div class="h-4 bg-gray-100 dark:bg-gray-800 rounded w-12"></div>
                </div>
                <div class="h-1 bg-gray-100 dark:bg-gray-800 rounded-full w-full"></div>
                <div class="space-y-2">
                    <div class="h-14 bg-gray-100 dark:bg-gray-800 rounded-xl"></div>
                    <div class="h-14 bg-gray-100 dark:bg-gray-800 rounded-xl"></div>
                    <div class="h-14 bg-gray-100 dark:bg-gray-800 rounded-xl"></div>
                    <div class="h-14 bg-gray-100 dark:bg-gray-800 rounded-xl"></div>
                </div>
            </div>
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.daily-task-widget', [
            'tasks' => $this->todayTasks,
            'taskStats' => $this->taskStats,
        ]);
    }
}
