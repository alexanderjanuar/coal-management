<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Models\User;
use App\Models\UserClient;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected function afterCreate(): void
    {
        // Get all directors
        $directors = User::role('direktur')->get();

        // Assign each director to the new client
        foreach ($directors as $director) {
            UserClient::create([
                'user_id' => $director->id,
                'client_id' => $this->record->id
            ]);
        }

        // Optional: Show a notification
        Notification::make()
            ->title('Client created successfully')
            ->body('All directors have been automatically assigned to this client.')
            ->success()
            ->send();
    }
}
