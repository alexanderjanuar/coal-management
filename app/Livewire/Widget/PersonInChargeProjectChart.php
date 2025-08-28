<?php

namespace App\Livewire\Widget;

use Filament\Widgets\ChartWidget;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PersonInChargeProjectChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    public function getFilters(): ?array
    {
        return [
            'all' => 'Semua Proyek',
            'not_completed' => 'Proyek Belum Selesai',
            'completed' => 'Proyek Selesai',
        ];
    }

    protected function getData(): array
    {
        // Get PICs and count their projects using the direct pic_id relationship
        $filter = $this->filter ?? 'all';
        
        // Get PICs and count their projects using the direct pic_id relationship
        $query = User::query()
            ->join('projects', 'users.id', '=', 'projects.pic_id');

        // Apply filter based on project status
        if ($filter === 'not_completed') {
            $query->whereNotIn('projects.status', ['completed', 'completed (Not Payed Yet)']);
        } elseif ($filter === 'completed') {
            $query->whereIn('projects.status', ['completed', 'completed (Not Payed Yet)']);
        }

        $picData = $query->select('users.name', DB::raw('COUNT(projects.id) as project_count'))
            ->groupBy('users.id', 'users.name')
            ->having('project_count', '>', 0)
            ->orderBy('project_count', 'desc')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Number of Projects',
                    'data' => $picData->pluck('project_count')->toArray(),
                    'backgroundColor' => [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(199, 199, 199, 0.8)',
                        'rgba(83, 102, 255, 0.8)',
                        'rgba(255, 99, 255, 0.8)',
                        'rgba(99, 255, 132, 0.8)',
                    ],
                    'borderColor' => [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)',
                        'rgba(83, 102, 255, 1)',
                        'rgba(255, 99, 255, 1)',
                        'rgba(99, 255, 132, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $picData->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Jumlah Proyek',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Person in Charge (PIC)',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
        ];
    }
}
