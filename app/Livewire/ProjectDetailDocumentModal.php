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
use Filament\Forms\Components\RichEditor;

class ProjectDetailDocumentModal extends Component implements HasForms
{
    use InteractsWithForms;

    /**
     * Component Properties
     */
    public RequiredDocument $document;
    public ?array $data = [];
    public ?array $commentData = [];
    public ?string $newComment = '';
    public ?int $editingCommentId = null;
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
        $this->uploadFileForm->fill();
        $this->createCommentForm->fill();
    }

    protected function getForms(): array
    {
        return [
            'uploadFileForm',
            'createCommentForm',
        ];
    }

    /**
     * Form Configuration
     */
    public function uploadFileForm(Form $form): Form
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

    public function createCommentForm(Form $form): Form
    {
        return $form
            ->schema([
                RichEditor::make('newComment')
                    ->label('')
                    ->id('comment-editor-' . $this->document->id)
                    ->toolbarButtons([
                        'attachFiles',
                        'bold',
                        'bulletList',
                        'h2',
                        'link',
                        'orderedList',
                        'underline',
                    ])
                    ->extraInputAttributes(['style' => 'font-size:14px'])
                    ->placeholder('Write your comment here...')
                    ->required()
            ])
            ->statePath('commentData');
    }

    /**
     * Get the appropriate notification icon based on action and status type
     * 
     * @param string $type The type of notification (success, danger, etc.)
     * @param string $action The action being performed (optional)
     * @return string The heroicon name
     */
    protected function getNotificationIcon(string $type, string $action = ''): string
    {
        // First check for specific actions
        if ($action) {
            return match ($action) {
                'document_upload' => 'heroicon-o-document-arrow-up',
                'document_download' => 'heroicon-o-document-arrow-down',
                'document_review' => 'heroicon-o-document-magnifying-glass',
                'comment' => 'heroicon-o-chat-bubble-left-ellipsis',
                'status_change' => 'heroicon-o-arrow-path',
                'rejection' => 'heroicon-o-x-mark',
                'approval' => 'heroicon-o-check-badge',
                'document_delete' => 'heroicon-o-document-minus',
                'document_preview' => 'heroicon-o-document-text',
                'notification' => 'heroicon-o-bell-alert',
                default => $this->getDefaultIconForType($type)
            };
        }

        // Fallback to type-based icons
        return $this->getDefaultIconForType($type);
    }

    /**
     * Get default icon based on notification type
     * 
     * @param string $type
     * @return string
     */
    private function getDefaultIconForType(string $type): string
    {
        return match ($type) {
            'success' => 'heroicon-o-check-circle',
            'danger' => 'heroicon-o-x-circle',
            'warning' => 'heroicon-o-exclamation-triangle',
            'info' => 'heroicon-o-information-circle',
            'error' => 'heroicon-o-x-circle',
            default => 'heroicon-o-bell'
        };
    }

    /**
     * Enhanced notification system for project members
     */
    protected function sendProjectNotifications(
        string $title,
        string $body,
        string $type = 'info',
        ?string $action = null,
        ?string $notificationAction = null
    ): void {
        // Create the notification
        $notification = Notification::make()
                    ->title($title)
                    ->body($body)
                    ->icon($this->getNotificationIcon($type, $notificationAction))
                    ->color($type)
            ->{$type}()
                ->persistent();

        // Add action if provided
        if ($action) {
            $notification->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label($action)
                    ->markAsRead()
                    ->url(route('filament.admin.resources.projects.view', [
                        'record' => $this->document->projectStep->project->id,
                        'openDocument' => $this->document->id
                    ])),

                \Filament\Notifications\Actions\Action::make('Mark As Read')
                    ->markAsRead(),
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
                return $user->id === auth()->id();
            });

        // Send notifications to all project users
        foreach ($projectUsers as $user) {
            $notification->icon($this->getNotificationIcon($type, $notificationAction))
                ->color($type)
                ->sendToDatabase($user)
                ->broadcast($user);
        }

        // Send UI notification to current user
        Notification::make()
                    ->title($title)
                    ->body($body)
                    ->icon($this->getNotificationIcon($type, $notificationAction))
                    ->color($type)
            ->{$type}()
                ->send();
    }

    /**
     * Document Management Methods
     */
    public function uploadDocument(): void
    {
        $data = $this->uploadFileForm->getState();

        $submission = SubmittedDocument::create([
            'required_document_id' => $this->document->id,
            'user_id' => auth()->id(),
            'file_path' => $data['document'],
        ]);

        // Change status to 'uploaded' when document is first uploaded
        $this->document->status = 'uploaded';  // Changed from 'pending_review' to 'uploaded'
        $this->document->save();

        $this->uploadFileForm->fill();

        $this->dispatch('refresh');
        $this->dispatch('documentUploaded', documentId: $submission->id);

        // Get related project information
        $projectStep = $this->document->projectStep;
        $project = $projectStep->project;
        $client = $project->client;

        // Send notifications
        $this->sendProjectNotifications(
            "New Document",
            sprintf(
                "<span style='color: #f59e0b; font-weight: 500;'>%s</span><br><strong>Project:</strong> %s<br><strong>Document:</strong> %s<br><strong>Uploaded by:</strong> %s",
                $client->name,
                $project->name,
                $this->document->name,
                auth()->user()->name
            ),
            'success',
            'View Document',
            'document_upload'
        );
    }

    public function viewDocument(SubmittedDocument $submission): void
    {
        $this->previewingDocument = $submission;
        $this->previewUrl = Storage::disk('public')->url($submission->file_path);
        $this->isPreviewModalOpen = true;

        // Check if the viewer is a project manager or director and document status is 'uploaded'
        if (
            auth()->user()->hasRole(['direktur', 'project-manager']) &&
            $this->document->status === 'uploaded'
        ) {
            // Update status to pending_review and set reviewer information
            $this->document->update([
                'status' => 'pending_review',
                'reviewer_id' => auth()->id(),
                'reviewed_at' => now()
            ]);

            // Create a system comment for the status change
            $this->createStatusChangeComment('uploaded', 'pending_review');

            // Send notification about status change
            $this->sendProjectNotifications(
                "Document Under Review",
                sprintf(
                    "<span style='color: #f59e0b; font-weight: 500;'>%s</span><br><strong>Document:</strong> %s<br><strong>Status:</strong> Under Review<br><strong>Reviewer:</strong> %s",
                    $this->document->projectStep->project->client->name,
                    $this->document->name,
                    auth()->user()->name
                ),
                'info',
                'View Document',
                'document_review'
            );
        }
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

            // Determine notification action based on status
            $notificationAction = match ($status) {
                'approved' => 'approval',
                'rejected' => 'rejection',
                default => 'status_change'
            };

            // Send notifications with HTML formatting
            $this->sendProjectNotifications(
                "Status Updated",
                sprintf(
                    "<span style='color: #f59e0b; font-weight: 500;'>%s</span><br><strong>Document:</strong> %s<br><strong>Status:</strong> %s → %s<br><strong>Updated by:</strong> %s",
                    $client->name,
                    $this->document->name,
                    ucwords(str_replace('_', ' ', $oldStatus)),
                    ucwords(str_replace('_', ' ', $status)),
                    auth()->user()->name
                ),
                'success',
                'View Document',
                $notificationAction
            );
        } catch (\Exception $e) {
            $this->sendNotification('error', 'Error updating status', 'Please try again.');
        }
    }

    /**
     * Comment Management Methods
     */
    public function addComment(): void
    {
        $data = $this->createCommentForm->getState();

        $this->validate([
            'commentData.newComment' => 'required|min:1|max:1000'
        ]);

        try {
            if ($this->editingCommentId) {
                // Update existing comment
                $comment = Comment::findOrFail($this->editingCommentId);

                if ($comment->user_id !== auth()->id()) {
                    throw new \Exception('Unauthorized action.');
                }

                $comment->update([
                    'content' => $data['newComment']
                ]);

                $this->editingCommentId = null; // Reset editing state
            } else {
                // Create new comment
                $comment = Comment::create([
                    'user_id' => auth()->id(),
                    'commentable_type' => RequiredDocument::class,
                    'commentable_id' => $this->document->id,
                    'content' => $data['newComment'],
                    'status' => 'approved'
                ]);
            }

            // Reset form and refresh
            $this->createCommentForm->fill();
            $this->dispatch('refresh');

            // Send notification
            $plainContent = strip_tags($comment->content);
            $truncatedContent = Str::limit($plainContent, 100);

            $this->sendProjectNotifications(
                $this->editingCommentId ? "Comment Updated" : "New Comment",
                sprintf(
                    "<span style='color: #f59e0b; font-weight: 500;'>%s</span><br><strong>Document:</strong> %s<br><strong>Comment:</strong> %s<br><strong>By:</strong> %s",
                    $this->document->projectStep->project->client->name,
                    $this->document->name,
                    $truncatedContent,
                    auth()->user()->name
                ),
                'info',
                'View Comment',
                'comment'
            );

        } catch (\Exception $e) {
            $this->sendNotification('error', 'Error', 'Unable to save comment.');
        }
    }

    public function editComment(int $commentId): void
    {
        try {
            $comment = Comment::findOrFail($commentId);

            // Ensure user can only edit their own comments
            if ($comment->user_id !== auth()->id()) {
                throw new \Exception('Unauthorized action.');
            }

            // Set the form data for editing
            $this->createCommentForm->fill([
                'newComment' => $comment->content
            ]);

            // You might want to set a state to track which comment is being edited
            $this->editingCommentId = $commentId;

            // Show the comment form if it's hidden
            $this->dispatch('showCommentForm');

        } catch (\Exception $e) {
            $this->sendNotification('error', 'Error', 'Unable to edit comment.');
        }
    }

    public function deleteComment(int $commentId): void
    {
        try {
            $comment = Comment::findOrFail($commentId);

            // Ensure user can only delete their own comments
            if ($comment->user_id !== auth()->id()) {
                throw new \Exception('Unauthorized action.');
            }

            // Store comment info for notification
            $commentContent = strip_tags($comment->content);
            $truncatedContent = Str::limit($commentContent, 50);

            // Delete the comment
            $comment->delete();

            // Send notification
            $this->sendProjectNotifications(
                "Comment Deleted",
                sprintf(
                    "<span style='color: #f59e0b; font-weight: 500;'>%s</span><br><strong>Document:</strong> %s<br><strong>Comment:</strong> %s",
                    $this->document->projectStep->project->client->name,
                    $this->document->name,
                    $truncatedContent
                ),
                'info',
                null,
                'comment'
            );

            $this->dispatch('refresh');

        } catch (\Exception $e) {
            $this->sendNotification('error', 'Error', 'Unable to delete comment.');
        }
    }

    public function removeDocument(int $documentId): void
    {
        try {
            $submission = SubmittedDocument::findOrFail($documentId);

            // Store document info for notification
            $documentName = basename($submission->file_path);
            $clientName = $this->document->projectStep->project->client->name;
            $projectName = $this->document->projectStep->project->name;

            // Delete the file from storage
            Storage::disk('public')->delete($submission->file_path);

            // Delete the record
            $submission->delete();

            // Send notification
            $this->sendProjectNotifications(
                "Document Removed",
                sprintf(
                    "<span style='color: #f59e0b; font-weight: 500;'>%s</span><br><strong>Project:</strong> %s<br><strong>Document:</strong> %s<br><strong>Removed by:</strong> %s",
                    $clientName,
                    $projectName,
                    $documentName,
                    auth()->user()->name
                ),
                'danger',
                null,
                'document_delete'
            );

            // Show success notification
            Notification::make()
                ->title('Document Removed')
                ->success()
                ->send();

            $this->dispatch('refresh');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to remove document. Please try again.')
                ->danger()
                ->send();
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
            ->icon($this->getNotificationIcon($type, 'notification'));

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