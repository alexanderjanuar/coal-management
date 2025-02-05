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
        // User instance
        $user = auth()->user();

        // Initialize clients query
        $clientsQuery = Client::query()
            ->with([
                'projects' => function ($query) {
                    $query->latest();
                },
                'projects.steps.tasks',
                'projects.steps.requiredDocuments' // Add this to eager load documents
            ]);

        // Filter clients based on user role and associations
        if (!$user->hasRole('super-admin')) {
            $clientIds = $user->userClients()->pluck('client_id');
            $clientsQuery->whereIn('id', $clientIds);
        }

        // Get filtered clients
        $clients = $clientsQuery->get();

        // Calculate progress for each project including tasks and documents
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

                // Calculate progress percentage
                $project->progress = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
            });
        });

        return [
            'clients' => $clients,
        ];
    }
}