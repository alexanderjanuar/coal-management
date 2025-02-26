<?php

namespace App\Livewire\Widget;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Carbon;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectReportChart extends ApexChartWidget
{
    protected static ?string $chartId = 'ProjectReportChart';
    protected static ?string $heading = 'Project Creation Timeline';
    protected static ?string $subheading = 'Number of projects created over time';

    // Make widget responsive

    protected function getOptions(): array
    {
        // Get projects created in the last 6 months
        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        // Group projects by month and count them
        $projectData = Project::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
            
        // Format data for the chart
        $categories = [];
        $series = [];
        
        // Create arrays for all months in the range (including months with zero projects)
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $monthKey = $currentDate->format('Y-m');
            $monthName = $currentDate->format('M Y');
            
            $categories[] = $monthName;
            
            // Find if we have data for this month
            $monthData = $projectData->firstWhere('month', $monthKey);
            $series[] = $monthData ? $monthData->count : 0;
            
            $currentDate->addMonth();
        }

        $hasData = !empty($series) && array_sum($series) > 0;

        $avgProjects = count($series) > 0 ? array_sum($series) / count($series) : 0;

        return [
            'chart' => [
                'type' => 'area', // Changed to area to ensure fill works
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
                    'name' => 'New Projects',
                    'data' => $series
                ]
            ],
            'noData' => [
                'text' => 'No project data available',
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
                'size' => 5,
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