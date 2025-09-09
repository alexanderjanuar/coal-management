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
use Nben\FilamentRecordNav\Concerns\WithRecordNavigation;
use Nben\FilamentRecordNav\Actions\NextRecordAction;
use Nben\FilamentRecordNav\Actions\PreviousRecordAction;

class ViewProject extends ViewRecord
{
    use WithRecordNavigation;
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
        
        // Update required document statuses first
        $this->updateRequiredDocumentStatuses();
        
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

    /**
     * Check if the client is inactive (which locks the project)
     */
    public function isClientInactive(): bool
    {
        return $this->record->client->status === 'Inactive';
    }

    /**
     * Check if the project is locked (completed or client inactive)
     */
    public function isProjectLocked(): bool
    {
        return $this->record->status === 'completed' || $this->isClientInactive();
    }

    /**
     * Update required document statuses based on submitted documents
     */
    private function updateRequiredDocumentStatuses(): void
    {
        foreach ($this->record->steps as $step) {
            foreach ($step->requiredDocuments as $requiredDocument) {
                $submittedDocs = $requiredDocument->submittedDocuments;
                
                if ($submittedDocs->isEmpty()) {
                    // Check if status is approved_without_document, if so keep it
                    if ($requiredDocument->status === 'approved_without_document') {
                        continue;
                    }
                    
                    if ($requiredDocument->status !== 'draft') {
                        $requiredDocument->status = 'draft';
                        $requiredDocument->save();
                    }
                    continue;
                }
                
                // Get all submitted document statuses
                $submittedStatuses = $submittedDocs->pluck('status')->toArray();
                
                // Determine the required document status based on submitted documents
                $newStatus = $this->determineRequiredDocumentStatus($submittedStatuses);
                
                if ($requiredDocument->status !== $newStatus) {
                    $requiredDocument->status = $newStatus;
                    $requiredDocument->save();
                }
            }
        }
    }

