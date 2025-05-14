<?php

namespace App\Filament\Resources\TaxReportResource\Pages;

use App\Filament\Resources\TaxReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTaxReports extends ListRecords
{
    protected static string $resource = TaxReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'January' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('month', 'January')),
            'February' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('month', 'February')),
            'March' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('month', 'March')),
            'April' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('month', 'April')),
            'May' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('month', 'May')),
            'June' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('month', 'June')),
            'July' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('month', 'July')),
            'August' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('month', 'August')),
            'September' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('month', 'September')),
            'October' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('month', 'October')),
            'November' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('month', 'November')),
            'December' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('month', 'December')),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        // Get the current month name (e.g., "January", "February", etc.)
        $currentMonth = now()->format('F');
        
        // Check if the current month exists as a tab
        if (array_key_exists($currentMonth, $this->getTabs())) {
            return $currentMonth;
        }
        
        // Fallback to 'all' if the current month doesn't exist as a tab
        return 'all';
    }
}
