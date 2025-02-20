<?php

namespace App\Livewire\ProjectDetail;

use Livewire\Component;
use App\Models\RequiredDocument;
use App\Models\SubmittedDocument;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    // Document Preview Properties
    public $previewingDocument = null;
    public $previewUrl = null;
    public $isPreviewModalOpen = false;

    public function mount(): void
    {
        $this->uploadFileForm->fill();
    }

    public function openDocumentModal($documentId)
    {
        $this->dispatch('close-modal', id: 'database-notifications');
        $this->dispatch('open-modal', id: 'documentModal');
        $this->document = RequiredDocument::find($documentId);
    }

    protected function getForms(): array
    {
        return [
            'uploadFileForm',
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
                        $clientName = Str::slug($this->document->projectStep->project->client->name);
                        $projectName = Str::slug($this->document->projectStep->project->name);
                        return "clients/{$clientName}/{$projectName}";
                    })
                    ->downloadable()
                    ->openable()
                    ->helperText(function () {
                        if (auth()->user()->hasRole('client')) {
                            return 'You do not have permission to upload documents';
                        }
                        return 'Accepted files: PDF, Word, Excel, Images, CSV (Max size: 10MB)';
                    })
            ])
            ->statePath('FormData');
    }

    public function uploadDocument(): void
    {
        $data = $this->uploadFileForm->getState();

        $submission = SubmittedDocument::create([
            'required_document_id' => $this->document->id,
            'user_id' => auth()->id(),
            'file_path' => $data['document'],
        ]);

        $this->document->status = 'uploaded';
        $this->document->save();

        $this->uploadFileForm->fill();
        $this->dispatch('refresh');
        $this->dispatch('documentUploaded', documentId: $submission->id);

        Notification::make()
            ->title('Document uploaded successfully')
            ->success()
            ->send();
    }

    public function viewDocument(SubmittedDocument $submission): void
    {
        try {
            // Instead of loading the whole file into memory
            $this->previewingDocument = $submission;
            $this->previewUrl = Storage::disk('public')->url($submission->file_path);
            $this->isPreviewModalOpen = true;

            // Check file size before processing
            $fileSize = Storage::disk('public')->size($submission->file_path);
            if ($fileSize > 50 * 1024 * 1024) { // 50MB limit
                Notification::make()
                    ->title('File too large for preview')
                    ->body('Please download the file to view it.')
                    ->warning()
                    ->send();
                return;
            }

            if (
                auth()->user()->hasRole(['direktur', 'project-manager']) &&
                $this->document->status === 'uploaded'
            ) {
                $this->document->update([
                    'status' => 'pending_review',
                    'reviewer_id' => auth()->id(),
                    'reviewed_at' => now()
                ]);
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error previewing document')
                ->body('Please try downloading the file instead.')
                ->danger()
                ->send();

            $this->previewingDocument = null;
            $this->previewUrl = null;
            $this->isPreviewModalOpen = false;
        }
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

            $this->dispatch('refresh');
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

    public function render()
    {
        return view('livewire.project-detail.document-modal-manager', [
            'fileType' => $this->getFileType()
        ]);
    }
}