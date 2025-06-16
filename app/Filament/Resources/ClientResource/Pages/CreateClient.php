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
        // Get users with the required roles
        $directors = User::role('direktur')->get();
        $staffMembers = User::role('staff')->get();
        $projectManagers = User::role('project-manager')->get();
        $verificator = User::role('verificator')->get();

        // Count how many users we're assigning for the notification
        $assignedCount = 0;

        // Assign each director to the new client
        foreach ($directors as $director) {
            UserClient::create([
                'user_id' => $director->id,
                'client_id' => $this->record->id
            ]);
            $assignedCount++;
        }

        // Assign each staff member to the new client
        foreach ($staffMembers as $staff) {
            UserClient::create([
                'user_id' => $staff->id,
                'client_id' => $this->record->id
            ]);
            $assignedCount++;
        }

        // Assign each project manager to the new client
        foreach ($projectManagers as $manager) {
            UserClient::create([
                'user_id' => $manager->id,
                'client_id' => $this->record->id
            ]);
            $assignedCount++;
        }

        foreach ($verificator as $verif) {
            UserClient::create([
                'user_id' => $verif->id,
                'client_id' => $this->record->id
            ]);
            $assignedCount++;
        }

        // Show a notification with the assignment details
        Notification::make()
            ->title('Client successfully created')
            ->body("All directors, project managers, and staff have been automatically assigned to this client. ({$assignedCount} users in total)")
            ->success()
            ->send();
    }
}