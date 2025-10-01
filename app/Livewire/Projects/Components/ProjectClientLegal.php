<?php

namespace App\Livewire\Projects\Components;

use App\Models\Project;
use App\Models\Client;
use App\Models\ClientDocument;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Action;

class ProjectClientLegal extends Component implements HasForms, HasActions
{
    use WithFileUploads, WithPagination;
    use InteractsWithForms, InteractsWithActions;

    public Project $project;
    public Client $client;
    public $search = '';
    public $fileTypeFilter = '';
    public $uploaderFilter = '';
    public $selectedDocuments = [];
    public $previewingDocument = null;
    
    // Form data
    public ?array $data = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'fileTypeFilter' => ['except' => ''],
        'uploaderFilter' => ['except' => ''],
    ];

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->client = Client::find($project->client_id);
        
        if (!$this->client) {
            throw new \Exception('Project must have an associated client');
        }

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('documents')
                    ->label('Unggah Dokumen')
                    ->multiple()
                    ->required()
                    ->disk('public')
                    ->directory(function () {
                        $sluggedName = Str::slug($this->client->name);
                        return "clients/{$sluggedName}/Legal";
                    })
                    ->maxSize(10240) // 10MB per file
                    ->maxFiles(10)
                    ->downloadable()
                    ->previewable()
                    ->openable()
                    ->reorderable()
                    ->appendFiles()
                    ->getUploadedFileNameForStorageUsing(
                        fn (UploadedFile $file): string => $this->generateUniqueFileName($file)
                    )
                    ->helperText('Unggah satu atau beberapa dokumen sekaligus. Format yang didukung: PDF, Gambar, Word, Excel. Maksimal 10MB per file, hingga 10 file.')
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function getDocumentsProperty()
    {
        return ClientDocument::with('user')
            ->where('client_id', $this->project->client_id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('original_filename', 'like', '%' . $this->search . '%')
                      ->orWhere('file_path', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->fileTypeFilter, function ($query) {
                switch ($this->fileTypeFilter) {
                    case 'pdf':
                        $query->where('file_path', 'LIKE', '%.pdf');
                        break;
                    case 'image':
                        $query->where(function ($q) {
                            $q->where('file_path', 'LIKE', '%.jpg')
                              ->orWhere('file_path', 'LIKE', '%.jpeg')
                              ->orWhere('file_path', 'LIKE', '%.png')
                              ->orWhere('file_path', 'LIKE', '%.gif');
                        });
                        break;
                    case 'document':
                        $query->where(function ($q) {
                            $q->where('file_path', 'LIKE', '%.doc')
                              ->orWhere('file_path', 'LIKE', '%.docx');
                        });
                        break;
                    case 'spreadsheet':
                        $query->where(function ($q) {
                            $q->where('file_path', 'LIKE', '%.xls')
                              ->orWhere('file_path', 'LIKE', '%.xlsx');
                        });
                        break;
                }
            })
            ->when($this->uploaderFilter, function ($query) {
                $query->where('user_id', $this->uploaderFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getUploadersProperty()
    {
        return ClientDocument::with('user')
            ->where('client_id', $this->project->client_id)
            ->get()
            ->pluck('user')
            ->unique('id')
            ->values();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFileTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedUploaderFilter()
    {
        $this->resetPage();
    }

    public function uploadAction(): Action
    {
        return Action::make('upload')
            ->label('Unggah Dokumen')
            ->icon('heroicon-o-cloud-arrow-up')
            ->color('primary')
            ->modalHeading('Unggah Dokumen Klien')
            ->modalDescription('Unggah satu atau beberapa dokumen legal untuk klien ini.')
            ->modalWidth('2xl')
            ->form([
                FileUpload::make('documents')
                    ->label('Pilih Dokumen')
                    ->multiple()
                    ->required()
                    ->disk('public')
                    ->directory(function () {
                        $sluggedName = Str::slug($this->client->name);
                        return "clients/{$sluggedName}/Legal";
                    })
                    ->maxSize(10240) // 10MB per file
                    ->maxFiles(10)
                    ->downloadable()
                    ->previewable()
                    ->openable()
                    ->reorderable()
                    ->appendFiles()
                    ->getUploadedFileNameForStorageUsing(
                        fn (UploadedFile $file): string => $this->generateUniqueFileName($file)
                    )
                    ->helperText('Format yang didukung: PDF, Gambar, Word, Excel. Maksimal 10MB per file, hingga 10 file.')
                    ->columnSpanFull(),
            ])
            ->action(function (array $data): void {
                $this->handleDocumentUpload($data);
            });
    }

    public function previewAction(): Action
    {
        return Action::make('preview')
            ->label('Pratinjau')
            ->icon('heroicon-o-eye')
            ->color('info')
            ->modalContent(function (array $arguments) {
                $document = ClientDocument::with('user')->find($arguments['document']);
                return view('filament.modals.document-preview', ['record' => $document]);
            })
            ->modalWidth('7xl')
            ->slideOver();
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->label('Hapus')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Hapus Dokumen')
            ->modalDescription('Apakah Anda yakin ingin menghapus dokumen ini? Tindakan ini tidak dapat dibatalkan.')
            ->action(function (array $arguments): void {
                $this->handleDocumentDelete($arguments['document']);
            });
    }

    public function downloadZipAction(): Action
    {
        return Action::make('downloadZip')
            ->label('Unduh ZIP')
            ->icon('heroicon-o-archive-box-arrow-down')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Unduh sebagai ZIP')
            ->modalDescription('Ini akan membuat file ZIP yang berisi semua dokumen yang dipilih.')
            ->action(function (): void {
                $this->downloadSelectedAsZip();
            })
            ->visible(fn(): bool => !empty($this->selectedDocuments));
    }

    protected function handleDocumentUpload(array $data): void
    {
        if (isset($data['documents']) && is_array($data['documents'])) {
            $uploadedCount = 0;
            $failedCount = 0;
            
            foreach ($data['documents'] as $filePath) {
                try {
                    ClientDocument::create([
                        'client_id' => $this->project->client_id,
                        'user_id' => auth()->id(),
                        'file_path' => $filePath,
                        'original_filename' => $this->getOriginalFileName($filePath),
                    ]);
                    $uploadedCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    \Log::error('Gagal menyimpan dokumen: ' . $e->getMessage(), [
                        'file_path' => $filePath,
                        'client_id' => $this->project->client_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            if ($uploadedCount > 0) {
                Notification::make()
                    ->title('Dokumen berhasil diunggah')
                    ->body($uploadedCount === 1 
                        ? "1 dokumen telah diunggah." 
                        : "{$uploadedCount} dokumen telah diunggah."
                    )
                    ->success()
                    ->send();
            }
            
            if ($failedCount > 0) {
                Notification::make()
                    ->title('Beberapa unggahan gagal')
                    ->body("{$failedCount} dokumen gagal diunggah. Silakan cek log untuk detail.")
                    ->warning()
                    ->send();
            }

            $this->resetPage();
        } else {
            Notification::make()
                ->title('Tidak ada dokumen yang dipilih')
                ->body('Silakan pilih minimal satu dokumen untuk diunggah.')
                ->warning()
                ->send();
        }
    }

    protected function handleDocumentDelete($documentId): void
    {
        try {
            $document = ClientDocument::findOrFail($documentId);
            
            // Delete file from storage
            Storage::disk('public')->delete($document->file_path);
            
            // Delete database record
            $document->delete();
            
            Notification::make()
                ->title('Dokumen berhasil dihapus')
                ->success()
                ->send();
                
            $this->resetPage();
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal menghapus dokumen')
                ->body('Terjadi kesalahan saat menghapus dokumen.')
                ->danger()
                ->send();
        }
    }

    public function downloadDocument($documentId)
    {
        $document = ClientDocument::findOrFail($documentId);
        $filePath = Storage::disk('public')->path($document->file_path);
        $originalName = $document->original_filename ?? basename($document->file_path);
        
        if (!file_exists($filePath)) {
            Notification::make()
                ->title('File tidak ditemukan')
                ->body('File yang diminta tidak dapat ditemukan.')
                ->danger()
                ->send();
            return;
        }
        
        return response()->download($filePath, $originalName);
    }

    public function downloadSelectedAsZip()
    {
        if (empty($this->selectedDocuments)) {
            Notification::make()
                ->title('Tidak ada dokumen yang dipilih')
                ->body('Silakan pilih minimal satu dokumen untuk diunduh.')
                ->warning()
                ->send();
            return;
        }

        return $this->downloadAsZip($this->selectedDocuments);
    }

    public function toggleDocumentSelection($documentId)
    {
        if (in_array($documentId, $this->selectedDocuments)) {
            $this->selectedDocuments = array_diff($this->selectedDocuments, [$documentId]);
        } else {
            $this->selectedDocuments[] = $documentId;
        }
    }

    public function selectAllDocuments()
    {
        $this->selectedDocuments = $this->documents->pluck('id')->toArray();
    }

    public function deselectAllDocuments()
    {
        $this->selectedDocuments = [];
    }

    // Helper Methods

    private function generateUniqueFileName(UploadedFile $file): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $cleanName = Str::slug($originalName);
        $timestamp = now()->format('Ymd_His');
        
        return "{$cleanName}_{$timestamp}.{$extension}";
    }

    private function getOriginalFileName(string $filePath): string
    {
        $basename = basename($filePath);
        
        // Remove timestamp pattern if exists
        $pattern = '/^(.+)_\d{8}_\d{6}(\..+)$/';
        if (preg_match($pattern, $basename, $matches)) {
            return $matches[1] . $matches[2];
        }
        
        return $basename;
    }

    private function isPreviewable(string $filePath): bool
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return in_array($extension, ['pdf', 'jpg', 'jpeg', 'png']);
    }

    public function getFileType(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return match($extension) {
            'pdf' => 'PDF Dokumen',
            'jpg', 'jpeg', 'png', 'gif' => 'File Gambar',
            'doc', 'docx' => 'Dokumen Word',
            'xls', 'xlsx' => 'Spreadsheet Excel',
            default => 'Dokumen',
        };
    }

    public function getFileTypeColor(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return match($extension) {
            'pdf' => 'red',
            'jpg', 'jpeg', 'png', 'gif' => 'blue',
            'doc', 'docx' => 'indigo',
            'xls', 'xlsx' => 'green',
            default => 'gray',
        };
    }

    public function getFileSize(string $filePath): string
    {
        try {
            $size = Storage::disk('public')->size($filePath);
            return $this->formatBytes($size);
        } catch (\Exception $e) {
            return 'Tidak diketahui';
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function downloadAsZip(array $documentIds)
    {
        $documents = ClientDocument::whereIn('id', $documentIds)->get();
        
        if ($documents->isEmpty()) {
            Notification::make()
                ->title('Tidak ada dokumen untuk diunduh')
                ->warning()
                ->send();
            return;
        }

        $zip = new \ZipArchive();
        $sluggedClientName = Str::slug($this->client->name);
        $zipFileName = "{$sluggedClientName}-legal-documents-" . now()->format('Y-m-d-H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);
        
        if (!file_exists(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }
        
        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            foreach ($documents as $document) {
                $filePath = Storage::disk('public')->path($document->file_path);
                $originalName = $document->original_filename ?? basename($document->file_path);
                
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, $originalName);
                }
            }
            $zip->close();
            
            return response()->download($zipPath, $zipFileName)->deleteFileAfterSend();
        }
        
        Notification::make()
            ->title('Gagal membuat file ZIP')
            ->body('Tidak dapat membuat arsip ZIP. Silakan coba lagi.')
            ->danger()
            ->send();
    }

    public function render()
    {
        return view('livewire.projects.components.project-client-legal', [
            'documents' => $this->documents,
            'uploaders' => $this->uploaders,
        ]);
    }
}