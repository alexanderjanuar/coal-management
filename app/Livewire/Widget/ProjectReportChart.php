<?php

namespace App\Livewire\Widget;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Carbon;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectReportChart extends ApexChartWidget
{
    protected static ?string $chartId = 'ProjectReportChart';
    protected static ?string $heading = 'Project Completed Timeline';
    protected static ?string $subheading = 'Number of projects completed over time';

    public ?string $filter = 'month';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    protected function getOptions(): array
    {
        $activeFilter = $this->filter;

        // Set date range based on selected filter
        switch ($activeFilter) {
            case 'today':
                $startDate = Carbon::now()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $groupFormat = '%Y-%m-%d %H'; // Group by hour
                $displayFormat = 'H:i'; // Hour:Minute
                break;

            case 'week':
                $startDate = Carbon::now()->subWeek()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $groupFormat = '%Y-%m-%d'; // Group by day
                $displayFormat = 'D M d'; // Day of week, Month, Day
                break;

            case 'month':
                $startDate = Carbon::now()->subMonth()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $groupFormat = '%Y-%m-%d'; // Group by day
                $displayFormat = 'M d'; // Month, Day
                break;

            case 'year':
                $startDate = Carbon::now()->startOfYear()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $groupFormat = '%Y-%m'; // Group by month
                $displayFormat = 'M Y'; // Month, Year
                break;

            default:
                $startDate = Carbon::now()->subMonth()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $groupFormat = '%Y-%m-%d';
                $displayFormat = 'M d';
        }

        $projectData = Project::select(
            DB::raw('DATE_FORMAT(updated_at, "' . $groupFormat . '") as time_period'),
            DB::raw('COUNT(*) as count')
        )
            ->where('status', 'completed')
            ->where('updated_at', '>=', $startDate)
            ->where('updated_at', '<=', $endDate)
            ->groupBy('time_period')
            ->orderBy('time_period')
            ->get();


        // Format data for the chart
        $categories = [];
        $series = [];

        // Create arrays for all time periods in the range (including periods with zero projects)
        $currentDate = clone $startDate;

        // Different interval for different filters
        $interval = match ($activeFilter) {
            'today' => 'hour',
            'week', 'month' => 'day',
            'year' => 'month',
            default => 'day'
        };

        $intervalMethod = 'add' . ucfirst($interval);

        while ($currentDate <= $endDate) {
            $timePeriodKey = $currentDate->format(match ($interval) {
                'hour' => 'Y-m-d H',
                'day' => 'Y-m-d',
                'month' => 'Y-m',
                default => 'Y-m-d'
            });

            $timePeriodDisplay = $currentDate->format($displayFormat);

            $categories[] = $timePeriodDisplay;

            // Find if we have data for this time period
            $periodData = $projectData->firstWhere('time_period', $timePeriodKey);
            $series[] = $periodData ? $periodData->count : 0;

            $currentDate->$intervalMethod();
        }

        $hasData = !empty($series) && array_sum($series) > 0;
        $avgProjects = count($series) > 0 ? array_sum($series) / count($series) : 0;

        // Limit number of visible labels on x-axis for readability
        $maxLabels = 12;
        $showEveryNth = max(1, ceil(count($categories) / $maxLabels));

        $xaxisLabels = array_map(function ($index) use ($showEveryNth) {
            return $index % $showEveryNth === 0;
        }, array_keys($categories));

        return [
            'chart' => [
                'type' => 'area',
                'height' => '400px',
                'toolbar' => [
                    'show' => false
                ],
                'zoom' => [
                    'enabled' => false
                ],
                'background' => 'transparent',
                'animations' => [
                    'enabled' => true,
                    'speed' => 500,
                ],
            ],
            'series' => [
                [
                    'name' => 'Completed Projects',
                    'data' => $series
                ]
            ],
            'noData' => [
                'text' => 'No completed projects data available',
                'align' => 'center',
                'verticalAlign' => 'middle',
                'style' => [
                    'fontSize' => '16px',
                    'fontFamily' => 'inherit',
                    'color' => '#ffffff'
                ]
            ],
            'colors' => ['#f59e0b'], // Amber color
            'stroke' => [
                'curve' => 'smooth',
                'width' => 3,
                'lineCap' => 'round'
            ],
            'markers' => [
                'size' => $activeFilter === 'year' ? 5 : 0, // Only show markers for yearly view
                'hover' => [
                    'size' => 7
                ]
            ],

            'grid' => [
                'show' => true,
                'borderColor' => '#374151', // Gray-700
                'strokeDashArray' => 4,
            ],
            'xaxis' => [
                'categories' => $categories,
                'labels' => [
                    'show' => $hasData,
                    'style' => [
                        'fontFamily' => 'inherit',
                        'fontSize' => '12px',
                        'fontWeight' => 500,
                    ],
                    'offsetY' => 5,
                ],
                'axisBorder' => [
                    'show' => false
                ],
                'axisTicks' => [
                    'show' => false
                ],
                'crosshairs' => [
                    'show' => true,
                    'position' => 'back',
                    'stroke' => [
                        'color' => '#f59e0b', // Match main color
                        'width' => 1,
                        'dashArray' => 3,
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'show' => $hasData,
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
                'title' => [
                    'text' => 'Number of Projects',
                    'style' => [
                        'fontFamily' => 'inherit',
                    ]
                ],
                'min' => 0,
                'forceNiceScale' => true,
            ],
            'tooltip' => [
                'enabled' => true,
                'theme' => 'dark',
                'y' => [
                    'formatter' => 'function (val) { return val + " project(s)" }'
                ]
            ],
            'legend' => [
                'show' => true,
                'position' => 'bottom',
                'horizontalAlign' => 'center',
                'floating' => false,
                'fontFamily' => 'inherit',
            ],
            'dataLabels' => [
                'enabled' => false
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shade' => 'light',
                    'type' => 'vertical',
                    'shadeIntensity' => 0.4,
                    'gradientToColors' => ['rgba(245, 158, 11, 0.1)'],
                    'opacityFrom' => 0.8,
                    'opacityTo' => 0.2,
                    'stops' => [0, 90, 100]
                ]
            ],
            'states' => [
                'hover' => [
                    'filter' => [
                        'type' => 'lighten',
                        'value' => 0.1,
                    ]
                ],
                'active' => [
                    'filter' => [
                        'type' => 'none',
                    ]
                ]
            ],
        ];
    }

    public function getPollingInterval(): ?string
    {
        return '30s';
    }
}