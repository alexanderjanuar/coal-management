<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\Client;
use App\Models\Project;
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
use Filament\Tables\Filters\SelectFilter;
// use Guava\FilamentModalRelationManagers\Concerns\CanBeEmbeddedInModals;
class StepsRelationManager extends RelationManager
{
    protected static string $relationship = 'steps';

    // use CanBeEmbeddedInModals;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Step Detail')
                        ->description('Set the Project Detail')
                        ->schema([
                            Forms\Components\TextInput::make('name'),
                            Select::make('project_id')
                                ->required()
                                ->label('Project Name')
                                ->searchable()
                                ->options(Project::all()->pluck('name', 'id'))
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $highestOrder = \App\Models\ProjectStep::where('project_id', $state)
                                            ->max('order') ?? 0;
                                        $set('order', $highestOrder + 1);
                                    }
                                }),
                            TextInput::make('order')
                                ->numeric()
                                ->readOnly(),
                            Textarea::make('description')
                                ->columnSpanFull(),
                        ])->columns(3),
                    Wizard\Step::make('Task')
                        ->description('Set the Project Detail')
                        ->schema([
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
                    Wizard\Step::make('Documents')
                        ->description('Set the Project Detail')
                        ->schema([
                            Repeater::make('requiredDocuments')
                                ->label('Required Documents')
                                ->relationship('requiredDocuments')
                                ->schema([
                                    TextInput::make('name')->required(),
                                    TextInput::make('description')->required(),
                                ])
                                ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                                ->columns(2)
                        ])
                ])
                    ->skippable()
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
                SelectFilter::make('project')
                    ->options(
                        Project::query()
                            ->whereHas('steps')
                            ->with('client')
                            ->get()
                            ->mapWithKeys(function ($project) {
                                return [$project->id => $project->client->name . ' - ' . $project->name];
                            })
                    )
                    ->query(function (Builder $query, array $data) {
                        if (blank($data['value'])) {
                            return $query;
                        }

                        return $query->where('project_id', $data['value']);
                    })
                    ->searchable()
                    ->preload()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->recordUrl(
                fn(Model $record): string => route('filament.admin.resources.project-steps.view', ['record' => $record]),
            )
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
