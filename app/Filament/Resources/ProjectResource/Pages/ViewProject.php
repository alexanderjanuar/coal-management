<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Livewire\Attributes\Computed;
use App\Models\Comment;
use App\Models\Task;
use App\Models\RequiredDocument;
use Filament\Notifications\Notification;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;
    protected static string $view = 'filament.pages.project-details';

    public $newTaskStatus = '';
    public $selectedTaskId = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $this->updateProjectStepStatus();
        $this->updateProjectStatus();

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

    private function updateProjectStatus(): void
    {
        $steps = $this->record->steps;

        if ($steps->isEmpty()) {
            return;
        }

        if ($steps->where('status', 'in_progress')->count() > 0) {
            $this->record->status = 'in_progress';
        } elseif ($steps->every(fn($step) => $step->status === 'completed')) {
            $this->record->status = 'completed';
        }

        $this->record->save();
    }

    private function updateProjectStepStatus(): void
    {
        foreach ($this->record->steps as $step) {
            $tasks = $step->tasks;
            $documents = $step->requiredDocuments;

            if ($tasks->isEmpty() && $documents->isEmpty()) {
                continue;
            }

            $tasksCompleted = $tasks->every(fn($task) => $task->status === 'completed');
            $documentsCompleted = $documents->every(fn($doc) => $doc->status === 'approved');

            if (
                $tasks->where('status', 'in_progress')->count() > 0 ||
                $documents->where('status', 'pending_review')->count() > 0
            ) {
                $step->status = 'in_progress';
            } elseif ($tasksCompleted && $documentsCompleted) {
                $step->status = 'completed';
            }

            $step->save();
        }
    }

    private function calculateProgress(): int
    {
        $totalSteps = $this->record->steps->count();
        $completedSteps = $this->record->steps->where('status', 'completed')->count();

        return $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;
    }

    // Task Status Management
    public function toggleTaskStatus(Task $task): void
    {
        $task->status = $task->status === 'completed' ? 'pending' : 'completed';
        $task->save();

        Notification::make()
            ->title('Task status updated successfully')
            ->success()
            ->send();
    }



    // Document Status Management
    public function updateDocumentStatus(RequiredDocument $document, string $status): void
    {
        $document->status = $status;
        $document->save();

        Notification::make()
            ->title("Document status updated to " . ucfirst($status))
            ->success()
            ->send();
    }

    // Step Status Management
    public function updateStepStatus(string $status): void
    {
        $this->record->status = $status;
        $this->record->save();

        Notification::make()
            ->title("Project status updated to " . ucfirst($status))
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('edit')
                ->url(static::getResource()::getUrl('edit', ['record' => $this->record]))
                ->icon('heroicon-o-pencil-square')
                ->button(),
            Actions\Action::make('viewActivity')
                ->label('View Activity Log')
                ->icon('heroicon-o-clock')
                ->url(fn() => ProjectResource::getUrl('activity', ['record' => $this->record])),
        ];
    }

    public function updateTaskStatus($taskId, $status): void
    {
        $this->selectedTaskId = $taskId;
        $this->newTaskStatus = $status;
    }

    public function confirmStatusChange(): void
    {
        $task = Task::find($this->selectedTaskId);
        $oldStatus = $task->status;

        $task->status = $this->newTaskStatus;
        $task->save();

        Comment::create([
            'user_id' => auth()->id(),
            'commentable_id' => $task->id,
            'commentable_type' => Task::class,
            'content' => "Status changed from " . ucfirst($oldStatus) . " to " . ucfirst($this->newTaskStatus)
        ]);

        $this->dispatch('close-modal', ['id' => "confirm-status-modal-{$this->selectedTaskId}"]);

        $this->updateProjectStepStatus();
        $this->updateProjectStatus();
    }
}