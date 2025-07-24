<?php

// app/Exports/UserDetail/UserSubmittedDocumentsExport.php

namespace App\Exports\UserDetail;

use App\Models\User;
use App\Models\SubmittedDocument;
use Maatwebsite\Excel\Concerns\FromCollection;
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

class UserSubmittedDocumentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    protected $user;
    protected $startDate;
    protected $endDate;
    protected $selectedIds;
    
    public function __construct(User $user, $startDate = null, $endDate = null, $selectedIds = null)
    {
        $this->user = $user;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->selectedIds = $selectedIds;
    }

    public function collection()
    {
        $query = SubmittedDocument::where('user_id', $this->user->id)
            ->with(['requiredDocument.projectStep.project.client', 'user']);
            
        // Apply date filters if provided
        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }
        
        // Apply selected IDs filter if provided (for bulk action)
        if ($this->selectedIds && is_array($this->selectedIds)) {
            $query->whereIn('id', $this->selectedIds);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        $dateRangeInfo = '';
        if ($this->startDate && $this->endDate) {
            $dateRangeInfo = 'Periode: ' . date('d M Y', strtotime($this->startDate)) . ' - ' . date('d M Y', strtotime($this->endDate));
        } elseif ($this->startDate) {
            $dateRangeInfo = 'Dari: ' . date('d M Y', strtotime($this->startDate));
        } elseif ($this->endDate) {
            $dateRangeInfo = 'Sampai: ' . date('d M Y', strtotime($this->endDate));
        } else {
            $dateRangeInfo = 'Semua Periode';
        }
        
        $exportType = $this->selectedIds ? 'LAPORAN DOKUMEN TERPILIH' : 'LAPORAN DOKUMEN YANG DIUNGGAH';
        
        return [
            [$exportType], // Row 1 - Title
            ['Pengguna: ' . $this->user->name], // Row 2 - User info
            ['Email: ' . $this->user->email], // Row 3 - Email
            [$dateRangeInfo], // Row 4 - Date range
            ['Tanggal Laporan: ' . now()->format('d M Y H:i')], // Row 5 - Report date
            ['Total Dokumen: ' . $this->collection()->count()], // Row 6 - Total count
            [], // Row 7 - Empty
            [ // Row 8 - Column headers
                'NO',
                'NAMA FILE',
                'TIPE FILE',
                'KLIEN',
                'TAHAP PROYEK',
                'STATUS',
                'TANGGAL DIUNGGAH',
                'TERAKHIR DIPERBARUI',
                'CATATAN',
                'ALASAN PENOLAKAN'
            ]
        ];
    }

    public function map($document): array
    {
        static $counter = 0;
        $counter++;
        
        $fileName = pathinfo(basename($document->file_path), PATHINFO_FILENAME);
        $fileExtension = strtoupper(pathinfo(basename($document->file_path), PATHINFO_EXTENSION));
        
        $status = match($document->status) {
            'uploaded' => 'Diunggah',
            'pending_review' => 'Menunggu Review',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => ucfirst($document->status)
        };

        return [
            $counter,
            $fileName,
            $fileExtension,
            $document->requiredDocument->projectStep->project->client->name ?? 'N/A',
            $document->requiredDocument->projectStep->name ?? 'N/A',
            $status,
            $document->created_at->format('d M Y H:i'),
            $document->updated_at->format('d M Y H:i'),
            $document->notes ?? '',
            $document->rejection_reason ?? ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Title styling (Row 1)
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1f2937'] // Dark gray
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // User info styling (Rows 2-6)
        for ($row = 2; $row <= 6; $row++) {
            $sheet->mergeCells("A{$row}:J{$row}");
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'f3f4f6'] // Light gray
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ]);
        }

        // Header row styling (Row 8)
        $sheet->getStyle('A8:J8')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '3b82f6'] // Blue
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Data rows styling (From row 9 onwards)
        $lastRow = $this->collection()->count() + 8;
        if ($lastRow > 8) {
            $sheet->getStyle("A9:J{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'd1d5db']
                    ]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true
                ]
            ]);

            // Alternating row colors
            for ($row = 9; $row <= $lastRow; $row++) {
                if (($row - 9) % 2 == 1) {
                    $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'f9fafb'] // Very light gray
                        ]
                    ]);
                }
            }
        }

        // Status column conditional formatting (Column F)
        if ($lastRow > 8) {
            for ($row = 9; $row <= $lastRow; $row++) {
                $statusValue = $sheet->getCell("F{$row}")->getValue();
                $color = match($statusValue) {
                    'Disetujui' => '10b981', // Green
                    'Ditolak' => 'ef4444', // Red
                    'Menunggu Review' => 'f59e0b', // Yellow
                    'Diunggah' => '6b7280', // Gray
                    default => '6b7280'
                };
                
                $sheet->getStyle("F{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $color]
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);
            }
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,  // NO
            'B' => 30, // NAMA FILE
            'C' => 12, // TIPE FILE
            'D' => 25, // KLIEN
            'E' => 25, // TAHAP PROYEK
            'F' => 15, // STATUS
            'G' => 18, // TANGGAL DIUNGGAH
            'H' => 18, // TERAKHIR DIPERBARUI
            'I' => 35, // CATATAN
            'J' => 35, // ALASAN PENOLAKAN
        ];
    }

    public function title(): string
    {
        return 'Dokumen ' . str_replace(' ', '_', $this->user->name);
    }
}