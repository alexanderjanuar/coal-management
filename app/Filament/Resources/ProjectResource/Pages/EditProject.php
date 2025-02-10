<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function afterSave(): void
    {
        $project = $this->record;
        $client = $project->client;

        ProjectResource::sendProjectNotifications(
            "Project Updated",
            sprintf(
                "<strong>Client:</strong> %s<br><strong>Project:</strong> %s<br><strong>Type:</strong> %s<br><strong>Due Date:</strong> %s<br><strong>Updated by:</strong> %s",
                $client->name,
                $project->name,
                ucwords($project->type),
                $project->due_date->format('d M Y'),
                auth()->user()->name
            ),
            $project,
            'info',
            'View Project'
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
