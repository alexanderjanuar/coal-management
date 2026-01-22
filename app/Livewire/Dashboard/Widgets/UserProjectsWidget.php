<?php

namespace App\Livewire\Dashboard\Widgets;

use App\Models\Project;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;

#[Lazy] // Lazy load this widget
class UserProjectsWidget extends Component
{
    public int $limit = 10;
    
    /**
     * Get user's projects with PIC status and deadline information
     * Uses #[Computed] for automatic caching within the request lifecycle
     */
    #[Computed]
    public function userProjects()
    {
        $user = auth()->user();
        
        return Project::with(['client:id,name']) // Only select needed columns
            ->select([
                'id', 
                'name', 
                'client_id', 
                'pic_id', 
                'status', 
                'priority', 
                'due_date'
            ])
            ->whereNotIn('status', ['completed', 'canceled'])
            ->where(function($query) use ($user) {
                $query->where('pic_id', $user->id)
                      ->orWhereHas('userProject', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->orderByRaw("
                CASE 
                    WHEN due_date < NOW() THEN 0
                    WHEN due_date <= DATE_ADD(NOW(), INTERVAL 7 DAY) THEN 1
                    WHEN priority = 'urgent' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('due_date')
            ->limit($this->limit)
            ->get()
            ->map(function($project) use ($user) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'client' => ['name' => $project->client?->name],
                    'status' => $project->status,
                    'priority' => $project->priority,
                    'due_date' => $project->due_date,
                    'is_pic' => $project->pic_id === $user->id,
                ];
            });
    }

    /**
     * Calculate statistics
     */
    #[Computed]
    public function stats()
    {
        $projects = $this->userProjects;
        
        return [
            'total' => $projects->count(),
            'pic_count' => $projects->where('is_pic', true)->count(),
            'member_count' => $projects->where('is_pic', false)->count(),
            'overdue_count' => $projects->filter(function($project) {
                return isset($project['due_date']) && 
                       Carbon::parse($project['due_date'])->isPast();
            })->count(),
            'due_soon_count' => $projects->filter(function($project) {
                if (!isset($project['due_date'])) return false;
                $dueDate = Carbon::parse($project['due_date']);
                return $dueDate->isFuture() && $dueDate->diffInDays(now()) <= 7;
            })->count(),
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
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-32"></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="h-20 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    <div class="h-20 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
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
        return view('livewire.dashboard.widgets.user-projects-widget', [
            'projects' => $this->userProjects,
            'stats' => $this->stats,
        ]);
    }
}