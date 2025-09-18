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
        $query = Client::query()->with(['pic', 'accountRepresentative', 'clientCredential']);

        // Jika ID spesifik disediakan (untuk record terpilih), filter berdasarkan ID tersebut
        if ($this->selectedIds !== null && !empty($this->selectedIds)) {
            $query->whereIn('id', $this->selectedIds);
        } else {
            // Terapkan filter reguler hanya jika tidak mengekspor record terpilih
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
            'Nama Klien',
            'Email Klien',
            'Alamat',
            'NPWP',
            'Status PKP',
            'EFIN',
            'Account Representative',
            'Telepon AR',
            'Email AR',
            'KPP',
            'Nama PIC',
            'NIK PIC',
            'Core Tax User ID',
            'Core Tax Password',
            'Akun DJP',
            'Password DJP',
            'Email Account',
            'Password Email',
            'Status Klien',
            'Tanggal Dibuat',
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
            $client->pkp_status ?? 'Non-PKP',
            $client->EFIN ?? '',
            $client->accountRepresentative?->name ?? '',
            $client->accountRepresentative?->phone_number ?? '',
            $client->accountRepresentative?->email ?? '',
            $client->accountRepresentative?->kpp ?? '',
            $client->pic?->name ?? '',
            $client->pic?->nik ?? '',
            $client->clientCredential?->core_tax_user_id ?? '',
            $client->clientCredential?->core_tax_password ?? '',
            $client->clientCredential?->djp_account ?? '',
            $client->clientCredential?->djp_password ?? '',
            $client->clientCredential?->email ?? '',
            $client->clientCredential?->email_password ?? '',
            $client->status ?? 'Active',
            $client->created_at ? $client->created_at->format('d/m/Y H:i') : '',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 25,  // Nama Klien
            'C' => 25,  // Email Klien
            'D' => 30,  // Alamat
            'E' => 20,  // NPWP
            'F' => 15,  // Status PKP
            'G' => 15,  // EFIN
            'H' => 20,  // Account Representative
            'I' => 15,  // Telepon AR
            'J' => 20,  // Email AR
            'K' => 20,  // KPP
            'L' => 20,  // Nama PIC
            'M' => 18,  // NIK PIC
            'N' => 18,  // Core Tax User ID
            'O' => 18,  // Core Tax Password
            'P' => 15,  // Akun DJP
            'Q' => 15,  // Password DJP
            'R' => 20,  // Email Account
            'S' => 15,  // Password Email
            'T' => 12,  // Status Klien
            'U' => 18,  // Tanggal Dibuat
        ];
    }

    public function title(): string
    {
        if ($this->selectedIds !== null) {
            $count = count($this->selectedIds);
            return "Klien Terpilih ({$count} record)";
        }
        return 'Data Klien';
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        // Tambahkan info ekspor di bagian atas
        $sheet->insertNewRowBefore(1, 3);
        
        if ($this->selectedIds !== null) {
            $sheet->setCellValue('A1', 'EKSPOR KLIEN TERPILIH');
            $sheet->setCellValue('A2', 'Record Terpilih: ' . count($this->selectedIds));
        } else {
            $sheet->setCellValue('A1', 'EKSPOR DATABASE KLIEN');
            $sheet->setCellValue('A2', 'Semua Record');
        }
        
        $sheet->setCellValue('A3', 'Dibuat pada: ' . now()->format('d/m/Y H:i:s'));

        // Gabungkan sel judul
        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->mergeCells('A2:' . $lastColumn . '2');
        $sheet->mergeCells('A3:' . $lastColumn . '3');

        // Styling judul
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

        // Styling info
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

        // Styling header (sekarang di baris 4)
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

        // Border untuk semua data
        $sheet->getStyle('A4:' . $lastColumn . ($lastRow + 3))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        // Alignment tengah untuk kolom tertentu
        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // No
        $sheet->getStyle('F:F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Status PKP
        $sheet->getStyle('T:T')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Status Klien

        // Warna baris bergantian (mulai dari baris 5)
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

        // Bekukan baris header
        $sheet->freezePane('A5');

        // Set tinggi baris
        $sheet->getRowDimension('1')->setRowHeight(25);
        $sheet->getRowDimension('4')->setRowHeight(20);

        return [];
    }
}