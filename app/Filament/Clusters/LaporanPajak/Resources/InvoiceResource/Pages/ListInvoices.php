<?php

namespace App\Filament\Clusters\LaporanPajak\Resources\InvoiceResource\Pages;

use App\Filament\Clusters\LaporanPajak\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Laporan Pajak Baru')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make()
                ->label('Semua Periode')
                ->badge(function () {
                    return \App\Models\TaxReport::count();
                }),
        ];

        // Define all months with their Indonesian labels
        $months = [
            'January' => 'Januari',
            'February' => 'Februari', 
            'March' => 'Maret',
            'April' => 'April',
            'May' => 'Mei',
            'June' => 'Juni',
            'July' => 'Juli',
            'August' => 'Agustus',
            'September' => 'September',
            'October' => 'Oktober',
            'November' => 'November',
            'December' => 'Desember',
        ];

        // Only add tabs for months that have data
        foreach ($months as $monthEn => $monthId) {
            $count = \App\Models\TaxReport::where('month', 'like', "%{$monthEn}%")->count();
            
            // Only add tab if there are tax reports for this month (count > 0)
            if ($count > 0) {
                $tabs[$monthEn] = Tab::make($monthEn)
                    ->label($monthId)
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('month', 'like', "%{$monthEn}%"))
                    ->badge($count);
            }
        }

        return $tabs;
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

    /**
     * Get the page title
     */
    public function getTitle(): string
    {
        return 'Ringkasan Laporan PPN';
    }

    /**
     * Get the page heading
     */
    public function getHeading(): string
    {
        return 'Ringkasan Laporan PPN per Periode';
    }

    /**
     * Get the page subheading
     */
    public function getSubheading(): ?string
    {
        return 'Kelola dan pantau laporan pajak PPN berdasarkan periode dan klien';
    }

    /**
     * Get additional header actions for statistics
     */
    protected function getHeaderWidgets(): array
    {
        return [
            // You can add widget classes here if you create statistics widgets
        ];
    }

    /**
     * Override to add custom styling or behavior
     */
    public function mount(): void
    {
        parent::mount();
        
        // Add any custom initialization logic here
        // For example, you could set default filters based on user permissions
    }

    /**
     * Get the breadcrumbs for this page
     */
    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Laporan Pajak',
            '#' => 'PPN',
        ];
    }
}