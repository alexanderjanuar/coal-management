<?php

namespace App\Filament\Resources;

use App\Filament\Exports\ClientExporter;
use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers\ProgressRelationManager;
use App\Filament\Resources\ClientResource\RelationManagers\ApplicationsRelationManager;
use App\Filament\Resources\ProjectStepResource\RelationManagers\RequiredDocumentsRelationManager;
use Filament\Forms\Components\Section;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Fieldset;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use Filament\Forms\Components\Select;
use App\Filament\Imports\ClientImporter;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Actions\Exports\Models\Export;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Support\Enums\FontWeight;
class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Master Data';


    public static function form(Form $form): Form
    {
        return $form

            ->schema([
                Section::make('Client Profile')
                    ->description('Detail dari Client')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Client Name')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->label('Client Email')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('adress')
                            ->label('Adress'),
                        Forms\Components\TextInput::make('person_in_charge')
                            ->label('Person In Charge'),
                        FileUpload::make('logo')
                            ->label('Client Logo')
                            ->columnSpanFull()
                    ])
                    ->columns(2),
                Section::make('Client Tax')
                    ->description('Detail of Client Tax')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        Forms\Components\TextInput::make('NPWP')
                            ->label('NPWP')
                            ->required(),
                        Forms\Components\TextInput::make('EFIN')
                            ->label('EFIN'),
                        Forms\Components\TextInput::make('account_representative')
                            ->label('Account Representative (AR)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('ar_phone_number')
                            ->label('AR Phone Number'),
                        Select::make('KPP')
                            ->label('KPP')
                            ->native(false)
                            ->options([
                                'SAMARINDA ULU' => 'Samarinda Ulu',
                                'SAMARINDA ILIR' => 'Samarinda Ilir',
                                'TENGGARONG' => 'Tenggarong',
                                'BALIKPAPAN BARAT' => 'Balikpapan Barat',
                                'BALIKPAPAN TIMUR' => 'Balikpapan Timur',
                                'MADYA DUA JAKARTA BARAT' => 'Madya Dua Jakarta Barat',
                                'MADYA BALIKPAPAN' => 'Madya Balikpapan',
                                'BONTANG' => 'Bontang',
                                'BANJARBARU' => 'Banjarbaru',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Client')
                    ->searchable(),
                Tables\Columns\TextColumn::make('NPWP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(ClientImporter::class)
                    ->color('primary')
                    ->label('Import Clients')
                    ->icon('heroicon-o-arrow-down-tray'),
                ExportAction::make()
                    ->exporter(ClientExporter::class)
                    ->label('Export Clients')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->fileName(fn(Export $export): string => "client-{$export->getKey()}")
            ])
            ->actions([
                RelationManagerAction::make('progress-relation-manager')
                    ->label('Projects')
                    ->slideOver()
                    ->Icon('heroicon-o-folder')
                    ->color('warning')
                    ->relationManager(ProgressRelationManager::make()),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if (auth()->user()->hasRole('super-admin')) {
                    return $query;
                } else {
                    $query->whereIn('id', auth()->user()->userClients->pluck('client_id'));
                    return $query;
                }
            });
    }



    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'projects.name'];
    }

    public static function getRelations(): array
    {
        return [
            ApplicationsRelationManager::class,
            ProgressRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
