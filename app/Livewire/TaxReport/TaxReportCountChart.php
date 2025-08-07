<?php

namespace App\Livewire\TaxReport;

use App\Models\TaxReport;
use Filament\Widgets\ChartWidget;

class TaxReportCountChart extends ChartWidget
{
    protected static ?string $heading = 'Laporan Pajak Bulanan';

    protected static ?string $description = 'Distribusi laporan pajak berdasarkan bulan';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';


    public ?string $filter = 'all';

    protected function getData(): array
    {
        $months = [
            'January' => 'Jan',
            'February' => 'Feb',
            'March' => 'Mar',
            'April' => 'Apr',
            'May' => 'May',
            'June' => 'Jun',
            'July' => 'Jul',
            'August' => 'Aug',
            'September' => 'Sep',
            'October' => 'Oct',
            'November' => 'Nov',
            'December' => 'Dec'
        ];

        // Build query to count tax reports grouped by month
        $query = TaxReport::selectRaw('month, COUNT(*) as count')
            ->groupBy('month');

        // Apply year filter if selected
        if ($this->filter !== 'all') {
            $query->whereYear('created_at', $this->filter);
        }

        $data = $query->get()->keyBy('month');

        // Prepare chart data
        $labels = [];
        $counts = [];

        foreach ($months as $fullMonth => $shortMonth) {
            $labels[] = $shortMonth;
            $monthData = $data->get($fullMonth);
            $counts[] = $monthData ? (int) $monthData->count : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Laporan Pajak',
                    'data' => $counts,
                    'backgroundColor' => [
                        'rgba(251, 146, 60, 0.8)',   // August - Orange
                    ],
                    'borderColor' => [
                        'rgb(251, 146, 60)',   // August
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            'all' => 'Semua Tahun',
            date('Y') => 'Tahun ' . date('Y'),
            date('Y') - 1 => 'Tahun ' . (date('Y') - 1),
            date('Y') - 2 => 'Tahun ' . (date('Y') - 2),
        ];
    }

   
}