<?php

namespace App\Filament\Resources\TaxReportResource\RelationManagers;

use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\RawJs;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;

class BupotsRelationManager extends RelationManager
{
    protected static string $relationship = 'bupots';

    public function isReadOnly(): bool
    {
        return false;
    }
    protected static ?string $title = 'Bupot';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('created_by')
                    ->default(auth()->id()),
                    
                Wizard::make([
                    Wizard\Step::make('Informasi Umum')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Section::make('Data Bukti Potong')
                                ->columns(2)
                                ->schema([
                                    Forms\Components\Select::make('invoice_id')
                                        ->label('Faktur Terkait')
                                        ->options(function () {
                                            // Get invoices related to the current tax report
                                            $taxReport = $this->getOwnerRecord();
                                            if ($taxReport) {
                                                return Invoice::where('tax_report_id', $taxReport->id)
                                                    ->pluck('invoice_number', 'id')
                                                    ->toArray();
                                            }
                                            return [];
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->placeholder('Pilih faktur (opsional)')
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                            if ($state) {
                                                $invoice = Invoice::find($state);
                                                if ($invoice) {
                                                    $set('company_name', $invoice->company_name);
                                                    $set('npwp', $invoice->npwp);
                                                    $set('dpp', $invoice->dpp);
                                                    
                                                    // Set bupot type based on invoice type
                                                    if ($invoice->type === 'Faktur Keluaran') {
                                                        $set('bupot_type', 'Bupot Keluaran');
                                                    } elseif ($invoice->type === 'Faktur Masuk') {
                                                        $set('bupot_type', 'Bupot Masukan');
                                                    }
                                                }
                                            }
                                        }),
                                        
                                        Forms\Components\Select::make('tax_period')
                                        ->label('Periode Pajak')
                                        ->native(false)
                                        ->options([
                                            'Januari' => 'Januari',
                                            'Februari' => 'Februari',
                                            'Maret' => 'Maret',
                                            'April' => 'April',
                                            'Mei' => 'Mei',
                                            'Juni' => 'Juni',
                                            'Juli' => 'Juli',
                                            'Agustus' => 'Agustus',
                                            'September' => 'September',
                                            'Oktober' => 'Oktober',
                                            'November' => 'November',
                                            'Desember' => 'Desember',
                                        ])
                                        ->default(function() {
                                            // Get current month in Indonesian
                                            $months = [
                                                1 => 'Januari',
                                                2 => 'Februari',
                                                3 => 'Maret',
                                                4 => 'April',
                                                5 => 'Mei',
                                                6 => 'Juni',
                                                7 => 'Juli',
                                                8 => 'Agustus',
                                                9 => 'September',
                                                10 => 'Oktober',
                                                11 => 'November',
                                                12 => 'Desember',
                                            ];
                                            $currentMonth = (int)date('n');
                                            return $months[$currentMonth];
                                        })
                                        ->required()
                                        ->helperText('Pilih bulan untuk periode pajak ini'),
                                        
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
                                        
                                    Forms\Components\Select::make('bupot_type')
                                        ->label('Jenis Bukti Potong')
                                        ->required()
                                        ->native(false)
                                        ->options([
                                            'Bupot Masukan' => 'Bupot Masukan',
                                            'Bupot Keluaran' => 'Bupot Keluaran',
                                        ])
                                        ->default('Bupot Keluaran'),
                                        
                                    Forms\Components\Select::make('pph_type')
                                        ->label('Jenis PPh')
                                        ->required()
                                        ->native(false)
                                        ->options([
                                            'PPh 21' => 'PPh 21',
                                            'PPh 23' => 'PPh 23',
                                        ])
                                        ->default('PPh 23'),
                                ]),
                        ]),
                        
                    Wizard\Step::make('Nilai Pajak')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            Section::make('Detail Nominal')
                                ->columns(2)
                                ->schema([
                                    Forms\Components\TextInput::make('dpp')
                                        ->label('DPP (Dasar Pengenaan Pajak)')
                                        ->required()
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->mask(RawJs::make('$money($input)'))
                                        ->stripCharacters(',')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                            // Auto-calculate bupot amount based on PPh type
                                            if (is_numeric($state) && $state > 0) {
                                                $pphType = $get('pph_type');
                                                $rate = ($pphType === 'PPh 21') ? 0.05 : 0.02; // 5% for PPh 21, 2% for PPh 23
                                                $set('bupot_amount', floatval($state) * $rate);
                                            }
                                        }),
                                        
