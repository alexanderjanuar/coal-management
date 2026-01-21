<?php
// App/Livewire/Client/Management/DokumenTab.php

namespace App\Livewire\Client\Management;

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
                $requirement = ClientDocumentRequirement::create([
                    'client_id' => $this->client->id,
                    'created_by' => auth()->id(),
                    'name' => $data['documentName'],
                    'description' => $data['documentDescription'] ?? null,
                    'category' => $data['requirementCategory'] ?? 'other',
                    'is_required' => $data['isRequired'] ?? true,
                    'due_date' => $data['dueDate'] ?? null,
                    'status' => 'pending',
                ]);
                
                // Send notification to client users about new requirement
                $this->sendRequirementCreatedNotification($this->client, $requirement);
                
                $this->closeUploadModal();
                $this->loadData();
                
                Notification::make()
                    ->title('Berhasil!')
                    ->body('Persyaratan dokumen berhasil ditambahkan dan notifikasi telah dikirim ke client!')
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
                    ->body('Dokumen berhasil diupload dan notifikasi telah dikirim ke client!')
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

    /**
     * Quick approve from preview modal
     */
    public function quickApprove($documentId)
    {
        try {
            $document = ClientDocument::findOrFail($documentId);
            
            // Validate document is in correct status
            if ($document->status !== 'pending_review') {
                Notification::make()
                    ->title('Error!')
                    ->body('Dokumen tidak dalam status pending review')
                    ->danger()
                    ->send();
                return;
            }

            // Update document status
            $document->status = 'valid';
            $document->reviewed_by = auth()->id();
            $document->reviewed_at = now();
            $document->admin_notes = $this->reviewNotes ?: null;
            $document->save();

            // Close preview modal
            $this->closePreviewModal();

            // Send notification to client
            $this->sendDocumentReviewNotification($document, 'approved', $this->reviewNotes);

            // Reset review notes
            $this->reviewNotes = '';

            // Reload data
            $this->loadData();

            Notification::make()
                ->title('Berhasil!')
                ->body('Dokumen telah disetujui')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error!')
                ->body('Gagal menyetujui dokumen: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Quick reject from preview modal
     */
    public function quickReject($documentId)
    {
        try {
            $document = ClientDocument::findOrFail($documentId);
            
            // Validate document is in correct status
            if ($document->status !== 'pending_review') {
                Notification::make()
                    ->title('Error!')
                    ->body('Dokumen tidak dalam status pending review')
                    ->danger()
                    ->send();
                return;
            }

            // Validate rejection notes
            if (empty($this->reviewNotes)) {
                Notification::make()
                    ->title('Error!')
                    ->body('Alasan penolakan wajib diisi')
                    ->danger()
                    ->send();
                return;
            }

            // Update document status
            $document->status = 'rejected';
            $document->reviewed_by = auth()->id();
            $document->reviewed_at = now();
            $document->admin_notes = $this->reviewNotes;
            $document->save();

            // Close preview modal
            $this->closePreviewModal();

            // Send notification to client
            $this->sendDocumentReviewNotification($document, 'rejected', $this->reviewNotes);

            // Reset review notes
            $this->reviewNotes = '';

            // Reload data
            $this->loadData();

            Notification::make()
                ->title('Berhasil!')
                ->body('Dokumen telah ditolak')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error!')
                ->body('Gagal menolak dokumen: ' . $e->getMessage())
                ->danger()
                ->send();
        }
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
            \Log::error('submitReview: No document to review');
            Notification::make()
                ->title('Error!')
                ->body('Dokumen tidak ditemukan')
                ->danger()
                ->send();
            return;
        }

        \Log::info('submitReview called', [
            'document_id' => $this->documentToReview->id,
            'document_filename' => $this->documentToReview->original_filename,
            'action' => $this->reviewAction,
            'client_id' => $this->documentToReview->client_id,
            'reviewer_id' => auth()->id(),
            'has_notes' => !empty($this->reviewNotes),
        ]);

        try {
            if ($this->reviewAction === 'approve') {
                \Log::info('Approving document', [
                    'document_id' => $this->documentToReview->id,
                ]);
                
                // Update document status
                $this->documentToReview->update([
                    'status' => 'valid',
                    'admin_notes' => $this->reviewNotes,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                ]);
                
                \Log::info('Document status updated to valid', [
                    'document_id' => $this->documentToReview->id,
                    'new_status' => $this->documentToReview->fresh()->status,
                ]);
                
                // Send notification to client users
                \Log::info('About to send approval notification');
                $this->sendDocumentReviewNotification($this->documentToReview, 'approved', $this->reviewNotes);
                \Log::info('Approval notification method completed');
                
                Notification::make()
                    ->title('Berhasil!')
                    ->body('Dokumen telah disetujui dan notifikasi telah dikirim ke client!')
                    ->success()
                    ->send();
                    
            } elseif ($this->reviewAction === 'reject') {
                if (empty($this->reviewNotes)) {
                    \Log::warning('Rejection attempted without notes', [
                        'document_id' => $this->documentToReview->id,
                    ]);
                    
                    Notification::make()
                        ->title('Error!')
                        ->body('Alasan penolakan harus diisi')
                        ->danger()
                        ->send();
                    return;
                }
                
                \Log::info('Rejecting document', [
                    'document_id' => $this->documentToReview->id,
                    'reason' => $this->reviewNotes,
                ]);
                
                // Update document status
                $this->documentToReview->update([
                    'status' => 'rejected',
                    'admin_notes' => $this->reviewNotes,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                ]);
                
                \Log::info('Document status updated to rejected', [
                    'document_id' => $this->documentToReview->id,
                ]);
                
                // Send notification to client users
                \Log::info('About to send rejection notification');
                $this->sendDocumentReviewNotification($this->documentToReview, 'rejected', $this->reviewNotes);
                \Log::info('Rejection notification method completed');
                
                Notification::make()
                    ->title('Dokumen Ditolak')
                    ->body('Dokumen telah ditolak dan notifikasi telah dikirim ke client!')
                    ->warning()
                    ->send();
            }

            $this->closeReviewModal();
            $this->loadData();

        } catch (\Exception $e) {
            \Log::error('submitReview failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'document_id' => $this->documentToReview->id ?? null,
                'action' => $this->reviewAction ?? null,
            ]);
            
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
                \Log::warning('No client users found for admin upload notification', [
                    'client_id' => $client->id,
                    'uploader_id' => auth()->id(),
                ]);
                return;
            }

            $adminName = auth()->user()->name;
            $clientName = $client->name;

            // Determine document type with emoji
            $docType = 'dokumen';
            $docTypeEmoji = '📄';
            
            if ($this->selectedRequirementId) {
                $requirement = ClientDocumentRequirement::find($this->selectedRequirementId);
                $docType = $requirement ? $requirement->name : 'dokumen persyaratan';
                $docTypeEmoji = '📋';
            } elseif ($this->selectedSopId) {
                $sopDoc = SopLegalDocument::find($this->selectedSopId);
                $docType = $sopDoc ? $sopDoc->name : 'dokumen legal';
                $docTypeEmoji = '⚖️';
            } elseif ($this->isAdditionalDocument) {
                $docType = 'dokumen tambahan';
                $docTypeEmoji = '➕';
            }

            foreach ($clientUsers as $user) {
                $body = sprintf(
                    "<div style='color: #374151; line-height: 1.6;'>
                        <div style='margin-bottom: 12px;'>
                            <strong>%s</strong> menambahkan dokumen baru
                        </div>
                        <div style='margin: 12px 0; color: #6b7280; font-size: 14px;'>
                            <div style='margin-bottom: 6px;'>Client: %s</div>
                            <div style='margin-bottom: 6px;'>Jenis: %s %s</div>
                            <div>File: %s</div>
                        </div>
                        <div style='color: #9ca3af; font-size: 12px; margin-top: 12px;'>
                            %s
                        </div>
                    </div>",
                    $adminName,
                    $clientName,
                    $docTypeEmoji,
                    $docType,
                    $filename,
                    now()->format('d M Y • H:i')
                );

                Notification::make()
                    ->title('Dokumen Baru dari Admin')
                    ->body($body)
                    ->icon('heroicon-o-document-plus')
                    ->iconColor('primary')
                    ->sendToDatabase($user)
                    ->broadcast($user);
                    
                \Log::debug('Admin upload notification sent', [
                    'recipient_id' => $user->id,
                    'recipient_name' => $user->name,
                    'client_id' => $client->id,
                    'filename' => $filename,
                ]);
            }
            
            \Log::info('Admin upload notifications sent successfully', [
                'total_sent' => $clientUsers->count(),
                'client_id' => $client->id,
                'admin_id' => auth()->id(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to send admin upload notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'client_id' => $client->id ?? null,
            ]);
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
                \Log::warning('No client users found for review notification', [
                    'document_id' => $document->id,
                    'client_id' => $document->client_id,
                ]);
                return;
            }

            $reviewerName = auth()->user()->name;
            $clientName = $document->client->name;
            $filename = $document->original_filename;

            if ($action === 'approved') {
                foreach ($clientUsers as $user) {
                    $body = sprintf(
                        "<div style='color: #374151; line-height: 1.6;'>
                            <div style='margin-bottom: 12px;'>
                                Dokumen Anda telah <strong style='color: #059669;'>disetujui</strong>
                            </div>
                            <div style='margin: 12px 0; color: #6b7280; font-size: 14px;'>
                                <div style='margin-bottom: 6px;'>Client: %s</div>
                                <div style='margin-bottom: 6px;'>Dokumen: %s</div>
                                <div style='margin-bottom: 6px;'>Reviewer: %s</div>
                                %s
                            </div>
                            <div style='margin: 10px 0; padding: 8px; color: #065f46; font-size: 13px;'>
                                ✓ Status: Valid
                            </div>
                            <div style='color: #9ca3af; font-size: 12px; margin-top: 12px;'>
                                %s
                            </div>
                        </div>",
                        $clientName,
                        $filename,
                        $reviewerName,
                        $notes ? "<div>Catatan: {$notes}</div>" : '',
                        now()->format('d M Y • H:i')
                    );

                    Notification::make()
                        ->title('Dokumen Disetujui')
                        ->body($body)
                        ->icon('heroicon-o-check-circle')
                        ->iconColor('success')
                        ->sendToDatabase($user)
                        ->broadcast($user);
                    
                    \Log::debug('Document approval notification sent', [
                        'recipient_id' => $user->id,
                        'document_id' => $document->id,
                    ]);
                }
            } elseif ($action === 'rejected') {
                foreach ($clientUsers as $user) {
                    $body = sprintf(
                        "<div style='color: #374151; line-height: 1.6;'>
                            <div style='margin-bottom: 12px;'>
                                Dokumen perlu <strong style='color: #dc2626;'>diperbaiki</strong>
                            </div>
                            <div style='margin: 12px 0; color: #6b7280; font-size: 14px;'>
                                <div style='margin-bottom: 6px;'>Client: %s</div>
                                <div style='margin-bottom: 6px;'>Dokumen: %s</div>
                                <div style='margin-bottom: 6px;'>Reviewer: %s</div>
                            </div>
                            <div style='margin: 10px 0; padding: 10px; color: #991b1b; font-size: 13px;'>
                                <div style='margin-bottom: 4px; font-weight: 500;'>Alasan:</div>
                                <div>%s</div>
                            </div>
                            <div style='color: #9ca3af; font-size: 12px; margin-top: 12px;'>
                                %s
                            </div>
                        </div>",
                        $clientName,
                        $filename,
                        $reviewerName,
                        $notes ?? 'Tidak ada catatan',
                        now()->format('d M Y • H:i')
                    );

                    Notification::make()
                        ->title('Dokumen Ditolak')
                        ->body($body)
                        ->icon('heroicon-o-x-circle')
                        ->iconColor('danger')
                        ->sendToDatabase($user)
                        ->broadcast($user);
                    
                    \Log::debug('Document rejection notification sent', [
                        'recipient_id' => $user->id,
                        'document_id' => $document->id,
                        'reason' => $notes,
                    ]);
                }
            }

            \Log::info('Document review notifications sent successfully', [
                'total_sent' => $clientUsers->count(),
                'action' => $action,
                'document_id' => $document->id,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to send document review notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'document_id' => $document->id ?? null,
                'action' => $action ?? null,
            ]);
        }
    }

    /**
     * Send notification when a new requirement is created
     */
    protected function sendRequirementCreatedNotification(Client $client, ClientDocumentRequirement $requirement)
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
                \Log::warning('No client users found for requirement notification', [
                    'client_id' => $client->id,
                    'requirement_id' => $requirement->id,
                ]);
                return;
            }

            $adminName = auth()->user()->name;
            $clientName = $client->name;
            
            // Category emoji
            $categoryEmoji = match($requirement->category) {
                'legal' => '⚖️',
                'financial' => '💰',
                'operational' => '⚙️',
                'compliance' => '✅',
                default => '📋'
            };

            foreach ($clientUsers as $user) {
                $body = sprintf(
                    "<div style='color: #374151; line-height: 1.6;'>
                        <div style='margin-bottom: 12px;'>
                            <strong>%s</strong> meminta dokumen tambahan
                        </div>
                        <div style='margin: 12px 0; color: #6b7280; font-size: 14px;'>
                            <div style='margin-bottom: 6px;'>Client: %s</div>
                            <div style='margin-bottom: 6px;'>Dokumen: %s <strong>%s</strong></div>
                            <div style='margin-bottom: 6px;'>Kategori: %s %s</div>
                            %s
                            %s
                        </div>
                        %s
                        <div style='color: #9ca3af; font-size: 12px; margin-top: 12px;'>
                            %s
                        </div>
                    </div>",
                    $adminName,
                    $clientName,
                    $requirement->is_required ? '⚠' : '',
                    $requirement->name,
                    $categoryEmoji,
                    ucfirst($requirement->category),
                    $requirement->description ? "<div style='margin-bottom: 6px;'>Keterangan: {$requirement->description}</div>" : '',
                    $requirement->due_date ? "<div style='margin-bottom: 6px;'>Tenggat: <strong style='color: #dc2626;'>{$requirement->due_date->format('d M Y')}</strong></div>" : '',
                    $requirement->is_required ? "<div style='margin: 10px 0; padding: 7px; color: #78350f; font-size: 12px;'>⚠ Dokumen Wajib</div>" : '',
                    now()->format('d M Y • H:i')
                );

                Notification::make()
                    ->title('Persyaratan Dokumen Baru')
                    ->body($body)
                    ->icon('heroicon-o-document-plus')
                    ->iconColor('warning')
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('Mark As Read')
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($user)
                    ->broadcast($user);
                
                \Log::debug('Requirement created notification sent', [
                    'recipient_id' => $user->id,
                    'requirement_id' => $requirement->id,
                ]);
            }

            \Log::info('Requirement created notifications sent successfully', [
                'total_sent' => $clientUsers->count(),
                'requirement_id' => $requirement->id,
                'client_id' => $client->id,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to send requirement created notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'requirement_id' => $requirement->id ?? null,
            ]);
        }
    }

    #[On('refresh-data')]
    public function refreshData()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.client.management.dokumen-tab');
    }
}