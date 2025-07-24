<?php

// app/Livewire/UserDetail/UserSubmittedDocuments.php

namespace App\Livewire\UserDetail;

use App\Models\SubmittedDocument;
use App\Models\User;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UserDetail\UserSubmittedDocumentsExport;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class UserSubmittedDocuments extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SubmittedDocument::query()
                    ->where('user_id', $this->user->id)
                    ->with(['requiredDocument.projectStep.project.client', 'user'])
            )
            ->headerActions([
                Action::make('export_excel')
                    ->label('Export ke Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->form([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('start_date')
                                    ->label('Tanggal & Waktu Mulai')
                                    ->placeholder('Pilih tanggal dan waktu mulai')
                                    ->default(now()->subMonth()->startOfDay())
                                    ->maxDate(now())
                                    ->seconds(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->timezone(config('app.timezone'))
                                    ->required(),
                                    
                                DateTimePicker::make('end_date')
                                    ->label('Tanggal & Waktu Akhir')
                                    ->placeholder('Pilih tanggal dan waktu akhir')
                                    ->default(now()->endOfDay())
                                    ->maxDate(now())
                                    ->seconds(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->timezone(config('app.timezone'))
                                    ->required()
                                    ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                        $startDate = $get('start_date');
                                        if ($startDate && $state && $state < $startDate) {
                                            $set('end_date', $startDate);
                                            Notification::make()
                                                ->title('Waktu tidak valid')
                                                ->body('Tanggal dan waktu akhir tidak boleh kurang dari tanggal dan waktu mulai.')
                                                ->warning()
                                                ->send();
                                        }
                                    }),
                            ])
                    ])
                    ->action(function (array $data): \Symfony\Component\HttpFoundation\BinaryFileResponse {
                        return $this->exportToExcel($data['start_date'], $data['end_date']);
                    })
                    ->modalHeading('Export Dokumen ke Excel')
                    ->modalDescription('Pilih rentang tanggal dan waktu untuk dokumen yang ingin diekspor')
                    ->modalSubmitActionLabel('Export Excel')
                    ->modalCancelActionLabel('Batal')
                    ->tooltip('Download laporan dalam format Excel'),
            ])
            ->columns([
                TextColumn::make('file_name')
                    ->label('Nama File')
                    ->state(fn (SubmittedDocument $record): string => pathinfo(basename($record->file_path), PATHINFO_FILENAME))
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->wrap(),

                TextColumn::make('file_type')
                    ->label('Tipe File')
                    ->state(fn (SubmittedDocument $record): string => strtoupper(pathinfo(basename($record->file_path), PATHINFO_EXTENSION)))
                    ->badge()
                    ->icon(fn (SubmittedDocument $record): string => match(strtolower(pathinfo(basename($record->file_path), PATHINFO_EXTENSION))) {
                        'pdf' => 'heroicon-o-document-text',
                        'doc', 'docx' => 'heroicon-o-document',
                        'xls', 'xlsx' => 'heroicon-o-table-cells',
                        'ppt', 'pptx' => 'heroicon-o-presentation-chart-line',
                        'jpg', 'jpeg', 'png', 'gif', 'bmp' => 'heroicon-o-photo',
                        'zip', 'rar', '7z' => 'heroicon-o-archive-box',
                        'txt' => 'heroicon-o-document-text',
                        'csv' => 'heroicon-o-table-cells',
                        default => 'heroicon-o-document'
                    })
                    ->color(fn (SubmittedDocument $record): string => match(strtolower(pathinfo(basename($record->file_path), PATHINFO_EXTENSION))) {
                        'pdf' => 'danger',
                        'doc', 'docx' => 'primary',
                        'xls', 'xlsx' => 'success',
                        'ppt', 'pptx' => 'warning',
                        'jpg', 'jpeg', 'png', 'gif', 'bmp' => 'info',
                        'zip', 'rar', '7z' => 'purple',
                        'txt' => 'gray',
                        'csv' => 'emerald',
                        default => 'secondary'
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('requiredDocument.projectStep.project.client.name')
                    ->label('Klien')
                    ->searchable()
                    ->sortable()
                    ->limit(25),

                TextColumn::make('requiredDocument.projectStep.name')
                    ->label('Tahap Proyek')
                    ->searchable()
                    ->sortable()
                    ->limit(25),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'uploaded' => 'Diunggah',
                        'pending_review' => 'Menunggu Review',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => ucfirst($state)
                    })
                    ->colors([
                        'info' => 'uploaded',
                        'warning' => 'pending_review',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->icons([
                        'heroicon-o-arrow-up-tray' => 'uploaded',
                        'heroicon-o-clock' => 'pending_review',
                        'heroicon-o-check-circle' => 'approved',
                        'heroicon-o-x-circle' => 'rejected',
                    ])
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Waktu Dikirim')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'uploaded' => 'Diunggah',
                        'pending_review' => 'Menunggu Review',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->multiple(),

                SelectFilter::make('file_type')
                    ->label('Tipe File')
                    ->options([
                        'pdf' => 'PDF',
                        'doc' => 'DOC',
                        'docx' => 'DOCX',
                        'xls' => 'XLS',
                        'xlsx' => 'XLSX',
                        'ppt' => 'PPT',
                        'pptx' => 'PPTX',
                        'jpg' => 'JPG',
                        'jpeg' => 'JPEG',
                        'png' => 'PNG',
                        'gif' => 'GIF',
                        'txt' => 'TXT',
                        'csv' => 'CSV',
                        'zip' => 'ZIP',
                        'rar' => 'RAR',
                        '7z' => '7Z',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['values'])) {
                            return $query;
                        }
                        
                        return $query->where(function (Builder $query) use ($data) {
                            foreach ($data['values'] as $extension) {
                                $query->orWhere('file_path', 'like', '%.'. $extension);
                            }
                        });
                    })
                    ->multiple(),

                Filter::make('created_at_range')
                    ->label('Waktu Upload')
                    ->form([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('start_date')
                                    ->label('Dari Tanggal & Waktu')
                                    ->placeholder('Pilih tanggal dan waktu mulai')
                                    ->seconds(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->timezone(config('app.timezone'))
                                    ->maxDate(now()),
                                    
                                DateTimePicker::make('end_date')
                                    ->label('Sampai Tanggal & Waktu')
                                    ->placeholder('Pilih tanggal dan waktu akhir')
                                    ->seconds(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->timezone(config('app.timezone'))
                                    ->maxDate(now())
                                    ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                        $startDate = $get('start_date');
                                        if ($startDate && $state && $state < $startDate) {
                                            $set('end_date', $startDate);
                                            Notification::make()
                                                ->title('Waktu tidak valid')
                                                ->body('Tanggal dan waktu akhir tidak boleh kurang dari tanggal dan waktu mulai.')
                                                ->warning()
                                                ->send();
                                        }
                                    }),
                            ])
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn (Builder $query, $date): Builder => $query->where('created_at', '>=', $date),
                            )
                            ->when(
                                $data['end_date'],
                                fn (Builder $query, $date): Builder => $query->where('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['start_date'] ?? null) {
                            $indicators['start_date'] = 'Dari: ' . date('d M Y H:i', strtotime($data['start_date']));
                        }
                        
                        if ($data['end_date'] ?? null) {
                            $indicators['end_date'] = 'Sampai: ' . date('d M Y H:i', strtotime($data['end_date']));
                        }
                        
                        return $indicators;
                    }),

                SelectFilter::make('project')
                    ->label('Proyek')
                    ->relationship('requiredDocument.projectStep.project', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('client')
                    ->label('Klien')
                    ->relationship('requiredDocument.projectStep.project.client', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat Detail')
                        ->modalHeading('Detail Dokumen')
                        ->modalContent(fn (SubmittedDocument $record): View => view(
                            'filament.modals.submitted-document-details',
                            ['record' => $record]
                        ))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup'),

                    Action::make('download')
                        ->label('Unduh File')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(fn (SubmittedDocument $record) => $this->downloadDocument($record)),

                    Action::make('update_status')
                        ->label('Perbarui Status')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->visible(fn (): bool => auth()->user()->can('update_document_status'))
                        ->form([
                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'uploaded' => 'Diunggah',
                                    'pending_review' => 'Menunggu Review',
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak',
                                ])
                                ->required()
                                ->default(fn (SubmittedDocument $record): string => $record->status),

                            Textarea::make('notes')
                                ->label('Catatan')
                                ->placeholder('Tambahkan catatan tentang perubahan status ini...')
                                ->rows(3),

                            Textarea::make('rejection_reason')
                                ->label('Alasan Penolakan')
                                ->placeholder('Berikan alasan penolakan...')
                                ->visible(fn (callable $get): bool => $get('status') === 'rejected')
                                ->required(fn (callable $get): bool => $get('status') === 'rejected')
                                ->rows(3),
                        ])
                        ->action(function (SubmittedDocument $record, array $data): void {
                            $record->update([
                                'status' => $data['status'],
                                'notes' => $data['notes'] ?? null,
                                'rejection_reason' => $data['rejection_reason'] ?? null,
                            ]);

                            // Update the required document status if needed
                            if ($data['status'] === 'approved') {
                                $record->requiredDocument->update(['status' => 'approved']);
                            } elseif ($data['status'] === 'rejected') {
                                $record->requiredDocument->update(['status' => 'rejected']);
                            }

                            Notification::make()
                                ->title('Status dokumen berhasil diperbarui')
                                ->success()
                                ->send();
                        }),
                ])
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button()
            ])
            ->bulkActions([
                BulkAction::make('export_selected')
                    ->label('Export Terpilih ke Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->form([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('start_date')
                                    ->label('Tanggal & Waktu Mulai (Opsional)')
                                    ->placeholder('Filter berdasarkan tanggal dan waktu mulai')
                                    ->maxDate(now())
                                    ->seconds(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->timezone(config('app.timezone')),
                                    
                                DateTimePicker::make('end_date')
                                    ->label('Tanggal & Waktu Akhir (Opsional)')
                                    ->placeholder('Filter berdasarkan tanggal dan waktu akhir')
                                    ->maxDate(now())
                                    ->seconds(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->timezone(config('app.timezone'))
                                    ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                        $startDate = $get('start_date');
                                        if ($startDate && $state && $state < $startDate) {
                                            $set('end_date', $startDate);
                                            Notification::make()
                                                ->title('Waktu tidak valid')
                                                ->body('Tanggal dan waktu akhir tidak boleh kurang dari tanggal dan waktu mulai.')
                                                ->warning()
                                                ->send();
                                        }
                                    }),
                            ])
                    ])
                    ->action(function (Collection $records, array $data): \Symfony\Component\HttpFoundation\BinaryFileResponse {
                        return $this->exportSelectedDocuments($records, $data['start_date'] ?? null, $data['end_date'] ?? null);
                    })
                    ->modalHeading('Export Dokumen Terpilih')
                    ->modalDescription('Export dokumen yang dipilih. Anda dapat menambahkan filter tanggal dan waktu tambahan.')
                    ->modalSubmitActionLabel('Export Excel')
                    ->modalCancelActionLabel('Batal')
                    ->deselectRecordsAfterCompletion(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->poll('30s') // Auto-refresh every 30 seconds
            ->emptyStateHeading('Tidak Ada Dokumen yang Dikirim')
            ->emptyStateDescription('Pengguna ini belum mengirim dokumen apapun.')
            ->emptyStateIcon('heroicon-o-document');
    }

     public function exportToExcel($startDate = null, $endDate = null)
    {
        $dateRangeText = '';
        if ($startDate && $endDate) {
            $dateRangeText = '_' . date('Y-m-d_H-i', strtotime($startDate)) . '_sampai_' . date('Y-m-d_H-i', strtotime($endDate));
        }
        
        $fileName = 'Laporan_Dokumen_' . str_replace(' ', '_', $this->user->name) . $dateRangeText . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new UserSubmittedDocumentsExport($this->user, $startDate, $endDate), $fileName);
    }

    public function exportSelectedDocuments(Collection $records, $startDate = null, $endDate = null)
    {
        $recordIds = $records->pluck('id')->toArray();
        
        $dateRangeText = '';
        if ($startDate && $endDate) {
            $dateRangeText = '_' . date('Y-m-d_H-i', strtotime($startDate)) . '_sampai_' . date('Y-m-d_H-i', strtotime($endDate));
        }
        
        $fileName = 'Laporan_Dokumen_Terpilih_' . str_replace(' ', '_', $this->user->name) . $dateRangeText . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new UserSubmittedDocumentsExport($this->user, $startDate, $endDate, $recordIds), $fileName);
    }

    public function downloadDocument(SubmittedDocument $document)
    {    
        // Check if file exists
        if (!Storage::exists($document->file_path)) {
            Notification::make()
                ->title('File Tidak Ditemukan')
                ->body('File yang diminta tidak tersedia di server.')
                ->danger()
                ->send();
            return;
        }
        
        // Get the full path to the file
        $fullPath = Storage::path($document->file_path);
        
        // Get original filename from path
        $originalFileName = basename($document->file_path);
        
        // Return file download response
        return response()->download($fullPath, $originalFileName, [
            'Content-Type' => Storage::mimeType($document->file_path),
        ]);
    }

    public function render(): View
    {
        return view('livewire.user-detail.user-submitted-documents');
    }
}