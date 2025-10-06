<?php
// app/Livewire/DailyTask/Components/DailyTaskListComponent.php

namespace App\Livewire\DailyTask\Components;

use App\Models\DailyTask;
use App\Models\User;
use App\Models\Project;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Services\DailyTask\DailyTaskList\TaskGroupingService;
use App\Services\DailyTask\DailyTaskList\TaskFilterService;

class DailyTaskListComponent extends Component
{
    use WithPagination;

    protected TaskGroupingService $groupingService;
    protected TaskFilterService $filterService;

    // Filter state
    public array $currentFilters = [
        'search' => '',
        'date' => null,
        'date_start' => null,
        'date_end' => null,
        'status' => [],
        'priority' => [],
        'project' => [],
        'assignee' => [],
        'group_by' => 'status',
        'view_mode' => 'list',
        'sort_by' => 'task_date',
        'sort_direction' => 'desc',
    ];

    // Task creation state
    public array $creatingNewTasks = [];
    public array $newTaskData = [];
    public ?string $editingGroup = null;
    
    // Pagination settings
    public int $perPage = 20;
    public array $perPageOptions = [10, 20, 50, 100];
    
    // Load more settings untuk grouped view
    public array $groupLoadedCounts = [];
    public int $initialGroupLoad = 10;
    public int $groupLoadIncrement = 5;

    protected $listeners = [
        'taskUpdated' => 'refreshTasks',
        'task-created' => 'refreshTasks',
        'taskStatusChanged' => 'refreshTasks',
        'taskDeleted' => 'refreshTasks',
        'subtaskAdded' => 'refreshTasks',
        'subtaskUpdated' => 'refreshTasks',
        'cancelNewTask' => 'cancelNewTask',
        'filtersChanged' => 'updateFilters',
    ];

    public function boot(TaskGroupingService $groupingService, TaskFilterService $filterService)
    {
        $this->groupingService = $groupingService;
        $this->filterService = $filterService;
    }

    public function mount(): void
    {
        $this->currentFilters = [
            'search' => '',
            'date' => null,
            'date_start' => null,
            'date_end' => null,
            'status' => [],
            'priority' => [],
            'project' => [],
            'assignee' => [auth()->id()],
            'group_by' => 'status',
            'view_mode' => 'list',
            'sort_by' => 'priority',
            'sort_direction' => 'desc',
        ];
    }

    /**
     * Update filters from filter component
     */
    public function updateFilters(array $filters): void
    {
        $this->currentFilters = array_merge($this->currentFilters, $filters);
        $this->resetPage();
        $this->groupLoadedCounts = [];
        $this->dispatch('$refresh');
    }

    /**
     * Refresh tasks list
     */
    public function refreshTasks(): void
    {
        unset($this->tasksQuery);
        unset($this->groupedTasks);
        $this->resetPage();
        $this->groupLoadedCounts = [];
        $this->dispatch('$refresh');
    }

    /**
     * Get base query with eager loading
     */
    #[Computed]
    public function tasksQuery()
    {
        $filters = $this->currentFilters;
        
        $query = DailyTask::query()
            ->with([
                'project:id,name,client_id',
                'project.client:id,name',
                'creator:id,name,avatar_url',
                'assignedUsers:id,name,avatar_url',
                'subtasks:id,daily_task_id,title,status'
            ])
            ->select([
                'id', 'title', 'description', 'status', 'priority', 
                'task_date', 'start_task_date', 'project_id', 'created_by',
                'created_at', 'updated_at'
            ]);
        
        $query = $this->filterService->apply($query, $filters);
        $query = $this->filterService->applySorting($query, $filters['sort_by'], $filters['sort_direction']);
        
        return $query;
    }

    /**
     * Get paginated tasks for ungrouped view
     */
    public function getTasks()
    {
        return $this->tasksQuery->paginate($this->perPage);
    }

    /**
     * Get grouped tasks
     */
    #[Computed]
    public function groupedTasks(): Collection
    {
        $tasks = $this->tasksQuery->get();
        $groupBy = $this->currentFilters['group_by'];
        
        return $this->groupingService->group($tasks, $groupBy);
    }

    /**
     * Get limited tasks for a specific group
     */
    public function getGroupTasks(string $groupKey): Collection
    {
        $loadedCount = $this->groupLoadedCounts[$groupKey] ?? $this->initialGroupLoad;
        
        if (!isset($this->groupedTasks[$groupKey])) {
            return collect();
        }
        
        return $this->groupedTasks[$groupKey]->take($loadedCount);
    }

