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