<?php

namespace App\Livewire\TaxReport;

use App\Models\Bupot;
use App\Models\IncomeTax;
use App\Models\Invoice;
use App\Models\TaxReport;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $year = date('Y');
        
        // Get basic tax statistics
        $totalReports = TaxReport::count();
        $thisYearReports = TaxReport::whereYear('created_at', $year)->count();
        $totalTax = Invoice::sum('ppn') + IncomeTax::sum('pph_21_amount') + Bupot::sum('bupot_amount');

        // Get report status statistics
        $ppnStats = [
            'sudah_lapor' => TaxReport::where('ppn_report_status', 'Sudah Lapor')->count(),
            'total' => $totalReports,
        ];
        
        $pphStats = [
            'sudah_lapor' => TaxReport::where('pph_report_status', 'Sudah Lapor')->count(),
            'total' => $totalReports,
        ];
        
        $bupotStats = [
            'sudah_lapor' => TaxReport::where('bupot_report_status', 'Sudah Lapor')->count(),
            'total' => $totalReports,
        ];

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
            
            Stat::make('Laporan Tahun Ini', number_format($thisYearReports))
                ->description('Laporan tahun ' . $year)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success')
                ->chart($chartData['this_year_reports']),
            
            Stat::make('Total Nilai Pajak', 'Rp ' . number_format($totalTax, 0, ',', '.'))
                ->description('PPN + PPh 21 + Bupot')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning')
                ->chart($chartData['total_tax']),
            
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
                ->descriptionIcon('heroicon-m-document-text')
                ->color($bupotCompletion >= 80 ? 'success' : ($bupotCompletion >= 50 ? 'warning' : 'danger'))
                ->chart($chartData['bupot_completion']),
        ];
    }

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

        // Get all tax reports with related data in single query
        $taxReportsData = TaxReport::selectRaw('
                month,
                COUNT(*) as total_count,
                SUM(CASE WHEN YEAR(created_at) = ? THEN 1 ELSE 0 END) as this_year_count,
                SUM(CASE WHEN ppn_report_status = "Sudah Lapor" THEN 1 ELSE 0 END) as ppn_completed,
                SUM(CASE WHEN pph_report_status = "Sudah Lapor" THEN 1 ELSE 0 END) as pph_completed,
                SUM(CASE WHEN bupot_report_status = "Sudah Lapor" THEN 1 ELSE 0 END) as bupot_completed
            ', [$year])
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        // Get tax amounts by month in efficient queries
        $monthlyTaxAmounts = [];
        foreach ($months as $month) {
            $taxReportIds = TaxReport::where('month', $month)->pluck('id');
            
            if ($taxReportIds->isNotEmpty()) {
                $ppnTotal = Invoice::whereIn('tax_report_id', $taxReportIds)->sum('ppn');
                $pphTotal = IncomeTax::whereIn('tax_report_id', $taxReportIds)->sum('pph_21_amount');
                $bupotTotal = Bupot::whereIn('tax_report_id', $taxReportIds)->sum('bupot_amount');
                
                $monthlyTaxAmounts[$month] = $ppnTotal + $pphTotal + $bupotTotal;
            } else {
                $monthlyTaxAmounts[$month] = 0;
            }
        }

        // Process data for each month
        foreach ($months as $month) {
            $data = $taxReportsData->get($month);
            
            if ($data) {
                $totalReports[] = (int) $data->total_count;
                $thisYearReports[] = (int) $data->this_year_count;
                $totalTax[] = $monthlyTaxAmounts[$month];
                
                // Calculate completion percentages
                $total = (int) $data->total_count;
                $ppnCompletion[] = $total > 0 ? round(($data->ppn_completed / $total) * 100, 1) : 0;
                $pphCompletion[] = $total > 0 ? round(($data->pph_completed / $total) * 100, 1) : 0;
                $bupotCompletion[] = $total > 0 ? round(($data->bupot_completed / $total) * 100, 1) : 0;
            } else {
                $totalReports[] = 0;
                $thisYearReports[] = 0;
                $totalTax[] = 0;
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
        return 3; // Display 3 stats per row
    }
}