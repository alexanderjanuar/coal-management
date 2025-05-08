<?php

namespace App\Filament\Resources\TaxReportResource\Pages;

use App\Filament\Resources\TaxReportResource;
use App\Models\Bupot;
use App\Models\Client;
use App\Models\IncomeTax;
use App\Models\Invoice;
use App\Models\TaxReport;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;

class TaxReportDashboard extends Page
{
    protected static string $resource = TaxReportResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $modelLabel = 'Dashboard Pajak';

    protected static ?string $navigationGroup = 'Tax';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static string $view = 'filament.resources.tax-report-resource.pages.tax-report-dashboard';
    
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
        // First, we get clients with basic counts
        $clients = Client::withCount(['taxreports', 'projects'])
            ->orderByDesc('taxreports_count')
            ->limit(5)
            ->get();
        
        // Then, for each client, we calculate the sum of PPN from related invoices
        foreach ($clients as $client) {
            // Find all tax reports for this client
            $taxReportIds = $client->taxreports()->pluck('id')->toArray();
            
            // Calculate sum of PPN from invoices related to these tax reports
            $ppnSum = 0;
            if (!empty($taxReportIds)) {
                $ppnSum = Invoice::whereIn('tax_report_id', $taxReportIds)->sum('ppn');
            }
            
            // Add the calculated sum as an attribute
            $client->invoices_sum_ppn = $ppnSum;
        }
        
        return $clients;
    }
    
    public function getTaxTypeDistribution()
    {
        $ppnTotal = Invoice::sum('ppn');
        $pph21Total = IncomeTax::sum('pph_21_amount');
        $bupotsTotal = Bupot::sum('bupot_amount');
        
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
        
        return [
            'total_reports' => TaxReport::count(),
            'total_this_year' => TaxReport::whereYear('created_at', $year)->count(),
            'total_tax' => Invoice::sum('ppn') + IncomeTax::sum('pph_21_amount') + Bupot::sum('bupot_amount'),
            'unpaid_reports' => 0, // Placeholder, add logic if you track payment status
        ];
    }
}