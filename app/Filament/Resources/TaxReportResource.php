<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaxReportResource\Pages;
use App\Filament\Resources\TaxReportResource\RelationManagers;
use App\Models\Client;
use App\Models\TaxReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Support\RawJs;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Get;
use Closure;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Components\Tab;

class TaxReportResource extends Resource
{
    protected static ?string $model = TaxReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationGroup = 'Tax';


    public static function form(Form $form): Form
    {
        return $form
        
            ->schema([
                Split::make([
                    Section::make([
                        Select::make('client_id')
                            ->required()
                            ->label('Client Name')
                            ->searchable()
                            ->options(Client::all()->pluck('name', 'id')),
                        Select::make('month')
                            ->required()
                            ->label('Month')
                            ->searchable()
                            ->options(array_combine(
                                ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                                ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
                            ))
                            ->rules([
                                fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                    $exists = TaxReport::where('client_id', $get('client_id'))
                                        ->where('month', $value)
                                        ->exists();
                                    if ($exists) {
                                        $fail("Tax report for this client in {$value} already exists.");
                                    }
                                },
                            ])
                        ,
                        Select::make('status')
                            ->required()
                            ->label('Status')
                            ->searchable()
                            ->options(array_combine(
                                ['PKP', 'NON-PKP'],
                                ['PKP', 'NON-PKP']
                            ))
                            ->columnSpanFull()
                    ])->columns(2),
                    Section::make([
                        Forms\Components\TextInput::make('ppn')
                            ->label('PPN')
                            ->prefix('Rp')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('pph_21')
                            ->label('PPH 21')
                            ->prefix('Rp')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('pph_unifikasi')
                            ->label('PPH Unifikasi')
                            ->prefix('Rp')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->default(0)
                        ,
                    ])->grow(false),
                ])->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.name')  // Changed from client_id to show client name
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('month')
                    ->searchable()
                    ->sortable()
                    ->badge()  // Makes month stand out visually
                    ->color('success'),

                Tables\Columns\TextColumn::make('ppn')
                    ->label('PPN')
                    ->numeric()
                    ->money('idr')  // Format as Indonesian Rupiah
                    ->alignRight()
                    ->sortable()
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total PPN')
                            ->money('idr')
                    ),
                Tables\Columns\TextColumn::make('pph_21')
                    ->label('PPH 21')
                    ->numeric()
                    ->money('idr')
                    ->alignRight()
                    ->sortable()
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total PPH 21')
                            ->money('idr')
                    ),
                Tables\Columns\TextColumn::make('pph_unifikasi')
                    ->label('PPH Unifikasi')
                    ->numeric()
                    ->money('idr')
                    ->alignRight()
                    ->sortable()
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total PPN + PPH 21')
                            ->money('idr')
                    ),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PKP' => 'success',
                        'NON-PKP' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('amount_including_vat')
                    ->numeric()
                    ->money('idr')
                    ->label('Total')
                    ->state(function (TaxReport $record): float {
                        return $record->pph_21 + $record->nihil + $record->ppn;
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created Date')
                    ->dateTime('d M Y, H:i')  // Better date format
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->groups([
                'client.name',
            ])
            ->filters([
                //
            ])

            ->actions([
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

    protected function beforeCreate(): void
    {

        if (!auth()->user()->team->subscribed()) {
            Notification::make()
                ->warning()
                ->title('You don\'t have an active subscription!')
                ->body('Choose a plan to continue.')
                ->persistent()
                ->actions([
                    Action::make('subscribe')
                        ->button()
                        ->url(route('subscribe'), shouldOpenInNewTab: true),
                ])
                ->send();

            $this->halt();
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaxReports::route('/'),
            'create' => Pages\CreateTaxReport::route('/create'),
            'view' => Pages\ViewTaxReport::route('/{record}'),
            'edit' => Pages\EditTaxReport::route('/{record}/edit'),
        ];
    }
}
