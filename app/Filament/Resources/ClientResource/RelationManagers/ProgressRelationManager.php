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
use Filament\Forms\Components\Repeater;

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
                    ])->columns(2),
                Section::make('steps')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        
                    ])->columns(2)
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
            ]);
    }
}
