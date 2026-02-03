<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Str;

class ProjectGroupedSheet implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    protected $projects;
    protected $groupBy;
    protected $groupedData = [];
    protected $rowCount = 0;

    public function __construct($projects, $groupBy)
    {
        $this->projects = $projects;
        $this->groupBy = $groupBy;
    }

    public function array(): array
    {
        $data = [];
        
        // Title row
        $data[] = ['', '', '', '', '', '', '', '', '', ''];
        $data[] = ['', 'REKAP PROYEK - DIKELOMPOKKAN BERDASARKAN ' . strtoupper($this->getGroupByLabel()), '', '', '', '', '', '', '', ''];
        $data[] = ['', 'Dibuat: ' . now()->format('d/m/Y H:i:s'), '', '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', '', '', ''];
        
        // Group projects
        $grouped = $this->groupProjects();
        
        foreach ($grouped as $groupName => $groupProjects) {
            // Group header row
            $data[] = ['', $groupName . ' (' . count($groupProjects) . ' proyek)', '', '', '', '', '', '', '', ''];
            
            // Column headers
            $data[] = ['', 'No', 'Nama Klien', 'Nama Proyek', 'SOP', 'PIC', 'Status', 'Prioritas', 'Tipe', 'Progress'];
            
            // Data rows
            $counter = 0;
            foreach ($groupProjects as $project) {
                $counter++;
                $data[] = [
                    '',
                    $counter,
                    $project->client?->name ?? '',
                    $project->name ?? '',
                    $project->sop?->name ?? '',
                    $project->pic?->name ?? '',
                    $this->translateStatus($project->status),
                    $this->translatePriority($project->priority),
                    $this->translateType($project->type),
                    $this->calculateProgress($project) . '%',
                ];
            }
            
            // Spacing after group
            $data[] = ['', '', '', '', '', '', '', '', '', ''];
        }
        
        $this->rowCount = count($data);
        $this->groupedData = $grouped;
        
        return $data;
    }
    
    protected function groupProjects()
    {
        return match ($this->groupBy) {
            'pic' => $this->projects->groupBy(fn ($p) => $p->pic?->name ?? 'Tanpa PIC'),
            'status' => $this->projects->groupBy(fn ($p) => $this->translateStatus($p->status)),
            'priority' => $this->projects->groupBy(fn ($p) => $this->translatePriority($p->priority)),
            'sop' => $this->projects->groupBy(fn ($p) => $p->sop?->name ?? 'Tanpa SOP'),
            'client' => $this->projects->groupBy(fn ($p) => $p->client?->name ?? 'Tanpa Klien'),
            'type' => $this->projects->groupBy(fn ($p) => $this->translateType($p->type)),
            default => collect(['Semua Proyek' => $this->projects]),
        };
    }
    
    protected function getGroupByLabel()
    {
        return match ($this->groupBy) {
            'pic' => 'PIC',
            'status' => 'Status',
            'priority' => 'Prioritas',
            'sop' => 'SOP',
            'client' => 'Klien',
            'type' => 'Tipe Proyek',
            default => 'Semua',
        };
    }
    
    protected function translateStatus($status)
    {
        return match ($status) {
            'draft' => 'Draft',
            'in_progress' => 'Sedang Dikerjakan',
            'on_hold' => 'Ditunda',
            'completed' => 'Selesai',
            'canceled' => 'Dibatalkan',
            default => Str::title(str_replace('_', ' ', $status ?? '')),
        };
    }
    
    protected function translatePriority($priority)
    {
        return match ($priority) {
            'urgent' => 'Mendesak',
            'normal' => 'Normal',
            'low' => 'Rendah',
            default => Str::title($priority ?? ''),
        };
    }
    
    protected function translateType($type)
    {
        return match ($type) {
            'single' => 'On Spot',
            'monthly' => 'Bulanan',
            'yearly' => 'Tahunan',
            default => Str::title($type ?? ''),
        };
    }
    
    protected function calculateProgress($project)
    {
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

        return $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 3,
            'B' => 6,
            'C' => 25,
            'D' => 30,
            'E' => 20,
            'F' => 18,
            'G' => 18,
            'H' => 12,
            'I' => 12,
            'J' => 10,
        ];
    }

    public function title(): string
    {
        return 'Rekap ' . $this->getGroupByLabel();
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = 'J';
        
        // Main title
        $sheet->mergeCells('B2:J2');
        $sheet->getStyle('B2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        
        // Subtitle
        $sheet->mergeCells('B3:J3');
        $sheet->getStyle('B3')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '6B7280']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        
        // Find and style group headers and column headers
        $currentRow = 5;
        $grouped = $this->groupProjects();
        
        foreach ($grouped as $groupName => $groupProjects) {
            // Group header styling (merged, colored)
            $sheet->mergeCells("B{$currentRow}:J{$currentRow}");
            $sheet->getStyle("B{$currentRow}:J{$currentRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $currentRow++;
            
            // Column headers styling
            $sheet->getStyle("B{$currentRow}:J{$currentRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6B7280']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $currentRow++;
            
            // Data rows styling
            $dataStartRow = $currentRow;
            $dataEndRow = $currentRow + count($groupProjects) - 1;
            
            if ($dataStartRow <= $dataEndRow) {
                $sheet->getStyle("B{$dataStartRow}:J{$dataEndRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                
                // Center specific columns
                $sheet->getStyle("B{$dataStartRow}:B{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G{$dataStartRow}:J{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Alternating row colors
                for ($i = $dataStartRow; $i <= $dataEndRow; $i++) {
                    if (($i - $dataStartRow) % 2 == 1) {
                        $sheet->getStyle("B{$i}:J{$i}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9FAFB']],
                        ]);
                    }
                }
            }
            
            $currentRow = $dataEndRow + 2; // +2 for spacing row
        }
        
        // Freeze panes
        $sheet->freezePane('B5');
        
        // Row heights
        $sheet->getRowDimension('2')->setRowHeight(25);
        
        return [];
    }
}
