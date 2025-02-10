<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Project;
use App\Models\RequiredDocument;
use App\Models\SubmittedDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class StatsDetailModal extends Component
{
    public $type;
    public $data;
    public $isPreviewModalOpen = false;
    public $previewingDocument = null;
    public $previewUrl = null;
    public $fileType = null;
    public $currentIndex = 0;
    public $totalDocuments = 0;

    protected $queryLimit = 5;

    public function viewDocument(SubmittedDocument $submission): void
    {
        $submissionWithRelations = Cache::remember(
            "submitted_document_{$submission->id}", 
            300, 
            fn() => SubmittedDocument::with('requiredDocument.submittedDocuments')
                ->find($submission->id)
        );

        $allSubmissions = $submissionWithRelations->requiredDocument->submittedDocuments;
        $this->totalDocuments = $allSubmissions->count();
        $this->currentIndex = $allSubmissions->search(fn($doc) => $doc->id === $submission->id);

        $this->updatePreviewDocument($submission);
    }

    public function nextDocument()
    {
        if (!$this->previewingDocument) return;

        $requiredDocument = Cache::remember(
            "required_document_{$this->previewingDocument->required_document_id}_submissions",
            300,
            fn() => RequiredDocument::with('submittedDocuments')
                ->find($this->previewingDocument->required_document_id)
        );

        $allSubmissions = $requiredDocument->submittedDocuments;
        $this->currentIndex = ($this->currentIndex + 1) % $this->totalDocuments;
        $this->updatePreviewDocument($allSubmissions[$this->currentIndex]);
    }

    public function previousDocument()
    {
        if (!$this->previewingDocument) return;

        $requiredDocument = Cache::remember(
            "required_document_{$this->previewingDocument->required_document_id}_submissions",
            300,
            fn() => RequiredDocument::with('submittedDocuments')
                ->find($this->previewingDocument->required_document_id)
        );

        $allSubmissions = $requiredDocument->submittedDocuments;
        $this->currentIndex = ($this->currentIndex - 1 + $this->totalDocuments) % $this->totalDocuments;
        $this->updatePreviewDocument($allSubmissions[$this->currentIndex]);
    }

    protected function updatePreviewDocument(SubmittedDocument $document)
    {
        $this->previewingDocument = $document;
        $this->previewUrl = Storage::disk('public')->url($document->file_path);
        $this->fileType = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
    }

    public function downloadDocument($documentId)
    {
        $document = Cache::remember(
            "download_document_{$documentId}", 
            300, 
            fn() => SubmittedDocument::find($documentId)
        );

        return $document ? Storage::disk('public')->download($document->file_path) : null;
    }

    public function mount($type, $data)
    {
        $this->type = $type;
        $this->data = $this->enrichData($data);
    }

    private function enrichData($baseData)
    {
        $user = auth()->user();
        $cacheKey = "stats_detail_{$this->type}_{$user->id}";
        
        return Cache::remember($cacheKey, 300, function () use ($baseData, $user) {
            $enrichedData = $baseData;
            $baseQuery = Project::query()
                ->when(!$user->hasRole('super-admin'), function ($query) use ($user) {
                    $query->whereHas('client', function ($q) use ($user) {
                        $q->whereIn('id', $user->userClients()->pluck('client_id'));
                    });
                });

            switch ($this->type) {
                case 'total':
                    $projects = $baseQuery->with(['client', 'steps'])
                        ->latest()
                        ->take($this->queryLimit)
                        ->get();

                    $projectStats = $baseQuery->selectRaw('
                        COUNT(*) as total,
                        SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as active_count,
                        SUM(CASE WHEN status = "on_hold" THEN 1 ELSE 0 END) as on_hold_count,
                        SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_count
                    ')->first();

                    $enrichedData = array_merge($enrichedData, [
                        'recent_projects' => $projects,
                        'active_count' => $projectStats->active_count,
                        'on_hold_count' => $projectStats->on_hold_count,
                        'completed_count' => $projectStats->completed_count,
                        'on_schedule_percentage' => $this->calculateOnSchedulePercentage($projects),
                        'efficiency_rate' => $this->calculateEfficiencyRate($projects)
                    ]);
                    break;

                case 'active':
                    $activeProjects = $baseQuery->with(['client', 'steps'])
                        ->where('status', 'in_progress')
                        ->latest()
                        ->take($this->queryLimit)
                        ->get();

                    $enrichedData = array_merge($enrichedData, [
                        'active_projects' => $activeProjects,
                        'initial_phase' => $activeProjects->where('completion_percentage', '<', 33)->count(),
                        'mid_phase' => $activeProjects->whereBetween('completion_percentage', [33, 65])->count(),
                        'final_phase' => $activeProjects->where('completion_percentage', '>=', 66)->count(),
                        'on_schedule_percentage' => $this->calculateOnSchedulePercentage($activeProjects),
                        'efficiency_rate' => $this->calculateEfficiencyRate($activeProjects)
                    ]);
                    break;

                case 'completed':
                    $completedProjects = $baseQuery->with(['client', 'steps'])
                        ->where('status', 'completed')
                        ->latest()
                        ->take($this->queryLimit)
                        ->get();

                    $enrichedData = array_merge($enrichedData, [
                        'completed_projects' => $completedProjects,
                        'this_month' => $completedProjects->where('updated_at', '>=', now()->startOfMonth())->count(),
                        'last_month' => $completedProjects->whereBetween('updated_at', [
                            now()->subMonth()->startOfMonth(),
                            now()->startOfMonth()
                        ])->count(),
                        'avg_duration' => round($completedProjects->avg(fn($project) => 
                            $project->created_at->diffInDays($project->updated_at)
                        )),
                        'on_schedule_percentage' => $this->calculateOnSchedulePercentage($completedProjects),
                        'efficiency_rate' => $this->calculateEfficiencyRate($completedProjects)
                    ]);
                    break;

                case 'pending':
                    $pendingDocs = RequiredDocument::with(['projectStep.project', 'submittedDocuments'])
                        ->where('status', 'pending_review')
                        ->when(!$user->hasRole('super-admin'), function ($query) use ($user) {
                            $query->whereHas('projectStep.project.client', function ($q) use ($user) {
                                $q->whereIn('id', $user->userClients()->pluck('client_id'));
                            });
                        })
                        ->latest()
                        ->take($this->queryLimit)
                        ->get();

                    $enrichedData = array_merge($enrichedData, [
                        'pending_documents' => $pendingDocs,
                        'pending_review_count' => $pendingDocs->count(),
                        'awaiting_count' => RequiredDocument::whereDoesntHave('submittedDocuments')->count(),
                        'urgent_count' => $pendingDocs->where('is_urgent', true)->count()
                    ]);
                    break;
            }

            return $enrichedData;
        });
    }

    private function calculateOnSchedulePercentage($projects)
    {
        if ($projects->isEmpty()) return 0;
        
        $onSchedule = $projects->filter(fn($project) => true)->count();
        return round(($onSchedule / $projects->count()) * 100);
    }

    private function calculateEfficiencyRate($projects)
    {
        if ($projects->isEmpty()) return 0;
        return 90; // Your efficiency calculation logic here
    }

    public function render()
    {
        return view('livewire.dashboard.stats-detail-modal');
    }
}