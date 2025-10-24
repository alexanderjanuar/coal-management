<?php

namespace App\Livewire\Client\Components;

use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\SopLegalDocument;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;

class DokumenTab extends Component implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    public Client $client;
    public $checklist = [];
    public $additionalDocuments = [];
    public $stats = [];
    
    // Modal state
    public $selectedSopId = null;
    public $uploadFile = [];
    public $documentNumber = '';
    public $documentName = '';
    public $expiredAt = '';
    public $isAdditionalDocument = false;
    public $documentToDelete = null;
    
    // Preview state
    public $previewDocument = null;

    public function mount(Client $client)
    {
        $this->client = $client;
        $this->loadData();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('selectedSopId')
                                    ->label('Jenis Dokumen')
                                    ->options(function () {
                                        if ($this->isAdditionalDocument) {
                                            return [];
                                        }
                                        return $this->client->getApplicableSopDocuments()
                                                   ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->required()
                                    ->visible(!$this->isAdditionalDocument)
                                    ->columnSpan(2),
                                    
                                TextInput::make('documentName')
                                    ->label('Nama Dokumen')
                                    ->placeholder('Masukkan nama dokumen')
                                    ->required()
                                    ->visible($this->isAdditionalDocument)
                                    ->columnSpan(2),
                                    
                                TextInput::make('documentNumber')
                                    ->label('Nomor Dokumen')
                                    ->placeholder('Masukkan nomor dokumen (opsional)')
                                    ->columnSpan(1),
                                    
                                DatePicker::make('expiredAt')
                                    ->label('Tanggal Kadaluarsa')
                                    ->placeholder('Pilih tanggal kadaluarsa (opsional)')
                                    ->after('today')
                                    ->columnSpan(1),
                            ]),
                            
                        FileUpload::make('uploadFile')
                            ->label('File Dokumen')
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240) // 10MB
                            ->required()
                            ->helperText('Format: PDF, JPG, JPEG, PNG, DOC, DOCX (Maksimal 10MB)')
                            ->disk('public')
                            ->directory(function () {
                                return $this->isAdditionalDocument 
                                    ? $this->client->getFolderPath() 
                                    : $this->client->getLegalFolderPath();
                            })
                            ->visibility('private')
                            ->uploadingMessage('Sedang mengupload...')
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public function loadData()
    {
        $this->checklist = $this->client->getLegalDocumentsChecklist();
        $this->loadAdditionalDocuments();
        $this->calculateStats();
    }

    public function loadAdditionalDocuments()
    {
        $this->additionalDocuments = $this->client->clientDocuments()
            ->whereNull('sop_legal_document_id')
            ->with('user')
            ->latest()
            ->get();
    }

    public function calculateStats()
    {
        $this->stats = $this->client->getLegalDocumentsStats();
    }

    public function openUploadModal($sopId = null, $isAdditional = false)
    {
        $this->selectedSopId = $sopId;
        $this->isAdditionalDocument = $isAdditional;
        $this->resetModalFields();
        
        $this->dispatch('open-modal', id : 'upload-document-modal');
    }

    public function closeUploadModal()
    {
        $this->resetModalFields();
        $this->dispatch('close-modal', ['id' => 'upload-document-modal']);
    }

    private function resetModalFields()
    {
        $this->uploadFile = [];
        $this->documentNumber = '';
        $this->documentName = '';
        $this->expiredAt = '';
        
        if (!$this->selectedSopId) {
            $this->selectedSopId = null;
        }
    }

    public function uploadDocument()
    {
        $data = $this->form->getState();
        
        try {
            // Handle file upload
            if (!empty($data['uploadFile'])) {
                $uploadedFile = is_array($data['uploadFile']) ? $data['uploadFile'][0] : $data['uploadFile'];
                
                // Get original filename
                if (is_object($uploadedFile) && method_exists($uploadedFile, 'getClientOriginalName')) {
                    $originalName = $uploadedFile->getClientOriginalName();
                } else {
                    // If it's already a stored path (string), get the filename
                    $originalName = basename($uploadedFile);
                }
                
                $filename = time() . '_' . $originalName;
                
                // Determine storage path
                $storagePath = $this->isAdditionalDocument 
                    ? $this->client->getFolderPath() 
                    : $this->client->getLegalFolderPath();
                
                // Store file
                if (is_object($uploadedFile) && method_exists($uploadedFile, 'storeAs')) {
                    $filePath = $uploadedFile->storeAs($storagePath, $filename, 'public');
                } else {
                    // File already stored, just use the path
                    $filePath = $uploadedFile;
                }

                // Create document record
                $documentData = [
                    'client_id' => $this->client->id,
                    'user_id' => auth()->id(),
                    'file_path' => $filePath,
                    'original_filename' => $originalName,
                    'document_number' => $data['documentNumber'] ?? null,
                    'expired_at' => $data['expiredAt'] ?? null,
                    'status' => 'valid',
                ];

                if (!$this->isAdditionalDocument) {
                    $documentData['sop_legal_document_id'] = $data['selectedSopId'];
                } else {
                    $documentData['document_category'] = 'additional';
                    $documentData['description'] = $data['documentName'] ?? null;
                }

                ClientDocument::create($documentData);

                $this->closeUploadModal();
                $this->loadData();
                
                Notification::make()
                    ->title('Berhasil!')
                    ->body('Dokumen berhasil diupload!')
                    ->success()
                    ->duration(3000)
                    ->send();
            } else {
                Notification::make()
                    ->title('Error!')
                    ->body('Tidak ada file yang diupload')
                    ->warning()
                    ->duration(3000)
                    ->send();
            }

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error!')
                ->body('Gagal mengupload dokumen: ' . $e->getMessage())
                ->danger()
                ->duration(5000)
                ->send();
        }
    }

    public function downloadDocument($documentId)
    {
        $document = ClientDocument::find($documentId);
        if ($document && $document->file_path) {
            return response()->download(storage_path('app/public/' . $document->file_path));
        }
        
        Notification::make()
            ->title('File tidak ditemukan')
            ->body('File dokumen tidak dapat ditemukan atau sudah dihapus.')
            ->warning()
            ->send();
    }

    public function deleteDocument()
    {
        try {
            if (!$this->documentToDelete) {
                return;
            }
            
            $document = ClientDocument::find($this->documentToDelete);
            if ($document) {
                if (\Storage::disk('public')->exists($document->file_path)) {
                    \Storage::disk('public')->delete($document->file_path);
                }
                
                $document->delete();
                $this->loadData();
                
                Notification::make()
                    ->title('Berhasil!')
                    ->body('Dokumen berhasil dihapus!')
                    ->success()
                    ->duration(3000)
                    ->send();
            }
            
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error!')
                ->body('Gagal menghapus dokumen: ' . $e->getMessage())
                ->danger()
                ->duration(5000)
                ->send();
        }
    }

    public function deleteDocumentConfirm($documentId)
    {
        $this->documentToDelete = $documentId;
        
        $this->dispatch('open-modal', id: 'confirm-delete-modal');
    }

    public function closeDeleteModal()
    {
        $this->documentToDelete = null;
        $this->dispatch('close-modal', id : 'confirm-delete-modal');
    }

    public function previewDocuments($documentId)
    {
        $this->previewDocument = ClientDocument::with('user')->find($documentId);
        if ($this->previewDocument) {
            $this->dispatch('open-modal', id : 'preview-document-modal');
        } else {
            Notification::make()
                ->title('Error!')
                ->body('Dokumen tidak ditemukan')
                ->danger()
                ->duration(3000)
                ->send();
        }
    }

    public function closePreviewModal()
    {
        $this->previewDocument = null;
        $this->dispatch('close-modal', ['id' => 'preview-document-modal']);
    }

    #[On('refresh-data')]
    public function refreshData()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.client.components.dokumen-tab');
    }
}