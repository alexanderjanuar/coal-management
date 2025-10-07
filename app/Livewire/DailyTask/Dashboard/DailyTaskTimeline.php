<?php

namespace App\Livewire\DailyTask\Dashboard;

use Filament\Widgets\ChartWidget;
use App\Models\DailyTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Illuminate\Support\Carbon;

class DailyTaskTimeline extends ChartWidget
{
    protected static ?string $heading = 'Timeline Tugas Harian';
    protected static ?int $sort = 2;

    // Filter properties - menggunakan public agar bisa diakses dari luar
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
        // Update semua filter properties
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

    // Optional: Listen to specific date range updates
    #[On('updateDateRange')]
    public function updateDateRange(string $range, string $from, string $to): void
    {
        $this->filter = $range;
        $this->fromDate = Carbon::parse($from)->startOfDay();
        $this->toDate = Carbon::parse($to)->endOfDay();
        
        $this->updateChartData();
    }

    // Optional: Listen to department filter updates
    #[On('updateDepartment')]
    public function updateDepartment(?string $department): void
    {
        $this->department = $department;
        $this->updateChartData();
    }

    // Optional: Listen to position filter updates
    #[On('updatePosition')]
    public function updatePosition(?string $position): void
    {
        $this->position = $position;
        $this->updateChartData();
    }

    // Method untuk mendapatkan filter options
    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hari Ini',
            'yesterday' => 'Kemarin',
            'this_week' => 'Minggu Ini',
            'last_week' => 'Minggu Lalu',
            'this_month' => 'Bulan Ini',
            'last_month' => 'Bulan Lalu',
            'this_year' => 'Tahun Ini',
            'custom' => 'Custom',
        ];
    }

    protected function getData(): array
    {
        // Clear any cached data
        $this->cachedData = null;
        
        // Generate date range untuk timeline
        $dates = $this->getDateRange();
        
        // Get task data grouped by date and status
        $taskData = $this->getTaskData($dates);
        
        return [
            'datasets' => [
                [
                    'label' => 'Selesai',
                    'data' => $taskData['completed'],
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                    'pointBackgroundColor' => 'rgb(34, 197, 94)',
                    'pointBorderColor' => 'rgb(255, 255, 255)',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 4,
                ],
                [
                    'label' => 'Sedang Dikerjakan',
                    'data' => $taskData['in_progress'],
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                    'pointBackgroundColor' => 'rgb(59, 130, 246)',
                    'pointBorderColor' => 'rgb(255, 255, 255)',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 4,
                ],
                [
                    'label' => 'Tertunda',
                    'data' => $taskData['pending'],
                    'borderColor' => 'rgb(251, 146, 60)',
                    'backgroundColor' => 'rgba(251, 146, 60, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                    'pointBackgroundColor' => 'rgb(251, 146, 60)',
                    'pointBorderColor' => 'rgb(255, 255, 255)',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 4,
                ],
            ],
            'labels' => $dates['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                    ],
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.1)',
                        'drawBorder' => false,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'maxRotation' => 45,
                        'minRotation' => 0,
                    ],
                ],
            ],
        ];
    }

    // Method untuk mendapatkan date range untuk timeline
    protected function getDateRange(): array
    {
        $dates = [];
        $labels = [];
        
        $startDate = $this->fromDate->copy();
        $endDate = $this->toDate->copy();
        
        // Limit to maximum 31 days untuk performance
        if ($startDate->diffInDays($endDate) > 31) {
            $startDate = $endDate->copy()->subDays(30);
        }
        
        while ($startDate->lte($endDate)) {
            $dates[] = $startDate->format('Y-m-d');
            
            // Format label berdasarkan date range
            if ($startDate->diffInDays($endDate) <= 7) {
                // Show day name untuk week view
                $labels[] = $startDate->format('D, j M');
            } elseif ($startDate->diffInDays($endDate) <= 31) {
                // Show date untuk month view
                $labels[] = $startDate->format('j M');
            } else {
                // Show month untuk year view
                $labels[] = $startDate->format('M Y');
            }
            
            $startDate->addDay();
        }
        
        return [
            'dates' => $dates,
            'labels' => $labels,
        ];
    }

    // Method untuk mendapatkan task data berdasarkan tanggal dan status
    protected function getTaskData(array $dateRange): array
    {
        // Base query dengan date filter
        $baseQuery = DailyTask::whereBetween('task_date', [
            $this->fromDate->format('Y-m-d'),
            $this->toDate->format('Y-m-d')
        ]);

        // Apply department/position filters - perbaiki relationship
        if ($this->department || $this->position) {
            $baseQuery->whereHas('assignments', function ($assignmentQuery) {
                $assignmentQuery->whereHas('user', function ($userQuery) {
                    if ($this->department) {
                        $userQuery->where('department', $this->department);
                    }
                    if ($this->position) {
                        $userQuery->where('position', $this->position);
                    }
                });
            });
        }

        // Get task counts grouped by date and status
        $taskCounts = $baseQuery
            ->select([
                DB::raw('DATE(task_date) as date'),
                'status',
                DB::raw('COUNT(*) as count')
            ])
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        // Initialize data arrays
        $completed = [];
        $inProgress = [];
        $pending = [];

        // Fill data untuk setiap date
        foreach ($dateRange['dates'] as $date) {
            $dayTasks = $taskCounts->get($date, collect());
            
            $completed[] = $dayTasks->where('status', 'completed')->sum('count');
            $inProgress[] = $dayTasks->where('status', 'in_progress')->sum('count');
            $pending[] = $dayTasks->where('status', 'pending')->sum('count');
        }

        return [
            'completed' => $completed,
            'in_progress' => $inProgress,
            'pending' => $pending,
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

        if ($this->department) {
            $query->whereHas('assignedUsers', function ($userQuery) {
                $userQuery->where('department', $this->department);
            });
        }

        if ($this->position) {
            $query->whereHas('assignedUsers', function ($userQuery) {
                $userQuery->where('position', $this->position);
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
        ];
    }
}