    /**
     * Determine required document status based on submitted document statuses
     */
    private function determineRequiredDocumentStatus(array $submittedStatuses): string
    {
        // Priority order: uploaded > pending_review > approved/rejected
        // If any document is uploaded, the required document should be "uploaded"
        if (in_array('uploaded', $submittedStatuses)) {
            return 'uploaded';
        }
        
        // If any document is pending review, the required document should be "pending_review"
        if (in_array('pending_review', $submittedStatuses)) {
            return 'pending_review';
        }
        
        // If all documents are approved or rejected, the required document should be "approved"
        $onlyApprovedOrRejected = !array_diff($submittedStatuses, ['approved', 'rejected']);
        if ($onlyApprovedOrRejected && !empty($submittedStatuses)) {
            return 'approved';
        }
        
        // Default fallback
        return 'draft';
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
            
            // Updated logic: step is completed only if all required documents are approved or approved_without_document
            $documentsCompleted = $documents->every(fn($doc) => 
                in_array($doc->status, ['approved', 'approved_without_document'])
            );
            
            // Check for documents that are still in progress
            $hasDocumentsInProgress = $documents->whereIn('status', ['uploaded', 'pending_review'])->count() > 0;

            if (
                $tasks->where('status', 'in_progress')->count() > 0 ||
                $hasDocumentsInProgress
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

    public function toggleTaskStatus(Task $task): void
    {
        if ($this->isClientInactive()) {
            Notification::make()
                ->title('Client is inactive')
                ->body('This client is inactive and its projects are locked from modifications.')
                ->warning()
                ->send();
            return;
        }
        
        if ($this->record->status === 'completed') {
            Notification::make()
                ->title('Project is completed')
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

    public function updateDocumentStatus(RequiredDocument $document, string $status): void
    {
        if ($this->isClientInactive()) {
            Notification::make()
                ->title('Client is inactive')
                ->body('This client is inactive and its projects are locked from modifications.')
                ->warning()
                ->send();
            return;
        }
        
        if ($this->record->status === 'completed') {
            Notification::make()
                ->title('Project is completed')
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
        $hasRequiredRole = auth()->user()->hasAnyRole(['direktur', 'project-manager', 'super-admin','verificator']);
        
        // Check if client is inactive
        $clientInactive = $this->isClientInactive();
        
        if (!$hasRequiredRole) {
            return [
                // Only include the other actions if user doesn't have required role
                Actions\Action::make('edit')
                    ->url(static::getResource()::getUrl('edit', ['record' => $this->record]))
                    ->icon('heroicon-o-pencil-square')
                    ->visible(fn() => !$this->isProjectLocked())
                    ->button(),
                
                Actions\Action::make('viewActivity')
                    ->label('View Activity Log')
                    ->icon('heroicon-o-clock')
                    ->url(fn() => ProjectResource::getUrl('activity', ['record' => $this->record])),
            ];
        }
        
        // Update the document completion check logic
        $allDocumentsResolved = true;
        $unfinishedItems = [];
        
        foreach ($this->record->steps as $step) {
            foreach ($step->requiredDocuments as $document) {
                // Check if document is not approved or approved_without_document
                if (!in_array($document->status, ['approved', 'approved_without_document'])) {
                    $statusLabel = match($document->status) {
                        'approved_without_document' => 'Disetujui Tanpa Dokumen',
                        'approved' => 'Disetujui',
                        'pending_review' => 'Menunggu Review',
                        'uploaded' => 'Diunggah',
                        'rejected' => 'Ditolak',
                        'draft' => 'Draft',
                        default => ucfirst(str_replace('_', ' ', $document->status))
                    };
                    $unfinishedItems[] = "Document: {$document->name} ({$statusLabel})";
                    $allDocumentsResolved = false;
                }
            }
        }
        
        // All documents resolved is the only requirement now
        $requirementsMet = $allDocumentsResolved && !$clientInactive;
        
        // Determine tooltip message if requirements are not met
        $tooltipMessage = "";
        if (!$requirementsMet) {
            if ($clientInactive) {
                $tooltipMessage = "This project cannot be completed because the client is inactive. ";
            } else {
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
        }
        
        // Conditions for showing complete button
        $canComplete = $requirementsMet && $this->record->status !== 'completed';

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
                            if (!in_array($document->status, ['approved', 'approved_without_document'])) {
                                // Only change status if not already approved or approved_without_document
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
            
            // Modify edit action to be disabled when project is completed or client inactive
            Actions\Action::make('edit')
                ->url(static::getResource()::getUrl('edit', ['record' => $this->record]))
                ->icon('heroicon-o-pencil-square')
                ->visible(fn() => !$this->isProjectLocked())
                ->button(),
            
            Actions\Action::make('viewActivity')
                ->label('View Activity Log')
                ->icon('heroicon-o-clock')
                ->url(fn() => ProjectResource::getUrl('activity', ['record' => $this->record])),

            PreviousRecordAction::make(),
            NextRecordAction::make(),
        ];
    }

    /**
     * Send notification to project managers and directors
     */
    protected function notifyProjectReadyForCompletion(): void
    {
        // Get users assigned to this project who are directors or project managers
        $projectManagers = \App\Models\User::whereHas('roles', function($query) {
            $query->whereIn('name', ['director', 'project-manager','verificator']);
        })
        ->whereHas('userProjects', function($query) {
            $query->where('project_id', $this->record->id);
        })
        ->get();
        
        // If no project managers are assigned, get all project managers/directors in the system
        if ($projectManagers->isEmpty()) {
            $projectManagers = \App\Models\User::whereHas('roles', function($query) {
                $query->whereIn('name', ['director', 'project-manager', 'super-admin','verificator']);
            })->get();
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

        // Update statuses in the correct order
        $this->updateRequiredDocumentStatuses();
        $this->updateProjectStepStatus();
        $this->updateProjectStatus();
    }
}