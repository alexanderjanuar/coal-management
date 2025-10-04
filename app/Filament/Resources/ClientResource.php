<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers\ApplicationsRelationManager;
use App\Filament\Resources\ClientResource\RelationManagers\ClientDocumentsRelationManager;
use App\Filament\Resources\ClientResource\RelationManagers\ProgressRelationManager;
use Filament\Forms\Components\Section;
use App\Models\Client;
use App\Models\Pic;
use App\Models\ClientCredential;
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
use App\Models\AccountRepresentative;


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
                        Forms\Components\Select::make('client_type')
                            ->label('Tipe Klien')
                            ->options([
                                'Badan' => 'Badan/Perusahaan',
                                'Pribadi' => 'Pribadi/Perorangan',
                            ])
                            ->default('Pribadi')
                            ->required()
                            ->live() // Penting untuk reactive behavior
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                // Auto-clear PIC jika diubah ke Pribadi
                                if ($state === 'Pribadi') {
                                    $set('pic_id', null);
                                }
                            })
                            ->native(false)
                            ->helperText(function (Forms\Get $get) {
                                if ($get('client_type') === 'Badan') {
                                    return '✅ Klien Badan memerlukan PIC (Person In Charge)';
                                }
                                return 'ℹ️ Klien Pribadi tidak memerlukan PIC';
                            })
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Client Name')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->label('Client Email')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('adress')
                            ->label('Address'),
                        Select::make('pic_id')
                                ->label('Person In Charge (PIC)')
                                ->relationship('pic', 'name')
                                ->searchable()
                                ->preload()
                                ->visible(fn (Forms\Get $get) => $get('client_type') === 'Badan') // HANYA TAMPIL JIKA BADAN
                                ->disabled(fn (Forms\Get $get) => $get('client_type') !== 'Badan')
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
                                ->helperText(function (Forms\Get $get) {
                                    if ($get('client_type') === 'Badan') {
                                        return 'Pilih atau buat PIC baru untuk klien Badan';
                                    }
                                    return 'PIC tidak diperlukan untuk klien Pribadi';
                                }),
                        
                        Select::make('ar_id')
                            ->label('Account Representative (AR)')
                            ->relationship('accountRepresentative', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('AR Name'),
                                Forms\Components\TextInput::make('phone_number')
                                    ->tel()
                                    ->label('Phone Number')
                                    ->placeholder('+62 XXX XXXX XXXX'),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->unique()
                                    ->label('Email'),
                                Forms\Components\Select::make('kpp')
                                    ->label('KPP')
                                    ->options(\App\Services\Clients\KppService::getKppOptions())
                                    ->searchable()
                                    ->placeholder('Pilih atau cari KPP...')
                                    ->helperText('Pilih KPP tempat AR bertugas'),
                                Forms\Components\Textarea::make('notes')
                                    ->label('Notes')
                                    ->rows(2),
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
                                    ->modalHeading('Create New Account Representative')
                                    ->modalSubmitActionLabel('Create AR')
                                    ->modalWidth('lg');
                            })
                            ->helperText('Select or create a new Account Representative for this client'),
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
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('300')
                            ->imageResizeTargetHeight('300')
                            ->maxSize(5120) // 5MB max
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
                
                Section::make('Client Tax Information')
                    ->description('Tax registration and compliance details')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\TextInput::make('NPWP')
                            ->label('NPWP')
                            ->placeholder('XX.XXX.XXX.X-XXX.XXX'),
                        Forms\Components\TextInput::make('EFIN')
                            ->label('EFIN')
                            ->placeholder('Electronic Filing Identification Number'),                       
                        // PKP STATUS FIELD
                        Select::make('pkp_status')
                            ->label('Status PKP')
                            ->options([
                                'Non-PKP' => 'Non-PKP',
                                'PKP' => 'PKP (Pengusaha Kena Pajak)',
                            ])
                            ->default('Non-PKP')
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
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF'),

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
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Active' => 'success',
                        'Inactive' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'Active' => 'heroicon-o-check-circle',
                        'Inactive' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\BadgeColumn::make('client_type')
                    ->label('Tipe')
                    ->colors([
                        'primary' => 'Badan',
                        'warning' => 'Pribadi',
                    ])
                    ->icons([
                        'heroicon-o-building-office' => 'Badan',
                        'heroicon-o-user' => 'Pribadi',
                    ])
                    ->sortable(),
                  
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

                Tables\Filters\SelectFilter::make('client_type')
                    ->label('Tipe Klien')
                    ->options([
                        'Badan' => 'Badan/Perusahaan',
                        'Pribadi' => 'Pribadi/Perorangan',
                    ])
                    ->native(false),
         
                Tables\Filters\SelectFilter::make('status')
                    ->label('Client Status')
                    ->native(false)
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                    ])
                    ->default('Active'),
                    
                // PKP STATUS FILTER
                Tables\Filters\SelectFilter::make('pkp_status')
                    ->label('Status PKP')
                    ->options([
                        'PKP' => 'PKP',
                        'Non-PKP' => 'Non-PKP',
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
            Tables\Actions\Action::make('view_all_credentials')
                ->label('')
                ->icon('heroicon-o-key')
                ->color('info')
                ->tooltip('Lihat Semua Kredensial')
                ->modalHeading(fn ($record) => 'Kredensial Aplikasi - ' . $record->name)
                ->modalContent(fn ($record) => view('filament.modals.clients.client-all-credentials', ['record' => $record]))
                ->modalWidth('4xl')
                ->slideOver(),


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


            Tables\Actions\Action::make('manage_legal_documents')
                ->label('')
                ->icon('heroicon-o-folder')
                ->color('warning')
                ->tooltip('Kelola Legal Documents')
                ->modalWidth('7xl')
                ->modalHeading(fn ($record) => 'Legal Documents - ' . $record->name)
                ->modalDescription('Kelola dokumen legal klien berdasarkan SOP atau upload dokumen custom')
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
                    
                    Tables\Actions\BulkAction::make('bulk_deactivate_clients')
                        ->label('Nonaktifkan Klien Terpilih')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Nonaktifkan Klien Terpilih')
                        ->modalDescription(fn ($records) => 
                            "Apakah Anda yakin ingin menonaktifkan {$records->count()} klien yang dipilih? Status mereka akan diubah menjadi 'Inactive'."
                        )
                        ->modalSubmitActionLabel('Ya, Nonaktifkan Klien')
                        ->action(function ($records) {
                            $count = 0;
                            
                            \DB::transaction(function () use ($records, &$count) {
                                foreach ($records as $record) {
                                    if ($record->status !== 'Inactive') {
                                        $record->update(['status' => 'Inactive']);
                                        $count++;
                                    }
                                }
                            });
                            
                            if ($count > 0) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Klien Berhasil Dinonaktifkan')
                                    ->body("Berhasil menonaktifkan {$count} klien.")
                                    ->success()
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Tidak Ada Perubahan')
                                    ->body('Klien yang dipilih sudah dalam status tidak aktif.')
                                    ->warning()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('bulk_activate_clients')
                        ->label('Aktifkan Klien Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Aktifkan Klien Terpilih')
                        ->modalDescription(fn ($records) => 
                            "Apakah Anda yakin ingin mengaktifkan {$records->count()} klien yang dipilih? Status mereka akan diubah menjadi 'Active'."
                        )
                        ->modalSubmitActionLabel('Ya, Aktifkan Klien')
                        ->action(function ($records) {
                            $count = 0;
                            
                            \DB::transaction(function () use ($records, &$count) {
                                foreach ($records as $record) {
                                    if ($record->status !== 'Active') {
                                        $record->update(['status' => 'Active']);
                                        $count++;
                                    }
                                }
                            });
                            
                            if ($count > 0) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Klien Berhasil Diaktifkan')
                                    ->body("Berhasil mengaktifkan {$count} klien.")
                                    ->success()
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Tidak Ada Perubahan')
                                    ->body('Klien yang dipilih sudah dalam status aktif.')
                                    ->warning()
                                    ->send();
                            }
                        })
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
            ])
            ->recordClasses(function (Client $record) {
                if ($record->status === 'Inactive') {
                    return 'border-l-4 border-l-red-500 dark:border-l-red-400 opacity-70 hover:bg-red-50 dark:hover:bg-red-900/10 bg-red-50/30 dark:bg-red-900/10';
                }
                // Default hover effect for active clients with complete info
                return 'hover:bg-gray-50 dark:hover:bg-gray-800/10';
            })
            ->deferLoading();
    }

    // public static function getExportStatistics($records = null): array
    // {
    //     if ($records) {
    //         // Statistics for selected records
    //         $clientsCount = $records->count();
    //         $withPIC = $records->whereNotNull('pic_id')->count();
    //         $withCoreTax = $records->filter(function($client) {
    //             return $client->core_tax_user_id && $client->core_tax_password;
    //         })->count();
    //         $activeContracts = $records->filter(function($client) {
    //             return $client->ppn_contract || $client->pph_contract || $client->bupot_contract;
    //         })->count();
    //     } else {
    //         // Statistics for all records
    //         $clientsCount = Client::count();
    //         $withPIC = Client::whereNotNull('pic_id')->count();
    //         $withCoreTax = Client::whereNotNull('core_tax_user_id')
    //             ->whereNotNull('core_tax_password')->count();
    //         $activeContracts = Client::where(function($q) {
    //             $q->where('ppn_contract', true)
    //             ->orWhere('pph_contract', true)
    //             ->orWhere('bupot_contract', true);
    //         })->count();
    //     }

    //     return [
    //         'total_clients' => $clientsCount,
    //         'with_pic' => $withPIC,
    //         'with_core_tax' => $withCoreTax,
    //         'with_contracts' => $activeContracts,
    //     ];
    // }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'Active')->count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'NPWP'];
    }

    public static function getRelations(): array
    {
        return [
            ProgressRelationManager::class,
            ApplicationsRelationManager::class, // TAMBAH INI
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