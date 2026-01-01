<?php

namespace App\Exports\TaxReport\PPh;

use App\Models\IncomeTax;
use App\Models\TaxReport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PphTaxListExport implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    protected $taxReportId;
    protected $taxReport;
    protected $incomeTaxes;
    protected $monthName;
    protected $year;
    protected $clientName;

    public function __construct($taxReportId)
    {
        $this->taxReportId = $taxReportId;
        $this->taxReport = TaxReport::with('client')->findOrFail($taxReportId);
        
        // Load income taxes
        $this->incomeTaxes = IncomeTax::where('tax_report_id', $taxReportId)
            ->orderBy('masa_pajak')
            ->orderBy('nomor_pemotongan')
            ->get();
        
        // Extract month and year
        $this->monthName = $this->convertToIndonesianMonth($this->taxReport->month);
        preg_match('/(\d{4})/', $this->taxReport->month, $matches);
        $this->year = $matches[1] ?? date('Y');
        $this->clientName = $this->taxReport->client->name;
    }

    public function array(): array
    {
        $data = [];

        // Row 1: Title
        $data[] = ['REKAPAN PPH 21 ' . strtoupper($this->clientName) . ' ' . strtoupper($this->monthName) . ' ' . $this->year];
        
        // Row 2: Header row
        $data[] = [
            'O',
            'MASA PAJAK',
            'NOMOR PEMOTONGAN',
            'STATUS',
            'NITKU PEMOTONG',
            'JENIS PAJAK',
            'KODE OBJEK PAJAK',
            'NPWP',
            'NAMA',
            'DPP (Rp)',
            'PAJAK PENGHASILAN (Rp)',
            'FASILITAS PAJAK'
        ];

        // Data rows (starting from row 3)
        $totalDpp = 0;
        $totalPph = 0;
        $index = 1;

        foreach ($this->incomeTaxes as $income) {
            $data[] = [
                $index++,
                $this->formatMasaPajak($income->masa_pajak),
                $income->nomor_pemotongan,
                strtoupper($income->status ?? 'NORMAL'),
                $income->nitku,
                $income->jenis_pajak,
                $income->kode_objek_pajak,
                $this->formatNpwp($income->npwp),
                $income->nama,
                $income->dasar_pengenaan_pajak,
                $income->pajak_penghasilan,
                $income->fasilitas_pajak ?? 'Tanpa Fasilitas'
            ];

            $totalDpp += $income->dasar_pengenaan_pajak;
            $totalPph += $income->pajak_penghasilan;
        }

        // Summary rows
        $data[] = [
            '', '', '', '', '', '', '', '',
            'JUMLAH PENDAPATAN KOTOR DAN PAJAK PENGHASILAN YANG DIPOTONG',
            'Rp',
            number_format($totalDpp, 0, ',', '.'),
            ''
        ];

        $data[] = [
            '', '', '', '', '', '', '', '',
            'JUMLAH TOTAL PENDAPATAN KOTOR DAN PAJAK PENGHASILAN YANG DITANGGUNG SERTA PAJAK PENGHASILAN',
            'Rp',
            number_format($totalPph, 0, ',', '.'),
            ''
        ];

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $dataRowCount = $this->incomeTaxes->count();
        $titleRow = 1;
        $headerRow = 2;
        $firstDataRow = 3;
        $lastDataRow = $firstDataRow + $dataRowCount - 1;
        $summaryRow1 = $lastDataRow + 1;
        $summaryRow2 = $summaryRow1 + 1;

        // Title styling (row 1)
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Header row styling (row 2)
        $sheet->getStyle("A{$headerRow}:L{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9D9D9']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
        $sheet->getRowDimension(2)->setRowHeight(35);

        // Data rows styling
        if ($dataRowCount > 0) {
            $sheet->getStyle("A{$firstDataRow}:L{$lastDataRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            // O column - center
            $sheet->getStyle("A{$firstDataRow}:A{$lastDataRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);

            // MASA PAJAK, STATUS - center
            $sheet->getStyle("B{$firstDataRow}:B{$lastDataRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
            
            $sheet->getStyle("D{$firstDataRow}:D{$lastDataRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);

            // DPP and PPh - right align with number format
            $sheet->getStyle("J{$firstDataRow}:K{$lastDataRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
            ]);

            $sheet->getStyle("J{$firstDataRow}:K{$lastDataRow}")
                ->getNumberFormat()
                ->setFormatCode('#,##0');
        }

        // Summary rows styling
        $sheet->getStyle("A{$summaryRow1}:L{$summaryRow2}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // O
            'B' => 12,  // MASA PAJAK
            'C' => 15,  // NOMOR PEMOTONGAN
            'D' => 10,  // STATUS
            'E' => 22,  // NITKU PEMOTONG
            'F' => 12,  // JENIS PAJAK
            'G' => 15,  // KODE OBJEK PAJAK
            'H' => 20,  // NPWP
            'I' => 30,  // NAMA
            'J' => 15,  // DPP (Rp)
            'K' => 22,  // PAJAK PENGHASILAN (Rp)
            'L' => 18,  // FASILITAS PAJAK
        ];
    }

    public function title(): string
    {
        return 'PPh_' . $this->monthName . '_' . $this->year;
    }

    private function formatMasaPajak(?string $masaPajak): string
    {
        if (!$masaPajak) return '-';
        
        try {
            $month = substr($masaPajak, 0, 2);
            $day = substr($masaPajak, 2, 2);
            $year = substr($masaPajak, 4, 4);
            
            return "{$day}/{$month}/{$year}";
        } catch (\Exception $e) {
            return $masaPajak;
        }
    }

    private function formatNpwp(?string $npwp): string
    {
        if (!$npwp) return '-';
        
        $npwp = preg_replace('/[^0-9]/', '', $npwp);
        
        return "'" . $npwp;
    }

    private function convertToIndonesianMonth(string $month): string
    {
        $monthNames = [
            '01' => 'Januari', '1' => 'Januari', 'january' => 'Januari', 'jan' => 'Januari',
            '02' => 'Februari', '2' => 'Februari', 'february' => 'Februari', 'feb' => 'Februari',
            '03' => 'Maret', '3' => 'Maret', 'march' => 'Maret', 'mar' => 'Maret',
            '04' => 'April', '4' => 'April', 'april' => 'April', 'apr' => 'April',
            '05' => 'Mei', '5' => 'Mei', 'may' => 'Mei',
            '06' => 'Juni', '6' => 'Juni', 'june' => 'Juni', 'jun' => 'Juni',
            '07' => 'Juli', '7' => 'Juli', 'july' => 'Juli', 'jul' => 'Juli',
            '08' => 'Agustus', '8' => 'Agustus', 'august' => 'Agustus', 'aug' => 'Agustus',
            '09' => 'September', '9' => 'September', 'september' => 'September', 'sep' => 'September',
            '10' => 'Oktober', 'october' => 'Oktober', 'oct' => 'Oktober',
            '11' => 'November', 'november' => 'November', 'nov' => 'November',
            '12' => 'Desember', 'december' => 'Desember', 'dec' => 'Desember',
        ];

        $cleanMonth = strtolower(trim($month));
        
        if (preg_match('/\d{4}-(\d{1,2})/', $month, $matches)) {
            $cleanMonth = $matches[1];
        }
        
        return $monthNames[$cleanMonth] ?? ucfirst($cleanMonth);
    }
}