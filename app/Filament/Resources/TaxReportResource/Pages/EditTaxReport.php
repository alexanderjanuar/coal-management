<?php

namespace App\Filament\Resources\TaxReportResource\Pages;

use App\Filament\Resources\TaxReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaxReport extends EditRecord
{
    protected static string $resource = TaxReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
