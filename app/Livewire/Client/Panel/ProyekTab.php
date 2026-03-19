<?php

namespace App\Livewire\Client\Panel;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Client;
use App\Models\Project;
use App\Models\UserClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ProyekTab extends Component
{
    public ?int $selectedClientId = null;
    public $selectedProjectId;

    public function mount()
    {
        // Restore from session if the sidebar switcher has been used
        $sessionClientId = session('client_panel_selected_client_id');
        $clientIds = UserClient::where('user_id', auth()->id())->pluck('client_id');

        if ($sessionClientId && $clientIds->contains((int) $sessionClientId)) {
            $this->selectedClientId = (int) $sessionClientId;
        } elseif ($clientIds->isNotEmpty()) {
            $this->selectedClientId = $clientIds->first();
        }

        // Auto-select first project of the selected client
        $firstProject = $this->getProjectsProperty()->first();
        if ($firstProject) {
            $this->selectedProjectId = $firstProject->id;
        }
    }

    /**
     * Called by the global ClientSwitcher when the user picks a different client.
     */
    #[On('client-switched')]
    public function onClientSwitched(int $clientId): void
    {
        $this->selectedClientId = $clientId;
        $this->selectedProjectId = null;

        $firstProject = $this->getProjectsProperty()->first();
        if ($firstProject) {
            $this->selectedProjectId = $firstProject->id;
        }
    }

    public function getProjectsProperty(): Collection
    {
        if (!$this->selectedClientId) {
            return collect([]);
        }

        // Verify the client belongs to this user
        $clientIds = UserClient::where('user_id', auth()->id())->pluck('client_id');
        if (!$clientIds->contains($this->selectedClientId)) {
            return collect([]);
        }

        return Project::where('client_id', $this->selectedClientId)
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
        return $this->projects->whereIn('status', ['draft', 'in_progress', 'review', 'analysis'])->count();
    }

    public function getCompletedProjectsCountProperty()
    {
        return $this->projects->where('status', 'completed')->count();
    }

    public function selectProject($projectId)
    {
        $this->selectedProjectId = $projectId;
    }

    public function downloadDeliverable($projectId, $fileIndex)
    {
        $project = Project::findOrFail($projectId);

        // Authorization check
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
        return view('livewire.client.panel.proyek-tab');
    }
}