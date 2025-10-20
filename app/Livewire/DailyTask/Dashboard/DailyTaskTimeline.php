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
    
    // Tambahkan properti untuk mengatur ukuran chart
    protected static ?string $maxHeight = '450px'; // Batasi tinggi maksimal
    
    // Filter properties
    public ?string $filter = 'today';
    public Carbon $fromDate;
    public Carbon $toDate;
    public ?string $department = null;
    public ?string $position = null;

    public function mount(): void
    {
        $this->fromDate = now()->startOfDay();
        $this->toDate = now()->endOfDay();
        $this->filter = 'today';
    }

    #[On('filtersUpdated')]
    public function updateFilters(array $filters): void
    {
        $this->filter = $filters['date_range'] ?? 'today';
        $this->fromDate = Carbon::parse($filters['from'])->startOfDay();
        $this->toDate = Carbon::parse($filters['to'])->endOfDay();
        $this->department = $filters['department'] ?? null;
        $this->position = $filters['position'] ?? null;
        
        $this->cachedData = null;
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

    protected function getData(): array
    {
        $this->cachedData = null;
        $users = $this->getFilteredUsers();
        $taskData = $this->getTaskDataPerUser($users);
        
        return [
            'datasets' => [
                [
                    'label' => 'Selesai',
                    'data' => $taskData['completed'],
                    'backgroundColor' => 'rgba(34, 197, 94, 0.85)', // green-500 - success
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'On Progress',
                    'data' => $taskData['in_progress'],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.85)', // blue-500 - active
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Pending',
                    'data' => $taskData['pending'],
                    'backgroundColor' => 'rgba(251, 146, 60, 0.85)', // orange-400 - pending
                    'borderColor' => 'rgb(251, 146, 60)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'On Hold',
                    'data' => $taskData['cancelled'],
                    'backgroundColor' => 'rgba(148, 163, 184, 0.75)', // slate-400 - cancelled
                    'borderColor' => 'rgb(148, 163, 184)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $taskData['userNames'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive'=> true,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'stacked' => true,
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                        'font' => [
                            'size' => 11,
                        ],
                    ],
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.05)',
                        'drawBorder' => false,
                    ],
                ],
                'x' => [
                    'stacked' => true,
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

    protected function getFilteredUsers()
    {
        $query = User::query();

        if ($this->department) {
            $query->where('department', $this->department);
        }

        if ($this->position) {
            $query->where('position', $this->position);
        }

        // Hanya ambil user yang memiliki task dalam rentang tanggal
        $query->whereHas('dailyTaskAssignments', function ($taskQuery) {
            $taskQuery->whereHas('dailyTask', function ($dailyTaskQuery) {
                $dailyTaskQuery->whereBetween('task_date', [
                    $this->fromDate->format('Y-m-d'),
                    $this->toDate->format('Y-m-d')
                ]);
            });
        });

        return $query->orderBy('name')->get();
    }

    protected function getTaskDataPerUser($users): array
    {
        $userData = [];

        foreach ($users as $user) {
            $taskCounts = DailyTask::whereHas('assignedUsers', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->whereBetween('task_date', [
                $this->fromDate->format('Y-m-d'),
                $this->toDate->format('Y-m-d')
            ])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

            $totalTasks = array_sum($taskCounts);
            
            // Hanya tambahkan user yang memiliki task
            if ($totalTasks > 0) {
                $userData[] = [
                    'name' => $user->name,
                    'completed' => $taskCounts['completed'] ?? 0,
                    'in_progress' => $taskCounts['in_progress'] ?? 0,
                    'pending' => $taskCounts['pending'] ?? 0,
                    'cancelled' => $taskCounts['cancelled'] ?? 0,
                    'total' => $totalTasks
                ];
            }
        }

        // Sort berdasarkan total task terbanyak
        usort($userData, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        // Jika tidak ada data, tampilkan pesan no data
        if (empty($userData)) {
            return [
                'userNames' => ['Tidak ada tugas'],
                'completed' => [0],
                'in_progress' => [0],
                'pending' => [0],
                'cancelled' => [0],
            ];
        }

        $userNames = array_column($userData, 'name');
        $completedData = array_column($userData, 'completed');
        $inProgressData = array_column($userData, 'in_progress');
        $pendingData = array_column($userData, 'pending');
        $cancelledData = array_column($userData, 'cancelled');

        return [
            'userNames' => $userNames,
            'completed' => $completedData,
            'in_progress' => $inProgressData,
            'pending' => $pendingData,
            'cancelled' => $cancelledData,
        ];
    }

    public function getDescription(): ?string
    {
        $totalTasks = $this->getTotalTasks();
        $totalUsers = $this->getTotalActiveUsers(); // Gunakan method baru
        
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

    // Method baru untuk menghitung user yang aktif memiliki task
    protected function getTotalActiveUsers(): int
    {
        $query = User::query();

        if ($this->department) {
            $query->where('department', $this->department);
        }
        if ($this->position) {
            $query->where('position', $this->position);
        }

        // Hanya hitung user yang memiliki task dalam rentang tanggal
        $query->whereHas('dailyTaskAssignments', function ($taskQuery) {
            $taskQuery->whereHas('dailyTask', function ($dailyTaskQuery) {
                $dailyTaskQuery->whereBetween('task_date', [
                    $this->fromDate->format('Y-m-d'),
                    $this->toDate->format('Y-m-d')
                ]);
            });
        });

        return $query->count();
    }

    // Tetap pertahankan method getTotalUsers untuk backward compatibility
    protected function getTotalUsers(): int
    {
        return $this->getTotalActiveUsers();
    }

    public function getCurrentFilters(): array
    {
        return [
            'department' => $this->department,
            'position' => $this->position,
            'total_users' => $this->getTotalActiveUsers(),
            'total_tasks' => $this->getTotalTasks(),
        ];
    }
}