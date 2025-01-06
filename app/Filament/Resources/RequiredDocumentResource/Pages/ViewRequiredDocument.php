<?php

namespace App\Filament\Resources\RequiredDocumentResource\Pages;

use App\Filament\Resources\RequiredDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRequiredDocument extends ViewRecord
{
    protected static string $resource = RequiredDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
