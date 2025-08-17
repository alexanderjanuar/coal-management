<?php

namespace App\Filament\Resources\UserClientResource\Pages;

use App\Filament\Resources\UserClientResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\UserClient;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;

class CreateUserClient extends CreateRecord
{
    protected static string $resource = UserClientResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Create the user first
        $user = User::create([
            'name' => $data['user']['name'],
            'email' => $data['user']['email'],
            'password' => Hash::make($data['user']['password']),
        ]);

        $firstUserClient = null;

        // Create multiple UserClient records
        foreach ($data['client_ids'] as $clientId) {
            $userClient = UserClient::create([
                'user_id' => $user->id,
                'client_id' => $clientId,
            ]);

            if (!$firstUserClient) {
                $firstUserClient = $userClient;
            }
        }

        return $firstUserClient;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }


}
