<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Models\User;
use App\Models\DailyTask;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Only keep the fields that belong to the projects table
        $projectData = [
            'name' => $data['name'],
            'client_id' => $data['client_id'],
            'type' => $data['type'],
            'sop_id' => $data['sop_id'] ?? null,
            'due_date' => isset($data['due_date']) ? Carbon::parse($data['due_date'])->format('Y-m-d') : null,
            'description' => $data['description'] ?? null,
        ];

        // Store user project data for use after creation
        $this->userProjectData = $data['userProject'] ?? [];

        return $projectData;
    }

    protected function afterCreate(): void
    {
        $project = $this->record;

        // Get all users with required roles (directors, project managers, and verificators)
        $requiredRoleUsers = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['direktur', 'super-admin']);
        })->get();

        // Get existing user IDs from form data
        $existingUserIds = collect($this->userProjectData)->pluck('user_id')->toArray();

        // Add users with required roles to userProject data if not already included
        foreach ($requiredRoleUsers as $user) {
            if (!in_array($user->id, $existingUserIds)) {
                $this->userProjectData[] = [
                    'user_id' => $user->id
                ];
            }
        }

        // Create user project relationships
        foreach ($this->userProjectData as $userData) {
            $project->userProject()->create([
                'user_id' => $userData['user_id']
            ]);
        }

        // Log the assignment
        \Log::info('Project assigned to users', [
            'project_id' => $project->id,
            'project_name' => $project->name,
            'assigned_users' => collect($this->userProjectData)->pluck('user_id')->toArray(),
            'auto_assigned_roles' => ['direktur', 'project-manager', 'verificator']
        ]);

        // Send notifications
        $client = $project->client;
        ProjectResource::sendProjectNotifications(
            "New Project Assigned",
            sprintf(
                "<strong>Client:</strong> %s<br><strong>Project:</strong> %s<br><strong>Type:</strong> %s<br><strong>Due Date:</strong> %s<br><strong>Created by:</strong> %s",
                $client->name,
                $project->name,
                ucwords($project->type),
                $project->due_date->format('d M Y'),
                auth()->user()->name
            ),
            $project,
            'success',
            'View Project'
        );

        // Auto-create a DailyTask for this project
        $memberIds = collect($this->userProjectData)->pluck('user_id')->unique()->toArray();

        if (!empty($memberIds)) {
            $dailyTask = DailyTask::create([
                'title'       => "{$project->name}",
                'description' => "{$project->name} - Client: {$client->name}",
                'project_id'  => $project->id,
                'created_by'  => auth()->id(),
                'priority'    => $project->priority ?? 'normal',
                'status'      => 'pending',
                'task_date'   => $project->due_date,
            ]);

            $dailyTask->assignedUsers()->sync($memberIds);

            // Create subtasks from project steps
            $project->load('steps');
            foreach ($project->steps->sortBy('order') as $step) {
                $dailyTask->subtasks()->create([
                    'title'  => $step->name,
                    'status' => 'pending',
                ]);
            }
        }

        // Show success notification with assignment details
        $assignedCount = count($this->userProjectData);
        $rolesAssigned = $requiredRoleUsers->pluck('roles.*.name')->flatten()->unique()->implode(', ');
        
        Notification::make()
            ->title('Project Created Successfully')
            ->body("Project has been assigned to {$assignedCount} users with roles: {$rolesAssigned}")
            ->success()
            ->duration(5000)
            ->send();
    }

    protected $userProjectData = [];
}