<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DocumentsOverview;
use App\Models\Client;
use App\Models\SubmittedDocument;
use App\Models\Progress;
use App\Models\Project;
use App\Models\Task;
use Filament\Pages\Page;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\DB;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Storage;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static string $view = 'filament.pages.dashboard';

    public $previewingDocument = null;
    public $previewUrl = null;
    public $fileType = null;

    // Add this property
    public $activeStatus = 'all';

    // Add this method
    public function changeStatus($status)
    {
        $this->activeStatus = $status;
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
                    // Order projects by last update
                    $query->orderBy('updated_at', 'desc');
                    if ($currentStatus !== 'all') {
                        $query->where('status', $currentStatus);
                    }
                },
                'projects.steps.tasks',
                'projects.steps.requiredDocuments'
            ])
            // Order clients based on their latest project update
            ->addSelect([
                'latest_project_update' => Project::select('updated_at')
                    ->whereColumn('client_id', 'clients.id')
                    ->latest()
                    ->limit(1)
            ])
            ->orderBy('latest_project_update', 'desc');

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
                        $completedItems += $documents->where('status', 'approved')->count();
                    }
                }

                $project->progress = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
            });
        });

        return [
            'clients' => $clients,
            'hasMoreClients' => $totalClients > 5,
            'totalClients' => $totalClients,
        ];
    }
}