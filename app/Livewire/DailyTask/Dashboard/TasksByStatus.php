<?php

namespace App\Livewire\DailyTask\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\DailyTask;
use Illuminate\Support\Carbon;

class TasksByStatus extends Component
{
    #[Url(as: 'status', history: true)]
    public string $activeTab = 'all';

    // Filters from dashboard filter widget
    public string $dateRange = 'today';
    public Carbon $fromDate;
    public Carbon $toDate;
    public ?string $department = null;
    public ?string $position = null;

    public ?DailyTask $task = null;

    // Pagination per tab
    public array $perPage = [
        'all' => 6,
        'pending' => 6,
        'in_progress' => 6,
        'completed' => 6,
        'cancelled' => 6,
    ];

    // Loading state
    public bool $isLoading = false;

    public function mount(): void
    {
        $this->fromDate = now()->startOfDay();
        $this->toDate = now()->endOfDay();
        $this->dateRange = 'today';
    }

    #[On('filtersUpdated')]
    public function updateFilters(array $filters): void
    {
        $this->isLoading = true;
        
        $this->dateRange = $filters['date_range'] ?? 'today';
        $this->fromDate = Carbon::parse($filters['from'])->startOfDay();
        $this->toDate = Carbon::parse($filters['to'])->endOfDay();
        $this->department = $filters['department'] ?? null;
        $this->position = $filters['position'] ?? null;
        
        $this->resetAllPages();
        
        $this->isLoading = false;
    }

    #[On('updateDateRange')]
    public function updateDateRange(string $range, string $from, string $to): void
    {
        $this->isLoading = true;
        
        $this->dateRange = $range;
        $this->fromDate = Carbon::parse($from)->startOfDay();
        $this->toDate = Carbon::parse($to)->endOfDay();
        
        $this->resetAllPages();
        
        $this->isLoading = false;
    }

    #[On('updateDepartment')]
    public function updateDepartment(?string $department): void
    {
        $this->isLoading = true;
        $this->department = $department;
        $this->resetAllPages();
        $this->isLoading = false;
    }

    #[On('updatePosition')]
    public function updatePosition(?string $position): void
    {
        $this->isLoading = true;
        $this->position = $position;
        $this->resetAllPages();
        $this->isLoading = false;
    }

    public function changeTab(string $status): void
    {
        $this->isLoading = true;
        $this->activeTab = $status;
        $this->resetPage($status);
        $this->isLoading = false;
    }

    public function changeTaskStatus(int $taskId, string $newStatus): void
    {
        $this->isLoading = true;
        
        try {
            $task = DailyTask::findOrFail($taskId);
            $task->update(['status' => $newStatus]);
            
            // Refresh counts
            unset($this->statusCounts);
            
            $this->dispatch('task-status-updated', [
                'taskId' => $taskId,
                'status' => $newStatus
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Gagal mengubah status: ' . $e->getMessage()
            ]);
        }
        
        $this->isLoading = false;
    }

    public function loadMore(string $status): void
    {
        $this->perPage[$status] += 6;
    }

    public function resetPage(string $status): void
    {
        $this->perPage[$status] = 6;
    }

    public function resetAllPages(): void
    {
        foreach (array_keys($this->perPage) as $status) {
            $this->perPage[$status] = 6;
        }
    }

    #[Computed]
    public function allTasks()
    {
        return $this->getTasksQuery()
            ->take($this->perPage['all'])
            ->get();
    }

    #[Computed]
    public function pendingTasks()
    {
        return $this->getTasksQuery('pending')
            ->take($this->perPage['pending'])
            ->get();
    }

    #[Computed]
    public function inProgressTasks()
    {
        return $this->getTasksQuery('in_progress')
            ->take($this->perPage['in_progress'])
            ->get();
    }

    #[Computed]
    public function completedTasks()
    {
        return $this->getTasksQuery('completed')
            ->take($this->perPage['completed'])
            ->get();
    }

    #[Computed]
    public function cancelledTasks()
    {
        return $this->getTasksQuery('cancelled')
            ->take($this->perPage['cancelled'])
            ->get();
    }

    #[Computed]
    public function statusCounts()
    {
        $baseQuery = DailyTask::query();
        $this->applyFilters($baseQuery);

        return [
            'all' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
        ];
    }

    public function hasMoreTasks(string $status): bool
    {
        $totalCount = $this->statusCounts[$status] ?? 0;
        $currentCount = $this->perPage[$status];
        
        return $currentCount < $totalCount;
    }

    protected function getTasksQuery(?string $status = null)
    {
        $query = DailyTask::with(['assignedUsers', 'project.client', 'subtasks', 'creator'])
            ->latest('task_date')
            ->latest('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        $this->applyFilters($query);

        return $query;
    }

    protected function applyFilters($query): void
    {
        $query->whereBetween('task_date', [
            $this->fromDate->format('Y-m-d'),
            $this->toDate->format('Y-m-d')
        ]);

        if ($this->department) {
            $query->whereHas('assignedUsers', function ($q) {
                $q->where('department', $this->department);
            });
        }

        if ($this->position) {
            $query->whereHas('assignedUsers', function ($q) {
                $q->where('position', $this->position);
            });
        }
    }

    public function getTabsConfig(): array
    {
        return [
            'all' => [
                'label' => 'Semua Tugas',
                'icon' => 'heroicon-o-queue-list',
            ],
            'pending' => [
                'label' => 'Tertunda',
                'icon' => 'heroicon-o-clock',
            ],
            'in_progress' => [
                'label' => 'Sedang Dikerjakan',
                'icon' => 'heroicon-o-arrow-path',
            ],
            'completed' => [
                'label' => 'Selesai',
                'icon' => 'heroicon-o-check-circle',
            ],
            'cancelled' => [
                'label' => 'Dibatalkan',
                'icon' => 'heroicon-o-x-circle',
            ],
        ];
    }

    public function getStatusOptions(): array
    {
        return [
            'pending' => [
                'label' => 'Tertunda',
                'color' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300 hover:bg-orange-200 dark:hover:bg-orange-900/50',
            ],
            'in_progress' => [
                'label' => 'Dikerjakan',
                'color' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-900/50',
            ],
            'completed' => [
                'label' => 'Selesai',
                'color' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 hover:bg-green-200 dark:hover:bg-green-900/50',
            ],
            'cancelled' => [
                'label' => 'Dibatalkan',
                'color' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-900/50',
            ],
        ];
    }

    public function openTaskDetailModal(int $taskId): void
    {
        $this->task = DailyTask::with([
            'project.client', 
            'creator', 
            'assignedUsers', 
            'subtasks',
            'comments.user'
        ])->find($taskId);

        @dd($this->task);
        
        if ($this->task) {
            $this->dispatch('open-modal', id: 'task-detail-modal');
        }
    }

    public function render()
    {
        return view('livewire.daily-task.dashboard.tasks-by-status', [
            'tabs' => $this->getTabsConfig(),
            'statusOptions' => $this->getStatusOptions(),
        ]);
    }
}