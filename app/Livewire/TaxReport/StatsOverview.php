<?php

namespace App\Livewire\TaxReport;

use App\Models\Bupot;
use App\Models\IncomeTax;
use App\Models\Invoice;
use App\Models\TaxReport;
use App\Models\TaxCalculationSummary;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $year = date('Y');
        
        // Get basic tax statistics
        $totalReports = TaxReport::count();
        $thisYearReports = TaxReport::whereYear('created_at', $year)->count();
        $totalTax = Invoice::sum('ppn') + IncomeTax::sum('pph_21_amount') + Bupot::sum('bupot_amount');

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

        // Get all chart data in one efficient query
        $chartData = $this->getMonthlyChartData($year);

        return [
            Stat::make('Total Laporan Pajak', number_format($totalReports))
                ->description('Total semua laporan')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary')
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
     * Get status statistics for a specific tax type
     */
    private function getStatusStats(string $taxType): array
    {
        $sudahLapor = TaxCalculationSummary::where('tax_type', $taxType)
            ->where('report_status', 'Sudah Lapor')
            ->count();
        
        $total = TaxCalculationSummary::where('tax_type', $taxType)->count();
        
        // If no summaries exist yet, use total tax reports as fallback
        if ($total === 0) {
            $total = TaxReport::count();
            $sudahLapor = 0;
        }
        
        return [
            'sudah_lapor' => $sudahLapor,
            'total' => $total,
        ];
    }

    /**
     * Get monthly chart data efficiently
     */
    private function getMonthlyChartData(int $year): array
    {
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        // Initialize arrays
        $totalReports = [];
        $thisYearReports = [];
        $totalTax = [];
        $ppnCompletion = [];
        $pphCompletion = [];
        $bupotCompletion = [];

        // Get tax reports count by month
        $taxReportsData = TaxReport::selectRaw('
                month,
                COUNT(*) as total_count,
                SUM(CASE WHEN YEAR(created_at) = ? THEN 1 ELSE 0 END) as this_year_count
            ', [$year])
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        // Get completion data from tax_calculation_summaries efficiently
        $completionData = DB::table('tax_calculation_summaries')
            ->join('tax_reports', 'tax_calculation_summaries.tax_report_id', '=', 'tax_reports.id')
            ->select([
                'tax_reports.month',
                'tax_calculation_summaries.tax_type',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN tax_calculation_summaries.report_status = "Sudah Lapor" THEN 1 ELSE 0 END) as completed')
            ])
            ->groupBy(['tax_reports.month', 'tax_calculation_summaries.tax_type'])
            ->get()
            ->groupBy('month');

        // Get tax amounts by month efficiently
        $monthlyTaxAmounts = DB::table('tax_reports')
            ->select([
                'tax_reports.month',
                DB::raw('COALESCE(SUM(invoices.ppn), 0) as ppn_total'),
                DB::raw('COALESCE(SUM(income_taxes.pph_21_amount), 0) as pph_total'),
                DB::raw('COALESCE(SUM(bupots.bupot_amount), 0) as bupot_total')
            ])
            ->leftJoin('invoices', 'tax_reports.id', '=', 'invoices.tax_report_id')
            ->leftJoin('income_taxes', 'tax_reports.id', '=', 'income_taxes.tax_report_id')
            ->leftJoin('bupots', 'tax_reports.id', '=', 'bupots.tax_report_id')
            ->groupBy('tax_reports.month')
            ->get()
            ->keyBy('month');

        // Process data for each month
        foreach ($months as $month) {
            // Total reports
            $reportData = $taxReportsData->get($month);
            $totalReports[] = $reportData ? (int) $reportData->total_count : 0;
            $thisYearReports[] = $reportData ? (int) $reportData->this_year_count : 0;
            
            // Tax amounts
            $taxData = $monthlyTaxAmounts->get($month);
            $totalTax[] = $taxData 
                ? ($taxData->ppn_total + $taxData->pph_total + $taxData->bupot_total)
                : 0;
            
            // Completion percentages
            $monthCompletionData = $completionData->get($month);
            
            if ($monthCompletionData) {
                // PPN completion
                $ppnData = $monthCompletionData->firstWhere('tax_type', 'ppn');
                $ppnCompletion[] = $ppnData && $ppnData->total > 0
                    ? round(($ppnData->completed / $ppnData->total) * 100, 1)
                    : 0;
                
                // PPh completion
                $pphData = $monthCompletionData->firstWhere('tax_type', 'pph');
                $pphCompletion[] = $pphData && $pphData->total > 0
                    ? round(($pphData->completed / $pphData->total) * 100, 1)
                    : 0;
                
                // Bupot completion
                $bupotData = $monthCompletionData->firstWhere('tax_type', 'bupot');
                $bupotCompletion[] = $bupotData && $bupotData->total > 0
                    ? round(($bupotData->completed / $bupotData->total) * 100, 1)
                    : 0;
            } else {
                $ppnCompletion[] = 0;
                $pphCompletion[] = 0;
                $bupotCompletion[] = 0;
            }
        }

        return [
            'total_reports' => $totalReports,
            'this_year_reports' => $thisYearReports,
            'total_tax' => $totalTax,
            'ppn_completion' => $ppnCompletion,
            'pph_completion' => $pphCompletion,
            'bupot_completion' => $bupotCompletion,
        ];
    }

    protected function getColumns(): int
    {
        return 4; // Display 4 stats per row (added Bupot)
    }
}