<?php

namespace App\Exports\Clients\Sheets;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ClientsCoreTaxSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $filters;

    protected $selectedIds;

    public function __construct($filters = [], $selectedIds = null)
    {
        $this->filters = $filters;
        $this->selectedIds = $selectedIds;
    }

    public function collection()
    {
        $query = Client::with(['pic']);
        
        if ($this->selectedIds !== null && !empty($this->selectedIds)) {
            $query->whereIn('id', $this->selectedIds);
        }
        
        return $query->orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Client Name',
            'Core Tax User ID',
            'Core Tax Password Status',
            'Credentials Status',
            'Last Updated',
            'Setup Completeness',
            'Notes',
        ];
    }

    public function map($client): array
    {
        static $counter = 0;
        $counter++;

        $status = 'Not Configured';
        $completeness = '0%';
        $notes = [];

        if ($client->core_tax_user_id && $client->core_tax_password) {
            $status = 'Complete';
            $completeness = '100%';
        } elseif ($client->core_tax_user_id || $client->core_tax_password) {
            $status = 'Incomplete';
            $completeness = '50%';
            if (!$client->core_tax_user_id) {
                $notes[] = 'Missing User ID';
            }
            if (!$client->core_tax_password) {
                $notes[] = 'Missing Password';
            }
        }

        return [
            $counter,
            $client->name,
            $client->core_tax_user_id ?: 'Not Set',
            $client->core_tax_password ? 'Configured' : 'Not Set',
            $status,
            $client->updated_at->format('Y-m-d H:i'),
            $completeness,
            implode('; ', $notes) ?: 'All credentials configured',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 30,  // Client Name
            'C' => 20,  // Core Tax User ID
            'D' => 18,  // Core Tax Password Status
            'E' => 18,  // Credentials Status
            'F' => 18,  // Last Updated
            'G' => 15,  // Setup Completeness
            'H' => 35,  // Notes
        ];
    }

    public function title(): string
    {
        return 'Core Tax Credentials';
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '7B1FA2'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        return [];
    }
}