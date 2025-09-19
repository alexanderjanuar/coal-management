<?php

namespace App\Livewire\Dashboard\Components;

use App\Models\Client;
use App\Models\SubmittedDocument;
use App\Models\Project;
use App\Models\DailyTask;
use App\Models\RequiredDocument;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GreetingCard extends Component
{
    public $showDocumentDetails = false;
    public $showTaskDetails = false;
    public $showReviewDetails = false;

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
        $completedProjectCount = $baseProjectQuery()
            ->where('status', 'completed')
            ->count();

        // Daily tasks counts
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

        // Document statistics for current user
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

        // Documents that need review (for all users that can review)
        $documentsNeedReview = 0;
        if (!$user->hasRole(['staff', 'client'])) {
            $needReviewQuery = SubmittedDocument::whereIn('status', ['uploaded', 'pending_review']);
            
            if (!$isSuperAdmin && !empty($clientIds)) {
                $needReviewQuery->whereHas('requiredDocument.projectStep.project', function($q) use ($clientIds) {
                    $q->whereIn('client_id', $clientIds);
                });
            }
            
            $documentsNeedReview = $needReviewQuery->count();
        }

        return [
            'completed_projects' => $completedProjectCount,
            'today_tasks' => $todayTasksCount,
            'completed_tasks_today' => $completedTasksCount,
            'incomplete_tasks_today' => $incompleteTasksCount,
            'submitted_documents' => $totalSubmittedDocuments,
            'approved_documents' => $approvedDocuments,
            'pending_documents' => $pendingDocuments,
            'rejected_documents' => $rejectedDocuments,
            'documents_need_review' => $documentsNeedReview,
            'client_ids' => $clientIds,
            'is_super_admin' => $isSuperAdmin,
        ];
    }

    /**
     * Get projects that are near completion (70%+ progress)
     */
    public function getNearCompletionProjects(): array
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super-admin');
        $clientIds = $isSuperAdmin ? null : $user->userClients()->pluck('client_id')->toArray();
        
        $nearCompletionProjects = collect();
        
        // Query projects with their steps, tasks, and documents - ACTIVE CLIENTS ONLY
        $projectsQuery = Project::with(['steps.tasks', 'steps.requiredDocuments', 'client'])
            ->whereNotIn('status', ['completed', 'canceled'])
            ->whereHas('client', function($query) {
                $query->where('status', 'Active'); // Only active clients
            });
        
        // Apply role-based filtering
        if (!$isSuperAdmin && !empty($clientIds)) {
            $projectsQuery->whereIn('client_id', $clientIds)
                ->where(function($subQuery) use ($user) {
                    $subQuery->where('pic_id', $user->id)
                            ->orWhereHas('userProject', function($q) use ($user) {
                                $q->where('user_id', $user->id);
                            });
                });
        }
        
        $projects = $projectsQuery->get();
        
        // Calculate progress for each project
        foreach ($projects as $project) {
            $totalItems = 0;
            $completedItems = 0;
            
            foreach ($project->steps as $step) {
                // Count tasks
                $tasks = $step->tasks;
                if ($tasks->count() > 0) {
                    $totalItems += $tasks->count();
                    $completedItems += $tasks->where('status', 'completed')->count();
                }
                
                // Count required documents
                $documents = $step->requiredDocuments;
                if ($documents->count() > 0) {
                    $totalItems += $documents->count();
                    $completedItems += $documents->where('status', 'approved')->count();
                }
            }
            
            // Calculate progress percentage
            $progress = $totalItems > 0 ? ($completedItems / $totalItems) * 100 : 0;
            
            // Near completion = 70%+ progress but not 100%
            if ($progress >= 70 && $progress < 100) {
                $nearCompletionProjects->push([
                    'project' => [
                        'id' => $project->id,
                        'name' => $project->name,
                        'status' => $project->status,
                        'priority' => $project->priority,
                        'client' => [
                            'id' => $project->client->id ?? null,
                            'name' => $project->client->name ?? 'No Client'
                        ]
                    ],
                    'progress' => round($progress),
                    'completed_items' => $completedItems,
                    'total_items' => $totalItems,
                    'progress_decimal' => round($progress, 1)
                ]);
            }
        }
        
        // Sort by progress descending (highest completion first)
        return $nearCompletionProjects
            ->sortByDesc('progress')
            ->values()
            ->toArray();
    }

    

    /**
     * Get sample of user's submitted documents
     */
    public function getUserDocuments(int $limit = 5): array
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super-admin');
        $clientIds = $isSuperAdmin ? null : $user->userClients()
            ->whereHas('client', function($query) {
                $query->where('status', 'Active');
            })
            ->pluck('client_id')
            ->toArray();

        $query = SubmittedDocument::with(['requiredDocument.projectStep.project.client'])
            ->where('user_id', $user->id)
            ->whereHas('requiredDocument.projectStep.project.client', function($clientQuery) {
                $clientQuery->where('status', 'Active'); // Only active clients
            });

        if (!$isSuperAdmin && !empty($clientIds)) {
            $query->whereHas('requiredDocument.projectStep.project', function($q) use ($clientIds) {
                $q->whereIn('client_id', $clientIds);
            });
        }

        return $query->orderByRaw("CASE 
                WHEN status = 'pending_review' THEN 1 
                WHEN status = 'uploaded' THEN 2 
                WHEN status = 'rejected' THEN 3 
                ELSE 4 END")
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get sample of today's tasks
     */
    public function getTodayTasks(int $limit = 5): array
    {
        if (!class_exists(DailyTask::class)) {
            return [];
        }

        $user = auth()->user();
        $today = today();

        return DailyTask::with(['project.client'])
            ->where(function($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhereHas('assignedUsers', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->where(function($projectQuery) {
                // Only include tasks for projects with active clients or tasks without project
                $projectQuery->whereHas('project.client', function($q) {
                    $q->where('status', 'Active');
                })->orWhereNull('project_id');
            })
            ->where(function($dateQuery) use ($today) {
                $dateQuery->where(function($q) use ($today) {
                    $q->where('start_task_date', '<=', $today)
                    ->where('task_date', '>=', $today);
                })->orWhere(function($q) use ($today) {
                    $q->where('task_date', $today)
                    ->whereNull('start_task_date');
                });
            })
            ->orderByRaw("CASE 
                WHEN priority = 'urgent' THEN 1 
                WHEN priority = 'high' THEN 2 
                WHEN priority = 'normal' THEN 3 
                ELSE 4 END")
            ->orderByRaw("CASE 
                WHEN status = 'in_progress' THEN 1 
                WHEN status = 'pending' THEN 2 
                ELSE 3 END")
            ->limit($limit)
            ->get()
            ->toArray();
}

    /**
     * Get documents that need review
     */
    public function getDocumentsNeedReview(int $limit = 5): array
    {
        $user = auth()->user();
        
        if ($user->hasRole(['staff', 'client'])) {
            return [];
        }

        $isSuperAdmin = $user->hasRole('super-admin');
        $clientIds = $isSuperAdmin ? null : $user->userClients()
            ->whereHas('client', function($query) {
                $query->where('status', 'Active');
            })
            ->pluck('client_id')
            ->toArray();

        $query = SubmittedDocument::with(['requiredDocument.projectStep.project.client', 'user'])
            ->whereIn('status', ['uploaded', 'pending_review'])
            ->whereHas('requiredDocument.projectStep.project.client', function($clientQuery) {
                $clientQuery->where('status', 'Active'); // Only active clients
            });

        if (!$isSuperAdmin && !empty($clientIds)) {
            $query->whereHas('requiredDocument.projectStep.project', function($q) use ($clientIds) {
                $q->whereIn('client_id', $clientIds);
            });
        }

        return $query->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    // Previous methods remain the same...
    public function getGreeting(): string
    {
        $hour = now()->format('H');
        return match(true) {
            $hour < 12 => 'Selamat Pagi',
            $hour < 15 => 'Selamat Siang',
            $hour < 18 => 'Selamat Sore',
            default => 'Selamat Malam'
        };
    }

    public function getFirstName(): string
    {
        return explode(' ', auth()->user()->name)[0];
    }

    public function getUserAvatar(): ?string
    {
        $user = auth()->user();
        if ($user->avatar_url || $user->avatar_path) {
            return $user->avatar;
        }
        return null;
    }

    public function getUserInitial(): string
    {
        return strtoupper(substr(auth()->user()->name, 0, 1));
    }

    public function getCurrentDate(): string
    {
        return now()->locale('id')->translatedFormat('l, d F Y');
    }

    public function getCurrentTime(): string
    {
        return now()->format('H:i') . ' WIB';
    }

    public function getDocumentMessage(array $stats): string
    {
        return match(true) {
            $stats['submitted_documents'] == 0 => "Belum ada dokumen yang disubmit",
            $stats['approved_documents'] == $stats['submitted_documents'] => "Semua dokumen sudah disetujui - excellent!",
            $stats['rejected_documents'] > 0 => $stats['approved_documents'] . " disetujui, " . $stats['rejected_documents'] . " ditolak, " . $stats['pending_documents'] . " pending",
            default => $stats['approved_documents'] . " disetujui dari " . $stats['submitted_documents'] . " dokumen yang disubmit"
        };
    }

    public function getTaskMessage(array $stats): string
    {
        return match(true) {
            $stats['today_tasks'] == 0 => "Tidak ada task aktif dalam periode ini",
            $stats['completed_tasks_today'] == $stats['today_tasks'] => "Semua task dalam periode ini sudah selesai - kerja yang bagus!",
            $stats['incomplete_tasks_today'] == 0 => "Tidak ada task yang belum selesai",
            default => $stats['completed_tasks_today'] . " selesai, " . $stats['incomplete_tasks_today'] . " belum selesai"
        };
    }

    public function getReviewMessage(array $stats): string
    {
        $user = auth()->user();
        
        if ($user->hasRole(['staff', 'client'])) {
            return "Anda tidak memiliki akses untuk mereview dokumen";
        }

        return match(true) {
            $stats['documents_need_review'] == 0 => "Tidak ada dokumen yang menunggu review",
            $stats['documents_need_review'] == 1 => "1 dokumen menunggu review Anda",
            default => $stats['documents_need_review'] . " dokumen menunggu review Anda"
        };
    }

    public function calculateProgress(int $completed, int $total): int
    {
        return $total > 0 ? (int) (($completed / $total) * 100) : 0;
    }

    public function render()
    {
        $dashboardStats = $this->getDashboardStats();
        
        return view('livewire.dashboard.component.greeting-card', [
            'dashboardStats' => $dashboardStats,
            'greeting' => $this->getGreeting(),
            'firstName' => $this->getFirstName(),
            'userAvatar' => $this->getUserAvatar(),
            'userInitial' => $this->getUserInitial(),
            'currentDate' => $this->getCurrentDate(),
            'currentTime' => $this->getCurrentTime(),
            'documentMessage' => $this->getDocumentMessage($dashboardStats),
            'taskMessage' => $this->getTaskMessage($dashboardStats),
            'reviewMessage' => $this->getReviewMessage($dashboardStats),
            'userDocuments' => $this->getUserDocuments(),
            'todayTasks' => $this->getTodayTasks(),
            'documentsNeedReview' => $this->getDocumentsNeedReview(),
        ]);
    }
}