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
        // Get clients with their progress and tasks
        $clients = Client::with(['progress' => function ($query) {
            $query->with('tasks')->orderBy('created_at', 'desc');
        }])->get();

        // Get process steps
        $processSteps = [
            [
                'number' => '1',
                'title' => 'Client Registration',
                'subtitle' => 'Create client profile'
            ],
            [
                'number' => '2',
                'title' => 'Progress Creation',
                'subtitle' => 'Initialize shipping process'
            ],
            [
                'number' => '3',
                'title' => 'Task Assignment',
                'subtitle' => 'Define shipping tasks'
            ],
            [
                'number' => '4',
                'title' => 'Document Upload',
                'subtitle' => 'Upload required files'
            ],
        ];

        return [
            'clients' => $clients,
            'processSteps' => $processSteps,
        ];
    }
}
