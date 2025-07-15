<?php

namespace App\Filament\Clusters\LaporanPajak\Resources\IncomeTaxResource\Pages;

use App\Filament\Clusters\LaporanPajak\Resources\IncomeTaxResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIncomeTax extends EditRecord
{
    protected static string $resource = IncomeTaxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