    /**
     * Load more tasks in a specific group
     */
    public function loadMoreInGroup(string $groupKey): void
    {
        $currentCount = $this->groupLoadedCounts[$groupKey] ?? $this->initialGroupLoad;
        $this->groupLoadedCounts[$groupKey] = $currentCount + $this->groupLoadIncrement;
    }

    /**
     * Check if group has more tasks to load
     */
    public function hasMoreInGroup(string $groupKey): bool
    {
        $loadedCount = $this->groupLoadedCounts[$groupKey] ?? $this->initialGroupLoad;
        $totalCount = $this->groupedTasks[$groupKey]->count() ?? 0;
        
        return $loadedCount < $totalCount;
    }

    /**
     * Get remaining count for a group
     */
    public function getRemainingCount(string $groupKey): int
    {
        $loadedCount = $this->groupLoadedCounts[$groupKey] ?? $this->initialGroupLoad;
        $totalCount = $this->groupedTasks[$groupKey]->count() ?? 0;
        
        return max(0, $totalCount - $loadedCount);
    }

    /**
     * Update per page setting
     */
    public function updatePerPage(int $perPage): void
    {
        $this->perPage = $perPage;
        $this->resetPage();
    }

    /**
     * Update task status
     */
    public function updateTaskStatus(int $taskId, string $status): void
    {
        $task = DailyTask::find($taskId);
        
        if (!$task) {
            Notification::make()
                ->title('Error')
                ->body('Task tidak ditemukan')
                ->danger()
                ->send();
            return;
        }

        $task->update(['status' => $status]);
        
        Notification::make()
            ->title('Status Diperbarui')
            ->body("Status task diubah menjadi " . ucfirst($status))
            ->success()
            ->send();
            
        $this->dispatch('taskStatusChanged');
    }

    /**
     * Sort by field
     */
    public function sortBy(string $field): void
    {
        if ($this->currentFilters['sort_by'] === $field) {
            $this->currentFilters['sort_direction'] = $this->currentFilters['sort_direction'] === 'asc' ? 'desc' : 'asc';
        } else {
            $this->currentFilters['sort_by'] = $field;
            $this->currentFilters['sort_direction'] = 'asc';
        }
        
        $this->resetPage();
    }

    /**
     * Open task detail modal
     */
    public function openTaskDetail(int $taskId): void
    {
        $this->dispatch('openTaskDetailModal', taskId: $taskId);
    }

    /**
     * Start creating new task in group
     */
    public function startCreatingTask(string $groupType, string $groupValue): void
    {
        $groupKey = $this->getGroupKey($groupType, $groupValue);
        
        // Reset all creation states
        $this->creatingNewTasks = [];
        $this->newTaskData = [];
        $this->creatingNewTasks[$groupKey] = true;
        $this->editingGroup = $groupKey;
        
        // Get defaults from group and filters
        $groupDefaults = $this->getDefaultsForGroup($groupType, $groupValue);
        $filterDefaults = $this->getDefaultsFromFilters($groupType);
        
        $this->newTaskData[$groupKey] = array_merge([
            'title' => '',
            'task_date' => today(),
            'start_task_date' => today(),
            'status' => 'pending',
            'priority' => 'normal',
            'project_id' => null,
            'assigned_users' => [],
        ], $filterDefaults, $groupDefaults);
    }

    /**
     * Get default values based on group
     */
    private function getDefaultsForGroup(string $groupType, string $groupValue): array
    {
        $defaults = [
            'task_date' => today(),
            'start_task_date' => today(),
        ];
        
        if ($groupType === 'status') {
            $defaults['status'] = strtolower(str_replace(' ', '_', $groupValue));
        } elseif ($groupType === 'priority') {
            $defaults['priority'] = strtolower($groupValue);
        } elseif ($groupType === 'project' && $groupValue !== 'No Project') {
            $projectId = Project::where('name', $groupValue)->first()?->id;
            if ($projectId) {
                $defaults['project_id'] = $projectId;
            }
        } elseif ($groupType === 'assignee' && $groupValue !== 'Unassigned' && !str_contains($groupValue, '+')) {
            $userId = User::where('name', $groupValue)->first()?->id;
            if ($userId) {
                $defaults['assigned_users'] = [$userId];
            }
        }
        
        return $defaults;
    }

