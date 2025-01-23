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
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class UserClientResource extends Resource
{
    protected static ?string $model = UserClient::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'Employees';
    protected static ?string $modelLabel = 'Employee';
    protected static ?string $pluralModelLabel = 'Employees';
    protected static ?string $breadcrumb = 'Employees';

    protected static ?string $navigationGroup = 'Master Data';
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('User Details')
                ->description('Create or edit team member information')
                ->collapsible()
                ->relationship('user')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->placeholder('Enter full name')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->placeholder('email@example.com'),

                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->required(fn($context) => $context === 'create')
                        ->dehydrated(fn($state) => filled($state))
                        ->revealable()
                        ->autocomplete('new-password')
                ])
                ->aside(),
            Section::make('Assignment')
                ->description('Assign client and role')
                ->schema([
                    Forms\Components\Select::make('client_id')
                        ->relationship('client', 'name')
                        ->searchable()
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
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.roles.name')
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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('client')
                    ->relationship('client', 'name'),
                Tables\Filters\SelectFilter::make('role')
                    ->options(fn() => \Spatie\Permission\Models\Role::whereNot('name', 'super-admin')
                        ->pluck('name', 'name'))
            ])
            ->actions([
                Tables\Actions\Action::make('assign_role')
                    ->label('Assign Role')
                    ->icon('heroicon-m-user-group')
                    ->color('success')
                    ->modalHeading(fn($record) => "Assign Role to {$record->user->name}")
                    ->modalIcon('heroicon-o-user-circle')
                    ->modalDescription('Select a role to assign to this employee.')
                    ->form([
                        Forms\Components\Select::make('role')
                            ->label('Role')
                            ->options(function () {
                                $user = auth()->user();

                                // Get base roles excluding super-admin
                                $roles = \Spatie\Permission\Models\Role::whereNot('name', 'super-admin');

                                // If user is not super-admin and not director, don't show any options
                                if (!$user->hasRole('super-admin') && !$user->hasRole('direktur')) {
                                    return [];
                                }

                                // If user is director, only show project-manager and staff
                                if ($user->hasRole('direktur')) {
                                    $roles->whereIn('name', ['project-manager', 'staff']);
                                }

                                return $roles->pluck('name', 'name')
                                    ->mapWithKeys(fn($role, $key) => [
                                        $key => match ($key) {
                                            'project-manager' => 'Project Manager',
                                            'admin' => 'Admin',
                                            'direktur' => 'Director',
                                            'staff' => 'Staff',
                                            default => $key
                                        }
                                    ]);
                            })
                            ->required()
                            ->searchable()
                            ->placeholder('Select a role')
                            ->helperText(function () {
                                $user = auth()->user();
                                if ($user->hasRole('super-admin')) {
                                    return 'As super admin, you can assign any role.';
                                } elseif ($user->hasRole('direktur')) {
                                    return 'As director, you can only assign Project Manager or Staff roles.';
                                }
                                return 'You do not have permission to assign roles.';
                            })
                            ->disabled(fn() => !auth()->user()->hasRole(['super-admin', 'direktur']))
                    ])
                    ->requiresConfirmation()
                    ->action(function (array $data, UserClient $record): void {
                        $record->user->syncRoles([$data['role']]);
                        Notification::make()
                            ->title('Role Assigned')
                            ->success()
                            ->body("Successfully assigned role to {$record->user->name}")
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
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

                                    // Get base roles excluding super-admin
                                    $roles = \Spatie\Permission\Models\Role::whereNot('name', 'super-admin');

                                    // If user is not super-admin and not director, don't show any options
                                    if (!$user->hasRole('super-admin') && !$user->hasRole('direktur')) {
                                        return [];
                                    }

                                    // If user is director, only show project-manager and staff
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
                                ->placeholder('Select a role')
                                ->helperText(function () {
                                    $user = auth()->user();
                                    if ($user->hasRole('super-admin')) {
                                        return 'As super admin, you can assign any role.';
                                    } elseif ($user->hasRole('direktur')) {
                                        return 'As director, you can only assign Project Manager or Staff roles.';
                                    }
                                    return 'You do not have permission to assign roles.';
                                })
                                ->disabled(fn() => !auth()->user()->hasRole(['super-admin', 'direktur']))
                        ])
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->user->syncRoles([$data['role']]);
                            });

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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);
        return $data;
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserClients::route('/'),
            'create' => Pages\CreateUserClient::route('/create'),
            'edit' => Pages\EditUserClient::route('/{record}/edit'),
        ];
    }
}
