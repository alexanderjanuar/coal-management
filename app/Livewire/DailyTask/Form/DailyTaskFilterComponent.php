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
            'date_preset' => '', // New: for quick date filters
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
     * Filter Form Definition - Simplified and compact
     */
    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        // Date Range
                        Forms\Components\DatePicker::make('date_start')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->live()
                            ->columnSpan(1),
                            
                        Forms\Components\DatePicker::make('date_end')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->live()
                            ->columnSpan(1),

                        Forms\Components\Select::make('date_preset')
                            ->label('Preset Tanggal')
                            ->options([
                                '' => 'Pilih preset...',
                                'this_week' => 'Minggu Ini',
                                'next_week' => 'Minggu Depan',
                                'this_month' => 'Bulan Ini',
                                'overdue' => 'Terlambat',
                            ])
                            ->live()
                            ->columnSpan(1),
                    ]),

                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Select::make('project')
                            ->label('Proyek')
                            ->options($this->getProjectOptions())
                            ->multiple()
                            ->searchable()
                            ->live()
                            ->columnSpan(1),
                            
                        Forms\Components\Select::make('assignee')
                            ->label('Penanggung Jawab')
                            ->options($this->getUserOptions())
                            ->multiple()
                            ->searchable()
                            ->live()
                            ->columnSpan(1),

                        Forms\Components\Select::make('department')
                            ->label('Departemen')
                            ->options($this->getDepartmentOptions())
                            ->multiple()
                            ->searchable()
                            ->live()
                            ->columnSpan(1),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('sort_by')
                            ->label('Urutkan')
                            ->options([
                                'task_date' => 'Tanggal Task',
                                'created_at' => 'Tanggal Dibuat',
                                'title' => 'Nama Task',
                                'priority' => 'Prioritas',
                            ])
                            ->live()
                            ->columnSpan(1),
                            
                        Forms\Components\Select::make('sort_direction')
                            ->label('Arah')
                            ->options([
                                'asc' => 'Naik',
                                'desc' => 'Turun',
                            ])
                            ->live()
                            ->columnSpan(1),
                    ]),
            ])
            ->statePath('filterData');
    }

    /**
     * NEW: Quick date filter preset handler
     */
    public function setDateFilter(string $preset): void
    {
        $this->filterData['date_preset'] = $preset;
        
        // Clear other date filters when using preset
        $this->filterData['date'] = null;
        $this->filterData['date_start'] = null;
        $this->filterData['date_end'] = null;
        
        // Set appropriate date range based on preset
        switch ($preset) {
            case 'today':
                $this->filterData['date'] = now()->format('Y-m-d');
                break;
                
            case 'tomorrow':
                $this->filterData['date'] = now()->addDay()->format('Y-m-d');
                break;
                
            case 'this_week':
                $this->filterData['date_start'] = now()->startOfWeek()->format('Y-m-d');
                $this->filterData['date_end'] = now()->endOfWeek()->format('Y-m-d');
                break;
                
            case 'next_week':
                $this->filterData['date_start'] = now()->addWeek()->startOfWeek()->format('Y-m-d');
                $this->filterData['date_end'] = now()->addWeek()->endOfWeek()->format('Y-m-d');
                break;
                
            case 'this_month':
                $this->filterData['date_start'] = now()->startOfMonth()->format('Y-m-d');
                $this->filterData['date_end'] = now()->endOfMonth()->format('Y-m-d');
                break;
                
            case 'overdue':
                $this->filterData['date_end'] = now()->subDay()->format('Y-m-d');
                break;
        }
        
        $this->filterForm->fill($this->filterData);
        $this->emitFiltersChanged();
    }

    /**
     * NEW: Quick filter toggle handler
     */
    public function toggleQuickFilter(string $filterType, string $value): void
    {
        if (!isset($this->filterData[$filterType])) {
            $this->filterData[$filterType] = [];
        }
        
        if (!is_array($this->filterData[$filterType])) {
            $this->filterData[$filterType] = [];
        }
        
        $currentValues = $this->filterData[$filterType];
        
        if (in_array($value, $currentValues)) {
            // Remove if already exists
            $this->filterData[$filterType] = array_values(array_filter($currentValues, fn($v) => $v !== $value));
        } else {
            // Add if doesn't exist
            $this->filterData[$filterType][] = $value;
        }
        
        $this->filterForm->fill($this->filterData);
        $this->emitFiltersChanged();
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
        // Clear preset when manual date is set
        $this->filterData['date_preset'] = '';
        $this->emitFiltersChanged();
    }
    
    public function updatedFilterDataDateStart(): void
    {
        // Clear preset when manual date range is set
        $this->filterData['date_preset'] = '';
        $this->emitFiltersChanged();
    }
    
    public function updatedFilterDataDateEnd(): void
    {
        // Clear preset when manual date range is set
        $this->filterData['date_preset'] = '';
        $this->emitFiltersChanged();
    }
    
    public function updatedFilterDataDatePreset(): void
    {
        if (!empty($this->filterData['date_preset'])) {
            $this->setDateFilter($this->filterData['date_preset']);
        }
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

    public function updatedFilterDataSortBy(): void
    {
        $this->emitFiltersChanged();
    }

    public function updatedFilterDataSortDirection(): void
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
            'date_preset' => '',
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
            'date_preset' => $data['date_preset'] ?? '',
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
                'label' => 'Pencarian',
                'value' => $filters['search'],
                'color' => 'primary',
                'icon' => 'heroicon-o-magnifying-glass',
            ];
        }

        // Date preset filter
        if (!empty($filters['date_preset'])) {
            $presetLabels = [
                'today' => 'Hari Ini',
                'tomorrow' => 'Besok',
                'this_week' => 'Minggu Ini',
                'next_week' => 'Minggu Depan',
                'this_month' => 'Bulan Ini',
                'overdue' => 'Terlambat',
            ];
            
            $activeFilters[] = [
                'type' => 'date_preset',
                'label' => 'Tanggal',
                'value' => $presetLabels[$filters['date_preset']] ?? $filters['date_preset'],
                'color' => $filters['date_preset'] === 'overdue' ? 'danger' : 'info',
                'icon' => 'heroicon-o-calendar-days',
            ];
        }

        // Single date filter
        if (!empty($filters['date']) && empty($filters['date_preset'])) {
            $date = $filters['date'];
            $dateValue = '';
            if ($date instanceof \Carbon\Carbon) {
                $dateValue = $date->format('d M Y');
            } elseif (is_string($date)) {
                try {
                    $dateValue = Carbon::parse($date)->format('d M Y');
                } catch (\Exception $e) {
                    $dateValue = $date;
                }
            }
            $activeFilters[] = [
                'type' => 'date',
                'label' => 'Tanggal',
                'value' => $dateValue,
                'color' => 'info',
                'icon' => 'heroicon-o-calendar-days',
            ];
        }

        // Date range filters
        if ((!empty($filters['date_start']) || !empty($filters['date_end'])) && empty($filters['date_preset'])) {
            $rangeValue = '';
            if (!empty($filters['date_start']) && !empty($filters['date_end'])) {
                $startDate = $filters['date_start'] instanceof \Carbon\Carbon 
                    ? $filters['date_start']->format('d M') 
                    : Carbon::parse($filters['date_start'])->format('d M');
                $endDate = $filters['date_end'] instanceof \Carbon\Carbon 
                    ? $filters['date_end']->format('d M Y') 
                    : Carbon::parse($filters['date_end'])->format('d M Y');
                $rangeValue = $startDate . ' - ' . $endDate;
            } elseif (!empty($filters['date_start'])) {
                $rangeValue = 'Dari ' . ($filters['date_start'] instanceof \Carbon\Carbon 
                    ? $filters['date_start']->format('d M Y') 
                    : Carbon::parse($filters['date_start'])->format('d M Y'));
            } elseif (!empty($filters['date_end'])) {
                $rangeValue = 'Sampai ' . ($filters['date_end'] instanceof \Carbon\Carbon 
                    ? $filters['date_end']->format('d M Y') 
                    : Carbon::parse($filters['date_end'])->format('d M Y'));
            }
            
            $activeFilters[] = [
                'type' => 'date_range',
                'label' => 'Rentang Tanggal',
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
                'value' => count($statusLabels) > 2 ? count($statusLabels) . ' status' : implode(', ', $statusLabels),
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
                'label' => 'Prioritas',
                'value' => count($priorityLabels) > 2 ? count($priorityLabels) . ' prioritas' : implode(', ', $priorityLabels),
                'color' => 'warning',
                'icon' => 'heroicon-o-exclamation-triangle',
                'count' => count($filters['priority']),
            ];
        }

        // Project filter
        if (!empty($filters['project'])) {
            $projectLabels = array_map(fn($projectId) => $this->getProjectOptions()[$projectId] ?? 'Proyek Tidak Diketahui', $filters['project']);
            $activeFilters[] = [
                'type' => 'project',
                'label' => 'Proyek',
                'value' => count($projectLabels) > 2 ? count($projectLabels) . ' proyek' : implode(', ', $projectLabels),
                'color' => 'info',
                'icon' => 'heroicon-o-folder',
                'count' => count($filters['project']),
            ];
        }

        // Assignee filter
        if (!empty($filters['assignee'])) {
            $assigneeLabels = array_map(fn($userId) => $this->getUserOptions()[$userId] ?? 'User Tidak Diketahui', $filters['assignee']);
            $activeFilters[] = [
                'type' => 'assignee',
                'label' => 'Penanggung Jawab',
                'value' => count($assigneeLabels) > 2 ? count($assigneeLabels) . ' orang' : implode(', ', $assigneeLabels),
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
                'label' => 'Departemen',
                'value' => count($deptLabels) > 2 ? count($deptLabels) . ' departemen' : implode(', ', $deptLabels),
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
                'label' => 'Jabatan',
                'value' => count($posLabels) > 2 ? count($posLabels) . ' jabatan' : implode(', ', $posLabels),
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
            case 'date_preset':
                $this->filterData['date_preset'] = '';
                $this->filterData['date'] = null;
                $this->filterData['date_start'] = null;
                $this->filterData['date_end'] = null;
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
            'pending' => 'Menunggu',
            'in_progress' => 'Sedang Dikerjakan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
    }

    /**
     * Get priority options
     */
    protected function getPriorityOptions(): array
    {
        return [
            'low' => 'Rendah',
            'normal' => 'Normal',
            'high' => 'Tinggi',
            'urgent' => 'Mendesak',
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