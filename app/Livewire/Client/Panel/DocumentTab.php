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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
    
    // Search and Filter
    public $searchQuery = '';
    public $statusFilter = 'all';
    public $typeFilter = 'all';
    
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
    
    // Cache duration (5 minutes)
    protected int $cacheDuration = 300;

    public function mount()
    {
        $this->loadClients();
        
        // Auto-select first client as default
        if ($this->clients->isNotEmpty()) {
            $this->selectedClientId = $this->clients->first()->id;
            $this->loadClientData($this->selectedClientId);
        }
    }

    /**
     * Load all clients linked to current user with optimized query
     */
    public function loadClients()
    {
        $userId = auth()->id();
        
        // Get client IDs first (lightweight query)
        $clientIds = UserClient::where('user_id', $userId)
            ->pluck('client_id');

        if ($clientIds->isEmpty()) {
            $this->clients = collect([]);
            return;
        }

        // Load clients with minimal data and eager load relationships
        $this->clients = Client::whereIn('id', $clientIds)
            ->select(['id', 'name', 'logo', 'client_type', 'pic_id', 'ar_id'])
            ->with([
                'pic:id,name',
                'accountRepresentative:id,name'
            ])
            ->orderBy('name')
            ->get()
            ->map(function ($client) {
                // Calculate stats using cached method
                $client->document_stats = $this->getClientDocumentStats($client->id);
                return $client;
            });
    }

    /**
     * Get document stats for a client (cached)
     */
    protected function getClientDocumentStats($clientId): array
    {
        $userId = auth()->id();
        
        return Cache::remember(
            "client_doc_stats_{$userId}_{$clientId}",
            $this->cacheDuration,
            function () use ($clientId) {
                $client = Client::find($clientId);
                return $client ? $client->getLegalDocumentsStats() : [];
            }
        );
    }

    public function selectClient($clientId)
    {
        $this->selectedClientId = $clientId;
        $this->clearClientCache($clientId);
        $this->loadClientData($clientId);
    }

    /**
     * Clear cache for specific client
     */
    protected function clearClientCache($clientId)
    {
        $userId = auth()->id();
        Cache::forget("client_doc_stats_{$userId}_{$clientId}");
        Cache::forget("client_checklist_{$userId}_{$clientId}");
        Cache::forget("client_additional_docs_{$userId}_{$clientId}");
        Cache::forget("client_requirements_{$userId}_{$clientId}");
    }

    public function loadClientData($clientId)
    {
        $this->currentClient = Client::find($clientId);
        
        if (!$this->currentClient) {
            return;
        }
        
        $this->checklist = $this->getClientChecklist();
        $this->loadAdditionalDocuments();
        $this->loadRequiredAdditionalDocuments();
        $this->calculateStats();
    }

    /**
     * Get client checklist with caching
     */
    protected function getClientChecklist()
    {
        if (!$this->currentClient) {
            return collect([]);
        }
        
        $userId = auth()->id();
        $clientId = $this->currentClient->id;
        
        return Cache::remember(
            "client_checklist_{$userId}_{$clientId}",
            $this->cacheDuration,
            fn() => $this->currentClient->getLegalDocumentsChecklist()
        );
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
                            ->directory(fn () => $this->getUploadDirectory())
                            ->visibility('private')
                            ->uploadingMessage('Sedang mengupload...')
                            ->columnSpanFull(),
                    ])
            ]);
    }

    /**
     * Get the upload directory based on client and document type
     * Format: clients/{slug}/Legal or clients/{slug}/Additional
     */
    protected function getUploadDirectory(): string
    {
        if (!$this->currentClient) {
            return 'temp';
        }
        
        // Create slug from client name
        $clientSlug = Str::slug($this->currentClient->name);
        
        // Determine subfolder based on document type
        if ($this->isAdditionalDocument || $this->selectedRequirementId) {
            // Additional documents or requirements go to 'Additional' folder
            return "clients/{$clientSlug}/Additional";
        } else {
            // Legal/SOP documents go to 'Legal' folder
            return "clients/{$clientSlug}/Legal";
        }
    }

    public function loadAdditionalDocuments()
    {
        if (!$this->currentClient) {
            $this->additionalDocuments = collect([]);
            return;
        }
        
        $userId = auth()->id();
        $clientId = $this->currentClient->id;
        
        $this->additionalDocuments = Cache::remember(
            "client_additional_docs_{$userId}_{$clientId}",
            $this->cacheDuration,
            function () {
                return $this->currentClient->clientDocuments()
                    ->additionalDocuments()
                    ->with('user:id,name')
                    ->select([
                        'id', 'client_id', 'user_id', 'file_path', 
                        'original_filename', 'description', 'status', 
                        'admin_notes', 'created_at', 'updated_at'
                    ])
                    ->latest()
                    ->get();
            }
        );
    }

    public function loadRequiredAdditionalDocuments()
    {
        if (!$this->currentClient) {
            $this->requiredAdditionalDocuments = collect([]);
            return;
        }
        
        $userId = auth()->id();
        $clientId = $this->currentClient->id;
        
        $this->requiredAdditionalDocuments = Cache::remember(
            "client_requirements_{$userId}_{$clientId}",
            $this->cacheDuration,
            function () {
                return $this->currentClient->documentRequirements()
                    ->with([
                        'createdBy:id,name',
                        'documents' => function($query) {
                            $query->latest()
                                ->with('user:id,name')
                                ->select([
                                    'id', 'client_id', 'user_id', 'requirement_id',
                                    'file_path', 'original_filename', 'status',
                                    'admin_notes', 'created_at', 'updated_at'
                                ]);
                        }
                    ])
                    ->latest()
                    ->get();
            }
        );
    }

    public function calculateStats()
    {
        if (!$this->currentClient) {
            $this->stats = [];
            return;
        }
        
        $this->stats = $this->getClientDocumentStats($this->currentClient->id);
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
                
                // Create sanitized filename with timestamp
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $baseFilename = pathinfo($originalName, PATHINFO_FILENAME);
                $sanitizedFilename = Str::slug($baseFilename);
                $filename = time() . '_' . $sanitizedFilename . '.' . $extension;
                
                // Get storage path based on document type
                $storagePath = $this->getUploadDirectory();
                
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

                // Clear cache
                $this->clearClientCache($this->currentClient->id);

                // Send notifications to other client users AND management
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
            \Log::error('Document upload error: ' . $e->getMessage());
            
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
        
        if (!$document || !$document->file_path) {
            Notification::make()
                ->title('File tidak ditemukan')
                ->body('File dokumen tidak dapat ditemukan atau sudah dihapus.')
                ->warning()
                ->send();
            return;
        }
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            Notification::make()
                ->title('File tidak ditemukan')
                ->body('File dokumen tidak dapat ditemukan di server.')
                ->warning()
                ->send();
            return;
        }
        
        return response()->download(
            storage_path('app/public/' . $document->file_path),
            $document->original_filename
        );
    }

    public function deleteDocument()
    {
        try {
            if (!$this->documentToDelete) {
                return;
            }
            
            $document = ClientDocument::find($this->documentToDelete);
            
            if (!$document) {
                Notification::make()
                    ->title('Error!')
                    ->body('Dokumen tidak ditemukan')
                    ->danger()
                    ->send();
                return;
            }
            
            // Only allow client to delete their own documents that are not yet approved
            if ($document->user_id !== auth()->id() || $document->status === 'valid') {
                Notification::make()
                    ->title('Error!')
                    ->body('Anda tidak dapat menghapus dokumen ini')
                    ->danger()
                    ->send();
                return;
            }
            
            // Delete file from storage
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            
            $clientId = $document->client_id;
            $document->delete();
            
            // Clear cache
            $this->clearClientCache($clientId);
            
            $this->loadClientData($clientId);
            $this->loadClients(); // Refresh stats
            
            Notification::make()
                ->title('Berhasil!')
                ->body('Dokumen berhasil dihapus!')
                ->success()
                ->duration(3000)
                ->send();
            
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            \Log::error('Document deletion error: ' . $e->getMessage());
            
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
        $this->previewDocument = ClientDocument::with('user:id,name')->find($documentId);
        
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

    /**
     * ENHANCED: Send notifications to clients AND management users
     */
    protected function sendDocumentUploadNotification(Client $client, string $filename)
    {
        try {
            $uploaderName = auth()->user()->name;
            $clientName = $client->name;
            $currentUserId = auth()->id();

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

            // 1️⃣ NOTIFY OTHER CLIENT USERS (existing functionality)
            $clientUsers = \App\Models\User::whereHas('userClients', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })
            ->whereHas('roles', function ($query) {
                $query->where('name', 'client');
            })
            ->where('id', '!=', $currentUserId)
            ->get();

            // 2️⃣ NOTIFY MANAGEMENT USERS (NEW!)
            // Get all users who have access to this client but are NOT client role
            $managementUsers = \App\Models\User::whereHas('userClients', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['project-manager', 'direktur', 'super-admin', 'verificator', 'staff']);
            })
            ->where('id', '!=', $currentUserId)
            ->where('status', 'active')
            ->get();

            // 3️⃣ NOTIFY CLIENT PIC (if assigned and not already in the list)
            $picUsers = collect([]);
            if ($client->pic_id && $client->pic_id !== $currentUserId) {
                $pic = \App\Models\User::find($client->pic_id);
                if ($pic && $pic->status === 'active') {
                    $picUsers->push($pic);
                }
            }

            // 4️⃣ NOTIFY ACCOUNT REPRESENTATIVE (if assigned and not already in the list)
            $arUsers = collect([]);
            if ($client->ar_id && $client->ar_id !== $currentUserId) {
                $ar = \App\Models\User::find($client->ar_id);
                if ($ar && $ar->status === 'active') {
                    $arUsers->push($ar);
                }
            }

            // Merge all recipients and remove duplicates
            $allRecipients = $clientUsers
                ->merge($managementUsers)
                ->merge($picUsers)
                ->merge($arUsers)
                ->unique('id');

            // 🔍 DEBUG: Log recipient information
            \Log::info('Document Upload Notification Recipients', [
                'client_id' => $client->id,
                'client_name' => $clientName,
                'uploader' => $uploaderName,
                'uploader_id' => $currentUserId,
                'document' => $filename,
                'doc_type' => $docType,
                'total_recipients' => $allRecipients->count(),
                'client_users' => $clientUsers->count(),
                'management_users' => $managementUsers->count(),
                'pic_users' => $picUsers->count(),
                'ar_users' => $arUsers->count(),
                'recipient_ids' => $allRecipients->pluck('id')->toArray(),
                'recipient_names' => $allRecipients->pluck('name')->toArray(),
            ]);

            if ($allRecipients->isEmpty()) {
                \Log::warning('No recipients found for document upload notification', [
                    'client_id' => $client->id,
                    'uploader_id' => $currentUserId,
                ]);
                return;
            }

            // Send notification to each recipient
            foreach ($allRecipients as $user) {
                // Customize message based on user role
                $isManagement = $user->hasAnyRole(['project-manager', 'direktur', 'super-admin', 'verificator', 'staff']);
                
                // Format document type with emoji
                $docTypeEmoji = match(true) {
                    str_contains(strtolower($docType), 'legal') => '⚖️',
                    str_contains(strtolower($docType), 'persyaratan') => '📋',
                    str_contains(strtolower($docType), 'tambahan') => '➕',
                    default => '📄'
                };
                
                if ($isManagement) {
                    // Management notification
                    $title = 'Dokumen Baru dari Client';
                    $body = sprintf(
                        "<div style='font-family: system-ui, -apple-system, sans-serif; color: #1f2937; line-height: 1.6;'>
                            <div style='margin-bottom: 12px; color: #374151;'>
                                Client mengupload dokumen baru
                            </div>
                            <div style='background: #f8fafc; border-left: 3px solid #3b82f6; padding: 14px; margin: 12px 0; border-radius: 4px;'>
                                <table style='width: 100%%; border-collapse: collapse;'>
                                    <tr>
                                        <td style='padding: 5px 0; color: #64748b; width: 100px; font-size: 13px;'>Client</td>
                                        <td style='padding: 5px 0; color: #0f172a; font-weight: 500;'>%s</td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 5px 0; color: #64748b; font-size: 13px;'>Uploader</td>
                                        <td style='padding: 5px 0; color: #334155;'>%s</td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 5px 0; color: #64748b; font-size: 13px;'>Jenis</td>
                                        <td style='padding: 5px 0; color: #475569;'>%s %s</td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 5px 0; color: #64748b; font-size: 13px;'>File</td>
                                        <td style='padding: 5px 0; color: #475569; font-family: Consolas, Monaco, monospace; font-size: 13px;'>%s</td>
                                    </tr>
                                </table>
                            </div>
                            <div style='color: #94a3b8; font-size: 12px; margin-top: 10px;'>
                                %s
                            </div>
                        </div>",
                        $clientName,
                        $uploaderName,
                        $docTypeEmoji,
                        $docType,
                        $filename,
                        now()->format('d M Y • H:i')
                    );
                } else {
                    // Client notification
                    $title = 'Dokumen Baru Diupload';
                    $body = sprintf(
                        "<div style='font-family: system-ui, -apple-system, sans-serif; color: #1f2937; line-height: 1.6;'>
                            <div style='margin-bottom: 12px; color: #374151;'>
                                <strong style='color: #111827;'>%s</strong> mengupload dokumen baru
                            </div>
                            <div style='background: #f8fafc; border-left: 3px solid #3b82f6; padding: 14px; margin: 12px 0; border-radius: 4px;'>
                                <table style='width: 100%%; border-collapse: collapse;'>
                                    <tr>
                                        <td style='padding: 5px 0; color: #64748b; width: 100px; font-size: 13px;'>Client</td>
                                        <td style='padding: 5px 0; color: #0f172a; font-weight: 500;'>%s</td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 5px 0; color: #64748b; font-size: 13px;'>Dokumen</td>
                                        <td style='padding: 5px 0; color: #334155;'>%s %s</td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 5px 0; color: #64748b; font-size: 13px;'>File</td>
                                        <td style='padding: 5px 0; color: #475569; font-family: Consolas, Monaco, monospace; font-size: 13px;'>%s</td>
                                    </tr>
                                </table>
                            </div>
                            <div style='color: #94a3b8; font-size: 12px; margin-top: 10px;'>
                                %s
                            </div>
                        </div>",
                        $uploaderName,
                        $clientName,
                        $docTypeEmoji,
                        $docType,
                        $filename,
                        now()->format('d M Y • H:i')
                    );
                }

                Notification::make()
                    ->title($title)
                    ->body($body)
                    ->icon('heroicon-o-document-arrow-up')
                    ->iconColor('primary')
                    ->sendToDatabase($user)
                    ->broadcast($user);
                
                // 🔍 DEBUG: Log each notification sent
                \Log::debug('Notification sent to user', [
                    'recipient_id' => $user->id,
                    'recipient_name' => $user->name,
                    'recipient_email' => $user->email,
                    'recipient_roles' => $user->roles->pluck('name')->toArray(),
                    'is_management' => $isManagement,
                ]);
            }

            // 🔍 DEBUG: Final success log
            \Log::info('Document upload notifications sent successfully', [
                'total_sent' => $allRecipients->count(),
                'client_id' => $client->id,
            ]);

        } catch (\Exception $e) {
            // Enhanced error logging
            \Log::error('Failed to send document upload notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'client_id' => $client->id ?? null,
                'uploader_id' => auth()->id() ?? null,
            ]);
        }
    }

    #[On('refresh-data')]
    public function refreshData()
    {
        $this->loadClients();
        if ($this->selectedClientId) {
            $this->clearClientCache($this->selectedClientId);
            $this->loadClientData($this->selectedClientId);
        }
    }
    
    /**
     * Filter documents based on search and filters
     */
    public function getFilteredChecklistProperty()
    {
        return $this->filterDocuments($this->checklist);
    }
    
    public function getFilteredRequirementsProperty()
    {
        return $this->filterDocuments($this->requiredAdditionalDocuments, true);
    }
    
    public function getFilteredAdditionalDocsProperty()
    {
        return $this->filterDocuments($this->additionalDocuments);
    }
    
    protected function filterDocuments($documents, $isRequirement = false)
    {
        $filtered = $documents;
        
        // Apply search filter
        if (!empty($this->searchQuery)) {
            $search = strtolower($this->searchQuery);
            $filtered = $filtered->filter(function ($doc) use ($search, $isRequirement) {
                if ($isRequirement) {
                    // For requirements
                    $name = strtolower($doc->name ?? '');
                    $description = strtolower($doc->description ?? '');
                    return str_contains($name, $search) || str_contains($description, $search);
                } else {
                    // For documents (array or object)
                    if (is_array($doc)) {
                        $name = strtolower($doc['name'] ?? '');
                        $description = strtolower($doc['description'] ?? '');
                    } else {
                        $name = strtolower($doc->description ?? $doc->original_filename ?? '');
                        $description = strtolower($doc->admin_notes ?? '');
                    }
                    return str_contains($name, $search) || str_contains($description, $search);
                }
            });
        }
        
        // Apply status filter
        if ($this->statusFilter !== 'all') {
            $filtered = $filtered->filter(function ($doc) use ($isRequirement) {
                if ($isRequirement) {
                    $latestDoc = $doc->getLatestDocument();
                    $status = $latestDoc ? $latestDoc->status : $doc->status;
                } else {
                    if (is_array($doc)) {
                        $status = $doc['is_uploaded'] 
                            ? ($doc['uploaded_document']->status ?? 'pending_review')
                            : 'not_uploaded';
                    } else {
                        $status = $doc->status ?? 'not_uploaded';
                    }
                }
                
                return match($this->statusFilter) {
                    'valid' => $status === 'valid',
                    'pending' => in_array($status, ['pending_review', 'pending']),
                    'not_uploaded' => in_array($status, ['not_uploaded', 'required']),
                    'expired' => $status === 'expired',
                    'rejected' => $status === 'rejected',
                    default => true,
                };
            });
        }
        
        return $filtered;
    }
    
    /**
     * Reset filters
     */
    public function resetFilters()
    {
        $this->searchQuery = '';
        $this->statusFilter = 'all';
        $this->typeFilter = 'all';
    }

    public function render()
    {
        return view('livewire.client.panel.document-tab');
    }
}