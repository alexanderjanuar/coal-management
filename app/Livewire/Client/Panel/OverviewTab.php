<?php

namespace App\Livewire\Client\Panel;

use Livewire\Component;
use App\Models\Client;
use App\Models\Project;
use App\Models\TaxReport;
use App\Models\UserClient;
use App\Models\ClientDocument;
use App\Models\ClientDocumentRequirement;
use App\Models\SopLegalDocument;
use App\Models\TaxCalculationSummary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class OverviewTab extends Component
{
    public Collection $clients;
    public ?int $selectedClientId = null;
    public bool $isLoading = true;
    
    // Cache duration in seconds (5 minutes)
    protected int $cacheDuration = 300;
    
    protected $listeners = ['refreshOverview' => 'refresh'];
    
    public function mount()
    {
        $this->loadClientData();
    }
    
    protected function loadClientData()
    {
        $this->isLoading = true;
        
        // Get all clients for current user - optimized with single query
        $this->clients = UserClient::query()
            ->where('user_id', auth()->id())
            ->with(['client' => fn($q) => $q->select('id', 'name', 'logo', 'status', 'client_type')])
            ->get()
            ->pluck('client')
            ->filter();
        
        // Set default selected client
        if ($this->clients->isNotEmpty() && !$this->selectedClientId) {
            $this->selectedClientId = $this->clients->first()->id;
        }
        
        $this->isLoading = false;
    }
    
    /**
     * Get selected client with caching
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
        $this->clearClientCache();
        $this->dispatch('client-changed', clientId: $clientId);
    }
    
    /**
     * Clear cached data for current client
     */
    protected function clearClientCache()
    {
        if (!$this->selectedClientId) {
            return;
        }
        
        $userId = auth()->id();
        $clientId = $this->selectedClientId;
        
        $cacheKeys = [
            "overview_projects_{$userId}_{$clientId}",
            "overview_documents_{$userId}_{$clientId}",
            "overview_doc_checklist_{$userId}_{$clientId}",
            "overview_requirements_{$userId}_{$clientId}",
            "overview_tax_report_{$userId}_{$clientId}",
            "overview_stats_{$userId}_{$clientId}",
        ];
        
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
    
    /**
     * Get all projects for selected client with optimized query
     * Sorted by status priority: active first, then completed
     */
    public function getProjectsProperty(): Collection
    {
        if (!$this->selectedClientId) {
            return collect();
        }
        
        $userId = auth()->id();
        $clientId = $this->selectedClientId;
        
        return Cache::remember(
            "overview_projects_{$userId}_{$clientId}",
            $this->cacheDuration,
            function () use ($clientId) {
                // Using raw SQL for custom status ordering
                $statusOrder = DB::raw("FIELD(status, 
                    'in_progress', 
                    'review', 
                    'analysis', 
                    'draft', 
                    'on_hold',
                    'completed', 
                    'completed (Not Payed Yet)', 
                    'canceled'
                )");
                
                return Project::query()
                    ->where('client_id', $clientId)
                    ->select(['id', 'client_id', 'name', 'status', 'priority', 'due_date', 'pic_id'])
                    ->with(['pic:id,name'])
                    ->orderBy($statusOrder)
                    ->orderBy('due_date', 'asc')
                    ->get();
            }
        );
    }
    
    /**
     * Get recent uploaded documents with optimized query
     */
    public function getRecentDocumentsProperty(): Collection
    {
        if (!$this->selectedClientId) {
            return collect();
        }
        
        $userId = auth()->id();
        $clientId = $this->selectedClientId;
        
        return Cache::remember(
            "overview_documents_{$userId}_{$clientId}",
            $this->cacheDuration,
            function () use ($clientId) {
                return ClientDocument::query()
                    ->where('client_id', $clientId)
                    ->whereNotNull('file_path')
                    ->select([
                        'id', 'client_id', 'user_id', 'sop_legal_document_id',
                        'file_path', 'original_filename', 'status', 'expired_at', 'created_at'
                    ])
                    ->with([
                        'user:id,name',
                        'sopLegalDocument:id,name'
                    ])
                    ->latest('created_at')
                    ->limit(10)
                    ->get();
            }
        );
    }
    
    /**
     * Get document checklist based on SOP Legal Documents
     * Shows which documents are required and their upload status
     */
    public function getDocumentChecklistProperty(): Collection
    {
        if (!$this->selectedClientId) {
            return collect();
        }
        
        $client = $this->selectedClient;
        
        if (!$client) {
            return collect();
        }
        
        $userId = auth()->id();
        $clientId = $this->selectedClientId;
        $clientType = $client->client_type;
        
        return Cache::remember(
            "overview_doc_checklist_{$userId}_{$clientId}",
            $this->cacheDuration,
            function () use ($clientId, $clientType) {
                // Get applicable SOP documents for this client type
                $sopDocuments = SopLegalDocument::query()
                    ->forClientType($clientType)
                    ->active()
                    ->orderBy('category')
                    ->orderBy('order')
                    ->select(['id', 'name', 'description', 'category', 'is_required'])
                    ->get();
                
                // Get all uploaded documents for this client (indexed by sop_legal_document_id)
                $uploadedDocs = ClientDocument::query()
                    ->where('client_id', $clientId)
                    ->whereNotNull('sop_legal_document_id')
                    ->whereNotNull('file_path')
                    ->select([
                        'id', 'sop_legal_document_id', 'file_path', 
                        'original_filename', 'status', 'user_id', 'updated_at'
                    ])
                    ->with(['user:id,name'])
                    ->get()
                    ->keyBy('sop_legal_document_id');
                
                // Map each SOP document with its upload status
                return $sopDocuments->map(function ($sopDoc) use ($uploadedDocs) {
                    $clientDoc = $uploadedDocs->get($sopDoc->id);
                    
                    return [
                        'type' => 'sop_legal',
                        'sop_id' => $sopDoc->id,
                        'requirement_id' => null,
                        'name' => $sopDoc->name,
                        'description' => $sopDoc->description,
                        'category' => $sopDoc->category,
                        'is_required' => $sopDoc->is_required,
                        'is_uploaded' => $clientDoc !== null,
                        'uploaded_document' => $clientDoc,
                        'file_path' => $clientDoc?->file_path,
                        'uploaded_at' => $clientDoc?->updated_at,
                        'uploaded_by' => $clientDoc?->user,
                        'status' => $clientDoc?->status ?? 'required',
                        'due_date' => null,
                    ];
                });
            }
        );
    }
    
    /**
     * Get additional document requirements created by admin
     * These are custom requirements specific to this client
     */
    public function getAdditionalRequirementsProperty(): Collection
    {
        if (!$this->selectedClientId) {
            return collect();
        }
        
        $userId = auth()->id();
        $clientId = $this->selectedClientId;
        
        return Cache::remember(
            "overview_requirements_{$userId}_{$clientId}",
            $this->cacheDuration,
            function () use ($clientId) {
                // Get all requirements for this client
                $requirements = ClientDocumentRequirement::query()
                    ->where('client_id', $clientId)
                    ->select([
                        'id', 'client_id', 'name', 'description', 
                        'category', 'is_required', 'status', 'due_date'
                    ])
                    ->orderBy('is_required', 'desc')
                    ->orderBy('due_date')
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                // Get uploaded documents for these requirements
                $requirementIds = $requirements->pluck('id')->toArray();
                
                $uploadedDocs = collect();
                if (!empty($requirementIds)) {
                    $uploadedDocs = ClientDocument::query()
                        ->where('client_id', $clientId)
                        ->whereIn('requirement_id', $requirementIds)
                        ->whereNotNull('file_path')
                        ->select([
                            'id', 'requirement_id', 'file_path', 
                            'original_filename', 'status', 'user_id', 'updated_at'
                        ])
                        ->with(['user:id,name'])
                        ->get()
                        ->keyBy('requirement_id');
                }
                
                // Map requirements with upload status
                return $requirements->map(function ($req) use ($uploadedDocs) {
                    $clientDoc = $uploadedDocs->get($req->id);
                    
                    // Document is uploaded if there's a file, regardless of requirement status
                    $isUploaded = $clientDoc !== null;
                    
                    return [
                        'type' => 'requirement',
                        'sop_id' => null,
                        'requirement_id' => $req->id,
                        'name' => $req->name,
                        'description' => $req->description,
                        'category' => $req->category,
                        'is_required' => $req->is_required,
                        'is_uploaded' => $isUploaded,
                        'uploaded_document' => $clientDoc,
                        'file_path' => $clientDoc?->file_path,
                        'uploaded_at' => $clientDoc?->updated_at,
                        'uploaded_by' => $clientDoc?->user,
                        'status' => $isUploaded ? ($clientDoc->status ?? 'pending_review') : 'required',
                        'due_date' => $req->due_date,
                        'requirement_status' => $req->status, // pending, fulfilled, waived
                    ];
                });
            }
        );
    }
    
    /**
     * Get combined document checklist (SOP + Requirements)
     * This merges both sources for a unified view
     */
    public function getAllDocumentsChecklistProperty(): Collection
    {
        $sopChecklist = $this->documentChecklist;
        $requirements = $this->additionalRequirements;
        
        // Merge both collections
        return $sopChecklist->concat($requirements);
    }
    
    /**
     * Get pending documents (not uploaded) from all sources
     */
    public function getPendingDocumentsProperty(): Collection
    {
        return $this->allDocumentsChecklist->filter(function ($doc) {
            // Not uploaded yet
            if (!$doc['is_uploaded']) {
                // For requirements, also check if not waived
                if ($doc['type'] === 'requirement') {
                    return ($doc['requirement_status'] ?? 'pending') !== 'waived';
                }
                return true;
            }
            return false;
        })->values();
    }
    
    /**
     * Get latest tax report with summaries - optimized query
     */
    public function getLatestTaxReportProperty(): ?TaxReport
    {
        if (!$this->selectedClientId) {
            return null;
        }
        
        $userId = auth()->id();
        $clientId = $this->selectedClientId;
        
        return Cache::remember(
            "overview_tax_report_{$userId}_{$clientId}",
            $this->cacheDuration,
            function () use ($clientId) {
                return TaxReport::query()
                    ->where('client_id', $clientId)
                    ->select(['id', 'client_id', 'month'])
                    ->with([
                        'taxCalculationSummaries' => fn($q) => $q
                            ->select([
                                'id', 'tax_report_id', 'tax_type',
                                'report_status', 'status_final', 'saldo_final'
                            ])
                            ->orderBy('tax_type')
                    ])
                    ->latest('month')
                    ->first();
            }
        );
    }
    
    /**
     * Get all stats in a single optimized query batch
     * Using raw SQL for maximum performance
     */
    public function getStatsProperty(): array
    {
        if (!$this->selectedClientId) {
            return [
                'projects' => ['total' => 0, 'active' => 0, 'completed' => 0, 'pending' => 0],
                'tax_reports' => ['total' => 0, 'reported' => 0, 'pending' => 0, 'completion_percentage' => 0],
                'documents' => ['total' => 0, 'valid' => 0, 'expired' => 0, 'pending' => 0, 'required' => 0],
            ];
        }
        
        $userId = auth()->id();
        $clientId = $this->selectedClientId;
        
        return Cache::remember(
            "overview_stats_{$userId}_{$clientId}",
            $this->cacheDuration,
            function () use ($clientId) {
                // Single query for project stats
                $projectStats = DB::table('projects')
                    ->where('client_id', $clientId)
                    ->selectRaw("
                        COUNT(*) as total,
                        SUM(CASE WHEN status IN ('in_progress', 'review') THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN status IN ('completed', 'completed (Not Payed Yet)') THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status IN ('draft', 'analysis') THEN 1 ELSE 0 END) as pending
                    ")
                    ->first();
                
                // Single query for tax report stats
                $taxStats = DB::table('tax_calculation_summaries')
                    ->join('tax_reports', 'tax_calculation_summaries.tax_report_id', '=', 'tax_reports.id')
                    ->where('tax_reports.client_id', $clientId)
                    ->selectRaw("
                        COUNT(*) as total,
                        SUM(CASE WHEN tax_calculation_summaries.report_status = 'Sudah Lapor' THEN 1 ELSE 0 END) as reported,
                        SUM(CASE WHEN tax_calculation_summaries.report_status = 'Belum Lapor' THEN 1 ELSE 0 END) as pending
                    ")
                    ->first();
                
                // Single query for document stats
                $docStats = DB::table('client_documents')
                    ->where('client_id', $clientId)
                    ->selectRaw("
                        SUM(CASE WHEN file_path IS NOT NULL THEN 1 ELSE 0 END) as total,
                        SUM(CASE WHEN status = 'valid' THEN 1 ELSE 0 END) as valid,
                        SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired,
                        SUM(CASE WHEN status = 'pending_review' THEN 1 ELSE 0 END) as pending
                    ")
                    ->first();
                
                // Count pending requirements (not uploaded)
                $pendingRequirements = DB::table('client_document_requirements')
                    ->where('client_id', $clientId)
                    ->where('status', 'pending')
                    ->count();
                
                $taxTotal = $taxStats->total ?? 0;
                $taxReported = $taxStats->reported ?? 0;
                
                return [
                    'projects' => [
                        'total' => (int) ($projectStats->total ?? 0),
                        'active' => (int) ($projectStats->active ?? 0),
                        'completed' => (int) ($projectStats->completed ?? 0),
                        'pending' => (int) ($projectStats->pending ?? 0),
                    ],
                    'tax_reports' => [
                        'total' => (int) $taxTotal,
                        'reported' => (int) $taxReported,
                        'pending' => (int) ($taxStats->pending ?? 0),
                        'completion_percentage' => $taxTotal > 0 ? round(($taxReported / $taxTotal) * 100) : 0,
                    ],
                    'documents' => [
                        'total' => (int) ($docStats->total ?? 0),
                        'valid' => (int) ($docStats->valid ?? 0),
                        'expired' => (int) ($docStats->expired ?? 0),
                        'pending' => (int) ($docStats->pending ?? 0),
                        'required' => (int) $pendingRequirements,
                    ],
                ];
            }
        );
    }
    
    /**
     * Shortcut accessors for stats
     */
    public function getProjectStatsProperty(): array
    {
        return $this->stats['projects'];
    }
    
    public function getTaxReportStatsProperty(): array
    {
        return $this->stats['tax_reports'];
    }
    
    public function getDocumentStatsProperty(): array
    {
        return $this->stats['documents'];
    }
    
    /**
     * Download document
     */
    public function downloadDocument(int $documentId)
    {
        $document = ClientDocument::query()
            ->where('id', $documentId)
            ->where('client_id', $this->selectedClientId)
            ->firstOrFail();
        
        if ($document->file_path && \Storage::disk('public')->exists($document->file_path)) {
            return \Storage::disk('public')->download(
                $document->file_path,
                $document->original_filename ?? basename($document->file_path)
            );
        }
        
        $this->dispatch('notify', [
            'type' => 'error',
            'message' => 'File tidak ditemukan'
        ]);
    }
    
    /**
     * Refresh the overview data
     */
    public function refresh()
    {
        $this->clearClientCache();
        $this->loadClientData();
        $this->dispatch('overview-refreshed');
    }

    /**
     * Alias for pending documents (for backward compatibility)
     */
    public function getRequiredDocumentsProperty(): Collection
    {
        return $this->pendingDocuments;
    }

    public function render()
    {
        return view('livewire.client.panel.overview-tab', [
            'selectedClient' => $this->selectedClient,
            'projects' => $this->projects,
            'recentDocuments' => $this->recentDocuments,
            'documentChecklist' => $this->documentChecklist,
            'requiredDocuments' => $this->pendingDocuments,
            'additionalRequirements' => $this->additionalRequirements,
            'allDocumentsChecklist' => $this->allDocumentsChecklist,
            'pendingDocuments' => $this->pendingDocuments,
            'latestTaxReport' => $this->latestTaxReport,
            'projectStats' => $this->projectStats,
            'taxReportStats' => $this->taxReportStats,
            'documentStats' => $this->documentStats,
        ]);
    }
}