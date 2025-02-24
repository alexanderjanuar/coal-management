<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Project;
use App\Models\RequiredDocument;
use Carbon\Carbon;
class DocumentsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        // Get the current month's start and last month's start
        $currentMonthStart = Carbon::now()->startOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();

        // Get base query depending on user role
        $query = Project::query();
        if (!auth()->user()->hasRole('super-admin')) {
            $query->whereIn('client_id', function ($subQuery) {
                $subQuery->select('client_id')
                    ->from('user_clients')
                    ->where('user_id', auth()->id());
            });
        }

        // Calculate total projects
        $totalProjects = $query->count();
        $lastMonthTotal = $query->where('created_at', '<', $currentMonthStart)->count();
        $totalChange = $totalProjects - $lastMonthTotal;
        $totalPercentChange = $lastMonthTotal > 0 ? round(($totalChange / $lastMonthTotal) * 100, 1) : 0;

        // Calculate active projects
        $activeProjects = $query->clone()->where('status', 'in_progress')->count();
        $lastMonthActive = $query->clone()
            ->where('status', 'in_progress')
            ->where('created_at', '<', $currentMonthStart)
            ->count();
        $activeChange = $activeProjects - $lastMonthActive;
        $activePercentChange = $lastMonthActive > 0 ? round(($activeChange / $lastMonthActive) * 100, 1) : 0;

        // Calculate completed projects
        $completedProjects = $query->clone()->where('status', 'completed')->count();
        $lastMonthCompleted = $query->clone()
            ->where('status', 'completed')
            ->where('created_at', '<', $currentMonthStart)
            ->count();
        $completedChange = $completedProjects - $lastMonthCompleted;
        $completedPercentChange = $lastMonthCompleted > 0 ? round(($completedChange / $lastMonthCompleted) * 100, 1) : 0;

        // Calculate pending documents
        $pendingDocs = RequiredDocument::whereHas('projectStep.project', function ($query) {
            if (!auth()->user()->hasRole('super-admin')) {
                $query->whereIn('client_id', function ($subQuery) {
                    $subQuery->select('client_id')
                        ->from('user_clients')
                        ->where('user_id', auth()->id());
                });
            }
        })->where('status', 'pending')->count();
        
        $lastMonthPending = RequiredDocument::whereHas('projectStep.project', function ($query) {
            if (!auth()->user()->hasRole('super-admin')) {
                $query->whereIn('client_id', function ($subQuery) {
                    $subQuery->select('client_id')
                        ->from('user_clients')
                        ->where('user_id', auth()->id());
                });
            }
        })->where('status', 'pending')
          ->where('created_at', '<', $currentMonthStart)
          ->count();
        
        $pendingChange = $pendingDocs - $lastMonthPending;
        $pendingPercentChange = $lastMonthPending > 0 ? round(($pendingChange / $lastMonthPending) * 100, 1) : 0;

        return [
            Stat::make('Total Projects', (string)$totalProjects)
                ->description($totalPercentChange . '% vs last month')
                ->descriptionIcon($totalChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($totalChange >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-document-text')
                ->chart([random_int(60, 70), random_int(70, 80), random_int(80, 90), $totalProjects]),

            Stat::make('Active Projects', (string)$activeProjects)
                ->description($activePercentChange . '% vs last month')
                ->descriptionIcon($activeChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($activeChange >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-play')
                ->chart([random_int(50, 60), random_int(60, 70), random_int(70, 80), $activeProjects]),

            Stat::make('Completed', (string)$completedProjects)
                ->description($completedPercentChange . '% vs last month')
                ->descriptionIcon($completedChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($completedChange >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-check-circle')
                ->chart([random_int(1, 2), random_int(2, 3), random_int(2, 3), $completedProjects]),

            Stat::make('Pending Documents', (string)$pendingDocs)
                ->description($pendingPercentChange . '% vs last month')
                ->descriptionIcon($pendingChange <= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($pendingChange <= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-document')
                ->chart([random_int(300, 350), random_int(350, 400), random_int(400, 430), $pendingDocs]),
        ];
    }
}
