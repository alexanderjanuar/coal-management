<?php

namespace App\Filament\Resources\UserClientResource\Pages;

use App\Filament\Resources\UserClientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\UserClient;
use Illuminate\Support\Facades\Hash;

class EditUserClient extends EditRecord
{
    protected static string $resource = UserClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Get all client IDs for this user
        $data['client_ids'] = UserClient::where('user_id', $this->record->user_id)
            ->pluck('client_id')
            ->toArray();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Update user details
        $record->user->update([
            'name' => $data['user']['name'],
            'email' => $data['user']['email'],
        ]);

        if (!empty($data['user']['password'])) {
            $record->user->update([
                'password' => Hash::make($data['user']['password']),
            ]);
        }

        // Delete existing client relationships
        UserClient::where('user_id', $record->user_id)->delete();

        // Create new client relationships
        foreach ($data['client_ids'] as $clientId) {
            UserClient::create([
                'user_id' => $record->user_id,
                'client_id' => $clientId,
            ]);
        }

        return $record;
    }
}
