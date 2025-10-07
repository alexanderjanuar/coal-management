<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SopLegalDocumentResource\Pages;
use App\Models\SopLegalDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SopLegalDocumentResource extends Resource
{
    protected static ?string $model = SopLegalDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    
    protected static ?string $navigationLabel = 'Legal Documents';
    
    protected static ?string $navigationGroup = 'Standard Operating Procedures';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dokumen')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Dokumen')
                            ->placeholder('e.g., Akta Pendirian'),
                        
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->maxLength(500)
                            ->label('Deskripsi')
                            ->placeholder('Jelaskan dokumen ini...'),
                        
                        Forms\Components\Select::make('client_type')
                            ->options([
                                'Badan' => 'Badan/Perusahaan',
                                'Pribadi' => 'Pribadi/Perorangan',
                                'Both' => 'Keduanya',
                            ])
                            ->required()
                            ->native(false)
                            ->label('Tipe Klien'),
                        
                        Forms\Components\Select::make('category')
                            ->options([
                                'Dasar' => 'Dokumen Dasar',
                                'PKP' => 'Khusus PKP',
                                'Pendukung' => 'Dokumen Pendukung',
                            ])
                            ->required()
                            ->default('Dasar')
                            ->native(false)
                            ->label('Kategori'),
                        
                        Forms\Components\Toggle::make('is_required')
                            ->label('Wajib')
                            ->default(true)
                            ->helperText('Apakah dokumen ini wajib diupload?'),
                        
                        Forms\Components\TextInput::make('order')
                            ->numeric()
                            ->default(0)
                            ->label('Urutan')
                            ->helperText('Urutan tampilan (angka kecil muncul duluan)'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Dokumen aktif akan muncul di checklist klien'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Dokumen')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\BadgeColumn::make('client_type')
                    ->label('Tipe Klien')
                    ->colors([
                        'primary' => 'Badan',
                        'warning' => 'Pribadi',
                        'success' => 'Both',
                    ]),
                
                Tables\Columns\BadgeColumn::make('category')
                    ->label('Kategori')
                    ->colors([
                        'info' => 'Dasar',
                        'success' => 'PKP',
                        'gray' => 'Pendukung',
                    ]),
                
                Tables\Columns\IconColumn::make('is_required')
                    ->label('Wajib')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                
                Tables\Columns\TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable(),
                
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif'),
                
                Tables\Columns\TextColumn::make('clientDocuments_count')
                    ->counts('clientDocuments')
                    ->label('Total Upload')
                    ->badge()
                    ->color('success'),
            ])
            ->defaultSort('order')
            ->filters([
                Tables\Filters\SelectFilter::make('client_type')
                    ->options([
                        'Badan' => 'Badan',
                        'Pribadi' => 'Pribadi',
                        'Both' => 'Both',
                    ]),
                
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'Dasar' => 'Dasar',
                        'PKP' => 'PKP',
                        'Pendukung' => 'Pendukung',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_required')
                    ->label('Wajib')
                    ->placeholder('Semua')
                    ->trueLabel('Wajib')
                    ->falseLabel('Opsional'),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Nonaktifkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSopLegalDocuments::route('/'),
            'create' => Pages\CreateSopLegalDocument::route('/create'),
            'edit' => Pages\EditSopLegalDocument::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }
}