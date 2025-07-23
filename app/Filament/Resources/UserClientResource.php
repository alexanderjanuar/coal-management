<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserClientResource\Pages;
use App\Filament\Resources\UserClientResource\RelationManagers;
use App\Models\UserClient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;
use App\Models\Client;

use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class UserClientResource extends Resource
{
    protected static ?string $model = UserClient::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Employees';
    protected static ?string $modelLabel = 'Employee';
    protected static ?string $pluralModelLabel = 'Employees';
    protected static ?string $breadcrumb = 'Employees';

    public static function shouldRegisterNavigation(): bool
    {
        return !auth()->user()->hasRole(['client', 'staff']);
    }

    protected static ?string $navigationGroup = 'Master Data';
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('User Details')
                ->description('Create or edit team member information')
                ->collapsible()
                ->schema([
                    Forms\Components\TextInput::make('user.name')
                        ->required()
                        ->placeholder('Enter full name')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('user.email')
                        ->email()
                        ->required()
                        ->unique('users', 'email', ignoreRecord: true)
                        ->placeholder('email@example.com'),

                    Forms\Components\FileUpload::make('user.avatar_path')
                        ->label('Avatar Image')
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
                        ->helperText('Upload and edit an image file (max 5MB). Click edit to crop and adjust the image.'),

                    Forms\Components\TextInput::make('user.avatar_url')
                        ->label('Avatar URL (Alternative)')
                        ->url()
                        ->placeholder('https://example.com/avatar.jpg')
                        ->helperText('Or enter a URL if you prefer not to upload a file'),

                    Forms\Components\TextInput::make('user.password')
                        ->password()
                        ->required(fn($context) => $context === 'create')
                        ->dehydrated(fn($state) => filled($state))
                        ->revealable()
                        ->autocomplete('new-password')
                ])
                ->aside(),
            Section::make('Assignment')
                ->description('Assign multiple clients')
                ->schema([
                    Forms\Components\Select::make('client_ids')
                        ->multiple()
                        ->searchable()
                        ->label('Client')
                        ->preload()
                        ->required()
                        ->columnSpanFull()
                        ->loadingMessage('Loading clients...')
                        ->optionsLimit(50)
                        ->options(fn() => !auth()->user()->hasRole('super-admin')
                            ? \App\Models\Client::where('id', auth()->user()->userClients()->first()->client_id)->pluck('name', 'id')
                            : \App\Models\Client::pluck('name', 'id')),
                ])
                ->aside()
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                ->whereHas('userClients')
                ->withCount('userClients')
            )
            ->columns([
                ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->circular()
                    ->getStateUsing(fn($record) => $record->avatar_url)
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF')
                    ->size(60),
                    
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                    
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'project-manager' => 'success',
                        'direktur' => 'warning',
                        'staff' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'project-manager' => 'Project Manager',
                        'direktur' => 'Director',
                        'staff' => 'Staff',
                        default => $state,
                    }),
                    
                TextColumn::make('user_clients_count')
                    ->label('Assigned Clients')
                    ->badge()
                    ->alignCenter()
                    ->color(fn($state) => $state > 0 ? 'success' : 'danger')
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Client')
                    ->native(false)
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        if (auth()->user()->hasRole('super-admin')) {
                            return Client::pluck('name', 'id');
                        }

                        return Client::whereIn(
                            'id',
                            auth()->user()->userClients->pluck('client_id')
                        )->pluck('name', 'id');
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('userClients', function ($query) use ($data) {
                                $query->where('client_id', $data['value']);
                            });
                        }
                    })
                    ->visible(fn() => !auth()->user()->hasRole('staff')),

                Tables\Filters\SelectFilter::make('roles')
                    ->label('Role')
                    ->native(false)
                    ->options(fn() => \Spatie\Permission\Models\Role::whereNot('name', 'super-admin')
                        ->pluck('name', 'name'))
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('roles', function ($query) use ($data) {
                                $query->where('name', $data['value']);
                            });
                        }
                    })
                    ->visible(fn() => !auth()->user()->hasRole('staff'))
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('change_avatar')
                        ->label('Change Avatar')
                        ->icon('heroicon-m-camera')
                        ->color('info')
                        ->modalHeading(fn($record) => "Change Avatar for {$record->name}")
                        ->modalIcon('heroicon-o-camera')
                        ->modalDescription('Upload a new avatar image or provide a URL.')
                        ->modalWidth('2xl')
                        ->form([
                            Forms\Components\Section::make('Current Avatar')
                                ->schema([
                                    Forms\Components\Placeholder::make('current_avatar')
                                        ->label('')
                                        ->content(function ($record) {
                                            $avatarUrl = $record->avatar;
                                            $source = '';
                                            if ($record->avatar_path) {
                                                $source = 'Uploaded file';
                                            } elseif ($record->avatar_url) {
                                                $source = 'External URL';
                                            } else {
                                                $source = 'Default generated';
                                            }
                                            
                                            return new \Illuminate\Support\HtmlString(
                                                '<div class="flex items-center space-x-4">
                                                    <img src="' . $avatarUrl . '" alt="Current Avatar" class="w-20 h-20 rounded-full object-cover shadow-lg">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">Current Avatar</p>
                                                        <p class="text-xs text-gray-500">Source: ' . $source . '</p>
                                                    </div>
                                                </div>'
                                            );
                                        })
                                ]),
                            
                            Forms\Components\Tabs::make('Avatar Options')
                                ->tabs([
                                    Forms\Components\Tabs\Tab::make('Upload & Edit')
                                        ->icon('heroicon-m-photo')
                                        ->schema([
                                            Forms\Components\FileUpload::make('avatar_file')
                                                ->label('Upload & Edit Avatar')
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
                                                ->helperText('Upload an image and click the edit button to crop, rotate, and adjust it before saving.')
                                                ->columnSpanFull()
                                        ]),
                                    
                                    Forms\Components\Tabs\Tab::make('Use URL')
                                        ->icon('heroicon-m-link')
                                        ->schema([
                                            Forms\Components\TextInput::make('avatar_url')
                                                ->label('Avatar URL')
                                                ->url()
                                                ->placeholder('https://example.com/avatar.jpg')
                                                ->helperText('Enter a direct URL to an image')
                                                ->default(fn($record) => $record->avatar_url)
                                                ->live(onBlur: true)
                                                ->columnSpanFull(),
                                                
                                            Forms\Components\Section::make('URL Preview')
                                                ->schema([
                                                    Forms\Components\Placeholder::make('url_preview')
                                                        ->label('')
                                                        ->content(function ($get) {
                                                            $url = $get('avatar_url');
                                                            if (!$url) {
                                                                return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">Enter a URL above to see preview</p>');
                                                            }
                                                            return new \Illuminate\Support\HtmlString(
                                                                '<div class="flex items-center space-x-4">
                                                                    <img src="' . $url . '" alt="URL Preview" class="w-20 h-20 rounded-full object-cover shadow-lg" onerror="this.src=\'https://via.placeholder.com/80x80/EF4444/FFFFFF?text=Error\'; this.className=\'w-20 h-20 rounded-full bg-red-100 flex items-center justify-center text-red-500 text-xs\';">
                                                                    <div>
                                                                        <p class="text-sm font-medium text-gray-900">URL Preview</p>
                                                                        <p class="text-xs text-gray-500">New avatar from URL</p>
                                                                    </div>
                                                                </div>'
                                                            );
                                                        })
                                                ])
                                                ->visible(fn($get) => filled($get('avatar_url')))
                                        ]),
                                    
                                    Forms\Components\Tabs\Tab::make('Remove Avatar')
                                        ->icon('heroicon-m-trash')
                                        ->schema([
                                            Forms\Components\Placeholder::make('remove_info')
                                                ->label('')
                                                ->content(new \Illuminate\Support\HtmlString(
                                                    '<div class="p-4 bg-red-50 rounded-lg border border-red-200">
                                                        <div class="flex items-center space-x-2">
                                                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                            </svg>
                                                            <p class="text-sm font-medium text-red-800">Remove Current Avatar</p>
                                                        </div>
                                                        <p class="text-xs text-red-600 mt-1">This will delete the current avatar and revert to the default generated avatar.</p>
                                                    </div>'
                                                )),
                                            
                                            Forms\Components\Checkbox::make('remove_avatar')
                                                ->label('Yes, remove the current avatar')
                                                ->helperText('Check this box to confirm avatar removal')
                                        ])
                                ])
                        ])
                        ->action(function (array $data, User $record): void {
                            // Handle avatar removal
                            if (!empty($data['remove_avatar'])) {
                                $record->deleteOldAvatar();
                                $record->update([
                                    'avatar_url' => null,
                                    'avatar_path' => null
                                ]);
                                
                                Notification::make()
                                    ->title('Avatar Removed')
                                    ->success()
                                    ->body("Avatar removed for {$record->name}. Now using default avatar.")
                                    ->send();
                                return;
                            }
                            
                            // Handle file upload with editing
                            if (!empty($data['avatar_file'])) {
                                // Delete old avatar file if exists
                                $record->deleteOldAvatar();
                                
                                // Generate the storage URL with 'storage/' prefix
                                $avatarUrl = 'storage/' . $data['avatar_file'];
                                
                                $record->update([
                                    'avatar_path' => $data['avatar_file'],
                                    'avatar_url' => $avatarUrl // Set both path and URL with storage/ prefix
                                ]);
                                
                                Notification::make()
                                    ->title('Avatar Updated')
                                    ->success()
                                    ->body("Successfully uploaded and edited new avatar for {$record->name}")
                                    ->send();
                                return;
                            }
                            
                            // Handle URL update
                            if (!empty($data['avatar_url'])) {
                                // Delete old avatar file if exists (since we're switching to URL)
                                $record->deleteOldAvatar();
                                
                                $record->update([
                                    'avatar_url' => $data['avatar_url'],
                                    'avatar_path' => null // Clear file path when using URL
                                ]);
                                
                                Notification::make()
                                    ->title('Avatar Updated')
                                    ->success()
                                    ->body("Successfully updated avatar URL for {$record->name}")
                                    ->send();
                                return;
                            }
                            
                            // If no action taken
                            Notification::make()
                                ->title('No Changes')
                                ->warning()
                                ->body('No avatar changes were made.')
                                ->send();
                        }),

                    Tables\Actions\Action::make('assign_client')
                        ->label('Assign Client')
                        ->icon('heroicon-m-building-office')
                        ->color('warning')
                        ->visible(fn() => auth()->user()->hasRole(['super-admin', 'direktur', 'project-manager']))
                        ->modalHeading(fn($record) => "Assign Client to {$record->name}")
                        ->form(function ($record) {
                            $assignedClientIds = $record->userClients()
                                ->pluck('client_id')
                                ->toArray();

                            return [
                                Forms\Components\Select::make('client_id')
                                    ->label('Clients')
                                    ->multiple()
                                    ->options(
                                        \App\Models\Client::whereNotIn('id', $assignedClientIds)
                                            ->pluck('name', 'id')
                                    )
                                    ->preload()
                                    ->searchable()
                                    ->required()
                                    ->loadingMessage('Loading clients...')
                                    ->helperText('Select clients to assign to this user.')
                            ];
                        })
                        ->action(function (array $data, User $record): void {
                            foreach ($data['client_id'] as $clientId) {
                                UserClient::create([
                                    'user_id' => $record->id,
                                    'client_id' => $clientId
                                ]);
                            }

                            Notification::make()
                                ->title('Clients Assigned')
                                ->success()
                                ->body("Successfully assigned clients to {$record->name}")
                                ->send();
                        }),

                    Tables\Actions\Action::make('assign_role')
                        ->label('Assign Role')
                        ->icon('heroicon-m-user-group')
                        ->color('success')
                        ->modalHeading(fn($record) => "Assign Role to {$record->name}")
                        ->modalIcon('heroicon-o-user-circle')
                        ->modalDescription('Select a role to assign to this employee.')
                        ->form([
                            Forms\Components\Select::make('role')
                                ->label('Role')
                                ->options(function () {
                                    $user = auth()->user();
                                    $roles = \Spatie\Permission\Models\Role::whereNot('name', 'super-admin');

                                    if (!$user->hasRole('super-admin') && !$user->hasRole('direktur')) {
                                        return [];
                                    }

                                    if ($user->hasRole('direktur')) {
                                        $roles->whereIn('name', ['project-manager', 'staff']);
                                    }

                                    return $roles->pluck('name', 'name')
                                        ->mapWithKeys(fn($role, $key) => [
                                            $key => match ($key) {
                                                'project-manager' => 'Project Manager',
                                                'direktur' => 'Director',
                                                'staff' => 'Staff',
                                                'client' => 'Client',
                                                default => $key
                                            }
                                        ]);
                                })
                                ->required()
                                ->searchable()
                                ->placeholder('Select a role')
                                ->disabled(fn() => !auth()->user()->hasRole(['super-admin', 'direktur']))
                        ])
                        ->action(function (array $data, User $record): void {
                            $record->syncRoles([$data['role']]);
                            Notification::make()
                                ->title('Role Assigned')
                                ->success()
                                ->body("Successfully assigned role to {$record->name}")
                                ->send();
                        }),

                    Tables\Actions\Action::make('unassign_client')
                        ->label('Unassign Client')
                        ->icon('heroicon-m-building-office-2')
                        ->color('danger')
                        ->visible(fn() => auth()->user()->hasRole(['super-admin', 'direktur', 'project-manager']))
                        ->modalHeading(fn($record) => "Unassign Client from {$record->name}")
                        ->form(function ($record) {
                            $assignedClients = $record->userClients()
                                ->join('clients', 'user_clients.client_id', '=', 'clients.id')
                                ->pluck('clients.name', 'user_clients.id')
                                ->toArray();

                            return [
                                Forms\Components\Select::make('user_client_ids')
                                    ->label('Assigned Clients')
                                    ->multiple()
                                    ->options($assignedClients)
                                    ->preload()
                                    ->searchable()
                                    ->required()
                                    ->loadingMessage('Loading assigned clients...')
                                    ->helperText('Select clients to unassign from this user.')
                            ];
                        })
                        ->action(function (array $data, User $record): void {
                            // Delete the selected user_client relationships
                            UserClient::whereIn('id', $data['user_client_ids'])->delete();

                            Notification::make()
                                ->title('Clients Unassigned')
                                ->success()
                                ->body("Successfully unassigned clients from {$record->name}")
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalButton('Unassign Selected Clients'),
                ])
            ])
            ->headerActions([
                Tables\Actions\Action::make('attach_user')
                    ->label('Attach Unassigned User')
                    ->icon('heroicon-m-user-plus')
                    ->color('gray')
                    ->visible(fn() => auth()->user()->hasRole('super-admin'))
                    ->form([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->options(function () {
                                return User::whereDoesntHave('userClients')
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Select a user who has no client assignments'),

                        Forms\Components\Section::make('Select Clients')
                            ->schema([
                                Forms\Components\CheckboxList::make('client_ids')
                                    ->label('Clients')
                                    ->options(Client::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->bulkToggleable()
                                    ->columns(2)
                                    ->helperText('Select clients to assign to this user')
                            ])
                    ])
                    ->action(function (array $data): void {
                        foreach ($data['client_ids'] as $clientId) {
                            UserClient::create([
                                'user_id' => $data['user_id'],
                                'client_id' => $clientId
                            ]);
                        }

                        $userName = User::find($data['user_id'])->name;

                        Notification::make()
                            ->title('User Assigned')
                            ->success()
                            ->body("Successfully assigned {$userName} to selected clients")
                            ->send();
                    })
                    ->modalHeading('Attach User to Clients')
                    ->modalDescription('Select an unassigned user and the clients to assign them to.')
                    ->requiresConfirmation()
                    ->modalButton('Attach User'),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('assignRole')
                        ->label('Assign Role')
                        ->icon('heroicon-m-user-group')
                        ->color('success')
                        ->modalHeading('Assign Role to Selected Employees')
                        ->modalIcon('heroicon-o-user-circle')
                        ->modalDescription('Select a role to assign to all selected employees.')
                        ->form([
                            Forms\Components\Select::make('role')
                                ->label('Role')
                                ->options(function () {
                                    $user = auth()->user();
                                    $roles = \Spatie\Permission\Models\Role::whereNot('name', 'super-admin');

                                    if (!$user->hasRole('super-admin') && !$user->hasRole('direktur')) {
                                        return [];
                                    }

                                    if ($user->hasRole('direktur')) {
                                        $roles->whereIn('name', ['project-manager', 'staff']);
                                    }

                                    return $roles->pluck('name', 'name')
                                        ->mapWithKeys(fn($role, $key) => [
                                            $key => match ($key) {
                                                'project-manager' => 'Project Manager',
                                                'direktur' => 'Director',
                                                'staff' => 'Staff',
                                                default => $key
                                            }
                                        ]);
                                })
                                ->required()
                                ->searchable()
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(fn($record) => $record->syncRoles([$data['role']]));

                            Notification::make()
                                ->title('Roles Assigned')
                                ->success()
                                ->body('Successfully assigned roles to selected employees.')
                                ->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        $query = parent::getGlobalSearchEloquentQuery();

        if (auth()->user()->hasRole('super-admin')) {
            return $query;
        }

        if (auth()->user()->hasRole(['direktur', 'project-manager'])) {
            return $query->whereHas('client', function ($q) {
                $q->whereIn('id', auth()->user()->userClients->pluck('client_id'));
            });
        }

        if (auth()->user()->hasRole('staff')) {
            return $query->where('user_id', auth()->user()->id);
        }

        return $query;
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
            'index' => Pages\ListUserClients::route('/'),
            'create' => Pages\CreateUserClient::route('/create'),
            'edit' => Pages\EditUserClient::route('/{record}/edit'),
            'view' => Pages\ViewUserClient::route('/{record}'),
        ];
    }
}