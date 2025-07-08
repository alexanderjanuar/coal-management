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

class ClientsContractsSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
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
            'PKP Status',
            'PPN Contract',
            'PPh Contract',
            'Bupot Contract',
            'Contract File',
            'Total Active Contracts',
            'Contract Notes',
        ];
    }

    public function map($client): array
    {
        static $counter = 0;
        $counter++;

        $activeContracts = 0;
        if ($client->ppn_contract) $activeContracts++;
        if ($client->pph_contract) $activeContracts++;
        if ($client->bupot_contract) $activeContracts++;

        $contractNotes = [];
        if ($client->pkp_status === 'Non-PKP' && !$client->ppn_contract) {
            $contractNotes[] = 'PPN not available for Non-PKP';
        }

        return [
            $counter,
            $client->name,
            $client->pkp_status ?: 'Non-PKP',
            $client->ppn_contract ? 'Active' : 'Inactive',
            $client->pph_contract ? 'Active' : 'Inactive',
            $client->bupot_contract ? 'Active' : 'Inactive',
            $client->contract_file ? 'Available' : 'Not Available',
            $activeContracts,
            implode('; ', $contractNotes) ?: '-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 30,  // Client Name
            'C' => 15,  // PKP Status
            'D' => 15,  // PPN Contract
            'E' => 15,  // PPh Contract
            'F' => 15,  // Bupot Contract
            'G' => 15,  // Contract File
            'H' => 18,  // Total Active Contracts
            'I' => 30,  // Contract Notes
        ];
    }

    public function title(): string
    {
        return 'Contracts';
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
                'startColor' => ['rgb' => 'E65100'],
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