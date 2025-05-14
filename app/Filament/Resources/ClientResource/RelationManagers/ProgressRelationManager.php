<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\Client;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;
// use Guava\FilamentModalRelationManagers\Concerns\CanBeEmbeddedInModals;
use IbrahimBougaoua\FilaProgress\Infolists\Components\ProgressBarEntry;
use IbrahimBougaoua\FilaProgress\Tables\Columns\ProgressBar;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Wizard;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\FileUpload;

class ProgressRelationManager extends RelationManager
{
    protected static string $relationship = 'projects';



    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Project Detail')
                        ->description('Set the Project Detail')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->label('Project Name')
                                ->unique()
                                ->maxLength(255),
                            Select::make('client_id')
                                ->required()
                                ->searchable()
                                ->native(false)
                                ->label('Client')
                                ->options(Client::all()->pluck('name', 'id')),
                            Textarea::make('description')
                                ->columnSpanFull()
                        ])
                    ,
                    Wizard\Step::make('Step & Task')
                        ->description('Set the Project Step & Task')
                        ->schema([
                            Repeater::make('steps')
                                ->label('Project Step')
                                ->addActionLabel('Add New Step')
                                ->relationship()
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->columnSpanFull(),
                                    Textarea::make('description')
                                        ->columnSpanFull(),
                                    Section::make('Tasks')
                                        ->description('Prevent abuse by limiting the number of requests per period')
                                        ->collapsed()
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
                                                ->collapsed()
                                                ->columns(2),
                                        ]),
                                    Section::make('Required Documents')
                                        ->description('Prevent abuse by limiting the number of requests per period')
                                        ->collapsed()
                                        ->schema([
                                            Repeater::make('requiredDocuments')
                                                ->label('Required Documents')
                                                ->relationship('requiredDocuments')
                                                ->schema([
                                                    TextInput::make('name')->required(),
                                                    TextInput::make('description')->required(),
                                                ])
                                                ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                                                ->addActionLabel('Add New Document')
                                                ->collapsed()
                                                ->columns(2)
                                    ])
                                ])
                                ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                                ->collapsible()
                                ->orderColumn('order')
                                ->columnSpanFull(),

                        ]),
                ])
                    ->skippable()
                    ->submitAction(new HtmlString(Blade::render(<<<BLADE
                                <x-filament::button
                                    type="submit"
                                    size="sm"
                                >
                                    Submit
                                </x-filament::button>
                            BLADE)))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                TextColumn::make('name')
                    ->label('Project Name'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'on_hold' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'canceled' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state): string => __(Str::title($state))),
                TextColumn::make('steps_count')->counts('steps')->badge()->label('Project Step'),
                ProgressBar::make('bar')
                    ->label('Progress')
                    ->getStateUsing(function ($record) {
                        $total = $record->steps()->count();
                        $progress = $record->steps()->where('status', 'completed')->count();
                        return [
                            'total' => $total,
                            'progress' => $progress,
                        ];
                    })
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'on_hold' => 'On Hold',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'canceled' => 'Canceled',
                    ])
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->recordUrl(
                fn(Model $record): string => route('filament.admin.resources.projects.view', ['record' => $record]),
            )
            ->bulkActions([
            ]);
    }
}
