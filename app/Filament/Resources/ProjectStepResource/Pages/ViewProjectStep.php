<?php

namespace App\Filament\Resources\ProjectStepResource\Pages;

use App\Filament\Resources\ProjectStepResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProjectStep extends ViewRecord
{
    protected static string $resource = ProjectStepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
