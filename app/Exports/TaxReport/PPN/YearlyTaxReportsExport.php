<?php
// app/Exports/TaxReport/PPN/YearlyTaxReportsExport.php

namespace App\Exports\TaxReport\PPN;

use App\Models\TaxReport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class YearlyTaxReportsExport implements WithMultipleSheets
{
    protected $clientId;
    protected $year;
    protected $clientName;

    public function __construct($clientId, $year)
    {
        $this->clientId = $clientId;
        $this->year = $year;
        
        // Get client name
        $firstReport = TaxReport::where('client_id', $clientId)
            ->where('year', $year)
            ->with('client')
            ->first();
        
        $this->clientName = $firstReport ? $firstReport->client->name : 'Unknown Client';
    }

    public function sheets(): array
    {
        $sheets = [];

        // First sheet: Yearly Summary
        $sheets[] = new YearlySummarySheet($this->clientId, $this->year, $this->clientName);

        // Following sheets: Monthly details (in chronological order)
        $taxReports = TaxReport::where('client_id', $this->clientId)
            ->where('year', $this->year)
            ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')")
            ->get();

        foreach ($taxReports as $report) {
            $sheets[] = new TaxReportInvoicesExport($report);
        }

        return $sheets;
    }
}