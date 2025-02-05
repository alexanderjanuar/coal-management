<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Support\Str;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Project Management';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation (): bool
    {
        return false;
    }
    public static function form(Form $form): Form
    {
        return $form
        
            ->schema([
                Forms\Components\TextInput::make('project_step_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\Toggle::make('requires_document')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('projectStep.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Task Title')
                    ->limit(10)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'blocked' => 'danger',
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
            ->actions([
                Action::make('Change Status')
                    ->form([
                        Select::make('status')
                            ->label('Task Status')
                            ->options(
                                function (Task $record) {
                                    if ($record->status == "pending") {
                                        return ['in_progress' => 'In Progress', 'blocked' => 'Blocked'];
                                    } elseif ($record->status == "in_progress") {
                                        return ['completed' => 'Completed', 'blocked' => 'Blocked'];
                                    }
                                }
                            )
                            ->native(false)
                            ->required(),
                    ])
                    ->action(function (array $data, Task $record) {
                        $record->status = $data['status'];
                        $record->save();
                    })
                    ->color('info')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->requiresConfirmation(),

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
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'view' => Pages\ViewTask::route('/{record}'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
