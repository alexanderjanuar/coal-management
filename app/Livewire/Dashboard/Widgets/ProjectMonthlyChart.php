<?php

namespace App\Livewire\Dashboard\Widgets;

use App\Models\Project;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ProjectMonthlyChart extends ChartWidget
{
    protected static ?string $heading = 'Status Proyek per PIC';
    protected static ?int $sort = 2;
    
    protected static ?string $maxHeight = '350px';
    
    public ?string $filter = 'active';

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

    protected function getData(): array
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

        // Collect unique PIC names
        $picNames = $data->pluck('pic_name')->unique()->values()->toArray();

        // Map all statuses to 3 categories
        $statusMapping = [
            'draft' => 'draft',
            'analysis' => 'in_progress',
            'in_progress' => 'in_progress',
            'review' => 'in_progress',
            'on_hold' => 'in_progress',
            'completed' => 'completed',
            'canceled' => 'completed',
        ];

        // Build data arrays for each status
        $draftData = [];
        $inProgressData = [];
        $completedData = [];

        foreach ($picNames as $pic) {
            $draftCount = 0;
            $inProgressCount = 0;
            $completedCount = 0;

            foreach ($data->where('pic_name', $pic) as $row) {
                $mapped = $statusMapping[$row->status] ?? 'in_progress';
                
                if ($mapped === 'draft') {
                    $draftCount += $row->count;
                } elseif ($mapped === 'in_progress') {
                    $inProgressCount += $row->count;
                } elseif ($mapped === 'completed') {
                    $completedCount += $row->count;
                }
            }

            $draftData[] = $draftCount;
            $inProgressData[] = $inProgressCount;
            $completedData[] = $completedCount;
        }

        // Handle empty data case
        if (empty($picNames)) {
            $picNames = ['Tidak ada data'];
            $draftData = [0];
            $inProgressData = [0];
            $completedData = [0];
        }

        // Truncate long PIC names for x-axis
        $labels = array_map(function ($name) {
            $parts = explode(' ', $name);
            return count($parts) > 1
                ? $parts[0] . ' ' . substr($parts[1], 0, 1) . '.'
                : $name;
        }, $picNames);

        return [
            'datasets' => [
                [
                    'label' => 'Draft',
                    'data' => $draftData,
                    'backgroundColor' => 'rgba(148, 163, 184, 0.85)', // gray
                    'borderColor' => 'rgb(148, 163, 184)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Berjalan',
                    'data' => $inProgressData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.85)', // blue
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Selesai',
                    'data' => $completedData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.85)', // green
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'stacked' => false,
                    'ticks' => [
                        'stepSize' => 2,
                        'precision' => 0,
                        'font' => [
                            'size' => 11,
                        ],
                    ],
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0, 0, 0, 0.03)',
                        'drawBorder' => false,
                    ],
                ],
                'x' => [
                    'stacked' => false,
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'maxRotation' => 45,
                        'minRotation' => 0,
                        'font' => [
                            'size' => 10,
                        ],
                    ],
                ],
            ],
            'animation' => [
                'duration' => 800,
                'animateRotate' => true,
                'animateScale' => true,
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'labels' => [
                        'font' => [
                            'size' => 11,
                        ],
                        'padding' => 15,
                    ],
                ],
            ],
        ];
    }

    public function getDescription(): ?string
    {
        $query = $this->baseProjectQuery();
        
        if ($this->filter === 'active') {
            $totalProjects = $query->whereNotIn('status', ['completed', 'canceled'])->count();
            return "Total {$totalProjects} proyek aktif";
        }

        $totalProjects = $query->count();
        return "Total {$totalProjects} proyek";
    }

    public function getPollingInterval(): ?string
    {
        return '60s';
    }
}
