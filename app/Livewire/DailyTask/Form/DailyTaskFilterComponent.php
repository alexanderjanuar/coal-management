<?php

namespace App\Livewire\DailyTask\Form;

use App\Models\Project;
use App\Models\User;
use Livewire\Component;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Carbon\Carbon;

class DailyTaskFilterComponent extends Component implements HasForms
{
    use InteractsWithForms;

    // Filter data
    public ?array $filterData = [];
    
    // Component state
    public bool $filtersCollapsed = true;
    public int $totalTasks = 0;

    // Events this component listens to
    protected $listeners = [
        'filtersUpdated' => '$refresh',
        'resetFilters' => 'resetFilters',
    ];

    protected function getForms(): array
    {
        return [
            'filterForm',
        ];
    }

    public function mount(array $initialFilters = []): void
    {        
        // Initialize filter form with defaults or provided values
        $this->filterData = array_merge([
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
            'view_mode' => 'list',
            'sort_by' => 'task_date',
            'sort_direction' => 'desc',
        ], $initialFilters);
        
        $this->filterForm->fill($this->filterData);
    }

    /**
     * Filter Form Definition
     */
    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        // Main Filters Row
                        Forms\Components\Grid::make(4)
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
                                    ->columnSpan(2),
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
                                        
                                        Forms\Components\Select::make('department')
                                            ->label('Department')
                                            ->options($this->getDepartmentOptions())
                                            ->multiple()
                                            ->prefixIcon('heroicon-o-building-office')
                                            ->native(false)
                                            ->live()
                                            ->searchable()
                                            ->helperText('Filter berdasarkan departemen user')
                                            ->columnSpan(2),
                                        
                                        Forms\Components\Select::make('position')
                                            ->label('Position')
                                            ->options($this->getPositionOptions())
                                            ->multiple()
                                            ->prefixIcon('heroicon-o-briefcase')
                                            ->native(false)
                                            ->live()
                                            ->searchable()
                                            ->helperText('Filter berdasarkan jabatan user')
                                            ->columnSpan(2),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed(),
                    ]),
            ])
            ->statePath('filterData');
    }

    /**
     * Handle filter changes and emit events to parent
     */
    public function updatedFilterDataSearch(): void
    {
        $this->emitFiltersChanged();
    }
    
    public function updatedFilterDataDate(): void
    {
        $this->emitFiltersChanged();
    }
    
    public function updatedFilterDataDateStart(): void
    {
        $this->emitFiltersChanged();
    }
    
    public function updatedFilterDataDateEnd(): void
    {
        $this->emitFiltersChanged();
    }
    
    public function updatedFilterDataStatus(): void
    {
        $this->emitFiltersChanged();
    }
    
    public function updatedFilterDataPriority(): void
    {
        $this->emitFiltersChanged();
    }
    
    public function updatedFilterDataProject(): void
    {
        $this->emitFiltersChanged();
    }
    
    public function updatedFilterDataAssignee(): void
    {
        $this->emitFiltersChanged();
    }
    
    public function updatedFilterDataDepartment(): void
    {
        $this->emitFiltersChanged();
    }
    
    public function updatedFilterDataPosition(): void
    {
        $this->emitFiltersChanged();
    }
    
    public function updatedFilterDataGroupBy(): void
    {
        $this->emitFiltersChanged();
    }
    
    public function updatedFilterDataViewMode(): void
    {
        $this->emitFiltersChanged();
    }

    /**
     * Emit filter changes to parent component
     */
    protected function emitFiltersChanged(): void
    {
        $this->dispatch('filtersChanged', filters: $this->getCurrentFilters());
    }

    /**
     * Reset Filters Action
     */
    public function resetFilters(): void
    {
        $this->filterData = [
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
            'view_mode' => 'list',
            'sort_by' => 'task_date',
            'sort_direction' => 'desc',
        ];
        
        $this->filterForm->fill($this->filterData);
        $this->emitFiltersChanged();
    }

    /**
     * Get current filter values
     */
    public function getCurrentFilters(): array
    {
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
            'department' => is_array($data['department'] ?? null) ? array_values(array_filter($data['department'])) : [],
            'position' => is_array($data['position'] ?? null) ? array_values(array_filter($data['position'])) : [],
            'group_by' => $data['group_by'] ?? 'status',
            'view_mode' => $data['view_mode'] ?? 'list',
            'sort_by' => $data['sort_by'] ?? 'task_date',
            'sort_direction' => $data['sort_direction'] ?? 'desc',
        ];
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

        // Department filter
        if (!empty($filters['department'])) {
            $deptLabels = array_map(fn($dept) => $this->getDepartmentOptions()[$dept] ?? $dept, $filters['department']);
            $activeFilters[] = [
                'type' => 'department',
                'label' => 'Department',
                'value' => implode(', ', $deptLabels),
                'color' => 'info',
                'icon' => 'heroicon-o-building-office',
                'count' => count($filters['department']),
            ];
        }

        // Position filter
        if (!empty($filters['position'])) {
            $posLabels = array_map(fn($pos) => $this->getPositionOptions()[$pos] ?? $pos, $filters['position']);
            $activeFilters[] = [
                'type' => 'position',
                'label' => 'Position',
                'value' => implode(', ', $posLabels),
                'color' => 'warning',
                'icon' => 'heroicon-o-briefcase',
                'count' => count($filters['position']),
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
            case 'department':
                $this->filterData['department'] = [];
                break;
            case 'position':
                $this->filterData['position'] = [];
                break;
        }
        
        $this->filterForm->fill($this->filterData);
        $this->emitFiltersChanged();
    }

    /**
     * Update total tasks count from parent
     */
    public function updateTotalTasks(int $count): void
    {
        $this->totalTasks = $count;
    }

    /**
     * Get status options
     */
    protected function getStatusOptions(): array
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
    protected function getPriorityOptions(): array
    {
        return [
            'low' => 'Low',
            'normal' => 'Normal',
            'high' => 'High',
            'urgent' => 'Urgent',
        ];
    }

    /**
     * Get group by options
     */
    protected function getGroupByOptions(): array
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
     * Get project options
     */
    protected function getProjectOptions(): array
    {
        return Project::pluck('name', 'id')->toArray();
    }

    /**
     * Get user options
     */
    protected function getUserOptions(): array
    {
        return User::where('status', 'active')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Get department options
     */
    protected function getDepartmentOptions(): array
    {
        return User::whereNotNull('department')
            ->where('status', 'active')
            ->distinct()
            ->orderBy('department')
            ->pluck('department', 'department')
            ->toArray();
    }

    /**
     * Get position options
     */
    protected function getPositionOptions(): array
    {
        return User::whereNotNull('position')
            ->where('status', 'active')
            ->distinct()
            ->orderBy('position')
            ->pluck('position', 'position')
            ->toArray();
    }

    public function render()
    {
        return view('livewire.daily-task.form.daily-task-filter-component', [
            'activeFilters' => $this->getActiveFilters(),
        ]);
    }
}