<?php

namespace App\Livewire\TaxReport\Dashboard;

use App\Models\Bupot;
use App\Models\IncomeTax;
use App\Models\Invoice;
use App\Models\TaxReport;
use App\Models\TaxCalculationSummary;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 2; // Display after filter

    // Filter properties
    public Carbon $fromDate;
    public Carbon $toDate;
    public ?string $dateRange = 'this_year';
    public ?int $clientId = null;
    public ?string $taxType = null;
    public ?string $reportStatus = null;
    public ?string $paymentStatus = null;

    public function mount(): void
    {
        // Set default date range (this year)
        $this->fromDate = now()->startOfYear();
        $this->toDate = now()->endOfYear();
    }

    // Event Listener for filter updates
    #[On('filtersUpdated')]
    public function updateFilters(array $filters): void
    {
        $this->dateRange = $filters['date_range'];
        $this->fromDate = Carbon::parse($filters['from'])->startOfDay();
        $this->toDate = Carbon::parse($filters['to'])->endOfDay();
        $this->clientId = $filters['client_id'];
        $this->taxType = $filters['tax_type'];
        $this->reportStatus = $filters['report_status'];
        $this->paymentStatus = $filters['payment_status'];
        
        // Clear cache when filters change
        $this->clearStatsCache();
        
        // Force refresh widget
        $this->dispatch('$refresh');
    }

    protected function getStats(): array
    {
        // Base query for tax reports with date filter
        $baseQuery = TaxReport::whereBetween('created_at', [
            $this->fromDate,
            $this->toDate
        ]);

        // Apply client filter
        if ($this->clientId) {
            $baseQuery->where('client_id', $this->clientId);
        }

        // Get total reports
        $totalReports = (clone $baseQuery)->count();

        // Get report status statistics from tax_calculation_summaries
        $ppnStats = $this->getStatusStats('ppn');
        $pphStats = $this->getStatusStats('pph');
        $bupotStats = $this->getStatusStats('bupot');

        // Calculate completion percentages
        $ppnCompletion = $ppnStats['total'] > 0 
            ? round(($ppnStats['sudah_lapor'] / $ppnStats['total']) * 100, 1) 
            : 0;
        
        $pphCompletion = $pphStats['total'] > 0 
            ? round(($pphStats['sudah_lapor'] / $pphStats['total']) * 100, 1) 
            : 0;
        
        $bupotCompletion = $bupotStats['total'] > 0 
            ? round(($bupotStats['sudah_lapor'] / $bupotStats['total']) * 100, 1) 
            : 0;

        // Get chart data based on period
        $chartData = $this->getChartData();

        // Generate period description
        $periodDesc = $this->getPeriodDescription();

        return [
            Stat::make('Total Laporan Pajak', number_format($totalReports))
                ->description($periodDesc)
                ->descriptionIcon('heroicon-m-document-text')
                ->color($totalReports > 0 ? 'primary' : 'gray')
                ->chart($chartData['total_reports']),
            
            Stat::make('Status PPN', $ppnStats['sudah_lapor'] . ' dari ' . $ppnStats['total'])
                ->description($ppnCompletion . '% sudah dilaporkan')
                ->descriptionIcon('heroicon-m-document-check')
                ->color($ppnCompletion >= 80 ? 'success' : ($ppnCompletion >= 50 ? 'warning' : 'danger'))
                ->chart($chartData['ppn_completion']),
            
            Stat::make('Status PPh', $pphStats['sudah_lapor'] . ' dari ' . $pphStats['total'])
                ->description($pphCompletion . '% sudah dilaporkan')
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color($pphCompletion >= 80 ? 'success' : ($pphCompletion >= 50 ? 'warning' : 'danger'))
                ->chart($chartData['pph_completion']),
            
            Stat::make('Status Bupot', $bupotStats['sudah_lapor'] . ' dari ' . $bupotStats['total'])
                ->description($bupotCompletion . '% sudah dilaporkan')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color($bupotCompletion >= 80 ? 'success' : ($bupotCompletion >= 50 ? 'warning' : 'danger'))
                ->chart($chartData['bupot_completion']),
        ];
    }

    /**
     * Get status statistics for a specific tax type with filters
     */
    private function getStatusStats(string $taxType): array
    {
        // Start building query
        $query = TaxCalculationSummary::query()
            ->join('tax_reports', 'tax_calculation_summaries.tax_report_id', '=', 'tax_reports.id')
            ->where('tax_calculation_summaries.tax_type', $taxType)
            ->whereBetween('tax_reports.created_at', [
                $this->fromDate,
                $this->toDate
            ]);

        // Apply filters
        if ($this->clientId) {
            $query->where('tax_reports.client_id', $this->clientId);
        }

        if ($this->reportStatus) {
            $query->where('tax_calculation_summaries.report_status', $this->reportStatus);
        }

        if ($this->paymentStatus) {
            $query->where('tax_calculation_summaries.status_final', $this->paymentStatus);
        }

        // If tax_type filter is set and doesn't match current type, return zeros
        if ($this->taxType && $this->taxType !== $taxType) {
            return [
                'sudah_lapor' => 0,
                'total' => 0,
            ];
        }

        // Get counts
        $total = (clone $query)->count();
        $sudahLapor = (clone $query)
            ->where('tax_calculation_summaries.report_status', 'Sudah Lapor')
            ->count();
        
        // If no summaries exist yet, use total tax reports as fallback
        if ($total === 0) {
            $baseQuery = TaxReport::whereBetween('created_at', [
                $this->fromDate,
                $this->toDate
            ]);
            
            if ($this->clientId) {
                $baseQuery->where('client_id', $this->clientId);
            }
            
            $total = $baseQuery->count();
            $sudahLapor = 0;
        }
        
        return [
            'sudah_lapor' => $sudahLapor,
            'total' => $total,
        ];
    }

    /**
     * Get chart data based on date range
     */
    private function getChartData(): array
    {
        $diffInMonths = $this->fromDate->diffInMonths($this->toDate);
        
        // If range is less than 2 months, use daily data
        if ($diffInMonths < 2) {
            return $this->getDailyChartData();
        }
        // If range is less than 12 months, use monthly data
        elseif ($diffInMonths < 12) {
            return $this->getMonthlyChartData();
        }
        // Otherwise use yearly data
        else {
            return $this->getYearlyChartData();
        }
    }

    /**
     * Get daily chart data for short periods
     */
    private function getDailyChartData(): array
    {
        $days = [];
        $currentDate = $this->fromDate->copy();
        
        while ($currentDate <= $this->toDate) {
            $days[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        // Initialize arrays
        $totalReports = array_fill(0, count($days), 0);
        $ppnCompletion = array_fill(0, count($days), 0);
        $pphCompletion = array_fill(0, count($days), 0);
        $bupotCompletion = array_fill(0, count($days), 0);

        // Get data grouped by date
        $reportsData = TaxReport::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$this->fromDate, $this->toDate])
            ->when($this->clientId, fn($q) => $q->where('client_id', $this->clientId))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Fill in the data
        foreach ($days as $index => $day) {
            $totalReports[$index] = $reportsData->get($day)?->count ?? 0;
        }

        return [
            'total_reports' => $totalReports,
            'ppn_completion' => $ppnCompletion,
            'pph_completion' => $pphCompletion,
            'bupot_completion' => $bupotCompletion,
        ];
    }

    /**
     * Get monthly chart data - HIGHLY OPTIMIZED VERSION WITH SINGLE QUERY
     */
    private function getMonthlyChartData(): array
    {
        $months = [];
        $currentDate = $this->fromDate->copy()->startOfMonth();
        
        while ($currentDate <= $this->toDate) {
            $months[] = $currentDate->format('Y-m');
            $currentDate->addMonth();
        }

        // Initialize arrays
        $totalReports = array_fill(0, count($months), 0);
        $ppnCompletion = array_fill(0, count($months), 0);
        $pphCompletion = array_fill(0, count($months), 0);
        $bupotCompletion = array_fill(0, count($months), 0);

        // Single optimized query to get all data
        $allData = DB::table('tax_reports')
            ->leftJoin('tax_calculation_summaries', 'tax_reports.id', '=', 'tax_calculation_summaries.tax_report_id')
            ->select([
                DB::raw('DATE_FORMAT(tax_reports.created_at, "%Y-%m") as month_key'),
                DB::raw('COUNT(DISTINCT tax_reports.id) as total_reports'),
                // PPN stats
                DB::raw('COUNT(CASE WHEN tax_calculation_summaries.tax_type = "ppn" THEN 1 END) as ppn_total'),
                DB::raw('SUM(CASE WHEN tax_calculation_summaries.tax_type = "ppn" AND tax_calculation_summaries.report_status = "Sudah Lapor" THEN 1 ELSE 0 END) as ppn_completed'),
                // PPh stats
                DB::raw('COUNT(CASE WHEN tax_calculation_summaries.tax_type = "pph" THEN 1 END) as pph_total'),
                DB::raw('SUM(CASE WHEN tax_calculation_summaries.tax_type = "pph" AND tax_calculation_summaries.report_status = "Sudah Lapor" THEN 1 ELSE 0 END) as pph_completed'),
                // Bupot stats
                DB::raw('COUNT(CASE WHEN tax_calculation_summaries.tax_type = "bupot" THEN 1 END) as bupot_total'),
                DB::raw('SUM(CASE WHEN tax_calculation_summaries.tax_type = "bupot" AND tax_calculation_summaries.report_status = "Sudah Lapor" THEN 1 ELSE 0 END) as bupot_completed'),
            ])
            ->whereBetween('tax_reports.created_at', [$this->fromDate, $this->toDate])
            ->when($this->clientId, fn($q) => $q->where('tax_reports.client_id', $this->clientId))
            ->when($this->taxType, fn($q) => $q->where('tax_calculation_summaries.tax_type', $this->taxType))
            ->when($this->reportStatus, fn($q) => $q->where('tax_calculation_summaries.report_status', $this->reportStatus))
            ->groupBy(DB::raw('DATE_FORMAT(tax_reports.created_at, "%Y-%m")'))
            ->get()
            ->keyBy('month_key');

        // Process data for each month
        foreach ($months as $index => $month) {
            $monthData = $allData->get($month);
            
            if ($monthData) {
                // Total reports
                $totalReports[$index] = $monthData->total_reports;
                
                // PPN completion percentage
                $ppnCompletion[$index] = $monthData->ppn_total > 0
                    ? round(($monthData->ppn_completed / $monthData->ppn_total) * 100, 1)
                    : 0;
                
                // PPh completion percentage
                $pphCompletion[$index] = $monthData->pph_total > 0
                    ? round(($monthData->pph_completed / $monthData->pph_total) * 100, 1)
                    : 0;
                
                // Bupot completion percentage
                $bupotCompletion[$index] = $monthData->bupot_total > 0
                    ? round(($monthData->bupot_completed / $monthData->bupot_total) * 100, 1)
                    : 0;
            }
        }

        return [
            'total_reports' => $totalReports,
            'ppn_completion' => $ppnCompletion,
            'pph_completion' => $pphCompletion,
            'bupot_completion' => $bupotCompletion,
        ];
    }
    /**
     * Get yearly chart data for long periods
     */
    private function getYearlyChartData(): array
    {
        $years = [];
        $currentYear = $this->fromDate->year;
        $endYear = $this->toDate->year;
        
        while ($currentYear <= $endYear) {
            $years[] = $currentYear;
            $currentYear++;
        }

        // Initialize arrays
        $totalReports = array_fill(0, count($years), 0);
        $ppnCompletion = array_fill(0, count($years), 0);
        $pphCompletion = array_fill(0, count($years), 0);
        $bupotCompletion = array_fill(0, count($years), 0);

        // Get data grouped by year
        $reportsData = TaxReport::selectRaw('YEAR(created_at) as year, COUNT(*) as count')
            ->whereBetween('created_at', [$this->fromDate, $this->toDate])
            ->when($this->clientId, fn($q) => $q->where('client_id', $this->clientId))
            ->groupBy('year')
            ->get()
            ->keyBy('year');

        // Fill in the data
        foreach ($years as $index => $year) {
            $totalReports[$index] = $reportsData->get($year)?->count ?? 0;
        }

        return [
            'total_reports' => $totalReports,
            'ppn_completion' => $ppnCompletion,
            'pph_completion' => $pphCompletion,
            'bupot_completion' => $bupotCompletion,
        ];
    }

    protected function getPeriodDescription(): string
    {
        return match($this->dateRange) {
            'this_month' => 'Bulan ini',
            'last_month' => 'Bulan lalu',
            'this_quarter' => 'Quarter ini',
            'last_quarter' => 'Quarter lalu',
            'this_year' => 'Tahun ini',
            'last_year' => 'Tahun lalu',
            'custom' => $this->fromDate->format('M Y') . ' - ' . $this->toDate->format('M Y'),
            default => 'Periode yang dipilih'
        };
    }

    protected function clearStatsCache(): void
    {
        $cacheKey = $this->getCacheKey();
        cache()->forget($cacheKey);
    }

    /**
     * Cache key based on active filters
     */
    protected function getCacheKey(): string
    {
        $filterHash = md5(serialize([
            'from' => $this->fromDate->format('Y-m-d'),
            'to' => $this->toDate->format('Y-m-d'),
            'client_id' => $this->clientId,
            'tax_type' => $this->taxType,
            'report_status' => $this->reportStatus,
            'payment_status' => $this->paymentStatus,
        ]));
        
        return "tax-report-stats-{$filterHash}-" . now()->format('Y-m-d-H');
    }

    /**
     * Refresh widget data every 60 seconds
     */
    protected function getPollingInterval(): ?string
    {
        return '60s';
    }

    protected function getColumns(): int
    {
        return 4; // Display 4 stats per row
    }
}