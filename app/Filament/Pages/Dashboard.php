<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DocumentsOverview;
use App\Models\Client;
use App\Models\SubmittedDocument;
use App\Models\Progress;
use App\Models\Project;
use App\Models\Task;
use App\Models\RequiredDocument;
use App\Models\DailyTask;
use Filament\Pages\Page;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\DB;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static string $view = 'filament.pages.dashboard';

    public $previewingDocument = null;
    public $previewUrl = null;
    public $fileType = null;

    // Modal properties
    public $showProjectModal = false;
    public $showDocumentModal = false;
    public $modalTitle = '';
    public $modalData = [];
    public $modalType = '';
    public $currentStatus = '';
    public $currentCount = 0;

    #[On('openProjectModal')]
    public function openProjectModal($status, $count)
    {
        $this->currentStatus = $status;
        $this->currentCount = $count;
        $this->modalType = 'project';
        
        // Set modal title based on status
        $this->modalTitle = match($status) {
            'all' => 'Semua Proyek (' . $count . ')',
            'in_progress' => 'Proyek Aktif (' . $count . ')',
            'completed' => 'Proyek Selesai (' . $count . ')',
            'draft' => 'Proyek Draft (' . $count . ')',
            'canceled' => 'Proyek Dibatalkan (' . $count . ')',
            default => 'Proyek (' . $count . ')'
        };

        // Get projects data
        $this->loadProjectData($status);
        
        // Open the modal using Filament's dispatch system
        $this->dispatch('open-modal', id: 'project-stats-modal');
    }

    #[On('openDocumentModal')]
    public function openDocumentModal($status, $count)
    {
        \Log::info('openDocumentModal called', ['status' => $status, 'count' => $count]);
        
        $this->currentStatus = $status;
        $this->currentCount = $count;
        $this->modalType = 'document';
        
        $this->modalTitle = match($status) {
            'pending_review' => 'Dokumen Pending Review (' . $count . ')',
            'uploaded' => 'Dokumen Terupload (' . $count . ')',
            'approved' => 'Dokumen Disetujui (' . $count . ')',
            'rejected' => 'Dokumen Ditolak (' . $count . ')',
            default => 'Dokumen (' . $count . ')'
        };

        $this->loadDocumentData($status);
        
        \Log::info('Modal data loaded', ['count' => count($this->modalData)]);
        if (!empty($this->modalData)) {
            \Log::info('First document data', $this->modalData[0]);
        }
        
        // Open the modal using Filament's dispatch system
        $this->dispatch('open-modal', id: 'document-stats-modal');
    }

    public function closeModal()
    {
        $this->modalData = [];
        $this->modalTitle = '';
        $this->currentStatus = '';
        $this->currentCount = 0;
        
        // Close both modals using Filament's dispatch system
        $this->dispatch('close-modal', id: 'project-stats-modal');
        $this->dispatch('close-modal', id: 'document-stats-modal');
    }

    private function loadProjectData($status)
    {
        $query = Project::with(['client', 'pic'])
            ->select('id', 'name', 'client_id', 'pic_id', 'status', 'priority', 'due_date', 'created_at');

        // Filter for non-admin users
        if (!auth()->user()->hasRole('super-admin')) {
            $query->whereIn('client_id', function ($subQuery) {
                $subQuery->select('client_id')
                    ->from('user_clients')
                    ->where('user_id', auth()->id());
            });
        }

        // Apply status filter
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $this->modalData = $query->orderBy('created_at', 'desc')
            ->limit(50) // Limit to prevent performance issues
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'client_name' => $project->client->name ?? 'Tidak ada klien',
                    'pic_name' => $project->pic->name ?? 'Belum ditugaskan',
                    'status' => $project->status,
                    'priority' => $project->priority,
                    'due_date' => $project->due_date?->format('d M Y'),
                    'created_at' => $project->created_at->format('d M Y'),
                    'url' => route('filament.admin.resources.projects.view', $project),
                ];
            })
            ->toArray();
    }

    private function loadDocumentData($status)
    {
        $query = RequiredDocument::with(['projectStep.project.client'])
            ->select('id', 'name', 'status', 'project_step_id', 'created_at', 'updated_at')
            ->whereHas('projectStep.project', function ($projectQuery) {
                if (!auth()->user()->hasRole('super-admin')) {
                    $projectQuery->whereIn('client_id', function ($subQuery) {
                        $subQuery->select('client_id')
                            ->from('user_clients')
                            ->where('user_id', auth()->id());
                    });
                }
            });

        // Apply status filter
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $this->modalData = $query->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($document) {
                return [
                    'id' => $document->id,
                    'name' => $document->name,
                    'project_name' => $document->projectStep?->project?->name ?? 'Tidak ada proyek',
                    'client_name' => $document->projectStep?->project?->client?->name ?? 'Tidak ada klien',
                    'status' => $document->status,
                    'created_at' => $document->created_at->format('d M Y'),
                    'updated_at' => $document->updated_at->format('d M Y H:i'),
                    'url' => $document->projectStep?->project ? route('filament.admin.resources.projects.view', [
                        'record' => $document->projectStep->project->id,
                        'openDocument' => $document->id
                    ]) : '#',
                ];
            })
            ->toArray();
    }

    /**
     * Get dashboard statistics for welcome card
     */
    public function getDashboardStats(): array
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super-admin');
        
        // Get client IDs for non-admin users once
        $clientIds = $isSuperAdmin ? null : $user->userClients()->pluck('client_id')->toArray();

        // Base project query with role-based filtering
        $baseProjectQuery = function() use ($isSuperAdmin, $clientIds, $user) {
            $query = Project::query();
            
            if (!$isSuperAdmin && !empty($clientIds)) {
                $query->whereIn('client_id', $clientIds)
                    ->where(function($subQuery) use ($user) {
                        $subQuery->where('pic_id', $user->id)
                                ->orWhereHas('userProject', function($q) use ($user) {
                                    $q->where('user_id', $user->id);
                                });
                    });
            }
            
            return $query;
        };

        // Project counts
        $activeProjectCount = $baseProjectQuery()
            ->whereNotIn('status', ['completed', 'canceled'])
            ->count();

        $completedProjectCount = $baseProjectQuery()
            ->where('status', 'completed')
            ->count();

        $urgentProjectCount = $baseProjectQuery()
            ->whereNotIn('status', ['completed', 'canceled'])
            ->where('priority', 'urgent')
            ->count();

        // Updated Daily tasks counts
        $todayTasksCount = 0;
        $completedTasksCount = 0;
        $incompleteTasksCount = 0;
        
        if (class_exists(DailyTask::class)) {
            $baseTaskQuery = DailyTask::where(function($query) use ($user) {
                    $query->where('created_by', $user->id)
                        ->orWhereHas('assignedUsers', function($q) use ($user) {
                            $q->where('user_id', $user->id);
                        });
                })
                ->where(function($dateQuery) {
                    $today = today();
                    $dateQuery->where(function($q) use ($today) {
                        $q->where('start_task_date', '<=', $today)
                        ->where('task_date', '>=', $today);
                    })->orWhere(function($q) use ($today) {
                        $q->where('task_date', $today)
                        ->whereNull('start_task_date');
                    });
                });

            $todayTasksCount = $baseTaskQuery->count();
            $completedTasksCount = (clone $baseTaskQuery)->where('status', 'completed')->count();
            $incompleteTasksCount = $todayTasksCount - $completedTasksCount;
        }

        // NEW: Document statistics
        $submittedDocumentsQuery = SubmittedDocument::where('user_id', $user->id);
        
        if (!$isSuperAdmin && !empty($clientIds)) {
            $submittedDocumentsQuery->whereHas('requiredDocument.projectStep.project', function($q) use ($clientIds) {
                $q->whereIn('client_id', $clientIds);
            });
        }

        $totalSubmittedDocuments = $submittedDocumentsQuery->count();
        $approvedDocuments = (clone $submittedDocumentsQuery)->where('status', 'approved')->count();
        $pendingDocuments = (clone $submittedDocumentsQuery)->whereIn('status', ['uploaded', 'pending_review'])->count();
        $rejectedDocuments = (clone $submittedDocumentsQuery)->where('status', 'rejected')->count();

        return [
            'active_projects' => $activeProjectCount,
            'completed_projects' => $completedProjectCount,
            'urgent_projects' => $urgentProjectCount,
            'today_tasks' => $todayTasksCount,
            'completed_tasks_today' => $completedTasksCount,
            'incomplete_tasks_today' => $incompleteTasksCount,
            // NEW: Document fields
            'submitted_documents' => $totalSubmittedDocuments,
            'approved_documents' => $approvedDocuments,
            'pending_documents' => $pendingDocuments,
            'rejected_documents' => $rejectedDocuments,
            'client_ids' => $clientIds,
            'is_super_admin' => $isSuperAdmin,
        ];
    }

    /**
     * Get recent projects for accordion
     */
    public function getRecentProjects(int $limit = 3): array
    {
        $stats = $this->getDashboardStats();
        
        $query = Project::with(['client'])
            ->whereNotIn('status', ['completed', 'canceled']);

        if (!$stats['is_super_admin'] && !empty($stats['client_ids'])) {
            $query->whereIn('client_id', $stats['client_ids'])
                  ->where(function($subQuery) use ($stats) {
                      $user = auth()->user();
                      $subQuery->where('pic_id', $user->id)
                               ->orWhereHas('userProject', function($q) use ($user) {
                                   $q->where('user_id', $user->id);
                               });
                  });
        }

        return $query->orderByRaw("CASE WHEN priority = 'urgent' THEN 0 WHEN priority = 'normal' THEN 1 ELSE 2 END")
                    ->orderBy('due_date')
                    ->limit($limit)
                    ->get()
                    ->toArray();
    }

    /**
     * Get today's daily tasks
     */
    public function getTodayTasks(int $limit = 4): array
    {
        if (!class_exists(DailyTask::class)) {
            return [];
        }

        $user = auth()->user();
        $today = today();

        return DailyTask::with(['project', 'subtasks'])
            ->where(function($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhereHas('assignedUsers', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->where(function($dateQuery) use ($today) {
                $dateQuery->where(function($q) use ($today) {
                    // Task yang start_task_date <= today <= task_date
                    $q->where('start_task_date', '<=', $today)
                    ->where('task_date', '>=', $today);
                })->orWhere(function($q) use ($today) {
                    // Task yang task_date = today (untuk backward compatibility)
                    $q->where('task_date', $today)
                    ->whereNull('start_task_date');
                });
            })
            ->orderByRaw("CASE WHEN priority = 'urgent' THEN 0 WHEN priority = 'high' THEN 1 WHEN priority = 'normal' THEN 2 ELSE 3 END")
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get recently completed projects
     */
    public function getCompletedProjects(int $limit = 3): array
    {
        $stats = $this->getDashboardStats();
        
        $query = Project::with(['client'])
            ->where('status', 'completed');

        if (!$stats['is_super_admin'] && !empty($stats['client_ids'])) {
            $query->whereIn('client_id', $stats['client_ids'])
                  ->where(function($subQuery) use ($stats) {
                      $user = auth()->user();
                      $subQuery->where('pic_id', $user->id)
                               ->orWhereHas('userProject', function($q) use ($user) {
                                   $q->where('user_id', $user->id);
                               });
                  });
        }

        return $query->orderBy('updated_at', 'desc')
                    ->limit($limit)
                    ->get()
                    ->toArray();
    }

    public function getViewData(): array
    {
        $user = auth()->user();
        $currentStatus = request()->query('status', 'in_progress');

        // Initialize clients query with projects ordered by latest update
        $clientsQuery = Client::query()
            ->whereHas('projects', function ($query) use ($currentStatus) {
                if ($currentStatus !== 'all') {
                    $query->where('status', $currentStatus);
                }
            })
            ->with([
                'projects' => function ($query) use ($currentStatus) {
                    // Add ordering by priority first, urgent projects come first
                    $query->orderByRaw("CASE WHEN priority = 'urgent' THEN 0 ELSE 1 END")
                        ->select('projects.*')
                        ->addSelect([
                            'latest_activity' => DB::query()
                                ->select(DB::raw('GREATEST(
                                    COALESCE(MAX(tasks.updated_at), "1970-01-01"),
                                    COALESCE(MAX(required_documents.updated_at), "1970-01-01")
                                )'))
                                ->from('project_steps')
                                ->leftJoin('tasks', 'project_steps.id', '=', 'tasks.project_step_id')
                                ->leftJoin('required_documents', 'project_steps.id', '=', 'required_documents.project_step_id')
                                ->whereColumn('project_steps.project_id', 'projects.id')
                                ->limit(1)
                        ])
                        ->orderByRaw('COALESCE(latest_activity, updated_at) DESC');

                    if ($currentStatus !== 'all') {
                        $query->where('status', $currentStatus);
                    }
                },
                'projects.steps.tasks',
                'projects.steps.requiredDocuments',
                'projects.steps.requiredDocuments.submittedDocuments'
            ])
            // Order clients based on urgent projects and latest activity
            ->addSelect([
                'has_urgent_projects' => Project::select(DB::raw('COUNT(*)'))
                    ->whereColumn('projects.client_id', 'clients.id')
                    ->where('priority', 'urgent'),
                'latest_project_activity' => Project::select(DB::raw('
                    GREATEST(
                        COALESCE(MAX(tasks.updated_at), "1970-01-01"),
                        COALESCE(MAX(required_documents.updated_at), "1970-01-01"),
                        COALESCE(MAX(projects.updated_at), "1970-01-01")
                    )
                '))
                    ->join('project_steps', 'projects.id', '=', 'project_steps.project_id')
                    ->leftJoin('tasks', 'project_steps.id', '=', 'tasks.project_step_id')
                    ->leftJoin('required_documents', 'project_steps.id', '=', 'required_documents.project_step_id')
                    ->whereColumn('projects.client_id', 'clients.id')
                    ->limit(1)
            ])
            ->orderByDesc('has_urgent_projects')
            ->orderByRaw('COALESCE(latest_project_activity, updated_at) DESC');

        // Filter clients based on user role and associations
        if (!$user->hasRole('super-admin')) {
            $clientIds = $user->userClients()->pluck('client_id');
            $clientsQuery->whereIn('id', $clientIds);
        }

        // Get total count before limiting
        $totalClients = $clientsQuery->count();

        // Get only 5 clients
        $clients = $clientsQuery->take(5)->get();

        // Calculate progress for each project
        $clients->each(function ($client) {
            $client->projects->each(function ($project) {
                $totalItems = 0;
                $completedItems = 0;

                foreach ($project->steps as $step) {
                    // Count tasks
                    $tasks = $step->tasks;
                    if ($tasks->count() > 0) {
                        $totalItems += $tasks->count();
                        $completedItems += $tasks->where('status', 'completed')->count();
                    }

                    // Count documents
                    $documents = $step->requiredDocuments;
                    if ($documents->count() > 0) {
                        $totalItems += $documents->count();
                        $completedItems += $documents->whereIn('status', ['approved'])->count();
                    }
                }

                // Calculate project progress
                $project->progress = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;

                // Add additional status info
                $project->has_urgent_tasks = $project->priority === 'urgent';
                $project->has_new_documents = $project->steps
                    ->flatMap->requiredDocuments
                    ->where('status', 'uploaded')
                    ->isNotEmpty();
            });
        });

        return [
            'clients' => $clients,
            'hasMoreClients' => $totalClients > 5,
            'totalClients' => $totalClients,
            'dashboard_stats' => $this->getDashboardStats(),
        ];
    }
}