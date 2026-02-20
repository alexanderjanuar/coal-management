<?php
// app/Livewire/DailyTask/Components/DailyTaskListComponent.php

namespace App\Livewire\DailyTask\Components;

use App\Models\DailyTask;
use App\Models\User;
use App\Models\Project;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
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
        'department' => [],
        'position' => [],
        'group_by' => 'status',
        'view_mode' => 'kanban',
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

    // Kanban settings
    public array $kanbanColumns = [
        'pending' => [
            'title' => 'To Do',
            'icon' => 'heroicon-o-queue-list',
            'color' => 'gray',
            'limit' => null
        ],
        'in_progress' => [
            'title' => 'In Progress',
            'icon' => 'heroicon-o-arrow-path',
            'color' => 'blue',
            'limit' => 5
        ],
        'completed' => [
            'title' => 'Done',
            'icon' => 'heroicon-o-check-circle',
            'color' => 'green',
            'limit' => null
        ],
    ];

    public array $creatingInColumn = [];
    public bool $showCompletedTasks = true;
    public int $maxCardsPerColumn = 50;

    // Incremented every time we switch TO kanban — forces a full child re-mount
    public int $kanbanMountKey = 0;

    protected $listeners = [
        'taskUpdated' => 'handleTaskUpdated',
        'task-created' => 'handleTaskCreated',
        'taskStatusChanged' => 'handleTaskStatusChanged',
        'taskDeleted' => 'handleTaskDeleted',
        'subtaskAdded' => 'handleSubtaskUpdated',
        'subtaskUpdated' => 'handleSubtaskUpdated',
        'cancelNewTask' => 'cancelNewTask',
        'filtersChanged' => 'updateFilters',
        'taskMoved' => 'handleTaskMoved',
        'switchViewMode' => 'switchViewMode',
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
            'view_mode' => 'kanban',
            'sort_by' => 'priority',
            'sort_direction' => 'desc',
        ];
    }

    /**
     * Update filters from filter component - RESET pagination
     */
    public function updateFilters(array $filters): void
    {
        $previousMode = $this->currentFilters['view_mode'] ?? 'kanban';
        $this->currentFilters = array_merge($this->currentFilters, $filters);
        $newMode = $this->currentFilters['view_mode'] ?? 'kanban';

        // Force child re-mount by bumping the key whenever we switch back to kanban
        if ($previousMode !== 'kanban' && $newMode === 'kanban') {
            $this->kanbanMountKey++;
        }

        $this->resetPage();
        $this->groupLoadedCounts = [];

        // Clear cached properties when switching views
        unset($this->tasksQuery);
        unset($this->groupedTasks);
    }

    /**
     * Switch view mode directly — called from the toggle buttons
     */
    #[On('switchViewMode')]
    public function switchViewMode(string $mode): void
    {
        $previousMode = $this->currentFilters['view_mode'] ?? 'kanban';
        $this->currentFilters['view_mode'] = $mode;

        // Force child re-mount by bumping the key whenever we switch back to kanban
        if ($previousMode !== 'kanban' && $mode === 'kanban') {
            $this->kanbanMountKey++;
        }

        $this->resetPage();
        $this->groupLoadedCounts = [];

        // Clear cached properties when switching views
        unset($this->tasksQuery);
        unset($this->groupedTasks);
    }

    /**
     * Handle task updated - DON'T reset pagination
     */
    public function handleTaskUpdated(): void
    {
        unset($this->tasksQuery);
        unset($this->groupedTasks);
        unset($this->kanbanTasks);
        $this->dispatch('$refresh');
    }

    /**
     * Handle task created - DON'T reset pagination
     */
    public function handleTaskCreated(): void
    {
        unset($this->tasksQuery);
        unset($this->groupedTasks);
        unset($this->kanbanTasks);
        $this->dispatch('$refresh');
    }

    /**
     * Handle task status changed - DON'T reset pagination
     */
    public function handleTaskStatusChanged(): void
    {
        unset($this->tasksQuery);
        unset($this->groupedTasks);
        unset($this->kanbanTasks);
        $this->dispatch('$refresh');
    }

    /**
     * Handle task deleted - DON'T reset pagination
     */
    public function handleTaskDeleted(): void
    {
        unset($this->tasksQuery);
        unset($this->groupedTasks);
        unset($this->kanbanTasks);
        $this->dispatch('$refresh');
    }

    /**
     * Handle subtask updated - DON'T reset pagination
     */
    public function handleSubtaskUpdated(): void
    {
        unset($this->tasksQuery);
        unset($this->groupedTasks);
        unset($this->kanbanTasks);
        $this->dispatch('$refresh');
    }

    /**
     * Refresh tasks - Manual refresh, RESET pagination
     */
    public function refreshTasks(): void
    {
        unset($this->tasksQuery);
        unset($this->groupedTasks);
        unset($this->kanbanTasks);
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
                'id',
                'title',
                'description',
                'status',
                'priority',
                'task_date',
                'start_task_date',
                'project_id',
                'created_by',
                'created_at',
                'updated_at'
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
     * Get tasks grouped by status for Kanban
     */
    #[Computed]
    public function kanbanTasks(): Collection
    {
        $tasks = $this->tasksQuery->get();
        $grouped = $tasks->groupBy('status');

        $result = collect();
        foreach (array_keys($this->kanbanColumns) as $status) {
            $columnTasks = $grouped->get($status, collect());

            if ($columnTasks->count() > $this->maxCardsPerColumn) {
                $columnTasks = $columnTasks->take($this->maxCardsPerColumn);
            }

            $result->put($status, $columnTasks);
        }

        return $result;
    }

    /**
     * Get column statistics for Kanban
     */
    public function getColumnStats(string $status): array
    {
        $tasks = $this->kanbanTasks->get($status, collect());
        $total = $tasks->count();

        return [
            'total' => $total,
            'urgent' => $tasks->where('priority', 'urgent')->count(),
            'high' => $tasks->where('priority', 'high')->count(),
            'overdue' => $tasks->filter(function ($task) {
                return $task->task_date && $task->task_date->isPast() && $task->status !== 'completed';
            })->count(),
            'limit' => $this->kanbanColumns[$status]['limit'] ?? null,
            'isAtLimit' => $this->kanbanColumns[$status]['limit']
                ? $total >= $this->kanbanColumns[$status]['limit']
                : false
        ];
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
     * Load more tasks in a specific group - DON'T reset pagination
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
     * Update per page setting - RESET pagination
     */
    public function updatePerPage(int $perPage): void
    {
        $this->perPage = $perPage;
        $this->resetPage();
    }

    /**
     * Update task status - DON'T reset pagination
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

        $this->handleTaskStatusChanged();
    }

    /**
     * Sort by field - RESET pagination
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
     * Open task detail modal - DON'T reset pagination
     */
    public function openTaskDetail(int $taskId): void
    {
        $this->dispatch('openTaskDetailModal', taskId: $taskId);
    }

    /**
     * Start creating new task in group - DON'T reset pagination
     */
    public function startCreatingTask(string $groupType, string $groupValue): void
    {
        $groupKey = $this->getGroupKey($groupType, $groupValue);

        $this->creatingNewTasks = [];
        $this->newTaskData = [];
        $this->creatingNewTasks[$groupKey] = true;
        $this->editingGroup = $groupKey;

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
     * Save new task - DON'T reset pagination
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

        $this->handleTaskCreated();
    }

    /**
     * Cancel new task creation - DON'T reset pagination
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
     * Handle task moved in Kanban
     */
    #[On('taskMoved')]
    public function handleTaskMoved(int $taskId, string $newStatus, int $newPosition): void
    {
        try {
            $task = DailyTask::find($taskId);

            if (!$task) {
                throw new \Exception('Task not found');
            }

            // Check WIP limit
            if ($this->kanbanColumns[$newStatus]['limit'] ?? null) {
                $currentCount = $this->kanbanTasks->get($newStatus, collect())->count();
                if ($currentCount >= $this->kanbanColumns[$newStatus]['limit']) {
                    Notification::make()
                        ->title('WIP Limit Reached')
                        ->body("Column '{$this->kanbanColumns[$newStatus]['title']}' has reached its work-in-progress limit")
                        ->warning()
                        ->duration(5000)
                        ->send();

                    $this->dispatch('revertTaskMove', taskId: $taskId);
                    return;
                }
            }

            $oldStatus = $task->status;
            $task->update(['status' => $newStatus]);

            if ($newStatus === 'in_progress' && !$task->start_task_date) {
                $task->update(['start_task_date' => now()]);
            }

            Notification::make()
                ->title('Task Moved')
                ->body("Task '{$task->title}' moved from {$this->kanbanColumns[$oldStatus]['title']} to {$this->kanbanColumns[$newStatus]['title']}")
                ->success()
                ->send();

            $this->handleTaskStatusChanged();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Moving Task')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->dispatch('revertTaskMove', taskId: $taskId);
        }
    }

    /**
     * Start creating task in Kanban column
     */
    public function startCreatingKanbanTask(string $status): void
    {
        if ($this->kanbanColumns[$status]['limit'] ?? null) {
            $currentCount = $this->kanbanTasks->get($status, collect())->count();
            if ($currentCount >= $this->kanbanColumns[$status]['limit']) {
                Notification::make()
                    ->title('WIP Limit Reached')
                    ->body("Cannot add more tasks to '{$this->kanbanColumns[$status]['title']}' column")
                    ->warning()
                    ->send();
                return;
            }
        }

        $this->creatingInColumn = [$status => true];
        $this->newTaskData[$status] = [
            'title' => '',
            'status' => $status,
            'priority' => 'normal',
            'task_date' => today(),
            'start_task_date' => $status === 'in_progress' ? today() : null,
            'project_id' => null,
            'assigned_users' => !empty($this->currentFilters['assignee'])
                ? $this->currentFilters['assignee']
                : [auth()->id()],
        ];
    }

    /**
     * Save new Kanban task
     */
    public function saveKanbanTask(string $status): void
    {
        if (empty($this->newTaskData[$status]['title'])) {
            Notification::make()
                ->title('Error')
                ->body('Task title is required')
                ->danger()
                ->send();
            return;
        }

        $data = $this->newTaskData[$status];

        $task = DailyTask::create([
            'title' => $data['title'],
            'status' => $data['status'],
            'priority' => $data['priority'],
            'task_date' => $data['task_date'],
            'start_task_date' => $data['start_task_date'],
            'project_id' => $data['project_id'],
            'created_by' => auth()->id(),
        ]);

        if (!empty($data['assigned_users'])) {
            $task->assignedUsers()->sync($data['assigned_users']);
        }

        unset($this->creatingInColumn[$status]);
        unset($this->newTaskData[$status]);

        Notification::make()
            ->title('Task Created')
            ->body("Task '{$task->title}' created successfully")
            ->success()
            ->send();

        $this->handleTaskCreated();
    }

    /**
     * Cancel Kanban task creation
     */
    public function cancelKanbanTask(string $status): void
    {
        unset($this->creatingInColumn[$status]);
        unset($this->newTaskData[$status]);
    }

    /**
     * Toggle show completed tasks
     */
    public function toggleCompletedTasks(): void
    {
        $this->showCompletedTasks = !$this->showCompletedTasks;
    }

    /**
     * Get available projects
     */
    public function getProjectOptions(): array
    {
        return Project::pluck('name', 'id')->toArray();
    }

    /**
     * Get available users (excluding clients)
     */
    public function getUserOptions(): array
    {
        return cache()->remember(
            'assignable_users_list',
            600,
            fn() => User::whereDoesntHave('roles', function ($query) {
                $query->where('name', 'client');
            })
                ->where('is_active', true)
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray()
        );
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
            'kanbanTasks' => $viewMode === 'kanban' ? $this->kanbanTasks : collect(),
            'kanbanColumns' => $this->kanbanColumns,
        ]);
    }
}