<?php

namespace App\Livewire\Dashboard\Widgets;

use App\Models\DailyTask;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TaskWeeklyChart extends ChartWidget
{
    protected static ?string $heading = 'Aktivitas Tugas Mingguan';
    protected static ?int $sort = 2;
    
    protected static ?string $maxHeight = '350px';

    protected function getData(): array
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
            'datasets' => [
                [
                    'label' => 'Selesai',
                    'data' => $completedSeries,
                    'backgroundColor' => 'rgba(6, 182, 212, 0.85)', // cyan
                    'borderColor' => 'rgb(6, 182, 212)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Belum',
                    'data' => $remainingSeries,
                    'backgroundColor' => 'rgba(229, 231, 235, 0.85)', // gray
                    'borderColor' => 'rgb(229, 231, 235)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $categories,
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
                    'stacked' => true,
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
                    'stacked' => true,
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 11,
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
        return 'Penyelesaian tugas 7 hari terakhir';
    }

    public function getPollingInterval(): ?string
    {
        return '60s';
    }
}
