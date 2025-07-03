<?php

namespace App\Filament\Resources\TaxReportResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Collection;
// Import our new services
use App\Services\ClientTypeService;
use App\Services\TaxCalculationService;
use App\Services\FileManagementService;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';
    protected static ?string $title = 'PPN';

    /**
     * Generate dynamic directory path for file uploads
     */
    private function generateDirectoryPath($get): string
    {
        $taxReportId = $get('tax_report_id') ?? $this->getOwnerRecord()->id;
        $taxReport = \App\Models\TaxReport::with('client')->find($taxReportId);
        
        return FileManagementService::generateInvoiceDirectoryPath($taxReport);
    }

    /**
     * Generate filename with invoice type and number
     */
    private function generateFileName($get, $originalFileName): string
    {
        $invoiceType = $get('type') ?? 'Unknown Type';
        $invoiceNumber = $get('invoice_number') ?? 'Unknown Number';
        
        return FileManagementService::generateInvoiceFileName($invoiceType, $invoiceNumber, $originalFileName);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('created_by')
                    ->default(auth()->id()),
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('AI Assistant (Opsional)')
                        ->icon('heroicon-o-sparkles')
                        ->description('Upload faktur untuk ekstraksi data otomatis menggunakan AI')
                        ->schema([
                            Section::make('Ekstraksi Data Faktur dengan AI')
                                ->description('Upload dokumen faktur dan biarkan AI mengisi data secara otomatis')
                                ->icon('heroicon-o-cpu-chip')
                                ->collapsible()
                                ->schema([
                                    Grid::make(1)
                                        ->schema([
                                            FileUpload::make('ai_upload_file')
                                                ->label('Upload Faktur untuk AI')
                                                ->placeholder('Pilih file faktur (PDF atau gambar)')
                                                ->disk('public')
                                                ->directory('temp/ai-processing')
                                                ->acceptedFileTypes(FileManagementService::getAcceptedFileTypes())
                                                ->maxSize(FileManagementService::getMaxFileSize())
                                                ->helperText('Format yang didukung: PDF, JPG, PNG, WEBP (Maksimal 10MB)')
                                                ->live()
                                                ->afterStateUpdated(function (Forms\Set $set, $state) {
                                                    if ($state) {
                                                        $set('ai_output', '');
                                                        $set('ai_processing_status', 'ready');
                                                    }
                                                })
                                                ->dehydrated(false)
                                                ->columnSpanFull(),
                                                
                                            Forms\Components\Hidden::make('ai_processing_status')
                                                ->dehydrated(false)
                                                ->default('idle'),
                                                
                                            Forms\Components\Actions::make([
                                                Forms\Components\Actions\Action::make('process_with_ai')
                                                    ->label('Proses dengan AI')
                                                    ->icon('heroicon-o-cpu-chip')
                                                    ->color('primary')
                                                    ->size('lg')
                                                    ->disabled(function (Forms\Get $get) {
                                                        $file = $get('ai_upload_file');
                                                        $status = $get('ai_processing_status');
                                                        return empty($file) || $status === 'processing';
                                                    })
                                                    ->action(function (Forms\Get $get, Forms\Set $set) {
                                                        $file = $get('ai_upload_file');
                                                        
                                                        if (!$file) {
                                                            Notification::make()
                                                                ->title('File Diperlukan')
                                                                ->body('Silakan upload file faktur terlebih dahulu.')
                                                                ->warning()
                                                                ->send();
                                                            return;
                                                        }
                                                        
                                                        $this->processInvoiceWithAI($file, $get, $set);
                                                    })
                                                    ->button()
                                                    ->extraAttributes(['class' => 'w-full justify-center']),
                                            ])
                                            ->columnSpanFull()
                                            ->alignCenter(),
                                            
                                            Forms\Components\Placeholder::make('ai_output')
                                                ->label('Hasil Ekstraksi AI')
                                                ->content(function (Forms\Get $get) {
                                                    $output = $get('ai_output');
                                                    $status = $get('ai_processing_status');
                                                    $extractedDataJson = $get('ai_extracted_data');
                                                    
                                                    // Parse extracted data if available
                                                    $data = null;
                                                    $error = null;
                                                    
                                                    if ($extractedDataJson) {
                                                        $data = json_decode($extractedDataJson, true);
                                                    }
                                                    
                                                    // Handle error status
                                                    if ($status === 'error' && $output) {
                                                        if (strpos($output, '❌ **Error:**') !== false) {
                                                            $error = str_replace(['❌ **Error:**', '*'], '', $output);
                                                        } else {
                                                            $error = $output;
                                                        }
                                                    }
                                                    
                                                    return view('components.tax-reports.ai-result-display', [
                                                        'status' => $status ?: 'idle',
                                                        'data' => $data,
                                                        'error' => $error,
                                                        'output' => $output
                                                    ]);
                                                })
                                                ->columnSpanFull()
                                                ->dehydrated(false),
                                                
                                            Forms\Components\Hidden::make('ai_output')
                                                ->default('')
                                                ->dehydrated(false),
                                                
                                            Forms\Components\Actions::make([
                                                Forms\Components\Actions\Action::make('apply_ai_data')
                                                    ->label('Terapkan Data AI ke Form')
                                                    ->icon('heroicon-o-arrow-right')
                                                    ->color('success')
                                                    ->size('lg')
                                                    ->visible(fn (Forms\Get $get) => $get('ai_processing_status') === 'completed')
                                                    ->action(function (Forms\Get $get, Forms\Set $set) {
                                                        $this->applyAIDataToForm($get, $set);
                                                        
                                                        Notification::make()
                                                            ->title('Data Berhasil Diterapkan')
                                                            ->body('Data hasil ekstraksi AI telah diterapkan ke form.')
                                                            ->success()
                                                            ->send();
                                                    })
                                                    ->button()
                                                    ->extraAttributes(['class' => 'w-full justify-center']),
                                            ])
                                            ->columnSpanFull()
                                            ->alignCenter(),
                                        ]),
                                ]),
                        ]),
                        
                    Forms\Components\Wizard\Step::make('Informasi Dasar')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Section::make('Informasi Faktur Pajak')
                                ->columns(12)
                                ->schema([
                                    Forms\Components\TextInput::make('invoice_number')
                                        ->label('Nomor Faktur')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255)
                                        ->placeholder('010.000-00.00000000')
                                        ->helperText('Format: 010.000-00.00000000')
                                        ->columnSpan(6)
                                        ->live(debounce: 500)
                                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                            if ($state && strlen($state) >= 2) {
                                                $clientTypeData = ClientTypeService::getClientTypeFromInvoiceNumber($state);
                                                $set('client_type', $clientTypeData['type']);
                                                $set('has_ppn', $clientTypeData['has_ppn']);
                                            }
                                        }),
                                        
                                    Forms\Components\DatePicker::make('invoice_date')
                                        ->label('Tanggal Faktur')
                                        ->required()
                                        ->native(false)
                                        ->default(now())
                                        ->columnSpan(6),
                                        
                                    Forms\Components\Select::make('client_type')
                                        ->label('Tipe Client')
                                        ->options(ClientTypeService::getClientTypeOptions())
                                        ->required()
                                        ->native(false)
                                        ->disabled()
                                        ->helperText('Otomatis terdeteksi dari 2 digit awal nomor faktur')
                                        ->columnSpan(8),
                                        
                                    Forms\Components\Toggle::make('has_ppn')
                                        ->label('Subject PPN')
                                        ->disabled()
                                        ->helperText('Otomatis terdeteksi berdasarkan tipe client')
                                        ->columnSpan(4),
                                        
                                    Forms\Components\Select::make('type')
                                        ->label('Jenis Faktur')
                                        ->native(false)
                                        ->options([
                                            'Faktur Keluaran' => 'Faktur Keluaran',
                                            'Faktur Masuk' => 'Faktur Masuk',
                                        ])
                                        ->required()
                                        ->reactive()
                                        ->live(debounce: 500)
                                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                            if ($state === 'Faktur Masuk') {
                                                $taxReportId = $get('tax_report_id') ?? $this->getOwnerRecord()->id;
                                                $taxReport = \App\Models\TaxReport::with('client')->find($taxReportId);
                                                
                                                if ($taxReport && $taxReport->client) {
                                                    $set('company_name', $taxReport->client->name);
                                                    $set('npwp', $taxReport->client->NPWP);
                                                }
                                            }
                                        })
                                        ->columnSpan(12),
                                        
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
                                    Forms\Components\Select::make('ppn_percentage')
                                        ->label('Tarif PPN')
                                        ->options(TaxCalculationService::getPPNPercentageOptions())
                                        ->default('11')
                                        ->native(false)
                                        ->required()
                                        ->live(debounce: 500)
                                        ->helperText('Pilih tarif PPN yang berlaku')
                                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                            if ($state === '11') {
                                                $set('dpp_nilai_lainnya', '0.00');
                                            } else {
                                                $set('dpp', '0.00');
                                            }
                                            $set('ppn', '0.00');
                                        })
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('dpp_nilai_lainnya')
                                        ->label('DPP Nilai Lainnya')
                                        ->required(fn (Forms\Get $get) => $get('ppn_percentage') === '12')
                                        ->prefix('Rp')
                                        ->placeholder('0.00')
                                        ->mask(RawJs::make('$money($input)'))
                                        ->dehydrateStateUsing(fn ($state) => TaxCalculationService::cleanMonetaryInput($state))
                                        ->default('0.00')
                                        ->helperText('Nilai DPP untuk perhitungan pajak 12%')
                                        ->visible(fn (Forms\Get $get) => $get('ppn_percentage') === '12')
                                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                            $this->calculateFromDppNilaiLainnya($get, $set, $state);
                                        })
                                        ->live(2000)
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('dpp')
                                        ->label(function (Forms\Get $get) {
                                            return $get('ppn_percentage') === '12' 
                                                ? 'DPP (Dihitung Otomatis)' 
                                                : 'DPP (Dasar Pengenaan Pajak)';
                                        })
                                        ->required()
                                        ->prefix('Rp')
                                        ->placeholder('0.00')
                                        ->mask(RawJs::make('$money($input)'))
                                        ->dehydrateStateUsing(fn ($state) => TaxCalculationService::cleanMonetaryInput($state))
                                        ->rules(['required'])
                                        ->readOnly(fn (Forms\Get $get) => $get('ppn_percentage') === '12')
                                        ->helperText(function (Forms\Get $get) {
                                            return $get('ppn_percentage') === '12' 
                                                ? 'Otomatis dihitung dari DPP Nilai Lainnya × 12/11'
                                                : 'Masukkan nilai DPP';
                                        })
                                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                            if ($get('ppn_percentage') === '11') {
                                                $this->calculatePPNFromDpp($get, $set, $state);
                                            }
                                        })
                                        ->live(2000),

                                    Forms\Components\TextInput::make('ppn')
                                        ->label('PPN')
                                        ->prefix('Rp')
                                        ->placeholder('0.00')
                                        ->required()
                                        ->readOnly()
                                        ->mask(RawJs::make('$money($input)'))
                                        ->dehydrateStateUsing(fn ($state) => TaxCalculationService::cleanMonetaryInput($state))
                                        ->rules(['required'])
                                        ->helperText('Otomatis terhitung sebesar 11% dari DPP'),
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
                                        ->directory(fn (Forms\Get $get) => $this->generateDirectoryPath($get))
                                        ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file, Forms\Get $get): string => $this->generateFileName($get, $file->getClientOriginalName()))
                                        ->acceptedFileTypes(FileManagementService::getAcceptedFileTypes())
                                        ->helperText(function (Forms\Get $get) {
                                            $path = $this->generateDirectoryPath($get);
                                            return "Akan disimpan di: storage/{$path}/[Jenis Faktur]-[Nomor Invoice].[ext]";
                                        })
                                        ->columnSpanFull(),
                                        
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
                                            
                                            return FileManagementService::generateBuktiSetorFileName($invoiceType, $invoiceNumber, $file->getClientOriginalName());
                                        })
                                        ->acceptedFileTypes(FileManagementService::getAcceptedFileTypes())
                                        ->helperText(function (Forms\Get $get) {
                                            $path = $this->generateDirectoryPath($get);
                                            return "Akan disimpan di: storage/{$path}/Bukti-Setor/";
                                        })
                                        ->columnSpanFull(),
                                        
                                    Forms\Components\RichEditor::make('notes')
                                        ->label('Catatan')
                                        ->placeholder('Tambahkan catatan relevan tentang faktur ini')
                                        ->toolbarButtons([
                                            'blockquote', 'bold', 'bulletList', 'h2', 'h3', 
                                            'italic', 'link', 'orderedList', 'redo', 'strike', 'undo',
                                        ])
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ])
                ->skippable()
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
                    }),

                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Nomor Faktur')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('client_type')
                    ->label('Tipe Client')
                    ->colors([
                        'success' => 'Swasta',
                        'info' => 'Pemerintah', 
                        'warning' => 'BUMN',
                        'danger' => 'Swasta (SKB)',
                    ])
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('has_ppn')
                    ->label('PPN')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn ($record) => $record->has_ppn ? 'Subject PPN' : 'Tidak subject PPN'),
                    
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
                    
                Tables\Columns\TextColumn::make('ppn_percentage')
                    ->label('Tarif PPN')
                    ->suffix('%')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        '11' => 'success',
                        '12' => 'warning',
                        null => 'gray',
                        default => 'gray',
                    })
                    ->getStateUsing(fn ($record) => $record->ppn_percentage ?? '11')
                    ->sortable(),

                Tables\Columns\TextColumn::make('dpp_nilai_lainnya')
                    ->label('DPP Nilai Lainnya')
                    ->money('Rp.')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn ($record) => ($record->ppn_percentage ?? '11') === '12')
                    ->tooltip('DPP Nilai Lainnya - untuk tarif 12%'),

                Tables\Columns\TextColumn::make('dpp')
                    ->label('DPP')
                    ->money('Rp.')
                    ->sortable()
                    ->description(function ($record) {
                        if (($record->ppn_percentage ?? '11') === '12' && ($record->dpp_nilai_lainnya ?? 0) > 0) {
                            return 'Dihitung dari DPP Nilai Lainnya';
                        }
                        return null;
                    }),

                Tables\Columns\TextColumn::make('ppn')
                    ->label('PPN')
                    ->money('Rp.')
                    ->summarize(Sum::make()->label('Total PPN')->money('Rp.'))
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('has_bukti_setor')
                    ->label('Bukti Setor')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->bukti_setor))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn ($record) => !empty($record->bukti_setor) ? "Bukti setor tersedia" : "Bukti setor belum diupload"),
                    
                Tables\Columns\IconColumn::make('has_bupots')
                    ->label('Bukti Potong')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->bupots()->count() > 0)
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(function ($record) {
                        $count = $record->bupots()->count();
                        return $count > 0 
                            ? "Faktur ini memiliki {$count} bukti potong terkait"
                            : "Faktur ini tidak memiliki bukti potong";
                    }),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->groups(['type'])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis Faktur')
                    ->options([
                        'Faktur Keluaran' => 'Faktur Keluaran',
                        'Faktur Masuk' => 'Faktur Masuk',
                    ]),
                    
                Tables\Filters\SelectFilter::make('client_type')
                    ->label('Tipe Client')
                    ->options(ClientTypeService::getClientTypeOptions()),

                Tables\Filters\Filter::make('has_ppn')
                    ->label('Subject PPN')
                    ->query(fn (Builder $query): Builder => $query->where('has_ppn', true)),

                Tables\Filters\Filter::make('no_ppn')
                    ->label('Tidak Subject PPN')
                    ->query(fn (Builder $query): Builder => $query->where('has_ppn', false)),
                    
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
                    ->label('Ekspor Semua ke Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $taxReport = $this->getOwnerRecord();
                        $monthYear = FileManagementService::convertToIndonesianMonth($taxReport->month) . '_' . date('Y');
                        $filename = 'Faktur_' . $monthYear . '.xlsx';
                        
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\TaxReportInvoicesExport($taxReport), // No selection = export all
                            $filename
                        );
                    })
                    ->tooltip('Ekspor semua faktur ke format Excel'),                                    
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
                        $taxReport = $this->getOwnerRecord();
                        
                        if ($taxReport) {
                            $client = \App\Models\Client::find($taxReport->client_id);
                            return !($client && $client->ppn_contract);
                        }
                        
                        return true;
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
                                                $taxReport = $record->taxReport;
                                                return FileManagementService::generateBuktiSetorDirectoryPath($taxReport);
                                            })
                                            ->acceptedFileTypes(FileManagementService::getAcceptedFileTypes())
                                            ->helperText('Unggah dokumen bukti setor pajak (PDF atau gambar)')
                                            ->columnSpanFull(),
                                    ])
                            ];
                        })
                        ->action(function ($record, array $data) {
                            $record->update(['bukti_setor' => $data['bukti_setor']]);
                            
                            Notification::make()
                                ->title('Bukti Setor Berhasil Diupload')
                                ->body('Bukti setor untuk faktur ' . $record->invoice_number . ' berhasil diupload.')
                                ->success()
                                ->send();
                        })
                        ->modalWidth('2xl'),
                    
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
                    Tables\Actions\BulkAction::make('export_selected')
                        ->label('Ekspor Terpilih ke Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $taxReport = $this->getOwnerRecord();
                            $selectedIds = $records->pluck('id')->toArray();
                            $monthYear = FileManagementService::convertToIndonesianMonth($taxReport->month) . '_' . date('Y');
                            $filename = 'Faktur_Terpilih_' . $monthYear . '.xlsx';
                            
                            return \Maatwebsite\Excel\Facades\Excel::download(
                                new \App\Exports\TaxReportInvoicesExport($taxReport, $selectedIds),
                                $filename
                            );
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Ekspor Faktur Terpilih')
                        ->modalDescription(function (Collection $records) {
                            $count = $records->count();
                            return "Apakah Anda yakin ingin mengekspor {$count} faktur yang terpilih ke Excel?";
                        })
                        ->modalSubmitActionLabel('Ya, Ekspor')
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus')
                        ->modalHeading('Hapus Faktur Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus faktur yang terpilih? Tindakan ini tidak dapat dibatalkan.'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Faktur Pajak')
            ->emptyStateDescription('Faktur pajak untuk laporan pajak ini akan muncul di sini. Tambahkan faktur masukan dan keluaran untuk melacak PPN.')
            ->emptyStateIcon('heroicon-o-document-duplicate');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    /**
     * Process invoice with AI using the AI Service
     */
    private function processInvoiceWithAI($file, Forms\Get $get, Forms\Set $set)
    {
        try {
            $set('ai_processing_status', 'processing');
            $set('ai_output', 'Sedang memproses dokumen dengan AI...');
            
            $taxReportId = $get('tax_report_id') ?? $this->getOwnerRecord()->id;
            $taxReport = \App\Models\TaxReport::with('client')->find($taxReportId);
            
            $clientName = 'unknown-client';
            $monthName = 'unknown-month';
            
            if ($taxReport && $taxReport->client) {
                $clientName = Str::slug($taxReport->client->name);
                $monthName = FileManagementService::convertToIndonesianMonth($taxReport->month);
            }
            
            $aiService = new \App\Services\InvoiceAIService();
            $result = $aiService->processInvoice($file, $clientName, $monthName);
            
            $output = $aiService->formatOutput($result);
            $set('ai_output', $output);
            
            if ($result['success'] && !$result['debug']) {
                $set('ai_extracted_data', json_encode($result['data']));
                $set('ai_processing_status', 'completed');
                
                Notification::make()
                    ->title('AI Processing Selesai')
                    ->body('Data faktur berhasil diekstrak. Silakan tinjau hasil dan terapkan ke form.')
                    ->success()
                    ->send();
            } elseif ($result['debug']) {
                $set('ai_processing_status', 'completed');
                
                Notification::make()
                    ->title('Debug Mode Aktif')
                    ->body('Menampilkan informasi debug. Periksa response structure.')
                    ->warning()
                    ->send();
            } else {
                $set('ai_processing_status', 'error');
                
                Notification::make()
                    ->title('Error AI Processing')
                    ->body('Terjadi kesalahan: ' . $result['error'])
                    ->danger()
                    ->send();
            }
            
        } catch (\Exception $e) {
            $set('ai_processing_status', 'error');
            $set('ai_output', '❌ **Error:** ' . $e->getMessage());
            
            Notification::make()
                ->title('Error AI Processing')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Apply AI extracted data to form fields
     */
    private function applyAIDataToForm(Forms\Get $get, Forms\Set $set)
    {
        $extractedDataJson = $get('ai_extracted_data');
        
        if (!$extractedDataJson) {
            Notification::make()
                ->title('Tidak Ada Data')
                ->body('Tidak ada data AI yang tersimpan untuk diterapkan.')
                ->warning()
                ->send();
            return;
        }
        
        $data = json_decode($extractedDataJson, true);
        
        if (!$data) {
            Notification::make()
                ->title('Data Tidak Valid')
                ->body('Data AI yang tersimpan tidak valid.')
                ->warning()
                ->send();
            return;
        }
        
        // Apply extracted data to form fields
        foreach ($data as $field => $value) {
            if ($field === 'ppn_percentage') {
                $set($field, $value);
            } elseif ($field === 'invoice_number') {
                $set($field, $value);
                // Auto-detect client type from invoice number
                if ($value && strlen($value) >= 2) {
                    $clientTypeData = ClientTypeService::getClientTypeFromInvoiceNumber($value);
                    $set('client_type', $clientTypeData['type']);
                    $set('has_ppn', $clientTypeData['has_ppn']);
                }
            } elseif (in_array($field, ['dpp', 'dpp_nilai_lainnya', 'ppn'])) {
                // Don't set these yet, we'll handle them after percentage is set
                continue;
            } else {
                $set($field, $value);
            }
        }
        
        // Handle DPP fields based on percentage
        $ppnPercentage = $data['ppn_percentage'] ?? '11';
        
        if ($ppnPercentage === '12') {
            // For 12%, take the AI's DPP value and put it in DPP Nilai Lainnya field
            if (isset($data['dpp'])) {
                $set('dpp_nilai_lainnya', TaxCalculationService::formatCurrency($data['dpp']));
                $this->calculateFromDppNilaiLainnya($get, $set, $data['dpp']);
            }
        } else {
            // For 11%, set DPP directly and calculate PPN
            if (isset($data['dpp'])) {
                $set('dpp', TaxCalculationService::formatCurrency($data['dpp']));
                $this->calculatePPNFromDpp($get, $set, $data['dpp']);
            }
            $set('dpp_nilai_lainnya', '0.00');
        }
        
        $set('ai_extracted_data', '');
        
        Notification::make()
            ->title('Data Diterapkan')
            ->body('Data AI berhasil diterapkan ke form.')
            ->success()
            ->send();
    }

    /**
     * Calculate DPP and PPN from DPP Nilai Lainnya (when PPN is 12%)
     */
    private function calculateFromDppNilaiLainnya(Forms\Get $get, Forms\Set $set, ?string $state): void
    {
        $dppNilaiLainnya = TaxCalculationService::cleanMonetaryInput($state);
        
        if ($dppNilaiLainnya > 0) {
            $result = TaxCalculationService::calculateFromDppNilaiLainnya($dppNilaiLainnya);
            $set('dpp', $result['dpp_formatted']);
            $set('ppn', $result['ppn_formatted']);
        } else {
            $set('dpp', '0.00');
            $set('ppn', '0.00');
        }
    }

    /**
     * Calculate PPN from DPP (when PPN is 11%)
     */
    private function calculatePPNFromDpp(Forms\Get $get, Forms\Set $set, ?string $state): void
    {
        $dpp = TaxCalculationService::cleanMonetaryInput($state);
        
        if ($dpp > 0) {
            $result = TaxCalculationService::calculatePPNFromDpp($dpp);
            $set('ppn', $result['ppn_formatted']);
        } else {
            $set('ppn', '0.00');
        }
    }
}