<?php

namespace App\Filament\Resources\AccountRepresentativeResource\Pages;

use App\Filament\Resources\AccountRepresentativeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateAccountRepresentative extends CreateRecord
{
    protected static string $resource = AccountRepresentativeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Account Representative berhasil ditambahkan')
            ->body("AR {$this->record->name} telah berhasil dibuat.");
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Log activity for creating new AR
        activity()
            ->withProperties($data)
            ->log("Account Representative baru akan dibuat: {$data['name']}");

        return $data;
    }

    protected function afterCreate(): void
    {
        // Log successful creation
        activity()
            ->performedOn($this->record)
            ->log("Account Representative {$this->record->name} berhasil dibuat");
    }

    public function getTitle(): string
    {
        return 'Tambah Account Representative Baru';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Simpan AR'),
            $this->getCreateAnotherFormAction()
                ->label('Simpan & Tambah Lagi'),
            $this->getCancelFormAction()
                ->label('Batal'),
        ];
    }
}