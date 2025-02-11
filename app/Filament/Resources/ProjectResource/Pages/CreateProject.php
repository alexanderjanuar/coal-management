<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Models\User;
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

        // Get all directors
        $directors = User::role('direktur')->get();

        // Get existing user IDs
        $existingUserIds = collect($this->userProjectData)->pluck('user_id')->toArray();

        // Add directors to userProject data
        foreach ($directors as $director) {
            if (!in_array($director->id, $existingUserIds)) {
                $this->userProjectData[] = [
                    'user_id' => $director->id
                ];
            }
        }

        // Create user project relationships
        foreach ($this->userProjectData as $userData) {
            $project->userProject()->create([
                'user_id' => $userData['user_id']
            ]);
        }

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
    }

    protected $userProjectData = [];
}
