<?php

namespace App\Filament\Resources\SopLegalDocumentResource\Pages;

use App\Filament\Resources\SopLegalDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSopLegalDocument extends EditRecord
{
    protected static string $resource = SopLegalDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
