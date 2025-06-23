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
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\Summarizers\Sum;

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
                                            ->acceptedFileTypes([
                                                'application/pdf', 
                                                'image/jpeg', 
                                                'image/png', 
                                                'image/jpg', 
                                                'image/webp'
                                            ])
                                            ->maxSize(10240) // 10MB
                                            ->helperText('Format yang didukung: PDF, JPG, PNG, WEBP (Maksimal 10MB)')
                                            ->live()
                                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                                // Clear previous AI output when new file is uploaded
                                                if ($state) {
                                                    $set('ai_output', '');
                                                    $set('ai_processing_status', 'ready');
                                                }
                                            })
                                            ->columnSpanFull(),
                                            
                                        Forms\Components\Hidden::make('ai_processing_status')
                                            ->default('idle'), // idle, ready, processing, completed, error
                                            
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
                                                    // TODO: Implement AI processing logic here
                                                    $file = $get('ai_upload_file');
                                                    
                                                    if (!$file) {
                                                        Notification::make()
                                                            ->title('File Diperlukan')
                                                            ->body('Silakan upload file faktur terlebih dahulu.')
                                                            ->warning()
                                                            ->send();
                                                        return;
                                                    }
                                                    
                                                    // Set processing status
                                                    $set('ai_processing_status', 'processing');
                                                    $set('ai_output', 'Sedang memproses dokumen dengan AI...');
                                                    
                                                    // Placeholder for AI processing
                                                    // This is where you'll implement the actual AI logic
                                                    $this->processInvoiceWithAI($file, $get, $set);
                                                })
                                                ->button()
                                                ->extraAttributes([
                                                    'class' => 'w-full justify-center'
                                                ]),
                                        ])
                                        ->columnSpanFull()
                                        ->alignCenter(),
                                        
                                        Forms\Components\Placeholder::make('ai_output')
                                            ->label('Hasil Ekstraksi AI')
                                            ->content(function (Forms\Get $get) {
                                                $output = $get('ai_output');
                                                $status = $get('ai_processing_status');
                                                
                                                if (empty($output)) {
                                                    return 'Upload file dan klik "Proses dengan AI" untuk melihat hasil ekstraksi data.';
                                                }
                                                
                                                return $output;
                                            })
                                            ->columnSpanFull()
                                            ->extraAttributes([
                                                'class' => 'bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border min-h-[120px]'
                                            ]),
                                            
                                        Forms\Components\Hidden::make('ai_output')
                                            ->default(''),
                                            
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('apply_ai_data')
                                                ->label('Terapkan Data AI ke Form')
                                                ->icon('heroicon-o-arrow-right')
                                                ->color('success')
                                                ->size('lg')
                                                ->visible(function (Forms\Get $get) {
                                                    $status = $get('ai_processing_status');
                                                    return $status === 'completed';
                                                })
                                                ->action(function (Forms\Get $get, Forms\Set $set) {
                                                    // TODO: Implement logic to apply AI extracted data to form fields
                                                    $this->applyAIDataToForm($get, $set);
                                                    
                                                    Notification::make()
                                                        ->title('Data Berhasil Diterapkan')
                                                        ->body('Data hasil ekstraksi AI telah diterapkan ke form. Silakan periksa dan lanjutkan ke langkah berikutnya.')
                                                        ->success()
                                                        ->send();
                                                })
                                                ->button()
                                                ->extraAttributes([
                                                    'class' => 'w-full justify-center'
                                                ]),
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
                    ->summarize(Sum::make()->label('Total PPN')->money('Rp.'))
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
            ->groups([
                'type',
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


    /**
     * Process invoice with AI (placeholder method)
     * TODO: Implement actual AI processing logic
     */

    private function processInvoiceWithAI($file, Forms\Get $get, Forms\Set $set)
    {
        try {
            // Set processing status
            $set('ai_processing_status', 'processing');
            $set('ai_output', 'Sedang memproses dokumen dengan AI...');
            
            // Get tax report info for context
            $taxReportId = $get('tax_report_id') ?? $this->getOwnerRecord()->id;
            $taxReport = \App\Models\TaxReport::with('client')->find($taxReportId);
            
            $clientName = 'unknown-client';
            $monthName = 'unknown-month';
            
            if ($taxReport && $taxReport->client) {
                $clientName = Str::slug($taxReport->client->name);
                $monthName = $this->convertToIndonesianMonth($taxReport->month);
            }
            
            // Get the file path - $file is the relative path from storage/app/public
            $filePath = $file;
            
            // Path to the Node.js script in resources/scripts (normalize path separators)
            $scriptPath = resource_path('scripts' . DIRECTORY_SEPARATOR . 'ai-invoice-processor.js');
            
            // Normalize path for Windows/Unix compatibility
            $scriptPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $scriptPath);
            
            // Check if script exists
            if (!file_exists($scriptPath)) {
                // Debug: List files in the directory
                $scriptsDir = resource_path('scripts');
                $files = is_dir($scriptsDir) ? scandir($scriptsDir) : ['Directory does not exist'];
                
                throw new \Exception('AI processor script not found at: ' . $scriptPath . '. Files in scripts directory: ' . implode(', ', $files));
            }
            
            // Check if node is available (Windows and Unix compatible)
            $nodeCheck = PHP_OS_FAMILY === 'Windows' 
                ? shell_exec('where node 2>NUL') 
                : shell_exec('which node 2>/dev/null');
                
            if (empty(trim($nodeCheck))) {
                throw new \Exception('Node.js is not installed or not in PATH. Please install Node.js first.');
            }
            
            // Check if package.json and node_modules exist (normalize paths)
            $packageJsonPath = resource_path('scripts' . DIRECTORY_SEPARATOR . 'package.json');
            $nodeModulesPath = resource_path('scripts' . DIRECTORY_SEPARATOR . 'node_modules');
            
            if (!file_exists($packageJsonPath)) {
                throw new \Exception('package.json not found at: ' . $packageJsonPath);
            }
            
            if (!is_dir($nodeModulesPath)) {
                throw new \Exception('Node modules not installed at: ' . $nodeModulesPath . '. Please run "npm install" in resources/scripts/');
            }
            
            // Prepare command with Windows/Unix compatibility
            $scriptsDir = resource_path('scripts');
            $scriptFile = 'ai-invoice-processor.js';
            
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows command
                $command = sprintf(
                    'cd /d %s && set "GOOGLE_GEMINI_API=%s" && node %s %s %s %s 2>&1',
                    escapeshellarg($scriptsDir),
                    escapeshellarg(config('services.gemini.api_key', env('GOOGLE_GEMINI_API'))),
                    escapeshellarg($scriptFile),
                    escapeshellarg($filePath),
                    escapeshellarg($clientName),
                    escapeshellarg($monthName)
                );
            } else {
                // Unix/Linux command
                $command = sprintf(
                    'cd %s && GOOGLE_GEMINI_API=%s node %s %s %s %s 2>&1',
                    escapeshellarg($scriptsDir),
                    escapeshellarg(config('services.gemini.api_key', env('GOOGLE_GEMINI_API'))),
                    escapeshellarg($scriptFile),
                    escapeshellarg($filePath),
                    escapeshellarg($clientName),
                    escapeshellarg($monthName)
                );
            }
            
            // Log the command for debugging (remove in production)
            \Log::info('AI Processing Command: ' . $command);
            
            // Execute the command with timeout
            $descriptorspec = [
                0 => ["pipe", "r"],  // stdin
                1 => ["pipe", "w"],  // stdout
                2 => ["pipe", "w"]   // stderr
            ];
            
            $process = proc_open($command, $descriptorspec, $pipes);
            
            if (!is_resource($process)) {
                throw new \Exception('Failed to start AI processing script');
            }
            
            // Close stdin
            fclose($pipes[0]);
            
            // Read stdout and stderr with timeout
            $output = '';
            $error = '';
            $timeout = 120; // 2 minutes timeout
            $start = time();
            
            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);
            
            while (time() - $start < $timeout) {
                $read = [$pipes[1], $pipes[2]];
                $write = null;
                $except = null;
                
                if (stream_select($read, $write, $except, 1)) {
                    if (in_array($pipes[1], $read)) {
                        $output .= fread($pipes[1], 8192);
                    }
                    if (in_array($pipes[2], $read)) {
                        $error .= fread($pipes[2], 8192);
                    }
                }
                
                // Check if process is still running
                $status = proc_get_status($process);
                if (!$status['running']) {
                    break;
                }
            }
            
            // Read any remaining output
            $output .= stream_get_contents($pipes[1]);
            $error .= stream_get_contents($pipes[2]);
            
            fclose($pipes[1]);
            fclose($pipes[2]);
            
            $exit_code = proc_close($process);
            
            // Check for timeout
            if (time() - $start >= $timeout) {
                throw new \Exception('AI processing timed out after 2 minutes');
            }
            
            // Check exit code
            if ($exit_code !== 0) {
                $errorMessage = !empty($error) ? $error : $output;
                throw new \Exception('AI processing failed: ' . $errorMessage);
            }
            
            // Log output for debugging (remove in production)
            \Log::info('AI Processing Output: ' . $output);
            if (!empty($error)) {
                \Log::warning('AI Processing Stderr: ' . $error);
            }
            
            // Parse the JSON response
            $result = json_decode(trim($output), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from AI processor: ' . json_last_error_msg() . '. Output: ' . $output);
            }
            
            if (!$result || !is_array($result)) {
                throw new \Exception('Invalid response format from AI processor. Output: ' . $output);
            }
            
            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Unknown error from AI processor');
            }
            
            if (!isset($result['data']) || !is_array($result['data'])) {
                throw new \Exception('No data returned from AI processor');
            }
            
            $extractedData = $result['data'];
            
            // Validate required fields
            $requiredFields = ['invoice_number', 'company_name', 'dpp', 'ppn'];
            foreach ($requiredFields as $field) {
                if (!isset($extractedData[$field]) || empty($extractedData[$field])) {
                    throw new \Exception("Missing required field from AI extraction: {$field}");
                }
            }
            
            // Set the processing status and output
            $set('ai_processing_status', 'completed');
            
            $formattedOutput = "✅ **Ekstraksi Data Berhasil**\n\n";
            $formattedOutput .= "**Data yang ditemukan:**\n";
            $formattedOutput .= "• Nomor Faktur: {$extractedData['invoice_number']}\n";
            $formattedOutput .= "• Tanggal Faktur: {$extractedData['invoice_date']}\n";
            $formattedOutput .= "• Nama Perusahaan: {$extractedData['company_name']}\n";
            $formattedOutput .= "• NPWP: {$extractedData['npwp']}\n";
            $formattedOutput .= "• Jenis Faktur: {$extractedData['type']}\n";
            $formattedOutput .= "• DPP: Rp " . number_format((int)$extractedData['dpp'], 0, ',', '.') . "\n";
            $formattedOutput .= "• Tarif PPN: {$extractedData['ppn_percentage']}%\n";
            $formattedOutput .= "• PPN: Rp " . number_format((int)$extractedData['ppn'], 0, ',', '.') . "\n\n";
            $formattedOutput .= "📋 Klik tombol **'Terapkan Data AI ke Form'** untuk mengisi form secara otomatis.";
            
            $set('ai_output', $formattedOutput);
            
            // Store extracted data for later use
            $set('ai_extracted_data', json_encode($extractedData));
            
            Notification::make()
                ->title('AI Processing Selesai')
                ->body('Data faktur berhasil diekstrak. Silakan tinjau hasil dan terapkan ke form.')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            // Handle errors
            $set('ai_processing_status', 'error');
            $set('ai_output', '❌ **Error:** ' . $e->getMessage());
            
            Notification::make()
                ->title('Error AI Processing')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();
                
            // Log the error for debugging
            \Log::error('AI Invoice Processing Error: ' . $e->getMessage(), [
                'file' => $file ?? 'unknown',
                'client' => $clientName ?? 'unknown',
                'month' => $monthName ?? 'unknown'
            ]);
        }
    }
    /**
     * Apply AI extracted data to form fields
     * TODO: Implement logic to populate form fields with AI data
     */
    private function applyAIDataToForm(Forms\Get $get, Forms\Set $set)
    {
        $extractedDataJson = $get('ai_extracted_data');
        
        if (!$extractedDataJson) {
            return;
        }
        
        $data = json_decode($extractedDataJson, true);
        
        if (!$data) {
            return;
        }
        
        // Apply extracted data to form fields
        foreach ($data as $field => $value) {
            $set($field, $value);
        }
        
        // Add hidden field to store AI extracted data
        $set('ai_extracted_data', '');
    }
}