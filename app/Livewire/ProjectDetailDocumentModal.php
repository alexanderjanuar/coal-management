<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\RequiredDocument;
use App\Models\SubmittedDocument;
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
                        // Get client name and project name
                        $clientName = Str::slug($this->document->projectStep->project->client->name);
                        $projectName = Str::slug($this->document->projectStep->project->name);
                        
                        // Create the directory path
                        return "clients/{$clientName}/{$projectName}";
                    })
                    ->downloadable()
                    ->openable()
            ])
            ->statePath('data');
    }

    /**
     * Document Management Methods
     */
    public function uploadDocument(): void
    {
        try {
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

            $this->sendNotification('success', 'Document uploaded successfully');
        } catch (\Exception $e) {
            $this->sendNotification('error', 'Error uploading document', 'Please try again or contact support.');
        }
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

            $this->sendNotification(
                'success',
                "Document status updated to " . str_replace('_', ' ', ucfirst($status))
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
            Comment::create([
                'user_id' => auth()->id(),
                'commentable_type' => RequiredDocument::class,
                'commentable_id' => $this->document->id,
                'content' => $this->newComment,
                'status' => 'approved'
            ]);

            $this->newComment = '';
            $this->dispatch('refresh');

            $this->sendNotification('success', 'Comment added successfully');
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
                "Status changed from %s to %s",
                ucwords(str_replace('_', ' ', $oldStatus)),
                ucwords(str_replace('_', ' ', $newStatus))
            ),
            'status' => 'approved'
        ]);
    }

    protected function sendNotification(string $type, string $title, ?string $body = null): void
    {
        $notification = Notification::make()
            ->title($title);

        if ($body) {
            $notification->body($body);
        }

        $notification->{$type}()->send();
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
        return view('livewire.project-detail-document-modal', [
            'comments' => $this->document->comments()
                ->with('user')
                ->latest()
                ->get(),
            'fileType' => $this->getFileType()
        ]);
    }
}