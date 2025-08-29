<?php

namespace App\Livewire\DailyTask;

use App\Models\DailyTask;
use App\Models\Project;
use App\Models\User;
use Livewire\Component;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use App\Models\Client;

class DailyTaskItem extends Component implements HasForms
{
    use InteractsWithForms;

    public DailyTask $task;
    public ?int $selectedClientId = null;
    public bool $showSubtasks = false;
    public ?array $newSubtaskData = [];

    protected $listeners = ['taskUpdated' => '$refresh'];

    public function mount(DailyTask $task): void
    {
        $this->task = $task;
        
        // Set selected client based on current project
        if ($this->task->project && $this->task->project->client_id) {
            $this->selectedClientId = $this->task->project->client_id;
        }
        
        $this->newSubtaskForm->fill([
            'title' => '',
        ]);
    }

    protected function getForms(): array
    {
        return [
            'newSubtaskForm',
        ];
    }

    public function viewDetails(): void
    {
        $this->dispatch('openTaskDetailModal', taskId: $this->task->id);
    }

    public function editTask(): void
    {
        // Redirect to edit page or emit event to parent
        // $this->redirect(route('filament.admin.resources.daily-tasks.edit', $this->task->id));
    }

    public function deleteTask(): void
    {
        $this->task->delete();
        
        Notification::make()
            ->title('Task Deleted')
            ->success()
            ->send();
            
        $this->dispatch('taskUpdated');
    }

    /**
     * New Subtask Form Definition
     */
    public function newSubtaskForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->placeholder('Add a subtask...')
                    ->required()
                    ->maxLength(255),
            ])
            ->statePath('newSubtaskData');
    }

    public function toggleSubtasks(): void
    {
        $this->showSubtasks = !$this->showSubtasks;
    }

    public function updateSelectedClient(int $clientId): void
    {
        $this->selectedClientId = $clientId;
        
        // If current project doesn't belong to selected client, clear it
        if ($this->task->project && $this->task->project->client_id !== $clientId) {
            $this->updateProject(null);
        }
    }

    public function getClientOptions(): array
    {
        return Client::where('status', 'Active')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getProjectOptions(): array
    {
        if (!$this->selectedClientId) {
            return [];
        }
        
        return Project::where('client_id', $this->selectedClientId)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function updateStatus(string $status): void
    {
        $this->task->update(['status' => $status]);
        
        $this->dispatch('taskUpdated');
        
        Notification::make()
            ->title('Status Updated')
            ->body("Status changed to " . ucfirst($status))
            ->success()
            ->send();
    }

    public function toggleTaskCompletion(): void
    {
        $newStatus = $this->task->status === 'completed' ? 'pending' : 'completed';
        $this->updateStatus($newStatus);
    }

    public function toggleSubtask(int $subtaskId): void
    {
        $subtask = DailyTaskSubtask::find($subtaskId);
        if ($subtask) {
            $newStatus = $subtask->status === 'completed' ? 'pending' : 'completed';
            $subtask->update(['status' => $newStatus]);
            $this->task->refresh();
        }
    }

    public function addSubtask(): void
    {
        $data = $this->newSubtaskForm->getState();

        $this->task->subtasks()->create([
            'title' => $data['title'],
            'status' => 'pending',
        ]);

        $this->newSubtaskForm->fill(['title' => '']);
        $this->task->refresh();
        
        Notification::make()
            ->title('Subtask Added')
            ->success()
            ->send();
    }

    public function deleteSubtask(int $subtaskId): void
    {
        DailyTaskSubtask::find($subtaskId)?->delete();
        $this->task->refresh();
        
        Notification::make()
            ->title('Subtask Deleted')
            ->success()
            ->send();
    }

    public function getPriorityColor(): string
    {
        return match ($this->task->priority) {
            'urgent' => 'danger',
            'high' => 'warning',
            'normal' => 'primary',
            'low' => 'gray',
            default => 'gray',
        };
    }

    public function getStatusColor(): string
    {
        return match ($this->task->status) {
            'completed' => 'success',
            'in_progress' => 'warning',
            'pending' => 'gray',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    public function getStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    public function updatePriority(string $priority): void
    {
        $this->task->update(['priority' => $priority]);
        
        $this->dispatch('taskUpdated');
        
        Notification::make()
            ->title('Priority Updated')
            ->body("Priority changed to " . ucfirst($priority))
            ->success()
            ->send();
    }

    public function updateProject($projectId): void
    {
        $this->task->update(['project_id' => $projectId]);
        
        $this->dispatch('taskUpdated');
        
        Notification::make()
            ->title('Project Updated')
            ->body($projectId ? "Project assigned" : "Project removed")
            ->success()
            ->send();
    }

    public function assignUser(int $userId): void
    {
        if (!$this->task->assignedUsers->contains($userId)) {
            $this->task->assignedUsers()->attach($userId);
            $this->task->refresh();
            
            $userName = User::find($userId)?->name ?? 'User';
            
            Notification::make()
                ->title('User Assigned')
                ->body("Assigned to {$userName}")
                ->success()
                ->send();
                
            $this->dispatch('taskUpdated');
        }
    }

    public function unassignUser(int $userId): void
    {
        $this->task->assignedUsers()->detach($userId);
        $this->task->refresh();
        
        $userName = User::find($userId)?->name ?? 'User';
        
        Notification::make()
            ->title('User Unassigned')
            ->body("Unassigned from {$userName}")
            ->success()
            ->send();
            
        $this->dispatch('taskUpdated');
    }

    public function getPriorityOptions(): array
    {
        return [
            'low' => 'Low',
            'normal' => 'Normal',
            'high' => 'High',
            'urgent' => 'Urgent',
        ];
    }

    public function getUserOptions(): array
    {
        return User::orderBy('name')->pluck('name', 'id')->toArray();
}

    public function render()
    {
        return view('livewire.daily-task.daily-task-item');
    }
}