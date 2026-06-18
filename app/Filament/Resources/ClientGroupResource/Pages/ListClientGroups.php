<?php

namespace App\Filament\Resources\ClientGroupResource\Pages;

use App\Filament\Resources\ClientGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\Page;

class ListClientGroups extends Page
{
    protected static string $resource = ClientGroupResource::class;

    protected static string $view = 'filament.resources.client-group-resource.pages.list-groups-panels';

    public function getTitle(): string
    {
        return 'Grup Client';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->url(fn () => ClientGroupResource::getUrl('create'))
                ->label('Tambah Grup Client')
                ->icon('heroicon-o-plus'),
        ];
    }
}
