<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Project;

class ProjectDetails extends Page
{
    public ?Project $record;

    protected static string $view = 'filament.pages.project-details';
    
    protected static bool $shouldRegisterNavigation = false;

    public function mount(Project $record): void 
    {
        $this->record = $record->load([
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
            'project' => $this->record,
            'client' => $this->record->client,
            'steps' => $this->record->steps,
        ];
    }

}