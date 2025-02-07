<?php

namespace App\Livewire\ProjectDetail;

use Livewire\Component;
use App\Models\Project;
use App\Models\SubmittedDocument;

use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class ProjectDetailAllDocument extends Component
{
    use WithPagination;

    public Project $project;
    public $search = '';

    public $previewingDocument = null;
    public $previewUrl = null;
    public $isPreviewModalOpen = false;
    public $fileType = null;
    public $currentIndex = 0;
    public $totalDocuments = 0;

    /**
     * Listeners
     */
    protected $listeners = [
        'refresh' => '$refresh',
        'documentUploaded' => 'handleDocumentUploaded',
    ];


    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function mount(Project $project)
    {
        $this->project = $project;
    }

    public function viewDocument(SubmittedDocument $submission): void
    {
        $requiredDocument = $submission->requiredDocument;
        $allSubmissions = $requiredDocument->submittedDocuments;
        
        $this->totalDocuments = $allSubmissions->count();
        $this->currentIndex = $allSubmissions->search(function($doc) use ($submission) {
            return $doc->id === $submission->id;
        });
        
        $this->updatePreviewDocument($submission);
    }

    protected function updatePreviewDocument(SubmittedDocument $document)
    {
        $this->previewingDocument = $document;
        $this->previewUrl = Storage::disk('public')->url($document->file_path);
        $this->fileType = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
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

    public function nextDocument()
    {
        $requiredDocument = $this->previewingDocument->requiredDocument;
        $allSubmissions = $requiredDocument->submittedDocuments;
        
        $this->currentIndex = ($this->currentIndex + 1) % $this->totalDocuments;
        $nextDoc = $allSubmissions[$this->currentIndex];
        
        $this->updatePreviewDocument($nextDoc);
    }

    public function previousDocument()
    {
        $requiredDocument = $this->previewingDocument->requiredDocument;
        $allSubmissions = $requiredDocument->submittedDocuments;
        
        $this->currentIndex = ($this->currentIndex - 1 + $this->totalDocuments) % $this->totalDocuments;
        $prevDoc = $allSubmissions[$this->currentIndex];
        
        $this->updatePreviewDocument($prevDoc);
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

            // Send notifications
            $this->sendProjectNotifications(
                "Document Status Updated",
                sprintf(
                    "Document '%s' status changed from %s to %s by %s",
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

    public function render()
    {
        $steps = $this->project->steps()
            ->with([
                'requiredDocuments' => function ($query) {
                    $query->when($this->search, function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    })
                        ->with('submittedDocuments');
                }
            ])
            ->get();

        return view('livewire.project-detail.project-detail-all-document', [
            'steps' => $steps
        ]);
    }
}