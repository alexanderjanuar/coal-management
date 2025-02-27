<?php

namespace App\Livewire\Widget;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Project;
use App\Models\RequiredDocument;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProjectsStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        // Get base query for projects
        $baseQuery = Project::query();

        // Filter for non-admin users
        if (!auth()->user()->hasRole('super-admin')) {
            $baseQuery->whereIn('client_id', function ($query) {
                $query->select('client_id')
                    ->from('user_clients')
                    ->where('user_id', auth()->id());
            });
        }

        // Get monthly data for the last 6 months
        $monthlyData = $baseQuery->select([
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as active'),
            DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed'),
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month_year')
        ])
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->orderBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))  // Fixed this line
            ->get();

        // Get pending documents count
        $pendingDocsQuery = RequiredDocument::query()
            ->whereHas('projectStep.project', function ($query) {
                if (!auth()->user()->hasRole('super-admin')) {
                    $query->whereIn('client_id', function ($subQuery) {
                        $subQuery->select('client_id')
                            ->from('user_clients')
                            ->where('user_id', auth()->id());
                    });
                }
            })
            ->where('status', 'pending_review')
            ->select([
                DB::raw('COUNT(*) as pending'),
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month_year')
            ])
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->orderBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))  // Fixed this line
            ->get();

        // Current month totals
        $currentTotal = $baseQuery->count();
        $currentActive = $baseQuery->clone()->where('status', 'in_progress')->count();
        $currentCompleted = $baseQuery->clone()->where('status', 'completed')->count();
        $currentPending = RequiredDocument::where('status', 'pending_review')->count();

        // Last month totals
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthTotal = $baseQuery->clone()->where('created_at', '<', now()->startOfMonth())->count();
        $lastMonthActive = $baseQuery->clone()->where('status', 'in_progress')->where('created_at', '<', now()->startOfMonth())->count();
        $lastMonthCompleted = $baseQuery->clone()->where('status', 'completed')->where('created_at', '<', now()->startOfMonth())->count();
        $lastMonthPending = RequiredDocument::where('status', 'pending_review')->where('created_at', '<', now()->startOfMonth())->count();

        // Calculate percentage changes
        $totalChange = $this->calculatePercentageChange($currentTotal, $lastMonthTotal);
        $activeChange = $this->calculatePercentageChange($currentActive, $lastMonthActive);
        $completedChange = $this->calculatePercentageChange($currentCompleted, $lastMonthCompleted);
        $pendingChange = $this->calculatePercentageChange($currentPending, $lastMonthPending);

        // Get chart data for last 6 months
        $chartData = collect(range(5, 0))
            ->map(fn($i) => now()->subMonths($i)->format('Y-m'))
            ->map(function ($monthYear) use ($monthlyData, $pendingDocsQuery) {
                return [
                    'total' => $monthlyData->firstWhere('month_year', $monthYear)?->total ?? 0,
                    'active' => $monthlyData->firstWhere('month_year', $monthYear)?->active ?? 0,
                    'completed' => $monthlyData->firstWhere('month_year', $monthYear)?->completed ?? 0,
                    'pending' => $pendingDocsQuery->firstWhere('month_year', $monthYear)?->pending ?? 0,
                ];
            });

        return [
            Stat::make('Total Projects', (string) $currentTotal)
                ->description($totalChange . '% vs last month')
                ->descriptionIcon($totalChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($totalChange >= 0 ? 'warning' : 'danger')
                ->icon('heroicon-o-document-text')
                ->chart($chartData->pluck('total')->toArray()),

            Stat::make('Active Projects', (string) $currentActive)
                ->description($activeChange . '% vs last month')
                ->descriptionIcon($activeChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($activeChange >= 0 ? 'warning' : 'danger')
                ->icon('heroicon-o-play')
                ->chart($chartData->pluck('active')->toArray()),

            Stat::make('Completed', (string) $currentCompleted)
                ->description($completedChange . '% vs last month')
                ->descriptionIcon($completedChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($completedChange >= 0 ? 'warning' : 'danger')
                ->icon('heroicon-o-check-circle')
                ->chart($chartData->pluck('completed')->toArray()),

            Stat::make('Pending Documents', (string) $currentPending)
                ->description($pendingChange . '% vs last month')
                ->descriptionIcon($pendingChange <= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($pendingChange <= 0 ? 'warning' : 'danger')
                ->icon('heroicon-o-document')
                ->chart($chartData->pluck('pending')->toArray()),
        ];
    }

    private function calculatePercentageChange($current, $previous): float
    {
        if ($previous == 0)
            return 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }
}
