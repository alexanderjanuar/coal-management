<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use IbrahimBougaoua\FilaProgress\Infolists\Components\ProgressBarEntry;
use Illuminate\Support\Str;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Model;

class StepsRelationManager extends RelationManager
{
    protected static string $relationship = 'steps';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Project Detail')
                        ->description('Set the Project Detail')
                        ->schema([

                        ]),
                    Wizard\Step::make('Step Detail')
                        ->description('Set the Project Detail')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->columnSpanFull(),
                            Textarea::make('description')
                                ->columnSpanFull(),
                            Repeater::make('tasks')
                                ->label('Project Task')
                                ->relationship('tasks')
                                ->schema([
                                    TextInput::make('title')->required(),
                                    TextInput::make('description')->required(),
                                ])
                                ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                                ->addActionLabel('Add New Task')
                                ->columns(2),

                        ]),
                    Wizard\Step::make('Project Documents')
                        ->description('Set the Project Detail')
                        ->schema([
                            Repeater::make('requiredDocuments')
                                ->label('Required Documents')
                                ->relationship('requiredDocuments')
                                ->schema([
                                    TextInput::make('name')->required(),
                                    TextInput::make('description')->required(),
                                    FileUpload::make('file_path')
                                        ->columnSpanFull()
                                ])
                                ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                                ->columns(2)
                        ])
                ])
                ->columnSpanFull()

            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('project.client.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order')
                    ->badge()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'waiting_for_documents' => 'gray',
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'canceled' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state): string => __(Str::title($state))),
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
                Tables\Actions\CreateAction::make(),
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
