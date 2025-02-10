<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\RequiredDocument;
use App\Models\SubmittedDocument;
use App\Models\ClientDocument;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Illuminate\Support\Str;

class ProjectDetailDocumentModal extends Component implements HasForms
{
    use InteractsWithForms;

    /**
     * Component Properties
     */
    public RequiredDocument $document;
    public ?array $data = [];
    public ?string $newComment = '';

    /**
     * Document Preview Properties
     */
    public $previewingDocument = null;
    public $previewUrl = null;
    public $isPreviewModalOpen = false;

    /**
     * Listeners
     */
    protected $listeners = [
        'refresh' => '$refresh',
        'documentUploaded' => 'handleDocumentUploaded',
    ];

    /**
     * Component Initialization
     */
    public function mount(RequiredDocument $document): void
    {
        $this->document = $document;
        $this->form->fill();
    }

    /**
     * Form Configuration
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('document')
                    ->label('Select Document')
                    ->required()
                    ->acceptedFileTypes([
                        'application/pdf',
                        'image/*',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ])
                    ->maxSize(10240)
                    ->preserveFilenames()
                    ->disk('public')
                    ->directory(function () {
                        $clientName = Str::slug($this->document->projectStep->project->client->name);
                        $projectName = Str::slug($this->document->projectStep->project->name);
                        return "clients/{$clientName}/{$projectName}";
                    })
                    ->downloadable()
                    ->openable()
                    ->disabled(auth()->user()->hasRole('client'))
                    ->helperText(function () {
                        if (auth()->user()->hasRole('client')) {
                            return 'You do not have permission to upload documents';
                        }
                        return null;
                    })
            ])
            ->statePath('data');
    }

    /**
     * Enhanced notification system for project members
     */
    protected function sendProjectNotifications(string $title, string $body, string $type = 'info', ?string $action = null): void
    {
        // Create the notification
        $notification = Notification::make()
            ->title($title)
            ->body($body)
            ->icon($this->getNotificationIcon($type));

        // Add action if provided
        if ($action) {
            $notification->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->button()
                    ->label($action)
                    ->url(route('filament.admin.resources.projects.view', [
                        'record' => $this->document->projectStep->project->id,
                        'openDocument' => $this->document->id  // Add document ID as parameter
                    ]))
            ]);
        }

        // Get all users related to the project
        $projectUsers = $this->document->projectStep->project->userProject()
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->reject(function ($user) {
                return $user->id === auth()->id(); // Exclude current user
            });

        // Send notifications to all project users
        foreach ($projectUsers as $user) {
            $notification->sendToDatabase($user)->broadcast($user)->persistent();
        }

        // Send UI notification to current user
        Notification::make()
                    ->title($title)
                    ->body($body)
            ->{$type}()
                ->send();
    }

    /**
     * Document Management Methods
     */
    public function uploadDocument(): void
    {
        $data = $this->form->getState();

        $submission = SubmittedDocument::create([
            'required_document_id' => $this->document->id,
            'user_id' => auth()->id(),
            'file_path' => $data['document'],
        ]);

        $this->document->status = 'pending_review';
        $this->document->save();

        $this->form->fill();

        $this->dispatch('refresh');
        $this->dispatch('documentUploaded', documentId: $submission->id);

        // Get related project information
        $projectStep = $this->document->projectStep;
        $project = $projectStep->project;
        $client = $project->client;

        // Send notifications with HTML line breaks
        $this->sendProjectNotifications(
            "New Document Uploaded",
            sprintf(
                "<strong>Client:</strong> %s<br><strong>Project:</strong> %s<br><strong>Step:</strong> %s<br><strong>Document:</strong> %s<br><strong>Uploaded by:</strong> %s",
                $client->name,
                $project->name,
                $projectStep->name,
                $this->document->name,
                auth()->user()->name
            ),
            'success',
            'View Document'
        );
    }

    public function viewDocument(SubmittedDocument $submission): void
    {
        $this->previewingDocument = $submission;
        $this->previewUrl = Storage::disk('public')->url($submission->file_path);
        $this->isPreviewModalOpen = true;
    }

    public function closePreview(): void
    {
        $this->isPreviewModalOpen = false;
        $this->previewUrl = null;
        $this->previewingDocument = null;
    }

    public function downloadDocument($documentId)
    {
        $document = SubmittedDocument::find($documentId);
        if ($document) {
            return Storage::disk('public')->download($document->file_path);
        }
    }

    /**
     * Status Management Methods
     */
    public function updateStatus(string $status): void
    {
        try {
            $oldStatus = $this->document->status;
            $this->document->status = $status;
            $this->document->save();

            $this->createStatusChangeComment($oldStatus, $status);

            $this->dispatch('refresh');

            // Get related project information
            $projectStep = $this->document->projectStep;
            $project = $projectStep->project;
            $client = $project->client;

            // Send notifications with HTML formatting
            $this->sendProjectNotifications(
                "Document Status Updated",
                sprintf(
                    "<strong>Client:</strong> %s<br><strong>Project:</strong> %s<br><strong>Step:</strong> %s<br><strong>Document:</strong> %s<br><strong>Status Change:</strong> %s to %s<br><strong>Updated by:</strong> %s",
                    $client->name,
                    $project->name,
                    $projectStep->name,
                    $this->document->name,
                    ucwords(str_replace('_', ' ', $oldStatus)),
                    ucwords(str_replace('_', ' ', $status)),
                    auth()->user()->name
                ),
                'success',
                'View Document'
            );
        } catch (\Exception $e) {
            $this->sendNotification('error', 'Error updating status', 'Please try again.');
        }
    }

    // Update the notification icon method to include more specific icons
    protected function getNotificationIcon(string $type): string
    {
        return match ($type) {
            'success' => 'heroicon-o-check-circle',
            'danger' => 'heroicon-o-x-circle',
            'warning' => 'heroicon-o-exclamation-triangle',
            'info' => 'heroicon-o-information-circle',
            default => 'heroicon-o-information-circle',
        };
    }

    /**
     * Comment Management Methods
     */
    public function addComment(): void
    {
        $this->validate([
            'newComment' => 'required|min:1|max:1000'
        ]);

        try {
            $comment = Comment::create([
                'user_id' => auth()->id(),
                'commentable_type' => RequiredDocument::class,
                'commentable_id' => $this->document->id,
                'content' => $this->newComment,
                'status' => 'approved'
            ]);

            $this->newComment = '';
            $this->dispatch('refresh');

            // Get related project information
            $projectStep = $this->document->projectStep;
            $project = $projectStep->project;
            $client = $project->client;

            $plainContent = strip_tags($comment->content);
            $truncatedContent = Str::limit($plainContent, 100);

            // Send notifications with HTML formatting
            $this->sendProjectNotifications(
                "New Comment on Document",
                sprintf(
                    "<strong>Client:</strong> %s<br><strong>Project:</strong> %s<br><strong>Step:</strong> %s<br><strong>Document:</strong> %s<br><strong>Comment by:</strong> %s<br><strong>Message:</strong> %s",
                    $client->name,
                    $project->name,
                    $projectStep->name,
                    $this->document->name,
                    auth()->user()->name,
                    $truncatedContent
                ),
                'info',
                'View Comment'
            );
        } catch (\Exception $e) {
            $this->sendNotification('error', 'Error adding comment', 'Please try again.');
        }
    }

    /**
     * Helper Methods
     */
    protected function getFileType(): ?string
    {
        if (!$this->previewingDocument) {
            return null;
        }

        return strtolower(pathinfo($this->previewingDocument->file_path, PATHINFO_EXTENSION));
    }

    protected function createStatusChangeComment(string $oldStatus, string $newStatus): void
    {
        $projectStep = $this->document->projectStep;
        $project = $projectStep->project;
        $client = $project->client;

        Comment::create([
            'user_id' => auth()->id(),
            'commentable_type' => RequiredDocument::class,
            'commentable_id' => $this->document->id,
            'content' => sprintf(
                "Status changed from <strong class='text-gray-700'>%s</strong> to <strong class='text-gray-700'>%s</strong>",
                ucwords(str_replace('_', ' ', $oldStatus)),
                ucwords(str_replace('_', ' ', $newStatus))
            ),
            'status' => 'approved'
        ]);
    }

    protected function sendNotification(string $type, string $title, ?string $body = null): void
    {
        $notification = Notification::make()
            ->title($title)
            ->icon($this->getNotificationIcon($type));

        if ($body) {
            $notification->body($body);
        }

        // Map status to notification type
        $notificationMethod = match ($type) {
            'danger' => 'danger',
            'success' => 'success',
            'warning' => 'warning',
            'info' => 'info',
            'error' => 'danger',
            default => 'info'
        };

        $notification->{$notificationMethod}()->send();
    }

    public function handleDocumentUploaded(int $documentId): void
    {
        $this->document->refresh();
    }

    /**
     * Render Method
     */
    public function render()
    {
        return view('livewire.project-detail.project-detail-document-modal', [
            'comments' => $this->document->comments()
                ->with('user')
                ->latest()
                ->get(),
            'fileType' => $this->getFileType()
        ]);
    }
}