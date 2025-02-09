<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Client;
use Livewire\WithFileUploads;
use App\Models\ClientDocument;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class DocumentClientModal extends Component implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    public $isOpen = false;
    public ?array $data = [];
    public $client;
    public $search = '';
    public $client_documents;
    public $showPreview = false;
    public $previewUrl = '';

    public function mount(Client $client): void
    {
        $this->client = $client;
        $this->loadDocuments();
        $this->form->fill();
    }

    public function loadDocuments(): void
    {
        $this->client_documents = ClientDocument::with('user')
            ->where('client_id', $this->client->id)
            ->latest()
            ->get();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Upload Document')
                    ->schema([
                        FileUpload::make('document')
                            ->required()
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'image/jpeg',
                                'image/png',
                                'image/jpg',
                                'image/gif'
                            ])
                            ->maxSize(10240)
                            ->disk('public')
                            ->directory('client-documents')
                            ->preserveFilenames()
                            ->downloadable()
                            ->previewable()
                    ])
            ])
            ->statePath('data');
    }

    public function toggleModal(): void
    {
        $this->isOpen = !$this->isOpen;
        if (!$this->isOpen) {
            $this->form->fill();
        }
        $this->dispatch($this->isOpen ? 'modal-opened' : 'modal-closed');
    }

    public function previewDocument($path): void
    {
        $this->previewUrl = Storage::disk('public')->url($path);
        $this->showPreview = true;
    }

    public function closePreview(): void
    {
        $this->showPreview = false;
        $this->previewUrl = '';
    }

    public function downloadDocument(ClientDocument $document): void
    {
        try {
            response()->download(Storage::disk('public')->path($document->file_path));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error downloading document')
                ->danger()
                ->send();
        }
    }

    public function deleteDocument(ClientDocument $document): void
    {
        try {
            Storage::disk('public')->delete($document->file_path);
            $document->delete();
            $this->loadDocuments();

            Notification::make()
                ->title('Document deleted successfully')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error deleting document')
                ->danger()
                ->send();
        }
    }

    public function uploadDocument(): void
    {
        try {
            $data = $this->form->getState();

            if (!isset($data['document'])) {
                Notification::make()
                    ->title('No document selected')
                    ->warning()
                    ->send();
                return;
            }

            ClientDocument::create([
                'client_id' => $this->client->id,
                'user_id' => auth()->id(),
                'file_path' => $data['document'],
            ]);

            // Reset the form but don't close modal
            $this->form->fill();

            // Refresh documents
            $this->loadDocuments();

            Notification::make()
                ->title('Document uploaded successfully')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error uploading document')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.dashboard.document-client-modal');
    }
}