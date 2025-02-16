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
                    // Get the latest activity timestamp for each project
                    $query->select('projects.*')
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
                'projects.steps.requiredDocuments'
            ])
            // Order clients based on their latest project activity
            ->addSelect([
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