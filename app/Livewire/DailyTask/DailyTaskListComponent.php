<?php

namespace App\Livewire\DailyTask;

use App\Models\DailyTask;
use App\Models\User;
use App\Models\Project;
use Livewire\Component;
use Livewire\WithPagination;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DailyTaskListComponent extends Component implements HasForms
{
    use InteractsWithForms, WithPagination;

    // Form data - single source of truth
    public ?array $filterData = [];

    public array $creatingNewTasks = []; // Track which groups are creating new tasks
    public array $newTaskData = []; // Store new task data for each group
    public ?string $editingGroup = null; // Track which group is currently being edited

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
        'cancelNewTask' => 'cancelNewTask', // Add this
    ];

    protected function getForms(): array
    {
        return [
            'filterForm',
        ];
    }

    public function mount(): void
    {        
        // Initialize filter form with defaults - remove default date filter
        $this->filterData = [
            'search' => '',
            'date' => null, // Ubah dari today() ke null
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
        
        $this->filterForm->fill($this->filterData);
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
     * Filter Form Definition - Simplified without afterStateUpdated
     */
    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        // Main Filters Row
                        Forms\Components\Grid::make(5)
                            ->schema([
                                Forms\Components\TextInput::make('search')
                                    ->placeholder('Search tasks or descriptions...')
                                    ->prefixIcon('heroicon-o-magnifying-glass')
                                    ->live(debounce: 750)
                                    ->columnSpan(2),
                                    
                                Forms\Components\DatePicker::make('date')
                                    ->label('Single Date')
                                    ->native(false)
                                    ->live()
                                    ->helperText('Filter by specific date')
                                    ->columnSpan(1),
                                    
                                Forms\Components\Select::make('group_by')
                                    ->options($this->getGroupByOptions())
                                    ->native(false)
                                    ->live()
                                    ->columnSpan(1),
                                    
                                Forms\Components\ToggleButtons::make('view_mode')
                                    ->options([
                                        'list' => 'List',
                                        'kanban' => 'Board',
                                    ])
                                    ->icons([
                                        'list' => 'heroicon-o-list-bullet',
                                        'kanban' => 'heroicon-o-squares-2x2',
                                    ])
                                    ->inline()
                                    ->default('list')
                                    ->live()
                                    ->columnSpan(1),
                            ]),
                            
                        // Date Range Filters
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('date_start')
                                    ->label('Start Date From')
                                    ->placeholder('Select start date...')
                                    ->native(false)
                                    ->live()
                                    ->helperText('Filter tasks starting from this date')
                                    ->columnSpan(1),
                                    
                                Forms\Components\DatePicker::make('date_end')
                                    ->label('End Date To')
                                    ->placeholder('Select end date...')
                                    ->native(false)
                                    ->live()
                                    ->helperText('Filter tasks up to this date')
                                    ->columnSpan(1),
                            ]),                                                  
                        // Secondary Filters Row
                        Forms\Components\Section::make('Advanced Filters')
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->label('Status')
                                            ->options($this->getStatusOptions())
                                            ->multiple()
                                            ->prefixIcon('heroicon-o-flag')
                                            ->native(false)
                                            ->live(),
                                            
                                        Forms\Components\Select::make('priority')
                                            ->label('Priority')
                                            ->options($this->getPriorityOptions())
                                            ->multiple()
                                            ->prefixIcon('heroicon-o-exclamation-triangle')
                                            ->native(false)
                                            ->live(),
                                            
                                        Forms\Components\Select::make('project')
                                            ->label('Project')
                                            ->options($this->getProjectOptions())
                                            ->multiple()
                                            ->prefixIcon('heroicon-o-folder')
                                            ->native(false)
                                            ->live()
                                            ->searchable(),
                                            
                                        Forms\Components\Select::make('assignee')
                                            ->label('Assignee')
                                            ->options($this->getUserOptions())
                                            ->multiple()
                                            ->prefixIcon('heroicon-o-user')
                                            ->native(false)
                                            ->live()
                                            ->searchable(),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed(),
                    ]),
            ])
            ->statePath('filterData');
    }

    /**
     * Handle filter changes - proper Livewire method names
     */
    public function updatedFilterDataSearch(): void
    {
        $this->resetPage();
    }
    
    public function updatedFilterDataDate(): void
    {
        $this->resetPage();
    }
    
    public function updatedFilterDataDateStart(): void
    {
        $this->resetPage();
    }
    
    public function updatedFilterDataDateEnd(): void
    {
        $this->resetPage();
    }
    
    public function updatedFilterDataStatus(): void
    {
        $this->resetPage();
    }
    
    public function updatedFilterDataPriority(): void
    {
        $this->resetPage();
    }
    
    public function updatedFilterDataProject(): void
    {
        $this->resetPage();
    }
    
    public function updatedFilterDataAssignee(): void
    {
        $this->resetPage();
    }
    
    public function updatedFilterDataGroupBy(): void
    {
        $this->resetPage();
    }
    
    public function updatedFilterDataViewMode(): void
    {
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
                ->body('Task not found')
                ->danger()
                ->send();
            return;
        }

        $task->update(['status' => $status]);
        
        Notification::make()
            ->title('Status Updated')
            ->body("Task status changed to " . $this->getStatusLabel($status))
            ->success()
            ->send();
            
        // Trigger refresh
        $this->dispatch('taskStatusChanged');
    }

    /**
     * Reset Filters Action
     */
    public function resetFilters(): void
    {
        $this->filterData = [
            'search' => '',
            'date' => null, // Ubah dari today() ke null
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
        
        $this->filterForm->fill($this->filterData);
        $this->resetPage();
    }

    /**
     * Get current filter values - simplified and more reliable
     */
    protected function getCurrentFilters(): array
    {
        // Always use filterData as source of truth
        $data = $this->filterData ?? [];
        
        return [
            'search' => !empty($data['search']) ? trim($data['search']) : '',
            'date' => $data['date'] ?? null,
            'date_start' => $data['date_start'] ?? null,
            'date_end' => $data['date_end'] ?? null,
            'status' => is_array($data['status'] ?? null) ? array_values(array_filter($data['status'])) : [],
            'priority' => is_array($data['priority'] ?? null) ? array_values(array_filter($data['priority'])) : [],
            'project' => is_array($data['project'] ?? null) ? array_values(array_filter($data['project'])) : [],
            'assignee' => is_array($data['assignee'] ?? null) ? array_values(array_filter($data['assignee'])) : [],
            'group_by' => $data['group_by'] ?? 'status',
            'view_mode' => $data['view_mode'] ?? 'list',
            'sort_by' => $data['sort_by'] ?? 'task_date',
            'sort_direction' => $data['sort_direction'] ?? 'desc',
        ];
    }

    /**
     * Change sort order
     */
    public function sortBy(string $field): void
    {
        if (($this->filterData['sort_by'] ?? '') === $field) {
            $this->filterData['sort_direction'] = ($this->filterData['sort_direction'] ?? 'asc') === 'asc' ? 'desc' : 'asc';
        } else {
            $this->filterData['sort_by'] = $field;
            $this->filterData['sort_direction'] = 'asc';
        }
        
        // No need to refill form for sorting
        $this->resetPage();
    }

    /**
     * Get tasks query with all filters applied - more robust filtering
     */
    public function getTasksQuery()
    {
        $filters = $this->getCurrentFilters();
        
        $query = DailyTask::query()->with(['project', 'creator', 'assignedUsers', 'subtasks']);
            
        // Apply search filter
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Apply date filters
        if (!empty($filters['date'])) {
            $date = $filters['date'];
            if ($date instanceof \Carbon\Carbon) {
                $query->whereDate('task_date', $date->format('Y-m-d'));
            } elseif (is_string($date)) {
                try {
                    $carbonDate = Carbon::parse($date);
                    $query->whereDate('task_date', $carbonDate->format('Y-m-d'));
                } catch (\Exception $e) {
                    // Handle silently, skip invalid dates
                }
            }
        }
        
        // Apply date range filters
        if (!empty($filters['date_start']) || !empty($filters['date_end'])) {
            if (!empty($filters['date_start'])) {
                $startDate = $filters['date_start'];
                if ($startDate instanceof \Carbon\Carbon) {
                    $query->whereDate('task_date', '>=', $startDate->format('Y-m-d'));
                } elseif (is_string($startDate)) {
                    try {
                        $carbonStartDate = Carbon::parse($startDate);
                        $query->whereDate('task_date', '>=', $carbonStartDate->format('Y-m-d'));
                    } catch (\Exception $e) {
                        // Handle silently
                    }
                }
            }
            
            if (!empty($filters['date_end'])) {
                $endDate = $filters['date_end'];
                if ($endDate instanceof \Carbon\Carbon) {
                    $query->whereDate('task_date', '<=', $endDate->format('Y-m-d'));
                } elseif (is_string($endDate)) {
                    try {
                        $carbonEndDate = Carbon::parse($endDate);
                        $query->whereDate('task_date', '<=', $carbonEndDate->format('Y-m-d'));
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
        $query->orderBy($sortBy, $sortDirection);
        
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
     * Get grouped tasks - improved grouping logic
     */
    public function getGroupedTasks(): Collection
    {
        $filters = $this->getCurrentFilters();
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
                    $groupValue = $task->task_date->format('M d, Y');
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
                    return strcmp($a, $b);
            }
            
            return strcasecmp($a, $b);
        });
        
        return $sorted;
    }

    /**
     * Get active filters for visual display
     */
    public function getActiveFilters(): array
    {
        $filters = $this->getCurrentFilters();
        $activeFilters = [];

        // Search filter
        if (!empty($filters['search'])) {
            $activeFilters[] = [
                'type' => 'search',
                'label' => 'Search',
                'value' => $filters['search'],
                'color' => 'primary',
                'icon' => 'heroicon-o-magnifying-glass',
            ];
        }

        // Date filter
        if (!empty($filters['date'])) {
            $date = $filters['date'];
            $dateValue = '';
            if ($date instanceof \Carbon\Carbon) {
                $dateValue = $date->format('M d, Y');
            } elseif (is_string($date)) {
                try {
                    $dateValue = Carbon::parse($date)->format('M d, Y');
                } catch (\Exception $e) {
                    $dateValue = $date;
                }
            }
            $activeFilters[] = [
                'type' => 'date',
                'label' => 'Date',
                'value' => $dateValue,
                'color' => 'info',
                'icon' => 'heroicon-o-calendar-days',
            ];
        }

        // Date range filters
        if (!empty($filters['date_start']) || !empty($filters['date_end'])) {
            $rangeValue = '';
            if (!empty($filters['date_start']) && !empty($filters['date_end'])) {
                $startDate = $filters['date_start'] instanceof \Carbon\Carbon 
                    ? $filters['date_start']->format('M d') 
                    : Carbon::parse($filters['date_start'])->format('M d');
                $endDate = $filters['date_end'] instanceof \Carbon\Carbon 
                    ? $filters['date_end']->format('M d, Y') 
                    : Carbon::parse($filters['date_end'])->format('M d, Y');
                $rangeValue = $startDate . ' - ' . $endDate;
            } elseif (!empty($filters['date_start'])) {
                $rangeValue = 'From ' . ($filters['date_start'] instanceof \Carbon\Carbon 
                    ? $filters['date_start']->format('M d, Y') 
                    : Carbon::parse($filters['date_start'])->format('M d, Y'));
            } elseif (!empty($filters['date_end'])) {
                $rangeValue = 'Until ' . ($filters['date_end'] instanceof \Carbon\Carbon 
                    ? $filters['date_end']->format('M d, Y') 
                    : Carbon::parse($filters['date_end'])->format('M d, Y'));
            }
            
            $activeFilters[] = [
                'type' => 'date_range',
                'label' => 'Date Range',
                'value' => $rangeValue,
                'color' => 'info',
                'icon' => 'heroicon-o-calendar',
            ];
        }

        // Status filter
        if (!empty($filters['status'])) {
            $statusLabels = array_map(fn($status) => $this->getStatusOptions()[$status] ?? $status, $filters['status']);
            $activeFilters[] = [
                'type' => 'status',
                'label' => 'Status',
                'value' => implode(', ', $statusLabels),
                'color' => 'success',
                'icon' => 'heroicon-o-flag',
                'count' => count($filters['status']),
            ];
        }

        // Priority filter
        if (!empty($filters['priority'])) {
            $priorityLabels = array_map(fn($priority) => $this->getPriorityOptions()[$priority] ?? $priority, $filters['priority']);
            $activeFilters[] = [
                'type' => 'priority',
                'label' => 'Priority',
                'value' => implode(', ', $priorityLabels),
                'color' => 'warning',
                'icon' => 'heroicon-o-exclamation-triangle',
                'count' => count($filters['priority']),
            ];
        }

        // Project filter
        if (!empty($filters['project'])) {
            $projectLabels = array_map(fn($projectId) => $this->getProjectOptions()[$projectId] ?? 'Unknown Project', $filters['project']);
            $activeFilters[] = [
                'type' => 'project',
                'label' => 'Project',
                'value' => implode(', ', $projectLabels),
                'color' => 'info',
                'icon' => 'heroicon-o-folder',
                'count' => count($filters['project']),
            ];
        }

        // Assignee filter
        if (!empty($filters['assignee'])) {
            $assigneeLabels = array_map(fn($userId) => $this->getUserOptions()[$userId] ?? 'Unknown User', $filters['assignee']);
            $activeFilters[] = [
                'type' => 'assignee',
                'label' => 'Assignee',
                'value' => implode(', ', $assigneeLabels),
                'color' => 'gray',
                'icon' => 'heroicon-o-user',
                'count' => count($filters['assignee']),
            ];
        }

        return $activeFilters;
    }

    /**
     * Remove specific filter
     */
    public function removeFilter(string $type): void
    {
        switch ($type) {
            case 'search':
                $this->filterData['search'] = '';
                break;
            case 'date':
                $this->filterData['date'] = null;
                break;
            case 'date_range':
                $this->filterData['date_start'] = null;
                $this->filterData['date_end'] = null;
                break;
            case 'status':
                $this->filterData['status'] = [];
                break;
            case 'priority':
                $this->filterData['priority'] = [];
                break;
            case 'project':
                $this->filterData['project'] = [];
                break;
            case 'assignee':
                $this->filterData['assignee'] = [];
                break;
        }
        
        $this->filterForm->fill($this->filterData);
        $this->resetPage();
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
     * Get group by options
     */
    public function getGroupByOptions(): array
    {
        return [
            'none' => 'No Grouping',
            'status' => 'Status',
            'priority' => 'Priority',
            'project' => 'Project',
            'assignee' => 'Assignee',
            'date' => 'Date',
        ];
    }

    /**
     * Get sort options
     */
    public function getSortOptions(): array
    {
        return [
            'task_date' => 'Date',
            'title' => 'Title',
            'priority' => 'Priority',
            'status' => 'Status',
            'created_at' => 'Created At',
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
        return $this->filterData['view_mode'] ?? 'list';
    }

    /**
     * Get current group by
     */
    public function getCurrentGroupBy(): string
    {
        return $this->filterData['group_by'] ?? 'status';
    }

    /**
     * Get current sort info
     */
    public function getCurrentSortBy(): string
    {
        return $this->filterData['sort_by'] ?? 'task_date';
    }

    public function getCurrentSortDirection(): string
    {
        return $this->filterData['sort_direction'] ?? 'desc';
    }

    /**
     * Manual refresh method for external triggers
     */
    public function refresh(): void
    {
        $this->refreshTasks();
    }


    public function createNewTaskForGroup(string $groupType, string $groupValue): void
    {
        // Determine the default values based on group
        $defaults = $this->getDefaultsForGroup($groupType, $groupValue);
        
        // Dispatch event to open create modal with pre-filled values
        $this->dispatch('openCreateTaskModal', defaults: $defaults);
    }

    /**
     * Get default values based on group type and value
     */
    private function getDefaultsForGroup(string $groupType, string $groupValue): array
    {
        $defaults = [
            'task_date' => today(),
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
                try {
                    $date = Carbon::createFromFormat('M d, Y', $groupValue);
                    $defaults['task_date'] = $date;
                } catch (\Exception $e) {
                    // Keep default date
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
    $this->newTaskData[$groupKey] = array_merge([
        'title' => '',
        'task_date' => today(),
        'status' => 'pending',
        'priority' => 'normal',
        'project_id' => null,
    ], $this->getDefaultsForGroup($groupType, $groupValue));
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
        'status' => $data['status'],
        'priority' => $data['priority'],
        'task_date' => $data['task_date'],
        'project_id' => $data['project_id'],
        'created_by' => auth()->id(),
    ]);

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


    public function updatePriority(string $priority): void
    {
        $this->task->update(['priority' => $priority]);
        
        $this->dispatch('taskUpdated');
        
        Notification::make()
            ->title('Priority Updated')
            ->body("Priority changed to " . ucfirst($priority))
            ->success()
            ->send();
    }

    public function updateProject($projectId): void
    {
        $this->task->update(['project_id' => $projectId]);
        
        $this->dispatch('taskUpdated');
        
        Notification::make()
            ->title('Project Updated')
            ->body($projectId ? "Project assigned" : "Project removed")
            ->success()
            ->send();
    }

    public function assignUser(int $userId): void
    {
        if (!$this->task->assignedUsers->contains($userId)) {
            $this->task->assignedUsers()->attach($userId);
            $this->task->refresh();
            
            $userName = User::find($userId)?->name ?? 'User';
            
            Notification::make()
                ->title('User Assigned')
                ->body("Assigned to {$userName}")
                ->success()
                ->send();
                
            $this->dispatch('taskUpdated');
        }
    }

    public function unassignUser(int $userId): void
    {
        $this->task->assignedUsers()->detach($userId);
        $this->task->refresh();
        
        $userName = User::find($userId)?->name ?? 'User';
        
        Notification::make()
            ->title('User Unassigned')
            ->body("Unassigned from {$userName}")
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
        
        return view('livewire.daily-task.daily-task-list-component', [
            'groupedTasks' => $this->getGroupedTasks(),
            'paginatedTasks' => $viewMode === 'list' && $groupBy === 'none' ? $this->getTasks() : null,
            'statusOptions' => $this->getStatusOptions(),
            'priorityOptions' => $this->getPriorityOptions(),
            'userOptions' => $this->getUserOptions(),
            'projectOptions' => $this->getProjectOptions(),
            'groupByOptions' => $this->getGroupByOptions(),
            'sortOptions' => $this->getSortOptions(),
            'totalTasks' => $this->getTotalTasksCount(),
            'viewMode' => $viewMode,
            'groupBy' => $groupBy,
            'sortBy' => $this->getCurrentSortBy(),
            'sortDirection' => $this->getCurrentSortDirection(),
            'activeFilters' => $this->getActiveFilters(),
        ]);
    }
}