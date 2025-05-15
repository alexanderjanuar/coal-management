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
use Filament\Forms\Components\FileUpload;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    protected static ?string $title = 'PPN';


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
                                ->columns(2)
                                ->schema([
                                    Forms\Components\TextInput::make('invoice_number')
                                        ->label('Nomor Faktur')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255)
                                        ->placeholder('010.000-00.00000000')
                                        ->helperText('Format: 010.000-00.00000000'),
                                        
                                    Forms\Components\Select::make('type')
                                        ->label('Jenis Faktur')
                                        ->native(false)
                                        ->options([
                                            'Faktur Keluaran' => 'Faktur Keluaran',
                                            'Faktur Masuk' => 'Faktur Masuk',
                                        ])
                                        ->required()
                                        ->reactive()
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
                                        }),
                                        
                                    Forms\Components\TextInput::make('company_name')
                                        ->label('Nama Perusahaan')
                                        ->required()
                                        ->maxLength(255),
                                        
                                    Forms\Components\TextInput::make('npwp')
                                        ->label('NPWP')
                                        ->required()
                                        ->placeholder('00.000.000.0-000.000')
                                        ->helperText('Format: 00.000.000.0-000.000')
                                        ->maxLength(255),
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
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->placeholder('0.00')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                            if (is_numeric($state)) {
                                                // Get the PPN percentage from the select field
                                                $ppnPercentage = $get('ppn_percentage') === '12' ? 0.12 : 0.11;
                                                $ppn = floatval($state) * $ppnPercentage;
                                                $set('ppn', number_format($ppn, 2, '.', ''));
                                            }
                                        }),
                                    
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
                                        ->live()
                                        ->helperText('Pilih tarif PPN yang berlaku')
                                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                            $dpp = $get('dpp');
                                            if (is_numeric($dpp)) {
                                                // Calculate PPN based on selected percentage
                                                $ppnPercentage = $state === '12' ? 0.12 : 0.11;
                                                $ppn = floatval($dpp) * $ppnPercentage;
                                                $set('ppn', number_format($ppn, 2, '.', ''));
                                            }
                                        }),
                                    
                                    Forms\Components\TextInput::make('ppn')
                                        ->label('PPN')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->placeholder('0.00')
                                        ->required()
                                        ->readOnly()
                                        ->stripCharacters(',')
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
                                        ->required()
                                        ->openable()
                                        ->downloadable()
                                        ->disk('public')
                                        ->directory('invoices')   
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                        ->helperText('Unggah dokumen faktur (PDF atau gambar)')
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
                        return 'System';
                    })
                    ->defaultImageUrl(asset('images/default-avatar.png'))
                    ->size(40)
                    ->tooltip(function ($record): string {
                        return $record->creator?->name ?? 'System';
                    }),
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
                    
                // New column to show related bupots count
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
            ])
            ->headerActions([
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
                                \Filament\Notifications\Notification::make()
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
                    })
,
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}