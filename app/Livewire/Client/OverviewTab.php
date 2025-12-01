<?php

namespace App\Livewire\Client;

use Livewire\Component;
use App\Models\Client;
use App\Models\Project;
use App\Models\TaxReport;
use App\Models\UserClient;
use App\Models\ClientDocument;
use App\Models\TaxCalculationSummary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class OverviewTab extends Component
{
    public Collection $clients;
    public ?int $selectedClientId = null;
    public bool $isLoading = true;
    
    protected $listeners = ['refreshOverview' => '$refresh'];
    
    public function mount()
    {
        $this->loadClientData();
    }
    
    protected function loadClientData()
    {
        $this->isLoading = true;
        
        // Get all clients for current user
        $this->clients = UserClient::where('user_id', auth()->id())
            ->with('client:id,name,logo,status')
            ->get()
            ->pluck('client')
            ->filter();
        
        // Set default selected client (first one)
        if ($this->clients->isNotEmpty() && !$this->selectedClientId) {
            $this->selectedClientId = $this->clients->first()->id;
        }
        
        $this->isLoading = false;
    }
    
    /**
     * Get selected client
     */
    public function getSelectedClientProperty(): ?Client
    {
        if (!$this->selectedClientId) {
            return null;
        }
        
        return $this->clients->firstWhere('id', $this->selectedClientId);
    }
    
    /**
     * Change selected client
     */
    public function selectClient(int $clientId)
    {
        $this->selectedClientId = $clientId;
        $this->dispatch('client-changed', clientId: $clientId);
    }
    
    /**
     * Get active projects for selected client
     */
    public function getActiveProjectsProperty(): Collection
    {
        if (!$this->selectedClientId) {
            return collect();
        }
        
        return Project::where('client_id', $this->selectedClientId)
            ->whereNotIn('status', ['completed', 'completed (Not Payed Yet)', 'canceled'])
            ->with([
                'client:id,name',
                'pic:id,name',
            ])
            ->orderByRaw("FIELD(status, 'review', 'in_progress', 'analysis', 'draft')")
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();
    }
    
    /**
     * Get recent documents for selected client
     */
    public function getRecentDocumentsProperty(): Collection
    {
        if (!$this->selectedClientId) {
            return collect();
        }
        
        return ClientDocument::where('client_id', $this->selectedClientId)
            ->with([
                'user:id,name,avatar_url',
                'sopLegalDocument:id,name,category'
            ])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }
    
    /**
     * Get upcoming deadlines
     */
    public function getUpcomingDeadlinesProperty(): Collection
    {
        if (!$this->selectedClientId) {
            return collect();
        }
        
        return Project::where('client_id', $this->selectedClientId)
            ->whereIn('status', ['draft', 'analysis', 'in_progress', 'review'])
            ->where('due_date', '>=', now())
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get(['id', 'name', 'due_date', 'priority', 'status', 'client_id']);
    }
    
    /**
     * Get latest tax report with summaries
     */
    public function getLatestTaxReportProperty(): ?TaxReport
    {
        if (!$this->selectedClientId) {
            return null;
        }
        
        return TaxReport::where('client_id', $this->selectedClientId)
            ->with([
                'taxCalculationSummaries' => function($query) {
                    $query->orderBy('tax_type');
                }
            ])
            ->orderBy('month', 'desc')
            ->first();
    }
    
    /**
     * Get project statistics
     */
    public function getProjectStatsProperty(): array
    {
        if (!$this->selectedClientId) {
            return [
                'total' => 0,
                'active' => 0,
                'completed' => 0,
                'pending' => 0,
            ];
        }
        
        $projects = Project::where('client_id', $this->selectedClientId)
            ->select('status')
            ->get();
        
        $total = $projects->count();
        $statusCounts = $projects->countBy('status');
        
        return [
            'total' => $total,
            'active' => ($statusCounts->get('in_progress', 0) + $statusCounts->get('review', 0)),
            'completed' => ($statusCounts->get('completed', 0) + $statusCounts->get('completed (Not Payed Yet)', 0)),
            'pending' => ($statusCounts->get('draft', 0) + $statusCounts->get('analysis', 0)),
        ];
    }
    
    /**
     * Get tax report statistics
     */
    public function getTaxReportStatsProperty(): array
    {
        if (!$this->selectedClientId) {
            return [
                'total' => 0,
                'reported' => 0,
                'pending' => 0,
                'completion_percentage' => 0,
            ];
        }
        
        $summaries = TaxCalculationSummary::whereHas('taxReport', function($query) {
                $query->where('client_id', $this->selectedClientId);
            })
            ->select('report_status')
            ->get()
            ->countBy('report_status');
        
        $total = $summaries->sum();
        $reported = $summaries->get('Sudah Lapor', 0);
        
        return [
            'total' => $total,
            'reported' => $reported,
            'pending' => $summaries->get('Belum Lapor', 0),
            'completion_percentage' => $total > 0 ? round(($reported / $total) * 100) : 0,
        ];
    }
    
    /**
     * Get document statistics
     */
    public function getDocumentStatsProperty(): array
    {
        if (!$this->selectedClientId) {
            return [
                'total' => 0,
                'valid' => 0,
                'expired' => 0,
                'pending' => 0,
            ];
        }
        
        $documents = ClientDocument::where('client_id', $this->selectedClientId)
            ->select('status')
            ->get()
            ->countBy('status');
        
        return [
            'total' => $documents->sum(),
            'valid' => $documents->get('valid', 0),
            'expired' => $documents->get('expired', 0),
            'pending' => $documents->get('pending', 0),
        ];
    }
    
    /**
     * Get pending required documents count
     */
    public function getPendingDocumentsCountProperty(): int
    {
        if (!$this->selectedClientId) {
            return 0;
        }
        
        return DB::table('required_documents')
            ->join('project_steps', 'required_documents.project_step_id', '=', 'project_steps.id')
            ->join('projects', 'project_steps.project_id', '=', 'projects.id')
            ->where('projects.client_id', $this->selectedClientId)
            ->whereIn('required_documents.status', ['draft', 'uploaded', 'pending_review'])
            ->count();
    }
    
    /**
     * Download document
     */
    public function downloadDocument(int $documentId)
    {
        $document = ClientDocument::findOrFail($documentId);
        
        // Check authorization
        if ($document->client_id !== $this->selectedClientId) {
            abort(403);
        }
        
        if ($document->file_path && \Storage::disk('public')->exists($document->file_path)) {
            return \Storage::disk('public')->download(
                $document->file_path,
                $document->original_filename ?? basename($document->file_path)
            );
        }
        
        session()->flash('error', 'File tidak ditemukan');
    }
    
    /**
     * Refresh the overview data
     */
    public function refresh()
    {
        $this->loadClientData();
        $this->dispatch('overview-refreshed');
    }

    public function render()
    {
        return view('livewire.client.overview-tab', [
            'selectedClient' => $this->selectedClient,
            'activeProjects' => $this->activeProjects,
            'recentDocuments' => $this->recentDocuments,
            'upcomingDeadlines' => $this->upcomingDeadlines,
            'latestTaxReport' => $this->latestTaxReport,
            'projectStats' => $this->projectStats,
            'taxReportStats' => $this->taxReportStats,
            'documentStats' => $this->documentStats,
            'pendingDocumentsCount' => $this->pendingDocumentsCount,
        ]);
    }
}