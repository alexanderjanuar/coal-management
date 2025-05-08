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

    public function getMonthlyTaxesData($taxType = 'ppn')
    {
        $currentYear = date('Y');
        $chartData = [];
        
        // Initialize data for all months
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = [
                'x' => Carbon::create()->month($i)->format('M'),
                'y' => 0,
            ];
        }
        
        // Get the appropriate data based on tax type
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
        
        // Merge the data with the initialized array
        foreach ($monthlyData as $month => $data) {
            if (isset($chartData[$month-1])) {
                $chartData[$month-1]['y'] = (float)$data->total;
            }
        }
        
        return json_encode($chartData);
    }
    
    public function getTaxTypeDistribution()
    {
        // Make sure all values are numeric and defaulting to 0 if null
        $ppnTotal = floatval(Invoice::sum('ppn')) ?: 0;
        $pph21Total = floatval(IncomeTax::sum('pph_21_amount')) ?: 0;
        $bupotsTotal = floatval(Bupot::sum('bupot_amount')) ?: 0;
        
        // Check if all values are zero
        $allZero = ($ppnTotal == 0 && $pph21Total == 0 && $bupotsTotal == 0);
        
        // If all values are zero, return a dataset with placeholder values
        if ($allZero) {
            return [
                ['name' => 'PPN', 'value' => 1],
                ['name' => 'PPh 21', 'value' => 1],
                ['name' => 'Bupot', 'value' => 1],
            ];
        }
        
        // Otherwise return the actual values
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