<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Project;

class ProjectDetails extends Page
{
    public Project $project;

    protected static string $view = 'filament.pages.project-details';
    
    protected static bool $shouldRegisterNavigation = false;

    public function mount(Project $project): void 
    {   
        $this->project = $project->load([
            'client',
            'steps' => fn($query) => $query->orderBy('order'),
            'steps.tasks',
            'steps.requiredDocuments',
            'steps.requiredDocuments.submittedDocuments'
        ]);

    }

    protected function getViewData(): array
    {
        return [
            'project' => $this->project,
            'client' => $this->project->client,
            'steps' => $this->project->steps,
        ];
    }

}