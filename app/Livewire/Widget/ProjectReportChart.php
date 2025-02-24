<?php

namespace App\Livewire\Widget;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Carbon;
use App\Models\Project;

class ProjectReportChart extends ApexChartWidget
{
    protected static ?string $chartId = 'ProjectReportChart';
    protected static ?string $heading = 'Urgent Projects Timeline';

    // Make widget responsive
    protected function getHeight(): ?string
    {
        return '400px';
    }


    protected function getOptions(): array
    {
        $projects = Project::with('client')
            ->where('priority', 'urgent')
            ->where('due_date', '>=', now())
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get()
            ->map(function ($project) {
                return [
                    'x' => $project->name . ' (' . $project->client->name . ')',
                    'y' => [
                        Carbon::parse($project->created_at)->timestamp * 1000,
                        Carbon::parse($project->due_date)->timestamp * 1000
                    ],
                ];
            })->toArray();

        $hasData = !empty($projects);

        return [
            'chart' => [
                'type' => 'rangeBar',
                'height' => '100%',
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
                    'name' => 'Urgent Projects',
                    'data' => $projects ?: []
                ]
            ],
            'noData' => [
                'text' => 'No urgent projects available',
                'align' => 'center',
                'verticalAlign' => 'middle',
                'style' => [
                    'fontSize' => '16px',
                    'fontFamily' => 'inherit',
                    'color' => '#ffffff'
                ]
            ],
            'colors' => ['#ef4444'],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => true,
                    'borderRadius' => 4,
                    'distributed' => true,
                ]
            ],
            'grid' => [
                'show' => false,
                'padding' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0
                ],
            ],
            'xaxis' => [
                'type' => 'datetime',
                'labels' => [
                    'show' => $hasData,
                    'style' => [
                        'fontFamily' => 'inherit',
                        'colors' => '#ffffff'
                    ],
                    'datetimeFormatter' => [
                        'year' => 'yyyy',
                        'month' => 'MMM \'yy',
                        'day' => 'dd MMM',
                    ],
                ],
                'axisBorder' => [
                    'show' => false
                ],
                'axisTicks' => [
                    'show' => false
                ],
                'crosshairs' => [
                    'show' => false
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'show' => $hasData,
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
                'axisBorder' => [
                    'show' => false
                ],
            ],
            'tooltip' => [
                'enabled' => true,
                'theme' => 'dark',
                'x' => [
                    'format' => 'dd MMM yyyy'
                ]
            ],
            'legend' => [
                'show' => false
            ],
            'states' => [
                'hover' => [
                    'filter' => [
                        'type' => 'none',
                    ]
                ],
            ],
        ];
    }


    public function getPollingInterval(): ?string
    {
        return '30s';
    }
}