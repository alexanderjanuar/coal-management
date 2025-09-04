<?php

namespace App\Filament\Resources\AccountRepresentativeResource\Pages;

use App\Filament\Resources\AccountRepresentativeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditAccountRepresentative extends EditRecord
{
    protected static string $resource = AccountRepresentativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Detail'),
            // Actions\DeleteAction::make()
            //     ->label('Hapus AR')
            //     ->before(function () {
            //         // Check if AR has clients before deletion
            //         if ($this->record->clients()->count() > 0) {
            //             throw new \Exception(
            //                 "AR {$this->record->name} tidak dapat dihapus karena masih memiliki " . 
            //                 $this->record->clients()->count() . " klien. " .
            //                 "Hapus atau pindahkan klien terlebih dahulu."
            //             );
            //         }
            //     }),
        ];
    }

    // protected function getRedirectUrl(): ?string
    // {
    //     return $this->getResource()::getUrl('view', ['record' => $this->record]);
    // }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Account Representative berhasil diperbarui')
            ->body("Data AR {$this->record->name} telah berhasil disimpan.");
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Log what's being changed
        $changes = [];
        $original = $this->record->getOriginal();
        
        foreach ($data as $key => $value) {
            if (isset($original[$key]) && $original[$key] != $value) {
                $changes[$key] = [
                    'from' => $original[$key],
                    'to' => $value
                ];
            }
        }

        if (!empty($changes)) {
            activity()
                ->performedOn($this->record)
                ->withProperties([
                    'changes' => $changes,
                    'new_data' => $data
                ])
                ->log("Data AR {$this->record->name} akan diperbarui");
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Log successful update
        activity()
            ->performedOn($this->record)
            ->log("Data AR {$this->record->name} berhasil diperbarui");
    }

    public function getTitle(): string
    {
        return "Edit Account Representative: {$this->record->name}";
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan Perubahan'),
            // $this->getCancelFormAction()
            //     ->label('Batal')
            //     ->url($this->getResource()::getUrl('view', ['record' => $this->record])),
        ];
    }
}