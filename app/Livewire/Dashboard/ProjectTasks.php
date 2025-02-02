<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\ProjectStep;
class ProjectTasks extends Component
{
    public ProjectStep $step;

    public function mount(ProjectStep $step)
    {
        $this->step = $step->load(['tasks.comments']);
    }

    public function getTaskProgressProperty()
    {
        if ($this->step->tasks->isEmpty()) {
            return 0;
        }
        
        return ($this->step->tasks->where('status', 'completed')->count() / $this->step->tasks->count()) * 100;
    }
    public function render()
    {
        return view('livewire.dashboard.project-tasks');
    }
}
