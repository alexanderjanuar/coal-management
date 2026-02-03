<?php

namespace App\Exports;

use App\Models\Project;
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
use Illuminate\Support\Str;

class ProjectSheet implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    protected $projectIds;
    protected $sheetTitle;
    protected static $counter = 0;

    public function __construct($projectIds, $sheetTitle)
    {
        $this->projectIds = $projectIds;
        $this->sheetTitle = $sheetTitle;
        self::$counter = 0; // Reset counter for each sheet
    }

    public function query()
    {
        return Project::query()
            ->with(['client', 'sop', 'pic', 'steps.tasks', 'steps.requiredDocuments'])
            ->whereIn('id', $this->projectIds)
            ->orderBy('client_id')
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Klien',
            'Nama Proyek',
            'SOP',
            'PIC',
            'Status',
            'Prioritas',
            'Tipe Proyek',
            'Tanggal Jatuh Tempo',
            'Progress',
        ];
    }

    public function map($project): array
    {
        self::$counter++;

        // Calculate progress
        $steps = $project->steps;
        $totalItems = 0;
        $completedItems = 0;

        foreach ($steps as $step) {
            $totalItems++;
            if ($step->status === 'completed') {
                $completedItems++;
            }

            $tasks = $step->tasks;
            $totalItems += $tasks->count();
            $completedItems += $tasks->where('status', 'completed')->count();

            $documents = $step->requiredDocuments;
            $totalItems += $documents->count();
            $completedItems += $documents->where('status', 'approved')->count();
        }

        $percentage = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;

        // Translate status
        $statusTranslation = match ($project->status) {
            'draft' => 'Draft',
            'in_progress' => 'Sedang Dikerjakan',
            'on_hold' => 'Ditunda',
            'completed' => 'Selesai',
            'canceled' => 'Dibatalkan',
            default => Str::title(str_replace('_', ' ', $project->status)),
        };

        // Translate priority
        $priorityTranslation = match ($project->priority) {
            'urgent' => 'Mendesak',
            'normal' => 'Normal',
            'low' => 'Rendah',
            default => Str::title($project->priority ?? ''),
        };

        // Translate type
        $typeTranslation = match ($project->type) {
            'single' => 'On Spot',
            'monthly' => 'Bulanan',
            'yearly' => 'Tahunan',
            default => Str::title($project->type ?? ''),
        };

        return [
            self::$counter,
            $project->client?->name ?? '',
            $project->name ?? '',
            $project->sop?->name ?? '',
            $project->pic?->name ?? '',
            $statusTranslation,
            $priorityTranslation,
            $typeTranslation,
            $project->due_date ? date('d/m/Y', strtotime($project->due_date)) : '',
            $percentage . '%',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 25,  // Nama Klien
            'C' => 30,  // Nama Proyek
            'D' => 25,  // SOP
            'E' => 18,  // PIC
            'F' => 18,  // Status
            'G' => 12,  // Prioritas
            'H' => 12,  // Tipe Proyek
            'I' => 18,  // Tanggal Jatuh Tempo
            'J' => 10,  // Progress
        ];
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        // Add export info at the top
        $sheet->insertNewRowBefore(1, 2);
        
        $sheet->setCellValue('A1', strtoupper($this->sheetTitle));
        $sheet->setCellValue('A2', 'Jumlah: ' . count($this->projectIds) . ' proyek | Dibuat: ' . now()->format('d/m/Y H:i'));

        // Merge title cells
        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->mergeCells('A2:' . $lastColumn . '2');

        // Title styling
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Info styling
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 10,
                'color' => ['rgb' => '6B7280'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Header styling (now at row 3)
        $sheet->getStyle('A3:' . $lastColumn . '3')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Border for all data
        $sheet->getStyle('A3:' . $lastColumn . ($lastRow + 2))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        // Center alignment for specific columns
        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F:F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J:J')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Alternating row colors (starting from row 4)
        for ($i = 4; $i <= ($lastRow + 2); $i++) {
            if (($i - 3) % 2 == 0) {
                $sheet->getStyle('A' . $i . ':' . $lastColumn . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F0FDF4'],
                    ],
                ]);
            }
        }

        // Freeze header pane
        $sheet->freezePane('A4');

        // Set row heights
        $sheet->getRowDimension('1')->setRowHeight(22);
        $sheet->getRowDimension('3')->setRowHeight(20);

        return [];
    }
}
