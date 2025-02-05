<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Project;
use App\Models\RequiredDocument;
use App\Models\SubmittedDocument;
use Illuminate\Support\Facades\Storage;


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

    public function viewDocument(SubmittedDocument $submission): void
    {
        $requiredDocument = $submission->requiredDocument;
        $allSubmissions = $requiredDocument->submittedDocuments;

        $this->totalDocuments = $allSubmissions->count();
        $this->currentIndex = $allSubmissions->search(function ($doc) use ($submission) {
            return $doc->id === $submission->id;
        });

        $this->updatePreviewDocument($submission);
    }

    public function nextDocument()
    {
        $requiredDocument = $this->previewingDocument->requiredDocument;
        $allSubmissions = $requiredDocument->submittedDocuments;

        $this->currentIndex = ($this->currentIndex + 1) % $this->totalDocuments;
        $nextDoc = $allSubmissions[$this->currentIndex];

        $this->updatePreviewDocument($nextDoc);
    }

    public function previousDocument()
    {
        $requiredDocument = $this->previewingDocument->requiredDocument;
        $allSubmissions = $requiredDocument->submittedDocuments;

        $this->currentIndex = ($this->currentIndex - 1 + $this->totalDocuments) % $this->totalDocuments;
        $prevDoc = $allSubmissions[$this->currentIndex];

        $this->updatePreviewDocument($prevDoc);
    }

    protected function updatePreviewDocument(SubmittedDocument $document)
    {
        $this->previewingDocument = $document;
        $this->previewUrl = Storage::disk('public')->url($document->file_path);
        $this->fileType = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
    }

    public function downloadDocument($documentId)
    {
        $document = SubmittedDocument::find($documentId);
        if ($document) {
            return Storage::disk('public')->download($document->file_path);
        }
    }

    public function mount($type, $data)
    {
        $this->type = $type;
        $this->data = $this->enrichData($data);
    }

    private function enrichData($baseData)
    {
        $enrichedData = $baseData;
        $user = auth()->user();

        switch ($this->type) {
            case 'total':
                $projects = Project::with(['client', 'steps'])
                    ->when(!$user->hasRole('super-admin'), function ($query) use ($user) {
                        $query->whereHas('client', function ($q) use ($user) {
                            $q->whereIn('id', $user->userClients()->pluck('client_id'));
                        });
                    })
                    ->latest()
                    ->take(5)
                    ->get();

                $enrichedData['recent_projects'] = $projects;
                $enrichedData['active_count'] = Project::where('status', 'in_progress')->count();
                $enrichedData['on_hold_count'] = Project::where('status', 'on_hold')->count();
                $enrichedData['completed_count'] = Project::where('status', 'completed')->count();
                $enrichedData['on_schedule_percentage'] = $this->calculateOnSchedulePercentage($projects);
                $enrichedData['efficiency_rate'] = $this->calculateEfficiencyRate($projects);

                break;

            case 'active':
                $activeProjects = Project::with(['client', 'steps'])
                    ->where('status', 'in_progress')
                    ->when(!$user->hasRole('super-admin'), function ($query) use ($user) {
                        $query->whereHas('client', function ($q) use ($user) {
                            $q->whereIn('id', $user->userClients()->pluck('client_id'));
                        });
                    })
                    ->latest()
                    ->take(5)
                    ->get();

                $enrichedData['active_projects'] = $activeProjects;
                $enrichedData['initial_phase'] = $activeProjects->filter(function ($project) {
                    return $project->completion_percentage < 33;
                })->count();
                $enrichedData['mid_phase'] = $activeProjects->filter(function ($project) {
                    return $project->completion_percentage >= 33 && $project->completion_percentage < 66;
                })->count();
                $enrichedData['final_phase'] = $activeProjects->filter(function ($project) {
                    return $project->completion_percentage >= 66;
                })->count();
                $enrichedData['on_schedule_percentage'] = $this->calculateOnSchedulePercentage($activeProjects);
                $enrichedData['efficiency_rate'] = $this->calculateEfficiencyRate($activeProjects);
                break;

            case 'completed':
                $completedProjects = Project::with(['client', 'steps'])
                    ->where('status', 'completed')
                    ->when(!$user->hasRole('super-admin'), function ($query) use ($user) {
                        $query->whereHas('client', function ($q) use ($user) {
                            $q->whereIn('id', $user->userClients()->pluck('client_id'));
                        });
                    })
                    ->latest()
                    ->take(5)
                    ->get();

                $enrichedData['completed_projects'] = $completedProjects;
                $enrichedData['this_month'] = $completedProjects->where('updated_at', '>=', now()->startOfMonth())->count();
                $enrichedData['last_month'] = $completedProjects->where('updated_at', '>=', now()->subMonth()->startOfMonth())
                    ->where('updated_at', '<', now()->startOfMonth())
                    ->count();
                $enrichedData['avg_duration'] = round($completedProjects->avg(function ($project) {
                    return $project->created_at->diffInDays($project->updated_at);
                }));
                $enrichedData['on_schedule_percentage'] = $this->calculateOnSchedulePercentage($completedProjects);
                $enrichedData['efficiency_rate'] = $this->calculateEfficiencyRate($completedProjects);
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
                    ->take(5)
                    ->get();

                $enrichedData['pending_documents'] = $pendingDocs;
                $enrichedData['pending_review_count'] = $pendingDocs->where('status', 'pending_review')->count();
                $enrichedData['awaiting_count'] = RequiredDocument::whereDoesntHave('submittedDocuments')->count();
                $enrichedData['urgent_count'] = $pendingDocs->filter(function ($doc) {
                    return $doc->is_urgent ?? false;
                })->count();
                break;
        }

        return $enrichedData;
    }

    private function calculateOnSchedulePercentage($projects)
    {
        if ($projects->isEmpty()) {
            return 0;
        }

        $onSchedule = $projects->filter(function ($project) {
            // Add your logic to determine if project is on schedule
            return true;
        })->count();

        return round(($onSchedule / $projects->count()) * 100);
    }

    private function calculateEfficiencyRate($projects)
    {
        if ($projects->isEmpty()) {
            return 0;
        }

        $efficiency = $projects->avg(function ($project) {
            // Add your logic to calculate efficiency
            return 90;
        });

        return round($efficiency);
    }
    public function render()
    {
        return view('livewire.dashboard.stats-detail-modal');
    }
}
