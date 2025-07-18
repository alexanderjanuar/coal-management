<?php

namespace App\Filament\Clusters\LaporanPajak\Resources;

use App\Exports\TaxReportExporter;
use App\Filament\Clusters\LaporanPajak;
use App\Filament\Clusters\LaporanPajak\Resources\InvoiceResource\Pages;
use App\Filament\Clusters\LaporanPajak\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use App\Models\TaxReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
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

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    public static ?string $label = 'PPN';

    protected static ?string $pluralLabel = 'PPN'; 

    protected static ?string $cluster = LaporanPajak::class;

    protected static ?string $navigationLabel = 'PPN';

    protected static ?string $navigationGroup = 'Jenis Pajak';

    /**
     * Generate dynamic directory path for file uploads
     */
    private static function generateDirectoryPath($get): string
    {
        $taxReportId = $get('tax_report_id');
        $taxReport = null;
        
        if ($taxReportId) {
            $taxReport = \App\Models\TaxReport::with('client')->find($taxReportId);
        }
        
        return FileManagementService::generateInvoiceDirectoryPath($taxReport);
    }

    /**
     * Generate filename with invoice type and number
     */
    private static function generateFileName($get, $originalFileName): string
    {
        $invoiceType = $get('type') ?? 'Unknown Type';
        $invoiceNumber = $get('invoice_number') ?? 'Unknown Number';
        
        return FileManagementService::generateInvoiceFileName($invoiceType, $invoiceNumber, $originalFileName);
    }

    public static function form(Form $form): Form
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
                                                        
                                                        self::processInvoiceWithAI($file, $get, $set);
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
                                                
                                            Forms\Components\Hidden::make('ai_extracted_data')
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
                                                        self::applyAIDataToForm($get, $set);
                                                        
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
                                    // Tax Report Selection - STANDALONE SPECIFIC
                                    Forms\Components\Select::make('tax_report_id')
                                        ->label('Laporan Pajak')
                                        ->required()
                                        ->relationship('taxReport', 'id')
                                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->client->name} - {$record->month}")
                                        ->searchable(['client.name', 'month'])
                                        ->preload()
                                        ->columnSpan(12)
                                        ->live(debounce: 500)
                                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                            if ($state && $get('type') === 'Faktur Masuk') {
                                                $taxReport = \App\Models\TaxReport::with('client')->find($state);
                                                
                                                if ($taxReport && $taxReport->client) {
                                                    $set('company_name', $taxReport->client->name);
                                                    $set('npwp', $taxReport->client->NPWP);
                                                }
                                            }
                                        }),
                                        
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
                                                $taxReportId = $get('tax_report_id');
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
                                            self::calculateFromDppNilaiLainnya($get, $set, $state);
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
                                                self::calculatePPNFromDpp($get, $set, $state);
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
                                        ->directory(fn (Forms\Get $get) => self::generateDirectoryPath($get))
                                        ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file, Forms\Get $get): string => self::generateFileName($get, $file->getClientOriginalName()))
                                        ->acceptedFileTypes(FileManagementService::getAcceptedFileTypes())
                                        ->helperText(function (Forms\Get $get) {
                                            $path = self::generateDirectoryPath($get);
                                            return "Akan disimpan di: storage/{$path}/[Jenis Faktur]-[Nomor Invoice].[ext]";
                                        })
                                        ->columnSpanFull(),
                                        
                                    FileUpload::make('bukti_setor')
                                        ->label('Bukti Setor (Opsional)')
                                        ->openable()
                                        ->downloadable()
                                        ->disk('public')
                                        ->directory(function (Forms\Get $get) {
                                            $basePath = self::generateDirectoryPath($get);
                                            return $basePath . '/Bukti-Setor';
                                        })
                                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, Forms\Get $get): string {
                                            $invoiceType = $get('type') ?? 'Unknown Type';
                                            $invoiceNumber = $get('invoice_number') ?? 'Unknown Number';
                                            
                                            return FileManagementService::generateBuktiSetorFileName($invoiceType, $invoiceNumber, $file->getClientOriginalName());
                                        })
                                        ->acceptedFileTypes(FileManagementService::getAcceptedFileTypes())
                                        ->helperText(function (Forms\Get $get) {
                                            $path = self::generateDirectoryPath($get);
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
                                        
                                    Forms\Components\Toggle::make('nihil')
                                        ->label('Nihil')
                                        ->helperText('Centang jika faktur ini nihil')
                                        ->default(false),
                                ]),
                        ]),
                ])
                ->skippable()
                ->persistStepInQueryString('invoice-wizard-step')
                ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                // Get tax reports with preloaded aggregated invoice data
                \App\Models\TaxReport::query()
                    ->with(['client'])
                    ->withSum([
                        'invoices as ppn_keluar_sum' => function ($query) {
                            $query->where('type', 'Faktur Keluaran')
                                ->where('invoice_number', 'NOT LIKE', '02%')
                                ->where('invoice_number', 'NOT LIKE', '03%')
                                ->where('invoice_number', 'NOT LIKE', '07%')
                                ->where('invoice_number', 'NOT LIKE', '08%');
                        }
                    ], 'ppn')
                    ->withSum([
                        'invoices as ppn_masuk_sum' => function ($query) {
                            $query->where('type', 'Faktur Masuk');
                        }
                    ], 'ppn')
                    ->withSum([
                        'invoices as peredaran_bruto_sum' => function ($query) {
                            $query->where('type', 'Faktur Keluaran');
                        }
                    ], 'dpp')
                    ->withCount([
                        'invoices as total_invoices'
                    ])
                    ->withCount([
                        'invoices as invoices_keluar_count' => function ($query) {
                            $query->where('type', 'Faktur Keluaran');
                        }
                    ])
                    ->withCount([
                        'invoices as invoices_masuk_count' => function ($query) {
                            $query->where('type', 'Faktur Masuk');
                        }
                    ])
                    ->withCount([
                        'invoices as invoices_with_bukti_setor' => function ($query) {
                            $query->whereNotNull('bukti_setor')->where('bukti_setor', '!=', '');
                        }
                    ])
                    ->withCount([
                        'invoices as nihil_invoices' => function ($query) {
                            $query->where('nihil', 1);
                        }
                    ])
            )
            ->columns([
                // Tax Report Information
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->badge()
                    ->color('indigo')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => "NPWP: " . ($record->client->NPWP ?? 'N/A')),

                // Invoice Counts
                Tables\Columns\TextColumn::make('total_invoices')
                    ->label('Total Faktur')
                    ->badge()
                    ->color('gray')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoices_keluar_count')
                    ->label('Faktur Keluaran')
                    ->badge()
                    ->color('success')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoices_masuk_count')
                    ->label('Faktur Masukan')
                    ->badge()
                    ->color('warning')
                    ->alignCenter()
                    ->sortable(),

                // Status Pembayaran with filtered calculations
                Tables\Columns\BadgeColumn::make('invoice_tax_status')
                    ->label('Status Pembayaran')
                    ->colors([
                        'success' => 'Lebih Bayar',
                        'warning' => 'Kurang Bayar',
                        'gray' => 'Nihil',
                    ])
                    ->formatStateUsing(function (\App\Models\TaxReport $record): string {
                        if (!$record->invoice_tax_status) {
                            return 'Belum Dihitung';
                        }
                        
                        // Use preloaded sums dengan filter nomor faktur untuk PPN keluar
                        $ppnMasuk = $record->ppn_masuk_sum ?? 0;
                        $ppnKeluar = $record->ppn_keluar_sum ?? 0; // Sudah filtered di query
                        $selisih = $ppnKeluar - $ppnMasuk;
                        
                        if ($selisih == 0) {
                            return 'Nihil';
                        }
                        
                        $amount = number_format(abs($selisih), 0, ',', '.');
                        return $record->invoice_tax_status . ' (Rp ' . $amount . ')';
                    })
                    ->tooltip(function (\App\Models\TaxReport $record): string {
                        // Use preloaded sums dengan penjelasan filter
                        $totalMasuk = $record->ppn_masuk_sum ?? 0;
                        $totalKeluar = $record->ppn_keluar_sum ?? 0; // Sudah exclude 02,03,07,08
                        $selisih = $totalKeluar - $totalMasuk;

                        return "Faktur Masuk: Rp " . number_format($totalMasuk, 0, ',', '.') . "\n" .
                            "Faktur Keluar*: Rp " . number_format($totalKeluar, 0, ',', '.') . "\n" .
                            "Selisih: Rp " . number_format($selisih, 0, ',', '.') . "\n\n" .
                            "*Tidak termasuk nomor faktur 02, 03, 07, 08";
                    })
                    ->sortable(),

                // Status PPN from Tax Report
                Tables\Columns\BadgeColumn::make('ppn_report_status')
                    ->label('Status PPN')
                    ->colors([
                        'success' => 'Sudah Lapor',
                        'danger' => 'Belum Lapor',
                    ])
                    ->sortable(),

                // Peredaran Bruto
                Tables\Columns\TextColumn::make('peredaran_bruto')
                    ->label('Peredaran Bruto')
                    ->badge()
                    ->state(function (\App\Models\TaxReport $record): string {
                        $peredaranBruto = $record->peredaran_bruto_sum ?? 0;
                        return "Rp " . number_format($peredaranBruto, 0, ',', '.');
                    })
                    ->tooltip(function (\App\Models\TaxReport $record): string {
                        $invoicesCount = $record->invoices_keluar_count ?? 0;
                        return "Total DPP dari {$invoicesCount} faktur keluaran (tanpa filter)";
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('peredaran_bruto_sum', $direction);
                    })
                    ->color('info')
                    ->weight('medium'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Client Filter
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload(),

                // Month/Period Filter
                Tables\Filters\Filter::make('period')
                    ->form([
                        Forms\Components\TextInput::make('month')
                            ->label('Periode (Bulan)')
                            ->placeholder('January 2024')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['month'],
                                fn (Builder $query, $month): Builder => $query->where('month', 'like', "%{$month}%"),
                            );
                    }),

                // Filter by PPN Status
                Tables\Filters\SelectFilter::make('invoice_tax_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'Kurang Bayar' => 'Kurang Bayar',
                        'Lebih Bayar' => 'Lebih Bayar',
                        'Nihil' => 'Nihil',
                    ]),

                // Filter by PPN Report Status
                Tables\Filters\SelectFilter::make('ppn_report_status')
                    ->label('Status Laporan PPN')
                    ->options([
                        'Sudah Lapor' => 'Sudah Lapor',
                        'Belum Lapor' => 'Belum Lapor',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_all')
                    ->label('Ekspor Semua ke Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        // Get all tax reports with relationships
                        $taxReports = TaxReport::with(['client', 'invoices', 'incomeTaxs', 'bupots'])->get();
                        
                        if ($taxReports->isEmpty()) {
                            Notification::make()
                                ->title('Tidak Ada Data')
                                ->body('Tidak ada laporan pajak yang tersedia untuk diekspor.')
                                ->warning()
                                ->send();
                            return;
                        }
                        
                        // Generate filename with current date
                        $filename = 'Laporan_Pajak_Ringkasan_' . date('Y-m-d_H-i-s') . '.xlsx';
                        
                        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\TaxReportExporter($taxReports), $filename);
                    })
                    ->tooltip('Ekspor ringkasan laporan pajak ke Excel')
                    ->requiresConfirmation()
                    ->modalHeading('Ekspor Ringkasan Laporan Pajak')
                    ->modalDescription('Akan mengekspor ringkasan semua laporan pajak yang tersedia ke file Excel.')
                    ->modalSubmitActionLabel('Ya, Ekspor'),

                Tables\Actions\CreateAction::make()
                    ->label('Faktur Baru')
                    ->successNotificationTitle('Faktur berhasil dibuat')
                    ->modalWidth('7xl'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('view_invoices')
                        ->label('Lihat Detail Faktur')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(function ($record) {
                            // Navigate to a detailed view of invoices for this tax report
                            return route('filament.admin.laporan-pajak.resources.invoices.index', [
                                'tableFilters' => [
                                    'tax_report_id' => ['value' => $record->id]
                                ]
                            ]);
                        })
                        ->tooltip('Lihat semua faktur dalam laporan pajak ini'),
                    Tables\Actions\Action::make('export_report')
                        ->label('Ekspor Laporan')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function ($record) {
                            $taxReport = \App\Models\TaxReport::with(['client', 'invoices', 'incomeTaxs', 'bupots'])->find($record->id);
                            
                            if (!$taxReport) {
                                Notification::make()
                                    ->title('Data Tidak Ditemukan')
                                    ->body('Laporan pajak tidak ditemukan.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            $filename = 'Laporan_Pajak_' . Str::slug($taxReport->client->name) . '_' . $taxReport->month . '_' . date('Y-m-d') . '.xlsx';
                            
                            return \Maatwebsite\Excel\Facades\Excel::download(
                                new \App\Exports\TaxReportExporter(collect([$taxReport])), 
                                $filename
                            );
                        })
                        ->tooltip('Ekspor laporan pajak khusus periode ini'),

                    Tables\Actions\Action::make('mark_as_reported')
                        ->label('Tandai Sudah Lapor')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => $record->ppn_report_status === 'Belum Lapor')
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Pelaporan PPN')
                        ->modalDescription(function ($record) {
                            return 'Apakah Anda yakin ingin menandai laporan PPN untuk ' . $record->client->name . ' periode ' . $record->month . ' sebagai "Sudah Lapor"?';
                        })
                        ->modalSubmitActionLabel('Ya, Tandai Sudah Lapor')
                        ->modalCancelActionLabel('Batal')
                        ->action(function ($record) {
                            $record->update([
                                'ppn_report_status' => 'Sudah Lapor',
                                'ppn_reported_at' => now(),
                            ]);
                            
                            Notification::make()
                                ->title('Status Berhasil Diperbarui')
                                ->body('Laporan PPN untuk ' . $record->client->name . ' periode ' . $record->month . ' telah ditandai sebagai "Sudah Lapor".')
                                ->success()
                                ->send();
                        })
                        ->tooltip('Tandai laporan PPN sebagai sudah dilaporkan'),

                    Tables\Actions\Action::make('mark_as_not_reported')
                        ->label('Tandai Belum Lapor')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => $record->ppn_report_status === 'Sudah Lapor')
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Pembatalan Pelaporan PPN')
                        ->modalDescription(function ($record) {
                            return 'Apakah Anda yakin ingin menandai laporan PPN untuk ' . $record->client->name . ' periode ' . $record->month . ' sebagai "Belum Lapor"?';
                        })
                        ->modalSubmitActionLabel('Ya, Tandai Belum Lapor')
                        ->modalCancelActionLabel('Batal')
                        ->action(function ($record) {
                            $record->update([
                                'ppn_report_status' => 'Belum Lapor',
                                'ppn_reported_at' => null,
                            ]);
                            
                            Notification::make()
                                ->title('Status Berhasil Diperbarui')
                                ->body('Laporan PPN untuk ' . $record->client->name . ' periode ' . $record->month . ' telah ditandai sebagai "Belum Lapor".')
                                ->success()
                                ->send();
                        })
                        ->tooltip('Tandai laporan PPN sebagai belum dilaporkan'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export_selected')
                        ->label('Ekspor Terpilih ke Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $taxReportIds = $records->pluck('id')->toArray();
                            $taxReports = \App\Models\TaxReport::with(['client', 'invoices', 'incomeTaxs', 'bupots'])
                                ->whereIn('id', $taxReportIds)
                                ->get();
                            
                            $filename = 'Laporan_Pajak_Terpilih_' . count($taxReportIds) . '_periods_' . date('Y-m-d_H-i-s') . '.xlsx';
                            
                            return \Maatwebsite\Excel\Facades\Excel::download(
                                new TaxReportExporter($taxReports), 
                                $filename
                            );
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Ekspor Laporan Terpilih')
                        ->modalDescription(function (Collection $records) {
                            $count = $records->count();
                            return "Apakah Anda yakin ingin mengekspor {$count} laporan pajak yang terpilih ke Excel?";
                        })
                        ->modalSubmitActionLabel('Ya, Ekspor')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Laporan Pajak')
            ->emptyStateDescription('Laporan pajak akan muncul di sini setelah ada faktur yang dibuat untuk berbagai periode.')
            ->emptyStateIcon('heroicon-o-document-duplicate');
    }
    public function isReadOnly(): bool
    {
        return false;
    }

    /**
     * Process invoice with AI using the AI Service
     */
    private static function processInvoiceWithAI($file, Forms\Get $get, Forms\Set $set)
    {
        try {
            $set('ai_processing_status', 'processing');
            $set('ai_output', 'Sedang memproses dokumen dengan AI...');
            
            $taxReportId = $get('tax_report_id');
            $taxReport = null;
            $clientName = 'unknown-client';
            $monthName = 'unknown-month';
            
            if ($taxReportId) {
                $taxReport = \App\Models\TaxReport::with('client')->find($taxReportId);
                
                if ($taxReport && $taxReport->client) {
                    $clientName = Str::slug($taxReport->client->name);
                    $monthName = FileManagementService::convertToIndonesianMonth($taxReport->month);
                }
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
    private static function applyAIDataToForm(Forms\Get $get, Forms\Set $set)
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
                self::calculateFromDppNilaiLainnya($get, $set, $data['dpp']);
            }
        } else {
            // For 11%, set DPP directly and calculate PPN
            if (isset($data['dpp'])) {
                $set('dpp', TaxCalculationService::formatCurrency($data['dpp']));
                self::calculatePPNFromDpp($get, $set, $data['dpp']);
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
    private static function calculateFromDppNilaiLainnya(Forms\Get $get, Forms\Set $set, ?string $state): void
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
    private static function calculatePPNFromDpp(Forms\Get $get, Forms\Set $set, ?string $state): void
    {
        $dpp = TaxCalculationService::cleanMonetaryInput($state);
        
        if ($dpp > 0) {
            $result = TaxCalculationService::calculatePPNFromDpp($dpp);
            $set('ppn', $result['ppn_formatted']);
        } else {
            $set('ppn', '0.00');
        }
    }

    public static function getRelations(): array
    {
        return [
            // No relations for standalone resource
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}