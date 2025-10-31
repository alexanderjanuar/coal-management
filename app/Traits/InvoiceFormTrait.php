<?php

namespace App\Traits;

use Asmit\FilamentUpload\Enums\PdfViewFit;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\FileUpload;
use Filament\Support\RawJs;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Services\ClientTypeService;
use App\Services\TaxCalculationService;
use App\Services\FileManagementService;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;

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
        return Forms\Components\Wizard\Step::make('Upload & AI Processing')
            ->icon('heroicon-o-sparkles')
            ->description('Upload berkas faktur dan ekstraksi data otomatis menggunakan AI')
            ->schema([
                Section::make('Upload Berkas & Ekstraksi Data AI')
                    ->description('Upload dokumen faktur untuk penyimpanan dan ekstraksi data otomatis')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->schema([
                        Grid::make(2)->schema([
                            // MAIN FILE UPLOAD (Dipindahkan dari step terakhir)
                            AdvancedFileUpload::make('file_path')
                                ->label('Upload Berkas Faktur')
                                ->required()
                                ->openable()
                                ->downloadable()
                                ->required()
                                ->disk('public')
                                ->pdfPreviewHeight(600) // Customize preview height
                                ->pdfDisplayPage(1) // Set default page
                                ->pdfToolbar(true) // Enable toolbar
                                ->pdfZoomLevel(110) // Set zoom level
                                ->pdfFitType(PdfViewFit::FIT) // Set fit type
                                ->pdfNavPanes(true) // Enable navigation panes
                                ->directory(fn (Forms\Get $get) => method_exists($this, 'generateDirectoryPath') ? $this->generateDirectoryPath($get) : 'invoices')
                                ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file, Forms\Get $get): string => 
                                    method_exists($this, 'generateFileName') 
                                        ? $this->generateFileName($get, $file->getClientOriginalName())
                                        : $file->getClientOriginalName()
                                )
                                ->maxSize(10240) // 10MB
                                ->helperText('Upload berkas faktur (PDF/gambar) - akan otomatis diproses AI')
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                    if ($state) {
                                        // Auto-trigger AI processing when file is uploaded
                                        $set('ai_processing_status', 'processing');
                                        $set('ai_output', 'Sedang memproses dokumen dengan AI...');
                                        
                                        // Trigger AI processing
                                        if (method_exists($this, 'processInvoiceWithAI')) {
                                            $this->processInvoiceWithAI($state, $get, $set);
                                        }
                                    }
                                })
                                ->columnSpan(2),

                            // AI PROCESSING STATUS & OUTPUT (Using existing custom view)
                            Forms\Components\Placeholder::make('ai_processing_display')
                                ->label('Status AI Processing')
                                ->content(function (Forms\Get $get) {
                                    $status = $get('ai_processing_status') ?? 'idle';
                                    $output = $get('ai_output');
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
                                    
                                    // Use your existing custom view
                                    return view('components.tax-reports.ai-result-display', [
                                        'status' => $status,
                                        'data' => $data,
                                        'error' => $error,
                                        'output' => $output
                                    ]);
                                })
                                ->columnSpan(2),
                        ]),

                        // HIDDEN FIELDS untuk AI processing
                        Forms\Components\Hidden::make('ai_processing_status')
                            ->default('idle')
                            ->dehydrated(false),
                            
                        Forms\Components\Hidden::make('ai_output')
                            ->default('')
                            ->dehydrated(false),
                            
                        Forms\Components\Hidden::make('ai_extracted_data')
                            ->default('')
                            ->dehydrated(false),

                        // ACTION BUTTONS untuk AI
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('apply_ai_data')
                                ->label('Terapkan Data AI ke Form')
                                ->icon('heroicon-o-bolt')
                                ->color('success')
                                ->size('lg')
                                ->visible(fn (Forms\Get $get) => $get('ai_processing_status') === 'completed' && $get('ai_extracted_data'))
                                ->action(function (Forms\Get $get, Forms\Set $set) {
                                    if (method_exists($this, 'applyAIDataToForm')) {
                                        $this->applyAIDataToForm($get, $set);
                                    }
                                    
                                    \Filament\Notifications\Notification::make()
                                        ->title('Data Berhasil Diterapkan')
                                        ->body('Data hasil ekstraksi AI telah diterapkan ke form. Silakan lanjutkan ke step berikutnya.')
                                        ->success()
                                        ->send();
                                })
                                ->button()
                                ->extraAttributes(['class' => 'w-full justify-center']),
                                
                            Forms\Components\Actions\Action::make('reprocess_ai')
                                ->label('Proses Ulang dengan AI')
                                ->icon('heroicon-o-arrow-path')
                                ->color('warning')
                                ->size('lg')
                                ->visible(fn (Forms\Get $get) => $get('file_path') && $get('ai_processing_status') !== 'processing')
                                ->action(function (Forms\Get $get, Forms\Set $set) {
                                    $filePath = $get('file_path');
                                    if ($filePath && method_exists($this, 'processInvoiceWithAI')) {
                                        $set('ai_processing_status', 'processing');
                                        $set('ai_output', 'Sedang memproses ulang dokumen dengan AI...');
                                        $this->processInvoiceWithAI($filePath, $get, $set);
                                    }
                                })
                                ->button()
                                ->extraAttributes(['class' => 'w-full justify-center']),
                        ])
                        ->columnSpanFull()
                        ->alignCenter(),
                    ]),
            ]);
    }

    /**
     * Get revision info step
     */
    private function getRevisionInfoStep(): Forms\Components\Wizard\Step
    {
        return Forms\Components\Wizard\Step::make('Upload Berkas & Info Revisi')
            ->icon('heroicon-o-arrow-path')
            ->description('Upload berkas faktur revisi dan informasi revisi')
            ->schema([
                Section::make('Upload Berkas Faktur Revisi')
                    ->description('Upload berkas faktur revisi (wajib)')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->schema([
                        Grid::make(2)->schema([
                            // File upload untuk revisi
                            AdvancedFileUpload::make('file_path')
                                ->label('Upload Berkas Faktur Revisi')
                                ->required()
                                ->openable()
                                ->downloadable()
                                ->pdfPreviewHeight(600) 
                                ->pdfDisplayPage(1)
                                ->pdfToolbar(true)
                                ->pdfZoomLevel(100)
                                ->pdfFitType(PdfViewFit::FIT)
                                ->disk('public')
                                ->directory(fn (Forms\Get $get) => method_exists($this, 'generateDirectoryPath') ? $this->generateDirectoryPath($get) : 'invoices')
                                ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file, Forms\Get $get): string => 
                                    method_exists($this, 'generateFileName') 
                                        ? $this->generateFileName($get, $file->getClientOriginalName())
                                        : $file->getClientOriginalName()
                                )
                                ->maxSize(10240)
                                ->helperText('Upload berkas faktur revisi (PDF/gambar) - akan otomatis diproses AI')
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                    if ($state) {
                                        $set('ai_processing_status', 'processing');
                                        $set('ai_output', 'Sedang memproses dokumen revisi dengan AI...');
                                        
                                        if (method_exists($this, 'processInvoiceWithAI')) {
                                            $this->processInvoiceWithAI($state, $get, $set);
                                        }
                                    }
                                })
                                ->columnSpan(1),

                            // AI Processing untuk revisi
                            Forms\Components\Placeholder::make('ai_processing_display')
                                ->label('Status AI Processing')
                                ->content(function (Forms\Get $get) {
                                    $status = $get('ai_processing_status') ?? 'idle';
                                    $output = $get('ai_output');
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
                                        'status' => $status,
                                        'data' => $data,
                                        'error' => $error,
                                        'output' => $output
                                    ]);
                                })
                                ->columnSpan(1),
                        ]),

                        // Hidden fields dan action buttons sama seperti di step AI normal
                        Forms\Components\Hidden::make('ai_processing_status')->default('idle')->dehydrated(false),
                        Forms\Components\Hidden::make('ai_output')->default('')->dehydrated(false),
                        Forms\Components\Hidden::make('ai_extracted_data')->default('')->dehydrated(false),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('apply_ai_data')
                                ->label('Terapkan Data AI ke Form Revisi')
                                ->icon('heroicon-o-bolt')
                                ->color('success')
                                ->size('lg')
                                ->visible(fn (Forms\Get $get) => $get('ai_processing_status') === 'completed' && $get('ai_extracted_data'))
                                ->action(function (Forms\Get $get, Forms\Set $set) {
                                    if (method_exists($this, 'applyAIDataToForm')) {
                                        $this->applyAIDataToForm($get, $set);
                                    }
                                    
                                    \Filament\Notifications\Notification::make()
                                        ->title('Data Revisi Berhasil Diterapkan')
                                        ->body('Data hasil ekstraksi AI dari berkas revisi telah diterapkan ke form.')
                                        ->success()
                                        ->send();
                                })
                                ->button()
                                ->extraAttributes(['class' => 'w-full justify-center']),
                        ])->columnSpanFull()->alignCenter(),
                    ]),

                // Section info faktur asli dan alasan revisi tetap sama
                Section::make('Detail Revisi')
                    ->description('Informasi tentang revisi yang akan dibuat')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        // ... (kode yang sudah ada untuk original_invoice_info dan revision_reason)
                    ]),
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
                                                                               
                        Forms\Components\Select::make('is_business_related')
                            ->label('Keterkaitan Bisnis')
                            ->options([
                                true => 'Terkait Aktivitas Bisnis Utama',
                                false => 'Tidak Terkait Bisnis Utama (Personal/Non-Operasional)',
                            ])
                            ->required()
                            ->default(true)
                            ->native(false)
                            ->live()
                            ->helperText(function (Forms\Get $get) {
                                $isBusinessRelated = $get('is_business_related');
                                if ($isBusinessRelated === true) {
                                    return '✅ Faktur ini terkait dengan kegiatan operasional bisnis utama client';
                                } elseif ($isBusinessRelated === false) {
                                    return '⚠️ Faktur ini untuk keperluan personal atau non-operasional (misal: pembelian pribadi, investasi non-operasional)';
                                }
                                return 'Pilih apakah faktur ini terkait dengan aktivitas bisnis utama atau tidak';
                            })
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?bool $state) {
                                // Optional: Auto-adjust tax treatment based on business relation
                                if ($state === false) {
                                }
                            })
                            ->columnSpan(12),
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
        return Forms\Components\Wizard\Step::make('Dokumen Tambahan & Catatan')
            ->icon('heroicon-o-paper-clip')
            ->schema([
                Section::make('Dokumen Pendukung & Catatan')
                    ->description('Upload dokumen tambahan dan berikan catatan')
                    ->schema([
                        // HANYA BUKTI SETOR (file_path sudah dipindah ke step pertama atau revisi)
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
                            ->placeholder(function (Forms\Get $get) {
                                return $get('is_revision') 
                                    ? 'Tambahkan catatan tentang revisi ini dan perubahan yang dilakukan...'
                                    : 'Tambahkan catatan relevan tentang faktur ini...';
                            })
                            ->toolbarButtons([
                                'blockquote', 'bold', 'bulletList', 'h2', 'h3', 
                                'italic', 'link', 'orderedList', 'redo', 'strike', 'undo',
                            ])
                            ->columnSpanFull(),
                            
                    ]),
            ]);
    }
}