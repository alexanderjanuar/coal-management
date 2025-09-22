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
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Filament\Notifications\Notification;

class SuggestionResource extends Resource
{
    protected static ?string $model = Suggestion::class;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole(['super-admin','admin','direktur','project-manager']);
    }   

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?string $navigationLabel = 'Pengembangan Sistem';

    protected static ?string $modelLabel = 'Pengembangan';

    protected static ?string $pluralModelLabel = 'Pengembangan Sistem';

    protected static ?string $navigationGroup = 'Manajemen';

    protected static ?int $navigationSort = 99;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'new')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('status', 'new')->count() > 5 ? 'warning' : 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengguna')
                    ->description('Detail pengguna yang mengajukan usulan pengembangan')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Pengguna')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn ($context) => $context === 'create')
                            ->default(auth()->id()),
                            
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Tanggal Pengajuan')
                            ->disabled()
                            ->default(now()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Pengembangan')
                    ->description('Informasi lengkap tentang usulan pengembangan sistem')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan judul usulan pengembangan')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('type')
                            ->label('Jenis')
                            ->required()
                            ->options([
                                'bug' => 'Perbaikan Bug',
                                'feature' => 'Fitur Baru',
                                'improvement' => 'Peningkatan',
                                'other' => 'Lainnya',
                            ])
                            ->native(false)
                            ->helperText('Pilih jenis usulan pengembangan'),

                        Forms\Components\Select::make('priority')
                            ->label('Prioritas')
                            ->required()
                            ->options([
                                'low' => 'Rendah',
                                'medium' => 'Sedang',
                                'high' => 'Tinggi',
                            ])
                            ->native(false)
                            ->helperText('Tentukan tingkat prioritas'),

                        Forms\Components\RichEditor::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->placeholder('Jelaskan usulan pengembangan secara detail')
                            ->toolbarButtons([
                                'bold',
                                'bulletList',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'undo',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status & Penanganan')
                    ->description('Informasi status dan penanganan usulan')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'new' => 'Baru',
                                'in_review' => 'Sedang Ditinjau',
                                'accepted' => 'Diterima',
                                'rejected' => 'Ditolak',
                                'implemented' => 'Sudah Diterapkan',
                            ])
                            ->default('new')
                            ->native(false)
                            ->required(),

                        Forms\Components\Select::make('handled_by')
                            ->label('Ditangani Oleh')
                            ->relationship('handler', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih penanggungjawab'),

                        Forms\Components\DateTimePicker::make('handled_at')
                            ->label('Tanggal Ditangani')
                            ->placeholder('Akan diisi otomatis')
                            ->native(false),

                        Forms\Components\RichEditor::make('admin_notes')
                            ->label('Catatan Admin')
                            ->placeholder('Catatan atau feedback untuk usulan ini')
                            ->toolbarButtons([
                                'bold',
                                'bulletList',
                                'italic',
                                'link',
                                'redo',
                                'undo',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->visible(fn () => auth()->user()->hasRole(['super-admin', 'admin'])),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user')
                    ->iconColor('primary')
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->wrap()
                    ->limit(50)
                    ->weight(FontWeight::SemiBold),

                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'bug' => 'danger',
                        'feature' => 'success',
                        'improvement' => 'warning',
                        'other' => 'gray',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'bug' => 'Perbaikan Bug',
                        'feature' => 'Fitur Baru',
                        'improvement' => 'Peningkatan',
                        'other' => 'Lainnya',
                        default => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'low' => 'info',
                        'medium' => 'warning',
                        'high' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                        default => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'new' => 'gray',
                        'in_review' => 'warning',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'implemented' => 'primary',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'new' => 'Baru',
                        'in_review' => 'Sedang Ditinjau',
                        'accepted' => 'Diterima',
                        'rejected' => 'Ditolak',
                        'implemented' => 'Sudah Diterapkan',
                        default => ucwords(str_replace('_', ' ', $state)),
                    }),

                Tables\Columns\TextColumn::make('handler.name')
                    ->label('Ditangani Oleh')
                    ->default('Belum Ditangani')
                    ->icon('heroicon-m-user-circle')
                    ->iconColor('gray'),

                Tables\Columns\TextColumn::make('handled_at')
                    ->label('Tanggal Ditangani')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Belum Ditangani'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'bug' => 'Perbaikan Bug',
                        'feature' => 'Fitur Baru',
                        'improvement' => 'Peningkatan',
                        'other' => 'Lainnya',
                    ]),
                    
                Tables\Filters\SelectFilter::make('priority')
                    ->label('Prioritas')
                    ->options([
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                    ]),
                    
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'new' => 'Baru',
                        'in_review' => 'Sedang Ditinjau',
                        'accepted' => 'Diterima',
                        'rejected' => 'Ditolak',
                        'implemented' => 'Sudah Diterapkan',
                    ]),

                Tables\Filters\Filter::make('handled')
                    ->label('Sudah Ditangani')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('handled_by')),

                Tables\Filters\Filter::make('unhandled')
                    ->label('Belum Ditangani')
                    ->query(fn (Builder $query): Builder => $query->whereNull('handled_by')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),
                    
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->visible(fn () => auth()->user()->hasRole(['super-admin', 'admin'])),

                Tables\Actions\Action::make('assign_to_me')
                    ->label('Tangani Saya')
                    ->icon('heroicon-o-hand-raised')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Tangani Usulan Pengembangan')
                    ->modalDescription('Apakah Anda yakin ingin menangani usulan ini?')
                    ->action(function (Suggestion $record) {
                        $record->update([
                            'handled_by' => auth()->id(),
                            'handled_at' => now(),
                            'status' => 'in_review',
                        ]);
                        
                        Notification::make()
                            ->title('Berhasil!')
                            ->body('Usulan berhasil diambil untuk ditangani.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Suggestion $record) => 
                        auth()->user()->hasRole(['super-admin', 'admin']) && 
                        !$record->handled_by
                    ),

                Tables\Actions\Action::make('mark_implemented')
                    ->label('Tandai Selesai')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Tandai Sebagai Sudah Diterapkan')
                    ->modalDescription('Apakah usulan ini sudah berhasil diterapkan?')
                    ->action(function (Suggestion $record) {
                        $record->update(['status' => 'implemented']);
                        
                        Notification::make()
                            ->title('Berhasil!')
                            ->body('Usulan ditandai sebagai sudah diterapkan.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Suggestion $record) => 
                        auth()->user()->hasRole(['super-admin', 'admin']) && 
                        $record->status === 'accepted'
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_in_review')
                        ->label('Tandai Sedang Ditinjau')
                        ->icon('heroicon-o-eye')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'in_review']);
                            });
                            
                            Notification::make()
                                ->title('Berhasil!')
                                ->body(count($records) . ' usulan ditandai sedang ditinjau.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => auth()->user()->hasRole(['super-admin', 'admin'])),

                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus')
                        ->visible(fn () => auth()->user()->hasRole(['super-admin'])),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-light-bulb')
            ->emptyStateHeading('Belum Ada Usulan Pengembangan')
            ->emptyStateDescription('Mulai dengan membuat usulan pengembangan sistem baru.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Usulan Baru')
                    ->icon('heroicon-o-plus'),
            ]);
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