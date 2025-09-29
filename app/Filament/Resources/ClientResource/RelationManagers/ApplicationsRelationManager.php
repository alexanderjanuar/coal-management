<?php
// app/Filament/Resources/ClientResource/RelationManagers/ApplicationsRelationManager.php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\Application;
use App\Models\ApplicationClient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Crypt;

class ApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'applicationCredentials';
    protected static ?string $title = 'Application Credentials';
    protected static ?string $recordTitleAttribute = 'username';

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Application Selection')
                ->description('Pilih aplikasi dan atur kredensial akun')
                ->schema([
                    Select::make('application_id')
                        ->required()
                        ->label('Application')
                        ->options(Application::pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set) => $set('additional_data', []))
                        ->helperText('Pilih aplikasi yang akan dikonfigurasi'),
                ]),

            Section::make('Basic Credentials')
                ->description('Username dan password untuk aplikasi')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('username')
                            ->required()
                            ->maxLength(255)
                            ->autocomplete(false)
                            ->placeholder('Enter username/email')
                            ->helperText('Username untuk login'),

                        Forms\Components\TextInput::make('password')
                            ->required(fn (string $operation) => $operation === 'create')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->autocomplete('new-password')
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null)
                            ->placeholder('Enter password')
                            ->helperText(fn (string $operation) => $operation === 'edit' 
                                ? 'Kosongkan jika tidak ingin mengubah password' 
                                : 'Password untuk login'),
                    ]),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('username')
            ->columns([
                Tables\Columns\ImageColumn::make('application.logo')
                    ->label('')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->application->name ?? 'App') . '&color=7F9CF5&background=EBF4FF'),

                Tables\Columns\TextColumn::make('application.name')
                    ->label('Application')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->badge()
                    ->color(fn ($record) => match($record->application->category) {
                        'tax' => 'success',
                        'accounting' => 'info',
                        'email' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Username copied!'),

                Tables\Columns\TextColumn::make('password')
                    ->label('Password')
                    ->copyable()
                    ->copyMessage('Password copied!'),

                Tables\Columns\TextColumn::make('last_used_at')
                    ->label('Last Used')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Never used'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('application_id')
                    ->label('Application')
                    ->relationship('application', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('application.category')
                    ->label('Category')
                    ->options([
                        'tax' => 'Tax Applications',
                        'accounting' => 'Accounting',
                        'email' => 'Email',
                        'api' => 'API Services',
                        'other' => 'Other',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),
                
                Tables\Filters\Filter::make('expired')
                    ->label('Expired Accounts')
                    ->query(fn ($query) => $query->where('account_period', '<', now()))
                    ->toggle(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Credential')
                    ->icon('heroicon-o-plus')
                    ->modalWidth('3xl'),
            ])
            ->actions([            
                Tables\Actions\EditAction::make()
                    ->modalWidth('3xl'),
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No Application Credentials')
            ->emptyStateDescription('Add credentials for applications used by this client.')
            ->emptyStateIcon('heroicon-o-key')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add First Credential')
                    ->icon('heroicon-o-plus'),
            ]);
    }
}