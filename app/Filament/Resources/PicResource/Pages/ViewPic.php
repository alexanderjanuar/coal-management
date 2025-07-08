<?php

namespace App\Filament\Resources\PicResource\Pages;

use App\Filament\Resources\PicResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPic extends ViewRecord
{
    protected static string $resource = PicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
