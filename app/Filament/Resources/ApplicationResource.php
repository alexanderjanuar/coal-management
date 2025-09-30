<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Models\Application;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;
    protected static ?string $navigationIcon = 'heroicon-o-device-tablet';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $modelLabel = 'Aplikasi';
    protected static ?string $pluralModelLabel = 'Aplikasi';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Aplikasi')
                    ->description('Detail dasar tentang aplikasi')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Aplikasi')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Core Tax, DJP Online, Email')
                                    ->helperText('Nama aplikasi yang akan digunakan'),

                                Forms\Components\Select::make('category')
                                    ->label('Kategori')
                                    ->required()
                                    ->native(false)
                                    ->options([
                                        'tax' => 'Perpajakan',
                                        'accounting' => 'Akuntansi',
                                        'email' => 'Email',
                                        'api' => 'API Services',
                                        'other' => 'Lainnya',
                                    ])
                                    ->default('other')
                                    ->helperText('Kategori aplikasi untuk pengelompokan'),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Deskripsi singkat tentang aplikasi ini...')
                            ->helperText('Jelaskan fungsi dan kegunaan aplikasi')
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('app_url')
                                    ->label('URL Aplikasi')
                                    ->maxLength(255)
                                    ->url()
                                    ->placeholder('https://example.com')
                                    ->helperText('URL aplikasi (opsional)'),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->default(true)
                                    ->helperText('Nonaktifkan jika aplikasi sudah tidak digunakan'),
                            ]),

                        FileUpload::make('logo')
                            ->label('Logo Aplikasi')
                            ->disk('public')
                            ->directory('applications')
                            ->maxSize(2048) // 2MB max
                            ->helperText('Upload logo aplikasi (JPG, PNG - Maks 2MB)')
                            ->columnSpanFull(),
                    ]),

                Section::make('Statistik Penggunaan')
                    ->description('Informasi penggunaan aplikasi ini')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Forms\Components\Placeholder::make('usage_stats')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record) {
                                    return 'Statistik akan ditampilkan setelah aplikasi dibuat.';
                                }

                                $totalClients = $record->applicationClients()->count();
                                $activeClients = $record->activeClients()->count();
                                $lastUsed = $record->applicationClients()
                                    ->whereNotNull('last_used_at')
                                    ->max('last_used_at');

                                return view('filament.modals.application.application-usage-stats', [
                                    'totalClients' => $totalClients,
                                    'activeClients' => $activeClients,
                                    'lastUsed' => $lastUsed,
                                ]);
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->hiddenOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\ImageColumn::make('logo')
                        ->circular()
                        ->size(60)
                        ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF')
                        ->alignCenter(),

                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('name')
                            ->label('Nama')
                            ->searchable()
                            ->sortable()
                            ->weight(FontWeight::Bold)
                            ->size('lg')
                            ->alignCenter(),
                    ]),
                ]),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'tax' => 'Perpajakan',
                        'accounting' => 'Akuntansi',
                        'email' => 'Email',
                        'api' => 'API Services',
                        'other' => 'Lainnya',
                    ])
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif Saja')
                    ->falseLabel('Nonaktif Saja'),

                Tables\Filters\Filter::make('has_clients')
                    ->label('Memiliki Klien')
                    ->query(fn ($query) => $query->has('applicationClients'))
                    ->toggle(),
            ])
            ->actions([
            ])
            ->defaultSort('name', 'asc')
            ->emptyStateHeading('Belum Ada Aplikasi')
            ->emptyStateDescription('Tambahkan aplikasi pertama yang akan digunakan untuk manajemen kredensial klien.')
            ->emptyStateIcon('heroicon-o-device-tablet')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Aplikasi')
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
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
            'index' => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'view' => Pages\ViewApplication::route('/{record}'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
        ];
    }
}