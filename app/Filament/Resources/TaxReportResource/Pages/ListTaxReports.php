<?php

namespace App\Filament\Resources\TaxReportResource\Pages;

use App\Filament\Resources\TaxReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Models\TaxReport;

class ListTaxReports extends ListRecords
{
    protected static string $resource = TaxReportResource::class;
    
    protected static string $view = 'filament.pages.tax-report.list-tax-reports';

    public ?int $selectedYear = null;
    public ?string $selectedMonth = null;

    public function mount(): void
    {
        parent::mount();
        
        // Default to current year
        $this->selectedYear = $this->selectedYear ?? now()->year;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Laporan Pajak')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getAvailableYears(): array
    {
        $years = TaxReport::distinct()
            ->pluck('year')
            ->filter()
            ->sort()
            ->reverse()
            ->values()
            ->toArray();

        if (empty($years)) {
            $years[] = now()->year;
        }

        return $years;
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if ($this->selectedYear) {
            $query->where('year', $this->selectedYear);
        }

        if ($this->selectedMonth) {
            $query->where('month', $this->selectedMonth);
        }

        return $query;
    }

    // Update table to refresh when year/month changes
    public function updatedSelectedYear(): void
    {
        $this->selectedMonth = null;
        $this->resetTable();
    }

    public function updatedSelectedMonth(): void
    {
        $this->resetTable();
    }
}