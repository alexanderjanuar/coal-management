<?php

namespace App\Filament\Resources\TaxReportResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Swis\Filament\Activitylog\Tables\Actions\ActivitylogAction;
use Filament\Notifications\Notification;

use Filament\Forms\Components\FileUpload;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    protected static ?string $title = 'PPN';


    /**
     * Generate dynamic directory path for file uploads
     */
    private function generateDirectoryPath($get): string
    {
        // Get tax report to access client information
        $taxReportId = $get('tax_report_id') ?? $this->getOwnerRecord()->id;
        $taxReport = \App\Models\TaxReport::with('client')->find($taxReportId);
        
        // Default values
        $clientName = 'unknown-client';
        $monthName = 'unknown-month';
        
        if ($taxReport && $taxReport->client) {
            // Clean client name for folder structure
            $clientName = Str::slug($taxReport->client->name);
            
            // Convert month from tax report to Indonesian month name
            $monthName = $this->convertToIndonesianMonth($taxReport->month);
        }
        
        return "clients/{$clientName}/SPT/{$monthName}/Invoice";
    }

    /**
     * Generate filename with invoice type and number
     */
    private function generateFileName($get, $originalFileName): string
    {
        $invoiceType = $get('type') ?? 'Unknown Type';
        $invoiceNumber = $get('invoice_number') ?? 'Unknown Number';
        
        // Clean invoice number for filename (remove special characters)
        $cleanInvoiceNumber = Str::slug($invoiceNumber);
        
        // Get file extension
        $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        
        return "{$invoiceType}-{$cleanInvoiceNumber}.{$extension}";
    }

    /**
     * Convert month format to Indonesian month names
     */
    private function convertToIndonesianMonth($month): string
    {
        // Handle different month formats
        $monthNames = [
            '01' => 'Januari', '1' => 'Januari', 'january' => 'Januari', 'jan' => 'Januari',
            '02' => 'Februari', '2' => 'Februari', 'february' => 'Februari', 'feb' => 'Februari',
            '03' => 'Maret', '3' => 'Maret', 'march' => 'Maret', 'mar' => 'Maret',
            '04' => 'April', '4' => 'April', 'april' => 'April', 'apr' => 'April',
            '05' => 'Mei', '5' => 'Mei', 'may' => 'Mei',
            '06' => 'Juni', '6' => 'Juni', 'june' => 'Juni', 'jun' => 'Juni',
            '07' => 'Juli', '7' => 'Juli', 'july' => 'Juli', 'jul' => 'Juli',
            '08' => 'Agustus', '8' => 'Agustus', 'august' => 'Agustus', 'aug' => 'Agustus',
            '09' => 'September', '9' => 'September', 'september' => 'September', 'sep' => 'September',
            '10' => 'Oktober', 'october' => 'Oktober', 'oct' => 'Oktober',
            '11' => 'November', 'november' => 'November', 'nov' => 'November',
            '12' => 'Desember', 'december' => 'Desember', 'dec' => 'Desember',
        ];

        $cleanMonth = strtolower(trim($month));
        
        // If it's a date format like "2025-01", extract the month part
        if (preg_match('/\d{4}-(\d{1,2})/', $month, $matches)) {
            $cleanMonth = $matches[1];
        }
        
        return $monthNames[$cleanMonth] ?? Str::title($cleanMonth);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('created_by')
                    ->default(auth()->id()),
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Informasi Dasar')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Section::make('Informasi Faktur Pajak')
                                ->columns(12) // Using 12-column grid for finer control
                                ->schema([
                                    // First row - Invoice number and date
                                    Forms\Components\TextInput::make('invoice_number')
                                        ->label('Nomor Faktur')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255)
                                        ->placeholder('010.000-00.00000000')
                                        ->helperText('Format: 010.000-00.00000000')
                                        ->columnSpan(6)
                                        ->live(debounce: 500), // Add live update for filename generation
                                        
                                    Forms\Components\DatePicker::make('invoice_date')
                                        ->label('Tanggal Faktur')
                                        ->required()
                                        ->native(false)
                                        ->default(now())
                                        ->columnSpan(6),
                                        
                                    // Second row - Invoice type with reactive behavior
                                    Forms\Components\Select::make('type')
                                        ->label('Jenis Faktur')
                                        ->native(false)
                                        ->options([
                                            'Faktur Keluaran' => 'Faktur Keluaran',
                                            'Faktur Masuk' => 'Faktur Masuk',
                                        ])
                                        ->required()
                                        ->reactive()
                                        ->live(debounce: 500) // Add live update for filename generation
                                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                            if ($state === 'Faktur Masuk') {
                                                // Get the tax report and its client information
                                                $taxReportId = $get('tax_report_id') ?? $this->getOwnerRecord()->id;
                                                $taxReport = \App\Models\TaxReport::with('client')->find($taxReportId);
                                                
                                                if ($taxReport && $taxReport->client) {
                                                    $set('company_name', $taxReport->client->name);
                                                    $set('npwp', $taxReport->client->NPWP);
                                                }
                                            }
                                        })
                                        ->columnSpan(12),
                                        
                                    // Third row - Company and NPWP
                                    Forms\Components\TextInput::make('company_name')
                                        ->label('Nama Perusahaan')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(6),
                                        
                                    Forms\Components\TextInput::make('npwp')
                                        ->label('NPWP')
                                        ->required()
                                        ->placeholder('00.000.000.0-000.000')
                                        ->helperText('Format: 00.000.000.0-000.000')
                                        ->maxLength(255)
                                        ->columnSpan(6),
                                ]),
                        ]),
                    
                    Forms\Components\Wizard\Step::make('Rincian Keuangan')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            Section::make('Detail Perpajakan')
                                ->columns(2)
                                ->schema([
                                    Forms\Components\TextInput::make('dpp')
                                        ->label('DPP (Dasar Pengenaan Pajak)')
                                        ->required()
                                        ->prefix('Rp')
                                        ->placeholder('0.00')
                                        ->mask(RawJs::make('$money($input)'))
                                        // Convert to numeric value for storage
                                        ->dehydrateStateUsing(fn ($state) => preg_replace('/[^0-9.]/', '', $state))
                                        // This is key - don't use numeric() validator with masked inputs
                                        ->rules(['required'])
                                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                            // Clean the input by removing non-numeric characters except decimal point
                                            $cleanedInput = preg_replace('/[^0-9.]/', '', $state);
                                            
                                            if (is_numeric($cleanedInput)) {
                                                // Get the PPN percentage from the select field
                                                $ppnPercentage = $get('ppn_percentage') === '12' ? 0.12 : 0.11;
                                                $ppn = floatval($cleanedInput) * $ppnPercentage;
                                                $set('ppn', number_format($ppn, 2, '.', ','));
                                            }
                                        })                                       
                                        ->live(2000),

                                    // New field for PPN percentage selection
                                    Forms\Components\Select::make('ppn_percentage')
                                        ->label('Tarif PPN')
                                        ->options([
                                            '11' => '11%',
                                            '12' => '12%',
                                        ])
                                        ->default('11')
                                        ->native(false)
                                        ->required()
                                        ->live(debounce: 500)
                                        ->helperText('Pilih tarif PPN yang berlaku')
                                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                            $dpp = $get('dpp');
                                            
                                            // Clean the input by removing non-numeric characters except decimal point
                                            $cleanedInput = preg_replace('/[^0-9.]/', '', $dpp);
                                            
                                            if (is_numeric($cleanedInput)) {
                                                // Calculate PPN based on selected percentage
                                                $ppnPercentage = $state === '12' ? 0.12 : 0.11;
                                                $ppn = floatval($cleanedInput) * $ppnPercentage;
                                                $set('ppn', number_format($ppn, 2, '.', ','));
                                            }
                                        }),

                                    Forms\Components\TextInput::make('ppn')
                                        ->label('PPN')
                                        ->prefix('Rp')
                                        ->placeholder('0.00')
                                        ->required()
                                        ->readOnly()
                                        ->mask(RawJs::make('$money($input)'))
                                        // Convert to numeric value for storage
                                        ->dehydrateStateUsing(fn ($state) => preg_replace('/[^0-9.]/', '', $state))
                                        // This is key - don't use numeric() validator with masked inputs
                                        ->rules(['required'])
                                        ->helperText(function (Forms\Get $get) {
                                            $percentage = $get('ppn_percentage') === '12' ? '12%' : '11%';
                                            return "Otomatis terhitung sebesar {$percentage} dari DPP";
                                        }),
                                ]),
                        ]),
                    
                    Forms\Components\Wizard\Step::make('Dokumen & Catatan')
                        ->icon('heroicon-o-paper-clip')
                        ->schema([
                            Section::make('Dokumen Pendukung')
                                ->schema([
                                    FileUpload::make('file_path')
                                        ->label('Berkas Faktur')
                                        ->openable()
                                        ->downloadable()
                                        ->disk('public')
                                        ->directory(function (Forms\Get $get) {
                                            return $this->generateDirectoryPath($get);
                                        })
                                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, Forms\Get $get): string {
                                            return $this->generateFileName($get, $file->getClientOriginalName());
                                        })
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                        ->helperText(function (Forms\Get $get) {
                                            $path = $this->generateDirectoryPath($get);
                                            return "Akan disimpan di: storage/{$path}/[Jenis Faktur]-[Nomor Invoice].[ext]";
                                        })
                                        ->columnSpanFull(),
                                        
                                    // New field for bukti setor (optional)
                                    FileUpload::make('bukti_setor')
                                        ->label('Bukti Setor (Opsional)')
                                        ->openable()
                                        ->downloadable()
                                        ->disk('public')
                                        ->directory(function (Forms\Get $get) {
                                            $basePath = $this->generateDirectoryPath($get);
                                            return $basePath . '/Bukti-Setor';
                                        })
                                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, Forms\Get $get): string {
                                            $invoiceType = $get('type') ?? 'Unknown Type';
                                            $invoiceNumber = $get('invoice_number') ?? 'Unknown Number';
                                            $cleanInvoiceNumber = Str::slug($invoiceNumber);
                                            $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                                            
                                            return "Bukti-Setor-{$invoiceType}-{$cleanInvoiceNumber}.{$extension}";
                                        })
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                        ->helperText(function (Forms\Get $get) {
                                            $path = $this->generateDirectoryPath($get);
                                            return "Akan disimpan di: storage/{$path}/Bukti-Setor/";
                                        })
                                        ->columnSpanFull(),
                                        
                                    Forms\Components\RichEditor::make('notes')
                                        ->label('Catatan')
                                        ->placeholder('Tambahkan catatan relevan tentang faktur ini')
                                        ->toolbarButtons([
                                            'blockquote',
                                            'bold',
                                            'bulletList',
                                            'h2',
                                            'h3',
                                            'italic',
                                            'link',
                                            'orderedList',
                                            'redo',
                                            'strike',
                                            'undo',
                                        ])
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ])
                ->skippable() // Allowing steps to be skipped
                ->persistStepInQueryString('invoice-wizard-step')
                ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('company_name')
            ->columns([
                Tables\Columns\ImageColumn::make('user_avatar')
                    ->label('Dibuat Oleh')
                    ->circular()
                    ->state(function ($record) {
                        // If we have a created_by value
                        if ($record->created_by) {
                            $user = \App\Models\User::find($record->created_by);
                            if ($user && method_exists($user, 'getAvatarUrl')) {
                                return $user->getAvatarUrl();
                            }
                        }
                        return null;
                    })
                    ->defaultImageUrl(asset('images/default-avatar.png'))
                    ->size(40)
                    ->tooltip(function ($record): string {
                        if ($record->created_by) {
                            $user = \App\Models\User::find($record->created_by);
                            return $user ? $user->name : 'User #' . $record->created_by;
                        }
                        return 'No System';
                    })
                    ->defaultImageUrl(asset('images/default-avatar.png'))
                    ->size(40),

                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Nomor Faktur')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Nama Perusahaan')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Jenis Faktur')
                    ->colors([
                        'success' => 'Faktur Keluaran',
                        'warning' => 'Faktur Masuk',
                    ]),
                    
                Tables\Columns\TextColumn::make('dpp')
                    ->label('DPP')
                    ->money('Rp.')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('ppn')
                    ->label('PPN')
                    ->money('Rp.')
                    ->sortable(),
                    
                // New column for bukti setor
                Tables\Columns\IconColumn::make('has_bukti_setor')
                    ->label('Bukti Setor')
                    ->boolean()
                    ->getStateUsing(function ($record) {
                        return !empty($record->bukti_setor);
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(function ($record) {
                        if (!empty($record->bukti_setor)) {
                            return "Bukti setor tersedia";
                        }
                        
                        return "Bukti setor belum diupload";
                    }),
                    
                // Existing column for bupots
                Tables\Columns\IconColumn::make('has_bupots')
                    ->label('Bukti Potong')
                    ->boolean()
                    ->getStateUsing(function ($record) {
                        return $record->bupots()->count() > 0;
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(function ($record) {
                        $count = $record->bupots()->count();
                        
                        if ($count > 0) {
                            return "Faktur ini memiliki {$count} bukti potong terkait";
                        }
                        
                        return "Faktur ini tidak memiliki bukti potong";
                    }),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis Faktur')
                    ->options([
                        'Faktur Keluaran' => 'Faktur Keluaran',
                        'Faktur Masuk' => 'Faktur Masuk',
                    ]),
                    
                // New filter for bukti setor
                Tables\Filters\Filter::make('has_bukti_setor')
                    ->label('Memiliki Bukti Setor')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('bukti_setor')->where('bukti_setor', '!=', '')),
                    
                Tables\Filters\Filter::make('no_bukti_setor')
                    ->label('Belum Ada Bukti Setor')
                    ->query(fn (Builder $query): Builder => $query->where(function ($q) {
                        $q->whereNull('bukti_setor')->orWhere('bukti_setor', '');
                    })),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_all')
                    ->label('Ekspor ke Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $taxReport = $this->getOwnerRecord();
                        $monthYear = $this->convertToIndonesianMonth($taxReport->month) . '_' . date('Y');
                        $filename = 'Faktur_' . $monthYear . '.xlsx';
                        
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\TaxReportInvoicesExport($taxReport),
                            $filename
                        );
                    })
                    ->tooltip('Ekspor semua faktur ke format Excel'),
                Tables\Actions\Action::make('invoice_tax_status')
                    ->label(function () {
                        $taxReport = $this->getOwnerRecord();
                        $status = $taxReport->invoice_tax_status ?? 'Belum Ditentukan';
                        
                        // Map status values to display text
                        $statusMap = [
                            'Lebih Bayar' => 'Lebih Bayar',
                            'kurang_bayar' => 'Kurang Bayar',
                            'nihil' => 'Nihil',
                            'belum_ditentukan' => 'Belum Ditentukan'
                        ];
                        
                        return 'Status: ' . ($statusMap[$status] ?? $status);
                    })
                    ->color(function () {
                        $taxReport = $this->getOwnerRecord();
                        $status = $taxReport->invoice_tax_status ?? 'belum_ditentukan';
                        
                        // Color mapping based on status
                        return match($status) {
                            'Lebih Bayar' => 'success',
                            'Kurang Bayar' => 'danger', 
                            'Nihil' => 'warning',
                            default => 'gray'
                        };
                    })
                    ->icon(function () {
                        $taxReport = $this->getOwnerRecord();
                        $status = $taxReport->invoice_tax_status ?? 'belum_ditentukan';
                        
                        // Icon mapping based on status
                        return match($status) {
                            'Lebih Bayar' => 'heroicon-o-arrow-trending-up',
                            'Kurang Bayar' => 'heroicon-o-arrow-trending-down',
                            'nihil' => 'heroicon-o-minus-circle',
                            default => 'heroicon-o-question-mark-circle'
                        };
                    })
                    ->disabled(true)
                    ->tooltip(function () {
                        $taxReport = $this->getOwnerRecord();
                        $status = $taxReport->invoice_tax_status ?? 'belum_ditentukan';
                        
                        // Tooltip explanations
                        return match($status) {
                            'Lebih Bayar' => 'Pajak yang dibayar lebih besar dari kewajiban pajak',
                            'Kurang Bayar' => 'Pajak yang dibayar kurang dari kewajiban pajak',
                            'nihil' => 'Tidak ada kewajiban pajak atau sudah seimbang',
                            default => 'Status laporan pajak belum ditentukan'
                        };
                 }),
                Tables\Actions\CreateAction::make()
                    ->label('Faktur Baru')
                    ->successNotificationTitle('Faktur berhasil dibuat')
                    ->modalWidth('7xl')
                    ->tooltip(function () {
                        $taxReport = $this->getOwnerRecord();
                        
                        if ($taxReport) {
                            $client = \App\Models\Client::find($taxReport->client_id);
                            
                            if (!$client || !$client->ppn_contract) {
                                return 'Klien tidak memiliki kontrak PPN aktif. Aktifkan kontrak PPN terlebih dahulu.';
                            }
                        }
                        
                        return 'Tambah Faktur Pajak Baru';
                    })
                    ->disabled(function () {
                        // Get the tax report first
                        $taxReport = $this->getOwnerRecord();
                        
                        // If we have a tax report, check the client's contract status
                        if ($taxReport) {
                            $client = \App\Models\Client::find($taxReport->client_id);
                            
                            // Disable the button if the client doesn't have an active ppn_contract
                            return !($client && $client->ppn_contract);
                        }
                        
                        return true; // Disable if no tax report is found
                    })
                    ->before(function (array $data) {
                        // Get the tax report
                        $taxReport = $this->getOwnerRecord();
                        
                        if ($taxReport) {
                            // Double-check the client's contract status as a safeguard
                            $client = \App\Models\Client::find($taxReport->client_id);
                            if (!$client || !$client->ppn_contract) {
                                // Use notification
                                Notification::make()
                                    ->title('Kontrak PPN Tidak Aktif')
                                    ->body('Klien tidak memiliki kontrak PPN aktif. Aktifkan kontrak PPN terlebih dahulu.')
                                    ->danger()
                                    ->send();
                                
                                // Throw validation exception to stop the process
                                throw new \Illuminate\Validation\ValidationException(
                                    validator: validator([], []),
                                    response: response()->json([
                                        'message' => 'Klien tidak memiliki kontrak PPN aktif.',
                                    ], 422)
                                );
                            }
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat')
                        ->modalWidth('7xl'),
                        
                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->modalWidth('7xl'),
                    
                    // New action for uploading bukti setor
                    Tables\Actions\Action::make('upload_bukti_setor')
                        ->label('Upload Bukti Setor')
                        ->icon('heroicon-o-cloud-arrow-up')
                        ->color('info')
                        ->visible(fn ($record) => empty($record->bukti_setor))
                        ->form(function ($record) {
                            return [
                                Section::make('Upload Bukti Setor Pajak')
                                    ->description('Upload dokumen bukti setor untuk faktur ini')
                                    ->schema([
                                        FileUpload::make('bukti_setor')
                                            ->label('Bukti Setor')
                                            ->required()
                                            ->openable()
                                            ->downloadable()
                                            ->disk('public')
                                            ->directory(function () use ($record) {
                                                // Generate path for existing record
                                                $taxReport = $record->taxReport;
                                                $clientName = Str::slug($taxReport->client->name);
                                                $monthName = $this->convertToIndonesianMonth($taxReport->month);
                                                return "clients/{$clientName}/SPT/{$monthName}/Invoice/Bukti-Setor";
                                            })
                                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) use ($record): string {
                                                $cleanInvoiceNumber = Str::slug($record->invoice_number);
                                                $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                                                return "Bukti-Setor-{$record->type}-{$cleanInvoiceNumber}.{$extension}";
                                            })
                                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                            ->helperText('Unggah dokumen bukti setor pajak (PDF atau gambar)')
                                            ->columnSpanFull(),
                                    ])
                            ];
                        })
                        ->action(function ($record, array $data) {
                            $record->update([
                                'bukti_setor' => $data['bukti_setor']
                            ]);
                            
                            Notification::make()
                                ->title('Bukti Setor Berhasil Diupload')
                                ->body('Bukti setor untuk faktur ' . $record->invoice_number . ' berhasil diupload.')
                                ->success()
                                ->send();
                        })
                        ->modalWidth('2xl'),
                    
                    // Action to view/download bukti setor
                    Tables\Actions\Action::make('view_bukti_setor')
                        ->label('Lihat Bukti Setor')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->visible(fn ($record) => !empty($record->bukti_setor))
                        ->url(fn ($record) => asset('storage/' . $record->bukti_setor))
                        ->openUrlInNewTab()
                        ->tooltip('Lihat bukti setor pajak'),
                        
                    Tables\Actions\Action::make('download')
                        ->label('Unduh Berkas')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn ($record) => $record->file_path ? asset('storage/' . $record->file_path) : null)
                        ->openUrlInNewTab()
                        ->visible(fn ($record) => $record->file_path)
                        ->tooltip('Unduh berkas faktur pajak'),
                        
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->modalHeading('Hapus Faktur')
                        ->modalDescription('Apakah Anda yakin ingin menghapus faktur ini? Tindakan ini tidak dapat dibatalkan.'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus')
                        ->modalHeading('Hapus Faktur Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus faktur yang terpilih? Tindakan ini tidak dapat dibatalkan.'),
                        
                    Tables\Actions\BulkAction::make('export')
                        ->label('Ekspor ke Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(fn () => null) // Implement export functionality here
                        ->requiresConfirmation()
                        ->modalHeading('Ekspor Faktur')
                        ->modalDescription('Apakah Anda yakin ingin mengekspor faktur yang terpilih?')
                        ->modalSubmitActionLabel('Ya, Ekspor'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Faktur Pajak')
            ->emptyStateDescription('Faktur pajak untuk laporan pajak ini akan muncul di sini. Tambahkan faktur masukan dan keluaran untuk melacak PPN.')
            ->emptyStateIcon('heroicon-o-document-duplicate')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Faktur Pajak')
                    ->modalWidth('7xl')
                    ->icon('heroicon-o-plus')
                    ->tooltip(function () {
                        $taxReport = $this->getOwnerRecord();
                        
                        if ($taxReport) {
                            $client = \App\Models\Client::find($taxReport->client_id);
                            
                            if (!$client || !$client->ppn_contract) {
                                return 'Klien tidak memiliki kontrak PPN aktif. Aktifkan kontrak PPN terlebih dahulu.';
                            }
                        }
                        
                        return 'Tambah Faktur Pajak';
                    })
                    ->disabled(function () {
                        // Get the tax report
                        $taxReport = $this->getOwnerRecord();
                        
                        // If we have a tax report, check the client's contract status
                        if ($taxReport) {
                            $client = \App\Models\Client::find($taxReport->client_id);
                            
                            // Disable the button if the client doesn't have an active ppn_contract
                            return !($client && $client->ppn_contract);
                        }
                        
                        return true; // Disable if no tax report is found
                    }),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}