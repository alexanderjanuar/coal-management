<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuggestionResource\Pages;
use App\Filament\Resources\SuggestionResource\RelationManagers;
use App\Filament\Resources\SuggestionResource\Widgets\CreateSuggestionWidget;
use App\Models\Suggestion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SuggestionResource extends Resource
{
    protected static ?string $model = Suggestion::class;

      public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole(['super-admin','admin']);
    }   
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Kotak Saran';

    protected static ?string $modelLabel = 'Kotak Saran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // User Column
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Submitted By')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user')
                    ->iconColor('primary'),

                // Title Column
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->wrap()
                    ->limit(50),

                // Type Badge
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'bug' => 'danger',
                        'feature' => 'success',
                        'improvement' => 'warning',
                        'other' => 'gray',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),

                // Priority Badge
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'low' => 'info',
                        'medium' => 'warning',
                        'high' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),

                // Status Badge
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'new' => 'gray',
                        'in_review' => 'warning',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'implemented' => 'primary',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => ucwords(str_replace('_', ' ', $state))),

                // Handler Info
                Tables\Columns\TextColumn::make('handler.name')
                    ->label('Handled By')
                    ->default('Unassigned')
                    ->icon('heroicon-m-user-circle')
                    ->iconColor('gray'),

                // Handled Date
                Tables\Columns\TextColumn::make('handled_at')
                    ->label('Handled Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(),

                // Creation Date
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted At')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'bug' => 'Bug Report',
                        'feature' => 'Feature Request',
                        'improvement' => 'Improvement',
                        'other' => 'Other',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'New',
                        'in_review' => 'In Review',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                        'implemented' => 'Implemented',
                    ]),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->emptyStateIcon('heroicon-o-light-bulb')
            ->emptyStateHeading('No Suggestions Yet')
            ->emptyStateDescription('Start by creating a new suggestion.');
    }

    public static function getWidgets(): array
    {
        return [
            CreateSuggestionWidget::class,
        ];
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
            'index' => Pages\ListSuggestions::route('/'),
            'create' => Pages\CreateSuggestion::route('/create'),
            'edit' => Pages\EditSuggestion::route('/{record}/edit'),
        ];
    }
}
