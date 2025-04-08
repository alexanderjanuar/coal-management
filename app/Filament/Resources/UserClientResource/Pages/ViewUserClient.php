<?php

namespace App\Filament\Resources\UserClientResource\Pages;

use App\Filament\Resources\UserClientResource;
use App\Models\User;
use App\Models\UserClient;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUserClient extends ViewRecord
{
    protected static string $resource = UserClientResource::class;
    protected static string $view = 'filament.pages.user-client-details';

    public function mount(int|string $record): void
    {
        parent::mount($record);
    }

    protected function resolveRecord(int|string $key): UserClient
    {
        // Find the user first
        $user = User::find($key);
        
        if (!$user) {
            return null;
        }
        
        // Get the first user_client record for this user
        // or create a temporary one with just the user ID
        $userClient = UserClient::where('user_id', $user->id)->first();
        
        if (!$userClient) {
            // If no actual record exists, create a temporary one for the view
            // This won't be saved to the database
            $userClient = new UserClient();
            $userClient->user_id = $user->id;
            $userClient->setRelation('user', $user);
        }
        
        return $userClient;
    }
    
    public function getViewData(): array
    {
        $record = $this->record;
        $user = $record->user;
        $client = $record->client;
        
        // Get all projects the user is assigned to
        $userProjects = $user->userProjects;
        
        // Get project IDs
        $projectIds = $userProjects->pluck('project_id')->filter();
        
        // Calculate document statistics
        $totalSubmittedDocuments = \App\Models\SubmittedDocument::where('user_id', $user->id)->count();
        
        $approvedDocuments = \App\Models\SubmittedDocument::where('user_id', $user->id)
            ->where('status', 'approved')
            ->count();
        
        $rejectedDocuments = \App\Models\SubmittedDocument::where('user_id', $user->id)
            ->where('status', 'rejected')
            ->count();

        // Calculate other stats...
        $totalProjects = $userProjects->count();
        $activeProjects = $userProjects->filter(function ($userProject) {
            return $userProject->project && $userProject->project->status === 'in_progress';
        })->count();
        
        $completedProjects = $userProjects->filter(function ($userProject) {
            return $userProject->project && $userProject->project->status === 'completed';
        })->count();
        
        $urgentProjects = $userProjects->filter(function ($userProject) {
            return $userProject->project && $userProject->project->priority === 'urgent';
        })->count();
        
        return [
            'record' => $record,
            'user' => $user,
            'client' => $client,
            'userProjects' => $userProjects,
            'stats' => [
                'totalProjects' => $totalProjects,
                'activeProjects' => $activeProjects,
                'completedProjects' => $completedProjects,
                'urgentProjects' => $urgentProjects,
                'totalSubmittedDocuments' => $totalSubmittedDocuments,
                'approvedDocuments' => $approvedDocuments,
                'rejectedDocuments' => $rejectedDocuments,
            ],
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
    
    protected function getGravatarUrl(string $email): string
    {
        $hash = md5(strtolower(trim($email)));
        return "https://www.gravatar.com/avatar/{$hash}?s=200&d=mp";
    }
}
