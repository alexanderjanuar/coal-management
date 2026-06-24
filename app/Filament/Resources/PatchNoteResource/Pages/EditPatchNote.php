<?php

namespace App\Filament\Resources\PatchNoteResource\Pages;

use App\Filament\Resources\PatchNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPatchNote extends EditRecord
{
    protected static string $resource = PatchNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
