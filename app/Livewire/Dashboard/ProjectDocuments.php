<?php

namespace App\Livewire\Dashboard;

use App\Models\ProjectStep;
use Livewire\Component;
use App\Models\SubmittedDocument;
use Illuminate\Support\Facades\Storage;

class ProjectDocuments extends Component
{
    public $step;
    public $previewingDocument = null;
    public $previewUrl = null;
    public $fileType = null;
    public $currentIndex = 0;
    public $totalDocuments = 0;

    public function mount(ProjectStep $step)
    {
        $this->step = $step;
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

    protected function updatePreviewDocument(SubmittedDocument $document)
    {
        $this->previewingDocument = $document;
        $this->previewUrl = Storage::disk('public')->url($document->file_path);
        $this->fileType = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
    }

    public function downloadDocument($documentId)
    {
        $document = SubmittedDocument::find($documentId);
        if ($document) {
            return Storage::disk('public')->download($document->file_path);
        }
    }

    public function render()
    {
        return view('livewire.dashboard.project-documents');
    }
}