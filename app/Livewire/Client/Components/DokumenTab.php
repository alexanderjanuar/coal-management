<?php
// App/Livewire/Client/Components/DokumenTab.php

namespace App\Livewire\Client\Components;

use App\Models\Client;
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
use Filament\Forms\Components\Toggle;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

class DokumenTab extends Component implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    public Client $client;
    public $checklist = [];
    public $additionalDocuments = [];
    public $requiredAdditionalDocuments = [];
    public $stats = [];
    
    // Modal state
    public $selectedSopId = null;
    public $uploadFile = [];
    public $documentNumber = '';
    public $documentName = '';
    public $expiredAt = '';
    public $isAdditionalDocument = false;
    public $isRequirementMode = false;
    public $documentToDelete = null;
    public $adminNotes = '';
    public $documentDescription = '';
    public $requirementCategory = 'other';
    public $isRequired = true;
    public $dueDate = null;
    public $selectedRequirementId = null;
    
    // Preview state
    public $previewDocument = null;
    
    // Review modal state
    public $documentToReview = null;
    public $reviewAction = null;
    public $reviewNotes = '';

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
                                // For Legal Documents
                                Select::make('selectedSopId')
                                    ->label('Jenis Dokumen')
                                    ->options(function () {
                                        if ($this->isAdditionalDocument || $this->isRequirementMode) {
                                            return [];
                                        }
                                        return $this->client->getApplicableSopDocuments()
                                                   ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->required()
                                    ->visible(!$this->isAdditionalDocument && !$this->isRequirementMode && !$this->selectedRequirementId)
                                    ->columnSpan(2),
                                
                                // For uploading to a requirement
                                Select::make('selectedRequirementId')
                                    ->label('Untuk Persyaratan')
                                    ->options(function () {
                                        return $this->client->documentRequirements()
                                            ->pending()
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->required()
                                    ->visible($this->selectedRequirementId && !$this->isRequirementMode)
                                    ->disabled()
                                    ->columnSpan(2),
                                
                                // For creating requirement (admin only)
                                TextInput::make('documentName')
                                    ->label('Nama Dokumen yang Dibutuhkan')
                                    ->placeholder('Contoh: KTP Direktur, NPWP Perusahaan')
                                    ->required()
                                    ->visible($this->isRequirementMode)
                                    ->helperText('Nama dokumen yang harus diupload oleh client')
                                    ->columnSpan(2),
                                
                                Textarea::make('documentDescription')
                                    ->label('Deskripsi/Keterangan')
                                    ->placeholder('Jelaskan dokumen apa yang dibutuhkan...')
                                    ->visible($this->isRequirementMode)
                                    ->rows(3)
                                    ->columnSpan(2),
                                
                                Select::make('requirementCategory')
                                    ->label('Kategori')
                                    ->options([
                                        'legal' => 'Legal',
                                        'financial' => 'Financial',
                                        'operational' => 'Operational',
                                        'compliance' => 'Compliance',
                                        'other' => 'Lainnya',
                                    ])
                                    ->default('other')
                                    ->visible($this->isRequirementMode)
                                    ->required()
                                    ->columnSpan(1),
                                
                                Toggle::make('isRequired')
                                    ->label('Wajib?')
                                    ->default(true)
                                    ->visible($this->isRequirementMode)
                                    ->columnSpan(1),
                                
                                DatePicker::make('dueDate')
                                    ->label('Tenggat Waktu')
                                    ->placeholder('Pilih tenggat waktu (opsional)')
                                    ->after('today')
                                    ->visible($this->isRequirementMode)
                                    ->columnSpan(2),
                                    
                                // For actual document upload
                                TextInput::make('documentNumber')
                                    ->label('Nomor Dokumen')
                                    ->placeholder('Masukkan nomor dokumen (opsional)')
                                    ->visible(!$this->isRequirementMode)
                                    ->columnSpan(1),
                                    
                                DatePicker::make('expiredAt')
                                    ->label('Tanggal Kadaluarsa')
                                    ->placeholder('Pilih tanggal kadaluarsa (opsional)')
                                    ->after('today')
                                    ->visible(!$this->isRequirementMode)
                                    ->columnSpan(1),
                            ]),
                        
                        Textarea::make('adminNotes')
                            ->label('Catatan Admin')
                            ->placeholder('Tambahkan catatan untuk dokumen ini (opsional)')
                            ->rows(3)
                            ->visible(!$this->isRequirementMode)
                            ->columnSpanFull(),
                            
                        FileUpload::make('uploadFile')
                            ->label('File Dokumen')
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240) // 10MB
                            ->helperText('Format: PDF, JPG, JPEG, PNG, DOC, DOCX (Maksimal 10MB)')
                            ->disk('public')
                            ->directory(function () {
                                return $this->isAdditionalDocument || $this->isRequirementMode || $this->selectedRequirementId
                                    ? $this->client->getFolderPath() 
                                    : $this->client->getLegalFolderPath();
                            })
                            ->visibility('private')
                            ->uploadingMessage('Sedang mengupload...')
                            ->visible(!$this->isRequirementMode)
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public function loadData()
    {
        $this->checklist = $this->client->getLegalDocumentsChecklist();
        $this->loadAdditionalDocuments();
        $this->loadRequiredAdditionalDocuments();
        $this->calculateStats();
    }

    public function loadAdditionalDocuments()
    {
        $this->additionalDocuments = $this->client->clientDocuments()
            ->additionalDocuments()
            ->with('user', 'reviewer')
            ->latest()
            ->get();
    }

    public function loadRequiredAdditionalDocuments()
    {
        $this->requiredAdditionalDocuments = $this->client->documentRequirements()
            ->with(['createdBy', 'documents' => function($query) {
                $query->latest()->with('user', 'reviewer');
            }])
            ->latest()
            ->get();
    }

    public function calculateStats()
    {
        $this->stats = $this->client->getLegalDocumentsStats();
    }

    public function openRequirementModal()
    {
        $this->isRequirementMode = true;
        $this->isAdditionalDocument = false;
        $this->selectedRequirementId = null;
        $this->resetModalFields();
        
        $this->dispatch('open-modal', id: 'upload-document-modal');
    }

    public function openUploadModal($sopId = null, $isAdditional = false, $requirementId = null)
    {
        $this->selectedSopId = $sopId;
        $this->isAdditionalDocument = $isAdditional;
        $this->isRequirementMode = false;
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
        $this->adminNotes = '';
        $this->documentDescription = '';
        $this->requirementCategory = 'other';
        $this->isRequired = true;
        $this->dueDate = null;
        
        if (!$this->selectedSopId && !$this->selectedRequirementId) {
            $this->selectedSopId = null;
            $this->selectedRequirementId = null;
        }
    }

    public function uploadDocument()
    {
        $data = $this->form->getState();
        
        try {
            // If creating a requirement (template)
            if ($this->isRequirementMode) {
                ClientDocumentRequirement::create([
                    'client_id' => $this->client->id,
                    'created_by' => auth()->id(),
                    'name' => $data['documentName'],
                    'description' => $data['documentDescription'] ?? null,
                    'category' => $data['requirementCategory'] ?? 'other',
                    'is_required' => $data['isRequired'] ?? true,
                    'due_date' => $data['dueDate'] ?? null,
                    'status' => 'pending',
                ]);
                
                $this->closeUploadModal();
                $this->loadData();
                
                Notification::make()
                    ->title('Berhasil!')
                    ->body('Persyaratan dokumen berhasil ditambahkan!')
                    ->success()
                    ->duration(3000)
                    ->send();
                    
                return;
            }
            
            // Normal upload flow
            if (!empty($data['uploadFile'])) {
                $uploadedFile = is_array($data['uploadFile']) ? $data['uploadFile'][0] : $data['uploadFile'];
                
                if (is_object($uploadedFile) && method_exists($uploadedFile, 'getClientOriginalName')) {
                    $originalName = $uploadedFile->getClientOriginalName();
                } else {
                    $originalName = basename($uploadedFile);
                }
                
                $filename = time() . '_' . $originalName;
                
                $storagePath = $this->isAdditionalDocument || $this->selectedRequirementId
                    ? $this->client->getFolderPath() 
                    : $this->client->getLegalFolderPath();
                
                if (is_object($uploadedFile) && method_exists($uploadedFile, 'storeAs')) {
                    $filePath = $uploadedFile->storeAs($storagePath, $filename, 'public');
                } else {
                    $filePath = $uploadedFile;
                }

                $documentData = [
                    'client_id' => $this->client->id,
                    'user_id' => auth()->id(),
                    'file_path' => $filePath,
                    'original_filename' => $originalName,
                    'document_number' => $data['documentNumber'] ?? null,
                    'expired_at' => $data['expiredAt'] ?? null,
                    'status' => 'valid',
                    'admin_notes' => $data['adminNotes'] ?? null,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
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
                if ($this->isAdditionalDocument) {
                    $documentData['document_category'] = 'additional';
                }

                ClientDocument::create($documentData);

                // Send notifications to client users
                $this->sendAdminUploadNotification($this->client, $originalName);

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

    public function openReviewModal($documentId, $action)
    {
        $this->documentToReview = ClientDocument::find($documentId);
        $this->reviewAction = $action;
        $this->reviewNotes = '';
        
        $this->dispatch('open-modal', id: 'review-document-modal');
    }

    public function closeReviewModal()
    {
        $this->documentToReview = null;
        $this->reviewAction = null;
        $this->reviewNotes = '';
        $this->dispatch('close-modal', id: 'review-document-modal');
    }

    public function submitReview()
    {
        if (!$this->documentToReview) {
            return;
        }

        try {
            if ($this->reviewAction === 'approve') {
                $this->documentToReview->approve($this->reviewNotes);
                
                // Send notification to client users
                $this->sendDocumentReviewNotification($this->documentToReview, 'approved', $this->reviewNotes);
                
                Notification::make()
                    ->title('Berhasil!')
                    ->body('Dokumen telah disetujui')
                    ->success()
                    ->send();
            } elseif ($this->reviewAction === 'reject') {
                if (empty($this->reviewNotes)) {
                    Notification::make()
                        ->title('Error!')
                        ->body('Alasan penolakan harus diisi')
                        ->danger()
                        ->send();
                    return;
                }
                
                $this->documentToReview->reject($this->reviewNotes);
                
                // Send notification to client users
                $this->sendDocumentReviewNotification($this->documentToReview, 'rejected', $this->reviewNotes);
                
                Notification::make()
                    ->title('Dokumen Ditolak')
                    ->body('Dokumen telah ditolak')
                    ->warning()
                    ->send();
            }

            $this->closeReviewModal();
            $this->loadData();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error!')
                ->body('Gagal memproses review: ' . $e->getMessage())
                ->danger()
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
                if ($document->file_path && \Storage::disk('public')->exists($document->file_path)) {
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
        $this->dispatch('close-modal', id: 'confirm-delete-modal');
    }

    public function deleteRequirement($requirementId)
    {
        try {
            $requirement = ClientDocumentRequirement::find($requirementId);
            if ($requirement) {
                $requirement->delete();
                $this->loadData();
                
                Notification::make()
                    ->title('Berhasil!')
                    ->body('Persyaratan dokumen berhasil dihapus!')
                    ->success()
                    ->duration(3000)
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error!')
                ->body('Gagal menghapus: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function waiveRequirement($requirementId)
    {
        try {
            $requirement = ClientDocumentRequirement::find($requirementId);
            if ($requirement) {
                $requirement->waive('Dikecualikan oleh admin');
                $this->loadData();
                
                Notification::make()
                    ->title('Berhasil!')
                    ->body('Persyaratan dokumen dikecualikan!')
                    ->success()
                    ->duration(3000)
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error!')
                ->body('Gagal mengecualikan: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function previewDocuments($documentId)
    {
        $this->previewDocument = ClientDocument::with('user', 'reviewer')->find($documentId);
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

    protected function sendAdminUploadNotification(Client $client, string $filename)
    {
        try {
            // Get all users linked to this client who have 'client' role
            $clientUsers = \App\Models\User::whereHas('userClients', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })
            ->whereHas('roles', function ($query) {
                $query->where('name', 'client');
            })
            ->get();

            if ($clientUsers->isEmpty()) {
                return;
            }

            $adminName = auth()->user()->name;
            $clientName = $client->name;

            // Determine document type
            $docType = 'dokumen';
            if ($this->selectedRequirementId) {
                $requirement = ClientDocumentRequirement::find($this->selectedRequirementId);
                $docType = $requirement ? $requirement->name : 'dokumen persyaratan';
            } elseif ($this->selectedSopId) {
                $sopDoc = SopLegalDocument::find($this->selectedSopId);
                $docType = $sopDoc ? $sopDoc->name : 'dokumen legal';
            } elseif ($this->isAdditionalDocument) {
                $docType = 'dokumen tambahan';
            }

            foreach ($clientUsers as $user) {
                Notification::make()
                    ->title('📄 Dokumen Baru dari Admin')
                    ->body("Admin {$adminName} telah mengupload {$docType} untuk {$clientName}: {$filename}")
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('view')
                            ->label('👁️ Lihat Dokumen')
                            ->url(route('filament.client.pages.document-page'))
                            ->button()
                            ->color('success')
                            ->markAsRead(),
                        \Filament\Notifications\Actions\Action::make('dismiss')
                            ->label('Tutup')
                            ->color('gray')
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($user);
            }

        } catch (\Exception $e) {
            \Log::error('Failed to send admin upload notification: ' . $e->getMessage());
        }
    }

    protected function sendDocumentReviewNotification(ClientDocument $document, string $action, ?string $notes = null)
    {
        try {
            // Get all users linked to this client who have 'client' role
            $clientUsers = \App\Models\User::whereHas('userClients', function ($query) use ($document) {
                $query->where('client_id', $document->client_id);
            })
            ->whereHas('roles', function ($query) {
                $query->where('name', 'client');
            })
            ->get();

            if ($clientUsers->isEmpty()) {
                return;
            }

            $reviewerName = auth()->user()->name;
            $clientName = $document->client->name;
            $filename = $document->original_filename;

            if ($action === 'approved') {
                foreach ($clientUsers as $user) {
                    Notification::make()
                        ->title('✅ Dokumen Disetujui')
                        ->body("Dokumen '{$filename}' untuk {$clientName} telah disetujui oleh {$reviewerName}." . ($notes ? " Catatan: {$notes}" : ''))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('view')
                                ->label('👁️ Lihat Dokumen')
                                ->url(route('filament.client.pages.document-page'))
                                ->button()
                                ->color('success')
                                ->markAsRead(),
                            \Filament\Notifications\Actions\Action::make('dismiss')
                                ->label('Tutup')
                                ->color('gray')
                                ->markAsRead(),
                        ])
                        ->sendToDatabase($user);
                }
            } elseif ($action === 'rejected') {
                foreach ($clientUsers as $user) {
                    Notification::make()
                        ->title('❌ Dokumen Ditolak')
                        ->body("Dokumen '{$filename}' untuk {$clientName} telah ditolak oleh {$reviewerName}. Alasan: {$notes}")
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->persistent()
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('reupload')
                                ->label('📤 Upload Ulang')
                                ->url(route('filament.client.pages.document-page'))
                                ->button()
                                ->color('danger')
                                ->markAsRead(),
                            \Filament\Notifications\Actions\Action::make('dismiss')
                                ->label('Tutup')
                                ->color('gray')
                                ->markAsRead(),
                        ])
                        ->sendToDatabase($user);
                }
            }

        } catch (\Exception $e) {
            \Log::error('Failed to send document review notification: ' . $e->getMessage());
        }
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