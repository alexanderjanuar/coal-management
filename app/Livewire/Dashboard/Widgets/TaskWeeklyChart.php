<?php

namespace App\Livewire\Dashboard\Widgets;

use App\Models\DailyTask;
use Illuminate\Support\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class TaskWeeklyChart extends ApexChartWidget
{
    protected static ?string $chartId = 'taskWeeklyChart';
    protected static ?string $heading = 'Aktivitas Tugas Mingguan';
    protected static ?string $subheading = 'Penyelesaian tugas 7 hari terakhir';

    protected static ?int $contentHeight = 280;

    protected function getOptions(): array
    {
        $user = auth()->user();
        $categories = [];
        $completedSeries = [];
        $remainingSeries = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $categories[] = $date->locale('id')->translatedFormat('D');

            $baseQuery = DailyTask::where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhereHas('assignedUsers', fn ($s) => $s->where('user_id', $user->id));
                })
                ->where(function ($dateQ) use ($date) {
                    $dateQ->where(function ($q) use ($date) {
                        $q->where('start_task_date', '<=', $date)
                          ->where('task_date', '>=', $date);
                    })->orWhere(function ($q) use ($date) {
                        $q->where('task_date', $date->toDateString())
                          ->whereNull('start_task_date');
                    });
                });

            $total = (clone $baseQuery)->count();
            $completed = (clone $baseQuery)->where('status', 'completed')->count();
            $remaining = max(0, $total - $completed);

            $completedSeries[] = $completed;
            $remainingSeries[] = $remaining;
        }

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 280,
                'stacked' => true,
                'toolbar' => ['show' => false],
                'background' => 'transparent',
                'animations' => [
                    'enabled' => true,
                    'speed' => 400,
                ],
                'fontFamily' => 'inherit',
            ],
            'series' => [
                [
                    'name' => 'Selesai',
                    'data' => $completedSeries,
                ],
                [
                    'name' => 'Belum',
                    'data' => $remainingSeries,
                ],
            ],
            'colors' => ['#06b6d4', '#e5e7eb'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 4,
                    'borderRadiusApplication' => 'end',
                    'columnWidth' => '55%',
                ],
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
                    'formatter' => 'function (val) { return val + " tugas" }',
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
            'states' => [
                'hover' => [
                    'filter' => [
                        'type' => 'darken',
                        'value' => 0.9,
                    ],
                ],
            ],
            'noData' => [
                'text' => 'Belum ada data tugas',
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
