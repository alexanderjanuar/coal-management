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

    protected function getFileType(): ?string
    {
        if (!$this->previewingDocument) {
            return null;
        }
        return strtolower(pathinfo($this->previewingDocument->file_path, PATHINFO_EXTENSION));
    }

    public function render()
    {
        return view('livewire.project-detail.document-modal-manager',[
            'fileType' => $this->getFileType()
        ]);
    }
}