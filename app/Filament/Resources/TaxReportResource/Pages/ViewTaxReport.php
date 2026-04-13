<?php

namespace App\Filament\Resources\TaxReportResource\Pages;

use App\Filament\Resources\TaxReportResource;
use App\Models\Client;
use App\Models\TaxReport;
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
        $record      = $this->record;
        $currentMonth = $record->month;
        $currentYear  = $record->year ?? $record->created_at->year;

        // Load all reports for this month+year in one query, keyed by client_id
        $reportsForPeriod = TaxReport::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->get(['id', 'client_id'])
            ->keyBy('client_id');

        // Only include clients that actually have a report for this month+year
        $clientNavMap = Client::orderBy('name')
            ->whereIn('id', $reportsForPeriod->keys())
            ->get(['id', 'name'])
            ->map(function (Client $client) use ($reportsForPeriod, $record) {
                $report = $reportsForPeriod->get($client->id);

                return [
                    'id'         => $client->id,
                    'name'       => $client->name,
                    'is_current' => $client->id === $record->client_id,
                    'url'        => TaxReportResource::getUrl('view', ['record' => $report->id]),
                ];
            })
            ->values();

        return [
            'record'       => $record,
            'clientNavMap' => $clientNavMap,
            'currentMonth' => $currentMonth,
            'currentYear'  => $currentYear,
        ];
    }
}