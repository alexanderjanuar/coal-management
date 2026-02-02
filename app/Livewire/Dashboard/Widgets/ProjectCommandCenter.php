<?php

namespace App\Livewire\Dashboard\Widgets;

use App\Models\Project;
use App\Models\DailyTask;
use App\Models\User;
use App\Models\UserActivity;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;

#[Lazy]
class ProjectCommandCenter extends Component
{
    public string $activeTab = 'overview';
    public int $year;

    public function mount()
    {
        $this->year = now()->year;
    }

    public function updatedYear()
    {
        unset(
            $this->pipeline,
            $this->activeProjects,
            $this->upcomingDeadlines,
            $this->teamWorkload,
            $this->availableYears,
        );
    }

    #[Computed]
    public function availableYears()
    {
        return Project::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();
    }

    /**
     * Build the role-scoped base project query
     */
    private function baseProjectQuery()
    {
        $user = auth()->user();
        $query = Project::query()->whereYear('created_at', $this->year);

        if (!$user->hasRole('super-admin') && !$user->hasRole('director')) {
            $clientIds = $user->userClients()->pluck('client_id')->toArray();

            if (!empty($clientIds)) {
                $query->whereIn('client_id', $clientIds)
                    ->where(function ($sub) use ($user) {
                        $sub->where('pic_id', $user->id)
                            ->orWhereHas('userProject', function ($q) use ($user) {
                                $q->where('user_id', $user->id);
                            });
                    });
            }
        }

        return $query;
    }

    /**
     * Project counts grouped by status for the pipeline view
     */
    #[Computed]
    public function pipeline()
    {
        $stages = [
            'draft'       => ['label' => 'Draft', 'count' => 0],
            'analysis'    => ['label' => 'Analisis', 'count' => 0],
            'in_progress' => ['label' => 'Berjalan', 'count' => 0],
            'review'      => ['label' => 'Review', 'count' => 0],
            'completed'   => ['label' => 'Selesai', 'count' => 0],
        ];

        $counts = $this->baseProjectQuery()
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        foreach ($counts as $status => $total) {
            if (isset($stages[$status])) {
                $stages[$status]['count'] = $total;
            }
        }

        $grandTotal = array_sum(array_column($stages, 'count'));

        return [
            'stages' => $stages,
            'total' => $grandTotal,
        ];
    }

    /**
     * Active projects with progress data for the project cards
     */
    #[Computed]
    public function activeProjects()
    {
        $projects = $this->baseProjectQuery()
            ->with([
                'client:id,name',
                'pic:id,name,avatar_url',
                'steps.tasks',
                'steps.requiredDocuments',
                'userProjects.user:id,name,avatar_url',
            ])
            ->whereNotIn('status', ['completed', 'canceled'])
            ->orderByRaw("CASE WHEN priority = 'urgent' THEN 0 WHEN due_date < NOW() THEN 1 ELSE 2 END")
            ->orderBy('due_date')
            ->limit(6)
            ->get();

        return $projects->map(function ($project) {
            $totalItems = 0;
            $completedItems = 0;

            foreach ($project->steps as $step) {
                $tc = $step->tasks->count();
                $cc = $step->tasks->where('status', 'completed')->count();
                $totalItems += $tc;
                $completedItems += $cc;

                $dc = $step->requiredDocuments->count();
                $ac = $step->requiredDocuments->where('status', 'approved')->count();
                $totalItems += $dc;
                $completedItems += $ac;
            }

            $progress = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
            $dueDate = $project->due_date;
            $daysLeft = $dueDate ? now()->diffInDays($dueDate, false) : null;

            $members = $project->userProjects
                ->map(fn ($up) => [
                    'name' => $up->user?->name,
                    'avatar_url' => $up->user?->avatar_url,
                    'initials' => $up->user ? strtoupper(substr($up->user->name, 0, 1)) : '?',
                ])
                ->filter(fn ($m) => $m['name'] !== null)
                ->unique('name')
                ->take(4)
                ->values();

            return [
                'id' => $project->id,
                'name' => $project->name,
                'client' => $project->client?->name ?? '-',
                'pic' => $project->pic?->name,
                'pic_avatar' => $project->pic?->avatar_url,
                'status' => $project->status,
                'priority' => $project->priority,
                'progress' => $progress,
                'total_items' => $totalItems,
                'completed_items' => $completedItems,
                'due_date' => $dueDate?->format('d M Y'),
                'days_left' => $daysLeft,
                'is_overdue' => $daysLeft !== null && $daysLeft < 0,
                'members' => $members,
                'extra_members' => max(0, $project->userProjects->count() - 4),
            ];
        });
    }

