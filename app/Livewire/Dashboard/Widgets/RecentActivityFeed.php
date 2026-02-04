<?php

namespace App\Livewire\Dashboard\Widgets;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use App\Models\UserActivity;
use App\Models\User;
use Carbon\Carbon;

class RecentActivityFeed extends Component
{
    #[Url]
    public string $activeTab = 'activity';

    public string $dateFilter = 'today';
    public ?string $userFilter = null;
    public string $search = '';
    public int $limit = 20;
    public bool $isLive = true;

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function setDateFilter(string $filter): void
    {
        $this->dateFilter = $filter;
        $this->resetPage();
    }

    public function loadMore(): void
    {
        $this->limit += 15;
    }

    public function resetPage(): void
    {
        $this->limit = 20;
    }

    public function toggleLive(): void
    {
        $this->isLive = !$this->isLive;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedUserFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function users()
    {
        return User::orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function activities()
    {
        $query = $this->getBaseQuery()
            ->with(['user', 'client', 'project'])
            ->latest()
            ->limit($this->limit);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', "%{$this->search}%")
                  ->orWhere('action', 'like', "%{$this->search}%")
                  ->orWhereHas('user', function ($uq) {
                      $uq->where('name', 'like', "%{$this->search}%");
                  })
                  ->orWhereHas('client', function ($cq) {
                      $cq->where('name', 'like', "%{$this->search}%");
                  });
            });
        }

