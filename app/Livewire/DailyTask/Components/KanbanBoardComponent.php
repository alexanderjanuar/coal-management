<?php
// app/Livewire/DailyTask/Components/KanbanBoardComponent.php

namespace App\Livewire\DailyTask\Components;

use App\Models\DailyTask;
use App\Models\User;
use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;

class KanbanBoardComponent extends Component implements HasForms
{
    use InteractsWithForms;
    // Filter state (synced with parent component)
    public array $currentFilters = [];

    // Column configuration
    public array $columns = [
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
            'limit' => 5 // WIP limit
        ],
        'completed' => [
            'title' => 'Done',
            'icon' => 'heroicon-o-check-circle',
            'color' => 'green',
            'limit' => null
        ],
    ];

    // State for inline task creation
    public array $creatingInColumn = [];
    public array $newTaskData = [];
    public ?string $currentFormStatus = null;

    // Drag & drop state
    public bool $isDragging = false;
    public ?string $draggedTaskId = null;

    // Lazy loading state
    public array $loadedTasksPerColumn = [
        'pending' => 10,
        'in_progress' => 10,
        'completed' => 10,
    ];

    public int $tasksPerLoad = 10;
    public array $loadingMore = [];

    // Loading state
    public bool $isInitialLoading = true;

    // Performance optimization
    public int $maxCardsPerColumn = 100;
    public bool $showCompletedTasks = true;

    protected $listeners = [
        'taskUpdated' => 'handleRefresh',
        'task-created' => 'handleRefresh',
        'taskStatusChanged' => 'handleRefresh',
        'taskDeleted' => 'handleRefresh',
        'filtersChanged' => 'updateFilters',
        'filtersUpdated' => 'updateFilters',
        'taskMoved' => 'handleTaskMoved',
        'loadMoreTasks' => 'loadMoreTasksInColumn',
    ];

    public function mount(array $initialFilters = []): void
    {
        $this->currentFilters = $initialFilters;

        // Simulate initial loading
        $this->dispatch('initialLoadComplete');
    }

    /**
     * Mark initial loading as complete
     */
    public function completeInitialLoad(): void
    {
        $this->isInitialLoading = false;
    }

    /**
     * Update filters from parent component or dashboard
     */
    #[On('filtersChanged')]
    #[On('filtersUpdated')]
    public function updateFilters(array $filters): void
    {
        $this->currentFilters = array_merge($this->currentFilters, $filters);

        // Reset loaded tasks count when filters change
        $this->loadedTasksPerColumn = [
            'pending' => 10,
            'in_progress' => 10,
            'completed' => 10,
        ];

        // Clear computed property cache
        unset($this->kanbanTasks);

        $this->dispatch('$refresh');
    }

    /**
     * Handle refresh without resetting filters
     */
    public function handleRefresh(): void
    {
        unset($this->kanbanTasks);
        $this->dispatch('$refresh');
    }

    /**
     * Load more tasks in a specific column
     */
    public function loadMoreTasksInColumn(string $status): void
    {
        $this->loadingMore[$status] = true;

        // Simulate loading delay for better UX
        usleep(300000); // 300ms delay

        $this->loadedTasksPerColumn[$status] += $this->tasksPerLoad;

        $this->loadingMore[$status] = false;

        // Clear cache to reload
        unset($this->kanbanTasks);
    }

    /**
     * Get filtered tasks query
     */
    protected function getTasksQuery()
    {
        $query = DailyTask::query()
            ->with([
                'project:id,name,client_id',
                'project.client:id,name',
                'creator:id,name,avatar_url',
                'assignedUsers:id,name,avatar_url,department,position',
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

        // Apply filters from parent component
        if (!empty($this->currentFilters['search'])) {
            $search = $this->currentFilters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($this->currentFilters['priority'])) {
            $query->whereIn('priority', $this->currentFilters['priority']);
        }

        if (!empty($this->currentFilters['project'])) {
            $query->whereIn('project_id', $this->currentFilters['project']);
        }

        if (!empty($this->currentFilters['assignee'])) {
            $query->whereHas('assignedUsers', function ($q) {
                $q->whereIn('user_id', $this->currentFilters['assignee']);
            });
        }

        // Handle date filters from dashboard
        if (!empty($this->currentFilters['from']) && !empty($this->currentFilters['to'])) {
            $query->whereBetween('task_date', [
                $this->currentFilters['from'],
                $this->currentFilters['to']
            ]);
        } elseif (!empty($this->currentFilters['date'])) {
            $query->whereDate('task_date', $this->currentFilters['date']);
        } elseif (!empty($this->currentFilters['date_start']) && !empty($this->currentFilters['date_end'])) {
            $query->whereBetween('task_date', [
                $this->currentFilters['date_start'],
                $this->currentFilters['date_end']
            ]);
        }

        // Handle department filter from dashboard
        if (!empty($this->currentFilters['department'])) {
            $query->whereHas('assignedUsers', function ($q) {
                $q->where('department', $this->currentFilters['department']);
            });
        }

        // Handle position filter from dashboard
        if (!empty($this->currentFilters['position'])) {
            $query->whereHas('assignedUsers', function ($q) {
                $q->where('position', $this->currentFilters['position']);
            });
        }

        // Filter by status (don't show completed if toggled off)
        if (!$this->showCompletedTasks) {
            $query->where('status', '!=', 'completed');
        }

        // Sort by priority and due date for Kanban
        $query->orderByRaw("
            CASE priority
                WHEN 'urgent' THEN 1
                WHEN 'high' THEN 2
                WHEN 'normal' THEN 3
                WHEN 'low' THEN 4
            END
        ")->orderBy('task_date', 'asc');

        return $query;
    }

    /**
     * Get tasks grouped by status for Kanban columns with lazy loading
     */
    #[Computed]
    public function kanbanTasks(): Collection
    {
        $tasks = $this->getTasksQuery()->get();

        // Group by status
        $grouped = $tasks->groupBy('status');

        // Ensure all columns exist even if empty
        $result = collect();
        foreach (array_keys($this->columns) as $status) {
            $columnTasks = $grouped->get($status, collect());

            // Apply lazy loading - only take the number of loaded tasks
            $limit = $this->loadedTasksPerColumn[$status] ?? $this->tasksPerLoad;
            $columnTasks = $columnTasks->take($limit);

            $result->put($status, $columnTasks);
        }

        return $result;
    }

    /**
     * Get total tasks count per column (before lazy loading)
     */
    public function getTotalTasksCount(string $status): int
    {
        return $this->getTasksQuery()
            ->where('status', $status)
            ->count();
    }

    /**
     * Check if there are more tasks to load
     */
    public function hasMoreTasks(string $status): bool
    {
        $loaded = $this->loadedTasksPerColumn[$status] ?? $this->tasksPerLoad;
        $total = $this->getTotalTasksCount($status);

        return $loaded < $total;
    }

    /**
     * Get column statistics
     */
    public function getColumnStats(string $status): array
    {
        // Get total count from database
        $total = $this->getTasksQuery()->where('status', $status)->count();

        // Get actually loaded count (minimum of loaded limit or total)
        $loadedLimit = $this->loadedTasksPerColumn[$status] ?? $this->tasksPerLoad;
        $actualLoaded = min($loadedLimit, $total);

        // Get all tasks for other stats
        $allTasks = $this->getTasksQuery()->where('status', $status)->get();

        return [
            'total' => $total,
            'loaded' => $actualLoaded,  // ← Fixed: now shows min(limit, total)
            'urgent' => $allTasks->where('priority', 'urgent')->count(),
            'high' => $allTasks->where('priority', 'high')->count(),
            'overdue' => $allTasks->filter(function ($task) {
                return $task->task_date && $task->task_date->isPast() && $task->status !== 'completed';
            })->count(),
            'limit' => $this->columns[$status]['limit'] ?? null,
            'isAtLimit' => $this->columns[$status]['limit']
                ? $total >= $this->columns[$status]['limit']
                : false
        ];
    }

    /**
     * Handle task moved between columns
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
            if ($this->columns[$newStatus]['limit'] ?? null) {
                $currentCount = $this->getTotalTasksCount($newStatus);
                if ($currentCount >= $this->columns[$newStatus]['limit']) {
                    Notification::make()
                        ->title('WIP Limit Reached')
                        ->body("Column '{$this->columns[$newStatus]['title']}' has reached its work-in-progress limit")
                        ->warning()
                        ->duration(5000)
                        ->send();

                    $this->dispatch('revertTaskMove', taskId: $taskId);
                    return;
                }
            }

            $oldStatus = $task->status;
            $task->update(['status' => $newStatus]);

            // Auto-set start_task_date when moved to in_progress
            if ($newStatus === 'in_progress' && !$task->start_task_date) {
                $task->update(['start_task_date' => now()]);
            }

            Notification::make()
                ->title('Task Moved')
                ->body("Task '{$task->title}' moved from {$this->columns[$oldStatus]['title']} to {$this->columns[$newStatus]['title']}")
                ->success()
                ->send();

            $this->handleRefresh();

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
     * Start creating new task in column
     */
    public function startCreatingKanbanTask(string $status): void
    {
        // Check WIP limit
        if ($this->columns[$status]['limit'] ?? null) {
            $currentCount = $this->getTotalTasksCount($status);
            if ($currentCount >= $this->columns[$status]['limit']) {
                Notification::make()
                    ->title('Batas WIP Tercapai')
                    ->body("Tidak dapat menambah tugas ke kolom '{$this->columns[$status]['title']}'")
                    ->warning()
                    ->send();
                return;
            }
        }

        $this->currentFormStatus = $status;
        $this->creatingInColumn = [$status => true];
        $this->newTaskData[$status] = [
            'title' => '',
            'description' => '',
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
     * Save new task
     */
    public function saveKanbanTask(string $status): void
    {
        // Validate using Filament form
        $validated = $this->form->getState();

        if (empty($validated['title'])) {
            Notification::make()
                ->title('Error')
                ->body('Judul tugas wajib diisi')
                ->danger()
                ->send();
            return;
        }

        $data = $this->newTaskData[$status];

        $task = DailyTask::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $status,
            'priority' => $validated['priority'],
            'task_date' => $validated['task_date'],
            'start_task_date' => $status === 'in_progress' ? today() : null,
            'project_id' => $data['project_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        if (!empty($data['assigned_users'])) {
            $task->assignedUsers()->sync($data['assigned_users']);
        }

        unset($this->creatingInColumn[$status]);
        unset($this->newTaskData[$status]);
        $this->currentFormStatus = null;

        Notification::make()
            ->title('Tugas Dibuat')
            ->body("Tugas '{$task->title}' berhasil dibuat")
            ->success()
            ->send();

        $this->handleRefresh();
    }

    /**
     * Cancel task creation
     */
    public function cancelKanbanTask(string $status): void
    {
        unset($this->creatingInColumn[$status]);
        unset($this->newTaskData[$status]);
        $this->currentFormStatus = null;
    }

    /**
     * Get task creation form schema
     */
    public function getTaskFormSchema(string $status): array
    {
        return [
            TextInput::make('title')
                ->label('Judul Tugas')
                ->placeholder('Masukkan judul tugas...')
                ->required()
                ->maxLength(255)
                ->autofocus()
                ->columnSpanFull(),

            Textarea::make('description')
                ->label('Deskripsi')
                ->placeholder('Deskripsi tugas (opsional)...')
                ->rows(2)
                ->maxLength(1000)
                ->columnSpanFull(),

            Select::make('priority')
                ->label('Prioritas')
                ->options([
                    'low' => 'Rendah',
                    'normal' => 'Normal',
                    'high' => 'Tinggi',
                    'urgent' => 'Mendesak',
                ])
                ->default('normal')
                ->required()
                ->native(false),

            DatePicker::make('task_date')
                ->label('Tanggal')
                ->default(today())
                ->required()
                ->native(false)
                ->displayFormat('d/m/Y'),
        ];
    }

    /**
     * Create form instance for specific status
     */
    public function form(Form $form): Form
    {
        $status = $this->currentFormStatus ?? 'pending';

        return $form
            ->schema($this->getTaskFormSchema($status))
            ->statePath('newTaskData.' . $status)
            ->model(DailyTask::class);
    }

    /**
     * Toggle show completed tasks
     */
    public function toggleCompletedTasks(): void
    {
        $this->showCompletedTasks = !$this->showCompletedTasks;
        $this->handleRefresh();
    }

    /**
     * Open task detail modal
     */
    public function openTaskDetail(int $taskId): void
    {
        $this->dispatch('openTaskDetailModal', taskId: $taskId);
    }

    public function render()
    {
        // Mark loading complete after first render
        if ($this->isInitialLoading) {
            $this->isInitialLoading = false;
        }

        return view('livewire.daily-task.components.kanban-board-component', [
            'kanbanTasks' => $this->kanbanTasks,
            'columns' => $this->columns,
        ]);
    }
}