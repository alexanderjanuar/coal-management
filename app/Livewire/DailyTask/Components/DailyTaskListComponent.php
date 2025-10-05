<?php

namespace App\Livewire\DailyTask\Components;

use App\Models\DailyTask;
use App\Models\User;
use App\Models\Project;
use Livewire\Component;
use Livewire\WithPagination;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DailyTaskListComponent extends Component
{
    use WithPagination;

    // Current filters applied (received from filter component)
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

    // Pagination
    public int $perPage = 20;

    // Add listeners for child components and page events
    protected $listeners = [
        'taskUpdated' => 'refreshTasks',
        'task-created' => 'refreshTasks',
        'taskStatusChanged' => 'refreshTasks',
        'taskDeleted' => 'refreshTasks',
        'subtaskAdded' => 'refreshTasks',
        'subtaskUpdated' => 'refreshTasks',
        'cancelNewTask' => 'cancelNewTask',
        'filtersChanged' => 'updateFilters', // Listen to filter changes
    ];

    public function mount(): void
    {
        $this->currentFilters = [
            'search' => '',
            'date' => null, // Hapus filter date default
            'date_start' => null,
            'date_end' => null,
            'status' => [],
            'priority' => [],
            'project' => [],
            'assignee' => [auth()->id()], // Filter hanya untuk user yang sedang login
            'group_by' => 'status',
            'view_mode' => 'list',
            'sort_by' => 'priority',
            'sort_direction' => 'desc',
        ];
    }

    private function getPriorityOrder(): array
    {
        return [
            'urgent' => 4,
            'high' => 3,
            'normal' => 2,
            'low' => 1
        ];
    }

    /**
     * Update filters when received from filter component
     */
    public function updateFilters(array $filters): void
    {
        $this->currentFilters = array_merge($this->currentFilters, $filters);
        $this->resetPage();
        $this->dispatch('$refresh');
    }

    /**
     * Refresh tasks when updates occur
     */
    public function refreshTasks(): void
    {
        $this->resetPage();
        $this->dispatch('$refresh');
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
            ->body("Status task diubah menjadi " . $this->getStatusLabel($status))
            ->success()
            ->send();
            
        $this->dispatch('taskStatusChanged');
    }

    /**
     * Change sort order
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
     * Get tasks query with all filters applied
     */
    public function getTasksQuery()
    {
        $filters = $this->currentFilters;
        
        $query = DailyTask::query()->with(['project', 'creator', 'assignedUsers', 'subtasks']);
            
        // Apply search filter
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Apply date filters - Ubah untuk menggunakan start_task_date
        if (!empty($filters['date'])) {
            $date = $filters['date'];
            if ($date instanceof \Carbon\Carbon) {
                $query->whereDate('start_task_date', $date->format('Y-m-d'));
            } elseif (is_string($date)) {
                try {
                    $carbonDate = Carbon::parse($date);
                    $query->whereDate('start_task_date', $carbonDate->format('Y-m-d'));
                } catch (\Exception $e) {
                    // Handle silently, skip invalid dates
                }
            }
        }
        
        // Apply date range filters - Ubah untuk menggunakan start_task_date
        if (!empty($filters['date_start']) || !empty($filters['date_end'])) {
            if (!empty($filters['date_start'])) {
                $startDate = $filters['date_start'];
                if ($startDate instanceof \Carbon\Carbon) {
                    $query->whereDate('start_task_date', '>=', $startDate->format('Y-m-d'));
                } elseif (is_string($startDate)) {
                    try {
                        $carbonStartDate = Carbon::parse($startDate);
                        $query->whereDate('start_task_date', '>=', $carbonStartDate->format('Y-m-d'));
                    } catch (\Exception $e) {
                        // Handle silently
                    }
                }
            }
            
            if (!empty($filters['date_end'])) {
                $endDate = $filters['date_end'];
                if ($endDate instanceof \Carbon\Carbon) {
                    $query->whereDate('start_task_date', '<=', $endDate->format('Y-m-d'));
                } elseif (is_string($endDate)) {
                    try {
                        $carbonEndDate = Carbon::parse($endDate);
                        $query->whereDate('start_task_date', '<=', $carbonEndDate->format('Y-m-d'));
                    } catch (\Exception $e) {
                        // Handle silently
                    }
                }
            }
        }
        
        // Apply status filter
        if (!empty($filters['status'])) {
        $query->whereIn('status', $filters['status']);
        }
        
        // Apply priority filter
        if (!empty($filters['priority'])) {
            $query->whereIn('priority', $filters['priority']);
        }
        
        // Apply project filter
        if (!empty($filters['project'])) {
            $query->whereIn('project_id', $filters['project']);
        }
        
        // Apply assignee filter
        if (!empty($filters['assignee'])) {
            $query->whereHas('assignedUsers', function ($q) use ($filters) {
                $q->whereIn('users.id', $filters['assignee']);
            });
        }
        
        // Apply sorting
        $sortBy = $filters['sort_by'];
        $sortDirection = $filters['sort_direction'];

        if ($sortBy === 'priority') {
            // Custom priority sorting berdasarkan order yang sudah ditentukan
            if ($sortDirection === 'desc') {
                // Urgent first (highest priority)
                $query->orderByRaw("CASE priority 
                    WHEN 'urgent' THEN 4 
                    WHEN 'high' THEN 3 
                    WHEN 'normal' THEN 2 
                    WHEN 'low' THEN 1 
                    ELSE 0 END DESC");
            } else {
                // Low first (ascending)
                $query->orderByRaw("CASE priority 
                    WHEN 'urgent' THEN 4 
                    WHEN 'high' THEN 3 
                    WHEN 'normal' THEN 2 
                    WHEN 'low' THEN 1 
                    ELSE 0 END ASC");
            }
            // Secondary sort by task_date
            $query->orderBy('task_date', 'asc');
        } else {
            // Default sorting untuk field lain
            $query->orderBy($sortBy, $sortDirection);
        }
        
        return $query;
    }

    /**
     * Get tasks for current page (pagination)
     */
    public function getTasks()
    {
        return $this->getTasksQuery()->paginate($this->perPage);
    }

    /**
     * Get grouped tasks
     */
    public function getGroupedTasks(): Collection
    {
        $filters = $this->currentFilters;
        $groupBy = $filters['group_by'];
        
        // Get the filtered tasks first
        $query = $this->getTasksQuery();
        $tasks = $query->get();
        
        // If no grouping, return all tasks
        if ($groupBy === 'none') {
            return collect(['All Tasks' => $tasks]);
        }

        // Group the already-filtered tasks
        $grouped = $tasks->groupBy(function ($task) use ($groupBy) {
            $groupValue = null;
            
            switch ($groupBy) {
                case 'status':
                    $groupValue = $this->getStatusOptions()[$task->status] ?? ucfirst($task->status);
                    break;
                case 'priority':
                    $groupValue = $this->getPriorityOptions()[$task->priority] ?? ucfirst($task->priority);
                    break;
                case 'project':
                    $groupValue = $task->project?->name ?? 'No Project';
                    break;
                case 'assignee':
                    if (!$task->assignedUsers || $task->assignedUsers->count() === 0) {
                        $groupValue = 'Unassigned';
                    } elseif ($task->assignedUsers->count() === 1) {
                        $groupValue = $task->assignedUsers->first()->name;
                    } else {
                        $groupValue = $task->assignedUsers->first()->name . ' (+' . ($task->assignedUsers->count() - 1) . ' more)';
                    }
                    break;
                case 'date':
                    // Group by date categories based on task_date (deadline)
                    if ($task->status === 'completed') {
                        $groupValue = 'Selesai';
                    } elseif (!$task->task_date) {
                        $groupValue = 'Tanpa Deadline';
                    } elseif ($task->task_date->isPast()) {
                        $groupValue = 'Terlambat';
                    } else {
                        $groupValue = 'Mendatang';
                    }
                    break;
                default:
                    $groupValue = 'All Tasks';
                    break;
            }
            
            return $groupValue;
        });
        
        // Sort groups logically
        $sorted = $grouped->sortKeysUsing(function ($a, $b) use ($groupBy) {
            switch ($groupBy) {
                case 'status':
                    $order = ['Pending', 'In Progress', 'Completed', 'Cancelled'];
                    $aPos = array_search($a, $order);
                    $bPos = array_search($b, $order);
                    if ($aPos !== false && $bPos !== false) {
                        return $aPos <=> $bPos;
                    }
                    break;
                    
                case 'priority':
                    $order = ['Urgent', 'High', 'Normal', 'Low'];
                    $aPos = array_search($a, $order);
                    $bPos = array_search($b, $order);
                    if ($aPos !== false && $bPos !== false) {
                        return $aPos <=> $bPos;
                    }
                    break;
                    
                case 'date':
                    // Custom order for date groups
                    $order = ['Terlambat', 'Mendatang', 'Tanpa Deadline', 'Selesai'];
                    $aPos = array_search($a, $order);
                    $bPos = array_search($b, $order);
                    if ($aPos !== false && $bPos !== false) {
                        return $aPos <=> $bPos;
                    }
                    break;
            }
            
            return strcasecmp($a, $b);
        });

        // Sort tasks within each group by priority jika bukan group by priority
        if ($groupBy !== 'priority') {
            $sorted = $sorted->map(function ($tasks) {
                return $tasks->sortBy(function ($task) {
                    $priorityOrder = ['urgent' => 4, 'high' => 3, 'normal' => 2, 'low' => 1];
                    return -($priorityOrder[$task->priority] ?? 0);
                });
            });
        }
        
        return $sorted;
    }

    /**
     * Handle opening task detail modal
     */
    public function handleOpenTaskDetailModal(int $taskId): void
    {
        $this->dispatch('openTaskDetailModal', taskId: $taskId);
    }

    /**
     * Open task detail modal
     */
    public function openTaskDetail(int $taskId): void
    {
        $this->dispatch('openTaskDetailModal', taskId: $taskId);
    }

    /**
     * Get status options
     */
    public function getStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    /**
     * Get status label
     */
    public function getStatusLabel(string $status): string
    {
        return $this->getStatusOptions()[$status] ?? $status;
    }

    /**
     * Get total tasks count
     */
    public function getTotalTasksCount(): int
    {
        return $this->getTasksQuery()->count();
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
     * Get current sort info
     */
    public function getCurrentSortBy(): string
    {
        return $this->currentFilters['sort_by'] ?? 'task_date';
    }

    public function getCurrentSortDirection(): string
    {
        return $this->currentFilters['sort_direction'] ?? 'desc';
    }

    /**
     * Manual refresh method for external triggers
     */
    public function refresh(): void
    {
        $this->refreshTasks();
    }

    /**
     * Create new task for group
     */
    public function createNewTaskForGroup(string $groupType, string $groupValue): void
    {
        $defaults = $this->getDefaultsForGroup($groupType, $groupValue);
        $this->dispatch('openCreateTaskModal', defaults: $defaults);
    }

    /**
     * Get default values based on group type and value
     */
    private function getDefaultsForGroup(string $groupType, string $groupValue): array
    {
        $defaults = [
            'task_date' => today(),
            'start_task_date' => today(),
        ];
        
        switch ($groupType) {
            case 'status':
                $statusMap = array_flip($this->getStatusOptions());
                if (isset($statusMap[$groupValue])) {
                    $defaults['status'] = $statusMap[$groupValue];
                }
                break;
                
            case 'priority':
                $priorityMap = array_flip($this->getPriorityOptions());
                if (isset($priorityMap[$groupValue])) {
                    $defaults['priority'] = $priorityMap[$groupValue];
                }
                break;
                
            case 'project':
                if ($groupValue !== 'No Project') {
                    $projectId = Project::where('name', $groupValue)->first()?->id;
                    if ($projectId) {
                        $defaults['project_id'] = $projectId;
                    }
                }
                break;
                
            case 'assignee':
                if ($groupValue !== 'Unassigned' && !str_contains($groupValue, '+')) {
                    $userId = User::where('name', $groupValue)->first()?->id;
                    if ($userId) {
                        $defaults['assigned_users'] = [$userId];
                    }
                }
                break;
                
            case 'date':
                // Set defaults based on date category
                switch ($groupValue) {
                    case 'Terlambat':
                        // For overdue, set to yesterday
                        $defaults['task_date'] = today()->subDay();
                        $defaults['start_task_date'] = today();
                        $defaults['status'] = 'pending';
                        break;
                    case 'Mendatang':
                        // For future, set to tomorrow
                        $defaults['task_date'] = today()->addDay();
                        $defaults['start_task_date'] = today();
                        $defaults['status'] = 'pending';
                        break;
                    case 'Tanpa Deadline':
                        // For no due date, set task_date to null
                        $defaults['task_date'] = null;
                        $defaults['start_task_date'] = today();
                        $defaults['status'] = 'pending';
                        break;
                    case 'Selesai':
                        // For done, set status to completed
                        $defaults['task_date'] = today();
                        $defaults['start_task_date'] = today();
                        $defaults['status'] = 'completed';
                        break;
                }
                break;
        }
        
        return $defaults;
    }

    /**
     * Start creating new task for group
     */
    public function startCreatingTask(string $groupType, string $groupValue): void
    {
        $groupKey = $groupType . '_' . str_replace([' ', '+'], ['_', '_plus_'], $groupValue);
        
        // Cancel any other creating tasks
        $this->creatingNewTasks = [];
        $this->newTaskData = [];
        
        // Start creating for this group
        $this->creatingNewTasks[$groupKey] = true;
        $this->editingGroup = $groupKey;
        
        // Set default values based on group
        $groupDefaults = $this->getDefaultsForGroup($groupType, $groupValue);
        
        // Get defaults from current filters
        $filterDefaults = $this->getDefaultsFromFilters($groupType);
        
        // Merge: filter defaults first, then group defaults override
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
     * Get default values from current active filters (excluding group by field)
     */
    private function getDefaultsFromFilters(string $excludeGroupType): array
    {
        $defaults = [];
        $filters = $this->currentFilters;
        
        // Apply assignee filter (if not grouping by assignee)
        if ($excludeGroupType !== 'assignee' && !empty($filters['assignee'])) {
            $defaults['assigned_users'] = $filters['assignee'];
        }
        
        // Apply project filter (if not grouping by project)
        if ($excludeGroupType !== 'project' && !empty($filters['project']) && count($filters['project']) === 1) {
            $defaults['project_id'] = $filters['project'][0];
        }
        
        // Apply status filter (if not grouping by status)
        if ($excludeGroupType !== 'status' && !empty($filters['status']) && count($filters['status']) === 1) {
            $defaults['status'] = $filters['status'][0];
        }
        
        // Apply priority filter (if not grouping by priority)
        if ($excludeGroupType !== 'priority' && !empty($filters['priority']) && count($filters['priority']) === 1) {
            $defaults['priority'] = $filters['priority'][0];
        }
        
        // Apply date filter (if not grouping by date)
        if ($excludeGroupType !== 'date') {
            if (!empty($filters['date'])) {
                $date = $filters['date'];
                if ($date instanceof \Carbon\Carbon) {
                    $defaults['task_date'] = $date;
                    $defaults['start_task_date'] = $date;
                } elseif (is_string($date)) {
                    try {
                        $carbonDate = Carbon::parse($date);
                        $defaults['task_date'] = $carbonDate;
                        $defaults['start_task_date'] = $carbonDate;
                    } catch (\Exception $e) {
                        // Skip invalid dates
                    }
                }
            }
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
        
        // Create task
        $task = DailyTask::create([
            'title' => $data['title'],
            'status' => $data['status'] ?? 'pending',
            'priority' => $data['priority'] ?? 'normal',
            'task_date' => $data['task_date'] ?? today(),
            'start_task_date' => $data['start_task_date'] ?? today(),
            'project_id' => $data['project_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Assign users if any
        if (!empty($data['assigned_users']) && is_array($data['assigned_users'])) {
            $task->assignedUsers()->sync($data['assigned_users']);
        }

        // Clear the creating state
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
     * Cancel creating new task
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
     * Check if group is creating new task
     */
    public function isCreatingTask(string $groupType, string $groupValue): bool
    {
        $groupKey = $groupType . '_' . str_replace([' ', '+'], ['_', '_plus_'], $groupValue);
        return isset($this->creatingNewTasks[$groupKey]) && $this->creatingNewTasks[$groupKey];
    }

    /**
     * Get group key for tracking
     */
    public function getGroupKey(string $groupType, string $groupValue): string
    {
        return $groupType . '_' . str_replace([' ', '+'], ['_', '_plus_'], $groupValue);
    }

    /**
     * Update task priority
     */
    public function updatePriority(int $taskId, string $priority): void
    {
        $task = DailyTask::find($taskId);
        
        if (!$task) {
            return;
        }
        
        $task->update(['priority' => $priority]);
        
        $this->dispatch('taskUpdated');
        
        Notification::make()
            ->title('Priority Diperbarui')
            ->body("Priority diubah menjadi " . ucfirst($priority))
            ->success()
            ->send();
    }

    /**
     * Update task project
     */
    public function updateProject(int $taskId, $projectId): void
    {
        $task = DailyTask::find($taskId);
        
        if (!$task) {
            return;
        }
        
        $task->update(['project_id' => $projectId]);
        
        $this->dispatch('taskUpdated');
        
        Notification::make()
            ->title('Project Diperbarui')
            ->body($projectId ? "Project berhasil diassign" : "Project dihapus")
            ->success()
            ->send();
    }

    /**
     * Assign user to task
     */
    public function assignUser(int $taskId, int $userId): void
    {
        $task = DailyTask::find($taskId);
        
        if (!$task) {
            return;
        }
        
        if (!$task->assignedUsers->contains($userId)) {
            $task->assignedUsers()->attach($userId);
            $task->refresh();
            
            $userName = User::find($userId)?->name ?? 'User';
            
            Notification::make()
                ->title('User Diassign')
                ->body("Task diassign ke {$userName}")
                ->success()
                ->send();
                
            $this->dispatch('taskUpdated');
        }
    }

    /**
     * Unassign user from task
     */
    public function unassignUser(int $taskId, int $userId): void
    {
        $task = DailyTask::find($taskId);
        
        if (!$task) {
            return;
        }
        
        $task->assignedUsers()->detach($userId);
        $task->refresh();
        
        $userName = User::find($userId)?->name ?? 'User';
        
        Notification::make()
            ->title('User Dibatalkan')
            ->body("Assignment ke {$userName} dibatalkan")
            ->success()
            ->send();
            
        $this->dispatch('taskUpdated');
    }

    public function getPriorityOptions(): array
    {
        return [
            'low' => 'Low',
            'normal' => 'Normal',
            'high' => 'High',
            'urgent' => 'Urgent',
        ];
    }

    public function getProjectOptions(): array
    {
        return Project::pluck('name', 'id')->toArray();
    }

    public function getUserOptions(): array
    {
        return User::orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function render()
    {
        $viewMode = $this->getCurrentViewMode();
        $groupBy = $this->getCurrentGroupBy();
        $totalTasks = $this->getTotalTasksCount();
        
        // Update filter component with current total tasks
        $this->dispatch('updateTotalTasks', count: $totalTasks);
        
        return view('livewire.daily-task.components.daily-task-list-component', [
            'groupedTasks' => $this->getGroupedTasks(),
            'paginatedTasks' => $viewMode === 'list' && $groupBy === 'none' ? $this->getTasks() : null,
            'statusOptions' => $this->getStatusOptions(),
            'priorityOptions' => $this->getPriorityOptions(),
            'userOptions' => $this->getUserOptions(),
            'projectOptions' => $this->getProjectOptions(),
            'totalTasks' => $totalTasks,
            'viewMode' => $viewMode,
            'groupBy' => $groupBy,
            'sortBy' => $this->getCurrentSortBy(),
            'sortDirection' => $this->getCurrentSortDirection(),
        ]);
    }
}