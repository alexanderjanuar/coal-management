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
    protected static ?string $heading = 'Jumlah Tugas Per User';
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
        
        // Get users based on department/position filter
        $users = $this->getFilteredUsers();
        
        // Get task data per user
        $taskData = $this->getTaskDataPerUser($users);
        
        // Generate teal color variations for each bar
        $tealColors = $this->generateTealColors(count($taskData['userNames']));
        
        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Tugas',
                    'data' => $taskData['taskCounts'],
                    'backgroundColor' => $tealColors['background'],
                    'borderColor' => $tealColors['border'],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $taskData['userNames'],
        ];
    }

    // Method untuk generate teal color variations
    protected function generateTealColors(int $count): array
    {
        $backgroundColors = [];
        $borderColors = [];
        
        // Base teal colors dengan variasi saturation dan lightness
        $tealVariations = [
            ['bg' => 'rgba(20, 184, 166, 0.8)', 'border' => 'rgb(20, 184, 166)'],   // teal-500
            ['bg' => 'rgba(45, 212, 191, 0.8)', 'border' => 'rgb(45, 212, 191)'],   // teal-400
            ['bg' => 'rgba(94, 234, 212, 0.8)', 'border' => 'rgb(94, 234, 212)'],   // teal-300
            ['bg' => 'rgba(153, 246, 228, 0.8)', 'border' => 'rgb(153, 246, 228)'], // teal-200
            ['bg' => 'rgba(13, 148, 136, 0.8)', 'border' => 'rgb(13, 148, 136)'],   // teal-600
            ['bg' => 'rgba(15, 118, 110, 0.8)', 'border' => 'rgb(15, 118, 110)'],   // teal-700
            ['bg' => 'rgba(17, 94, 89, 0.8)', 'border' => 'rgb(17, 94, 89)'],       // teal-800
            ['bg' => 'rgba(19, 78, 74, 0.8)', 'border' => 'rgb(19, 78, 74)'],       // teal-900
            ['bg' => 'rgba(134, 239, 172, 0.8)', 'border' => 'rgb(134, 239, 172)'], // teal-green mix
            ['bg' => 'rgba(103, 232, 249, 0.8)', 'border' => 'rgb(103, 232, 249)'], // teal-cyan mix
        ];
        
        for ($i = 0; $i < $count; $i++) {
            $colorIndex = $i % count($tealVariations);
            $backgroundColors[] = $tealVariations[$colorIndex]['bg'];
            $borderColors[] = $tealVariations[$colorIndex]['border'];
        }
        
        return [
            'background' => $backgroundColors,
            'border' => $borderColors,
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
            'plugins' => [
                'legend' => [
                    'display' => false, // Hide legend karena hanya 1 dataset
                ],
            ],
        ];
    }

    // Method untuk mendapatkan users yang sudah difilter
    protected function getFilteredUsers()
    {
        $query = User::query();

        // Apply department filter
        if ($this->department) {
            $query->where('department', $this->department);
        }

        // Apply position filter
        if ($this->position) {
            $query->where('position', $this->position);
        }

        // Only get users who have task assignments - gunakan relationship yang benar
        $query->whereHas('dailyTaskAssignments', function ($taskQuery) {
            // Pastikan task ada
            $taskQuery->whereNotNull('id');
        });

        return $query->orderBy('name')->get();
    }

    // Method untuk mendapatkan task data per user
    protected function getTaskDataPerUser($users): array
    {
        $userData = [];

        foreach ($users as $user) {
            // Get total task count for this user dengan date filter
            $totalTasks = DailyTask::whereHas('assignedUsers', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->whereBetween('task_date', [
                $this->fromDate->format('Y-m-d'),
                $this->toDate->format('Y-m-d')
            ])
            ->count();

            $userData[] = [
                'name' => $user->name,
                'count' => $totalTasks
            ];
        }

        // Sort by task count (descending - highest first)
        usort($userData, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        // Extract sorted names and counts
        $userNames = array_column($userData, 'name');
        $taskCounts = array_column($userData, 'count');

        // If no users found, show empty state
        if (empty($userNames)) {
            return [
                'userNames' => ['Tidak ada data'],
                'taskCounts' => [0],
            ];
        }

        return [
            'userNames' => $userNames,
            'taskCounts' => $taskCounts,
        ];
    }

    // Method untuk mendapatkan deskripsi berdasarkan filter aktif
    public function getDescription(): ?string
    {
        $totalTasks = $this->getTotalTasks();
        $totalUsers = $this->getTotalUsers();
        
        $filterDesc = [];
        if ($this->department) {
            $filterDesc[] = "Department: {$this->department}";
        }
        if ($this->position) {
            $filterDesc[] = "Position: {$this->position}";
        }
        
        $filterText = !empty($filterDesc) ? ' (' . implode(', ', $filterDesc) . ')' : '';
        
        return "Total {$totalTasks} tugas untuk {$totalUsers} users{$filterText}";
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

    // Method untuk mendapatkan total users
    protected function getTotalUsers(): int
    {
        $query = User::query();

        if ($this->department) {
            $query->where('department', $this->department);
        }
        if ($this->position) {
            $query->where('position', $this->position);
        }

        // Gunakan relationship yang benar
        $query->whereHas('dailyTaskAssignments');

        return $query->count();
    }

    // Method untuk debugging - bisa dihapus di production
    public function getCurrentFilters(): array
    {
        return [
            'department' => $this->department,
            'position' => $this->position,
            'total_users' => $this->getTotalUsers(),
            'total_tasks' => $this->getTotalTasks(),
        ];
    }
}