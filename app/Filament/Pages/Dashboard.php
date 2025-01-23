<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DocumentsOverview;
use App\Models\Client;
use App\Models\Progress;
use App\Models\Project;
use App\Models\Task;
use Filament\Pages\Page;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\DB;
use Filament\Actions\Action;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static string $view = 'filament.pages.dashboard';

    public function getViewData(): array
    {
        // User instance
        $user = auth()->user();

        // Initialize clients query
        $clientsQuery = Client::query()
            ->with([
                'projects' => function ($query) {
                    $query->latest();
                },
                'projects.steps.tasks' => function ($query) {
                    $query->withCount('comments');
                }
            ]);

        // Filter clients based on user role and associations
        if (!$user->hasRole('super-admin')) {
            // Get client IDs associated with the user
            $clientIds = $user->userClients()->pluck('client_id');
            $clientsQuery->whereIn('id', $clientIds);
        }

        // Get filtered clients
        $clients = $clientsQuery->get();

        // Calculate stats based on accessible projects
        $accessibleProjectIds = $clients->pluck('projects')->flatten()->pluck('id');

        $stats = [
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

        // Calculate progress for each project
        $clients->each(function ($client) {
            $client->projects->each(function ($project) {
                $totalSteps = $project->steps->count();
                if ($totalSteps > 0) {
                    $completedSteps = $project->steps->where('status', 'completed')->count();
                    $project->progress = round(($completedSteps / $totalSteps) * 100);
                } else {
                    $project->progress = 0;
                }
            });
        });

        return [
            'clients' => $clients,
            'stats' => $stats,
        ];
    }
}