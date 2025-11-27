<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountRepresentativeResource\Pages;
use App\Models\AccountRepresentative;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\ActionGroup;
use App\Models\Client;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class AccountRepresentativeResource extends Resource
{
    protected static ?string $model = AccountRepresentative::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Account Representative';

    protected static ?string $modelLabel = 'Account Representative';

    protected static ?string $pluralModelLabel = 'Account Representatives';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('account-representative.*');
    }
    
    public static function canAccess(): bool
    {
        return auth()->user()->can('account-representative.*');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Dasar')
                    ->description('Data dasar Account Representative')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Budi Santoso')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('phone_number')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->placeholder('Contoh: +62-21-1234567')
                            ->maxLength(255)
                            ->helperText('Format: +62-21-xxxxxxx atau format lainnya'),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->placeholder('contoh@pajak.go.id')
                            ->maxLength(255)
                            ->helperText('Email resmi dari institusi pajak'),
                    ])->columns(2),

                Section::make('Informasi Kantor')
                    ->description('Detail lokasi dan kantor AR')
                    ->schema([
                        Forms\Components\Select::make('kpp')
                            ->label('KPP')
                            ->options(\App\Services\Clients\KppService::getKppOptions())
                            ->searchable()
                            ->placeholder('Pilih atau cari KPP...')
                            ->helperText('Pilih KPP tempat AR bertugas'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'inactive' => 'Tidak Aktif',
                            ])
                            ->default('active')
                            ->required()
                            ->helperText('Status keaktifan Account Representative'),
                    ])->columns(2),

                Section::make('Catatan Tambahan')
                    ->description('Informasi tambahan tentang AR')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->placeholder('Catatan tambahan tentang AR ini...')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->description(fn (AccountRepresentative $record): string => 
                        $record->kpp ?? 'Lokasi tidak diset'
                    ),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Telepon')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Nomor telepon disalin!')
                    ->placeholder('Tidak ada'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email disalin!')
                    ->placeholder('Tidak ada'),

                Tables\Columns\TextColumn::make('kpp')
                    ->label('Kantor')
                    ->searchable()
                    ->placeholder('Tidak diset')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('clients_count')
                    ->label('Jumlah Klien')
                    ->counts('clients')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                    ])
                    ->selectablePlaceholder(false)
                    ->beforeStateUpdated(function ($record, $state) {
                        // Log activity when status changes
                        activity()
                            ->performedOn($record)
                            ->log("Status AR {$record->name} diubah menjadi " . ($state === 'active' ? 'Aktif' : 'Tidak Aktif'));
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                    ])
                    ->default('active'),

                Tables\Filters\Filter::make('has_clients')
                    ->label('Memiliki Klien')
                    ->query(fn (Builder $query): Builder => $query->has('clients'))
                    ->toggle(),

                Tables\Filters\Filter::make('no_clients')
                    ->label('Tanpa Klien')
                    ->query(fn (Builder $query): Builder => $query->doesntHave('clients'))
                    ->toggle(),

                Tables\Filters\Filter::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Sampai Tanggal'),
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
                    }),
            ])
            ->filtersFormColumns(3)
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Detail'),
                    Tables\Actions\EditAction::make()
                        ->label('Edit'),
                    // Tables\Actions\Action::make('view_clients')
                    //     ->label('Lihat Klien')
                    //     ->icon('heroicon-o-user-group')
                    //     ->color('info')
                    //     ->url(fn (AccountRepresentative $record): string => 
                    //         route('filament.admin.resources.clients.index', [
                    //             'tableFilters[ar_id][value]' => $record->id
                    //         ])
                    //     )
                    //     ->visible(fn (AccountRepresentative $record): bool => $record->clients()->count() > 0),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('assign_to_clients')
                        ->label('Assign ke Klien')
                        ->icon('heroicon-o-user-plus')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('client_ids')
                                ->label('Pilih Klien')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->options(function () {
                                    return Client::query()
                                        ->where('status', 'Active')
                                        ->orderBy('name')
                                        ->pluck('name', 'id');
                                })
                                ->getSearchResultsUsing(function (string $search) {
                                    return Client::query()
                                        ->where('status', 'Active')
                                        ->where('name', 'like', "%{$search}%")
                                        ->limit(50)
                                        ->pluck('name', 'id');
                                })
                                ->required()
                                ->helperText('Pilih satu atau lebih klien untuk diassign ke AR yang dipilih'),
                                
                            Forms\Components\Toggle::make('replace_existing')
                                ->label('Ganti AR yang sudah ada')
                                ->helperText('Jika diaktifkan, akan mengganti AR yang sudah diassign ke klien tersebut')
                                ->default(false),
                                
                            Forms\Components\Textarea::make('notes')
                                ->label('Catatan Assignment')
                                ->placeholder('Catatan untuk assignment ini...')
                                ->maxLength(500)
                                ->rows(3),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $clientIds = $data['client_ids'];
                            $replaceExisting = $data['replace_existing'] ?? false;
                            $notes = $data['notes'] ?? null;
                            
                            $successCount = 0;
                            $skippedCount = 0;
                            $updatedCount = 0;
                            
                            foreach ($records as $ar) {
                                foreach ($clientIds as $clientId) {
                                    $client = Client::find($clientId);
                                    
                                    if (!$client) {
                                        continue;
                                    }
                                    
                                    // Cek apakah client sudah punya AR
                                    if ($client->ar_id && !$replaceExisting) {
                                        $skippedCount++;
                                        continue;
                                    }
                                    
                                    $previousAr = $client->ar_id;
                                    
                                    // Update client dengan AR baru
                                    $client->update(['ar_id' => $ar->id]);
                                    
                                    // Log activity
                                    $logMessage = "Klien {$client->name} diassign ke AR {$ar->name}";
                                    if ($previousAr) {
                                        $previousArName = AccountRepresentative::find($previousAr)?->name ?? 'Unknown';
                                        $logMessage .= " (sebelumnya: {$previousArName})";
                                        $updatedCount++;
                                    } else {
                                        $successCount++;
                                    }
                                    
                                    if ($notes) {
                                        $logMessage .= " - Catatan: {$notes}";
                                    }
                                    
                                    activity()
                                        ->performedOn($client)
                                        ->causedBy(auth()->user())
                                        ->log($logMessage);
                                }
                            }
                            
                            // Notification dengan summary
                            $message = "Assignment selesai! ";
                            if ($successCount > 0) {
                                $message .= "{$successCount} assignment baru, ";
                            }
                            if ($updatedCount > 0) {
                                $message .= "{$updatedCount} AR diganti, ";
                            }
                            if ($skippedCount > 0) {
                                $message .= "{$skippedCount} dilewati (sudah ada AR).";
                            }
                            
                            Notification::make()
                                ->title('Assignment AR Berhasil')
                                ->body(rtrim($message, ', ') . '.')
                                ->success()
                                ->send();
                        })
                        ->modalHeading('Assign AR ke Klien')
                        ->modalDescription('Pilih klien yang akan diassign ke Account Representative yang dipilih')
                        ->modalSubmitActionLabel('Assign Sekarang')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'active']);
                                activity()
                                    ->performedOn($record)
                                    ->log("AR {$record->name} diaktifkan melalui bulk action");
                            });
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->modalHeading('Aktifkan Account Representatives')
                        ->modalDescription('Apakah Anda yakin ingin mengaktifkan AR yang dipilih?'),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Nonaktifkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'inactive']);
                                activity()
                                    ->performedOn($record)
                                    ->log("AR {$record->name} dinonaktifkan melalui bulk action");
                            });
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->modalHeading('Nonaktifkan Account Representatives')
                        ->modalDescription('Apakah Anda yakin ingin menonaktifkan AR yang dipilih?'),

                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->defaultSort('name')
            ->striped()
            ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListAccountRepresentatives::route('/'),
            'create' => Pages\CreateAccountRepresentative::route('/create'),
            'edit' => Pages\EditAccountRepresentative::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'active')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['clients']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone_number', 'kpp'];
    }
}