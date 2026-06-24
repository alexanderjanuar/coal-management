<?php

namespace App\Filament\Resources\PatchNoteResource\Pages;

use App\Filament\Resources\PatchNoteResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePatchNote extends CreateRecord
{
    protected static string $resource = PatchNoteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
