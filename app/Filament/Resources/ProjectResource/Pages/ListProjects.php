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

    protected function getBaseQuery(): Builder
    {
        $user = auth()->user();

        // Start with Project model's query builder
        $query = Project::query();

        // If user is not super-admin, filter by their assigned clients
        if (!$user->hasRole('super-admin')) {
            $query->whereIn('client_id', function ($subQuery) use ($user) {
                $subQuery->select('client_id')
                    ->from('user_clients')
                    ->where('user_id', $user->id);
            });
        }

        return $query;
    }

    public function getTabs(): array
    {
        $baseQuery = $this->getBaseQuery();

        return [
            'all' => Tab::make('All')
                ->icon('heroicon-m-squares-2x2')
                ->badge($baseQuery->count()),

            'On Spot' => Tab::make('On Spot')
                ->icon('heroicon-m-bolt')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'single'))
                ->badge($baseQuery->clone()->where('type', 'single')->count()),

            'Monthly' => Tab::make('Monthly')
                ->icon('heroicon-m-calendar')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'monthly'))
                ->badge($baseQuery->clone()->where('type', 'monthly')->count()),

            'Yearly' => Tab::make('Yearly')
                ->icon('heroicon-m-calendar-days')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'yearly'))
                ->badge($baseQuery->clone()->where('type', 'yearly')->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}