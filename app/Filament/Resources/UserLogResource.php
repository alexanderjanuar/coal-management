<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserLogResource\Pages;
use App\Models\UserLog;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserLogResource extends Resource
{
    protected static ?string $model = UserLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Pengguna Aktif';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 100;

    protected static ?string $recordTitleAttribute = 'user.name';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole(['direktur','super-admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('url')
                    ->label('URL')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\DateTimePicker::make('created_at')
                    ->label('Access Time')
                    ->default(now())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable()
                    ->description(fn (UserLog $record): string => $record->user->email ?? '')
                    ->weight('medium'),

                Tables\Columns\BadgeColumn::make('activity_status')
                    ->label('Status')
                    ->getStateUsing(fn (UserLog $record): string => $record->activity_status_text)
                    ->colors([
                        'success' => fn ($state): bool => str_contains($state, 'Online'),
                        'warning' => fn ($state): bool => str_contains($state, 'Aktif'),
                        'info' => fn ($state): bool => str_contains($state, 'Terakhir') && !str_contains($state, 'Tidak'),
                        'gray' => fn ($state): bool => str_contains($state, 'Terakhir'),
                        'danger' => fn ($state): bool => str_contains($state, 'Tidak aktif'),
                    ])
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('created_at', $direction === 'asc' ? 'desc' : 'asc');
                    }),

                Tables\Columns\TextColumn::make('url')
                    ->label('Page Yang Terakhir Diakses')
                    ->searchable()
                    ->getStateUsing(fn (UserLog $record): string => $record->page_name ?? $record->url)
                    ->limit(50)
                    ->tooltip(fn (UserLog $record): string => $record->url)
                    ->wrap(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Last Active')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable()
                    ->description(fn (UserLog $record): string => $record->last_activity)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // User Filter
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                // Activity Status Filter - Consolidated
                SelectFilter::make('activity_status')
                    ->label('Activity Status')
                    ->options([
                        '5min' => '🟢 Online (Last 5 min)',
                        '10min' => '🟡 Active (Last 10 min)',
                        '30min' => '🔵 Active (Last 30 min)',
                        '1hour' => '⚪ Active (Last hour)',
                        'today' => '📅 Active Today',
                        'inactive' => '🔴 Inactive (Over 1 hour)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        
                        return match($value) {
                            '5min' => $query->where('created_at', '>=', now()->subMinutes(5)),
                            '10min' => $query->where('created_at', '>=', now()->subMinutes(10)),
                            '30min' => $query->where('created_at', '>=', now()->subMinutes(30)),
                            '1hour' => $query->where('created_at', '>=', now()->subHours(1)),
                            'today' => $query->whereDate('created_at', today()),
                            'inactive' => $query->where('created_at', '<', now()->subHours(1)),
                            default => $query,
                        };
                    }),

                // Date Range Filter
                Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators[] = 'From: ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators[] = 'Until: ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    })
                    ->columnSpan(2), // Span 2 columns for date range
            ],)
            ->filtersFormColumns(4) // Changed to 3 columns
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->poll('30s'); // Auto-refresh every 30 seconds for live monitoring
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
            'index' => Pages\ListUserLogs::route('/'),
            // 'create' => Pages\CreateUserLog::route('/create'),
            // 'view' => Pages\ViewUserLog::route('/{record}'),
            // 'edit' => Pages\EditUserLog::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        // Show count of currently active users
        return (string) UserLog::getCurrentlyActiveUsersCount();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $activeCount = UserLog::getCurrentlyActiveUsersCount();
        
        return match(true) {
            $activeCount > 10 => 'success',
            $activeCount > 5 => 'warning',
            $activeCount > 0 => 'info',
            default => 'gray'
        };
    }

    public static function getEloquentQuery(): Builder
    {
        // Get only the latest log per user using a subquery
        return parent::getEloquentQuery()
            ->with(['user'])
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('user_log')
                    ->groupBy('user_id');
            })
            ->latest('created_at');
    }
}