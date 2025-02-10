<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Project;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ProjectStats extends Component
{
    private function getAccessibleProjectIds()
    {
        $user = auth()->user();

        return Cache::remember("user_{$user->id}_project_ids", 300, function () use ($user) {
            if ($user->hasRole('super-admin')) {
                return Project::pluck('id');
            }

            return Project::whereIn('client_id', function ($query) use ($user) {
                $query->select('client_id')
                    ->from('user_clients')
                    ->where('user_id', $user->id);
            })->pluck('id');
        });
    }

    private function getStats(): array
    {
        $user = auth()->user();
        $now = now();
        $lastMonth = $now->copy()->subMonth();
        $accessibleProjectIds = $this->getAccessibleProjectIds();

        // Get all stats in single queries with conditional clauses
        $currentProjectStats = Cache::remember(
            "user_{$user->id}_current_project_stats",
            300,
            function () use ($accessibleProjectIds) {
                return DB::table('projects')
                    ->when(!empty($accessibleProjectIds), function ($query) use ($accessibleProjectIds) {
                        $query->whereIn('id', $accessibleProjectIds);
                    })
                    ->selectRaw('
                        COUNT(*) as total_projects,
                        SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as active_projects,
                        SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_projects
                    ')
                    ->first();
            }
        );

        $lastMonthProjectStats = Cache::remember(
            "user_{$user->id}_last_month_project_stats",
            300,
            function () use ($accessibleProjectIds, $lastMonth) {
                return DB::table('projects')
                    ->when(!empty($accessibleProjectIds), function ($query) use ($accessibleProjectIds) {
                        $query->whereIn('id', $accessibleProjectIds);
                    })
                    ->whereMonth('created_at', $lastMonth->month)
                    ->whereYear('created_at', $lastMonth->year)
                    ->selectRaw('
                        COUNT(*) as total_projects,
                        SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as active_projects,
                        SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_projects
                    ')
                    ->first();
            }
        );

        $pendingDocuments = Cache::remember(
            "user_{$user->id}_pending_documents",
            300,
            function () use ($accessibleProjectIds) {
                return DB::table('required_documents')
                    ->join('project_steps', 'required_documents.project_step_id', '=', 'project_steps.id')
                    ->when(!empty($accessibleProjectIds), function ($query) use ($accessibleProjectIds) {
                        $query->whereIn('project_steps.project_id', $accessibleProjectIds);
                    })
                    ->where('required_documents.status', 'pending_review')
                    ->count();
            }
        );

        $lastMonthPendingDocuments = Cache::remember(
            "user_{$user->id}_last_month_pending_documents",
            300,
            function () use ($accessibleProjectIds, $lastMonth) {
                return DB::table('required_documents')
                    ->join('project_steps', 'required_documents.project_step_id', '=', 'project_steps.id')
                    ->when(!empty($accessibleProjectIds), function ($query) use ($accessibleProjectIds) {
                        $query->whereIn('project_steps.project_id', $accessibleProjectIds);
                    })
                    ->where('required_documents.status', 'pending_review')
                    ->whereMonth('required_documents.created_at', $lastMonth->month)
                    ->whereYear('required_documents.created_at', $lastMonth->year)
                    ->count();
            }
        );

        // Prepare stats array
        $stats = [
            'total_projects' => $this->calculateStats(
                $currentProjectStats->total_projects ?? 0,
                $lastMonthProjectStats->total_projects ?? 0
            ),
            'active_projects' => $this->calculateStats(
                $currentProjectStats->active_projects ?? 0,
                $lastMonthProjectStats->active_projects ?? 0
            ),
            'completed_projects' => $this->calculateStats(
                $currentProjectStats->completed_projects ?? 0,
                $lastMonthProjectStats->completed_projects ?? 0
            ),
            'pending_documents' => $this->calculateStats(
                $pendingDocuments,
                $lastMonthPendingDocuments
            ),
        ];

        return $stats;
    }

    private function calculateStats($currentValue, $lastMonthValue): array
    {
        $change = $lastMonthValue > 0
            ? (($currentValue - $lastMonthValue) / $lastMonthValue) * 100
            : 0;

        return [
            'current' => $currentValue,
            'previous' => $lastMonthValue,
            'change' => round($change, 1),
            'trend' => $change >= 0 ? 'up' : 'down',
            'growth_rate' => max(0, $change),
            'decline_rate' => abs(min(0, $change))
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.project-stats', [
            'stats' => $this->getStats()
        ]);
    }
}