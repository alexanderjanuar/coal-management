<?php

namespace App\Filament\Resources\TaxReportResource\Pages;

use App\Filament\Resources\TaxReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTaxReport extends ViewRecord
{
    protected static string $resource = TaxReportResource::class;
    protected static string $view = 'filament.pages.tax-report.index';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Laporan')
                ->icon('heroicon-o-pencil-square'),
        ];
    }

    protected function getViewData(): array
    {
        return [
            'record' => $this->record,
        ];
    }
}