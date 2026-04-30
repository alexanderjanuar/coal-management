<?php

namespace App\Filament\Resources\ClientGroupResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ClientsRelationManager extends RelationManager
{
    protected static string $relationship = 'clients';

    protected static ?string $title = 'Client dalam Grup';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama Client')
                ->required()
                ->maxLength(255),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF')
                    ->size(36),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Client')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('NPWP')
                    ->label('NPWP')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active'   => 'success',
                        'Inactive' => 'danger',
                        default    => 'gray',
                    }),
            ])
            ->headerActions([
                Tables\Actions\AssociateAction::make()
                    ->label('Tambah Client ke Grup')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'email', 'NPWP']),
            ])
            ->actions([
                Tables\Actions\DissociateAction::make()
                    ->label('Keluarkan dari Grup'),
            ])
            ->bulkActions([
                Tables\Actions\DissociateBulkAction::make()
                    ->label('Keluarkan Terpilih'),
            ])
            ->emptyStateHeading('Belum ada client')
            ->emptyStateDescription('Tambahkan client ke grup ini.')
            ->emptyStateIcon('heroicon-o-users');
    }
}
