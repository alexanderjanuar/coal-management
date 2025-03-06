<?php

namespace App\Livewire\ProjectDetail;

use Livewire\Component;
use App\Models\RequiredDocument;
use App\Models\SubmittedDocument;
use App\Models\Comment;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Asmit\FilamentMention\Forms\Components\RichMentionEditor;

class DocumentModalManager extends Component implements HasForms
{
    use InteractsWithForms;

    protected $listeners = [
        'refresh' => '$refresh',
        'documentUploaded' => 'handleDocumentUploaded',
        'openDocumentModal' => 'openDocumentModal'
    ];

    public RequiredDocument $document;
    public ?array $FormData = [];
    public ?array $data = [];
    public ?int $editingCommentId = null;

    // Document Preview Properties
    public $previewingDocument = null;
    public $previewUrl = null;
    public $isPreviewModalOpen = false;

    /**
     * Rejection Modal Properties
     */
    public $isRejectionModalOpen = false;
    public $rejectionDocument = null;

    /**
     * Current overall submission status (derived from individual documents)
     */
    public $overallStatus = 'uploaded';

    /**
     * Track document being rejected
     */
    public $documentBeingRejected = null;

    /**
     * Document ordering by priority
     */
    protected $statusOrder = [
        'pending_review' => 1,
        'uploaded' => 2,
        'approved' => 3,
        'rejected' => 4
    ];

    /**
     * For rejection form
     */
    public ?array $rejectData = [];

    /**
     * For document notes
     */
    public ?array $notesData = [];

    public function mount(): void
    {
        $this->uploadFileForm->fill();
        $this->createCommentForm->fill();
        $this->rejectionForm->fill();
        $this->documentNotesForm->fill();
    }

    public function openDocumentModal($documentId)
    {
        $this->dispatch('close-modal', id: 'database-notifications');
        $this->dispatch('open-modal', id: 'documentModal');
        $this->document = RequiredDocument::find($documentId);
        $this->uploadFileForm->fill();
        $this->createCommentForm->fill();
        $this->rejectionForm->fill();
        $this->documentNotesForm->fill();

        // Calculate overall status based on submitted documents
        if (isset($this->document)) {
            $this->calculateOverallStatus();
        }
    }

