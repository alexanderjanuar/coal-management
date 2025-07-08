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

class ClientsPICSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
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
            'PIC Name',
            'PIC NIK',
            'PIC Email',
            'PIC Status',
            'Account Representative',
            'AR Phone',
            'Assignment Date',
            'PIC Total Clients',
        ];
    }

    public function map($client): array
    {
        static $counter = 0;
        $counter++;

        return [
            $counter,
            $client->name,
            $client->pic?->name ?: 'No PIC Assigned',
            $client->pic?->nik ?: '-',
            $client->pic?->email ?: '-',
            $client->pic?->status ?: '-',
            $client->account_representative ?: '-',
            $client->ar_phone_number ?: '-',
            $client->pic ? $client->updated_at->format('Y-m-d') : '-',
            $client->pic ? $client->pic->clients()->count() : 0,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 30,  // Client Name
            'C' => 25,  // PIC Name
            'D' => 20,  // PIC NIK
            'E' => 30,  // PIC Email
            'F' => 12,  // PIC Status
            'G' => 25,  // Account Representative
            'H' => 15,  // AR Phone
            'I' => 15,  // Assignment Date
            'J' => 15,  // PIC Total Clients
        ];
    }

    public function title(): string
    {
        return 'PIC Information';
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
                'startColor' => ['rgb' => '1976D2'],
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