<?php

namespace App\Console\Commands;

use App\Models\DailyTask;
use App\Models\Project;
use App\Models\User;
use Illuminate\Console\Command;

class BackfillProjectDailyTasks extends Command
{
    protected $signature = 'projects:backfill-daily-tasks
                            {--dry-run : Preview what would be created without writing to DB}';

    protected $description = 'Create DailyTasks (with subtasks & assignments) for existing draft/in_progress projects with active clients that do not yet have a linked DailyTask.';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('DRY RUN — no data will be written.');
        }

        // Fetch qualifying projects:
        // - status: draft or in_progress
        // - client status: Active
        // - no DailyTask already linked
        $projects = Project::with([
            'client',
            'steps' => fn($q) => $q->orderBy('order'),
            'userProject.user',
        ])
            ->whereIn('status', ['draft', 'in_progress'])
            ->whereHas('client', fn($q) => $q->where('status', 'Active'))
            ->get();

        if ($projects->isEmpty()) {
            $this->info('No qualifying projects found. All active draft/in_progress projects already have DailyTasks.');
            return self::SUCCESS;
        }

        $this->info("Found {$projects->count()} project(s) to backfill.");
        $this->newLine();

        // Get a fallback system user (super-admin) to set as creator
        $systemUser = User::whereHas('roles', fn($q) => $q->where('name', 'super-admin'))
            ->first();

        if (!$systemUser) {
            $this->error('No super-admin user found to use as task creator. Aborting.');
            return self::FAILURE;
        }

        $created = 0;

        foreach ($projects as $project) {
            $memberIds = $project->userProject->pluck('user_id')->unique()->toArray();
            $memberNames = $project->userProject->pluck('user.name')->filter()->unique()->implode(', ');

            $this->line("→ [{$project->status}] {$project->name} (Client: {$project->client->name})");

            if ($isDryRun) {
                $this->line("   Would create DailyTask with {$project->steps->count()} subtask(s).");
                foreach ($project->steps as $step) {
                    $this->line("     - Subtask: {$step->name}");
                }
                $assigneeDisplay = $memberNames ?: 'none (no project members)';
                $this->line("   Assignees (" . count($memberIds) . "): {$assigneeDisplay}");
                $this->newLine();
                continue;
            }

            // Create the DailyTask
            $dailyTask = DailyTask::create([
                'title' => $project->name,
                'description' => "{$project->name} - Client: {$project->client->name}",
                'project_id' => $project->id,
                'created_by' => $systemUser->id,
                'priority' => $project->priority ?? 'normal',
                'status' => match ($project->status) {
                    'in_progress' => 'in_progress',
                    default => 'pending',
                },
                'task_date' => $project->due_date,
            ]);

            // Assign all project members (from user_projects)
            if (!empty($memberIds)) {
                $dailyTask->assignedUsers()->sync($memberIds);
            }

            // Create subtasks from project steps
            foreach ($project->steps as $step) {
                $dailyTask->subtasks()->create([
                    'title' => $step->name,
                    'status' => $step->status === 'completed' ? 'completed' : 'pending',
                ]);
            }

            $this->info("   ✓ Created DailyTask with {$project->steps->count()} subtask(s), assigned to " . count($memberIds) . " user(s).");
            $created++;
        }

        $this->newLine();

        if ($isDryRun) {
            $this->warn("Dry run complete. {$projects->count()} DailyTask(s) would be created.");
        } else {
            $this->info("Done! {$created} DailyTask(s) created successfully.");
        }

        return self::SUCCESS;
    }
}
