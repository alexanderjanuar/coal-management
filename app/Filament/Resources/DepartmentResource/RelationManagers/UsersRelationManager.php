<?php

namespace App\Filament\Resources\DepartmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Anggota Departemen';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama')
                ->required()
                ->maxLength(255),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF&size=300')
                    ->width(36)
                    ->height(36),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->email),

                Tables\Columns\TextColumn::make('position')
                    ->label('Jabatan')
                    ->placeholder('Tidak diset')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                    ]),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('remove')
                    ->label('Keluarkan')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Keluarkan dari Departemen')
                    ->modalDescription(fn ($record) => "Apakah Anda yakin ingin mengeluarkan {$record->name} dari departemen ini?")
                    ->modalSubmitActionLabel('Ya, Keluarkan')
                    ->action(function ($record) {
                        $record->update(['department_id' => null]);

                        Notification::make()
                            ->title('Pengguna Dikeluarkan')
                            ->body("{$record->name} telah dikeluarkan dari departemen ini.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('remove_bulk')
                        ->label('Keluarkan dari Departemen')
                        ->icon('heroicon-o-arrow-right-on-rectangle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Keluarkan dari Departemen')
                        ->modalDescription('Apakah Anda yakin ingin mengeluarkan pengguna yang dipilih dari departemen ini?')
                        ->action(function ($records) {
                            $count = $records->count();
                            $records->each->update(['department_id' => null]);

                            Notification::make()
                                ->title('Pengguna Dikeluarkan')
                                ->body("{$count} pengguna telah dikeluarkan dari departemen ini.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('name')
            ->emptyStateHeading('Belum ada anggota')
            ->emptyStateDescription('Departemen ini belum memiliki anggota.')
            ->emptyStateIcon('heroicon-o-users');
    }
}
