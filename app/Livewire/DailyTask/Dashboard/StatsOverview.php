<?php

namespace App\Livewire\DailyTask\Dashboard;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\DailyTask;
use App\Models\DailyTaskAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 2; // Tampil setelah filter

    // Filter properties
    public Carbon $fromDate;
    public Carbon $toDate;
    public ?string $dateRange = 'today';
    public ?string $department = null;
    public ?string $position = null;

    public function mount(): void
    {
        // Set default date range
        $this->fromDate = now()->startOfDay();
        $this->toDate = now()->endOfDay();
    }

    // Event Listeners - Simplify jadi satu event utama
    #[On('filtersUpdated')]
    public function updateFilters(array $filters): void
    {
 
        $this->dateRange = $filters['date_range'];
        $this->fromDate = Carbon::parse($filters['from'])->startOfDay();
        $this->toDate = Carbon::parse($filters['to'])->endOfDay();
        $this->department = $filters['department'];
        $this->position = $filters['position'];
        
        // Clear cache when filters change
        $this->clearStatsCache();
        
        // Force refresh widget
        $this->dispatch('$refresh');
    }

    protected function getStats(): array
    {
        // Base query dengan filter tanggal
        $baseQuery = DailyTask::whereBetween('task_date', [
            $this->fromDate->format('Y-m-d'),
            $this->toDate->format('Y-m-d')
        ]);

        // Apply user filters (department/position) melalui assignments
        if ($this->department || $this->position) {
            $baseQuery->whereHas('assignments.user', function ($query) {
                if ($this->department) {
                    $query->where('department', $this->department);
                }
                if ($this->position) {
                    $query->where('position', $this->position);
                }
            });
        }

        // Optimized query untuk mendapatkan semua statistik
        $taskStats = $baseQuery->select([
            DB::raw('COUNT(*) as total_tasks'),
            DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_tasks'),
            DB::raw('SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress_tasks'),
            DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_tasks'),
            DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_tasks'),
            DB::raw('SUM(CASE WHEN task_date < CURDATE() AND status NOT IN ("completed", "cancelled") THEN 1 ELSE 0 END) as overdue_tasks')
        ])->first();

        // Query untuk task yang assigned ke user yang sedang login (dengan filter)
        $myTaskStats = null;
        if (auth()->check()) {
            $myTaskQuery = DailyTask::whereHas('assignments', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->whereBetween('task_date', [
                $this->fromDate->format('Y-m-d'),
                $this->toDate->format('Y-m-d')
            ]);

            $myTaskStats = $myTaskQuery->select([
                DB::raw('COUNT(*) as my_total_tasks'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as my_pending_tasks'),
                DB::raw('SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as my_in_progress_tasks'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as my_completed_tasks')
            ])->first();
        }

        // Hitung completion rate
        $completionRate = $taskStats->total_tasks > 0 
            ? round(($taskStats->completed_tasks / $taskStats->total_tasks) * 100, 1)
            : 0;

        // Generate period description
        $periodDesc = $this->getPeriodDescription();

        return [
            // Card 1: Total Task dalam periode
            Stat::make('Total Task', $taskStats->total_tasks)
                ->description($periodDesc)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($taskStats->total_tasks > 0 ? 'primary' : 'gray')
                ->chart([
                    $taskStats->completed_tasks,
                    $taskStats->in_progress_tasks,
                    $taskStats->pending_tasks,
                    $taskStats->cancelled_tasks
                ]),

            // Card 2: Task Saya (Personal) dalam periode
            Stat::make('Task Saya', $myTaskStats ? $myTaskStats->my_total_tasks : 0)
                ->description(
                    $myTaskStats 
                        ? $myTaskStats->my_pending_tasks . ' pending, ' . $myTaskStats->my_in_progress_tasks . ' dikerjakan'
                        : 'Tidak ada task dalam periode ini'
                )
                ->descriptionIcon('heroicon-m-user')
                ->color($myTaskStats && $myTaskStats->my_pending_tasks > 5 ? 'warning' : 'success')
                ->chart($myTaskStats ? [
                    $myTaskStats->my_completed_tasks,
                    $myTaskStats->my_in_progress_tasks,
                    $myTaskStats->my_pending_tasks
                ] : [0]),

            // Card 3: Task Tertunda (Overdue)
            Stat::make('Task Tertunda', $taskStats->overdue_tasks)
                ->description(
                    $taskStats->overdue_tasks > 0 
                        ? 'Melewati deadline dalam periode ini' 
                        : 'Tidak ada task yang tertunda'
                )
                ->descriptionIcon($taskStats->overdue_tasks > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-badge')
                ->color($taskStats->overdue_tasks > 0 ? 'danger' : 'success')
                ->chart($taskStats->overdue_tasks > 0 ? [
                    $taskStats->overdue_tasks,
                    max(1, $taskStats->total_tasks - $taskStats->overdue_tasks)
                ] : [0, 1]),

            // Card 4: Tingkat Penyelesaian dalam periode
            Stat::make('Completion Rate', $completionRate . '%')
                ->description(
                    $taskStats->completed_tasks . ' dari ' . $taskStats->total_tasks . ' task selesai'
                )
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($completionRate >= 80 ? 'success' : ($completionRate >= 60 ? 'warning' : 'danger'))
                ->chart([
                    $taskStats->completed_tasks,
                    $taskStats->in_progress_tasks,
                    $taskStats->pending_tasks,
                    $taskStats->cancelled_tasks
                ]),
        ];
    }

    protected function getPeriodDescription(): string
    {
        return match($this->dateRange) {
            'today' => 'Hari ini',
            'yesterday' => 'Kemarin',
            'this_week' => 'Minggu ini',
            'last_week' => 'Minggu lalu',
            'this_month' => 'Bulan ini',
            'last_month' => 'Bulan lalu',
            'this_year' => 'Tahun ini',
            'custom' => $this->fromDate->format('d M') . ' - ' . $this->toDate->format('d M Y'),
            default => 'Periode yang dipilih'
        };
    }

    protected function clearStatsCache(): void
    {
        $cacheKey = $this->getCacheKey();
        cache()->forget($cacheKey);
    }

    /**
     * Refresh widget data setiap 30 detik
     */
    protected function getPollingInterval(): ?string
    {
        return '30s';
    }

    /**
     * Cache key berdasarkan filter yang aktif
     */
    protected function getCacheKey(): string
    {
        $filterHash = md5(serialize([
            'user_id' => auth()->id(),
            'from' => $this->fromDate->format('Y-m-d'),
            'to' => $this->toDate->format('Y-m-d'),
            'department' => $this->department,
            'position' => $this->position,
        ]));
        
        return "daily-task-stats-{$filterHash}-" . now()->format('Y-m-d-H-i');
    }
}