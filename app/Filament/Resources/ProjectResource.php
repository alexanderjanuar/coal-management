<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Filament\Resources\ProjectResource\RelationManagers\ClientRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\StepsRelationManager;
use App\Models\Project;
use App\Models\Client;
use App\Models\User;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use IbrahimBougaoua\FilaProgress\Tables\Columns\ProgressBar;
use Filament\Tables\Filters\SelectFilter;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Forms\Components\Section as FormSection;

use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\FontWeight;
use IbrahimBougaoua\FilaProgress\Infolists\Components\ProgressBarEntry;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use Filament\Actions\CreateAction;


class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationGroup = 'Project Management';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Project Details')
                        ->description('Basic project information')
                        ->icon('heroicon-o-clipboard-document')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->label('Project Name')
                                ->unique(ignoreRecord: true)
                                ->maxLength(255)
                                ->columnSpanFull(),
                            Select::make('client_id')
                                ->required()
                                ->label('Client')
                                ->options(function () {
                                    // For super-admin, show all clients
                                    if (auth()->user()->hasRole('super-admin')) {
                                        return Client::pluck('name', 'id');
                                    }

                                    // For other users, only show their assigned clients
                                    return Client::whereIn(
                                        'id',
                                        auth()->user()->userClients()->pluck('client_id')
                                    )->pluck('name', 'id');
                                })
                                ->searchable()
                                ->live()
                                ->native(false)
                                ->columnSpanFull(),
                            Forms\Components\RichEditor::make('description')
                                ->columnSpanFull()
                        ]),

                    Forms\Components\Wizard\Step::make('Project Steps')
                        ->description('Configure steps, tasks, and documents')
                        ->icon('heroicon-o-squares-plus')
                        ->schema([
                            Repeater::make('steps')
                                ->label('Project Step')
                                ->addActionLabel('Add New Step')
                                ->relationship()
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->columnSpanFull(),
                                    Forms\Components\RichEditor::make('description')
                                        ->toolbarButtons([
                                            'bold',
                                            'bulletList',
                                            'italic',
                                            'link',
                                            'orderedList',
                                            'undo',
                                            'redo',
                                        ])
                                        ->columnSpanFull(),
                                    FormSection::make('Tasks')
                                        ->description('Add and manage tasks for this project step')
                                        ->collapsible()
                                        ->schema([
                                            Repeater::make('tasks')
                                                ->label('Project Task')
                                                ->relationship('tasks')
                                                ->schema([
                                                    TextInput::make('title')
                                                        ->required(),
                                                    Forms\Components\RichEditor::make('description')
                                                        ->required()
                                                        ->toolbarButtons([
                                                            'bold',
                                                            'bulletList',
                                                            'italic',
                                                            'link',
                                                            'orderedList',
                                                            'undo',
                                                            'redo',
                                                        ]),
                                                ])
                                                ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                                                ->addActionLabel('Add New Task'),
                                        ]),
                                    FormSection::make('Required Documents')
                                        ->description('Specify required documents for this project step')
                                        ->collapsible()
                                        ->schema([
                                            Repeater::make('requiredDocuments')
                                                ->label('Required Documents')
                                                ->relationship('requiredDocuments')
                                                ->schema([
                                                    TextInput::make('name')
                                                        ->required(),
                                                    Forms\Components\RichEditor::make('description')
                                                        ->toolbarButtons([
                                                            'bold',
                                                            'bulletList',
                                                            'italic',
                                                            'link',
                                                            'orderedList',
                                                            'undo',
                                                            'redo',
                                                        ]),
                                                ])
                                                ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                                                ->addActionLabel('Add New Document')
                                        ])
                                ])
                                ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                                ->orderColumn('order')
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Wizard\Step::make('Project Members')
                        ->description('Assign team members to the project')
                        ->icon('heroicon-o-users')
                        ->schema([
                            Repeater::make('userProject')
                                ->label('')
                                ->relationship()
                                ->schema([
                                    Select::make('user_id')
                                        ->label('Member')
                                        ->options(function (Forms\Get $get) {
                                            $clientId = $get('../../client_id');

                                            if (!$clientId) {
                                                return [];
                                            }

                                            return User::whereHas('userClients', function ($query) use ($clientId) {
                                                $query->where('client_id', $clientId);
                                            })->pluck('name', 'id');
                                        })
                                        ->live()
                                        ->required()
                                        ->searchable()
                                        ->distinct()
                                        ->native(false)
                                        ->helperText(function (Forms\Get $get) {
                                            $clientId = $get('../../client_id');
                                            return $clientId
                                                ? 'Select a user from this client'
                                                : 'Please select a client first';
                                        }),
                                ])
                                ->addActionLabel('Add New Member')
                                ->columnSpanFull(),
                        ]),
                ])->columnSpanFull()
                    ->skippable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client Name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Project Name')
                    ->description(
                        fn(Project $record): string =>
                        $record->description
                        ? \Str::limit($record->description, 45, '...')
                        : '-'
                    )
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'on_hold' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'canceled' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state): string => __(Str::title($state))),
                ProgressBar::make('bar')
                    ->label('Progress')
                    ->getStateUsing(function ($record) {
                        $total = $record->steps()->count();
                        $progress = $record->steps()->where('status', 'completed')->count();
                        return [
                            'total' => $total,
                            'progress' => $progress,
                        ];
                    }),
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
                SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        'draft' => 'Draft',
                        'on_hold' => 'On Hold',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'canceled' => 'Canceled',
                    ])
            ])
            ->actions([
                RelationManagerAction::make('project-step-relation-manager')
                    ->label('Project Step')
                    ->slideOver()
                    ->Icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->relationManager(StepsRelationManager::make()),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
                //
            StepsRelationManager::class,
            ClientRelationManager::class
        ];
    }


    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Project Detail')
                    ->description('Detail about the project for the client')
                    ->aside()
                    ->schema([
                        ProgressBarEntry::make('bar')
                            ->label('Progress')
                            ->getStateUsing(function ($record) {
                                $total = $record->steps()->count();
                                $progress = $record->steps()->where('status', 'completed')->count();
                                return [
                                    'total' => $total,
                                    'progress' => $progress,
                                ];
                            })
                            ->columnSpanFull(),
                        TextEntry::make('client.name'),
                        TextEntry::make('name')
                            ->label('Project Name'),

                        TextEntry::make('status')
                            ->label('Status')->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'draft' => 'gray',
                                'on_hold' => 'gray',
                                'in_progress' => 'warning',
                                'completed' => 'success',
                                'canceled' => 'danger',
                            })
                            ->formatStateUsing(fn(string $state): string => __(Str::title($state))),
                        TextEntry::make('description')

                    ])
                    ->columns(2)
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
