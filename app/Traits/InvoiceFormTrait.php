<?php

namespace App\Traits;

use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\FileUpload;
use Filament\Support\RawJs;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Services\ClientTypeService;
use App\Services\TaxCalculationService;
use App\Services\FileManagementService;

trait InvoiceFormTrait
{
    /**
     * Get the complete invoice form schema
     */
    public function getInvoiceFormSchema(bool $isRevision = false, $originalRecord = null): array
    {
        $schema = [
            Forms\Components\Hidden::make('created_by')
                ->default(auth()->id()),
        ];

        // Add revision fields if this is a revision
        if ($isRevision) {
            $schema = array_merge($schema, [
                Forms\Components\Hidden::make('is_revision')
                    ->default(true),
                Forms\Components\Hidden::make('original_invoice_id'),
                Forms\Components\Hidden::make('revision_number')
                    ->default(0),
            ]);
        }

        $wizardSteps = [];

        // Add AI Assistant step for new invoices (not revisions)
        if (!$isRevision) {
            $wizardSteps[] = $this->getAIAssistantStep();
        }

        // Add revision info step for revisions
        if ($isRevision) {
            $wizardSteps[] = $this->getRevisionInfoStep();
        }

        // Add common steps
        $wizardSteps = array_merge($wizardSteps, [
            $this->getBasicInfoStep($isRevision),
            $this->getFinancialDetailsStep(),
            $this->getDocumentsStep(),
        ]);

        $schema[] = Forms\Components\Wizard::make($wizardSteps)
            ->skippable()
            ->persistStepInQueryString('invoice-wizard-step')
            ->columnSpanFull();

        return $schema;
    }

    /**
     * Get AI Assistant step
     */
    private function getAIAssistantStep(): Forms\Components\Wizard\Step
    {
        return Forms\Components\Wizard\Step::make('AI Assistant (Opsional)')
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
                                                \Filament\Notifications\Notification::make()
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
                                        
                                        $data = null;
                                        $error = null;
                                        
                                        if ($extractedDataJson) {
                                            $data = json_decode($extractedDataJson, true);
                                        }
                                        
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
                                            
                                            \Filament\Notifications\Notification::make()
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
            ]);
    }

    /**
     * Get revision info step
     */
    private function getRevisionInfoStep(): Forms\Components\Wizard\Step
    {
        return Forms\Components\Wizard\Step::make('Informasi Revisi')
            ->icon('heroicon-o-arrow-path')
            ->description('Informasi tentang revisi yang akan dibuat')
            ->schema([
                Section::make('Detail Revisi')
                    ->description('Faktur ini adalah revisi dari faktur yang sudah ada')
                    ->icon('heroicon-o-arrow-path')
                    ->schema([
                        Forms\Components\Placeholder::make('original_invoice_info')
                            ->label('Faktur Asli')
                            ->content(function (Forms\Get $get) {
                                $originalId = $get('original_invoice_id');
                                if ($originalId) {
                                    $original = \App\Models\Invoice::find($originalId);
                                    if ($original) {
                                        return "Nomor: {$original->invoice_number} - {$original->company_name}";
                                    }
                                }
                                return 'Tidak ada';
                            }),
                            
                        Forms\Components\TextInput::make('revision_reason')
                            ->label('Alasan Revisi')
                            ->required(fn (Forms\Get $get) => $get('is_revision'))
                            ->placeholder('Jelaskan alasan pembuatan revisi')
                            ->helperText('Jelaskan mengapa revisi ini dibuat (misal: koreksi nilai pajak, perubahan data)')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * Get basic info step
     */
    private function getBasicInfoStep(bool $isRevision = false): Forms\Components\Wizard\Step
    {
        return Forms\Components\Wizard\Step::make('Informasi Dasar')
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
                            ->helperText($isRevision ? 'Nomor faktur revisi (dapat diedit)' : 'Format: 010.000-00.00000000')
                            ->columnSpan(6)
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) use ($isRevision) {
                                if (!$isRevision && $state && strlen($state) >= 2) {
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
                            ->helperText($isRevision ? 'Dapat diubah sesuai kebutuhan revisi' : 'Otomatis terdeteksi dari 2 digit awal nomor faktur')
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
                                if ($state === 'Faktur Masuk' && method_exists($this, 'getOwnerRecord')) {
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
            ]);
    }

    /**
     * Get financial details step
     */
    private function getFinancialDetailsStep(): Forms\Components\Wizard\Step
    {
        return Forms\Components\Wizard\Step::make('Rincian Keuangan')
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
                                if (method_exists($this, 'calculateFromDppNilaiLainnya')) {
                                    $this->calculateFromDppNilaiLainnya($get, $set, $state);
                                }
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
                                if ($get('ppn_percentage') === '11' && method_exists($this, 'calculatePPNFromDpp')) {
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
            ]);
    }

    /**
     * Get documents step
     */
    private function getDocumentsStep(): Forms\Components\Wizard\Step
    {
        return Forms\Components\Wizard\Step::make('Dokumen & Catatan')
            ->icon('heroicon-o-paper-clip')
            ->schema([
                Section::make('Dokumen Pendukung')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('Berkas Faktur')
                            ->openable()
                            ->downloadable()
                            ->disk('public')
                            ->directory(fn (Forms\Get $get) => method_exists($this, 'generateDirectoryPath') ? $this->generateDirectoryPath($get) : 'invoices')
                            ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file, Forms\Get $get): string => 
                                method_exists($this, 'generateFileName') 
                                    ? $this->generateFileName($get, $file->getClientOriginalName())
                                    : $file->getClientOriginalName()
                            )
                            ->acceptedFileTypes(FileManagementService::getAcceptedFileTypes())
                            ->helperText(function (Forms\Get $get) {
                                if (method_exists($this, 'generateDirectoryPath')) {
                                    $path = $this->generateDirectoryPath($get);
                                    return "Akan disimpan di: storage/{$path}/[Jenis Faktur]-[Nomor Invoice].[ext]";
                                }
                                return "Upload berkas faktur";
                            })
                            ->columnSpanFull(),
                            
                        FileUpload::make('bukti_setor')
                            ->label('Bukti Setor (Opsional)')
                            ->openable()
                            ->downloadable()
                            ->disk('public')
                            ->directory(function (Forms\Get $get) {
                                if (method_exists($this, 'generateDirectoryPath')) {
                                    $basePath = $this->generateDirectoryPath($get);
                                    return $basePath . '/Bukti-Setor';
                                }
                                return 'invoices/bukti-setor';
                            })
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, Forms\Get $get): string {
                                $invoiceType = $get('type') ?? 'Unknown Type';
                                $invoiceNumber = $get('invoice_number') ?? 'Unknown Number';
                                
                                return FileManagementService::generateBuktiSetorFileName($invoiceType, $invoiceNumber, $file->getClientOriginalName());
                            })
                            ->acceptedFileTypes(FileManagementService::getAcceptedFileTypes())
                            ->helperText(function (Forms\Get $get) {
                                if (method_exists($this, 'generateDirectoryPath')) {
                                    $path = $this->generateDirectoryPath($get);
                                    return "Akan disimpan di: storage/{$path}/Bukti-Setor/";
                                }
                                return "Upload bukti setor pajak";
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
            ]);
    }
}