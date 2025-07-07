<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Http\UploadedFile;
// use Guava\FilamentModalRelationManagers\Concerns\CanBeEmbeddedInModals;

class ClientDocumentsRelationManager extends RelationManager
{
    // use CanBeEmbeddedInModals;

    protected static string $relationship = 'clientDocuments';

    protected static ?string $title = 'Legal Documents';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // This form is not used since we use custom Action
                Forms\Components\Placeholder::make('info')
                    ->label('Information')
                    ->content('This form is not used. Please use the "Upload Documents" button above.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('original_filename')
                    ->label('Document Name')
                    ->searchable()
                    ->sortable()
                    ->description(function ($record) {
                        $extension = strtolower(pathinfo($record->file_path, PATHINFO_EXTENSION));
                        $type = match($extension) {
                            'pdf' => 'PDF Document',
                            'jpg', 'jpeg', 'png', 'gif' => 'Image File',
                            'doc', 'docx' => 'Word Document', 
                            'xls', 'xlsx' => 'Excel Spreadsheet',
                            default => 'Document',
                        };
                        $size = $this->getFileSize($record->file_path);
                        return "{$type} • {$size}";
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('file_type')
                    ->label('Type')
                    ->state(function ($record) {
                        $extension = strtolower(pathinfo($record->file_path, PATHINFO_EXTENSION));
                        return strtoupper($extension);
                    })
                    ->badge()
                    ->color(function ($record) {
                        $extension = strtolower(pathinfo($record->file_path, PATHINFO_EXTENSION));
                        return match($extension) {
                            'pdf' => 'danger',
                            'jpg', 'jpeg', 'png', 'gif' => 'info',
                            'doc', 'docx' => 'primary',
                            'xls', 'xlsx' => 'success',
                            default => 'gray',
                        };
                    }),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Uploaded By')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Upload Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn($record) => $record->created_at->format('F d, Y \a\t H:i:s')),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->label('Filter by Uploader')
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('file_type')
                    ->label('File Type')
                    ->options([
                        'pdf' => 'PDF',
                        'image' => 'Images',
                        'document' => 'Documents',
                        'spreadsheet' => 'Spreadsheets',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!$data['value']) return $query;
                        
                        return match($data['value']) {
                            'pdf' => $query->where('file_path', 'LIKE', '%.pdf'),
                            'image' => $query->where(function($q) {
                                $q->where('file_path', 'LIKE', '%.jpg')
                                  ->orWhere('file_path', 'LIKE', '%.jpeg')
                                  ->orWhere('file_path', 'LIKE', '%.png');
                            }),
                            'document' => $query->where(function($q) {
                                $q->where('file_path', 'LIKE', '%.doc')
                                  ->orWhere('file_path', 'LIKE', '%.docx');
                            }),
                            'spreadsheet' => $query->where(function($q) {
                                $q->where('file_path', 'LIKE', '%.xls')
                                  ->orWhere('file_path', 'LIKE', '%.xlsx');
                            }),
                            default => $query,
                        };
                    }),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->headerActions([
                Tables\Actions\Action::make('upload_documents')
                    ->label('Upload Documents')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->modalHeading('Upload Client Documents')
                    ->modalDescription('Upload one or multiple documents for this client at once.')
                    ->modalWidth('xl')
                    ->form([
                        Forms\Components\FileUpload::make('documents')
                            ->label('Upload Documents')
                            ->multiple()
                            ->required()
                            ->disk('public')
                            ->directory(function () {
                                $clientName = $this->getOwnerRecord()->name;
                                $sluggedName = \Illuminate\Support\Str::slug($clientName);
                                return "clients/{$sluggedName}/Legal";
                            })
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/jpeg',
                                'image/jpg', 
                                'image/png',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                            ])
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
                            ->helperText('Upload one or multiple documents at once. Supported formats: PDF, Images, Word, Excel. Max 10MB per file, up to 10 files.')
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data): void {
                        // Handle multiple file uploads
                        if (isset($data['documents']) && is_array($data['documents'])) {
                            $uploadedCount = 0;
                            $failedCount = 0;
                            
                            foreach ($data['documents'] as $filePath) {
                                try {
                                    $this->getOwnerRecord()->clientDocuments()->create([
                                        'user_id' => auth()->id(),
                                        'file_path' => $filePath,
                                        'original_filename' => $this->getOriginalFileName($filePath),
                                    ]);
                                    $uploadedCount++;
                                } catch (\Exception $e) {
                                    $failedCount++;
                                    // Log error if needed
                                    \Log::error('Failed to save document: ' . $e->getMessage(), [
                                        'file_path' => $filePath,
                                        'client_id' => $this->getOwnerRecord()->id,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }
                            
                            if ($uploadedCount > 0) {
                                Notification::make()
                                    ->title('Documents uploaded successfully')
                                    ->body($uploadedCount === 1 
                                        ? "1 document has been uploaded." 
                                        : "{$uploadedCount} documents have been uploaded."
                                    )
                                    ->success()
                                    ->send();
                            }
                            
                            if ($failedCount > 0) {
                                Notification::make()
                                    ->title('Some uploads failed')
                                    ->body("{$failedCount} document(s) failed to upload. Please check the logs.")
                                    ->warning()
                                    ->send();
                            }
                        } else {
                            Notification::make()
                                ->title('No documents selected')
                                ->body('Please select at least one document to upload.')
                                ->warning()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->label('Preview')
                    ->color('info')
                    ->modalContent(fn($record) => view('filament.modals.document-preview', ['record' => $record]))
                    ->modalWidth('7xl') // Much larger modal
                    ->slideOver() // Use slide-over for full screen feel
                    ->visible(fn($record) => $this->isPreviewable($record->file_path)),

                Tables\Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('Download')
                    ->color('success')
                    ->action(function ($record) {
                        $filePath = Storage::disk('public')->path($record->file_path);
                        $originalName = $record->original_filename ?? basename($record->file_path);
                        
                        if (!file_exists($filePath)) {
                            Notification::make()
                                ->title('File not found')
                                ->body('The requested file could not be found.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        return response()->download($filePath, $originalName);
                    }),

                Tables\Actions\EditAction::make()
                    ->visible(false), // Hide edit for now since we're handling multiple files

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you want to delete this document? This action cannot be undone.')
                    ->after(function ($record) {
                        // Delete the actual file from storage
                        Storage::disk('public')->delete($record->file_path);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Are you sure you want to delete these documents? This action cannot be undone.')
                        ->after(function ($records) {
                            // Delete the actual files from storage
                            foreach ($records as $record) {
                                Storage::disk('public')->delete($record->file_path);
                            }
                        }),
                    
                    Tables\Actions\BulkAction::make('download_zip')
                        ->label('Download as ZIP')
                        ->icon('heroicon-o-archive-box-arrow-down')
                        ->color('primary')
                        ->action(function ($records) {
                            return $this->downloadAsZip($records);
                        })
                        ->requiresConfirmation()
                        ->modalDescription('This will create a ZIP file containing all selected documents.')
                ]),
            ])
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateHeading('No documents uploaded')
            ->emptyStateDescription('Upload legal documents for this client using the button above.');
    }

    /**
     * Generate unique filename while preserving original name
     */
    private function generateUniqueFileName(UploadedFile $file): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        
        // Clean the filename (remove special characters, spaces, etc.)
        $cleanName = \Illuminate\Support\Str::slug($originalName);
        
        // Add timestamp to ensure uniqueness (optional, you can remove this if you want to keep original names)
        $timestamp = now()->format('Ymd_His');
        
        // Option 1: With timestamp for uniqueness
        return "{$cleanName}_{$timestamp}.{$extension}";
        
        // Option 2: Keep original name (uncomment this and comment above if you prefer)
        // return "{$cleanName}.{$extension}";
    }

    /**
     * Get original filename from path
     */
    private function getOriginalFileName(string $filePath): string
    {
        $basename = basename($filePath);
        
        // Remove timestamp pattern if exists (for option 1)
        $pattern = '/^(.+)_\d{8}_\d{6}(\..+)$/';
        if (preg_match($pattern, $basename, $matches)) {
            return $matches[1] . $matches[2];
        }
        
        return $basename;
    }

    /**
     * Get client folder path
     */
    private function getClientFolderPath(): string
    {
        $clientName = $this->getOwnerRecord()->name;
        $sluggedName = \Illuminate\Support\Str::slug($clientName);
        return "clients/{$sluggedName}/Legal";
    }

    /**
     * Get file type icon
     */
    private function getFileTypeIcon(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        return match($extension) {
            'pdf' => '<i class="fas fa-file-pdf text-red-500"></i>',
            'jpg', 'jpeg', 'png', 'gif' => '<i class="fas fa-file-image text-blue-500"></i>',
            'doc', 'docx' => '<i class="fas fa-file-word text-blue-600"></i>',
            'xls', 'xlsx' => '<i class="fas fa-file-excel text-green-600"></i>',
            default => '<i class="fas fa-file text-gray-500"></i>',
        };
    }

    /**
     * Check if file is previewable
     */
    private function isPreviewable(string $filePath): bool
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return in_array($extension, ['pdf', 'jpg', 'jpeg', 'png']);
    }

    /**
     * Get file size
     */
    private function getFileSize(string $filePath): string
    {
        try {
            $size = Storage::disk('public')->size($filePath);
            return $this->formatBytes($size);
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Format file size
     */
    private function formatFileSize(string $filePath): string
    {
        return $this->getFileSize($filePath);
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Download selected records as ZIP
     */
    private function downloadAsZip($records)
    {
        $zip = new \ZipArchive();
        $clientName = $this->getOwnerRecord()->name;
        $sluggedClientName = \Illuminate\Support\Str::slug($clientName);
        $zipFileName = "{$sluggedClientName}-legal-documents-" . now()->format('Y-m-d-H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);
        
        // Ensure temp directory exists
        if (!file_exists(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }
        
        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            foreach ($records as $record) {
                $filePath = Storage::disk('public')->path($record->file_path);
                $originalName = $record->original_filename ?? basename($record->file_path);
                
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, $originalName);
                }
            }
            $zip->close();
            
            return response()->download($zipPath, $zipFileName)->deleteFileAfterSend();
        }
        
        Notification::make()
            ->title('Error creating ZIP file')
            ->body('Unable to create ZIP archive. Please try again.')
            ->danger()
            ->send();
    }
}