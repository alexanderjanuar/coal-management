<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectStepResource\Pages;
use App\Filament\Resources\ProjectStepResource\RelationManagers;
use App\Models\Project;
use App\Models\ProjectStep;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use IbrahimBougaoua\FilaProgress\Tables\Columns\ProgressBar;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;

class ProjectStepResource extends Resource
{
    protected static ?string $model = ProjectStep::class;
    protected static ?string $navigationGroup = 'Project Management';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
        
            ->schema([

                Section::make('Step Information')
                    ->description('Basic information about the project step')
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Step Name')
                            ->required(),
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
                            ->label('Step Description')
                            ->columnSpanFull(),
                    ])->columns(3),

                Section::make('Tasks')
                    ->aside()
                    ->description('Define the tasks required for this project step')
                    ->schema([
                        Repeater::make('tasks')
                            ->label('Project Tasks')
                            ->relationship('tasks')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->helperText('Enter the task title'),
                                TextInput::make('description')
                                    ->required()
                                    ->helperText('Describe what needs to be done'),
                            ])
                            ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                            ->addActionLabel('Add New Task')
                            ->columns(2),
                    ]),

                Section::make('Required Documents')
                    ->description('Specify the documents needed for this project step')
                    ->aside()
                    ->schema([
                        Repeater::make('requiredDocuments')
                            ->label('Document List')
                            ->relationship('requiredDocuments')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->helperText('Enter document name'),
                                TextInput::make('description')
                                    ->required()
                                    ->helperText('Describe the document requirements'),
                                FileUpload::make('file_path')
                                    ->columnSpanFull()
                                    ->openable()
                                    ->downloadable()
                                    ->helperText('Upload the document file')
                            ])
                            ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                            ->columns(2)
                    ])
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Project Step Name')
                    ->searchable(),
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
                ProgressBar::make('bar')
                    ->label('Progress')
                    ->getStateUsing(function ($record) {
                        $total = $record->tasks()->count();
                        $progress = $record->tasks()->where('status', 'completed')->count();
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
            ->defaultGroup('project.client.name')
            ->filters([
                //
            ])
            ->actions([
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjectSteps::route('/'),
            'create' => Pages\CreateProjectStep::route('/create'),
            'view' => Pages\ViewProjectStep::route('/{record}'),
            'edit' => Pages\EditProjectStep::route('/{record}/edit'),
        ];
    }
}
