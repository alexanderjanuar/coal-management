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
        $steps = $this->record->steps;

        if ($steps->isEmpty()) {
            return 0;
        }

        $totalProgress = 0;

        foreach ($steps as $step) {
            // Add the step's progress percentage to total
            $totalProgress += $this->calculateStepProgress($step);
        }

        // Calculate average progress across all steps
        return round($totalProgress / $steps->count());
    }

    private function calculateStepProgress($step): float
    {
        $totalWeight = 0;
        $completedWeight = 0;

        // Calculate tasks progress
        $tasks = $step->tasks;
        if ($tasks->count() > 0) {
            $totalWeight += 1; // Tasks represent 50% of step weight
            $taskProgress = $tasks->where('status', 'completed')->count() / $tasks->count();
            $completedWeight += $taskProgress;
        }

        // Calculate documents progress
        $documents = $step->requiredDocuments;
        if ($documents->count() > 0) {
            $totalWeight += 1; // Documents represent 50% of step weight
            $docProgress = $documents->where('status', 'approved')->count() / $documents->count();
            $completedWeight += $docProgress;
        }

        // If no items, step progress is 0
        if ($totalWeight === 0) {
            return 0;
        }

        // Calculate percentage (average of tasks and documents progress)
        return ($completedWeight / $totalWeight) * 100;
    }
}