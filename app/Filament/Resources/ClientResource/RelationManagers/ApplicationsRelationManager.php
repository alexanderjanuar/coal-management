<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\Application;
use App\Models\ApplicationClient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Model;
use Closure;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class ApplicationsRelationManager extends RelationManager
{
    use InteractsWithRecord;
    protected static string $relationship = 'applications';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Application Details')
                    ->description('Select the application and set account credentials')
                    ->schema([
                        Select::make('application_id')
                            ->required()
                            ->label('Application')
                            ->options(Application::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->helperText('Choose the application this account belongs to'),
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('username')
                                    ->required()
                                    ->maxLength(255)
                                    ->autocomplete(false)
                                    ->helperText('Enter the account username'),

                                Forms\Components\TextInput::make('password')
                                    ->required()
                                    ->password()
                                    ->maxLength(255)
                                    ->autocomplete('new-password')
                                    ->helperText('Enter a secure password'),
                            ]),
                    ]),

                Section::make('Account Settings (Optional)')
                    ->description('Configure account activation and period settings')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('activation_code')
                                    ->maxLength(255)
                                    ->helperText('Optional activation code for the account'),

                                DatePicker::make('account_period')
                                    ->label('Account Period')
                                    ->displayFormat('d/m/Y')
                                    ->helperText('Select the account validity period'),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('username')
            ->columns([
                Tables\Columns\TextColumn::make('application.name')
                    ->label('Application')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Click to copy username'),

                Tables\Columns\IconColumn::make('password')
                    ->label('Has Password')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\TextColumn::make('activation_code')
                    ->label('Activation Code')
                    ->searchable()
                    ->toggleable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('account_period')
                    ->label('Account Period')
                    ->date('d M Y')
                    ->sortable()
                    ->badge()
                    ->color(
                        fn($record) =>
                        $record->account_period > now() ? 'success' : 'danger'
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created Date')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add New Account'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
