<?php

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
/**
 * Summary Sheet - Overview of all tax reports
 */
class TaxReportSummarySheet implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    protected $taxReports;

    public function __construct($taxReports)
    {
        $this->taxReports = $taxReports;
    }

    public function array(): array
    {
        $data = [];
        
        // Title rows
        $data[] = ['', '', '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', '', ''];
        $data[] = ['', 'RINGKASAN LAPORAN PAJAK - ' . strtoupper(date('Y')), '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', '', ''];
        
        // Headers
        $data[] = ['', 'No', 'Client', 'Periode', 'PPN Keluaran', 'PPN Masukan', 'Selisih', 'Status', 'Total Pajak'];
        
        // Data
        $no = 1;
        $totalPpnKeluaran = 0;
        $totalPpnMasukan = 0;
        $totalPajak = 0;
        
        foreach ($this->taxReports as $report) {
            // Calculate totals
            $ppnKeluaran = $report->invoices()->where('type', 'Faktur Keluaran')->sum('ppn');
            $ppnMasukan = $report->invoices()->where('type', 'Faktur Masuk')->sum('ppn');
            $selisih = $ppnKeluaran - $ppnMasukan;
            $pph21 = $report->incomeTaxs()->sum('pph_21_amount');
            $bupot = $report->bupots()->sum('bupot_amount');
            $totalTax = $ppnKeluaran + $ppnMasukan + $pph21 + $bupot;
            
            $data[] = [
                '',
                $no++,
                $report->client->name ?? 'Unknown',
                $report->month,
                'Rp ' . number_format($ppnKeluaran, 0, ',', '.'),
                'Rp ' . number_format($ppnMasukan, 0, ',', '.'),
                'Rp ' . number_format($selisih, 0, ',', '.'),
                $report->invoice_tax_status ?? 'Belum Dihitung',
                'Rp ' . number_format($totalTax, 0, ',', '.')
            ];
            
            $totalPpnKeluaran += $ppnKeluaran;
            $totalPpnMasukan += $ppnMasukan;
            $totalPajak += $totalTax;
        }
        
        // Totals row
        $data[] = [
            '',
            '',
            'TOTAL',
            '',
            'Rp ' . number_format($totalPpnKeluaran, 0, ',', '.'),
            'Rp ' . number_format($totalPpnMasukan, 0, ',', '.'),
            'Rp ' . number_format($totalPpnKeluaran - $totalPpnMasukan, 0, ',', '.'),
            '',
            'Rp ' . number_format($totalPajak, 0, ',', '.')
        ];
        
        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->taxReports) + 6;
        
        // Title
        $sheet->mergeCells('B3:I3');
        $sheet->getStyle('B3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        
        // Headers
        $sheet->getStyle('B5:I5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '4472C4']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);
        
        // Data rows
        $sheet->getStyle("B6:I{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);
        
        // Totals row
        $sheet->getStyle("B{$lastRow}:I{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'E8E8E8']
            ]
        ]);
        
        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 3,
            'B' => 8,
            'C' => 30,
            'D' => 15,
            'E' => 18,
            'F' => 18,
            'G' => 18,
            'H' => 15,
            'I' => 18,
        ];
    }

    public function title(): string
    {
        return 'Ringkasan';
    }
}