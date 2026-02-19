<?php

namespace App\Observers;

use App\Models\ProjectStep;

class ProjectStepObserver
{
    /**
     * Handle the ProjectStep "updated" event.
     * When a project step's status changes, sync the corresponding
     * DailyTaskSubTask (matched by title) on the project's linked DailyTask.
     */
    public function updated(ProjectStep $step): void
    {
        // Only act when the status field actually changed
        if (!$step->wasChanged('status')) {
            return;
        }

        // Traverse: ProjectStep → Project → DailyTasks → matching SubTask by title
        $project = $step->project()->with('dailyTasks.subtasks')->first();

        if (!$project) {
            return;
        }

        foreach ($project->dailyTasks as $dailyTask) {
            $subtask = $dailyTask->subtasks
                ->where('title', $step->name)
                ->first();

            if (!$subtask) {
                continue;
            }

            // Map project step status → daily task subtask status
            $newSubtaskStatus = $step->status === 'completed' ? 'completed' : 'pending';

            // Only update if status actually needs to change (avoid unnecessary writes)
            if ($subtask->status !== $newSubtaskStatus) {
                $subtask->update(['status' => $newSubtaskStatus]);
            }
        }
    }
}
