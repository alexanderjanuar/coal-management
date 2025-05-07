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
                                        ->mask(RawJs::make('$money($input)'))
                                        ->stripCharacters(',')
                                        ->placeholder('0.00')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                            if (is_numeric($state)) {
                                                $ppn = floatval($state) * 0.11; // 11% tax
                                                $set('ppn', number_format($ppn, 2, '.', ''));
                                            }
                                        }),
                                        
                                    Forms\Components\TextInput::make('ppn')
                                        ->label('PPN (11%)')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->placeholder('0.00')
                                        ->required()
                                        ->stripCharacters(',')
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
                                        ->required()
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
                Tables\Columns\ImageColumn::make('creator.avatar')
                ->label('Dibuat Oleh')
                ->circular()
                ->defaultImageUrl(function ($record) {
                    if ($record->created_by && $record->creator) {
                        // Generate initials from creator's name
                        $name = $record->creator->name;
                        $initials = collect(explode(' ', $name))
                            ->map(fn ($segment) => $segment[0] ?? '')
                            ->join('');
                            
                        return "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&color=FFFFFF&background=4F46E5";
                    }
                    
                    return "https://ui-avatars.com/api/?name=?&color=FFFFFF&background=4F46E5";
                })
                ->tooltip(fn ($record) => $record->creator ? $record->creator->name : 'Tidak Diketahui')
                ->size(40),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Nomor Faktur')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Nama Perusahaan')
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
                    ->money('IDR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('ppn')
                    ->label('PPN')
                    ->money('IDR')
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
                    ->modalWidth('7xl'),
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
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}