<?php

namespace App\Filament\Resources\PicResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientsRelationManager extends RelationManager
{
    protected static string $relationship = 'clients';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Assigned Clients';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter client name'),

                        Forms\Components\TextInput::make('NPWP')
                            ->label('NPWP')
                            ->maxLength(255)
                            ->placeholder('Enter NPWP number'),

                        Forms\Components\TextInput::make('KPP')
                            ->label('KPP')
                            ->maxLength(255)
                            ->placeholder('Enter KPP'),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('client@example.com'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'Active' => 'Active',
                                'Inactive' => 'Inactive',
                            ])
                            ->default('Active')
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Core Tax Credentials')
                    ->schema([
                        Forms\Components\TextInput::make('core_tax_user_id')
                            ->label('Core Tax User ID')
                            ->maxLength(255)
                            ->placeholder('Enter Core Tax User ID')
                            ->helperText('Client ID for Core Tax application'),

                        Forms\Components\TextInput::make('core_tax_password')
                            ->label('Core Tax Password')
                            ->maxLength(255)
                            ->placeholder('Enter Core Tax Password')
                            ->helperText('Password for Core Tax application'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('account_representative')
                            ->label('Account Representative')
                            ->maxLength(255)
                            ->placeholder('Enter AR name'),

                        Forms\Components\TextInput::make('ar_phone_number')
                            ->label('AR Phone Number')
                            ->tel()
                            ->maxLength(255)
                            ->placeholder('Enter phone number'),

                        Forms\Components\Textarea::make('adress')
                            ->label('Address')
                            ->maxLength(500)
                            ->placeholder('Enter client address')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Tax Information')
                    ->schema([
                        Forms\Components\TextInput::make('EFIN')
                            ->label('EFIN')
                            ->maxLength(255)
                            ->placeholder('Enter EFIN'),

                        Forms\Components\Toggle::make('ppn_contract')
                            ->label('PPN Contract')
                            ->helperText('Has PPN contract agreement'),

                        Forms\Components\Toggle::make('pph_contract')
                            ->label('PPh Contract')
                            ->helperText('Has PPh contract agreement'),

                        Forms\Components\Toggle::make('bupot_contract')
                            ->label('Bupot Contract')
                            ->helperText('Has Bupot contract agreement'),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('NPWP')
                    ->label('NPWP')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('NPWP copied!')
                    ->fontFamily('mono')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('core_tax_user_id')
                    ->label('Core Tax ID')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Core Tax ID copied!')
                    ->fontFamily('mono')
                    ->placeholder('Not set')
                    ->color(fn ($state) => $state ? 'primary' : 'gray'),

                Tables\Columns\TextColumn::make('core_tax_password')
                    ->label('Core Tax Password')
                    ->copyable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'Active',
                        'danger' => 'Inactive',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'Active',
                        'heroicon-o-x-circle' => 'Inactive',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('has_core_tax_credentials')
                    ->label('Has Core Tax Credentials')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereNotNull('core_tax_user_id')
                              ->whereNotNull('core_tax_password')
                    ),

                Tables\Filters\Filter::make('missing_core_tax_credentials')
                    ->label('Missing Core Tax Credentials')
                    ->query(fn (Builder $query): Builder => 
                        $query->where(function ($query) {
                            $query->whereNull('core_tax_user_id')
                                  ->orWhereNull('core_tax_password');
                        })
                    ),

                Tables\Filters\Filter::make('has_contracts')
                    ->label('Has Tax Contracts')
                    ->query(fn (Builder $query): Builder => 
                        $query->where(function ($query) {
                            $query->where('ppn_contract', true)
                                  ->orWhere('pph_contract', true)
                                  ->orWhere('bupot_contract', true);
                        })
                    ),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Client')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Add New Client')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Automatically assign the current PIC
                        $data['pic_id'] = $this->getOwnerRecord()->id;
                        return $data;
                    }),

                Tables\Actions\Action::make('assign_existing')
                    ->label('Assign Existing Clients')
                    ->icon('heroicon-o-link')
                    ->modalHeading('Assign Existing Clients')
                    ->modalSubmitActionLabel('Assign Clients')
                    ->form([
                        Forms\Components\Select::make('client_ids')
                            ->label('Select Clients')
                            ->placeholder('Search and select clients...')
                            ->multiple()
                            ->options(function () {
                                return \App\Models\Client::whereNull('pic_id')
                                    ->get()
                                    ->mapWithKeys(function ($client) {
                                        return [$client->id => "{$client->name}" . ($client->NPWP ? " ({$client->NPWP})" : "")];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getSearchResultsUsing(function (string $search) {
                                return \App\Models\Client::whereNull('pic_id')
                                    ->where(function ($query) use ($search) {
                                        $query->where('name', 'like', "%{$search}%")
                                              ->orWhere('NPWP', 'like', "%{$search}%")
                                              ->orWhere('email', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function ($client) {
                                        return [$client->id => "{$client->name}" . ($client->NPWP ? " ({$client->NPWP})" : "")];
                                    });
                            })
                            ->getOptionLabelsUsing(function (array $values) {
                                return \App\Models\Client::whereIn('id', $values)
                                    ->get()
                                    ->mapWithKeys(function ($client) {
                                        return [$client->id => "{$client->name}" . ($client->NPWP ? " ({$client->NPWP})" : "")];
                                    });
                            })
                            ->helperText('You can select multiple clients to assign them all at once'),
                    ])
                    ->action(function (array $data) {
                        $clientIds = $data['client_ids'] ?? [];
                        $assignedCount = 0;
                        $clientNames = [];
                        
                        foreach ($clientIds as $clientId) {
                            $client = \App\Models\Client::find($clientId);
                            if ($client && is_null($client->pic_id)) {
                                $client->update(['pic_id' => $this->getOwnerRecord()->id]);
                                $assignedCount++;
                                $clientNames[] = $client->name;
                            }
                        }
                        
                        if ($assignedCount > 0) {
                            $message = $assignedCount === 1 
                                ? "Client '{$clientNames[0]}' has been assigned to this PIC."
                                : "{$assignedCount} clients have been assigned to this PIC.";
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Clients Assigned Successfully')
                                ->body($message)
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('No Clients Assigned')
                                ->body('No valid clients were found to assign.')
                                ->warning()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading('Client Details'),

                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Client'),

                Tables\Actions\Action::make('view_credentials')
                    ->label('View Credentials')
                    ->icon('heroicon-o-key')
                    ->color('info')
                    ->modalHeading('Core Tax Credentials')
                    ->modalContent(fn ($record) => view('filament.modals.core-tax-credentials', ['record' => $record]))
                    ->modalActions([
                        Tables\Actions\Action::make('close')
                            ->label('Close')
                            ->color('gray')
                            ->close(),
                    ])
                    ->visible(fn ($record) => $record->core_tax_user_id && $record->core_tax_password),

                Tables\Actions\Action::make('unassign')
                    ->label('Unassign')
                    ->icon('heroicon-o-x-mark')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Unassign Client')
                    ->modalDescription('Are you sure you want to unassign this client from this PIC?')
                    ->action(function ($record) {
                        $record->update(['pic_id' => null]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Client Unassigned')
                            ->body("Client '{$record->name}' has been unassigned from this PIC.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('unassign_bulk')
                        ->label('Unassign Selected')
                        ->icon('heroicon-o-x-mark')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Unassign Clients')
                        ->modalDescription('Are you sure you want to unassign the selected clients from this PIC?')
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->update(['pic_id' => null]);
                                $count++;
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Clients Unassigned')
                                ->body("{$count} client(s) have been unassigned from this PIC.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => 'Active']))
                        ->requiresConfirmation()
                        ->modalHeading('Activate Clients')
                        ->modalDescription('Are you sure you want to activate the selected clients?'),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['status' => 'Inactive']))
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate Clients')
                        ->modalDescription('Are you sure you want to deactivate the selected clients?'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No clients assigned')
            ->emptyStateDescription('This PIC has no clients assigned yet. You can add a new client or assign an existing one.')
            ->emptyStateIcon('heroicon-o-users');
    }
}