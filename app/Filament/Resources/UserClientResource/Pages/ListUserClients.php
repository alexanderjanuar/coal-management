<?php

namespace App\Filament\Resources\UserClientResource\Pages;

use App\Filament\Resources\UserClientResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserClients extends ListRecords
{
    protected static string $resource = UserClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
