<?php
// Updated: app/Exports/Clients/ClientsExport.php

namespace App\Exports\Clients;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Database\Eloquent\Builder;

class ClientsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    protected $filters;
    protected $includePasswords;
    protected $selectedIds;

    public function __construct($filters = [], $includePasswords = false, $selectedIds = null)
    {
        $this->filters = $filters;
        $this->includePasswords = $includePasswords;
        $this->selectedIds = $selectedIds;
    }

    public function query()
    {
        $query = Client::query()->with(['pic']);

        // If specific IDs are provided (for selected records), filter by them
        if ($this->selectedIds !== null && !empty($this->selectedIds)) {
            $query->whereIn('id', $this->selectedIds);
        } else {
            // Apply regular filters only if not exporting selected records
            if (!empty($this->filters['pic_id'])) {
                $query->where('pic_id', $this->filters['pic_id']);
            }

            if (!empty($this->filters['status'])) {
                $query->where('status', $this->filters['status']);
            }

            if (!empty($this->filters['pkp_status'])) {
                $query->where('pkp_status', $this->filters['pkp_status']);
            }
        }

        return $query->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'No',
            'Client Name',
            'Email',
            'Address',
            'NPWP',
            'KPP',
            'PKP Status',
            'EFIN',
            'Account Representative',
            'AR Phone',
            'PIC Name',
            'PIC NIK',
            'Core Tax User ID',
            'Core Tax Password',
            'PPN Contract',
            'PPh Contract',
            'Bupot Contract',
            'Client Status',
            'Created Date',
        ];
    }

    public function map($client): array
    {
        static $counter = 0;
        $counter++;

        return [
            $counter,
            $client->name ?? '',
            $client->email ?? '',
            $client->adress ?? '',
            $client->NPWP ?? '',
            $client->KPP ?? '',
            $client->pkp_status ?? 'Non-PKP',
            $client->EFIN ?? '',
            $client->account_representative ?? '',
            $client->ar_phone_number ?? '',
            $client->pic?->name ?? 'No PIC Assigned',
            $client->pic?->nik ?? '',
            $client->core_tax_user_id ?? 'Not Configured',
            $this->includePasswords ? ($client->core_tax_password ?? 'Not Configured') : ($client->core_tax_password ? 'Configured' : 'Not Configured'),
            $client->ppn_contract ? 'Yes' : 'No',
            $client->pph_contract ? 'Yes' : 'No',
            $client->bupot_contract ? 'Yes' : 'No',
            $client->status ?? 'Active',
            $client->created_at ? $client->created_at->format('Y-m-d H:i') : '',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 25,  // Client Name
            'C' => 25,  // Email
            'D' => 30,  // Address
            'E' => 20,  // NPWP
            'F' => 20,  // KPP
            'G' => 15,  // PKP Status
            'H' => 15,  // EFIN
            'I' => 20,  // Account Representative
            'J' => 15,  // AR Phone
            'K' => 20,  // PIC Name
            'L' => 18,  // PIC NIK
            'M' => 18,  // Core Tax User ID
            'N' => 18,  // Core Tax Password
            'O' => 12,  // PPN Contract
            'P' => 12,  // PPh Contract
            'Q' => 12,  // Bupot Contract
            'R' => 12,  // Client Status
            'S' => 18,  // Created Date
        ];
    }

    public function title(): string
    {
        if ($this->selectedIds !== null) {
            $count = count($this->selectedIds);
            return "Selected Clients ({$count} records)";
        }
        return 'Clients Data';
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        // Add export info at the top
        $sheet->insertNewRowBefore(1, 3);
        
        if ($this->selectedIds !== null) {
            $sheet->setCellValue('A1', 'SELECTED CLIENTS EXPORT');
            $sheet->setCellValue('A2', 'Selected Records: ' . count($this->selectedIds));
        } else {
            $sheet->setCellValue('A1', 'CLIENT DATABASE EXPORT');
            $sheet->setCellValue('A2', 'All Records');
        }
        
        $sheet->setCellValue('A3', 'Generated on: ' . now()->format('Y-m-d H:i:s'));

        // Merge title cells
        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->mergeCells('A2:' . $lastColumn . '2');
        $sheet->mergeCells('A3:' . $lastColumn . '3');

        // Title styling
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $this->selectedIds ? '059669' : '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Info styling
        $sheet->getStyle('A2:A3')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 10,
                'color' => ['rgb' => '6B7280'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Header styling (now at row 4)
        $sheet->getStyle('A4:' . $lastColumn . '4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $this->selectedIds ? '059669' : '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // All data borders
        $sheet->getStyle('A4:' . $lastColumn . ($lastRow + 3))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        // Center alignment for specific columns
        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // No
        $sheet->getStyle('G:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // PKP Status
        $sheet->getStyle('O:R')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Contract columns

        // Alternate row colors (starting from row 5)
        for ($i = 5; $i <= ($lastRow + 3); $i++) {
            if (($i - 4) % 2 == 0) {
                $sheet->getStyle('A' . $i . ':' . $lastColumn . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $this->selectedIds ? 'F0FDF4' : 'F9FAFB'],
                    ],
                ]);
            }
        }

        // Freeze header row
        $sheet->freezePane('A5');

        // Set row heights
        $sheet->getRowDimension('1')->setRowHeight(25);
        $sheet->getRowDimension('4')->setRowHeight(20);

        return [];
    }
}