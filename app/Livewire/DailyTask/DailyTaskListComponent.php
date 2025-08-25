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
    public ?array $quickTaskData = [];
    public ?array $filterData = [];

    // Pagination
    public int $perPage = 20;

    // Add listeners for child components
    protected $listeners = [
        'taskUpdated' => 'refreshTasks',
        'task-created' => 'refreshTasks',
        'taskStatusChanged' => 'refreshTasks',
        'taskDeleted' => 'refreshTasks',
        'subtaskAdded' => 'refreshTasks',
        'subtaskUpdated' => 'refreshTasks',
    ];

    protected function getForms(): array
    {
        return [
            'quickTaskForm',
            'filterForm',
        ];
    }

    public function mount(): void
    {
        // Initialize form data with defaults
        $this->quickTaskForm->fill([
            'title' => '',
            'date' => today(),
        ]);
        
        // Initialize filter form with defaults - remove query string persistence for now
        $this->filterData = [
            'search' => '',
            'date' => today(),
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
     * Quick Task Form Definition
     */
    public function quickTaskForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Task Title')
                            ->placeholder('Enter task title...')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                            
                        Forms\Components\DatePicker::make('date')
                            ->label('Date')
                            ->default(today())
                            ->required()
                            ->native(false)
                            ->columnSpan(1),
                    ]),
            ])
            ->statePath('quickTaskData');
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
                                    ->native(false)
                                    ->live(),
                                    
                                Forms\Components\Select::make('group_by')
                                    ->options($this->getGroupByOptions())
                                    ->native(false)
                                    ->live(),
                                    
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
                                    ->live(),
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
     * Quick Task Form Submission
     */
    public function createQuickTask(): void
    {
        $data = $this->quickTaskForm->getState();

        try {
            DailyTask::create([
                'title' => $data['title'],
                'task_date' => $data['date'],
                'created_by' => auth()->id(),
                'status' => 'pending',
                'priority' => 'normal',
            ]);

            // Reset form
            $this->quickTaskForm->fill([
                'title' => '',
                'date' => today(),
            ]);      

            Notification::make()
                ->title('Success')
                ->body('New task created successfully')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to create task: ' . $e->getMessage())
                ->danger()
                ->send();
        }
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
    }

    /**
     * Reset Filters Action
     */
    public function resetFilters(): void
    {
        $this->filterData = [
            'search' => '',
            'date' => today(),
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
        
        // Apply date filter FIRST - this is critical
        if (!empty($filters['date'])) {
            $date = $filters['date'];
            if ($date instanceof \Carbon\Carbon) {
                $query->whereDate('task_date', $date->format('Y-m-d'));
            } elseif (is_string($date)) {
                try {
                    $carbonDate = Carbon::parse($date);
                    $query->whereDate('task_date', $carbonDate->format('Y-m-d'));
                } catch (\Exception $e) {
                    \Log::warning('Invalid date format in filter: ' . $date . ' Error: ' . $e->getMessage());
                }
            } elseif (is_array($date)) {
                // Handle array date format from some form inputs
                try {
                    $carbonDate = Carbon::createFromFormat('Y-m-d', $date['year'] . '-' . $date['month'] . '-' . $date['day']);
                    $query->whereDate('task_date', $carbonDate->format('Y-m-d'));
                } catch (\Exception $e) {
                    \Log::warning('Invalid array date format in filter: ' . json_encode($date) . ' Error: ' . $e->getMessage());
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
     * Get grouped tasks - improved grouping logic with better debugging
     */
    public function getGroupedTasks(): Collection
    {
        $filters = $this->getCurrentFilters();
        $groupBy = $filters['group_by'];
        
        // Get the filtered tasks first
        $query = $this->getTasksQuery();
        $tasks = $query->get();
        
        \Log::info("Tasks found after all filters applied: " . $tasks->count());
        \Log::info("Grouping by: " . $groupBy);
        
        // If no grouping, return all tasks
        if ($groupBy === 'none') {
            \Log::info("No grouping - returning all tasks");
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
            
            \Log::info("Task ID {$task->id} grouped under: {$groupValue}");
            return $groupValue;
        });
        
        \Log::info("Groups created: " . $grouped->keys()->implode(', '));
        \Log::info("Group counts: " . $grouped->map(fn($group) => $group->count())->toJson());
        
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
     * Get priority options
     */
    public function getPriorityOptions(): array
    {
        return [
            'low' => 'Low',
            'normal' => 'Normal',
            'high' => 'High',
            'urgent' => 'Urgent',
        ];
    }

    /**
     * Get user options
     */
    public function getUserOptions(): array
    {
        return User::orderBy('name')->pluck('name', 'id')->toArray();
    }

    /**
     * Get project options
     */
    public function getProjectOptions(): array
    {
        return Project::orderBy('name')->pluck('name', 'id')->toArray();
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
     * Debug method - force refresh component
     */
    public function refresh(): void
    {
        $this->resetPage();
        // Force re-render
    }

    /**
     * Debug method - log current state
     */
    public function logCurrentState(): void
    {
        if (app()->environment('local')) {
            \Log::info('=== DEBUG: Current Component State ===');
            \Log::info('Filter Data: ' . json_encode($this->filterData));
            \Log::info('Current Filters: ' . json_encode($this->getCurrentFilters()));
            \Log::info('View Mode: ' . $this->getCurrentViewMode());
            \Log::info('Group By: ' . $this->getCurrentGroupBy());
            \Log::info('Total Tasks: ' . $this->getTotalTasksCount());
            \Log::info('=== END DEBUG ===');
        }
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
        ]);
    }
}