<?php

namespace App\Filament\Resources\UserClientResource\Pages;

use App\Filament\Resources\UserClientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserClient extends EditRecord
{
    protected static string $resource = UserClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
