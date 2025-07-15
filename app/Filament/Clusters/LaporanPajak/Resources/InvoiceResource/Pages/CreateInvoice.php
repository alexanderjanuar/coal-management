<?php

namespace App\Filament\Clusters\LaporanPajak\Resources\InvoiceResource\Pages;

use App\Filament\Clusters\LaporanPajak\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;
}
