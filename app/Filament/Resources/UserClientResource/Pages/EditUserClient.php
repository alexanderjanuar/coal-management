<?php

namespace App\Filament\Resources\UserClientResource\Pages;

use App\Filament\Resources\UserClientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\UserClient;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;

class EditUserClient extends EditRecord
{
    protected static string $resource = UserClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus')
                ->modalHeading('Hapus Karyawan')
                ->modalDescription('Apakah Anda yakin ingin menghapus karyawan ini?')
                ->successNotificationTitle('Karyawan Dihapus'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load user data
        $user = User::find($this->record->user_id);
        
        if ($user) {
            // Fill user fields with dot notation
            $data['user.name'] = $user->name;
            $data['user.email'] = $user->email;
            $data['user.department'] = $user->department;
            $data['user.position'] = $user->position;
            $data['user.status'] = $user->status;
            $data['user.avatar_path'] = $user->avatar_path;
            $data['user.avatar_url'] = $user->avatar_url;
        }

        // Get all client IDs for this user
        $data['client_ids'] = UserClient::where('user_id', $this->record->user_id)
            ->pluck('client_id')
            ->toArray();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Update user details
        $updateData = [
            'name' => $data['user']['name'],
            'email' => $data['user']['email'],
            'department' => $data['user']['department'] ?? null,
            'position' => $data['user']['position'] ?? null,
            'status' => $data['user']['status'] ?? 'active',
        ];

        // Handle avatar_path
        if (isset($data['user']['avatar_path'])) {
            $record->user->deleteOldAvatar();
            $updateData['avatar_path'] = $data['user']['avatar_path'];
            $updateData['avatar_url'] = 'storage/' . $data['user']['avatar_path'];
        }

        // Handle avatar_url only if no file uploaded
        if (isset($data['user']['avatar_url']) && !isset($data['user']['avatar_path'])) {
            $updateData['avatar_url'] = $data['user']['avatar_url'];
            $updateData['avatar_path'] = null;
        }

        // Update password if provided
        if (!empty($data['user']['password'])) {
            $updateData['password'] = Hash::make($data['user']['password']);
        }

        $record->user->update($updateData);

        // Delete existing client relationships
        UserClient::where('user_id', $record->user_id)->delete();

        // Create new client relationships
        if (isset($data['client_ids']) && is_array($data['client_ids'])) {
            foreach ($data['client_ids'] as $clientId) {
                UserClient::create([
                    'user_id' => $record->user_id,
                    'client_id' => $clientId,
                ]);
            }
        }

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Karyawan Diperbarui')
            ->body('Data karyawan berhasil diperbarui.');
    }
}