        return $query->get();
    }

    #[Computed]
    public function totalCount(): int
    {
        return $this->getBaseQuery()->count();
    }

    #[Computed]
    public function statisticsData()
    {
        $baseQuery = $this->getBaseQuery();

        $userStats = (clone $baseQuery)
            ->selectRaw('user_id, COUNT(*) as total_activities')
            ->groupBy('user_id')
            ->with('user:id,name')
            ->orderByDesc('total_activities')
            ->limit(10)
            ->get();

        $actionStats = (clone $baseQuery)
            ->selectRaw("
                CASE
                    WHEN action LIKE '%created%' THEN 'Created'
                    WHEN action LIKE '%updated%' THEN 'Updated'
                    WHEN action LIKE '%uploaded%' THEN 'Uploaded'
                    WHEN action LIKE '%approved%' THEN 'Approved'
                    WHEN action LIKE '%rejected%' THEN 'Rejected'
                    WHEN action LIKE '%submitted%' THEN 'Submitted'
                    WHEN action LIKE '%completed%' THEN 'Completed'
                    WHEN action LIKE '%deleted%' THEN 'Deleted'
                    ELSE 'Other'
                END as action_type,
                COUNT(*) as count
            ")
            ->groupBy('action_type')
            ->orderByDesc('count')
            ->get();

        $totalActivities = (clone $baseQuery)->count();

        return [
            'user_stats' => $userStats,
            'action_stats' => $actionStats,
            'total' => $totalActivities,
        ];
    }

    #[Computed]
    public function latestTimestamp(): ?string
    {
        $latest = $this->getBaseQuery()->latest()->first();
        return $latest?->created_at?->toIso8601String();
    }

    private function getBaseQuery()
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super-admin') || $user->hasRole('director');

        $query = UserActivity::query();

        $query = match($this->dateFilter) {
            'today' => $query->whereDate('created_at', today()),
            'yesterday' => $query->whereDate('created_at', today()->subDay()),
            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            default => $query,
        };

        if ($this->userFilter) {
            $query->where('user_id', $this->userFilter);
        }

        if (!$isSuperAdmin) {
            $clientIds = $user->userClients()->pluck('client_id')->toArray();

            if (!empty($clientIds)) {
                $query->where(function ($q) use ($clientIds, $user) {
                    $q->whereIn('client_id', $clientIds)
                      ->orWhere('user_id', $user->id);
                });
            } else {
                $query->where('user_id', $user->id);
            }
        }

        return $query;
    }

    public function getActionColor(string $action): string
    {
        return match(true) {
            str_contains($action, 'created') => 'bg-emerald-500',
            str_contains($action, 'updated') => 'bg-blue-500',
            str_contains($action, 'deleted') => 'bg-red-500',
            str_contains($action, 'uploaded') => 'bg-violet-500',
            str_contains($action, 'approved') => 'bg-green-500',
            str_contains($action, 'rejected') => 'bg-rose-500',
            str_contains($action, 'submitted') => 'bg-amber-500',
            str_contains($action, 'completed') => 'bg-teal-500',
            default => 'bg-gray-400',
        };
    }

    public function getActionDotColor(string $action): string
    {
        return match(true) {
            str_contains($action, 'created') => 'bg-emerald-500',
            str_contains($action, 'updated') => 'bg-blue-500',
            str_contains($action, 'deleted') => 'bg-red-500',
            str_contains($action, 'uploaded') => 'bg-violet-500',
            str_contains($action, 'approved') => 'bg-green-500',
            str_contains($action, 'rejected') => 'bg-rose-500',
            str_contains($action, 'submitted') => 'bg-amber-500',
            str_contains($action, 'completed') => 'bg-teal-500',
            default => 'bg-gray-400',
        };
    }

    public function getActionBadgeColor(string $actionType): string
    {
        return match($actionType) {
            'Created' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            'Updated' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            'Deleted' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            'Uploaded' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
            'Approved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            'Rejected' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
            'Submitted' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            'Completed' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
        };
    }

    public function getMonoBadgeStyle(string $actionType): string
    {
        return match($actionType) {
            'Created' => 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900',
            'Updated' => 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
            'Deleted' => 'bg-gray-800 text-gray-100 dark:bg-gray-200 dark:text-gray-800',
            'Uploaded' => 'bg-gray-300 text-gray-800 dark:bg-gray-600 dark:text-gray-200',
            'Approved' => 'bg-gray-700 text-gray-100 dark:bg-gray-300 dark:text-gray-800',
            'Rejected' => 'bg-gray-500 text-white dark:bg-gray-400 dark:text-gray-900',
            'Submitted' => 'bg-gray-400 text-gray-900 dark:bg-gray-500 dark:text-gray-100',
            'Completed' => 'bg-gray-600 text-gray-100 dark:bg-gray-400 dark:text-gray-900',
            default => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
        };
    }

    public function getActionLabel(string $action): string
    {
        return match(true) {
            str_contains($action, 'created') => 'Created',
            str_contains($action, 'updated') => 'Updated',
            str_contains($action, 'deleted') => 'Deleted',
            str_contains($action, 'uploaded') => 'Uploaded',
            str_contains($action, 'approved') => 'Approved',
            str_contains($action, 'rejected') => 'Rejected',
            str_contains($action, 'submitted') => 'Submitted',
            str_contains($action, 'completed') => 'Completed',
            default => 'Activity',
        };
    }

    public function translateActionLabel(string $label): string
    {
        return match($label) {
            'Created' => 'Dibuat',
            'Updated' => 'Diperbarui',
            'Deleted' => 'Dihapus',
            'Uploaded' => 'Diunggah',
            'Approved' => 'Disetujui',
            'Rejected' => 'Ditolak',
            'Submitted' => 'Dikirim',
            'Completed' => 'Selesai',
            'Other' => 'Lainnya',
            default => 'Aktivitas',
        };
    }

    public function getActionIcon(string $action): string
    {
        return match(true) {
            str_contains($action, 'created') => 'M12 4.5v15m7.5-7.5h-15',
            str_contains($action, 'updated') => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182',
            str_contains($action, 'deleted') => 'm14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0',
            str_contains($action, 'uploaded') => 'M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5',
            str_contains($action, 'approved') => 'm4.5 12.75 6 6 9-13.5',
            str_contains($action, 'rejected') => 'M6 18 18 6M6 6l12 12',
            str_contains($action, 'submitted') => 'M6 12 3.75 4.5 6.75 3h13.5A2.25 2.25 0 0 1 22.5 5.25v13.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 18.75V8.25A2.25 2.25 0 0 1 6.75 6H9',
            str_contains($action, 'completed') => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            default => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        };
    }

    public function isRecent(string $createdAt): bool
    {
        return Carbon::parse($createdAt)->isAfter(now()->subMinutes(5));
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.recent-activity-feed');
    }
}
