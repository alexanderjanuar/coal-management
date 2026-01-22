<?php

namespace App\Livewire\Dashboard\Widgets;

use App\Models\DailyTask;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;

#[Lazy]
class DailyTasksWidget extends Component
{
    public int $limit = 10;
    
    /**
     * Get user's daily tasks
     */
    #[Computed]
    public function dailyTasks()
    {
        $user = auth()->user();
        $today = today();

        return DailyTask::with(['project:id,name'])
            ->select([
                'id',
                'title',
                'project_id',
                'status',
                'priority',
                'task_date',
                'start_task_date',
                'created_by',
                'description'
            ])
            ->where(function($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhereHas('assignedUsers', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->where(function($dateQuery) use ($today) {
                $dateQuery->where(function($q) use ($today) {
                    $q->where('start_task_date', '<=', $today)
                      ->where('task_date', '>=', $today);
                })->orWhere(function($q) use ($today) {
                    $q->where('task_date', $today)
                      ->whereNull('start_task_date');
                });
            })
            ->orderByRaw("
                CASE 
                    WHEN status = 'in_progress' THEN 0
                    WHEN status = 'draft' THEN 1
                    WHEN priority = 'urgent' THEN 2
                    WHEN priority = 'high' THEN 3
                    WHEN priority = 'normal' THEN 4
                    ELSE 5
                END
            ")
            ->orderBy('task_date')
            ->limit($this->limit)
            ->get()
            ->map(function($task) use ($user) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'project' => $task->project ? ['name' => $task->project->name] : null,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'task_date' => $task->task_date,
                    'start_task_date' => $task->start_task_date,
                    'is_owner' => $task->created_by === $user->id,
                    'description' => $task->description,
                ];
            });
    }

    /**
     * Calculate statistics
     */
    #[Computed]
    public function stats()
    {
        $tasks = $this->dailyTasks;
        
        return [
            'total' => $tasks->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'draft' => $tasks->where('status', 'draft')->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'pending' => $tasks->whereIn('status', ['pending', 'todo'])->count(),
            'urgent' => $tasks->where('priority', 'urgent')->count(),
            'high' => $tasks->where('priority', 'high')->count(),
        ];
    }

    /**
     * Placeholder for lazy loading
     */
    public function placeholder()
    {
        return <<<'HTML'
        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="animate-pulse">
                <div class="flex items-center gap-x-3 mb-4">
                    <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-32"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="h-20 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    <div class="h-20 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                </div>
                <div class="space-y-2">
                    <div class="h-16 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    <div class="h-16 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    <div class="h-16 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                </div>
            </div>
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.daily-tasks-widget', [
            'tasks' => $this->dailyTasks,
            'stats' => $this->stats,
        ]);
    }
}