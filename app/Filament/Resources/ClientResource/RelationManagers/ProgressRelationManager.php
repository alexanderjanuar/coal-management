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
use Guava\FilamentModalRelationManagers\Concerns\CanBeEmbeddedInModals;
use IbrahimBougaoua\FilaProgress\Infolists\Components\ProgressBarEntry;
use IbrahimBougaoua\FilaProgress\Tables\Columns\ProgressBar;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Model;

class ProgressRelationManager extends RelationManager
{
    protected static string $relationship = 'projects';

    use CanBeEmbeddedInModals;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Project Detail')
                    ->icon('heroicon-o-folder-open')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Project Detail')
                            ->unique()
                            ->maxLength(255),
                        Select::make('client_id')
                            ->required()
                            ->label('Client')
                            ->options(Client::all()->pluck('name', 'id')),
                        Textarea::make('description')
                            ->columnSpanFull()
                    ])
                    ->collapsible()
                    ->columns(2),
                Section::make('Project Step')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        Repeater::make('steps')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->columnSpanFull(),
                                Textarea::make('description')
                                    ->columnSpanFull(),
                                Repeater::make('tasks')
                                    ->relationship('tasks')
                                    ->schema([
                                        TextInput::make('title')->required(),
                                        TextInput::make('description')->required(),
                                    ])
                                    ->columns(2)
                            ])
                            ->orderColumn('order')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->columns(2)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                TextColumn::make('name')
                    ->label('Project Name')
                    ->description(fn(Project $record): string => \Str::limit($record->description, 30, '...')),
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
                fn (Model $record): string => route('filament.admin.resources.projects.view', ['record' => $record]),
            )
            ->bulkActions([
            ]);
    }
}