    protected function getForms(): array
    {
        return [
            'uploadFileForm',
            'createCommentForm',
            'rejectionForm',
            'documentNotesForm',
        ];
    }

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
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.oasis.opendocument.spreadsheet',
                        'text/csv',
                        'application/csv',
                        'text/x-csv'
                    ])
                    ->maxSize(10240)
                    ->preserveFilenames()
                    ->disk('public')
                    ->directory(function () {
                        if (!isset($this->document)) {
                            return "clients/temp";
                        }
                        $clientName = Str::slug($this->document->projectStep->project->client->name);
                        $projectName = Str::slug($this->document->projectStep->project->name);
                        return "clients/{$clientName}/{$projectName}";
                    })
                    ->multiple()
                    ->downloadable()
                    ->openable()
                    // ->disabled(auth()->user()->hasRole('client'))
                    ->helperText(function () {
                        if (auth()->user()->hasRole('client')) {
                            return 'You do not have permission to upload documents';
                        }
                        return 'Accepted files: PDF, Word, Excel, Images, CSV (Max size: 10MB)';
                    })
            ])
            ->statePath('FormData');
    }

    public function createCommentForm(Form $form): Form
    {
        return $form
            ->schema([
                RichMentionEditor::make('newComment')
                    ->lookupKey('name')
                    ->label('')
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
            ->statePath('data');
    }

    public function uploadDocument(): void
    {
        $data = $this->uploadFileForm->getState();

        // Check if document is an array (multiple files)
        if (is_array($data['document'])) {
            foreach ($data['document'] as $filePath) {
                SubmittedDocument::create([
                    'required_document_id' => $this->document->id,
                    'user_id' => auth()->id(),
                    'file_path' => $filePath,
                    'status' => 'uploaded', // Initial status for submitted documents
                ]);
            }
        } else {
            // Handle single file upload
            SubmittedDocument::create([
                'required_document_id' => $this->document->id,
                'user_id' => auth()->id(),
                'file_path' => $data['document'],
                'status' => 'uploaded', // Initial status for submitted documents
            ]);
        }

        // Recalculate overall status
        $this->calculateOverallStatus();

        $this->uploadFileForm->fill();

        $this->dispatch('refresh');
        $this->dispatch('documentUploaded', documentId: $this->document->id);

        // Get related project information
        $projectStep = $this->document->projectStep;
        $project = $projectStep->project;
        $client = $project->client;

        // Send notifications
        $this->sendProjectNotifications(
            "New Document" . (is_array($data['document']) ? "s" : "") . " Uploaded",
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

    public function addComment(): void
    {
        $data = $this->createCommentForm->getState();

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
            $this->processMentions($comment, $data['newComment']);
        } else {
            // Create new comment
            $comment = Comment::create([
                'user_id' => auth()->id(),
                'commentable_type' => RequiredDocument::class,
                'commentable_id' => $this->document->id,
                'content' => $data['newComment'],
                'status' => 'approved'
            ]);
            $this->processMentions($comment, $data['newComment']);
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


    }

    /**
     * Process user mentions in comment and send notifications
     * 
     * @param Comment $comment
     * @param string $content
     * @return void
     */
    protected function processMentions(Comment $comment, string $content): void
    {
        // Extract user IDs from href links in format <a href="...admin/users/{id}">@Username</a>
        preg_match_all('/<a href="[^"]*\/users\/(\d+)"[^>]*>@([^<]+)<\/a>/', $content, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            return;
        }

        $projectName = $this->document->projectStep->project->name;
        $documentName = $this->document->name;
        $clientName = $this->document->projectStep->project->client->name;

        // Process each mentioned user
        foreach ($matches as $match) {
            $userId = $match[1];
            $userName = $match[2];

            // Skip if the mentioned user is the comment author
            if ((int) $userId === auth()->id()) {
                continue;
            }

            // Find the mentioned user
            $user = \App\Models\User::find($userId);

            if (!$user) {
                continue;
            }

            // Send a mention notification to the user
            Notification::make()
                ->title('You were mentioned in a comment')
                ->body(sprintf(
                    '<strong>%s</strong> mentioned you in a comment on document <strong>%s</strong> for project <strong>%s</strong> (%s)',
                    auth()->user()->name,
                    $documentName,
                    $projectName,
                    $clientName
                ))
                ->icon('heroicon-o-at-symbol')
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->label('View Comment')
                        ->dispatch('openDocumentModal', [$this->document->id])
                        ->markAsRead(),
                ])
                ->color('warning')
                ->sendToDatabase($user)
                ->broadcast($user);
        }
    }

    public function editComment(int $commentId): void
    {
        $comment = Comment::findOrFail($commentId);

        if ($comment->user_id !== auth()->id()) {
            Notification::make()
                ->title('Unauthorized action')
                ->danger()
                ->send();
            return;
        }

        $this->editingCommentId = $commentId;
        $this->createCommentForm->fill([
            'comment' => $comment->content
        ]);

        $this->dispatch('showCommentForm');
    }

    public function deleteComment(int $commentId): void
    {
        $comment = Comment::findOrFail($commentId);

        if ($comment->user_id !== auth()->id()) {
            Notification::make()
                ->title('Unauthorized action')
                ->danger()
                ->send();
            return;
        }

        $comment->delete();
        $this->dispatch('refresh');

        Notification::make()
            ->title('Comment deleted')
            ->success()
            ->send();
    }

    public function viewDocument(SubmittedDocument $submission): void
    {
        $this->previewingDocument = $submission;
        $this->previewUrl = Storage::disk('public')->url($submission->file_path);

        // Load notes into the form
        $this->documentNotesForm->fill([
            'notes' => $submission->notes
        ]);

        // Only update status if the document is in 'uploaded' status
        if ($submission->status === 'uploaded') {
            // Change status to pending_review
            $oldStatus = $submission->status;
            $submission->status = 'pending_review';
            $submission->save();

            // Create a system comment
            Comment::create([
                'user_id' => auth()->id(),
                'commentable_type' => SubmittedDocument::class,
                'commentable_id' => $submission->id,
                'content' => sprintf(
                    "Document status automatically changed from <strong>%s</strong> to <strong>pending_review</strong> when viewed by <strong>%s</strong>",
                    $oldStatus,
                    auth()->user()->name
                ),
                'status' => 'approved'
            ]);

            // Recalculate the overall document status
            $this->calculateOverallStatus();
        }

        $this->isPreviewModalOpen = true;
    }

    protected function createStatusChangeComment(string $oldStatus, string $newStatus): void
    {
        Comment::create([
            'user_id' => auth()->id(),
            'commentable_type' => RequiredDocument::class,
            'commentable_id' => $this->document->id,
            'content' => sprintf(
                "Status changed from <strong>%s</strong> to <strong>%s</strong>",
                ucwords(str_replace('_', ' ', $oldStatus)),
                ucwords(str_replace('_', ' ', $newStatus))
            ),
            'status' => 'approved'
        ]);
    }

    public function downloadDocument($documentId)
    {
        $document = SubmittedDocument::find($documentId);
        if (!$document)
            return;

        try {
            $path = Storage::disk('public')->path($document->file_path);
            $filename = basename($document->file_path);

            return response()->stream(
                function () use ($path) {
                    $stream = fopen($path, 'rb');
                    while (!feof($stream)) {
                        echo fread($stream, 1024 * 8); // Read in chunks
                        flush();
                    }
                    fclose($stream);
                },
                200,
                [
                    'Content-Type' => Storage::disk('public')->mimeType($document->file_path),
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]
            );
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error downloading file')
                ->body('Please try again later.')
                ->danger()
                ->send();
        }
    }

    public function removeDocument(int $documentId): void
    {
        try {
            $submission = SubmittedDocument::findOrFail($documentId);
            Storage::disk('public')->delete($submission->file_path);
            $submission->delete();

            Notification::make()
                ->title('Document Removed')
                ->success()
                ->send();



        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to remove document. Please try again.')
                ->danger()
                ->send();
        }
    }

    public function updateStatus(string $status): void
    {
        $oldStatus = $this->document->status;
        $this->document->status = $status;
        $this->document->save();

        // Create a system comment for the status change
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
    }

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
                    ->dispatch('openDocumentModal', [$this->document->id]),
                // ->url(route('filament.admin.resources.projects.view', [
                //     'record' => $this->document->projectStep->project->id,
                //     'openDocument' => $this->document->id
                // ])),

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

    protected function getFileType(): ?string
    {
        if (!$this->previewingDocument) {
            return null;
        }
        return strtolower(pathinfo($this->previewingDocument->file_path, PATHINFO_EXTENSION));
    }

    public function handleDocumentUploaded(int $documentId): void
    {
        // Refresh the document if we have it loaded
        if (isset($this->document) && $this->document->id == $documentId) {
            $this->document->refresh();
        }
    }

    /**
     * Calculate the overall status based on individual document statuses
     */
    public function calculateOverallStatus(): void
    {
        $submissions = $this->document->submittedDocuments;

        if ($submissions->count() === 0) {
            $this->overallStatus = 'uploaded';
            $this->document->status = 'uploaded';
            $this->document->save();
            return;
        }

        // Count documents by status
        $approvedCount = $submissions->where('status', 'approved')->count();
        $pendingReviewCount = $submissions->where('status', 'pending_review')->count();
        $uploadedCount = $submissions->where('status', 'uploaded')->count();
        $totalCount = $submissions->count();

        if ($approvedCount === $totalCount) {
            // All documents are approved
            $status = 'approved';
        } elseif ($pendingReviewCount > 0 || $approvedCount > 0) {
            // At least one document is pending review or some (but not all) are approved
            $status = 'pending_review';
        } elseif ($uploadedCount > 0) {
            // At least one document is uploaded and none are pending or approved
            $status = 'uploaded';
        } else {
            // All remaining documents must be rejected
            $status = 'rejected';
        }

        // Only update if the status changed
        if ($this->document->status !== $status) {
            $this->document->status = $status;
            $this->document->save();
        }

        $this->overallStatus = $status;
    }

    public function rejectionForm(Form $form): Form
    {
        return $form
            ->schema([
                RichEditor::make('rejectionReason')
                    ->label('Reason for Rejection')
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'orderedList',
                    ])
                    ->placeholder('Please provide details about why this document is being rejected...')
                    ->required()
            ])
            ->statePath('rejectData');
    }

    /**
     * Document Notes Form
     */
    public function documentNotesForm(Form $form): Form
    {
        return $form
            ->schema([
                RichEditor::make('notes')
                    ->label('Document Notes')
                    ->placeholder('Add notes about this document...')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'bulletList',
                        'orderedList',
                    ])
                    ->extraInputAttributes(['style' => 'font-size:14px'])
            ])
            ->statePath('notesData');
    }

    /**
     * Update document notes
     */
    public function saveDocumentNotes(): void
    {
        if (!$this->previewingDocument) {
            return;
        }

        try {
            $data = $this->documentNotesForm->getState();

            // Update the notes directly on the document
            $this->previewingDocument->update([
                'notes' => $data['notes']
            ]);

            Notification::make()
                ->title('Notes Saved')
                ->success()
                ->send();

            // Send notification to project team
            $this->sendProjectNotifications(
                "Document Notes Updated",
                sprintf(
                    "<span style='color: #f59e0b; font-weight: 500;'>%s</span><br><strong>Document:</strong> %s<br><strong>File:</strong> %s<br><strong>Updated by:</strong> %s",
                    $this->document->projectStep->project->client->name,
                    $this->document->name,
                    basename($this->previewingDocument->file_path),
                    auth()->user()->name
                ),
                'info',
                'View Document',
                'document_note'
            );

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to save notes. Please try again.')
                ->danger()
                ->send();
        }
    }

    /**
     * Download all documents as a ZIP archive
     */
    public function downloadAllDocuments()
    {
        try {
            $documents = $this->document->submittedDocuments;

            if ($documents->isEmpty()) {
                Notification::make()
                    ->title('No Documents')
                    ->body('There are no documents available to download.')
                    ->warning()
                    ->send();
                return;
            }

            // Create temporary directory if it doesn't exist
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Get project and client details for filename
            $client = Str::slug($this->document->projectStep->project->client->name);
            $project = Str::slug($this->document->projectStep->project->name);
            $docName = Str::slug($this->document->name);

            // Create ZIP filename with better structure
            $zipFileName = sprintf(
                '%s_%s_%s_%s.zip',
                $client,
                $project,
                $docName,
                now()->format('Y-m-d_His')
            );

            $zipPath = $tempDir . '/' . $zipFileName;

            // Create new ZIP archive
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Cannot create zip file');
            }

            // Add files to ZIP
            foreach ($documents as $document) {
                $filePath = storage_path('app/public/' . $document->file_path);
                if (file_exists($filePath)) {
                    // Use original filename for better readability
                    $originalName = basename($document->file_path);
                    $zip->addFile($filePath, $originalName);
                }
            }

            $zip->close();

            // Return the ZIP file for download and delete it afterward
            return response()->download($zipPath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Notification::make()
                ->title('Download Failed')
                ->body('Failed to create download archive. Please try again.')
                ->danger()
                ->send();

            report($e); // Log the error for debugging
        }
    }

    /**
     * Get the last status change activity for this document
     *
     * @return \Spatie\Activitylog\Models\Activity|null
     */
    public function getLastStatusChangeActivity()
    {
        // Query for activities related to document status changes
        return \Spatie\Activitylog\Models\Activity::where(function ($query) {
            // Check for activities directly on the required document
            $query->where('subject_type', RequiredDocument::class)
                ->where('subject_id', $this->document->id)
                ->whereIn('description', ['approved', 'pending_review', 'uploaded', 'rejected', 'updated']);
        })
            ->orWhere(function ($query) {
                // Check for activities on submitted documents related to this required document
                $query->where('subject_type', SubmittedDocument::class)
                    ->whereIn('subject_id', $this->document->submittedDocuments->pluck('id'))
                    ->whereIn('description', ['approved', 'pending_review', 'uploaded', 'rejected', 'updated']);
            })
            ->where(function ($query) {
                // Make sure we only get activities that involve status changes
                $query->whereJsonContains('properties->attributes->status', 'approved')
                    ->orWhereJsonContains('properties->attributes->status', 'pending_review')
                    ->orWhereJsonContains('properties->attributes->status', 'uploaded')
                    ->orWhereJsonContains('properties->attributes->status', 'rejected');
            })
            ->with('causer')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get status information for the view
     * 
     * @param string $status
     * @return array
     */
    public function getStatusInfo(string $status = '')
    {
        return [
            'title' => match ($status) {
                'approved' => 'Approved Document',
                'pending_review' => 'Pending Review',
                'uploaded' => 'Document Uploaded',
                'rejected' => 'Document Rejected',
                default => 'Status Updated'
            },
            'color' => match ($status) {
                'approved' => 'bg-green-50 dark:bg-green-900/20',
                'pending_review' => 'bg-amber-50 dark:bg-amber-900/20',
                'uploaded' => 'bg-blue-50 dark:bg-blue-900/20',
                'rejected' => 'bg-red-50 dark:bg-red-900/20',
                default => 'bg-gray-50 dark:bg-gray-800/50'
            },
            'textColor' => match ($status) {
                'approved' => 'text-green-600 dark:text-green-400',
                'pending_review' => 'text-amber-600 dark:text-amber-400',
                'uploaded' => 'text-blue-600 dark:text-blue-400',
                'rejected' => 'text-red-600 dark:text-red-400',
                default => 'text-gray-600 dark:text-gray-400'
            },
            'icon' => match ($status) {
                'approved' => 'heroicon-m-check-badge',
                'pending_review' => 'heroicon-m-clock',
                'uploaded' => 'heroicon-m-arrow-up-tray',
                'rejected' => 'heroicon-m-x-circle',
                default => 'heroicon-m-document'
            }
        ];
    }

    /**
     * Status Management Methods
     */
    public function updateDocumentStatus(SubmittedDocument $submission, string $status): void
    {
        try {
            // If rejecting, we'll open the rejection modal instead
            if ($status === 'rejected') {
                $this->openRejectionModal($submission);
                return;
            }

            $oldStatus = $submission->status;
            $submission->status = $status;
            $submission->save();

            // Create a comment on the submitted document record
            Comment::create([
                'user_id' => auth()->id(),
                'commentable_type' => SubmittedDocument::class,
                'commentable_id' => $submission->id,
                'content' => sprintf(
                    "Status changed from <strong class='text-gray-700'>%s</strong> to <strong class='text-gray-700'>%s</strong> by <strong>%s</strong>",
                    $this->getStatusLabel($oldStatus),
                    $this->getStatusLabel($status),
                    auth()->user()->name
                ),
                'status' => 'approved'
            ]);

            // Recalculate overall document status
            $this->calculateOverallStatus();

            $this->dispatch('refresh');

            // Get related project information
            $projectStep = $this->document->projectStep;
            $project = $projectStep->project;
            $client = $project->client;

            // Determine notification action based on status
            $notificationAction = match ($status) {
                'approved' => 'approval',
                'pending_review' => 'pending_review',
                default => 'status_change'
            };

            // Send notifications with HTML formatting
            $this->sendProjectNotifications(
                "Document Status Updated",
                sprintf(
                    "<span style='color: #f59e0b; font-weight: 500;'>%s</span><br><strong>Document:</strong> %s<br><strong>File:</strong> %s<br><strong>Status:</strong> %s → %s<br><strong>Updated by:</strong> %s",
                    $client->name,
                    $this->document->name,
                    basename($submission->file_path),
                    $this->getStatusLabel($oldStatus),
                    $this->getStatusLabel($status),
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
     * Set document to pending review
     */
    public function setToPendingReview(SubmittedDocument $submission): void
    {
        $this->updateDocumentStatus($submission, 'pending_review');
    }

    /**
     * Trigger the rejection modal for a document
     */
    public function openRejectionModal(SubmittedDocument $document): void
    {
        $this->documentBeingRejected = $document;

        // Reset rejection form
        $this->rejectionForm->fill();

        // Emit event to open the modal
        $this->dispatch('openRejectionModal', $document->id);
    }

    /**
     * Handle document rejection with reason
     */
    public function submitRejection(): void
    {
        if (!$this->documentBeingRejected) {
            return;
        }

        // Validate rejection reason
        $this->validate([
            'rejectData.rejectionReason' => 'required|min:10'
        ]);

        try {
            // Store file path in a variable before resetting documentBeingRejected
            $rejectedFilePath = $this->documentBeingRejected->file_path;

            // Update document status
            $oldStatus = $this->documentBeingRejected->status;
            $this->documentBeingRejected->status = 'rejected';
            $this->documentBeingRejected->rejection_reason = $this->rejectData['rejectionReason'];
            $this->documentBeingRejected->save();

            // Create a comment
            Comment::create([
                'user_id' => auth()->id(),
                'commentable_type' => SubmittedDocument::class,
                'commentable_id' => $this->documentBeingRejected->id,
                'content' => sprintf(
                    "<div class='p-3 bg-red-50 dark:bg-red-900/30 rounded-lg border border-red-100 dark:border-red-800'><p class='text-red-800 dark:text-red-300 font-medium'>Document Rejected</p><div class='mt-2 text-red-700 dark:text-red-400'>%s</div></div>",
                    $this->rejectData['rejectionReason']
                ),
                'status' => 'approved'
            ]);

            // Reset form and modal
            $this->rejectionForm->fill();

            // Store the document in a temporary variable before clearing it
            $rejectedDocument = $this->documentBeingRejected;
            $this->documentBeingRejected = null;

            // Recalculate overall status
            $this->calculateOverallStatus();

            $this->dispatch('refresh');
            $this->dispatch('close-modal', ['id' => 'rejection-reason-modal']);

            // Send notification - now using the stored variables
            $projectStep = $this->document->projectStep;
            $project = $projectStep->project;
            $client = $project->client;

            $this->sendProjectNotifications(
                "Document Rejected",
                sprintf(
                    "<span style='color: #f59e0b; font-weight: 500;'>%s</span><br><strong>Document:</strong> %s<br><strong>File:</strong> %s<br><strong>Rejected by:</strong> %s",
                    $client->name,
                    $this->document->name,
                    basename($rejectedFilePath), // Using stored file path
                    auth()->user()->name
                ),
                'danger',
                'View Details',
                'rejection'
            );

            // Show success notification
            Notification::make()
                ->title('Document Rejected')
                ->body('The document has been rejected with the provided reason.')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->send();
        } catch (\Exception $e) {
            // Error handling
            Notification::make()
                ->title('Error')
                ->body('Failed to reject document. Please try again.')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->send();

            report($e); // Log the error
        }
    }

    /**
     * Get human-readable status label
     */
    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'uploaded' => 'Uploaded',
            'pending_review' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucwords(str_replace('_', ' ', $status))
        };
    }

    /**
     * Get status icon for UI elements
     */
    public function getStatusIcon(string $status): string
    {
        return match ($status) {
            'uploaded' => 'heroicon-m-arrow-up-tray',
            'pending_review' => 'heroicon-m-clock',
            'approved' => 'heroicon-m-check-circle',
            'rejected' => 'heroicon-m-x-circle',
            default => 'heroicon-m-question-mark-circle'
        };
    }

    /**
     * Get the ordered list of documents
     */
    protected function getOrderedDocuments()
    {
        return $this->document->submittedDocuments
            ->sortBy(function ($doc) {
                return $this->statusOrder[$doc->status] ?? 999;
            })
            ->values(); // Convert to indexed array for proper navigation
    }

    /**
     * Get next document based on status order with circular navigation
     */
    public function nextDocument(): void
    {
        if (!$this->previewingDocument) {
            return;
        }

        $documents = $this->getOrderedDocuments();
        $currentIndex = $documents->search(function ($item) {
            return $item->id === $this->previewingDocument->id;
        });

        // Next index with circular navigation
        $nextIndex = ($currentIndex + 1) % $documents->count();
        $this->viewDocument($documents[$nextIndex]);
    }

    /**
     * Get previous document based on status order with circular navigation
     */
    public function previousDocument(): void
    {
        if (!$this->previewingDocument) {
            return;
        }

        $documents = $this->getOrderedDocuments();
        $currentIndex = $documents->search(function ($item) {
            return $item->id === $this->previewingDocument->id;
        });

        // Previous index with circular navigation
        $prevIndex = ($currentIndex - 1 + $documents->count()) % $documents->count();
        $this->viewDocument($documents[$prevIndex]);
    }

    // These methods are no longer needed since we always show navigation
    public function hasNextDocument(): bool
    {
        return true;
    }

    public function hasPreviousDocument(): bool
    {
        return true;
    }

    /**
     * Get current document position and total
     */
    public function getDocumentPosition(): array
    {
        if (!$this->previewingDocument) {
            return [
                'current' => 0,
                'total' => 0
            ];
        }

        $documents = $this->getOrderedDocuments();
        $position = $documents->search(function ($item) {
            return $item->id === $this->previewingDocument->id;
        });

        return [
            'current' => $position + 1,
            'total' => $documents->count()
        ];
    }

    /**
     * Simple notification helper
     */
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

    /**
     * Close the document preview modal
     */
    public function closePreview(): void
    {
        $this->isPreviewModalOpen = false;
        $this->previewUrl = null;
        $this->previewingDocument = null;
    }

    public function render()
    {
        return view('livewire.project-detail.document-modal-manager', [
            'fileType' => $this->getFileType()
        ]);
    }
}