<?php
// File: app/Filament/Resources/PicResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\PicResource\Pages;
use App\Filament\Resources\PicResource\RelationManagers\ClientsRelationManager;
use App\Models\Pic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;

class PicResource extends Resource
{
    protected static ?string $model = Pic::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'PICs';

    protected static ?string $modelLabel = 'PIC';

    protected static ?string $pluralModelLabel = 'PICs';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter full name'),

                        Forms\Components\TextInput::make('nik')
                            ->label('NIK')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->length(16)
                            ->numeric()
                            ->placeholder('16-digit NIK number')
                            ->helperText('Nomor Induk Kependudukan (16 digits)'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Account Settings')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->default('Samarinda#1')
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->placeholder('Enter password'),

                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->native(false),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('NIK copied!')
                    ->fontFamily('mono'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'active',
                        'heroicon-o-x-circle' => 'inactive',
                    ]),

                Tables\Columns\TextColumn::make('clients_count')
                    ->label('Clients')
                    ->counts('clients')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('email_verified')
                    ->label('Email Verified')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),

                Tables\Filters\Filter::make('has_clients')
                    ->label('Has Clients')
                    ->query(fn (Builder $query): Builder => $query->has('clients')),
            ])
            ->actions([
                RelationManagerAction::make('client-relation-manager')
                    ->label('View Clients')
                    ->color('info')
                    ->modalWidth('7xl')
                    ->slideOver()
                    ->relationManager(ClientsRelationManager::make()),
                     // This makes it wider,
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete PIC')
                    ->modalDescription('Are you sure you want to delete this PIC? This action cannot be undone and will affect all assigned clients.')
                    ->modalSubmitActionLabel('Yes, delete it'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                    
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => 'active']))
                        ->requiresConfirmation()
                        ->modalHeading('Activate PICs')
                        ->modalDescription('Are you sure you want to activate the selected PICs?'),
                    
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['status' => 'inactive']))
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate PICs')
                        ->modalDescription('Are you sure you want to deactivate the selected PICs?'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Personal Information')
                    ->schema([
                        TextEntry::make('name')
                            ->icon('heroicon-o-user'),

                        TextEntry::make('nik')
                            ->label('NIK')
                            ->copyable()
                            ->copyMessage('NIK copied!')
                            ->fontFamily('mono'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'inactive' => 'danger',
                            }),
                    ])
                    ->columns(2),

                Section::make('Account Information')
                    ->schema([
                        TextEntry::make('clients_count')
                            ->label('Total Clients')
                            ->state(fn ($record) => $record->clients()->count())
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2),

                Section::make('Assigned Clients')
                    ->schema([
                        TextEntry::make('clients.name')
                            ->label('Client Names')
                            ->listWithLineBreaks()
                            ->limitList(10)
                            ->expandableLimitedList()
                            ->placeholder('No clients assigned'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ClientsRelationManager::class,
            // Add relation managers here if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPics::route('/'),
            'create' => Pages\CreatePic::route('/create'),
            'view' => Pages\ViewPic::route('/{record}'),
            'edit' => Pages\EditPic::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['clients']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'nik'];
    }
}
