<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['due_date'])) {
            $data['due_date'] = Carbon::parse($data['due_date'])->format('Y-m-d');
        }

        return $data;
    }
    
    protected function afterCreate(): void
    {
        $project = $this->record;
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
}
