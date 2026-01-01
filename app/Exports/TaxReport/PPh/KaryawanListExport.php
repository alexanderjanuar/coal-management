<?php

namespace App\Exports\TaxReport\Pph;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KaryawanListExport implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    protected $clientId;
    protected $clientName;
    protected $employees;

    public function __construct($clientId, $clientName)
    {
        $this->clientId = $clientId;
        $this->clientName = $clientName;
        
        // Load all employees for this client
        $this->employees = Employee::where('client_id', $clientId)
            ->orderBy('status', 'asc') // Active first
            ->orderBy('name', 'asc')
            ->get();
    }

    public function array(): array
    {
        $data = [];

        // Title row
        $data[] = ["DATA KARYAWAN {$this->clientName}"];
        
        // Header row
        $data[] = [
            'No',
            'Nama Karyawan',
            'NPWP',
            'Posisi',
            'Tipe',
            'Status Pernikahan',
            'Tanggungan',
            'Gaji Bulanan',
            'Status Aktif'
        ];

        // Data rows
        $index = 1;
        foreach ($this->employees as $employee) {
            $maritalStatus = $employee->marital_status === 'married' ? 'K' : 'TK';
            $tanggungan = $employee->marital_status === 'married' ? $employee->k : $employee->tk;
            
            $data[] = [
                $index++,
                $employee->name,
                $this->formatNpwp($employee->npwp),
                $employee->position ?? '-',
                $employee->type,
                $maritalStatus === 'K' ? 'Menikah' : 'Belum Menikah',
                "{$maritalStatus}/{$tanggungan}",
                $employee->salary ?? 0,
                $employee->status === 'active' ? 'Aktif' : 'Tidak Aktif'
            ];
        }

        // Summary row
        $data[] = [];
        
        $activeCount = $this->employees->where('status', 'active')->count();
        $inactiveCount = $this->employees->where('status', 'inactive')->count();
        $totalSalary = $this->employees->where('status', 'active')->sum('salary');
        
        $data[] = [
            '', '', '', '', '', '',
            'Total Karyawan Aktif:',
            $activeCount,
            ''
        ];
        
        $data[] = [
            '', '', '', '', '', '',
            'Total Karyawan Tidak Aktif:',
            $inactiveCount,
            ''
        ];
        
        $data[] = [
            '', '', '', '', '', '',
            'Total Gaji (Aktif):',
            'Rp ' . number_format($totalSalary, 0, ',', '.'),
            ''
        ];

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $dataRowCount = $this->employees->count();
        $titleRow = 1;
        $headerRow = 2;
        $firstDataRow = 3;
        $lastDataRow = $firstDataRow + $dataRowCount - 1;
        $summaryStartRow = $lastDataRow + 2;

        // Title styling (row 1)
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Header row styling (row 2)
        $sheet->getStyle("A{$headerRow}:I{$headerRow}")->applyFromArray([
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
        $sheet->getRowDimension(2)->setRowHeight(30);

        // Data rows styling
        if ($dataRowCount > 0) {
            $sheet->getStyle("A{$firstDataRow}:I{$lastDataRow}")->applyFromArray([
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

            // No column - center
            $sheet->getStyle("A{$firstDataRow}:A{$lastDataRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);

            // Gaji column - right align with number format
            $sheet->getStyle("H{$firstDataRow}:H{$lastDataRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
            ]);

            $sheet->getStyle("H{$firstDataRow}:H{$lastDataRow}")
                ->getNumberFormat()
                ->setFormatCode('#,##0');
        }

        // Summary section styling
        if ($summaryStartRow > 0) {
            for ($row = $summaryStartRow; $row <= $summaryStartRow + 2; $row++) {
                $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'F2F2F2']
                    ],
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ]);
            }
        }

        return [];
    }

    public function columnWidths(): array
    {   
        return [
            'A' => 5,   // No
            'B' => 25,  // Nama Karyawan
            'C' => 20,  // NPWP
            'D' => 20,  // Posisi
            'E' => 15,  // Tipe
            'F' => 18,  // Status Pernikahan
            'G' => 12,  // Tanggungan
            'H' => 18,  // Gaji Bulanan
            'I' => 15,  // Status Aktif
        ];
    }

    public function title(): string
    {
        return 'Data_Karyawan';
    }

    private function formatNpwp(?string $npwp): string
    {
        if (!$npwp) return '-';
        
        // Remove any existing formatting
        $npwp = preg_replace('/[^0-9]/', '', $npwp);
        
        // Return as string with apostrophe prefix to force text format
        return "'" . $npwp;
    }
}