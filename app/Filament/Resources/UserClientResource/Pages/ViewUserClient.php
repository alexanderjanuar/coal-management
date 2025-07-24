<?php

namespace App\Filament\Resources\UserClientResource\Pages;

use App\Filament\Resources\UserClientResource;
use App\Models\User;
use App\Models\UserClient;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUserClient extends ViewRecord
{
    protected static string $resource = UserClientResource::class;
    protected static string $view = 'filament.pages.user-client-details';

    public function mount(int|string $record): void
    {
        parent::mount($record);
    }

    protected function resolveRecord(int|string $key): UserClient
    {
        $user = User::findOrFail($key);
        
        // Get user's first client assignment or create temp record
        return UserClient::where('user_id', $user->id)->first() 
            ?? new UserClient(['user_id' => $user->id, 'user' => $user]);
    }
}
