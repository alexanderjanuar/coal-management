<?php
// app/Exports/TaxReportInvoicesExport.php

namespace App\Exports;

use App\Models\TaxReport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TaxReportInvoicesExport implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    protected $taxReport;

    public function __construct(TaxReport $taxReport)
    {
        $this->taxReport = $taxReport;
    }



    public function array(): array
    {
        $data = [];

        // Title row - FAKTUR [CLIENT NAME] [MONTH] [YEAR] - start at column B
        $clientName = strtoupper($this->taxReport->client->name ?? 'UNKNOWN CLIENT');
        $monthYear = strtoupper($this->getIndonesianMonth($this->taxReport->month)) . ' ' . date('Y');
        $data[] = ['', 'REKAP FAKTUR ' . $clientName . ' - ' . $monthYear, '', '', '', ''];
        $data[] = ['', '', '', '', '', '']; // Empty row

        // FAKTUR KELUARAN section - start at column B
        $data[] = ['', 'FAKTUR KELUARAN', '', '', '', ''];

        // Headers - start at column B
        $data[] = ['', 'No', 'Nama Penjual', 'Tanggal', 'DPP', 'PPN'];

        // Get Faktur Keluaran data
        $fakturKeluaran = $this->taxReport->invoices()
            ->where('type', 'Faktur Keluaran')
            ->orderBy('invoice_date')
            ->get();

        $totalDppKeluaran = 0;
        $totalPpnKeluaran = 0;

        // Data rows - start at column B
        foreach ($fakturKeluaran as $index => $invoice) {
            $data[] = [
                '',
                $index + 1,
                $invoice->company_name,
                date('n/j/Y', strtotime($invoice->invoice_date)),
                'Rp ' . number_format($invoice->dpp, 0, ',', ','),
                'Rp ' . number_format($invoice->ppn, 0, ',', ','),
            ];

            $totalDppKeluaran += $invoice->dpp;
            $totalPpnKeluaran += $invoice->ppn;
        }

        // JUMLAH row for Faktur Keluaran - start at column B
        $data[] = ['', '', 'JUMLAH', '', 'Rp ' . number_format($totalDppKeluaran, 0, ',', ','), 'Rp ' . number_format($totalPpnKeluaran, 0, ',', ',')];

        // Add 2 empty rows for spacing
        $data[] = ['', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', ''];

        // FAKTUR MASUKAN section - start at column B
        $data[] = ['', 'FAKTUR MASUKAN', '', '', '', ''];

        // Headers - start at column B
        $data[] = ['', 'No', 'Nama Penjual', 'Tanggal', 'DPP', 'PPN'];

        // Get Faktur Masukan data
        $fakturMasukan = $this->taxReport->invoices()
            ->where('type', 'Faktur Masuk')
            ->orderBy('invoice_date')
            ->get();

        $totalDppMasukan = 0;
        $totalPpnMasukan = 0;

        // Data rows - start at column B
        foreach ($fakturMasukan as $index => $invoice) {
            $data[] = [
                '',
                $index + 1,
                $invoice->company_name,
                date('n/j/Y', strtotime($invoice->invoice_date)),
                'Rp ' . number_format($invoice->dpp, 0, ',', ','),
                'Rp ' . number_format($invoice->ppn, 0, ',', ','),
            ];

            $totalDppMasukan += $invoice->dpp;
            $totalPpnMasukan += $invoice->ppn;
        }

        // JUMLAH row for Faktur Masukan - start at column B
        $data[] = ['', '', 'JUMLAH', '', 'Rp ' . number_format($totalDppMasukan, 0, ',', ','), 'Rp ' . number_format($totalPpnMasukan, 0, ',', ',')];

        // Add 2 empty rows for spacing
        $data[] = ['', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', ''];

        // REKAP KURANG ATAU LEBIH BAYAR PAJAK section
        $data[] = ['', 'REKAP KURANG ATAU LEBIH BAYAR PAJAK', '', '', '', ''];

        // Summary calculations
        $kurangLebihBayar = $totalPpnKeluaran - $totalPpnMasukan;
        $ppnDikompensasi = $this->taxReport->ppn_dikompensasi_dari_masa_sebelumnya ?? 0;
        $finalAmount = $kurangLebihBayar - $ppnDikompensasi;

        $data[] = ['', 'TOTAL PPN FAKTUR KELUARAN', '', '', '', 'Rp ' . number_format($totalPpnKeluaran, 0, ',', ',')];
        $data[] = ['', 'TOTAL PPN FAKTUR MASUKAN', '', '', '', 'Rp ' . number_format($totalPpnMasukan, 0, ',', ',')];
        $data[] = ['', 'PPN DIKOMPENSASIKAN DARI MASA SEBELUMNYA', '', '', '', 'Rp ' . number_format($ppnDikompensasi, 0, ',', ',')];
        $data[] = ['', 'TOTAL KURANG/ LEBIH BAYAR PAJAK', '', '', '', 'Rp ' . number_format($finalAmount, 0, ',', ',')];

        // Determine status and add status row
        if ($finalAmount > 0) {
            $status = 'KURANG BAYAR';
        } elseif ($finalAmount < 0) {
            $status = 'LEBIH BAYAR';
        } else {
            $status = 'NIHIL';
        }

        // Add status row
        $data[] = ['', $status, '', '', '', ''];

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $fakturKeluaran = $this->taxReport->invoices()->where('type', 'Faktur Keluaran')->get();
        $fakturMasukan = $this->taxReport->invoices()->where('type', 'Faktur Masuk')->get();
        $keluaranDataRowCount = $fakturKeluaran->count();
        $masukanDataRowCount = $fakturMasukan->count();

        // Calculate row positions dynamically for FAKTUR KELUARAN
        $titleRow = 1;
        $sectionHeaderRow = 3;
        $headerRow = 4;
        $dataStartRow = 5;
        $dataEndRow = $dataStartRow + $keluaranDataRowCount - 1;
        $jumlahRow = $dataEndRow + 1;

        // Title styling - FAKTUR MEI 2025 - merge across columns B-F
        $sheet->mergeCells('B1:F1');
        $sheet->getStyle('B1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // FAKTUR KELUARAN section header - MERGE B3:F3
        $sheet->mergeCells("B{$sectionHeaderRow}:F{$sectionHeaderRow}");
        $sheet->getStyle("B{$sectionHeaderRow}:F{$sectionHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '4472C4']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ]);

        // Headers row styling (No, Nama Penjual, Tanggal, DPP, PPN)
        $sheet->getStyle("B{$headerRow}:F{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'E8E8E8']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ]);

        // Data rows styling (only actual data rows)
        if ($keluaranDataRowCount > 0) {
            $sheet->getStyle("B{$dataStartRow}:F{$dataEndRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            // Align text columns to left
            $sheet->getStyle("C{$dataStartRow}:D{$dataEndRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ]);

            // Align numbers to the right
            $sheet->getStyle("E{$dataStartRow}:F{$dataEndRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
            ]);

            // Center the No column
            $sheet->getStyle("B{$dataStartRow}:B{$dataEndRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }

        // JUMLAH row styling for FAKTUR KELUARAN - UPDATED
        $sheet->getStyle("B{$jumlahRow}:F{$jumlahRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '4472C4']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']] // Changed from BORDER_MEDIUM to BORDER_THIN
            ]
        ]);

        // Align JUMLAH amounts to the right
        $sheet->getStyle("E{$jumlahRow}:F{$jumlahRow}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
        ]);

        // Calculate FAKTUR MASUKAN section positions
        $masukanSectionHeaderRow = $jumlahRow + 3;
        $masukanHeaderRow = $masukanSectionHeaderRow + 1;
        $masukanDataStartRow = $masukanHeaderRow + 1;
        $masukanDataEndRow = $masukanDataStartRow + $masukanDataRowCount - 1;
        $masukanJumlahRow = $masukanDataEndRow + 1;

        // FAKTUR MASUKAN section header
        $sheet->mergeCells("B{$masukanSectionHeaderRow}:F{$masukanSectionHeaderRow}");
        $sheet->getStyle("B{$masukanSectionHeaderRow}:F{$masukanSectionHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '4472C4']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ]);

        // FAKTUR MASUKAN headers row styling
        $sheet->getStyle("B{$masukanHeaderRow}:F{$masukanHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'E8E8E8']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ]);

        // FAKTUR MASUKAN data rows styling
        if ($masukanDataRowCount > 0) {
            $sheet->getStyle("B{$masukanDataStartRow}:F{$masukanDataEndRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            // Align text columns to left
            $sheet->getStyle("C{$masukanDataStartRow}:D{$masukanDataEndRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ]);

            // Align numbers to the right
            $sheet->getStyle("E{$masukanDataStartRow}:F{$masukanDataEndRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
            ]);

            // Center the No column
            $sheet->getStyle("B{$masukanDataStartRow}:B{$masukanDataEndRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }

        // FAKTUR MASUKAN JUMLAH row styling - UPDATED
        $sheet->getStyle("B{$masukanJumlahRow}:F{$masukanJumlahRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '4472C4']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']] // Changed from BORDER_MEDIUM to BORDER_THIN
            ]
        ]);

        // Align FAKTUR MASUKAN JUMLAH amounts to the right
        $sheet->getStyle("E{$masukanJumlahRow}:F{$masukanJumlahRow}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
        ]);

        // Calculate REKAP section positions
        $rekapSectionHeaderRow = $masukanJumlahRow + 3;
        $rekapDataStartRow = $rekapSectionHeaderRow + 1;
        $rekapDataEndRow = $rekapDataStartRow + 3; // 4 summary rows
        $statusRow = $rekapDataEndRow + 1; // Status row after REKAP data

        // REKAP KURANG ATAU LEBIH BAYAR PAJAK section header
        $sheet->mergeCells("B{$rekapSectionHeaderRow}:F{$rekapSectionHeaderRow}");
        $sheet->getStyle("B{$rekapSectionHeaderRow}:F{$rekapSectionHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '4472C4']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ]);

        // REKAP data rows styling
        $sheet->getStyle("B{$rekapDataStartRow}:F{$rekapDataEndRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Align description text to left
        $sheet->getStyle("B{$rekapDataStartRow}:B{$rekapDataEndRow}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);

        // Align amounts to the right
        $sheet->getStyle("F{$rekapDataStartRow}:F{$rekapDataEndRow}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
        ]);

        // Status row styling - NEW
        $sheet->mergeCells("B{$statusRow}:F{$statusRow}");

        // Calculate final amount for status determination
        $totalPpnKeluaran = $this->taxReport->invoices()->where('type', 'Faktur Keluaran')->sum('ppn');
        $totalPpnMasukan = $this->taxReport->invoices()->where('type', 'Faktur Masuk')->sum('ppn');
        $ppnDikompensasi = $this->taxReport->ppn_dikompensasi_dari_masa_sebelumnya ?? 0;
        $finalAmount = ($totalPpnKeluaran - $totalPpnMasukan) - $ppnDikompensasi;

        // Determine text color based on status
        if ($finalAmount > 0) {
            $textColor = 'FF0000'; // Red for KURANG BAYAR
        } elseif ($finalAmount < 0) {
            $textColor = '008000'; // Green for LEBIH BAYAR
        } else {
            $textColor = 'FF8C00'; // Orange for NIHIL
        }

        $sheet->getStyle("B{$statusRow}:F{$statusRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => $textColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ]);

        // Set row heights for better appearance - UPDATED
        $sheet->getRowDimension($sectionHeaderRow)->setRowHeight(25);
        $sheet->getRowDimension($headerRow)->setRowHeight(20);
        $sheet->getRowDimension($jumlahRow)->setRowHeight(18); // Reduced from 25 to 18
        $sheet->getRowDimension($masukanSectionHeaderRow)->setRowHeight(25);
        $sheet->getRowDimension($masukanHeaderRow)->setRowHeight(20);
        $sheet->getRowDimension($masukanJumlahRow)->setRowHeight(18); // Reduced from 25 to 18
        $sheet->getRowDimension($rekapSectionHeaderRow)->setRowHeight(25);
        $sheet->getRowDimension($statusRow)->setRowHeight(30); // Height for status row

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 3,   // Empty column
            'B' => 8,   // No
            'C' => 35,  // Nama Penjual
            'D' => 15,  // Tanggal
            'E' => 20,  // DPP (wider for Rp prefix)
            'F' => 20,  // PPN (wider for Rp prefix)
        ];
    }

    public function title(): string
    {
        $clientName = $this->taxReport->client->name ?? 'Unknown_Client';
        $monthYear = $this->getIndonesianMonth($this->taxReport->month) . '_' . date('Y');

        // Clean client name for filename (remove special characters and replace spaces with underscores)
        $cleanClientName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $clientName);
        $cleanClientName = preg_replace('/_+/', '_', $cleanClientName); // Replace multiple underscores with single
        $cleanClientName = trim($cleanClientName, '_'); // Remove leading/trailing underscores

        return 'Rekap_Faktur_' . $cleanClientName . '_' . $monthYear;
    }

    private function getIndonesianMonth($month): string
    {
        $monthNames = [
            '01' => 'Januari',
            '1' => 'Januari',
            'january' => 'Januari',
            'jan' => 'Januari',
            '02' => 'Februari',
            '2' => 'Februari',
            'february' => 'Februari',
            'feb' => 'Februari',
            '03' => 'Maret',
            '3' => 'Maret',
            'march' => 'Maret',
            'mar' => 'Maret',
            '04' => 'April',
            '4' => 'April',
            'april' => 'April',
            'apr' => 'April',
            '05' => 'Mei',
            '5' => 'Mei',
            'may' => 'Mei',
            '06' => 'Juni',
            '6' => 'Juni',
            'june' => 'Juni',
            'jun' => 'Juni',
            '07' => 'Juli',
            '7' => 'Juli',
            'july' => 'Juli',
            'jul' => 'Juli',
            '08' => 'Agustus',
            '8' => 'Agustus',
            'august' => 'Agustus',
            'aug' => 'Agustus',
            '09' => 'September',
            '9' => 'September',
            'september' => 'September',
            'sep' => 'September',
            '10' => 'Oktober',
            'october' => 'Oktober',
            'oct' => 'Oktober',
            '11' => 'November',
            'november' => 'November',
            'nov' => 'November',
            '12' => 'Desember',
            'december' => 'Desember',
            'dec' => 'Desember',
        ];

        $cleanMonth = strtolower(trim($month));

        // If it's a date format like "2025-01", extract the month part
        if (preg_match('/\d{4}-(\d{1,2})/', $month, $matches)) {
            $cleanMonth = $matches[1];
        }

        return $monthNames[$cleanMonth] ?? 'Unknown';
    }
}