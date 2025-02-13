<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Guava\FilamentModalRelationManagers\Concerns\CanBeEmbeddedInModals;

class ClientDocumentsRelationManager extends RelationManager
{
    use CanBeEmbeddedInModals;
    protected static string $relationship = 'clientDocuments';

    protected static ?string $title = 'Legal';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('file_path')
                    ->required()
                    ->label('Document')
                    ->preserveFilenames()
                    ->directory(function ($livewire) {
                        $clientName = Str::slug($livewire->getOwnerRecord()->name);
                        return "clients/{$clientName}/legal";
                    })
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if ($state) {
                            $filename = basename($state);
                            $set('name', $filename);
                        }
                    })
                    ->columnSpanFull(),
                Forms\Components\Hidden::make('name'),
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id())
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('file_path')
                    ->label('Document Name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn(string $state) => basename($state))
                    ->color(function (string $state) {
                        $extension = strtolower(pathinfo($state, PATHINFO_EXTENSION));

                        return match ($extension) {
                            'pdf' => 'danger',
                            'xlsx', 'xls' => 'success',
                            'doc', 'docx' => 'info',
                            'jpg', 'jpeg', 'png' => 'warning',
                            default => 'gray'
                        };
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Uploaded By')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('Download')
                    ->color('success')
                    ->url(fn($record) => Storage::url($record->file_path)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}