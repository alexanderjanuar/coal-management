<?php

namespace App\Filament\Pages;

use App\Models\Bupot;
use App\Models\Client;
use App\Models\IncomeTax;
use App\Models\Invoice;
use App\Models\TaxReport;
use Carbon\Carbon;
use Filament\Pages\Page;

class DashboardTaxReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $modelLabel = 'Dashboard';

    protected static ?string $title = '';
    
    protected static ?string $navigationGroup = 'Tax Management';
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Dashboard';

    protected static string $view = 'filament.pages.tax-report.dashboard-tax-report';

    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('daily-task.dashboard.*');
    }
    
    public static function canAccess(): bool
    {
        return auth()->user()->can('daily-task.dashboard.*');
    }

    public function getMonthlyInvoicesData()
    {
        $currentYear = date('Y');
        
        $monthlyData = Invoice::selectRaw('MONTH(created_at) as month, SUM(ppn) as total_ppn')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');
        
        $chartData = [];
        
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = [
                'x' => Carbon::create()->month($i)->format('M'),
                'y' => $monthlyData[$i]['total_ppn'] ?? 0,
            ];
        }
        
        return json_encode($chartData);
    }
    
    public function getTopClients()
    {
        $clients = Client::withCount(['taxreports', 'projects'])
            ->orderByDesc('taxreports_count')
            ->limit(5)
            ->get();
        
        foreach ($clients as $client) {
            $taxReportIds = $client->taxreports()->pluck('id')->toArray();
            
            $ppnSum = 0;
            if (!empty($taxReportIds)) {
                $ppnSum = Invoice::whereIn('tax_report_id', $taxReportIds)->sum('ppn');
            }
            
            $client->invoices_sum_ppn = $ppnSum;
        }
        
        return $clients;
    }

    public function getMonthlyTaxesData($taxType = 'ppn')
    {
        $currentYear = date('Y');
        $chartData = [];
        
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = [
                'x' => Carbon::create()->month($i)->format('M'),
                'y' => 0,
            ];
        }
        
        switch ($taxType) {
            case 'ppn':
                $monthlyData = Invoice::selectRaw('MONTH(created_at) as month, SUM(ppn) as total')
                    ->whereYear('created_at', $currentYear)
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->keyBy('month');
                break;
            
            case 'pph21':
                $monthlyData = IncomeTax::selectRaw('MONTH(created_at) as month, SUM(pph_21_amount) as total')
                    ->whereYear('created_at', $currentYear)
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->keyBy('month');
                break;
            
            case 'bupot':
                $monthlyData = Bupot::selectRaw('MONTH(created_at) as month, SUM(bupot_amount) as total')
                    ->whereYear('created_at', $currentYear)
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->keyBy('month');
                break;
                
            default:
                return json_encode($chartData);
        }
        
        foreach ($monthlyData as $month => $data) {
            if (isset($chartData[$month-1])) {
                $chartData[$month-1]['y'] = (float)$data->total;
            }
        }
        
        return json_encode($chartData);
    }
    
    public function getTaxTypeDistribution()
    {
        $ppnTotal = floatval(Invoice::sum('ppn')) ?: 0;
        $pph21Total = floatval(IncomeTax::sum('pph_21_amount')) ?: 0;
        $bupotsTotal = floatval(Bupot::sum('bupot_amount')) ?: 0;
        
        $allZero = ($ppnTotal == 0 && $pph21Total == 0 && $bupotsTotal == 0);
        
        if ($allZero) {
            return [
                ['name' => 'PPN', 'value' => 1],
                ['name' => 'PPh 21', 'value' => 1],
                ['name' => 'Bupot', 'value' => 1],
            ];
        }
        
        return [
            ['name' => 'PPN', 'value' => $ppnTotal],
            ['name' => 'PPh 21', 'value' => $pph21Total],
            ['name' => 'Bupot', 'value' => $bupotsTotal],
        ];
    }
    
    public function getRecentTaxReports()
    {
        return TaxReport::with(['client', 'invoices', 'incomeTaxs', 'bupots'])
            ->latest()
            ->limit(5)
            ->get();
    }
    
    public function getTaxStats()
    {
        $year = date('Y');
        
        // Calculate pending reports (reports with any "Belum Lapor" status)
        $pendingReports = TaxReport::where(function($query) {
            $query->where('ppn_report_status', 'Belum Lapor')
                  ->orWhere('pph_report_status', 'Belum Lapor')
                  ->orWhere('bupot_report_status', 'Belum Lapor');
        })->count();
        
        return [
            'total_reports' => TaxReport::count(),
            'total_this_year' => TaxReport::whereYear('created_at', $year)->count(),
            'total_tax' => Invoice::sum('ppn') + IncomeTax::sum('pph_21_amount') + Bupot::sum('bupot_amount'),
            'pending_reports' => $pendingReports,
        ];
    }

    /**
     * Get report status statistics for the status overview cards
     */
    public function getReportStatusStats()
    {
        return [
            'ppn' => [
                'sudah_lapor' => TaxReport::where('ppn_report_status', 'Sudah Lapor')->count(),
                'belum_lapor' => TaxReport::where('ppn_report_status', 'Belum Lapor')->count(),
                'total' => TaxReport::count(),
            ],
            'pph' => [
                'sudah_lapor' => TaxReport::where('pph_report_status', 'Sudah Lapor')->count(),
                'belum_lapor' => TaxReport::where('pph_report_status', 'Belum Lapor')->count(),
                'total' => TaxReport::count(),
            ],
            'bupot' => [
                'sudah_lapor' => TaxReport::where('bupot_report_status', 'Sudah Lapor')->count(),
                'belum_lapor' => TaxReport::where('bupot_report_status', 'Belum Lapor')->count(),
                'total' => TaxReport::count(),
            ],
        ];
    }

    /**
     * Get monthly report completion rate
     */
    public function getMonthlyCompletionRate()
    {
        $currentYear = date('Y');
        $monthlyData = [];
        
        for ($i = 1; $i <= 12; $i++) {
            $monthName = Carbon::create()->month($i)->format('M');
            
            $totalReports = TaxReport::whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $i)
                ->count();
            
            $completedReports = TaxReport::whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $i)
                ->where('ppn_report_status', 'Sudah Lapor')
                ->where('pph_report_status', 'Sudah Lapor')
                ->where('bupot_report_status', 'Sudah Lapor')
                ->count();
            
            $completionRate = $totalReports > 0 ? ($completedReports / $totalReports) * 100 : 0;
            
            $monthlyData[] = [
                'x' => $monthName,
                'y' => round($completionRate, 1),
                'total' => $totalReports,
                'completed' => $completedReports
            ];
        }
        
        return json_encode($monthlyData);
    }

    /**
     * Get overdue reports (reports that should have been filed)
     */
    public function getOverdueReports()
    {
        // Reports older than 1 month that haven't been filed
        $cutoffDate = Carbon::now()->subMonth();
        
        return TaxReport::where('created_at', '<', $cutoffDate)
            ->where(function($query) {
                $query->where('ppn_report_status', 'Belum Lapor')
                      ->orWhere('pph_report_status', 'Belum Lapor')
                      ->orWhere('bupot_report_status', 'Belum Lapor');
            })
            ->with('client')
            ->latest()
            ->limit(10)
            ->get();
    }
}
