<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Filament\Resources\ProjectResource\RelationManagers\ClientRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\StepsRelationManager;
use App\Models\Project;
use App\Models\Client;
use App\Models\User;
use App\Models\Sop;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Support\Enums\Alignment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use IbrahimBougaoua\FilaProgress\Tables\Columns\ProgressBar;
use Filament\Tables\Filters\SelectFilter;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Forms\Components\Section as FormSection;
use Filament\Tables\Actions\Action;
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
use Carbon\Carbon;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationGroup = 'Project Management';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard';

    protected static ?string $recordTitleAttribute = 'name';


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
                            Select::make('type')
                                ->label('Type')
                                ->options([
                                    'single' => 'On Spot',
                                    'monthly' => 'Monthly',
                                    'yearly' => 'Yearly',
                                ])
                                ->required()
                                ->native(false),
                            Select::make('sop_id')
                                ->label('Standard Operating Procedure')
                                ->options(Sop::query()->pluck('name', 'id'))
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if (!$state) {
                                        $set('steps', []);
                                        return;
                                    }

                                    $sop = Sop::with(['steps.tasks', 'steps.requiredDocuments'])->find($state);
                                    if (!$sop)
                                        return;

                                    // Set project type and type from SOP
                                    $set('type', $sop->type);
                                    $set('project_type', $sop->project_type);

                                    // Prepare steps data
                                    $stepsData = $sop->steps->map(function ($step) {
                                        return [
                                            'name' => $step->name,
                                            'description' => $step->description,
                                            'order' => $step->order,
                                            'priority' => $step->priority,
                                            'tasks' => $step->tasks->map(fn($task) => [
                                                'title' => $task->title,
                                                'description' => $task->description,
                                                'requires_document' => $task->requires_document,
                                            ])->toArray(),
                                            'requiredDocuments' => $step->requiredDocuments->map(fn($doc) => [
                                                'name' => $doc->name,
                                                'description' => $doc->description,
                                                'is_required' => $doc->is_required,
                                            ])->toArray(),
                                        ];
                                    })->toArray();

                                    $set('steps', $stepsData);
                                })
                                ->native(false),
                            DatePicker::make('due_date')
                                ->required()
                                ->date()
                                ->afterStateHydrated(function ($state) {
                                    return $state ? Carbon::parse($state)->format('d M Y') : '-';
                                })
                                ->beforeStateDehydrated(function ($state) {
                                    return $state ? Carbon::parse($state)->format('Y-m-d') : null;
                                })
                                ->native(false),

                            Forms\Components\RichEditor::make('description')
                                ->columnSpanFull(),

                        ])->columns(3),

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
                                                        ->label('Task Title')
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
                                                ->collapsed()
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
                                                        ->label('Documents Name')
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
                                                ->collapsed()
                                                ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                                                ->addActionLabel('Add New Document')
                                        ])
                                ])
                                ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                                ->orderColumn('order')
                                ->collapsed()
                                ->reorderable(false)
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
                                            })
                                                ->whereDoesntHave('roles', function ($query) {
                                                    $query->where('name', 'direktur');
                                                })
                                                ->pluck('name', 'id');
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
                                        })
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ])
                                ->addActionLabel('Add New Member')
                                ->columnSpanFull(),
                        ]),
                ])
                    ->columnSpanFull()
                    ->skippable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No. ')
                    ->rowIndex()
                    ->sortable(false),
                // Client Information
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client Name')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->numeric()
                    ->sortable(),

                // Project Information
                Tables\Columns\TextColumn::make('name')
                    ->label('Project Name')
                    ->weight(FontWeight::Bold)
                    ->searchable(),

                // Project Status
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft', 'on_hold' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'canceled' => 'danger',
                    })
                    ->sortable()
                    ->formatStateUsing(
                        fn(string $state): string =>
                        __(Str::title(str_replace('_', ' ', $state)))
                    ),

                // Progress Bar
                ProgressBar::make('bar')
                    ->label('Progress')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        // Get all steps for the project
                        $steps = $record->steps;

                        // Initialize counters for total and completed items
                        $totalItems = 0;
                        $completedItems = 0;

                        foreach ($steps as $step) {
                            // Count step itself
                            $totalItems++;
                            if ($step->status === 'completed') {
                                $completedItems++;
                            }

                            // Count and check tasks
                            $tasks = $step->tasks;
                            $totalItems += $tasks->count();
                            $completedItems += $tasks->where('status', 'completed')->count();

                            // Count and check required documents
                            $documents = $step->requiredDocuments;
                            $totalItems += $documents->count();
                            $completedItems += $documents->where('status', 'approved')->count();
                        }

                        return [
                            'total' => $totalItems ?: 1, // Prevent division by zero
                            'progress' => $completedItems,
                        ];
                    })

                    ->tooltip(function ($record) {
                        // Add tooltip to show detailed progress
                        $steps = $record->steps;

                        $totalItems = 0;
                        $completedItems = 0;

                        foreach ($steps as $step) {
                            $totalItems++;
                            if ($step->status === 'completed') {
                                $completedItems++;
                            }

                            $totalItems += $step->tasks->count();
                            $completedItems += $step->tasks->where('status', 'completed')->count();

                            $totalItems += $step->requiredDocuments->count();
                            $completedItems += $step->requiredDocuments->where('status', 'approved')->count();
                        }

                        return "Completed: {$completedItems} / {$totalItems} items";
                    }),

                // Timestamps
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // Filters
            ->filters([
                SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        'draft' => 'Draft',
                        'on_hold' => 'On Hold',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'canceled' => 'Canceled',
                    ]),
                DateRangeFilter::make('due_date'),
            ])
            // Row Actions
            ->actions([
                Tables\Actions\ActionGroup::make([
                    RelationManagerAction::make('project-step-relation-manager')
                        ->label('Project Step')
                        ->slideOver()
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->relationManager(StepsRelationManager::make()),
                    Tables\Actions\ViewAction::make()
                        ->icon('heroicon-o-eye'),
                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->label('Actions')
            ])
            ->recordUrl(
                fn(Project $record): string =>
                static::getUrl('view', ['record' => $record])
            )
            ->defaultSort('status', 'asc')
            ->groups([
                'client.name',
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if (auth()->user()->hasRole('super-admin')) {
                    return $query;
                } else {
                    $query->whereIn('id', auth()->user()->userClients->pluck('client_id'));
                    return $query;
                }
            })
            // Bulk Actions
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

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->name;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Client' => $record->client->name,
            'Status' => $record->status,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return ProjectResource::getUrl('view', ['record' => $record]);
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


    public static function sendProjectNotifications(string $title, string $body, $project, string $type = 'info', ?string $action = null): void
    {
        // Create the notification
        $notification = Notification::make()
            ->title($title)
            ->body($body)
            ->icon(match ($type) {
                'success' => 'heroicon-o-check-circle',
                'danger' => 'heroicon-o-x-circle',
                'warning' => 'heroicon-o-exclamation-triangle',
                default => 'heroicon-o-information-circle',
            })
            ->persistent();

        // Add action if provided
        if ($action) {
            $notification->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label($action)
                    ->url(static::getUrl('view', ['record' => $project->id])),
                \Filament\Notifications\Actions\Action::make('Mark As Read')
                    ->markAsRead(),
            ]);
        }

        // Get all users assigned to the project
        $projectUsers = $project->userProject()
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->reject(function ($user) {
                return $user->id === auth()->id(); // Exclude current user
            });

        // Send notifications to all project users
        foreach ($projectUsers as $user) {
            $notification->sendToDatabase($user)->broadcast($user);
        }

        // Send UI notification to current user
        Notification::make()
                    ->title($title)
                    ->body($body)
            ->{$type}()
                ->send();
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
