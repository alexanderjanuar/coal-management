<?php

namespace App\Filament\Resources\TaxReportResource\Pages;

use App\Filament\Resources\TaxReportResource;
use App\Models\TaxReport;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxReport extends CreateRecord
{
    protected static string $resource = TaxReportResource::class;

    protected function beforeCreate(): void
    {
        // Check for existing tax report for the same client in the same month
        $data = $this->data;
        $existingReport = TaxReport::where('client_id', $data['client_id'])
            ->where('month', $data['month'])
            ->first();
        
        if ($existingReport) {
            Notification::make()
                ->warning()
                ->title('Tax Report Already Exists')
                ->body('A tax report for this client in ' . $data['month'] . ' already exists.')
                ->persistent()
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url(TaxReportResource::getUrl('view', ['record' => $existingReport])),
                ])
                ->send();
                
            $this->halt();
        }
    }
}
