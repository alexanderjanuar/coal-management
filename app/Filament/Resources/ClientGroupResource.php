<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientGroupResource\Pages;
use App\Filament\Resources\ClientGroupResource\RelationManagers\ClientsRelationManager;
use App\Models\Client;
use App\Models\ClientGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\View as InfolisView;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;

class ClientGroupResource extends Resource
{
    protected static ?string $model = ClientGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Client Management';

    protected static ?string $navigationLabel = 'Grup Client';

    protected static ?string $modelLabel = 'Grup Client';

    protected static ?string $pluralModelLabel = 'Grup Client';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('clients.*');
    }

    public static function canAccess(): bool
    {
        return auth()->user()->can('clients.*');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Grup')
                ->icon('heroicon-o-building-library')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Grup')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Grup Harita, Grup Adaro'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active'   => 'Aktif',
                                'inactive' => 'Tidak Aktif',
                            ])
                            ->default('active')
                            ->required(),
                    ]),

                    Forms\Components\FileUpload::make('logo')
                        ->label('Logo Grup')
                        ->image()
                        ->imageEditor()
                        ->directory('client-groups/logos')
                        ->disk('public')
                        ->maxSize(2048)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Kontak & Alamat')
                ->icon('heroicon-o-map-pin')
                ->schema([
                    Forms\Components\Textarea::make('address')
                        ->label('Alamat')
                        ->rows(2)
                        ->maxLength(500)
                        ->columnSpanFull(),

                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('contact_name')
                            ->label('Nama Kontak')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('contact_email')
                            ->label('Email Kontak')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('contact_phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(20),
                    ]),
                ]),

            Forms\Components\Section::make('Catatan')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label('Catatan Internal')
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull(),
                ])
                ->collapsed(),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Grid::make(3)->schema([

                InfoSection::make('Profil Grup')
                    ->columnSpan(1)
                    ->icon('heroicon-o-building-library')
                    ->schema([
                        Grid::make(4)->schema([
                            ImageEntry::make('logo')
                                ->label('')
                                ->circular()
                                ->height(72)
                                ->width(72)
                                ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF&size=128')
                                ->columnSpan(1),

                            Grid::make(1)->schema([
                                TextEntry::make('name')
                                    ->label('Nama Grup')
                                    ->weight(FontWeight::Bold)
                                    ->size(TextEntry\TextEntrySize::Large),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'active'   => 'success',
                                        'inactive' => 'danger',
                                        default    => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'active'   => 'Aktif',
                                        'inactive' => 'Tidak Aktif',
                                        default    => $state,
                                    }),
                            ])->columnSpan(3),
                        ]),

                        TextEntry::make('address')
                            ->label('Alamat')
                            ->icon('heroicon-o-map-pin')
                            ->placeholder('Tidak ada alamat')
                            ->columnSpanFull(),

                        TextEntry::make('contact_name')
                            ->label('Nama Kontak')
                            ->icon('heroicon-o-user')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('contact_email')
                            ->label('Email Kontak')
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('contact_phone')
                            ->label('Nomor Telepon')
                            ->icon('heroicon-o-phone')
                            ->copyable()
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('notes')
                            ->label('Catatan Internal')
                            ->icon('heroicon-o-document-text')
                            ->placeholder('Tidak ada catatan')
                            ->columnSpanFull(),
                    ]),

                InfoSection::make('Client dalam Grup')
                    ->columnSpan(2)
                    ->icon('heroicon-o-users')
                    ->schema([
                        InfolisView::make('filament.resources.client-group-resource.client-cards')
                            ->viewData(fn ($record) => [
                                'clients' => $record->clients()->orderBy('name')->get(),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF')
                    ->size(40),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Grup')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn ($record) => $record->address ?? ''),

                Tables\Columns\TextColumn::make('clients_count')
                    ->label('Jumlah Client')
                    ->counts('clients')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'gray',
                        $state <= 3  => 'warning',
                        default      => 'success',
                    })
                    ->icon('heroicon-o-users')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('contact_name')
                    ->label('Kontak')
                    ->searchable()
                    ->description(fn ($record) => $record->contact_email ?? '')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('contact_phone')
                    ->label('Telepon')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'   => 'success',
                        'inactive' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active'   => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                        default    => $state,
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since()
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active'   => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Detail'),

                    Tables\Actions\EditAction::make()
                        ->label('Edit'),

                    Tables\Actions\Action::make('assign_clients')
                        ->label('Tambah Client')
                        ->icon('heroicon-o-user-plus')
                        ->color('primary')
                        ->modalHeading(fn ($record) => "Tambah Client ke \"{$record->name}\"")
                        ->modalDescription(function ($record) {
                            $count = $record->clients()->count();
                            return $count > 0
                                ? "Grup ini sudah memiliki {$count} client. Pilih client tambahan yang ingin dimasukkan."
                                : 'Pilih satu atau lebih client untuk ditambahkan ke grup ini.';
                        })
                        ->modalWidth('lg')
                        ->modalIcon('heroicon-o-user-plus')
                        ->form([
                            Forms\Components\Select::make('client_ids')
                                ->label('Pilih Client')
                                ->multiple()
                                ->options(fn () => Client::whereNull('group_id')
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->placeholder('Ketik untuk mencari client...')
                                ->helperText('Hanya client yang belum tergabung dalam grup manapun yang ditampilkan.')
                                ->noSearchResultsMessage('Tidak ada client yang ditemukan.')
                                ->loadingMessage('Memuat daftar client...'),
                        ])
                        ->modalSubmitActionLabel('Tambahkan ke Grup')
                        ->modalCancelActionLabel('Batal')
                        ->action(function (array $data, $record): void {
                            $count = count($data['client_ids']);
                            Client::whereIn('id', $data['client_ids'])->update(['group_id' => $record->id]);

                            Notification::make()
                                ->success()
                                ->title("{$count} client berhasil ditambahkan")
                                ->body("Client telah ditambahkan ke grup \"{$record->name}\".")
                                ->send();
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Grup Client')
                        ->modalDescription(function ($record) {
                            $count = $record->clients()->count();
                            return $count > 0
                                ? "Grup \"{$record->name}\" memiliki {$count} client. Semua client akan kehilangan asosiasi grupnya."
                                : "Apakah Anda yakin ingin menghapus grup \"{$record->name}\"?";
                        })
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->before(fn ($record) => $record->clients()->update(['group_id' => null])),
                ])
                ->tooltip('Aksi')
                ->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-building-library')
            ->emptyStateHeading('Belum ada grup client')
            ->emptyStateDescription('Buat grup untuk mengorganisir client yang terafiliasi.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Grup')
                    ->icon('heroicon-o-plus'),
            ])
            ->defaultSort('name')
            ->striped()
            ->paginated([10, 25, 50]);
    }

    public static function getRelations(): array
    {
        return [
            ClientsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListClientGroups::route('/'),
            'create' => Pages\CreateClientGroup::route('/create'),
            'view'   => Pages\ViewClientGroup::route('/{record}'),
            'edit'   => Pages\EditClientGroup::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'contact_name', 'contact_email'];
    }
}