                                    Forms\Components\TextInput::make('bupot_amount')
                                        ->label('Nilai Bukti Potong')
                                        ->required()
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->mask(RawJs::make('$money($input)'))
                                        ->stripCharacters(',')
                                        ->helperText(function (Forms\Get $get) {
                                            $pphType = $get('pph_type');
                                            $rate = ($pphType === 'PPh 21') ? '5%' : '2%';
                                            return "Secara default dihitung sebagai {$rate} dari DPP";
                                        }),
                                ]),
                        ]),
                        
                    Wizard\Step::make('Dokumen')
                        ->icon('heroicon-o-paper-clip')
                        ->schema([
                            Section::make('Bukti Dokumen')
                                ->schema([
                                    Forms\Components\FileUpload::make('file_path')
                                        ->label('Bukti Potong')
                                        ->required()
                                        ->disk('public')
                                        ->directory('bupot-documents')
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                        ->helperText('Unggah dokumen bukti potong (PDF atau gambar)')
                                        ->columnSpanFull(),
                                        
                                    Forms\Components\RichEditor::make('notes')
                                        ->label('Catatan')
                                        ->placeholder('Tambahkan catatan relevan tentang bukti potong ini')
                                        ->maxLength(1000)
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ])
                ->skippable()
                ->persistStepInQueryString('bupot-wizard-step')
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
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Nama Perusahaan')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('npwp')
                    ->label('NPWP')
                    ->searchable(),
                    
                Tables\Columns\BadgeColumn::make('bupot_type')
                    ->label('Jenis Bukti Potong')
                    ->colors([
                        'success' => 'Bupot Keluaran',
                        'warning' => 'Bupot Masukan',
                    ]),
                    
                Tables\Columns\BadgeColumn::make('pph_type')
                    ->label('Jenis PPh')
                    ->colors([
                        'primary' => 'PPh 21',
                        'info' => 'PPh 23',
                    ]),
                    
                Tables\Columns\TextColumn::make('dpp')
                    ->label('DPP')
                    ->money('IDR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('bupot_amount')
                    ->label('Nilai Bupot')
                    ->money('IDR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->label('Faktur Terkait')
                    ->placeholder('-')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('bupot_type')
                    ->label('Jenis Bukti Potong')
                    ->options([
                        'Bupot Keluaran' => 'Bupot Keluaran',
                        'Bupot Masukan' => 'Bupot Masukan',
                    ]),
                    
                Tables\Filters\SelectFilter::make('pph_type')
                    ->label('Jenis PPh')
                    ->options([
                        'PPh 21' => 'PPh 21',
                        'PPh 23' => 'PPh 23',
                    ]),
                    
                Tables\Filters\Filter::make('has_invoice')
                    ->label('Terkait Faktur')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('invoice_id')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Bukti Potong')
                    ->modalHeading('Tambah Bukti Potong')
                    ->modalWidth('7xl')
                    ->successNotificationTitle('Bukti potong berhasil ditambahkan'),
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
                        ->tooltip('Unduh berkas bukti potong'),
                        
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->modalHeading('Hapus Bukti Potong')
                        ->modalDescription('Apakah Anda yakin ingin menghapus bukti potong ini? Tindakan ini tidak dapat dibatalkan.'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus')
                        ->modalHeading('Hapus Bukti Potong Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus bukti potong yang terpilih? Tindakan ini tidak dapat dibatalkan.'),
                        
                    Tables\Actions\BulkAction::make('export')
                        ->label('Ekspor ke Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(fn () => null) // Implement export functionality here
                        ->requiresConfirmation()
                        ->modalHeading('Ekspor Bukti Potong')
                        ->modalDescription('Apakah Anda yakin ingin mengekspor bukti potong yang terpilih?')
                        ->modalSubmitActionLabel('Ya, Ekspor'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Bukti Potong')
            ->emptyStateDescription('Bukti potong pajak (untuk PPh 21 dan PPh 23) akan muncul di sini. Tambahkan bukti potong untuk melengkapi laporan pajak Anda.')
            ->emptyStateIcon('heroicon-o-document-check')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Bukti Potong')
                    ->modalWidth('7xl')
                    ->icon('heroicon-o-plus'),
                    
                Tables\Actions\Action::make('link_invoice')
                    ->label('Kaitkan dengan Faktur')
                    ->color('gray')
                    ->icon('heroicon-o-link')
                    ->visible(function () {
                        // Only show if there are invoices to link
                        $taxReport = $this->getOwnerRecord();
                        return $taxReport && $taxReport->invoices()->count() > 0;
                    })
                    ->action(function () {
                        // Placeholder for linking action
                    }),
            ]);
    }
}