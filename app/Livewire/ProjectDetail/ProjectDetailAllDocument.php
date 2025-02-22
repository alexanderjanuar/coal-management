<?php

namespace App\Livewire\ProjectDetail;

use Livewire\Component;
use App\Models\Project;
use App\Models\SubmittedDocument;
use App\Models\Client;
use App\Models\ClientDocument;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class ProjectDetailAllDocument extends Component
{
    use WithPagination;
    use WithFileUploads;

    public Client $client;
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $filters = [
        'dateRange' => '',
        'uploadedBy' => ''
    ];
    
    // Document preview properties
    public $previewingDocument = null;
    public $previewUrl = null;
    public $fileType = null;
    
    // Upload properties
    public $documentToUpload;
    public $documentName;
    
    protected $listeners = [
        'refresh' => '$refresh',
        'documentUploaded' => 'handleDocumentUploaded',
    ];

    public function mount(Client $client)
    {
        $this->client = $client;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function viewDocument(ClientDocument $document)
    {
        $this->previewingDocument = $document;
        $this->previewUrl = Storage::disk('public')->url($document->file_path);
        $this->fileType = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
    }

    public function closePreview()
    {
        $this->previewingDocument = null;
        $this->previewUrl = null;
        $this->fileType = null;
    }

    public function downloadDocument($documentId)
    {
        $document = ClientDocument::find($documentId);
        if ($document) {
            return Storage::disk('public')->download($document->file_path);
        }
    }

    public function uploadDocument()
    {
        $this->validate([
            'documentToUpload' => 'required|file|max:10240', // 10MB max
            'documentName' => 'required|string|max:255'
        ]);

        $path = $this->documentToUpload->store('client-documents', 'public');

        $document = new ClientDocument([
            'file_path' => $path,
            'user_id' => auth()->id()
        ]);

        $this->client->documents()->save($document);

        $this->reset(['documentToUpload', 'documentName']);
        $this->dispatch('document-uploaded');
    }

    public function deleteDocument(ClientDocument $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();
        
        $this->dispatch('refresh');
    }

    public function render()
    {
        $documents = $this->client->clientDocuments()
            ->when($this->search, function ($query) {
                $query->where('file_path', 'like', '%' . $this->search . '%');
            })
            ->when($this->filters['dateRange'], function ($query) {
                // Add date range filtering logic
            })
            ->when($this->filters['uploadedBy'], function ($query) {
                $query->where('user_id', $this->filters['uploadedBy']);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.project-detail.project-detail-all-document', [
            'documents' => $documents
        ]);
    }
}