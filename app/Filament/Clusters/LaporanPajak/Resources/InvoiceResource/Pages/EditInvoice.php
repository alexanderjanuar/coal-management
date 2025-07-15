<?php

namespace App\Filament\Clusters\LaporanPajak\Resources\InvoiceResource\Pages;

use App\Filament\Clusters\LaporanPajak\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
