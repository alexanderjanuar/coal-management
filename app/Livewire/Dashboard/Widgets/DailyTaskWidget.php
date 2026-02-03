<?php

namespace App\Livewire\Dashboard\Widgets;

use App\Models\DailyTask;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;

#[Lazy]
class DailyTaskWidget extends Component
{
    public string $period = 'today';

    public function updatedPeriod()
    {
        unset($this->tasks, $this->taskStats);
    }

    private function getDateRange(): array
    {
        return match ($this->period) {
            'today' => [today(), today()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [today(), today()],
        };
    }

    #[Computed]
    public function tasks()
    {
        $user = auth()->user();
        [$start, $end] = $this->getDateRange();

        return DailyTask::with(['project:id,name', 'assignedUsers:id,name,avatar_url'])
            ->select(['id', 'title', 'project_id', 'status', 'priority', 'task_date', 'start_task_date', 'created_by'])
            ->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('assignedUsers', function ($sub) use ($user) {
                        $sub->where('user_id', $user->id);
                    });
            })
            ->where(function ($dateQ) use ($start, $end) {
                $dateQ->where(function ($q) use ($start, $end) {
                    $q->where('start_task_date', '<=', $end)
                      ->where('task_date', '>=', $start);
                })->orWhere(function ($q) use ($start, $end) {
                    $q->whereBetween('task_date', [$start, $end])
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
            ->orderBy('task_date')
            ->limit(12)
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
                    'task_date' => $task->task_date,
                    'assignees' => $assignees,
                ];
            });
    }

    #[Computed]
    public function taskStats()
    {
        $tasks = $this->tasks;

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
            'tasks' => $this->tasks,
            'taskStats' => $this->taskStats,
        ]);
    }
}
