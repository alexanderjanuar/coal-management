<?php

namespace App\Livewire\DailyTask\Dashboard;

use Filament\Widgets\ChartWidget;
use App\Models\DailyTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Illuminate\Support\Carbon;

class DailyTaskStatus extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Status Tugas';
    protected static ?int $sort = 3;

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

    // Optional: Listen to specific date range updates
    #[On('updateDateRange')]
    public function updateDateRange(string $range, string $from, string $to): void
    {
        $this->filter = $range;
        $this->fromDate = Carbon::parse($from)->startOfDay();
        $this->toDate = Carbon::parse($to)->endOfDay();
        
        $this->cachedData = null;
        $this->skipRender = false;
    }

    // Optional: Listen to department filter updates
    #[On('updateDepartment')]
    public function updateDepartment(?string $department): void
    {
        $this->department = $department;
        $this->cachedData = null;
        $this->skipRender = false;
    }

    // Optional: Listen to position filter updates
    #[On('updatePosition')]
    public function updatePosition(?string $position): void
    {
        $this->position = $position;
        $this->cachedData = null;
        $this->skipRender = false;
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
                        'rgba(13, 148, 136, 0.9)',   // Selesai - teal-600
                        'rgba(20, 184, 166, 0.8)',   // Sedang Dikerjakan - teal-500
                        'rgba(45, 212, 191, 0.7)',   // Tertunda - teal-400
                        'rgba(153, 246, 228, 0.6)',  // Dibatalkan - teal-200
                    ],
                    'borderColor' => [
                        'rgb(13, 148, 136)',
                        'rgb(20, 184, 166)',
                        'rgb(45, 212, 191)',
                        'rgb(153, 246, 228)',
                    ],
                    'borderWidth' => 2,
                    'hoverBorderWidth' => 3,
                    'hoverOffset' => 10,
                ],
            ],
            'labels' => $statusData['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'stacked' => true,
                    'display' => false, // Hide Y-axis completely
                    'grid' => [
                        'display' => false, // Hide Y-axis grid
                    ],
                ],
                'x' => [
                    'stacked' => true,
                    'display' => false, // Hide X-axis completely
                    'grid' => [
                        'display' => false, // Hide X-axis grid
                    ],
                ],
            ],
            'cutout' => 0, // 0 untuk pie chart penuh, bisa diubah ke 50 untuk donut
            'animation' => [
                'duration' => 1500,
                'animateRotate' => true,
                'animateScale' => true,
            ],
        ];
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
            'in_progress' => 'Sedang Dikerjakan',
            'pending' => 'Tertunda',
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
                'counts' => [1], // Dummy data untuk empty state
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