<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'On Spot' => Tab::make('On Spot')
                ->icon('heroicon-m-user-group')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'single')),
            'Monthly' => Tab::make('Monthly')
                ->icon('heroicon-m-user-group')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'monthly')),
            'Yearly' => Tab::make('Yearly')
                ->icon('heroicon-m-user-group')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'yearly')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
