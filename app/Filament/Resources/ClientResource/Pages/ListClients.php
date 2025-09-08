<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Filament\Widgets\Clients\ClientBasicStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            ClientBasicStatsWidget::class,
        ];
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Client Baru')
                ->icon('heroicon-o-plus'),
        ];
    }
}
