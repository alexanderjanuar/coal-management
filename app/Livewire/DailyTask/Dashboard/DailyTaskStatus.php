<?php

namespace App\Livewire\DailyTask\Dashboard;

use Filament\Widgets\ChartWidget;
use App\Models\DailyTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Illuminate\Support\Carbon;
use Filament\Support\RawJs;

class DailyTaskStatus extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Status Tugas';
    protected static ?int $sort = 3;
    
    // Tambahkan properti untuk mengatur ukuran chart
    protected static ?string $maxHeight = '450px';

    // Filter properties
    public ?string $filter = 'today';
    public Carbon $fromDate;
    public Carbon $toDate;
    public ?string $department = null;
    public ?string $position = null;

    public function mount(): void
    {
        // Set default values
        $this->fromDate = now()->startOfDay();
        $this->toDate = now()->endOfDay();
        $this->filter = 'today';
    }

    // Method untuk mendengarkan event filter dari widget Filters
    #[On('filtersUpdated')]
    public function updateFilters(array $filters): void
    {
        // Update semua filter properties termasuk date filter
        $this->filter = $filters['date_range'] ?? 'today';
        $this->fromDate = Carbon::parse($filters['from'])->startOfDay();
        $this->toDate = Carbon::parse($filters['to'])->endOfDay();
        $this->department = $filters['department'] ?? null;
        $this->position = $filters['position'] ?? null;
        
        // Clear cached data
        $this->cachedData = null;
        
        // Force refresh component
        $this->skipRender = false;
    }

    #[On('updateDateRange')]
    public function updateDateRange(string $range, string $from, string $to): void
    {
        $this->filter = $range;
        $this->fromDate = Carbon::parse($from)->startOfDay();
        $this->toDate = Carbon::parse($to)->endOfDay();
        
        $this->cachedData = null;
        $this->skipRender = false;
    }

    #[On('updateDepartment')]
    public function updateDepartment(?string $department): void
    {
        $this->department = $department;
        $this->cachedData = null;
        $this->skipRender = false;
    }

    #[On('updatePosition')]
    public function updatePosition(?string $position): void
    {
        $this->position = $position;
        $this->cachedData = null;
        $this->skipRender = false;
    }

    public function getTitle(): ?string
    {
        dd('123');
        return 'Status Tugas';
    }

    protected function getData(): array
    {
        // Clear any cached data
        $this->cachedData = null;
        
        // Get task status data
        $statusData = $this->getStatusData();
        
        return [
            'datasets' => [
                [
                    'data' => $statusData['counts'],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.85)',   // Selesai - green-500
                        'rgba(59, 130, 246, 0.85)',  // Sedang Dikerjakan - blue-500
                        'rgba(251, 146, 60, 0.85)',  // Tertunda - orange-400
                        'rgba(148, 163, 184, 0.75)', // Dibatalkan - slate-400
                    ],
                    'borderColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(59, 130, 246)',
                        'rgb(251, 146, 60)',
                        'rgb(148, 163, 184)',
                    ],
                    'borderWidth' => 2,
                    'hoverBorderWidth' => 3,
                    'hoverOffset' => 15,
                ],
            ],
            'labels' => $statusData['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
            {
                responsive: true,
                cutout: '65%',
                scales: {
                    y: {
                        display: false,
                        ticks: {
                            display: false,
                        },
                        grid: {
                            display: false,
                            drawBorder: false,
                        },
                    },
                    x: {
                        display: false,
                        ticks: {
                            display: false,
                        },
                        grid: {
                            display: false,
                            drawBorder: false,
                        },
                    },
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 12,
                            font: {
                                size: 11,
                            },
                            usePointStyle: true,
                            pointStyle: 'circle',
                        },
                    },
                },
                animation: {
                    duration: 800,
                    animateRotate: true,
                    animateScale: true,
                },
                onClick: function(event,elements) {
                    console.log(elements, event);
                },
                layout: {
                    padding: {
                        top: 10,
                        bottom: 10,
                        left: 10,
                        right: 10,
                    },
                },
            }
        JS);
    }

    // Method untuk mendapatkan data status dengan filter
    protected function getStatusData(): array
    {
        // Base query dengan date filter
        $query = DailyTask::whereBetween('task_date', [
            $this->fromDate->format('Y-m-d'),
            $this->toDate->format('Y-m-d')
        ]);

        // Apply department/position filters
        if ($this->department || $this->position) {
            $query->whereHas('assignedUsers', function ($userQuery) {
                if ($this->department) {
                    $userQuery->where('department', $this->department);
                }
                if ($this->position) {
                    $userQuery->where('position', $this->position);
                }
            });
        }

        // Get task counts by status
        $statusCounts = $query
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Define status labels and colors order
        $statusMapping = [
            'completed' => 'Selesai',
            'in_progress' => 'In Progress',
            'pending' => 'Pending',
            'cancelled' => 'Dibatalkan',
        ];

        $labels = [];
        $counts = [];

        // Only include statuses that have data
        foreach ($statusMapping as $status => $label) {
            $count = $statusCounts[$status] ?? 0;
            if ($count > 0) {
                $labels[] = $label;
                $counts[] = $count;
            }
        }

        // If no data found, show empty state
        if (empty($labels)) {
            return [
                'labels' => ['Tidak ada data'],
                'counts' => [1],
            ];
        }

        return [
            'labels' => $labels,
            'counts' => $counts,
        ];
    }

    // Method untuk mendapatkan deskripsi berdasarkan filter aktif
    public function getDescription(): ?string
    {
        $totalTasks = $this->getTotalTasks();
        $periodDesc = $this->getPeriodDescription();
        
        $filterDesc = [];
        if ($this->department) {
            $filterDesc[] = "Department: {$this->department}";
        }
        if ($this->position) {
            $filterDesc[] = "Position: {$this->position}";
        }
        
        $filterText = !empty($filterDesc) ? ' (' . implode(', ', $filterDesc) . ')' : '';
        
        return "Total {$totalTasks} tugas untuk {$periodDesc}{$filterText}";
    }

    // Method untuk mendapatkan total tasks
    protected function getTotalTasks(): int
    {
        $query = DailyTask::whereBetween('task_date', [
            $this->fromDate->format('Y-m-d'),
            $this->toDate->format('Y-m-d')
        ]);

        if ($this->department || $this->position) {
            $query->whereHas('assignedUsers', function ($userQuery) {
                if ($this->department) {
                    $userQuery->where('department', $this->department);
                }
                if ($this->position) {
                    $userQuery->where('position', $this->position);
                }
            });
        }

        return $query->count();
    }

    // Helper method untuk mendapatkan deskripsi periode
    protected function getPeriodDescription(): string
    {
        return match($this->filter) {
            'today' => 'hari ini',
            'yesterday' => 'kemarin', 
            'this_week' => 'minggu ini',
            'last_week' => 'minggu lalu',
            'this_month' => 'bulan ini',
            'last_month' => 'bulan lalu',
            'this_year' => 'tahun ini',
            'custom' => $this->fromDate->format('d M') . ' - ' . $this->toDate->format('d M Y'),
            default => 'periode yang dipilih'
        };
    }

    // Method untuk debugging - bisa dihapus di production
    public function getCurrentFilters(): array
    {
        return [
            'filter' => $this->filter,
            'from' => $this->fromDate->format('Y-m-d'),
            'to' => $this->toDate->format('Y-m-d'),
            'department' => $this->department,
            'position' => $this->position,
            'total_tasks' => $this->getTotalTasks(),
        ];
    }
}