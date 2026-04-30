<?php

namespace App\Filament\Resources\ClientGroupResource\Pages;

use App\Filament\Resources\ClientGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewClientGroup extends ViewRecord
{
    protected static string $resource = ClientGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Grup'),
        ];
    }
}
