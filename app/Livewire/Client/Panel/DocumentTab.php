<?php

namespace App\Livewire\Client\Panel;

use App\Models\Client;
use App\Models\UserClient;
use App\Models\ClientDocument;
use App\Models\ClientDocumentRequirement;
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
use Filament\Forms\Components\Textarea;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

class DocumentTab extends Component implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    public $clients = [];
    public $selectedClientId = null;
    
    public $checklist = [];
    public $additionalDocuments = [];
    public $requiredAdditionalDocuments = [];
    public $stats = [];
    
    // Modal state
    public $currentClient = null;
    public $selectedSopId = null;
    public $uploadFile = [];
    public $documentNumber = '';
    public $documentName = '';
    public $expiredAt = '';
    public $isAdditionalDocument = false;
    public $selectedRequirementId = null;
    public $documentToDelete = null;
    public $documentDescription = '';
    
    // Preview state
    public $previewDocument = null;

    public function mount()
    {
        $this->loadClients();
        
        // Auto-select first client as default
        if ($this->clients->isNotEmpty()) {
            $this->selectedClientId = $this->clients->first()->id;
            $this->loadClientData($this->selectedClientId);
        }
    }

    public function loadClients()
    {
        // Get all clients linked to current user
        $clientIds = UserClient::where('user_id', auth()->id())
            ->pluck('client_id');

        if ($clientIds->isEmpty()) {
            $this->clients = collect([]);
            return;
        }

        $this->clients = Client::whereIn('id', $clientIds)
            ->with(['pic', 'accountRepresentative'])
            ->orderBy('name')
            ->get()
            ->map(function ($client) {
                // Calculate stats for each client
                $client->document_stats = $client->getLegalDocumentsStats();
                $client->requirement_stats = $client->requirement_stats;
                return $client;
            });
    }

    public function selectClient($clientId)
    {
        $this->selectedClientId = $clientId;
        $this->loadClientData($clientId);
    }

    public function loadClientData($clientId)
    {
        $this->currentClient = Client::find($clientId);
        
        if (!$this->currentClient) {
            return;
        }
        
        $this->checklist = $this->currentClient->getLegalDocumentsChecklist();
        $this->loadAdditionalDocuments();
        $this->loadRequiredAdditionalDocuments();
        $this->calculateStats();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                // For Legal Documents (SOP-based)
                                Select::make('selectedSopId')
                                    ->label('Jenis Dokumen')
                                    ->options(function () {
                                        if ($this->isAdditionalDocument || $this->selectedRequirementId || !$this->currentClient) {
                                            return [];
                                        }
                                        return $this->currentClient->getApplicableSopDocuments()
                                                   ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->required()
                                    ->visible(fn () => !$this->isAdditionalDocument && !$this->selectedRequirementId)
                                    ->columnSpan(2),
                                
                                // For uploading to a requirement
                                Select::make('selectedRequirementId')
                                    ->label('Untuk Persyaratan')
                                    ->options(function () {
                                        if (!$this->currentClient) return [];
                                        return $this->currentClient->documentRequirements()
                                            ->pending()
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->required()
                                    ->visible(fn () => $this->selectedRequirementId)
                                    ->disabled()
                                    ->columnSpan(2),
                                    
                                // For additional documents
                                TextInput::make('documentName')
                                    ->label('Nama Dokumen')
                                    ->placeholder('Masukkan nama dokumen')
                                    ->required()
                                    ->visible(fn () => $this->isAdditionalDocument && !$this->selectedRequirementId)
                                    ->columnSpan(2),
                            ]),
                        
                        Textarea::make('documentDescription')
                            ->label('Catatan')
                            ->placeholder('Tambahkan catatan untuk dokumen ini (opsional)')
                            ->rows(3)
                            ->columnSpanFull(),
                            
                        FileUpload::make('uploadFile')
                            ->label('File Dokumen')
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240) // 10MB
                            ->required()
                            ->helperText('Format: PDF, JPG, JPEG, PNG, DOC, DOCX (Maksimal 10MB)')
                            ->disk('public')
                            ->directory(function () {
                                if (!$this->currentClient) return 'temp';
                                return $this->isAdditionalDocument || $this->selectedRequirementId
                                    ? $this->currentClient->getFolderPath() 
                                    : $this->currentClient->getLegalFolderPath();
                            })
                            ->visibility('private')
                            ->uploadingMessage('Sedang mengupload...')
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public function loadAdditionalDocuments()
    {
        if (!$this->currentClient) {
            $this->additionalDocuments = collect([]);
            return;
        }
        
        $this->additionalDocuments = $this->currentClient->clientDocuments()
            ->additionalDocuments()
            ->with('user')
            ->latest()
            ->get();
    }

    public function loadRequiredAdditionalDocuments()
    {
        if (!$this->currentClient) {
            $this->requiredAdditionalDocuments = collect([]);
            return;
        }
        
        $this->requiredAdditionalDocuments = $this->currentClient->documentRequirements()
            ->with(['createdBy', 'documents' => function($query) {
                $query->latest()->with('user');
            }])
            ->latest()
            ->get();
    }

    public function calculateStats()
    {
        if (!$this->currentClient) {
            $this->stats = [];
            return;
        }
        
        $this->stats = $this->currentClient->getLegalDocumentsStats();
    }

    public function openUploadModal($clientId, $sopId = null, $isAdditional = false, $requirementId = null)
    {
        $this->currentClient = Client::find($clientId);
        $this->selectedSopId = $sopId;
        $this->isAdditionalDocument = $isAdditional;
        $this->selectedRequirementId = $requirementId;
        $this->resetModalFields();
        
        $this->dispatch('open-modal', id: 'upload-document-modal');
    }

    public function closeUploadModal()
    {
        $this->resetModalFields();
        $this->dispatch('close-modal', id: 'upload-document-modal');
    }

    private function resetModalFields()
    {
        $this->uploadFile = [];
        $this->documentNumber = '';
        $this->documentName = '';
        $this->expiredAt = '';
        $this->documentDescription = '';
        
        if (!$this->selectedSopId && !$this->selectedRequirementId) {
            $this->selectedSopId = null;
            $this->selectedRequirementId = null;
        }
    }

    public function uploadDocument()
    {
        if (!$this->currentClient) {
            Notification::make()
                ->title('Error!')
                ->body('Client tidak ditemukan')
                ->danger()
                ->send();
            return;
        }
        
        $data = $this->form->getState();
        
        try {
            // Handle file upload
            if (!empty($data['uploadFile'])) {
                $uploadedFile = is_array($data['uploadFile']) ? $data['uploadFile'][0] : $data['uploadFile'];
                
                // Get original filename
                if (is_object($uploadedFile) && method_exists($uploadedFile, 'getClientOriginalName')) {
                    $originalName = $uploadedFile->getClientOriginalName();
                } else {
                    $originalName = basename($uploadedFile);
                }
                
                $filename = time() . '_' . $originalName;
                
                // Determine storage path
                $storagePath = $this->isAdditionalDocument || $this->selectedRequirementId
                    ? $this->currentClient->getFolderPath() 
                    : $this->currentClient->getLegalFolderPath();
                
                // Store file
                if (is_object($uploadedFile) && method_exists($uploadedFile, 'storeAs')) {
                    $filePath = $uploadedFile->storeAs($storagePath, $filename, 'public');
                } else {
                    $filePath = $uploadedFile;
                }

                // Create document record
                $documentData = [
                    'client_id' => $this->currentClient->id,
                    'user_id' => auth()->id(),
                    'file_path' => $filePath,
                    'original_filename' => $originalName,
                    'description' => $data['documentDescription'] ?? null,
                    'status' => 'pending_review',
                ];

                // Link to SOP Legal Document
                if (!$this->isAdditionalDocument && !$this->selectedRequirementId && $this->selectedSopId) {
                    $documentData['sop_legal_document_id'] = $data['selectedSopId'];
                }
                
                // Link to Requirement
                if ($this->selectedRequirementId) {
                    $documentData['requirement_id'] = $this->selectedRequirementId;
                }
                
                // Set category for additional documents
                if ($this->isAdditionalDocument && !$this->selectedRequirementId) {
                    $documentData['document_category'] = 'additional';
                }

                ClientDocument::create($documentData);

                // Send notifications to other client users
                $this->sendDocumentUploadNotification($this->currentClient, $originalName);

                $this->closeUploadModal();
                $this->loadClientData($this->currentClient->id);
                $this->loadClients(); // Refresh stats
                
                Notification::make()
                    ->title('Berhasil!')
                    ->body('Dokumen berhasil diupload dan menunggu review!')
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
                // Only allow client to delete their own documents that are not yet approved
                if ($document->user_id !== auth()->id() || $document->status === 'valid') {
                    Notification::make()
                        ->title('Error!')
                        ->body('Anda tidak dapat menghapus dokumen ini')
                        ->danger()
                        ->send();
                    return;
                }
                
                if (\Storage::disk('public')->exists($document->file_path)) {
                    \Storage::disk('public')->delete($document->file_path);
                }
                
                $clientId = $document->client_id;
                $document->delete();
                
                $this->loadClientData($clientId);
                $this->loadClients(); // Refresh stats
                
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
        $document = ClientDocument::find($documentId);
        
        // Check if user can delete this document
        if (!$document || $document->user_id !== auth()->id() || $document->status === 'valid') {
            Notification::make()
                ->title('Error!')
                ->body('Anda tidak dapat menghapus dokumen ini')
                ->danger()
                ->send();
            return;
        }
        
        $this->documentToDelete = $documentId;
        $this->dispatch('open-modal', id: 'confirm-delete-modal');
    }

    public function closeDeleteModal()
    {
        $this->documentToDelete = null;
        $this->dispatch('close-modal', id: 'confirm-delete-modal');
    }

    public function previewDocuments($documentId)
    {
        $this->previewDocument = ClientDocument::with('user')->find($documentId);
        if ($this->previewDocument) {
            $this->dispatch('open-modal', id: 'preview-document-modal');
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
        $this->dispatch('close-modal', id: 'preview-document-modal');
    }

    protected function sendDocumentUploadNotification(Client $client, string $filename)
    {
        try {
            // Get all users linked to this client who have 'client' role (except current user)
            $clientUsers = \App\Models\User::whereHas('userClients', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })
            ->whereHas('roles', function ($query) {
                $query->where('name', 'client');
            })
            ->where('id', '!=', auth()->id())
            ->get();

            if ($clientUsers->isEmpty()) {
                return;
            }

            $uploaderName = auth()->user()->name;
            $clientName = $client->name;

            // Determine document type for better message
            $docType = 'dokumen';
            if ($this->selectedRequirementId) {
                $requirement = \App\Models\ClientDocumentRequirement::find($this->selectedRequirementId);
                $docType = $requirement ? $requirement->name : 'dokumen persyaratan';
            } elseif ($this->selectedSopId) {
                $sopDoc = \App\Models\SopLegalDocument::find($this->selectedSopId);
                $docType = $sopDoc ? $sopDoc->name : 'dokumen legal';
            } elseif ($this->isAdditionalDocument) {
                $docType = 'dokumen tambahan';
            }

            // Send notification to each client user
            foreach ($clientUsers as $user) {
                Notification::make()
                    ->title('📄 Dokumen Baru Diupload')
                    ->body("{$uploaderName} telah mengupload {$docType} untuk {$clientName}: {$filename}")
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('info')
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('view')
                            ->label('👁️ Lihat Dokumen')
                            ->url(route('filament.client.pages.document-page'))
                            ->button()
                            ->color('primary')
                            ->markAsRead(),
                        \Filament\Notifications\Actions\Action::make('dismiss')
                            ->label('Tutup')
                            ->color('gray')
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($user);
            }

        } catch (\Exception $e) {
            // Log error but don't interrupt the upload process
            \Log::error('Failed to send document upload notification: ' . $e->getMessage());
        }
    }

    #[On('refresh-data')]
    public function refreshData()
    {
        $this->loadClients();
        if ($this->selectedClientId) {
            $this->loadClientData($this->selectedClientId);
        }
    }

    public function render()
    {
        return view('livewire.client.panel.document-tab');
    }
}