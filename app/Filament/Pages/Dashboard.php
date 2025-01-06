<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DocumentsOverview;
use App\Models\Client;
use App\Models\Progress;
use App\Models\Task;
use Filament\Pages\Page;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\DB;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.dashboard';

    public function getViewData(): array
    {
        // Get statistics
        $stats = [
            'total_projects' => \App\Models\Project::count(),
            'active_projects' => \App\Models\Project::where('status', 'in_progress')->count(),
            'completed_projects' => \App\Models\Project::where('status', 'completed')->count(),
            'pending_documents' => \App\Models\SubmittedDocument::where('status', 'pending_review')->count(),
        ];

        // Get clients with their projects and all related data
        $clients = Client::with([
            'projects' => function ($query) {
                $query->latest();
            },
            'projects.steps' => function ($query) {
                $query->orderBy('order');
            },
            'projects.steps.tasks',
            'projects.steps.requiredDocuments',
            'projects.steps.requiredDocuments.submittedDocuments'
        ])->get();

        return [
            'clients' => $clients,
            'stats' => $stats,  // Make sure this is included
        ];
    }
}
