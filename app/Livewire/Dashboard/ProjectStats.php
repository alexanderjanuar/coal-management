<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Project;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

class ProjectStats extends Component
{

    private function getAccessibleProjectIds()
    {
        $user = auth()->user();
        $clientsQuery = Client::query()
            ->with([
                'projects' => function ($query) {
                    $query->latest();
                }
            ]);

        if (!$user->hasRole('super-admin')) {
            $clientIds = $user->userClients()->pluck('client_id');
            $clientsQuery->whereIn('id', $clientIds);
        }

        $clients = $clientsQuery->get();
        return $clients->pluck('projects')->flatten()->pluck('id');
    }

    private function getStats(): array
    {
        $user = auth()->user();
        $now = now();
        $lastMonth = $now->copy()->subMonth();

        $clientsQuery = Client::query()
            ->with([
                'projects' => function ($query) {
                    $query->latest();
                },
                'projects.steps.tasks',
                'projects.steps.requiredDocuments'
            ]);

        if (!$user->hasRole('super-admin')) {
            $clientIds = $user->userClients()->pluck('client_id');
            $clientsQuery->whereIn('id', $clientIds);
        }

        $clients = $clientsQuery->get();
        $accessibleProjectIds = $clients->pluck('projects')->flatten()->pluck('id');

        // Current month stats
        $currentStats = [
            'total_projects' => Project::when(!$user->hasRole('super-admin'), function ($query) use ($accessibleProjectIds) {
                $query->whereIn('id', $accessibleProjectIds);
            })->count(),

            'active_projects' => Project::when(!$user->hasRole('super-admin'), function ($query) use ($accessibleProjectIds) {
                $query->whereIn('id', $accessibleProjectIds);
            })->where('status', 'in_progress')->count(),

            'completed_projects' => Project::when(!$user->hasRole('super-admin'), function ($query) use ($accessibleProjectIds) {
                $query->whereIn('id', $accessibleProjectIds);
            })->where('status', 'completed')->count(),

            'pending_documents' => DB::table('required_documents')
                ->when(!$user->hasRole('super-admin'), function ($query) use ($accessibleProjectIds) {
                    $query->whereIn('project_step_id', function ($subQuery) use ($accessibleProjectIds) {
                        $subQuery->select('id')
                            ->from('project_steps')
                            ->whereIn('project_id', $accessibleProjectIds);
                    });
                })
                ->where('status', 'pending_review')
                ->count(),
        ];

        // Last month stats
        $lastMonthStats = [
            'total_projects' => Project::when(!$user->hasRole('super-admin'), function ($query) use ($accessibleProjectIds) {
                $query->whereIn('id', $accessibleProjectIds);
            })->whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->count(),

            'active_projects' => Project::when(!$user->hasRole('super-admin'), function ($query) use ($accessibleProjectIds) {
                $query->whereIn('id', $accessibleProjectIds);
            })->where('status', 'in_progress')
                ->whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->count(),

            'completed_projects' => Project::when(!$user->hasRole('super-admin'), function ($query) use ($accessibleProjectIds) {
                $query->whereIn('id', $accessibleProjectIds);
            })->where('status', 'completed')
                ->whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->count(),

            'pending_documents' => DB::table('required_documents')
                ->when(!$user->hasRole('super-admin'), function ($query) use ($accessibleProjectIds) {
                    $query->whereIn('project_step_id', function ($subQuery) use ($accessibleProjectIds) {
                        $subQuery->select('id')
                            ->from('project_steps')
                            ->whereIn('project_id', $accessibleProjectIds);
                    });
                })
                ->where('status', 'pending_review')
                ->whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->count(),
        ];

        // Calculate growth rates and changes
        $stats = [];
        foreach ($currentStats as $key => $currentValue) {
            $lastMonthValue = $lastMonthStats[$key];
            $change = $lastMonthValue > 0 ?
                (($currentValue - $lastMonthValue) / $lastMonthValue) * 100 :
                0;

            $stats[$key] = [
                'current' => $currentValue,
                'previous' => $lastMonthValue,
                'change' => round($change, 1),
                'trend' => $change >= 0 ? 'up' : 'down',
                'growth_rate' => max(0, $change),
                'decline_rate' => abs(min(0, $change))
            ];
        }

        return $stats;
    }

    public function render()
    {
        return view('livewire.dashboard.project-stats', [
            'stats' => $this->getStats()
        ]);
    }
}

