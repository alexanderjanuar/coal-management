<?php
// app/Exports/TaxReportExporter.php

namespace App\Exports;

use App\Models\TaxReport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;

class TaxReportExporter implements WithMultipleSheets
{
    protected $taxReports;

    public function __construct($taxReports)
    {
        $this->taxReports = $taxReports;
    }

    /**
     * Create multiple sheets - one summary + one for each tax report
     */
    public function sheets(): array
    {
        $sheets = [];
        
        // Create summary sheet first
        $sheets[] = new TaxReportSummarySheet($this->taxReports);
        
        // Create individual sheets for each tax report
        foreach ($this->taxReports as $taxReport) {
            $sheets[] = new TaxReportDetailSheet($taxReport);
        }
        
        return $sheets;
    }
}