<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Project;
use App\Models\Comment;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Livewire\Attributes\Computed;



class ProjectDetails extends Page implements HasForms
{
    use InteractsWithForms;

    public Project $record;
    public ?array $commentData = [];
    public ?int $selectedTaskId = null;

    protected static string $view = 'filament.pages.project-details';
    protected static bool $shouldRegisterNavigation = false;


    public ?string $comment = '';
    
    public function mount(int|string $record): void 
    {
        parent::mount($record);
        $this->form->fill();
    }

    protected function getViewData(): array
    {
        return [
            'record' => $this->record,
            'client' => $this->record->client,
            'steps' => $this->record->steps,
            'progressPercentage' => $this->calculateProgress(),
        ];
    }

    private function calculateProgress(): int
    {
        $totalSteps = $this->record->steps->count();
        $completedSteps = $this->record->steps->where('status', 'completed')->count();

        return $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;
    }
}