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

        // Create a CLONE for monthly data so it doesn't modify the original
        $monthlyData = $baseQuery->clone()->select([
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as active'),
            DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed'),
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month_year')
        ])
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->orderBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->get();

        // Get pending documents count for charts (use separate query)
        $pendingDocsBaseQuery = RequiredDocument::query()
            ->whereHas('projectStep.project', function ($query) {
                if (!auth()->user()->hasRole('super-admin')) {
                    $query->whereIn('client_id', function ($subQuery) {
                        $subQuery->select('client_id')
                            ->from('user_clients')
                            ->where('user_id', auth()->id());
                    });
                }
            });

        $pendingDocsQuery = $pendingDocsBaseQuery->clone()
            ->where('status', 'pending_review')
            ->select([
                DB::raw('COUNT(*) as pending'),
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month_year')
            ])
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->orderBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->get();

        // ALL TIME TOTALS using clean base query
        $totalProjects = $baseQuery->count();
        $activeProjects = $baseQuery->clone()->where('status', 'in_progress')->count();
        $completedProjects = $baseQuery->clone()->where('status', 'completed')->count();
        
        // Pending documents (all time)
        $pendingDocuments = $pendingDocsBaseQuery->clone()
            ->where('status', 'pending_review')
            ->count();

        // Last month totals for comparison
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();
        
        $lastMonthTotal = $baseQuery->clone()
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->count();
        $lastMonthActive = $baseQuery->clone()
            ->where('status', 'in_progress')
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->count();
        $lastMonthCompleted = $baseQuery->clone()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->count();
        $lastMonthPending = $pendingDocsBaseQuery->clone()
            ->where('status', 'pending_review')
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->count();

        // Calculate percentage changes (comparing current total with last month additions)
        $totalChange = $this->calculatePercentageChange($totalProjects, $totalProjects - $lastMonthTotal);
        $activeChange = $this->calculatePercentageChange($activeProjects, $activeProjects - $lastMonthActive);
        $completedChange = $this->calculatePercentageChange($completedProjects, $completedProjects - $lastMonthCompleted);
        $pendingChange = $this->calculatePercentageChange($pendingDocuments, $pendingDocuments - $lastMonthPending);

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
            Stat::make('Total Proyek', (string) $totalProjects)
                ->description($totalChange . '% dari bulan lalu')
                ->descriptionIcon($totalChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($totalChange >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-folder')
                ->chart($chartData->pluck('total')->toArray())
                ->extraAttributes([
                    'class' => 'cursor-pointer transition-all duration-200 hover:scale-105',
                    'wire:click' => "\$dispatch('openProjectModal', { status: 'all', count: {$totalProjects} })",
                ]),

            Stat::make('Proyek Aktif', (string) $activeProjects)
                ->description($activeChange . '% dari bulan lalu')
                ->descriptionIcon($activeChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($activeChange >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-play')
                ->chart($chartData->pluck('active')->toArray())
                ->extraAttributes([
                    'class' => 'cursor-pointer transition-all duration-200 hover:scale-105',
                    'wire:click' => "\$dispatch('openProjectModal', { status: 'in_progress', count: {$activeProjects} })",
                ]),

            Stat::make('Proyek Selesai', (string) $completedProjects)
                ->description($completedChange . '% dari bulan lalu')
                ->descriptionIcon($completedChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($completedChange >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-check-circle')
                ->chart($chartData->pluck('completed')->toArray())
                ->extraAttributes([
                    'class' => 'cursor-pointer transition-all duration-200 hover:scale-105',
                    'wire:click' => "\$dispatch('openProjectModal', { status: 'completed', count: {$completedProjects} })",
                ]),

            Stat::make('Dokumen Pending', (string) $pendingDocuments)
                ->description($pendingChange . '% dari bulan lalu')
                ->descriptionIcon($pendingChange <= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($pendingChange <= 0 ? 'success' : 'warning')
                ->icon('heroicon-o-document-text')
                ->chart($chartData->pluck('pending')->toArray())
                ->extraAttributes([
                    'class' => 'cursor-pointer transition-all duration-200 hover:scale-105',
                    'wire:click' => "\$dispatch('openDocumentModal', { status: 'pending_review', count: {$pendingDocuments} })",
                ]),
        ];
    }

    private function calculatePercentageChange($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }
}