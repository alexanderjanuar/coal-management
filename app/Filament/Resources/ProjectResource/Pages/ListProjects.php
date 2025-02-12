<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Project;
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
            'all' => Tab::make('All')
                ->icon('heroicon-m-squares-2x2')
                ->badge(Project::query()->count()),  // Grid icon representing all items
            'On Spot' => Tab::make('On Spot')
                ->icon('heroicon-m-bolt')  // Lightning bolt representing immediate/on-spot tasks
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'single'))
                ->badge(Project::query()->where('type', "single")->count()),
            'Monthly' => Tab::make('Monthly')
                ->icon('heroicon-m-calendar')  // Calendar icon for monthly tasks
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'monthly'))
                ->badge(Project::query()->where('type', "monthly")->count()),
            'Yearly' => Tab::make('Yearly')
                ->icon('heroicon-m-calendar-days')  // Calendar with days for yearly tasks
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'yearly'))
                ->badge(Project::query()->where('type', "yearly")->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
