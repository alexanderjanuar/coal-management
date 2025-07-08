<?php
// Updated: app/Exports/Clients/Sheets/ClientsMainSheet.php (Add selectedIds support)

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

class ClientsMainSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
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
            'Email',
            'Address',
            'NPWP',
            'KPP',
            'PKP Status',
            'EFIN',
            'Status',
            'Created Date',
        ];
    }

    public function map($client): array
    {
        static $counter = 0;
        $counter++;

        return [
            $counter,
            $client->name,
            $client->email ?: '-',
            $client->adress ?: '-',
            $client->NPWP ?: '-',
            $client->KPP ?: '-',
            $client->pkp_status ?: 'Non-PKP',
            $client->EFIN ?: '-',
            $client->status,
            $client->created_at->format('Y-m-d'),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 30,  // Client Name
            'C' => 30,  // Email
            'D' => 35,  // Address
            'E' => 20,  // NPWP
            'F' => 25,  // KPP
            'G' => 15,  // PKP Status
            'H' => 15,  // EFIN
            'I' => 12,  // Status
            'J' => 15,  // Created Date
        ];
    }

    public function title(): string
    {
        if ($this->selectedIds !== null) {
            return 'Selected Main Information';
        }
        return 'Main Information';
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        // Header styling with different color for selected records
        $headerColor = $this->selectedIds !== null ? '059669' : '2E7D32';
        
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $headerColor],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Borders
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