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
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

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

    /**
     * Check if the project is of type "yearly"
     */
    public function isYearlyProject(): bool
    {
        return $this->record->type === 'yearly';
    }

    /**
     * Convert project to Nihil status
     */
    public function convertToNihil(): void
    {
        try {
            // Start a database transaction
            DB::beginTransaction();
            
            // 1. Delete steps 1-3
            $stepsToDelete = $this->record->steps()
                ->whereIn('order', [1, 2, 3])
                ->get();
                
            foreach ($stepsToDelete as $step) {
                // Delete related tasks
                $step->tasks()->delete();
                
                // Delete related documents
                $requiredDocs = $step->requiredDocuments;
                foreach ($requiredDocs as $doc) {
                    // Delete submitted documents for each required document
                    $doc->submittedDocuments()->delete();
                    $doc->delete();
                }
                
                // Delete the step
                $step->delete();
            }
            
            // 2. Update project name to add (Nihil)
            if (!str_contains($this->record->name, '(Nihil)')) {
                $this->record->name = $this->record->name . ' (Nihil)';
                $this->record->save();
            }
            
            // Commit the transaction
            DB::commit();
            
            // Show success notification
            Notification::make()
                ->title('Success')
                ->body('Project has been converted to Nihil status successfully.')
                ->success()
                ->send();
                
            // Refresh the page to show changes
            $this->redirect(request()->header('Referer'));
            
        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollBack();
            
            // Show error notification
            Notification::make()
                ->title('Error')
                ->body('Failed to convert project to Nihil status: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}