<?php

namespace App\Filament\Resources\PicResource\Pages;

use App\Filament\Resources\PicResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPics extends ListRecords
{
    protected static string $resource = PicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
