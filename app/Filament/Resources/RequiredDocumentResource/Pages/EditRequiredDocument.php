<?php

namespace App\Filament\Resources\RequiredDocumentResource\Pages;

use App\Filament\Resources\RequiredDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRequiredDocument extends EditRecord
{
    protected static string $resource = RequiredDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
