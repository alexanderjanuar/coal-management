<?php

namespace App\Livewire\Client;

use Livewire\Component;
use App\Models\Project;
use App\Models\UserClient;
use Illuminate\Support\Facades\Storage;

class ProyekTab extends Component
{
    public $selectedProjectId;

    public function mount()
    {
        // Auto-select first project on mount
        $firstProject = $this->getProjectsProperty()->first();
        if ($firstProject) {
            $this->selectedProjectId = $firstProject->id;
        }
    }

    public function getProjectsProperty()
    {
        // Get all client IDs linked to current user
        $clientIds = UserClient::where('user_id', auth()->id())
            ->pluck('client_id');

        if ($clientIds->isEmpty()) {
            return collect([]);
        }

        return Project::whereIn('client_id', $clientIds)
            ->with([
                'client',
                'pic',
                'userProjects.user',
                'steps'
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($project) {
                // Ensure deliverable_files is always an array
                $project->deliverable_files = $project->deliverable_files ?? [];
                return $project;
            });
    }

    public function getActiveProjectsCountProperty()
    {
        $clientIds = UserClient::where('user_id', auth()->id())
            ->pluck('client_id');

        if ($clientIds->isEmpty()) {
            return 0;
        }

        return Project::whereIn('client_id', $clientIds)
            ->whereIn('status', ['draft', 'in_progress', 'review', 'analysis'])
            ->count();
    }

    public function getCompletedProjectsCountProperty()
    {
        $clientIds = UserClient::where('user_id', auth()->id())
            ->pluck('client_id');

        if ($clientIds->isEmpty()) {
            return 0;
        }

        return Project::whereIn('client_id', $clientIds)
            ->where('status', 'completed')
            ->count();
    }

    public function selectProject($projectId)
    {
        $this->selectedProjectId = $projectId;
    }

    public function downloadDeliverable($projectId, $fileIndex)
    {
        $project = Project::findOrFail($projectId);
        
        // Authorization check - verify user has access to this project's client
        $hasAccess = UserClient::where('user_id', auth()->id())
            ->where('client_id', $project->client_id)
            ->exists();

        if (!$hasAccess) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Anda tidak memiliki akses ke proyek ini'
            ]);
            return;
        }

        $deliverableFiles = is_array($project->deliverable_files) ? $project->deliverable_files : [];
        
        if (isset($deliverableFiles[$fileIndex])) {
            $fileData = $deliverableFiles[$fileIndex];
            
            // Handle both string paths and array structures
            $filePath = is_string($fileData) ? $fileData : ($fileData['path'] ?? null);
            
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                return Storage::disk('public')->download($filePath);
            }
        }

        $this->dispatch('notify', [
            'type' => 'error',
            'message' => 'File tidak ditemukan'
        ]);
    }

    public function render()
    {
        return view('livewire.client.proyek-tab');
    }
}