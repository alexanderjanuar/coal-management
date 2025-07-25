<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers\ClientDocumentsRelationManager;
use App\Filament\Resources\ClientResource\RelationManagers\ProgressRelationManager;
use Filament\Forms\Components\Section;
use App\Models\Client;
use App\Models\Pic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Fieldset;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use Filament\Forms\Components\Select;
use App\Filament\Imports\ClientImporter;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Actions\Exports\Models\Export;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Support\Enums\FontWeight;
use Filament\Infolists\Components\ViewEntry;
use App\Exports\Clients\ClientsExport;
use App\Exports\Clients\ClientsDetailedExport;
use Maatwebsite\Excel\Facades\Excel;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Hero Section for View Mode
                Forms\Components\Placeholder::make('client_hero')
                    ->label('')
                    ->content(fn (?Client $record) => $record ? view('filament.components.client-hero', ['record' => $record]) : '')
                    ->hiddenOn('create')
                    ->columnSpanFull(),

                Section::make('Client Profile')
                    ->description('Detail dari Client')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Client Name')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->label('Client Email')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('adress')
                            ->label('Address'),
                        Select::make('pic_id')
                            ->label('Person In Charge (PIC)')
                            ->relationship('pic', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('nik')
                                    ->label('NIK')
                                    ->required()
                                    ->unique()
                                    ->length(16)
                                    ->numeric(),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->required()
                                    ->minLength(8),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                    ])
                                    ->default('active')
                                    ->required(),
                            ])
                            ->helperText('Select or create a new PIC for this client'),
                        Select::make('status')
                            ->label('Client Status')
                            ->options([
                                'Active' => 'Active',
                                'Inactive' => 'Inactive',
                            ])
                            ->default('Active')
                            ->required()
                            ->native(false),
                        FileUpload::make('logo')
                            ->label('Client Logo')
                            ->openable()
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('avatars')
                            ->visibility('public')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('300')
                            ->imageResizeTargetHeight('300')
                            ->maxSize(5120) // 5MB max
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                            ->downloadable()
                            ->columnSpanFull()
                    ])
                    ->columns(2),

                // PIC Information Display for View Mode
                Forms\Components\Placeholder::make('pic_info')
                    ->label('')
                    ->content(fn (?Client $record) => $record && $record->pic ? view('filament.components.client-pic-info', ['record' => $record]) : '')
                    ->hiddenOn(['create', 'edit'])
                    ->columnSpanFull(),

                Section::make('Core Tax Account')
                    ->description('Client credentials for Core Tax application access')
                    ->icon('heroicon-o-key')
                    ->schema([
                        Forms\Components\TextInput::make('core_tax_user_id')
                            ->label('Core Tax User ID')
                            ->maxLength(255)
                            ->placeholder('Enter Core Tax User ID')
                            ->helperText('Unique identifier for Core Tax application login')
                            ->suffixIcon('heroicon-o-identification'),
                        Forms\Components\TextInput::make('core_tax_password')
                            ->label('Core Tax Password')
                            ->maxLength(255)
                            ->placeholder('Enter Core Tax Password')
                            ->default('Samarinda#1')
                            ->helperText('Password for Core Tax application access')
                            ->suffixIcon('heroicon-o-lock-closed'),
                        Forms\Components\Placeholder::make('core_tax_status')
                            ->label('Account Status')
                            ->content(function (Forms\Get $get, ?Client $record) {
                                if ($record) {
                                    return view('filament.components.client-core-tax-status', ['record' => $record]);
                                }
                                
                                $userId = $get('core_tax_user_id');
                                $password = $get('core_tax_password');
                                
                                if ($userId && $password) {
                                    return view('filament.components.core-tax-status-preview', ['status' => 'complete']);
                                } elseif ($userId || $password) {
                                    return view('filament.components.core-tax-status-preview', ['status' => 'incomplete']);
                                } else {
                                    return view('filament.components.core-tax-status-preview', ['status' => 'empty']);
                                }
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
                
                Section::make('Client Tax Information')
                    ->description('Tax registration and compliance details')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\TextInput::make('NPWP')
                            ->label('NPWP')
                            ->required()
                            ->placeholder('XX.XXX.XXX.X-XXX.XXX'),
                        Forms\Components\TextInput::make('EFIN')
                            ->label('EFIN')
                            ->placeholder('Electronic Filing Identification Number'),
                        Forms\Components\TextInput::make('account_representative')
                            ->label('Account Representative (AR)')
                            ->maxLength(255)
                            ->placeholder('AR Name'),
                        Forms\Components\TextInput::make('ar_phone_number')
                            ->label('AR Phone Number')
                            ->tel()
                            ->placeholder('+62 XXX XXXX XXXX'),
                        Select::make('KPP')
                            ->label('Kantor Pelayanan Pajak (KPP)')
                            ->native(false)
                            ->searchable()
                            ->options([
                                'SAMARINDA ULU' => 'KPP Samarinda Ulu',
                                'SAMARINDA ILIR' => 'KPP Samarinda Ilir',
                                'TENGGARONG' => 'KPP Tenggarong',
                                'BALIKPAPAN BARAT' => 'KPP Balikpapan Barat',
                                'BALIKPAPAN TIMUR' => 'KPP Balikpapan Timur',
                                'MADYA DUA JAKARTA BARAT' => 'KPP Madya Dua Jakarta Barat',
                                'MADYA BALIKPAPAN' => 'KPP Madya Balikpapan',
                                'BONTANG' => 'KPP Bontang',
                                'BANJARBARU' => 'KPP Banjarbaru',
                            ])
                            ->placeholder('Select KPP'),
                            
                        // PKP STATUS FIELD
                        Select::make('pkp_status')
                            ->label('Status PKP')
                            ->options([
                                'Non-PKP' => 'Non-PKP',
                                'PKP' => 'PKP (Pengusaha Kena Pajak)',
                            ])
                            ->default('Non-PKP')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                // Auto-disable PPN contract if Non-PKP is selected
                                if ($state === 'Non-PKP') {
                                    $set('ppn_contract', false);
                                }
                            })
                            ->helperText(function (Forms\Get $get) {
                                $status = $get('pkp_status');
                                if ($status === 'PKP') {
                                    return '✅ Client dapat membuat faktur pajak dan memungut PPN';
                                } else {
                                    return 'ℹ️ Client tidak dapat membuat faktur pajak (otomatis menonaktifkan kontrak PPN)';
                                }
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // Tax Information Display for View Mode
                Forms\Components\Placeholder::make('tax_info_display')
                    ->label('')
                    ->content(fn (?Client $record) => $record ? view('filament.components.client-tax-info', ['record' => $record]) : '')
                    ->hiddenOn(['create', 'edit'])
                    ->columnSpanFull(),
                
                Section::make('Contract Management')
                    ->description('Tax service contracts and agreements')
                    ->icon('heroicon-o-document-check')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Toggle::make('ppn_contract')
                                    ->label('PPN Contract')
                                    ->reactive()
                                    ->disabled(fn (Forms\Get $get) => $get('pkp_status') === 'Non-PKP')
                                    ->helperText(function (Forms\Get $get) {
                                        if ($get('pkp_status') === 'Non-PKP') {
                                            return 'Tidak tersedia untuk Non-PKP';
                                        }
                                        return 'Kontrak untuk pengelolaan PPN';
                                    })
                                    ->columnSpan(1),
                                    
                                Forms\Components\Toggle::make('pph_contract')
                                    ->label('PPh Contract')
                                    ->reactive()
                                    ->helperText('Kontrak untuk pengelolaan PPh')
                                    ->columnSpan(1),
                                    
                                Forms\Components\Toggle::make('bupot_contract')
                                    ->label('Bupot Contract')
                                    ->reactive()
                                    ->helperText('Kontrak untuk bukti potong')
                                    ->columnSpan(1),
                                
                                Forms\Components\FileUpload::make('contract_file')
                                    ->label('Contract Document')
                                    ->visible(function (callable $get) {
                                        return $get('ppn_contract') || $get('pph_contract') || $get('bupot_contract');
                                    })
                                    ->preserveFilenames()
                                    ->openable()
                                    ->downloadable()
                                    ->directory('client-contracts')
                                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                                    ->helperText('Upload signed contract document (PDF or image)')
                                    ->columnSpan(3),
                            ]),

                        // Contract Status Display for View Mode
                        Forms\Components\Placeholder::make('contracts_display')
                            ->label('')
                            ->content(fn (?Client $record) => $record ? view('filament.components.client-contracts-status', ['record' => $record]) : '')
                            ->hiddenOn(['create', 'edit'])
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                // System Information Display for View Mode
                Forms\Components\Placeholder::make('system_info')
                    ->label('')
                    ->content(fn (?Client $record) => $record ? view('filament.components.client-system-info', ['record' => $record]) : '')
                    ->hiddenOn(['create', 'edit'])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->circular()
                    ->defaultImageUrl('/images/default-avatar.png'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    
                    ->weight(FontWeight::Medium),
                    
                Tables\Columns\TextColumn::make('pic.name')
                    ->label('PIC')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->placeholder('No PIC assigned'),
                    
                Tables\Columns\TextColumn::make('NPWP')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('NPWP copied!')
                    ->fontFamily('mono'),
                    
                Tables\Columns\TextColumn::make('core_tax_user_id')
                    ->label('Core Tax ID')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray')
                    ->copyMessage('Core Tax ID copied!')
                    ->placeholder('Not configured')
                    ->formatStateUsing(fn ($state) => $state ?: '—'),
                    
                    
                // PKP STATUS COLUMN
                Tables\Columns\BadgeColumn::make('pkp_status')
                    ->label('PKP Status')
                    ->colors([
                        'success' => 'PKP',
                        'warning' => 'Non-PKP',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'PKP',
                        'heroicon-o-x-circle' => 'Non-PKP',
                    ])
                    ->sortable(),
                                     
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
                Tables\Filters\SelectFilter::make('pic_id')
                    ->label('Filter by PIC')
                    ->relationship('pic', 'name')
                    ->searchable()
                    ->preload(),
                    
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
                    
                // PKP STATUS FILTER
                Tables\Filters\SelectFilter::make('pkp_status')
                    ->label('Status PKP')
                    ->options([
                        'PKP' => 'PKP',
                        'Non-PKP' => 'Non-PKP',
                    ]),
                    
                Tables\Filters\SelectFilter::make('status')
                    ->label('Client Status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                    ]),
                    
                // CONTRACT FILTERS
                Tables\Filters\Filter::make('has_ppn_contract')
                    ->label('Memiliki Kontrak PPN')
                    ->query(fn (Builder $query): Builder => $query->where('ppn_contract', true)),
                    
                Tables\Filters\Filter::make('active_contracts')
                    ->label('Memiliki Kontrak Aktif')
                    ->query(fn (Builder $query): Builder => $query->where(function ($q) {
                        $q->where('ppn_contract', true)
                          ->orWhere('pph_contract', true)
                          ->orWhere('bupot_contract', true);
                    })),
            ])
            ->headerActions([
                    Tables\Actions\ActionGroup::make([
                        Tables\Actions\Action::make('export_excel_simple')
                            ->label('Simple Export')
                            ->icon('heroicon-o-document-arrow-down')
                            ->color('success')
                            ->action(function () {
                                return Excel::download(new \App\Exports\Clients\ClientsExport(), 'clients-' . now()->format('Y-m-d-H-i') . '.xlsx');
                            }),
                            
                        Tables\Actions\Action::make('export_excel_detailed')
                            ->label('Detailed Export (Multi-Sheet)')
                            ->icon('heroicon-o-document-chart-bar')
                            ->color('info')
                            ->action(function () {
                                return Excel::download(new \App\Exports\Clients\ClientsDetailedExport(), 'clients-detailed-' . now()->format('Y-m-d-H-i') . '.xlsx');
                            }),
                    ])
                    ->label('Excel Export')
                    ->icon('heroicon-o-table-cells')
                    ->color('primary'),
                ])
            ->actions([
            // Existing Core Tax action
            Tables\Actions\Action::make('view_core_tax_credentials')
                ->label('')
                ->icon('heroicon-o-key')
                ->color('info')
                ->modalHeading('Core Tax Credentials')
                ->modalContent(fn ($record) => view('filament.modals.client-core-tax-credentials', ['record' => $record]))
                ->modalActions([
                    Tables\Actions\Action::make('close')
                        ->label('Close')
                        ->color('gray')
                        ->close(),
                ])
                ->visible(fn ($record) => $record->core_tax_user_id || $record->core_tax_password),

            // PIC Management Actions Group
            Tables\Actions\ActionGroup::make([
                // Assign PIC (when no PIC assigned)
                Tables\Actions\Action::make('assign_pic')
                    ->label('Assign PIC')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->visible(fn ($record) => !$record->pic_id)
                    ->form([
                        Select::make('pic_id')
                            ->label('Select PIC')
                            ->relationship('pic', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter PIC name'),
                                Forms\Components\TextInput::make('nik')
                                    ->label('NIK')
                                    ->required()
                                    ->unique()
                                    ->length(16)
                                    ->numeric()
                                    ->placeholder('16-digit NIK'),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->required()
                                    ->minLength(8)
                                    ->placeholder('Minimum 8 characters'),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                    ])
                                    ->default('active')
                                    ->required(),
                            ])
                            ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                return $action
                                    ->modalHeading('Create New PIC')
                                    ->modalSubmitActionLabel('Create PIC')
                                    ->modalWidth('lg');
                            })
                            ->helperText('Select an existing PIC or create a new one'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update(['pic_id' => $data['pic_id']]);
                        
                        $pic = \App\Models\Pic::find($data['pic_id']);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('PIC Assigned Successfully')
                            ->body("PIC '{$pic->name}' has been assigned to client '{$record->name}'.")
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Assign PIC to Client')
                    ->modalSubmitActionLabel('Assign PIC')
                    ->modalWidth('md'),

                // Change PIC (when PIC is assigned)
                Tables\Actions\Action::make('change_pic')
                    ->label('Change PIC')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn ($record) => $record->pic_id)
                    ->form([
                        Forms\Components\Placeholder::make('current_pic')
                            ->label('Current PIC')
                            ->content(fn ($record) => $record->pic ? $record->pic->name : 'None assigned'),
                        
                        Select::make('pic_id')
                            ->label('New PIC')
                            ->relationship('pic', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter PIC name'),
                                Forms\Components\TextInput::make('nik')
                                    ->label('NIK')
                                    ->required()
                                    ->unique()
                                    ->length(16)
                                    ->numeric()
                                    ->placeholder('16-digit NIK'),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->required()
                                    ->minLength(8)
                                    ->placeholder('Minimum 8 characters'),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                    ])
                                    ->default('active')
                                    ->required(),
                            ])
                            ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                return $action
                                    ->modalHeading('Create New PIC')
                                    ->modalSubmitActionLabel('Create PIC')
                                    ->modalWidth('lg');
                            })
                            ->helperText('Select a different PIC or create a new one'),
                    ])
                    ->action(function ($record, array $data) {
                        $oldPic = $record->pic;
                        $record->update(['pic_id' => $data['pic_id']]);
                        
                        $newPic = \App\Models\Pic::find($data['pic_id']);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('PIC Changed Successfully')
                            ->body("Client '{$record->name}' has been reassigned from '{$oldPic?->name}' to '{$newPic->name}'.")
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Change PIC Assignment')
                    ->modalSubmitActionLabel('Change PIC')
                    ->modalWidth('md'),

                // View PIC Details (when PIC is assigned)
                Tables\Actions\Action::make('view_pic_details')
                    ->label('PIC Details')
                    ->icon('heroicon-o-identification')
                    ->color('info')
                    ->visible(fn ($record) => $record->pic_id)
                    ->modalHeading('PIC Information')
                    ->modalContent(fn ($record) => view('filament.modals.pic-details', ['record' => $record]))
                    ->modalActions([
                        Tables\Actions\Action::make('close')
                            ->label('Close')
                            ->color('gray')
                            ->close(),
                    ])
                    ->modalWidth('md'),

                // Unassign PIC (when PIC is assigned)
                Tables\Actions\Action::make('unassign_pic')
                    ->label('Unassign PIC')
                    ->icon('heroicon-o-user-minus')
                    ->color('danger')
                    ->visible(fn ($record) => $record->pic_id)
                    ->requiresConfirmation()
                    ->modalHeading('Unassign PIC from Client')
                    ->modalDescription(fn ($record) => "Are you sure you want to unassign PIC '{$record->pic?->name}' from client '{$record->name}'? This action can be reversed later.")
                    ->modalSubmitActionLabel('Yes, Unassign PIC')
                    ->action(function ($record) {
                        $picName = $record->pic?->name;
                        $record->update(['pic_id' => null]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('PIC Unassigned Successfully')
                            ->body("PIC '{$picName}' has been unassigned from client '{$record->name}'.")
                            ->success()
                            ->send();
                    }),
            ])
            ->label('PIC Management')
            ->icon('heroicon-o-users')
            ->color('primary'),

            // Existing actions
            RelationManagerAction::make('progress-relation-manager')
                ->label('')
                ->icon('heroicon-o-folder')
                ->color('warning')
                ->modalWidth('7xl')
                ->relationManager(ClientDocumentsRelationManager::make()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Quick Export Actions
                    Tables\Actions\BulkAction::make('export_selected_simple')
                        ->label('Export Selected (Simple)')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function ($records) {
                            $clientIds = $records->pluck('id')->toArray();
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Export Started')
                                ->body('Exporting ' . count($clientIds) . ' selected client(s)...')
                                ->info()
                                ->send();
                            
                            return Excel::download(
                                new \App\Exports\Clients\ClientsExport([], false, $clientIds), 
                                'selected-clients-simple-' . now()->format('Y-m-d-H-i') . '.xlsx'
                            );
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Export Selected Clients')
                        ->modalDescription(fn ($records) => 'Export ' . $records->count() . ' selected client(s) to a simple Excel file.')
                        ->modalSubmitActionLabel('Export Now')
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('export_selected_detailed')
                        ->label('Export Selected (Multi-Sheet)')
                        ->icon('heroicon-o-document-chart-bar')
                        ->color('info')
                        ->action(function ($records) {
                            $clientIds = $records->pluck('id')->toArray();
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Detailed Export Started')
                                ->body('Creating multi-sheet export for ' . count($clientIds) . ' client(s)...')
                                ->info()
                                ->send();
                            
                            return Excel::download(
                                new \App\Exports\Clients\ClientsDetailedExport([], $clientIds), 
                                'selected-clients-detailed-' . now()->format('Y-m-d-H-i') . '.xlsx'
                            );
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Export Selected Clients (Detailed)')
                        ->modalDescription(fn ($records) => 'Export ' . $records->count() . ' selected client(s) to a comprehensive multi-sheet Excel file with separate tabs for different data categories.')
                        ->modalSubmitActionLabel('Export Detailed')
                        ->deselectRecordsAfterCompletion(),

                    // Advanced Export with Options
                    Tables\Actions\BulkAction::make('export_selected_advanced')
                        ->label('Export Selected (Advanced)')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->color('warning')
                        ->form([
                            Forms\Components\Section::make('Selected Clients Overview')
                                ->schema([
                                Forms\Components\Placeholder::make('export_statistics')
                                    ->label('')
                                    ->content(function ($livewire) {
                                        $selectedIds = $livewire->selectedTableRecords ?? [];
                                        
                                        if (empty($selectedIds)) {
                                            return view('filament.components.export-statistics-empty');
                                        }
                                        
                                        $selectedRecords = Client::with('pic')->whereIn('id', $selectedIds)->get();
                                        
                                        $stats = [
                                            'total' => $selectedRecords->count(),
                                            'with_pic' => $selectedRecords->whereNotNull('pic_id')->count(),
                                            'without_pic' => $selectedRecords->whereNull('pic_id')->count(),
                                            'with_core_tax' => $selectedRecords->filter(function($client) {
                                                return $client->core_tax_user_id && $client->core_tax_password;
                                            })->count(),
                                            'pkp_clients' => $selectedRecords->where('pkp_status', 'PKP')->count(),
                                            'active_contracts' => $selectedRecords->filter(function($client) {
                                                return $client->ppn_contract || $client->pph_contract || $client->bupot_contract;
                                            })->count(),
                                        ];
                                        
                                        return view('filament.components.export-statistics', compact('stats'));
                                    }),
                            ])
                            ->collapsible(),
                                
                            Forms\Components\Section::make('Export Configuration')
                                ->schema([
                                    Forms\Components\Select::make('export_type')
                                        ->label('Export Format')
                                        ->options([
                                            'simple' => 'Simple Export (Single comprehensive sheet)',
                                            'detailed' => 'Detailed Export (Multiple organized sheets)',
                                        ])
                                        ->default('simple')
                                        ->required()
                                        ->live()
                                        ->helperText(fn (Forms\Get $get) => 
                                            $get('export_type') === 'detailed' 
                                                ? 'Creates separate sheets for: Main Info, Contracts, PIC Details, and Core Tax Credentials'
                                                : 'Single sheet with all client information in one place'
                                        ),
                                        
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\Toggle::make('include_passwords')
                                                ->label('Include Actual Passwords')
                                                ->helperText('⚠️ Security Risk: Actual Core Tax passwords will be visible')
                                                ->default(false),
                                                
                                            Forms\Components\Toggle::make('include_sensitive_data')
                                                ->label('Include Sensitive Data')
                                                ->helperText('Include PIC NIK and other sensitive information')
                                                ->default(true),
                                        ]),
                                        
                                    Forms\Components\TextInput::make('filename_prefix')
                                        ->label('Custom Filename Prefix')
                                        ->placeholder('e.g., monthly-report, client-audit')
                                        ->helperText('Optional: Add a custom prefix to the filename')
                                        ->maxLength(50),
                                        
                                    Forms\Components\Textarea::make('export_notes')
                                        ->label('Export Notes')
                                        ->placeholder('Add notes about this export (for your records)')
                                        ->rows(2)
                                        ->maxLength(500),
                                ]),
                        ])
                        ->action(function (array $data, $records) {
                            $clientIds = $records->pluck('id')->toArray();
                            $exportClass = $data['export_type'] === 'detailed' 
                                ? \App\Exports\Clients\ClientsDetailedExport::class 
                                : \App\Exports\Clients\ClientsExport::class;
                                
                            $prefix = $data['filename_prefix'] ? Str::slug($data['filename_prefix']) . '-' : '';
                            $filename = $prefix . 'selected-clients-' . $data['export_type'] . '-' . now()->format('Y-m-d-H-i') . '.xlsx';
                            
                            // Log the export activity (optional)
                            if ($data['export_notes']) {
                                \Log::info('Client export performed', [
                                    'user_id' => auth()->id(),
                                    'clients_count' => count($clientIds),
                                    'export_type' => $data['export_type'],
                                    'include_passwords' => $data['include_passwords'],
                                    'notes' => $data['export_notes'],
                                    'filename' => $filename,
                                ]);
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Advanced Export Completed')
                                ->body("Successfully exported {$records->count()} client(s) with your custom settings.")
                                ->success()
                                ->send();
                            
                            return Excel::download(
                                new $exportClass([], $data['include_passwords'], $clientIds), 
                                $filename
                            );
                        })
                        ->modalHeading('Advanced Export Options')
                        ->modalSubmitActionLabel('Export with Settings')
                        ->modalWidth('lg')
                        ->deselectRecordsAfterCompletion(),

                    // Smart Export Actions
                    Tables\Actions\BulkAction::make('export_incomplete_clients')
                        ->label('Export Incomplete Only')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('danger')
                        ->action(function ($records) {
                            $incompleteClients = $records->filter(function($client) {
                                return !$client->pic_id || 
                                    !$client->core_tax_user_id || 
                                    !$client->core_tax_password ||
                                    (!$client->ppn_contract && !$client->pph_contract && !$client->bupot_contract);
                            });
                            
                            if ($incompleteClients->isEmpty()) {
                                \Filament\Notifications\Notification::make()
                                    ->title('No Incomplete Clients')
                                    ->body('All selected clients have complete information!')
                                    ->success()
                                    ->send();
                                return;
                            }
                            
                            $clientIds = $incompleteClients->pluck('id')->toArray();
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Exporting Incomplete Clients')
                                ->body("Found {$incompleteClients->count()} clients with missing information.")
                                ->warning()
                                ->send();
                            
                            return Excel::download(
                                new \App\Exports\Clients\ClientsExport([], false, $clientIds), 
                                'incomplete-clients-' . now()->format('Y-m-d-H-i') . '.xlsx'
                            );
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Export Clients with Missing Information')
                        ->modalDescription('This will export only the selected clients that have incomplete data (missing PIC, Core Tax credentials, or contracts).')
                        ->modalSubmitActionLabel('Export Incomplete')
                        ->deselectRecordsAfterCompletion(),

                    // Export by PIC
                    Tables\Actions\BulkAction::make('export_by_pic_group')
                        ->label('Export Grouped by PIC')
                        ->icon('heroicon-o-user-group')
                        ->color('info')
                        ->action(function ($records) {
                            $clientIds = $records->pluck('id')->toArray();
                            
                            // Count clients by PIC for notification
                            $picGroups = $records->groupBy('pic_id');
                            $groupCount = $picGroups->count();
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Exporting by PIC Groups')
                                ->body("Exporting {$records->count()} clients across {$groupCount} PIC group(s).")
                                ->info()
                                ->send();
                            
                            return Excel::download(
                                new \App\Exports\Clients\ClientsDetailedExport([], $clientIds), 
                                'clients-by-pic-groups-' . now()->format('Y-m-d-H-i') . '.xlsx'
                            );
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Export Clients Grouped by PIC')
                        ->modalDescription('Export selected clients with detailed breakdown by their assigned PIC.')
                        ->modalSubmitActionLabel('Export by PIC')
                        ->deselectRecordsAfterCompletion(),

                    // Standard bulk actions
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('bulk_assign_pic')
                        ->label('Assign PIC to Selected')
                        ->icon('heroicon-o-user-plus')
                        ->color('success')
                        ->form([
                            Select::make('pic_id')
                                ->label('Select PIC')
                                ->relationship('pic', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')->required(),
                                    Forms\Components\TextInput::make('nik')->required()->length(16),
                                    Forms\Components\TextInput::make('email')->email(),
                                    Forms\Components\TextInput::make('password')->password()->required(),
                                    Forms\Components\Select::make('status')
                                        ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                                        ->default('active'),
                                ]),
                        ])
                        ->action(function (array $data, $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->update(['pic_id' => $data['pic_id']]);
                                $count++;
                            }
                            
                            $pic = \App\Models\Pic::find($data['pic_id']);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('PIC Assigned Successfully')
                                ->body("Assigned PIC '{$pic->name}' to {$count} client(s).")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Assign PIC to Selected Clients')
                        ->modalSubmitActionLabel('Assign PIC')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getExportStatistics($records = null): array
    {
        if ($records) {
            // Statistics for selected records
            $clientsCount = $records->count();
            $withPIC = $records->whereNotNull('pic_id')->count();
            $withCoreTax = $records->filter(function($client) {
                return $client->core_tax_user_id && $client->core_tax_password;
            })->count();
            $activeContracts = $records->filter(function($client) {
                return $client->ppn_contract || $client->pph_contract || $client->bupot_contract;
            })->count();
        } else {
            // Statistics for all records
            $clientsCount = Client::count();
            $withPIC = Client::whereNotNull('pic_id')->count();
            $withCoreTax = Client::whereNotNull('core_tax_user_id')
                ->whereNotNull('core_tax_password')->count();
            $activeContracts = Client::where(function($q) {
                $q->where('ppn_contract', true)
                ->orWhere('pph_contract', true)
                ->orWhere('bupot_contract', true);
            })->count();
        }

        return [
            'total_clients' => $clientsCount,
            'with_pic' => $withPIC,
            'with_core_tax' => $withCoreTax,
            'with_contracts' => $activeContracts,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'NPWP', 'core_tax_user_id'];
    }

    public static function getRelations(): array
    {
        return [
            ProgressRelationManager::class,
            ClientDocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}