    /**
     * Get default values from current filters
     */
    private function getDefaultsFromFilters(string $excludeGroupType): array
    {
        $defaults = [];
        $filters = $this->currentFilters;
        
        if ($excludeGroupType !== 'assignee' && !empty($filters['assignee'])) {
            $defaults['assigned_users'] = $filters['assignee'];
        }
        
        if ($excludeGroupType !== 'project' && !empty($filters['project']) && count($filters['project']) === 1) {
            $defaults['project_id'] = $filters['project'][0];
        }
        
        if ($excludeGroupType !== 'status' && !empty($filters['status']) && count($filters['status']) === 1) {
            $defaults['status'] = $filters['status'][0];
        }
        
        if ($excludeGroupType !== 'priority' && !empty($filters['priority']) && count($filters['priority']) === 1) {
            $defaults['priority'] = $filters['priority'][0];
        }
        
        return $defaults;
    }

    /**
     * Save new task
     */
    public function saveNewTask(string $groupKey): void
    {
        if (!isset($this->newTaskData[$groupKey]) || empty($this->newTaskData[$groupKey]['title'])) {
            Notification::make()
                ->title('Error')
                ->body('Judul task tidak boleh kosong')
                ->danger()
                ->send();
            return;
        }

        $data = $this->newTaskData[$groupKey];
        
        $task = DailyTask::create([
            'title' => $data['title'],
            'status' => $data['status'] ?? 'pending',
            'priority' => $data['priority'] ?? 'normal',
            'task_date' => $data['task_date'] ?? today(),
            'start_task_date' => $data['start_task_date'] ?? today(),
            'project_id' => $data['project_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        if (!empty($data['assigned_users']) && is_array($data['assigned_users'])) {
            $task->assignedUsers()->sync($data['assigned_users']);
        }

        unset($this->creatingNewTasks[$groupKey]);
        unset($this->newTaskData[$groupKey]);
        $this->editingGroup = null;

        Notification::make()
            ->title('Task Berhasil Dibuat')
            ->body("Task '{$task->title}' berhasil dibuat")
            ->success()
            ->send();

        $this->refreshTasks();
    }

    /**
     * Cancel new task creation
     */
    public function cancelNewTask(string $groupKey = null): void
    {
        if ($groupKey) {
            unset($this->creatingNewTasks[$groupKey]);
            unset($this->newTaskData[$groupKey]);
        } else {
            $this->creatingNewTasks = [];
            $this->newTaskData = [];
        }
        
        $this->editingGroup = null;
    }

    /**
     * Check if creating task in group
     */
    public function isCreatingTask(string $groupType, string $groupValue): bool
    {
        $groupKey = $this->getGroupKey($groupType, $groupValue);
        return isset($this->creatingNewTasks[$groupKey]) && $this->creatingNewTasks[$groupKey];
    }

    /**
     * Get group key for state management
     */
    public function getGroupKey(string $groupType, string $groupValue): string
    {
        return $groupType . '_' . str_replace([' ', '+'], ['_', '_plus_'], $groupValue);
    }

    /**
     * Get available projects
     */
    public function getProjectOptions(): array
    {
        return Project::pluck('name', 'id')->toArray();
    }

    /**
     * Get available users
     */
    public function getUserOptions(): array
    {
        return User::orderBy('name')->pluck('name', 'id')->toArray();
    }

    /**
     * Get total tasks count
     */
    public function getTotalTasksCount(): int
    {
        return $this->tasksQuery->count();
    }

    /**
     * Get current view mode
     */
    public function getCurrentViewMode(): string
    {
        return $this->currentFilters['view_mode'] ?? 'list';
    }

    /**
     * Get current group by
     */
    public function getCurrentGroupBy(): string
    {
        return $this->currentFilters['group_by'] ?? 'status';
    }

    /**
     * Get current sort by
     */
    public function getCurrentSortBy(): string
    {
        return $this->currentFilters['sort_by'] ?? 'task_date';
    }

    /**
     * Get current sort direction
     */
    public function getCurrentSortDirection(): string
    {
        return $this->currentFilters['sort_direction'] ?? 'desc';
    }

    /**
     * Render component
     */
    public function render()
    {
        $viewMode = $this->getCurrentViewMode();
        $groupBy = $this->getCurrentGroupBy();
        $totalTasks = $this->getTotalTasksCount();
        
        $this->dispatch('updateTotalTasks', count: $totalTasks);
        
        return view('livewire.daily-task.components.daily-task-list-component', [
            'groupedTasks' => $this->groupedTasks,
            'paginatedTasks' => $viewMode === 'list' && $groupBy === 'none' ? $this->getTasks() : null,
            'totalTasks' => $totalTasks,
            'viewMode' => $viewMode,
            'groupBy' => $groupBy,
            'sortBy' => $this->getCurrentSortBy(),
            'sortDirection' => $this->getCurrentSortDirection(),
        ]);
    }
}