<?php

namespace App\Livewire\Dashboard\Widgets;

use App\Models\Project;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ProjectMonthlyChart extends ApexChartWidget
{
    protected static ?string $chartId = 'projectMonthlyChart';
    protected static ?string $heading = 'Tren Proyek Bulanan';
    protected static ?string $subheading = 'Proyek dibuat vs diselesaikan per bulan';

    public ?string $filter = '6_months';

    protected static ?int $contentHeight = 280;

    protected function getFilters(): ?array
    {
        return [
            '6_months' => '6 Bulan Terakhir',
            'year' => 'Tahun Ini',
        ];
    }

    private function baseProjectQuery()
    {
        $user = auth()->user();
        $query = Project::query();

        if (!$user->hasRole('super-admin') && !$user->hasRole('director')) {
            $clientIds = $user->userClients()->pluck('client_id')->toArray();

            if (!empty($clientIds)) {
                $query->whereIn('client_id', $clientIds)
                    ->where(function ($sub) use ($user) {
                        $sub->where('pic_id', $user->id)
                            ->orWhereHas('userProject', function ($q) use ($user) {
                                $q->where('user_id', $user->id);
                            });
                    });
            }
        }

        return $query;
    }

    protected function getOptions(): array
    {
        if ($this->filter === 'year') {
            $startDate = Carbon::now()->startOfYear();
        } else {
            $startDate = Carbon::now()->subMonths(5)->startOfMonth();
        }
        $endDate = Carbon::now()->endOfMonth();

        // Created projects per month
        $created = (clone $this->baseProjectQuery())
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        // Completed projects per month
        $completed = (clone $this->baseProjectQuery())
            ->select(
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as count')
            )
            ->where('status', 'completed')
            ->where('updated_at', '>=', $startDate)
            ->where('updated_at', '<=', $endDate)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        // Build categories and series for each month in range
        $categories = [];
        $createdSeries = [];
        $completedSeries = [];

        $current = $startDate->copy()->startOfMonth();
        while ($current <= $endDate) {
            $key = $current->format('Y-m');
            $categories[] = $current->locale('id')->translatedFormat('M Y');
            $createdSeries[] = $created[$key] ?? 0;
            $completedSeries[] = $completed[$key] ?? 0;
            $current->addMonth();
        }

        return [
            'chart' => [
                'type' => 'area',
                'height' => 280,
                'toolbar' => ['show' => false],
                'zoom' => ['enabled' => false],
                'background' => 'transparent',
                'animations' => [
                    'enabled' => true,
                    'speed' => 500,
                ],
                'fontFamily' => 'inherit',
            ],
            'series' => [
                [
                    'name' => 'Dibuat',
                    'data' => $createdSeries,
                ],
                [
                    'name' => 'Selesai',
                    'data' => $completedSeries,
                ],
            ],
            'colors' => ['#06b6d4', '#6b7280'],
            'stroke' => [
                'curve' => 'smooth',
                'width' => [2.5, 2.5],
                'lineCap' => 'round',
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shade' => 'light',
                    'type' => 'vertical',
                    'shadeIntensity' => 0.3,
                    'opacityFrom' => 0.45,
                    'opacityTo' => 0.05,
                    'stops' => [0, 90, 100],
                ],
            ],
            'markers' => [
                'size' => 4,
                'strokeWidth' => 0,
                'hover' => ['size' => 6],
            ],
            'grid' => [
                'show' => true,
                'borderColor' => '#e5e7eb',
                'strokeDashArray' => 4,
                'padding' => ['left' => 8, 'right' => 8],
            ],
            'xaxis' => [
                'categories' => $categories,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                        'fontSize' => '11px',
                        'fontWeight' => 500,
                        'cssClass' => 'text-gray-500 dark:text-gray-400',
                    ],
                ],
                'axisBorder' => ['show' => false],
                'axisTicks' => ['show' => false],
                'crosshairs' => [
                    'show' => true,
                    'stroke' => [
                        'color' => '#06b6d4',
                        'width' => 1,
                        'dashArray' => 3,
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                        'fontSize' => '11px',
                        'cssClass' => 'text-gray-500 dark:text-gray-400',
                    ],
                    'formatter' => 'function (val) { return Math.round(val) }',
                ],
                'min' => 0,
                'forceNiceScale' => true,
            ],
            'tooltip' => [
                'enabled' => true,
                'theme' => 'dark',
                'shared' => true,
                'intersect' => false,
                'y' => [
                    'formatter' => 'function (val) { return val + " proyek" }',
                ],
            ],
            'legend' => [
                'show' => true,
                'position' => 'top',
                'horizontalAlign' => 'right',
                'fontFamily' => 'inherit',
                'fontSize' => '12px',
                'markers' => [
                    'size' => 4,
                    'shape' => 'circle',
                ],
                'itemMargin' => ['horizontal' => 12],
            ],
            'dataLabels' => ['enabled' => false],
            'noData' => [
                'text' => 'Belum ada data proyek',
                'align' => 'center',
                'verticalAlign' => 'middle',
                'style' => [
                    'fontSize' => '14px',
                    'fontFamily' => 'inherit',
                ],
            ],
        ];
    }

    public function getPollingInterval(): ?string
    {
        return '60s';
    }
}
