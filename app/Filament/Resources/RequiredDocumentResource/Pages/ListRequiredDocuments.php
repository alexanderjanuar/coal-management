<?php

namespace App\Filament\Resources\RequiredDocumentResource\Pages;

use App\Filament\Resources\RequiredDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRequiredDocuments extends ListRecords
{
    protected static string $resource = RequiredDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
