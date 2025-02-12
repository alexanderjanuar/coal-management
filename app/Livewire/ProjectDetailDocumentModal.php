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
                    ->url(route('filament.admin.resources.projects.view', [
                        'record' => $this->document->projectStep->project->id,
                        'openDocument' => $this->document->id
                    ]))
                    ->markAsRead(),

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

        $this->sendProjectNotifications(
            "Document Viewed",
            sprintf(
                "<span style='color: #f59e0b; font-weight: 500;'>%s</span>'s document '%s' viewed by %s",
                $this->document->projectStep->project->client->name,
                $this->document->name,
                auth()->user()->name
            ),
            'info',
            null,
            'document_preview'
        );
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
            $this->sendProjectNotifications(
                "Document Downloaded",
                "Document {$this->document->name} was downloaded by " . auth()->user()->name,
                'info',
                null,
                'document_download'
            );
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
                "New Comment",
                sprintf(
                    "<span style='color: #f59e0b; font-weight: 500;'>%s</span><br><strong>Document:</strong> %s<br><strong>Comment:</strong> %s<br><strong>By:</strong> %s",
                    $client->name,
                    $this->document->name,
                    $truncatedContent,
                    auth()->user()->name
                ),
                'info',
                'View Comment',
                'comment'
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