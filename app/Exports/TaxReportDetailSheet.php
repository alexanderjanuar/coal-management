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

class TaxReportDetailSheet implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    protected $taxReport;
    protected $fakturKeluaranData;
    protected $fakturMasukanData;

    public function __construct(TaxReport $taxReport)
    {
        $this->taxReport = $taxReport;
    }

    public function array(): array
    {
        $data = [];

        // Title row
        $clientName = strtoupper($this->taxReport->client->name ?? 'UNKNOWN CLIENT');
        $monthYear = strtoupper($this->getIndonesianMonth($this->taxReport->month)) . ' ' . date('Y');
        
        $data[] = ['', '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', ''];
        $data[] = ['', 'LAPORAN PAJAK ' . $clientName . ' - ' . $monthYear, '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', ''];

        // Get invoice data
        $fakturKeluaran = $this->taxReport->invoices()
            ->where('type', 'Faktur Keluaran')
            ->orderBy('invoice_date')
            ->get();
            
        $fakturMasukan = $this->taxReport->invoices()
            ->where('type', 'Faktur Masuk')
            ->orderBy('invoice_date')
            ->get();

        // FAKTUR KELUARAN section
        $data[] = ['', 'FAKTUR KELUARAN', '', '', '', '', '', ''];
        $data[] = ['', 'No', 'Nama Penjual', 'Nomor Seri Faktur', 'Tanggal', 'DPP Nilai Lainnya', 'DPP', 'PPN'];

        $totalDppNilaiLainnyaKeluaran = 0;
        $totalDppKeluaran = 0;
        $totalPpnKeluaran = 0;

        foreach ($fakturKeluaran as $index => $invoice) {
            $dppNilaiLainnya = $invoice->dpp_nilai_lainnya ?? 0;
            $data[] = [
                '',
                $index + 1,
                $invoice->company_name,
                $invoice->invoice_number,
                date('n/j/Y', strtotime($invoice->invoice_date)),
                'Rp ' . number_format($dppNilaiLainnya, 0, ',', '.'),
                'Rp ' . number_format($invoice->dpp, 0, ',', '.'),
                'Rp ' . number_format($invoice->ppn, 0, ',', '.'),
            ];

            $totalDppNilaiLainnyaKeluaran += $dppNilaiLainnya;
            $totalDppKeluaran += $invoice->dpp;
            $totalPpnKeluaran += $invoice->ppn;
        }

        // JUMLAH row for Faktur Keluaran
        $data[] = [
            '', 
            '', 
            'JUMLAH', 
            '',
            '', 
            'Rp ' . number_format($totalDppNilaiLainnyaKeluaran, 0, ',', '.'),
            'Rp ' . number_format($totalDppKeluaran, 0, ',', '.'), 
            'Rp ' . number_format($totalPpnKeluaran, 0, ',', '.')
        ];

        // Spacing
        $data[] = ['', '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', ''];

        // FAKTUR MASUKAN section
        $data[] = ['', 'FAKTUR MASUKAN', '', '', '', '', '', ''];
        $data[] = ['', 'No', 'Nama Penjual', 'Nomor Seri Faktur', 'Tanggal', 'DPP Nilai Lainnya', 'DPP', 'PPN'];

        $totalDppNilaiLainnyaMasukan = 0;
        $totalDppMasukan = 0;
        $totalPpnMasukan = 0;

        foreach ($fakturMasukan as $index => $invoice) {
            $dppNilaiLainnya = $invoice->dpp_nilai_lainnya ?? 0;
            $data[] = [
                '',
                $index + 1,
                $invoice->company_name,
                $invoice->invoice_number,
                date('n/j/Y', strtotime($invoice->invoice_date)),
                'Rp ' . number_format($dppNilaiLainnya, 0, ',', '.'),
                'Rp ' . number_format($invoice->dpp, 0, ',', '.'),
                'Rp ' . number_format($invoice->ppn, 0, ',', '.'),
            ];

            $totalDppNilaiLainnyaMasukan += $dppNilaiLainnya;
            $totalDppMasukan += $invoice->dpp;
            $totalPpnMasukan += $invoice->ppn;
        }

        // JUMLAH row for Faktur Masukan
        $data[] = [
            '', 
            '', 
            'JUMLAH', 
            '',
            '', 
            'Rp ' . number_format($totalDppNilaiLainnyaMasukan, 0, ',', '.'),
            'Rp ' . number_format($totalDppMasukan, 0, ',', '.'), 
            'Rp ' . number_format($totalPpnMasukan, 0, ',', '.')
        ];

        // Spacing
        $data[] = ['', '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', ''];

        // REKAP KURANG ATAU LEBIH BAYAR PAJAK section
        $data[] = ['', 'REKAP KURANG ATAU LEBIH BAYAR PAJAK', '', '', '', '', '', ''];

        $kurangLebihBayar = $totalPpnKeluaran - $totalPpnMasukan;
        $ppnDikompensasi = $this->taxReport->ppn_dikompensasi_dari_masa_sebelumnya ?? 0;
        $finalAmount = $kurangLebihBayar - $ppnDikompensasi;

        $data[] = ['', 'TOTAL PPN FAKTUR KELUARAN', '', '', '', '', '', 'Rp ' . number_format($totalPpnKeluaran, 0, ',', '.')];
        $data[] = ['', 'TOTAL PPN FAKTUR MASUKAN', '', '', '', '', '', 'Rp ' . number_format($totalPpnMasukan, 0, ',', '.')];
        $data[] = ['', 'PPN DIKOMPENSASIKAN DARI MASA SEBELUMNYA', '', '', '', '', '', 'Rp ' . number_format($ppnDikompensasi, 0, ',', '.')];
        $data[] = ['', 'TOTAL KURANG/ LEBIH BAYAR PAJAK', '', '', '', '', '', 'Rp ' . number_format($finalAmount, 0, ',', '.')];

        // Status
        if ($finalAmount > 0) {
            $status = 'KURANG BAYAR';
        } elseif ($finalAmount < 0) {
            $status = 'LEBIH BAYAR';
        } else {
            $status = 'NIHIL';
        }

        $data[] = ['', $status, '', '', '', '', '', ''];

        // Additional tax information
        $data[] = ['', '', '', '', '', '', '', ''];
        $data[] = ['', 'INFORMASI PAJAK LAINNYA', '', '', '', '', '', ''];
        
        $totalPph21 = $this->taxReport->incomeTaxs()->sum('pph_21_amount');
        $totalBupot = $this->taxReport->bupots()->sum('bupot_amount');
        
        $data[] = ['', 'TOTAL PPh 21', '', '', '', '', '', 'Rp ' . number_format($totalPph21, 0, ',', '.')];
        $data[] = ['', 'TOTAL BUKTI POTONG', '', '', '', '', '', 'Rp ' . number_format($totalBupot, 0, ',', '.')];
        $data[] = ['', 'GRAND TOTAL SEMUA PAJAK', '', '', '', '', '', 'Rp ' . number_format($totalPpnKeluaran + $totalPpnMasukan + $totalPph21 + $totalBupot, 0, ',', '.')];

        // Store data for styling
        $this->fakturKeluaranData = $fakturKeluaran;
        $this->fakturMasukanData = $fakturMasukan;

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $keluaranDataRowCount = $this->fakturKeluaranData->count();
        $masukanDataRowCount = $this->fakturMasukanData->count();

        // Title
        $sheet->mergeCells('B3:H3');
        $sheet->getStyle('B3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Calculate dynamic positions
        $keluaranSectionRow = 5;
        $keluaranHeaderRow = 6;
        $keluaranDataStart = 7;
        $keluaranDataEnd = $keluaranDataStart + $keluaranDataRowCount - 1;
        $keluaranJumlahRow = $keluaranDataEnd + 1;

        $masukanSectionRow = $keluaranJumlahRow + 3;
        $masukanHeaderRow = $masukanSectionRow + 1;
        $masukanDataStart = $masukanHeaderRow + 1;
        $masukanDataEnd = $masukanDataStart + $masukanDataRowCount - 1;
        $masukanJumlahRow = $masukanDataEnd + 1;

        $rekapSectionRow = $masukanJumlahRow + 3;
        $rekapDataStart = $rekapSectionRow + 1;
        $rekapDataEnd = $rekapDataStart + 3;
        $statusRow = $rekapDataEnd + 1;

        $infoSectionRow = $statusRow + 2;
        $infoDataStart = $infoSectionRow + 1;
        $infoDataEnd = $infoDataStart + 2;

        // Apply styles for each section
        $this->applySectionStyles($sheet, $keluaranSectionRow, $keluaranHeaderRow, $keluaranDataStart, $keluaranDataEnd, $keluaranJumlahRow);
        $this->applySectionStyles($sheet, $masukanSectionRow, $masukanHeaderRow, $masukanDataStart, $masukanDataEnd, $masukanJumlahRow);
        $this->applyRekapStyles($sheet, $rekapSectionRow, $rekapDataStart, $rekapDataEnd, $statusRow);
        $this->applyInfoStyles($sheet, $infoSectionRow, $infoDataStart, $infoDataEnd);

        return [];
    }

    private function applySectionStyles($sheet, $sectionRow, $headerRow, $dataStart, $dataEnd, $jumlahRow)
    {
        // Section header
        $sheet->mergeCells("B{$sectionRow}:H{$sectionRow}");
        $sheet->getStyle("B{$sectionRow}:H{$sectionRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4472C4']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Headers
        $sheet->getStyle("B{$headerRow}:H{$headerRow}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E8E8E8']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Data rows
        if ($dataStart <= $dataEnd) {
            $sheet->getStyle("B{$dataStart}:H{$dataEnd}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);
        }

        // Jumlah row
        $sheet->getStyle("B{$jumlahRow}:H{$jumlahRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4472C4']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
    }

    private function applyRekapStyles($sheet, $sectionRow, $dataStart, $dataEnd, $statusRow)
    {
        // Section header
        $sheet->mergeCells("B{$sectionRow}:H{$sectionRow}");
        $sheet->getStyle("B{$sectionRow}:H{$sectionRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4472C4']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Data rows
        $sheet->getStyle("B{$dataStart}:H{$dataEnd}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Merge description columns
        for ($row = $dataStart; $row <= $dataEnd; $row++) {
            $sheet->mergeCells("B{$row}:G{$row}");
            $sheet->getStyle("B{$row}:G{$row}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
            ]);
        }

        // Status row
        $sheet->mergeCells("B{$statusRow}:H{$statusRow}");
        $sheet->getStyle("B{$statusRow}:H{$statusRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
    }

    private function applyInfoStyles($sheet, $sectionRow, $dataStart, $dataEnd)
    {
        // Section header
        $sheet->mergeCells("B{$sectionRow}:H{$sectionRow}");
        $sheet->getStyle("B{$sectionRow}:H{$sectionRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '28a745']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Data rows
        $sheet->getStyle("B{$dataStart}:H{$dataEnd}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Merge description columns
        for ($row = $dataStart; $row <= $dataEnd; $row++) {
            $sheet->mergeCells("B{$row}:G{$row}");
            $sheet->getStyle("B{$row}:G{$row}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
            ]);
        }
    }

    public function columnWidths(): array
    {
        return [
            'A' => 3,
            'B' => 8,
            'C' => 35,
            'D' => 25,
            'E' => 15,
            'F' => 20,
            'G' => 20,
            'H' => 20,
        ];
    }

    public function title(): string
    {
        $clientName = $this->taxReport->client->name ?? 'Unknown_Client';
        $month = $this->getIndonesianMonth($this->taxReport->month);
        
        // Clean client name for sheet title (Excel has 31 character limit)
        $cleanClientName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $clientName);
        $cleanClientName = substr($cleanClientName, 0, 20);
        
        return $cleanClientName . '_' . $month;
    }

    private function getIndonesianMonth($month): string
    {
        $monthNames = [
            '01' => 'Jan', '1' => 'Jan', 'january' => 'Jan', 'jan' => 'Jan',
            '02' => 'Feb', '2' => 'Feb', 'february' => 'Feb', 'feb' => 'Feb',
            '03' => 'Mar', '3' => 'Mar', 'march' => 'Mar', 'mar' => 'Mar',
            '04' => 'Apr', '4' => 'Apr', 'april' => 'Apr', 'apr' => 'Apr',
            '05' => 'Mei', '5' => 'Mei', 'may' => 'Mei',
            '06' => 'Jun', '6' => 'Jun', 'june' => 'Jun', 'jun' => 'Jun',
            '07' => 'Jul', '7' => 'Jul', 'july' => 'Jul', 'jul' => 'Jul',
            '08' => 'Agu', '8' => 'Agu', 'august' => 'Agu', 'aug' => 'Agu',
            '09' => 'Sep', '9' => 'Sep', 'september' => 'Sep', 'sep' => 'Sep',
            '10' => 'Okt', 'october' => 'Okt', 'oct' => 'Okt',
            '11' => 'Nov', 'november' => 'Nov', 'nov' => 'Nov',
            '12' => 'Des', 'december' => 'Des', 'dec' => 'Des',
        ];

        $cleanMonth = strtolower(trim($month));
        return $monthNames[$cleanMonth] ?? 'Unknown';
    }
}