<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProjectMultiSheetExport implements WithMultipleSheets
{
    protected $selectedIds;
    protected $groupBy;

    public function __construct($selectedIds = null, $groupBy = 'none')
    {
        $this->selectedIds = $selectedIds;
        $this->groupBy = $groupBy;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        $query = Project::query()->with(['client', 'sop', 'pic', 'steps.tasks', 'steps.requiredDocuments']);
        
        if ($this->selectedIds !== null && !empty($this->selectedIds)) {
            $query->whereIn('id', $this->selectedIds);
        }
        
        $projects = $query->orderBy('client_id')->orderBy('name')->get();
        
        if ($this->groupBy === 'none') {
            // Single sheet with all data (no grouping)
            $sheets[] = new ProjectSheet($projects->pluck('id')->toArray(), 'Semua Proyek');
        } else {
            // First sheet: Grouped summary with all projects organized by group headers
            $sheets[] = new ProjectGroupedSheet($projects, $this->groupBy);
            
            // Additional sheets: One per group for detailed view
            $grouped = $this->groupProjects($projects);
            
            foreach ($grouped as $groupName => $groupProjects) {
                $sheetName = $this->sanitizeSheetName($groupName ?: 'Tidak Ada');
                $sheets[] = new ProjectSheet($groupProjects->pluck('id')->toArray(), $sheetName);
            }
        }
        
        return $sheets;
    }
    
    protected function groupProjects($projects)
    {
        return match ($this->groupBy) {
            'pic' => $projects->groupBy(fn ($p) => $p->pic?->name ?? 'Tanpa PIC'),
            'status' => $projects->groupBy(fn ($p) => $this->translateStatus($p->status)),
            'priority' => $projects->groupBy(fn ($p) => $this->translatePriority($p->priority)),
            'sop' => $projects->groupBy(fn ($p) => $p->sop?->name ?? 'Tanpa SOP'),
            'client' => $projects->groupBy(fn ($p) => $p->client?->name ?? 'Tanpa Klien'),
            'type' => $projects->groupBy(fn ($p) => $this->translateType($p->type)),
            default => collect(['Semua Proyek' => $projects]),
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
            default => $status ?? 'Unknown',
        };
    }
    
    protected function translatePriority($priority)
    {
        return match ($priority) {
            'urgent' => 'Mendesak',
            'normal' => 'Normal',
            'low' => 'Rendah',
            default => $priority ?? 'Unknown',
        };
    }
    
    protected function translateType($type)
    {
        return match ($type) {
            'single' => 'On Spot',
            'monthly' => 'Bulanan',
            'yearly' => 'Tahunan',
            default => $type ?? 'Unknown',
        };
    }
    
    protected function sanitizeSheetName($name)
    {
        // Excel sheet names have a 31 character limit and can't contain certain characters
        $name = preg_replace('/[\\\\\/\?\*\[\]:\'"]/', '', $name);
        return substr($name, 0, 31);
    }
}
