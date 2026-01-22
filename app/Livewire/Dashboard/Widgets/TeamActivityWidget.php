<?php

namespace App\Livewire\Dashboard\Widgets;

use App\Models\User;
use App\Models\UserActivity;
use App\Models\Project;
use App\Models\DailyTask;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;

#[Lazy]
class TeamActivityWidget extends Component
{
    /**
     * Get users based on role hierarchy
     * Director: sees all managers and staff
     * Manager: sees their staff
     * Staff: sees only themselves
     */
    #[Computed]
    public function visibleUsers()
    {
        $currentUser = auth()->user();
        
        // Director can see all managers and staff
        if ($currentUser->hasRole('director')) {
            return User::whereHas('roles', function($query) {
                $query->whereIn('name', ['manager', 'staff']);
            })
            ->with(['roles'])
            ->orderBy('name')
            ->get();
        }
        
        // Manager can see their staff
        if ($currentUser->hasRole('manager')) {
            // Get staff members assigned to manager's projects
            $staffIds = Project::where('pic_id', $currentUser->id)
                ->orWhereHas('userProject', function($q) use ($currentUser) {
                    $q->where('user_id', $currentUser->id)
                      ->where('role', 'manager');
                })
                ->with('userProject.user')
                ->get()
                ->pluck('userProject')
                ->flatten()
                ->pluck('user')
                ->unique('id')
                ->pluck('id');

            return User::whereIn('id', $staffIds)
                ->whereHas('roles', function($query) {
                    $query->where('name', 'staff');
                })
                ->with(['roles'])
                ->orderBy('name')
                ->get();
        }
        
        // Staff only sees themselves
        return collect([$currentUser]);
    }

    /**
     * Get activity statistics for visible users
     */
    #[Computed]
    public function activityStats()
    {
        $users = $this->visibleUsers;
        $today = today();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        $stats = [];
        
        foreach ($users as $user) {
            // Get tasks statistics
            $todayTasks = DailyTask::where(function($query) use ($user) {
                    $query->where('created_by', $user->id)
                        ->orWhereHas('assignedUsers', function($q) use ($user) {
                            $q->where('user_id', $user->id);
                        });
                })
                ->whereDate('task_date', $today)
                ->get();

            $weekTasks = DailyTask::where(function($query) use ($user) {
                    $query->where('created_by', $user->id)
                        ->orWhereHas('assignedUsers', function($q) use ($user) {
                            $q->where('user_id', $user->id);
                        });
                })
                ->whereBetween('task_date', [$thisWeek, now()])
                ->get();

            // Get projects count
            $activeProjects = Project::where(function($query) use ($user) {
                    $query->where('pic_id', $user->id)
                        ->orWhereHas('userProject', function($q) use ($user) {
                            $q->where('user_id', $user->id);
                        });
                })
                ->whereNotIn('status', ['completed', 'canceled'])
                ->count();

            // Get last activity
            $lastActivity = UserActivity::where('user_id', $user->id)
                ->latest()
                ->first();

            // Check if online (active in last 5 minutes)
            $isOnline = $lastActivity && 
                        $lastActivity->created_at->diffInMinutes(now()) < 5;

            $stats[] = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar_url' => $user->avatar_url,
                    'role' => $user->roles->first()?->name ?? 'staff',
                ],
                'today' => [
                    'total_tasks' => $todayTasks->count(),
                    'completed' => $todayTasks->where('status', 'completed')->count(),
                    'in_progress' => $todayTasks->where('status', 'in_progress')->count(),
                    'pending' => $todayTasks->whereIn('status', ['pending', 'draft'])->count(),
                ],
                'week' => [
                    'total_tasks' => $weekTasks->count(),
                    'completed' => $weekTasks->where('status', 'completed')->count(),
                    'completion_rate' => $weekTasks->count() > 0 
                        ? round(($weekTasks->where('status', 'completed')->count() / $weekTasks->count()) * 100) 
                        : 0,
                ],
                'projects' => [
                    'active' => $activeProjects,
                ],
                'activity' => [
                    'is_online' => $isOnline,
                    'last_seen' => $lastActivity?->created_at,
                    'last_action' => $lastActivity?->description ?? null,
                ],
            ];
        }

        return collect($stats);
    }

    /**
     * Get overall team statistics
     */
    #[Computed]
    public function teamStats()
    {
        $stats = $this->activityStats;
        
        return [
            'total_users' => $stats->count(),
            'online_users' => $stats->where('activity.is_online', true)->count(),
            'total_tasks_today' => $stats->sum('today.total_tasks'),
            'completed_today' => $stats->sum('today.completed'),
            'in_progress_today' => $stats->sum('today.in_progress'),
            'avg_completion_rate' => $stats->count() > 0 
                ? round($stats->avg('week.completion_rate')) 
                : 0,
        ];
    }

    /**
     * Placeholder for lazy loading
     */
    public function placeholder()
    {
        return <<<'HTML'
        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="animate-pulse">
                <div class="flex items-center gap-x-3 mb-4">
                    <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-32"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div class="h-16 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    <div class="h-16 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    <div class="h-16 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                </div>
                <div class="space-y-2">
                    <div class="h-16 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    <div class="h-16 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    <div class="h-16 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                </div>
            </div>
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.team-activity-widget', [
            'activityStats' => $this->activityStats,
            'teamStats' => $this->teamStats,
            'currentUserRole' => auth()->user()->roles->first()?->name ?? 'staff',
        ]);
    }
}