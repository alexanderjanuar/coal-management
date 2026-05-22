<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Exports\Clients\ClientsDetailedExport;
use App\Exports\Clients\ClientsExport;
use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;

class ListClients extends Page
{
    protected static string $resource = ClientResource::class;

    protected static string $view = 'filament.resources.client-resource.pages.list-clients-clickup';

    public function getTitle(): string
    {
        return 'Clients';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\Action::make('export_simple')
                    ->label('Simple Export')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        return Excel::download(
                            new ClientsExport(),
                            'clients-' . now()->format('Y-m-d-H-i') . '.xlsx',
                        );
                    }),

                Actions\Action::make('export_detailed')
                    ->label('Detailed Export (Multi-Sheet)')
                    ->icon('heroicon-o-document-chart-bar')
                    ->action(function () {
                        return Excel::download(
                            new ClientsDetailedExport(),
                            'clients-detailed-' . now()->format('Y-m-d-H-i') . '.xlsx',
                        );
                    }),
            ])
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->button(),

            Actions\CreateAction::make()
                ->url(fn () => ClientResource::getUrl('create'))
                ->label('Tambah Client Baru')
                ->icon('heroicon-o-plus'),
        ];
    }
}
