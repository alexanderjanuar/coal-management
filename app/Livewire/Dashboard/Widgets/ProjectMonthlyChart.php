<?php

namespace App\Livewire\Dashboard\Widgets;

use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ProjectMonthlyChart extends ApexChartWidget
{
    protected static ?string $chartId = 'projectPicStatusChart';
    protected static ?string $heading = 'Status Proyek per PIC';
    protected static ?string $subheading = 'Distribusi status proyek berdasarkan penanggung jawab';

    public ?string $filter = 'active';

    protected static ?int $contentHeight = 280;

    protected function getFilters(): ?array
    {
        return [
            'active' => 'Proyek Aktif',
            'all' => 'Semua Proyek',
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
        $query = $this->baseProjectQuery()
            ->whereNotNull('pic_id')
            ->join('users', 'projects.pic_id', '=', 'users.id')
            ->select(
                'users.name as pic_name',
                'projects.status',
                DB::raw('COUNT(*) as count')
            );

        if ($this->filter === 'active') {
            $query->whereNotIn('projects.status', ['completed', 'canceled']);
        }

        $data = $query
            ->groupBy('users.name', 'projects.status')
            ->orderBy('users.name')
            ->get();

        // Collect unique PIC names and build series
        $picNames = $data->pluck('pic_name')->unique()->values()->toArray();

        $statusConfig = [
            'draft'       => ['label' => 'Draft',    'color' => '#94a3b8'],
            'analysis'    => ['label' => 'Analisis',  'color' => '#06b6d4'],
            'in_progress' => ['label' => 'Berjalan',  'color' => '#3b82f6'],
            'review'      => ['label' => 'Review',    'color' => '#f59e0b'],
            'completed'   => ['label' => 'Selesai',   'color' => '#22c55e'],
            'canceled'    => ['label' => 'Dibatalkan', 'color' => '#ef4444'],
        ];

        // Filter out statuses with 0 total across all PICs
        $activeStatuses = $data->pluck('status')->unique()->values()->toArray();

        $series = [];
        $colors = [];

        foreach ($statusConfig as $status => $config) {
            if (!in_array($status, $activeStatuses)) {
                continue;
            }

            $seriesData = [];
            foreach ($picNames as $pic) {
                $row = $data->where('pic_name', $pic)->where('status', $status)->first();
                $seriesData[] = $row ? $row->count : 0;
            }

            $series[] = [
                'name' => $config['label'],
                'data' => $seriesData,
            ];
            $colors[] = $config['color'];
        }

        // Truncate long PIC names for x-axis
        $categories = array_map(function ($name) {
            $parts = explode(' ', $name);
            return count($parts) > 1
                ? $parts[0] . ' ' . substr($parts[1], 0, 1) . '.'
                : $name;
        }, $picNames);

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 280,
                'stacked' => false,
                'toolbar' => ['show' => false],
                'background' => 'transparent',
                'animations' => [
                    'enabled' => true,
                    'speed' => 400,
                ],
                'fontFamily' => 'inherit',
            ],
            'series' => $series,
            'colors' => $colors,
            'plotOptions' => [
                'bar' => [
                    'horizontal' => false,
                    'borderRadius' => 3,
                    'borderRadiusApplication' => 'end',
                    'columnWidth' => count($picNames) <= 3 ? '45%' : '70%',
                    'dataLabels' => ['position' => 'top'],
                ],
            ],
            'grid' => [
                'show' => true,
                'borderColor' => '#e5e7eb',
                'strokeDashArray' => 4,
                'padding' => ['left' => 4, 'right' => 4],
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
                    'rotate' => count($picNames) > 6 ? -45 : 0,
                    'trim' => true,
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
                    'formatter' => 'function (val) { return val + " proyek" }',
                ],
            ],
            'legend' => [
                'show' => true,
                'position' => 'top',
                'horizontalAlign' => 'right',
                'fontFamily' => 'inherit',
                'fontSize' => '11px',
                'markers' => [
                    'size' => 4,
                    'shape' => 'circle',
                ],
                'itemMargin' => ['horizontal' => 8],
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
