<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Project;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Route;

class ProjectDetailUser extends Component
{
    public Project $project;
    public $search = '';
    public $selectedRole = null;

    public function mount(Project $project)
    {
        $this->project = $project;
    }

    public function getUsersProperty()
    {
        return $this->project->userProject()
            ->with(['user'])
            ->get()
            ->map(function ($userProject) {
                $user = $userProject->user;
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => "https://ui-avatars.com/api/?name=" . urlencode($user->name) . "&color=7F9CF5&background=EBF4FF",
                    'comments_count' => $user->comments()
                        ->whereHasMorph('commentable', ['App\Models\Task'], function ($query) {
                            $query->whereIn('project_step_id', $this->project->steps->pluck('id'));
                        })->count(),
                    'documents_count' => $user->submittedDocuments()
                        ->whereHas('requiredDocument.projectStep', function ($query) {
                            $query->where('project_id', $this->project->id);
                        })->count(),
                    'last_active' => $user->comments()
                        ->whereHasMorph('commentable', ['App\Models\Task'], function ($query) {
                            $query->whereIn('project_step_id', $this->project->steps->pluck('id'));
                        })
                        ->latest()
                        ->first()?->created_at?->diffForHumans() ?? 'Never',
                ];
            });
    }

    public function getAvailableUsersProperty()
    {
        if (auth()->user()->hasRole('staff')) {
            return collect();
        }

        return User::query()
            ->whereDoesntHave('userProjects', function ($query) {
                $query->where('project_id', $this->project->id);
            })
            ->when(!auth()->user()->hasRole('super-admin'), function ($query) {
                $query->whereHas('userClients', function ($q) {
                    $q->whereIn('client_id', auth()->user()->userClients->pluck('client_id'));
                });
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->get();
    }

    public function addUserToProject($userId)
    {
        if (auth()->user()->hasRole('staff')) {
            return;
        }

        try {
            $this->project->userProject()->create([
                'user_id' => $userId
            ]);

            // Get user and project details
            $addedUser = User::find($userId);
            $client = $this->project->client;

            // Create notification for added user
            $notification = Notification::make()
                ->title('Project Assignment')
                ->body(sprintf(
                    "<strong>Client:</strong> %s<br><strong>Project:</strong> %s<br><strong>Assigned by:</strong> %s",
                    $client->name,
                    $this->project->name,
                    auth()->user()->name
                ))
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->button()
                        ->label('View Project')
                        ->url(route('filament.admin.resources.projects.view', ['record' => $this->project->id]))
                ])
                ->success();

            // Send to the newly added user
            $notification->sendToDatabase($addedUser)->broadcast($addedUser)->persistent();

            // UI notification for current user
            Notification::make()
                ->title('Member added successfully')
                ->success()
                ->send();

            $this->dispatch('refresh');
            $this->dispatch('close-modal');

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error adding member')
                ->danger()
                ->send();
        }
    }

    public function removeMember($userId)
    {
        if (auth()->user()->hasRole('staff')) {
            return;
        }

        try {
            // Get user details before removing
            $removedUser = User::find($userId);
            $client = $this->project->client;

            $this->project->userProject()
                ->where('user_id', $userId)
                ->delete();

            // Create notification for removed user
            $notification = Notification::make()
                ->title('Project Removal')
                ->body(sprintf(
                    "<strong>Client:</strong> %s<br><strong>Project:</strong> %s<br><strong>Removed by:</strong> %s<br><strong>Removal Date:</strong> %s",
                    $client->name,
                    $this->project->name,
                    auth()->user()->name,
                    now()->format('d M Y H:i')
                ))
                ->warning();

            // Send to the removed user
            $notification->sendToDatabase($removedUser)->broadcast($removedUser)->persistent();

            // UI notification for current user
            Notification::make()
                ->title('Member removed successfully')
                ->success()
                ->send();

            $this->dispatch('refresh');

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error removing member')
                ->danger()
                ->send();
        }
    }


    public function render()
    {
        return view('livewire.project-detail.project-detail-user', [
            'users' => $this->users,
            'availableUsers' => $this->availableUsers
        ]);
    }
}