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

    /**
     * Add a cache property to track if notification has been sent
     */
    public $completionNotificationSent = false;

    /**
     * Initialize component and update project statuses when the page loads
     */
    public function mount($record): void
    {
        parent::mount($record);
        
        // Update project and step statuses when page loads
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

        // Only update to in_progress automatically
        if ($steps->where('status', 'in_progress')->count() > 0) {
            $this->record->status = 'in_progress';
            $this->record->save();
        }
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
            $documentsCompleted = $documents->every(fn($doc) => $doc->status === 'approved' || $doc->status === 'rejected');
            
            // Only check for pending_review on documents that aren't rejected
            $pendingReviewCount = $documents->filter(function($doc) {
                return $doc->status === 'pending_review' && $doc->status !== 'rejected';
            })->count();

            if (
                $tasks->where('status', 'in_progress')->count() > 0 ||
                $pendingReviewCount > 0
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

    /**
     * Check if the project is locked (completed)
     */
    public function isProjectLocked(): bool
    {
        return $this->record->status === 'completed';
    }

    // Task Status Management
    public function toggleTaskStatus(Task $task): void
    {
        if ($this->isProjectLocked()) {
            Notification::make()
                ->title('Project is locked')
                ->body('This project is completed and its tasks can no longer be modified.')
                ->warning()
                ->send();
            return;
        }
        
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
        if ($this->isProjectLocked()) {
            Notification::make()
                ->title('Project is locked')
                ->body('This project is completed and its documents can no longer be modified.')
                ->warning()
                ->send();
            return;
        }
        
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
        // Check if current user has the required role
        $hasRequiredRole = auth()->user()->hasAnyRole(['director', 'project-manager', 'super-admin']);
        
        if (!$hasRequiredRole) {
            return [
                // Only include the other actions if user doesn't have required role
                Actions\Action::make('edit')
                    ->url(static::getResource()::getUrl('edit', ['record' => $this->record]))
                    ->icon('heroicon-o-pencil-square')
                    ->visible(fn() => $this->record->status !== 'completed')
                    ->button(),
                
                Actions\Action::make('viewActivity')
                    ->label('View Activity Log')
                    ->icon('heroicon-o-clock')
                    ->url(fn() => ProjectResource::getUrl('activity', ['record' => $this->record])),
            ];
        }
        
        // Check if all documents are either approved or rejected
        $allDocumentsResolved = true;
        $unfinishedItems = [];
        
        foreach ($this->record->steps as $step) {
            // Check for documents not in approved/rejected status
            foreach ($step->requiredDocuments as $document) {
                if ($document->status === 'draft') {
                    $unfinishedItems[] = "Document: {$document->name} (Draft)";
                    $allDocumentsResolved = false;
                }
                
                $submittedDocs = $document->submittedDocuments;
                if ($submittedDocs->count() > 0) {
                    foreach ($submittedDocs as $doc) {
                        if (!in_array($doc->status, ['approved', 'rejected'])) {
                            $unfinishedItems[] = "Submitted Document for {$document->name} needs review";
                            $allDocumentsResolved = false;
                            break;
                        }
                    }
                }
            }
        }
        
        // All documents resolved is the only requirement now
        $requirementsMet = $allDocumentsResolved;
        
        // Determine tooltip message if requirements are not met
        $tooltipMessage = "";
        if (!$requirementsMet) {
            $tooltipMessage = "This project cannot be completed yet. ";
            
            if (!$allDocumentsResolved) {
                if (count($unfinishedItems) > 0) {
                    $tooltipMessage .= "Unfinished documents: " . implode(", ", array_slice($unfinishedItems, 0, 3));
                    if (count($unfinishedItems) > 3) {
                        $tooltipMessage .= " and " . (count($unfinishedItems) - 3) . " more.";
                    }
                }
            }
        }
        
        // Conditions for showing complete button
        $canComplete = $requirementsMet && $this->record->status !== 'completed';
        
        // Check if this is the first time the button is available 
        if ($canComplete && !$this->completionNotificationSent) {
            $this->notifyProjectReadyForCompletion();
            $this->completionNotificationSent = true;
        }

        return [
            Actions\Action::make('completeProject')
                ->label('Complete Project')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->tooltip($tooltipMessage)
                ->disabled(!$requirementsMet)
                ->requiresConfirmation()
                ->modalHeading('Complete Project')
                ->modalDescription('Are you sure you want to mark this project as completed? This will lock all steps, tasks, and documents, preventing further changes.')
                ->modalSubmitActionLabel('Yes, complete project')
                ->visible(!$this->isProjectLocked())
                ->action(function() {
                    // Set project status to completed
                    $this->record->status = 'completed';
                    $this->record->save();
                    
                    // Mark all steps as completed
                    foreach ($this->record->steps as $step) {
                        $step->status = 'completed';
                        $step->save();
                        
                        // Mark all tasks as completed
                        foreach ($step->tasks as $task) {
                            if ($task->status !== 'completed') {
                                $task->status = 'completed';
                                $task->save();
                            }
                        }
                        
                        // Mark all documents as approved/completed
                        foreach ($step->requiredDocuments as $document) {
                            if ($document->status !== 'approved') {
                                // Only change status if not already approved
                                $document->status = 'approved';
                                $document->save();
                            }
                        }
                    }

                    // Create record in activity log
                    Comment::create([
                        'user_id' => auth()->id(),
                        'commentable_id' => $this->record->id,
                        'commentable_type' => get_class($this->record),
                        'content' => "Project marked as completed and locked. All steps, tasks, and documents were finalized."
                    ]);

                    Notification::make()
                        ->title('Project completed successfully')
                        ->body('The project has been marked as completed and all items have been locked.')
                        ->success()
                        ->send();
                }),
            
            // Modify edit action to be disabled when project is completed
            Actions\Action::make('edit')
                ->url(static::getResource()::getUrl('edit', ['record' => $this->record]))
                ->icon('heroicon-o-pencil-square')
                ->visible(fn() => $this->record->status !== 'completed')
                ->button(),
            
            Actions\Action::make('viewActivity')
                ->label('View Activity Log')
                ->icon('heroicon-o-clock')
                ->url(fn() => ProjectResource::getUrl('activity', ['record' => $this->record])),
        ];
    }

    /**
     * Send notification to project managers and directors
     */
    protected function notifyProjectReadyForCompletion(): void
    {
        // Get users assigned to this project who are directors or project managers
        $projectManagers = \App\Models\User::whereHas('roles', function($query) {
            $query->whereIn('name', ['director', 'project-manager']);
        })
        ->whereHas('userProjects', function($query) {
            $query->where('project_id', $this->record->id);
        })
        ->get();
        
        // If no project managers are assigned, get all project managers/directors in the system
        if ($projectManagers->isEmpty()) {
            $projectManagers = \App\Models\User::whereHas('roles', function($query) {
                $query->whereIn('name', ['director', 'project-manager', 'super-admin']);
            })->get();
        }
        
        // Create a database notification for each manager
        foreach ($projectManagers as $manager) {
            // Skip sending to current user if they're a manager
            if ($manager->id === auth()->id()) {
                continue;
            }
            
            Notification::make()
                ->title('Project Ready for Completion')
                ->body(sprintf(
                    'Project "%s" for client "%s" is ready to be marked as completed. All tasks and documents have been resolved.',
                    $this->record->name,
                    $this->record->client->name
                ))
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->label('View Project')
                        ->url(ProjectResource::getUrl('view', ['record' => $this->record]))
                        ->markAsRead(),
                    \Filament\Notifications\Actions\Action::make('dismiss')
                        ->label('Dismiss')
                        ->markAsRead(),
                ])
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->sendToDatabase($manager);
        }
        
        // Also create an activity log entry for the project
        Comment::create([
            'user_id' => auth()->id(),
            'commentable_id' => $this->record->id,
            'commentable_type' => get_class($this->record),
            'content' => "Project is now ready for completion."
        ]);
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