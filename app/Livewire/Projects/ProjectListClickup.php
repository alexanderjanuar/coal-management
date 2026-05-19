<?php

namespace App\Livewire\Projects;

use App\Filament\Resources\ProjectResource;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\User;
use App\Models\UserProject;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Support\Contracts\TranslatableContentDriver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

class ProjectListClickup extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    public const HARD_CAP = 500;
    public const DEFAULT_GROUP_LIMIT = 10;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'type')]
    public string $typeFilter = 'all';

    #[Url(as: 'status')]
    public array $statusFilter = [];

    #[Url(as: 'priority')]
    public array $priorityFilter = [];

    #[Url(as: 'due')]
    public string $dueDateFilter = 'any';

    #[Url(as: 'pic')]
    public array $picFilter = [];

    #[Url(as: 'client')]
    public array $clientFilter = [];

    #[Url(as: 'assignee')]
    public array $assigneeFilter = [];

    #[Url(as: 'active_clients')]
    public bool $activeClientsOnly = true;

    #[Url(as: 'group')]
    public string $groupBy = 'status';

    #[Url(as: 'sort')]
    public string $sortField = 'name';

    #[Url(as: 'dir')]
    public string $sortDirection = 'asc';

    #[Url(as: 'cols')]
    public array $visibleColumns = ['status', 'client', 'assignees', 'progress'];

    #[Url(as: 'view')]
    public string $viewMode = 'list';

    public function switchView(string $mode): void
    {
        if (in_array($mode, ['list', 'board'], true)) {
            $this->viewMode = $mode;
        }
    }

    public array $expanded = [];
    public array $expandedGroups = [];

    // Status manager panel
    public bool $showStatusManager = false;
    public string $newStatusLabel = '';
    public string $newStatusColor = '#2563eb';
    public string $newStatusShape = 'empty';
    public string $newStatusCategory = 'active';

    // Inline edit state
    public ?int $editingStatusId = null;
    public string $editLabel = '';
    public string $editColor = '#2563eb';
    public string $editShape = 'empty';
    public string $editCategory = 'active';

    // Project view modal
    public ?int $viewingProjectId = null;
    public string $newNoteContent = '';
    public string $newNoteType = 'general';

    public function openProjectView(int $projectId): void
    {
        $this->viewingProjectId = $projectId;
        $this->resetNoteForm();
    }

    public function closeProjectView(): void
    {
        $this->viewingProjectId = null;
        $this->resetNoteForm();
    }

    protected function resetNoteForm(): void
    {
        $this->newNoteContent = '';
        $this->newNoteType = 'general';
        $this->resetErrorBag(['newNoteContent']);
    }

    public function addNote(): void
    {
        $this->validate([
            'newNoteContent' => 'required|string|min:2|max:2000',
            'newNoteType'    => 'required|in:general,important,blocker',
        ], [], [
            'newNoteContent' => 'catatan',
            'newNoteType'    => 'tipe catatan',
        ]);

        if (! $this->viewingProjectId) {
            return;
        }

        \App\Models\ProjectNote::create([
            'project_id' => $this->viewingProjectId,
            'user_id'    => auth()->id(),
            'content'    => trim($this->newNoteContent),
            'type'       => $this->newNoteType,
        ]);

        $this->resetNoteForm();
    }

    public function getViewingProjectProperty(): ?Project
    {
        if (! $this->viewingProjectId) {
            return null;
        }

        return Project::query()
            ->with([
                'client:id,name,status',
                'department:id,name',
                'pic:id,name,avatar_url,avatar_path',
                'teamMembers:id,name,avatar_url,avatar_path',
                'steps' => fn ($q) => $q->orderBy('order')->with([
                    'requiredDocuments' => fn ($r) => $r->with([
                        'submittedDocuments' => fn ($s) => $s->latest(),
                    ]),
                ]),
                'statusRecord',
                'notes' => fn ($q) => $q->with('user:id,name,avatar_url,avatar_path')->latest(),
            ])
            ->withCount([
                'steps',
                'steps as steps_completed_count' => fn ($q) => $q->where('status', 'completed'),
            ])
            ->find($this->viewingProjectId);
    }

    public function getDepartmentOptionsProperty()
    {
        return \App\Models\Department::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function updateProjectDepartment(int $projectId, ?int $departmentId): void
    {
        $project = Project::find($projectId);
        if (! $project) {
            return;
        }

        // Validate the department exists when one is selected
        if ($departmentId !== null && ! \App\Models\Department::whereKey($departmentId)->exists()) {
            Notification::make()
                ->title('Departemen tidak ditemukan')
                ->danger()
                ->send();
            return;
        }

        $project->department_id = $departmentId;
        $project->save();

        Notification::make()
            ->title('Departemen diperbarui')
            ->success()
            ->send();
    }

    public function downloadSubmittedDocument(int $documentId)
    {
        $doc = \App\Models\SubmittedDocument::find($documentId);
        if (! $doc || ! $doc->file_path) {
            Notification::make()
                ->title('Dokumen tidak ditemukan')
                ->danger()
                ->send();
            return null;
        }

        if (! \Illuminate\Support\Facades\Storage::disk('public')->exists($doc->file_path)) {
            Notification::make()
                ->title('File tidak ada di server')
                ->danger()
                ->send();
            return null;
        }

        return response()->download(
            \Illuminate\Support\Facades\Storage::disk('public')->path($doc->file_path),
            basename($doc->file_path),
        );
    }

    public const TOGGLEABLE_COLUMNS = [
        'status' => 'Status',
        'client' => 'Client',
        'type' => 'Type',
        'assignees' => 'Assignees',
        'progress' => 'Progress',
    ];

    public const PRIORITIES = [
        'urgent' => ['label' => 'Urgent', 'color' => '#b91c1c', 'bg' => '#fee2e2'],
        'normal' => ['label' => 'Normal', 'color' => '#0e7490', 'bg' => '#cffafe'],
        'low' => ['label' => 'Low', 'color' => '#64748b', 'bg' => '#f1f5f9'],
    ];

    public const STEP_STATUSES = [
        'pending' => ['label' => 'Pending', 'color' => '#94a3b8', 'bg' => '#f1f5f9'],
        'in_progress' => ['label' => 'In Progress', 'color' => '#3b82f6', 'bg' => '#dbeafe'],
        'waiting_for_documents' => ['label' => 'Waiting Docs', 'color' => '#eab308', 'bg' => '#fef9c3'],
        'completed' => ['label' => 'Completed', 'color' => '#22c55e', 'bg' => '#dcfce7'],
    ];

    public const TYPES = [
        'all' => 'Any',
        'single' => 'On Spot',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
    ];

    public const DUE_DATE_PRESETS = [
        'any' => 'Any',
        'overdue' => 'Overdue',
        'today' => 'Today',
        'this_week' => 'This Week',
        'this_month' => 'This Month',
        'no_due_date' => 'No Due Date',
    ];

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'typeFilter',
            'statusFilter',
            'priorityFilter',
            'dueDateFilter',
            'picFilter',
            'clientFilter',
            'assigneeFilter',
        ]);
    }

    public function removeStatus(string $status): void
    {
        $this->statusFilter = array_values(array_diff($this->statusFilter, [$status]));
    }

    public function removePriority(string $priority): void
    {
        $this->priorityFilter = array_values(array_diff($this->priorityFilter, [$priority]));
    }

    public function removePic(int $id): void
    {
        $this->picFilter = array_values(array_filter($this->picFilter, fn ($v) => (int) $v !== $id));
    }

    public function removeClient(int $id): void
    {
        $this->clientFilter = array_values(array_filter($this->clientFilter, fn ($v) => (int) $v !== $id));
    }

    public function removeAssignee(int $id): void
    {
        $this->assigneeFilter = array_values(array_filter($this->assigneeFilter, fn ($v) => (int) $v !== $id));
    }

    public function resetType(): void
    {
        $this->typeFilter = 'all';
    }

    public function resetDueDate(): void
    {
        $this->dueDateFilter = 'any';
    }

    public function hasActiveFilters(): bool
    {
        return $this->search !== ''
            || $this->typeFilter !== 'all'
            || $this->dueDateFilter !== 'any'
            || !empty($this->statusFilter)
            || !empty($this->priorityFilter)
            || !empty($this->picFilter)
            || !empty($this->clientFilter)
            || !empty($this->assigneeFilter);
    }

    public function getActiveFilterCountProperty(): int
    {
        $count = 0;
        $count += \count($this->statusFilter);
        $count += \count($this->priorityFilter);
        $count += \count($this->picFilter);
        $count += \count($this->clientFilter);
        $count += \count($this->assigneeFilter);
        if ($this->typeFilter !== 'all') $count++;
        if ($this->dueDateFilter !== 'any') $count++;
        return $count;
    }

    /**
     * The status map used throughout the view.
     * Loaded from the project_statuses table — supports user-created statuses.
     *
     * Returns: [ key => ['label', 'color', 'bg', 'shape', 'category', 'sort_order', 'is_system'] ]
     * Ordered by category then sort_order.
     */
    public function getStatusMapProperty(): array
    {
        return \App\Models\ProjectStatus::ordered()
            ->get()
            ->mapWithKeys(fn ($s) => [
                $s->key => [
                    'label'      => $s->label,
                    'color'      => $s->color,
                    'bg'         => $s->bg_color,
                    'shape'      => $s->shape,
                    'category'   => $s->category,
                    'sort_order' => $s->sort_order,
                    'is_system'  => $s->is_system,
                ],
            ])
            ->toArray();
    }

    public function toggleColumn(string $key): void
    {
        if (!\array_key_exists($key, self::TOGGLEABLE_COLUMNS)) {
            return;
        }
        if (\in_array($key, $this->visibleColumns, true)) {
            $this->visibleColumns = array_values(array_diff($this->visibleColumns, [$key]));
        } else {
            $this->visibleColumns[] = $key;
        }
    }

    public function isColumnVisible(string $key): bool
    {
        return \in_array($key, $this->visibleColumns, true);
    }

    public function getGridTemplateProperty(): string
    {
        $cols = ['30px', '30px', 'minmax(220px, 1.5fr)'];          // expand, status-icon, name
        if ($this->isColumnVisible('status'))    $cols[] = '150px'; // status badge
        if ($this->isColumnVisible('client'))    $cols[] = '160px'; // client
        if ($this->isColumnVisible('type'))      $cols[] = '90px';  // type
        $cols[] = '110px';                                          // priority (always)
        if ($this->isColumnVisible('assignees')) $cols[] = '140px'; // assignees
        $cols[] = '150px';                                          // department (always — replaces due)
        if ($this->isColumnVisible('progress'))  $cols[] = '140px'; // progress
        $cols[] = '40px';                                           // actions (always)
        return implode(' ', $cols);
    }

    public function toggleExpand(int $projectId): void
    {
        if (\in_array($projectId, $this->expanded, true)) {
            $this->expanded = array_values(array_diff($this->expanded, [$projectId]));
        } else {
            $this->expanded[] = $projectId;
        }
    }

    public function toggleGroupExpand(string $groupKey): void
    {
        if (\in_array($groupKey, $this->expandedGroups, true)) {
            $this->expandedGroups = array_values(array_diff($this->expandedGroups, [$groupKey]));
        } else {
            $this->expandedGroups[] = $groupKey;
        }
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updateStatus(int $projectId, string $status): void
    {
        $statusMap = $this->statusMap;
        if (!\array_key_exists($status, $statusMap)) {
            return;
        }

        $project = $this->baseQuery()->find($projectId);
        if (!$project || $project->status === $status) {
            return;
        }

        $project->status = $status;
        $project->save();

        Notification::make()
            ->title("Status diubah ke " . $statusMap[$status]['label'])
            ->success()
            ->send();
    }

    public function updatePriority(int $projectId, string $priority): void
    {
        if (!\array_key_exists($priority, self::PRIORITIES)) {
            return;
        }

        $project = $this->baseQuery()->find($projectId);
        if (!$project || $project->priority === $priority) {
            return;
        }

        $project->priority = $priority;
        $project->save();

        Notification::make()
            ->title("Prioritas diubah ke " . self::PRIORITIES[$priority]['label'])
            ->success()
            ->send();
    }

    public function addProjectMember(int $projectId, int $userId): void
    {
        $project = $this->baseQuery()->find($projectId);
        if (!$project) return;

        $exists = UserProject::where('project_id', $projectId)
            ->where('user_id', $userId)
            ->exists();
        if ($exists) return;

        $user = User::find($userId);
        if (!$user) return;

        UserProject::create([
            'project_id'    => $projectId,
            'user_id'       => $userId,
            'role'          => null,
            'assigned_date' => now()->toDateString(),
        ]);

        Notification::make()
            ->title("{$user->name} ditambahkan ke {$project->name}")
            ->success()
            ->send();
    }

    public function removeProjectMember(int $projectId, int $userId): void
    {
        $project = $this->baseQuery()->find($projectId);
        if (!$project) return;

        $user = User::find($userId);
        if (!$user) return;

        UserProject::where('project_id', $projectId)
            ->where('user_id', $userId)
            ->delete();

        Notification::make()
            ->title("{$user->name} dikeluarkan dari {$project->name}")
            ->success()
            ->send();
    }

    /**
     * Pool of users that can be added as project members.
     * Excludes anyone with the 'client' role.
     */
    public function getTeamPoolProperty()
    {
        return User::whereDoesntHave('roles', fn ($q) => $q->where('name', 'client'))
            ->orderBy('name')
            ->get(['id', 'name', 'avatar_url', 'avatar_path']);
    }

    protected function baseQuery(): Builder
    {
        $user = auth()->user();
        $query = Project::query();

        if (!$user->hasRole('super-admin')) {
            $query->whereIn('client_id', function ($sub) use ($user) {
                $sub->select('client_id')
                    ->from('user_clients')
                    ->where('user_id', $user->id);
            });
        }

        return $query;
    }

    protected function filteredQuery(): Builder
    {
        $query = $this->baseQuery()
            ->with([
                'client:id,name',
                'department:id,name',
                'pic:id,name,avatar_url,avatar_path',
                'teamMembers:id,name,avatar_url,avatar_path',
                'steps:id,project_id,status,name,order,due_date,priority',
                'statusRecord',
            ])
            ->withCount([
                'steps',
                'steps as steps_completed_count' => fn ($q) => $q->where('status', 'completed'),
            ]);

        if ($this->search !== '') {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhereHas('client', fn ($c) => $c->where('name', 'like', $term));
            });
        }

        if ($this->typeFilter !== 'all') {
            $query->where('type', $this->typeFilter);
        }

        if (!empty($this->statusFilter)) {
            $query->whereIn('status', $this->statusFilter);
        }

        if (!empty($this->priorityFilter)) {
            $query->whereIn('priority', $this->priorityFilter);
        }

        if (!empty($this->picFilter)) {
            $query->whereIn('pic_id', $this->picFilter);
        }

        if (!empty($this->clientFilter)) {
            $query->whereIn('client_id', $this->clientFilter);
        }

        if ($this->activeClientsOnly) {
            $query->whereHas('client', fn ($q) => $q->where('status', 'Active'));
        }

        if (!empty($this->assigneeFilter)) {
            $query->whereHas('userProjects', function ($q) {
                $q->whereIn('user_id', $this->assigneeFilter);
            });
        }

        $this->applyDueDateFilter($query);

        return $this->applySort($query);
    }

    protected function applySort(Builder $query): Builder
    {
        $dir = $this->sortDirection;

        return match ($this->sortField) {
            // Sort by client name via subquery (no join — keeps the model hydrated cleanly)
            'client' => $query->orderBy(
                \App\Models\Client::select('name')
                    ->whereColumn('clients.id', 'projects.client_id'),
                $dir,
            ),
            // Sort by department name via subquery
            'department' => $query->orderBy(
                \App\Models\Department::select('name')
                    ->whereColumn('departments.id', 'projects.department_id'),
                $dir,
            ),
            // Sort by number of team members
            'assignees' => $query
                ->withCount('teamMembers')
                ->orderBy('team_members_count', $dir),
            // Sort by computed progress ratio (completed / total steps)
            'progress' => $query->orderByRaw(
                "CASE WHEN (SELECT COUNT(*) FROM project_steps WHERE project_steps.project_id = projects.id) > 0
                      THEN (SELECT COUNT(*) FROM project_steps WHERE project_steps.project_id = projects.id AND status = 'completed')
                         * 1.0 /
                         (SELECT COUNT(*) FROM project_steps WHERE project_steps.project_id = projects.id)
                      ELSE 0 END $dir"
            ),
            // Priority — enum-ish order: urgent > normal > low (or reverse)
            'priority' => $query->orderByRaw(
                "FIELD(priority, 'urgent', 'normal', 'low') " . ($dir === 'asc' ? 'asc' : 'desc')
            ),
            // Direct columns: name, status, type, due_date
            default => $query->orderBy($this->sortField, $dir),
        };
    }

    protected function applyDueDateFilter(Builder $query): void
    {
        match ($this->dueDateFilter) {
            'overdue' => $query->whereDate('due_date', '<', now())
                ->whereHas('statusRecord', fn ($q) => $q->whereNotIn('category', ['done', 'closed'])),
            'today' => $query->whereDate('due_date', now()->toDateString()),
            'this_week' => $query->whereBetween('due_date', [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString(),
            ]),
            'this_month' => $query->whereBetween('due_date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ]),
            'no_due_date' => $query->whereNull('due_date'),
            default => null,
        };
    }

    public function getPicOptionsProperty()
    {
        $ids = $this->baseQuery()
            ->whereNotNull('pic_id')
            ->distinct()
            ->pluck('pic_id');

        return User::whereIn('id', $ids)
            ->orderBy('name')
            ->get(['id', 'name', 'avatar_url', 'avatar_path']);
    }

    public function getClientOptionsProperty()
    {
        $ids = $this->baseQuery()
            ->distinct()
            ->pluck('client_id');

        return Client::whereIn('id', $ids)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getAssigneeOptionsProperty()
    {
        $projectIds = $this->baseQuery()->pluck('id');

        $userIds = UserProject::whereIn('project_id', $projectIds)
            ->distinct()
            ->pluck('user_id');

        return User::whereIn('id', $userIds)
            ->orderBy('name')
            ->get(['id', 'name', 'avatar_url', 'avatar_path']);
    }

    public function getTotalCountProperty(): int
    {
        return $this->filteredQuery()->count();
    }

    public function getProjectsProperty()
    {
        return $this->filteredQuery()->limit(self::HARD_CAP)->get();
    }

    /**
     * Columns for the kanban view, driven by $groupBy.
     * Each column: ['key', 'label', 'color'?, 'shape'?, 'count', 'projects'].
     * For PIC/client the 'key' is the foreign-key id (or null for unassigned).
     */
    public function getKanbanColumnsProperty(): array
    {
        $projects = $this->projects;

        return match ($this->groupBy) {
            'priority' => $this->kanbanByPriority($projects),
            'pic'      => $this->kanbanByPic($projects),
            'client'   => $this->kanbanByClient($projects),
            'none'     => $this->kanbanSingle($projects),
            default    => $this->kanbanByStatus($projects),  // 'status' is the default
        };
    }

    protected function kanbanByStatus($projects): array
    {
        $statusMap = $this->statusMap;
        $cols = [];

        foreach ($statusMap as $key => $meta) {
            $items = $projects->where('status', $key)->values();
            $cols[$key] = [
                'key'      => $key,
                'label'    => $meta['label'],
                'color'    => $meta['color'],
                'shape'    => $meta['shape'] ?? 'empty',
                'count'    => $items->count(),
                'projects' => $items,
            ];
        }

        $unknown = $projects->whereNotIn('status', array_keys($statusMap))->values();
        if ($unknown->isNotEmpty()) {
            $cols['__unknown'] = [
                'key'      => null,
                'label'    => 'Lainnya',
                'color'    => '#94a3b8',
                'shape'    => 'empty',
                'count'    => $unknown->count(),
                'projects' => $unknown,
            ];
        }

        return $cols;
    }

    protected function kanbanByPriority($projects): array
    {
        $cols = [];
        foreach (self::PRIORITIES as $key => $meta) {
            $items = $projects->where('priority', $key)->values();
            $cols[$key] = [
                'key'      => $key,
                'label'    => $meta['label'],
                'color'    => $meta['color'],
                'count'    => $items->count(),
                'projects' => $items,
            ];
        }
        return $cols;
    }

    protected function kanbanByPic($projects): array
    {
        $cols = [];

        // Unassigned column always shown first
        $unassigned = $projects->whereNull('pic_id')->values();
        $cols['_none'] = [
            'key'      => null,
            'label'    => 'Belum ada PIC',
            'color'    => '#94a3b8',
            'count'    => $unassigned->count(),
            'projects' => $unassigned,
        ];

        $picIds = $projects->whereNotNull('pic_id')->pluck('pic_id')->unique();
        if ($picIds->isNotEmpty()) {
            $picUsers = User::whereIn('id', $picIds)->orderBy('name')->get(['id', 'name', 'avatar_url', 'avatar_path']);
            foreach ($picUsers as $pic) {
                $items = $projects->where('pic_id', $pic->id)->values();
                $cols['p_' . $pic->id] = [
                    'key'      => $pic->id,
                    'label'    => $pic->name,
                    'color'    => '#6366f1',
                    'count'    => $items->count(),
                    'projects' => $items,
                ];
            }
        }

        return $cols;
    }

    protected function kanbanByClient($projects): array
    {
        $cols = [];

        $unassigned = $projects->whereNull('client_id')->values();
        if ($unassigned->isNotEmpty()) {
            $cols['_none'] = [
                'key'      => null,
                'label'    => 'Tanpa klien',
                'color'    => '#94a3b8',
                'count'    => $unassigned->count(),
                'projects' => $unassigned,
            ];
        }

        $clientIds = $projects->whereNotNull('client_id')->pluck('client_id')->unique();
        if ($clientIds->isNotEmpty()) {
            $clients = Client::whereIn('id', $clientIds)->orderBy('name')->get(['id', 'name']);
            foreach ($clients as $client) {
                $items = $projects->where('client_id', $client->id)->values();
                $cols['c_' . $client->id] = [
                    'key'      => $client->id,
                    'label'    => $client->name,
                    'color'    => '#0ea5e9',
                    'count'    => $items->count(),
                    'projects' => $items,
                ];
            }
        }

        return $cols;
    }

    protected function kanbanSingle($projects): array
    {
        return [
            '_all' => [
                'key'      => null,
                'label'    => 'Semua proyek',
                'color'    => '#64748b',
                'count'    => $projects->count(),
                'projects' => $projects,
            ],
        ];
    }

    public function updateProjectPic(int $projectId, $picId): void
    {
        $picId = $picId === null || $picId === '' ? null : (int) $picId;

        $project = $this->baseQuery()->find($projectId);
        if (! $project) return;
        if ((int) $project->pic_id === (int) $picId) return;

        if ($picId !== null && ! User::whereKey($picId)->exists()) {
            Notification::make()->title('PIC tidak ditemukan')->danger()->send();
            return;
        }

        $project->pic_id = $picId;
        $project->save();

        Notification::make()->title('PIC proyek diperbarui')->success()->send();
    }

    /**
     * Returns groups as: [groupKey => ['all' => Collection, 'visible' => Collection, 'hasMore' => bool, 'hidden' => int]]
     */
    public function getGroupedProjectsProperty(): array
    {
        $projects = $this->projects;

        if ($this->groupBy === 'none') {
            return ['' => $this->wrapGroup($projects)];
        }

        $grouped = [];
        foreach ($projects as $project) {
            $key = match ($this->groupBy) {
                'status' => $project->status ?? 'unknown',
                'priority' => $project->priority ?? 'unknown',
                'pic' => $project->pic?->name ?? 'Unassigned',
                'client' => $project->client?->name ?? 'No Client',
                default => '',
            };
            $grouped[$key] ??= collect();
            $grouped[$key]->push($project);
        }

        if ($this->groupBy === 'status') {
            // Use the DB-driven status order (category then sort_order)
            $grouped = $this->reorder($grouped, array_keys($this->statusMap));
        } elseif ($this->groupBy === 'priority') {
            $grouped = $this->reorder($grouped, array_keys(self::PRIORITIES));
        } else {
            ksort($grouped);
        }

        $result = [];
        foreach ($grouped as $key => $items) {
            $result[$key] = $this->wrapGroup($items, (string) $key);
        }

        return $result;
    }

    protected function reorder(array $grouped, array $order): array
    {
        $sorted = [];
        foreach ($order as $key) {
            if (isset($grouped[$key])) {
                $sorted[$key] = $grouped[$key];
            }
        }
        foreach ($grouped as $k => $v) {
            if (!isset($sorted[$k])) {
                $sorted[$k] = $v;
            }
        }
        return $sorted;
    }

    protected function wrapGroup($items, string $groupKey = ''): array
    {
        $count = $items->count();
        $isExpanded = \in_array($groupKey, $this->expandedGroups, true);
        $limit = self::DEFAULT_GROUP_LIMIT;

        $visible = ($isExpanded || $count <= $limit) ? $items : $items->take($limit);

        return [
            'all' => $items,
            'visible' => $visible,
            'total' => $count,
            'shown' => $visible->count(),
            'hasMore' => $count > $limit,
            'hidden' => max(0, $count - $visible->count()),
            'expanded' => $isExpanded,
        ];
    }

    public function getGroupLabel(string $key): string
    {
        return match ($this->groupBy) {
            'status' => $this->statusMap[$key]['label'] ?? ucfirst($key),
            'priority' => self::PRIORITIES[$key]['label'] ?? ucfirst($key),
            default => $key ?: 'Other',
        };
    }

    public function getGroupColor(string $key): array
    {
        return match ($this->groupBy) {
            'status' => $this->statusMap[$key] ?? ['color' => '#64748b', 'bg' => '#f1f5f9'],
            'priority' => self::PRIORITIES[$key] ?? ['color' => '#64748b', 'bg' => '#f1f5f9'],
            default => ['color' => '#64748b', 'bg' => '#f1f5f9'],
        };
    }

    public function viewUrl(Project $project): string
    {
        return ProjectResource::getUrl('view', ['record' => $project]);
    }

    public function editUrl(Project $project): string
    {
        return ProjectResource::getUrl('edit', ['record' => $project]);
    }

    public function deleteProjectAction(): Action
    {
        return Action::make('deleteProject')
            ->requiresConfirmation()
            ->modalHeading('Hapus Proyek')
            ->modalDescription(function (array $arguments): string {
                $project = Project::find($arguments['project'] ?? null);
                $name = $project?->name ?? 'proyek ini';
                return "Apakah Anda yakin ingin menghapus '{$name}'? Semua langkah, tugas, dan dokumen terkait akan ikut terhapus. Tindakan ini tidak dapat dibatalkan.";
            })
            ->modalSubmitActionLabel('Ya, Hapus')
            ->modalCancelActionLabel('Batal')
            ->modalIcon('heroicon-o-trash')
            ->color('danger')
            ->action(function (array $arguments): void {
                $project = Project::find($arguments['project'] ?? null);
                if (!$project) return;

                $name = $project->name;
                $project->delete();

                Notification::make()
                    ->title("Proyek '{$name}' dihapus")
                    ->success()
                    ->send();
            });
    }

    public function openStatusManager(): void
    {
        $this->showStatusManager = true;
    }

    public function closeStatusManager(): void
    {
        $this->showStatusManager = false;
        $this->newStatusLabel = '';
        $this->newStatusColor = '#2563eb';
        $this->newStatusShape = 'empty';
        $this->newStatusCategory = 'active';
        $this->editingStatusId = null;
        $this->editLabel = '';
        $this->editColor = '#2563eb';
        $this->editShape = 'empty';
        $this->editCategory = 'active';
        $this->resetErrorBag();
    }

    public function startEditStatus(int $id): void
    {
        if (!auth()->user()->hasRole('super-admin')) {
            return;
        }

        $status = ProjectStatus::find($id);
        if (!$status || $status->is_system) {
            return;
        }

        $this->editingStatusId = $id;
        $this->editLabel      = $status->label;
        $this->editColor      = $status->color;
        $this->editShape      = $status->shape;
        $this->editCategory   = $status->category;
        $this->resetErrorBag();
    }

    public function cancelEdit(): void
    {
        $this->editingStatusId = null;
        $this->editLabel = '';
        $this->editColor = '#2563eb';
        $this->editShape = 'empty';
        $this->editCategory = 'active';
        $this->resetErrorBag();
    }

    public function saveStatusEdit(): void
    {
        if (!auth()->user()->hasRole('super-admin') || !$this->editingStatusId) {
            return;
        }

        $status = ProjectStatus::find($this->editingStatusId);
        if (!$status || $status->is_system) {
            return;
        }

        $this->validate([
            'editLabel'    => 'required|string|min:2|max:60',
            'editColor'    => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'editShape'    => ['required', Rule::in(ProjectStatus::SHAPES)],
            'editCategory' => ['required', Rule::in(array_keys(ProjectStatus::CATEGORIES))],
        ]);

        $status->update([
            'label'    => $this->editLabel,
            'color'    => $this->editColor,
            'shape'    => $this->editShape,
            'category' => $this->editCategory,
        ]);

        $this->editingStatusId = null;
        $this->editLabel = '';

        Notification::make()
            ->title('Status berhasil diperbarui')
            ->success()
            ->send();
    }

    public function createStatus(): void
    {
        if (!auth()->user()->hasRole('super-admin')) {
            return;
        }

        $this->validate([
            'newStatusLabel'    => 'required|string|min:2|max:60',
            'newStatusColor'    => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'newStatusShape'    => ['required', Rule::in(ProjectStatus::SHAPES)],
            'newStatusCategory' => ['required', Rule::in(array_keys(ProjectStatus::CATEGORIES))],
        ]);

        $key = Str::slug($this->newStatusLabel, '_');

        if (ProjectStatus::where('key', $key)->exists()) {
            $this->addError('newStatusLabel', 'A status with a similar name already exists.');
            return;
        }

        $maxOrder = ProjectStatus::where('category', $this->newStatusCategory)->max('sort_order') ?? 0;

        ProjectStatus::create([
            'key'        => $key,
            'label'      => $this->newStatusLabel,
            'color'      => $this->newStatusColor,
            'shape'      => $this->newStatusShape,
            'category'   => $this->newStatusCategory,
            'sort_order' => $maxOrder + 1,
            'is_system'  => false,
        ]);

        $this->newStatusLabel = '';
        $this->newStatusColor = '#2563eb';
        $this->newStatusShape = 'empty';
        $this->newStatusCategory = 'active';
        $this->resetErrorBag();

        Notification::make()
            ->title('Status berhasil dibuat')
            ->success()
            ->send();
    }

    public function deleteStatus(int $id): void
    {
        if (!auth()->user()->hasRole('super-admin')) {
            return;
        }

        $status = ProjectStatus::find($id);
        if (!$status) {
            return;
        }

        if (!$status->canBeDeleted()) {
            $reason = $status->is_system
                ? 'Status sistem tidak dapat dihapus.'
                : 'Status ini masih digunakan oleh ' . $status->projects()->count() . ' proyek.';

            Notification::make()
                ->title('Tidak dapat menghapus status')
                ->body($reason)
                ->danger()
                ->send();
            return;
        }

        $label = $status->label;
        $status->delete();

        Notification::make()
            ->title("Status '{$label}' berhasil dihapus")
            ->success()
            ->send();
    }

    public function getStatusesForManagerProperty()
    {
        return ProjectStatus::ordered()->withCount('projects')->get();
    }

    public function render()
    {
        return view('livewire.projects.project-list-clickup', [
            'grouped' => $this->groupedProjects,
            'totalCount' => $this->totalCount,
            'isCapped' => $this->totalCount > self::HARD_CAP,
            'picOptions' => $this->picOptions,
            'clientOptions' => $this->clientOptions,
            'assigneeOptions' => $this->assigneeOptions,
            'teamPool' => $this->teamPool,
            'statuses' => $this->statusMap,
        ]);
    }
}