    /**
     * Team workload: per-member task counts and active projects
     */
    #[Computed]
    public function teamWorkload()
    {
        $currentUser = auth()->user();
        $today = today();

        if ($currentUser->hasRole('director') || $currentUser->hasRole('super-admin')) {
            $users = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['manager', 'staff']))
                ->with('roles')
                ->orderBy('name')
                ->limit(8)
                ->get();
        } elseif ($currentUser->hasRole('manager')) {
            $users = User::whereHas('roles', fn ($q) => $q->where('name', 'staff'))
                ->with('roles')
                ->orderBy('name')
                ->limit(8)
                ->get();
        } else {
            $users = collect([$currentUser->load('roles')]);
        }

        return $users->map(function ($user) use ($today) {
            $todayTasksQ = DailyTask::where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhereHas('assignedUsers', fn ($s) => $s->where('user_id', $user->id));
                })
                ->whereDate('task_date', $today);

            $total = $todayTasksQ->count();
            $completed = (clone $todayTasksQ)->where('status', 'completed')->count();

            $activeProjects = Project::whereYear('created_at', $this->year)
                ->where(function ($q) use ($user) {
                    $q->where('pic_id', $user->id)
                        ->orWhereHas('userProject', fn ($s) => $s->where('user_id', $user->id));
                })
                ->whereNotIn('status', ['completed', 'canceled'])
                ->count();

            $lastActivity = UserActivity::where('user_id', $user->id)->latest()->first();
            $isOnline = $lastActivity && $lastActivity->created_at->diffInMinutes(now()) < 5;

            return [
                'name' => $user->name,
                'avatar_url' => $user->avatar_url,
                'initials' => strtoupper(substr($user->name, 0, 1)),
                'role' => $user->roles->first()?->name ?? 'staff',
                'tasks_total' => $total,
                'tasks_completed' => $completed,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
                'active_projects' => $activeProjects,
                'is_online' => $isOnline,
                'last_seen' => $lastActivity?->created_at,
            ];
        });
    }

    /**
     * Upcoming deadlines across all accessible projects (next 14 days)
     */
    #[Computed]
    public function upcomingDeadlines()
    {
        return $this->baseProjectQuery()
            ->with('client:id,name')
            ->select(['id', 'name', 'client_id', 'due_date', 'status', 'priority'])
            ->whereNotIn('status', ['completed', 'canceled'])
            ->whereNotNull('due_date')
            ->where(function ($q) {
                $q->where('due_date', '<', now()) // overdue
                  ->orWhere('due_date', '<=', now()->addDays(14)); // upcoming 14 days
            })
            ->orderBy('due_date')
            ->limit(6)
            ->get()
            ->map(function ($p) {
                $daysLeft = now()->diffInDays($p->due_date, false);

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'client' => $p->client?->name,
                    'due_date' => $p->due_date->format('d M'),
                    'due_day' => $p->due_date->translatedFormat('D'),
                    'days_left' => $daysLeft,
                    'is_overdue' => $daysLeft < 0,
                    'is_urgent' => $p->priority === 'urgent',
                    'status' => $p->status,
                ];
            });
    }

    public function switchTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-8">
            <div class="animate-pulse space-y-8">
                <div class="flex items-center justify-between">
                    <div class="space-y-2">
                        <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-48"></div>
                        <div class="h-3 bg-gray-100 dark:bg-gray-800 rounded w-32"></div>
                    </div>
                    <div class="flex gap-2">
                        <div class="h-8 bg-gray-100 dark:bg-gray-800 rounded-lg w-20"></div>
                        <div class="h-8 bg-gray-100 dark:bg-gray-800 rounded-lg w-20"></div>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="flex-1 h-20 bg-gray-100 dark:bg-gray-800 rounded-xl"></div>
                    <div class="flex-1 h-20 bg-gray-100 dark:bg-gray-800 rounded-xl"></div>
                    <div class="flex-1 h-20 bg-gray-100 dark:bg-gray-800 rounded-xl"></div>
                    <div class="flex-1 h-20 bg-gray-100 dark:bg-gray-800 rounded-xl"></div>
                    <div class="flex-1 h-20 bg-gray-100 dark:bg-gray-800 rounded-xl"></div>
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <div class="h-24 bg-gray-100 dark:bg-gray-800 rounded-xl"></div>
                        <div class="h-24 bg-gray-100 dark:bg-gray-800 rounded-xl"></div>
                    </div>
                    <div class="space-y-3">
                        <div class="h-16 bg-gray-100 dark:bg-gray-800 rounded-xl"></div>
                        <div class="h-16 bg-gray-100 dark:bg-gray-800 rounded-xl"></div>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.project-command-center', [
            'pipeline' => $this->pipeline,
            'projects' => $this->activeProjects,
            'workload' => $this->teamWorkload,
            'deadlines' => $this->upcomingDeadlines,
            'availableYears' => $this->availableYears,
        ]);
    }
}
