<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Project;
class ProjectDetails extends Component
{
    public $project;
    protected $listeners = ['refreshTimeline' => '$refresh'];

    public $activeStep = null;

    public function mount(Project $project)
    {
        $this->project = $project->load([
            'steps.tasks',
            'steps.requiredDocuments'
        ]);
    }

    private function getDocumentCounts($step)
    {
        $requiredDocs = $step->requiredDocuments;

        return [
            'total' => $requiredDocs ? $requiredDocs->count() : 0,
            'approved' => $requiredDocs ? $requiredDocs->where('status', 'approved')->count() : 0
        ];
    }

    public function getProgressProperty()
    {
        $totalSteps = $this->project->steps->count();
        if ($totalSteps === 0)
            return 0;

        $completedSteps = $this->project->steps->where('status', 'completed')->count();
        return ($completedSteps / $totalSteps) * 100;
    }

    public function toggleStep($stepId)
    {
        $this->activeStep = $this->activeStep === $stepId ? null : $stepId;
    }

    public function getCompletedStepsProperty()
    {
        return $this->project->steps->where('status', 'completed')->count();
    }

    public function getTotalStepsProperty()
    {
        return $this->project->steps->count();
    }


    public function render()
    {
        return view('livewire.dashboard.project-details');
    }
}
