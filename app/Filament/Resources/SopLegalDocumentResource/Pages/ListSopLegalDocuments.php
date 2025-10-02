<?php

namespace App\Filament\Resources\SopLegalDocumentResource\Pages;

use App\Filament\Resources\SopLegalDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSopLegalDocuments extends ListRecords
{
    protected static string $resource = SopLegalDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
