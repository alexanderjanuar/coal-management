<?php

namespace App\Livewire\DailyTask;

use App\Models\DailyTask;
use App\Models\User;
use Livewire\Component;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions;

class DailyTaskDetailModal extends Component implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    public ?DailyTask $task = null;
    public ?array $commentData = [];

    protected $listeners = [
        'openTaskDetailModal' => 'openModal',
    ];

    public function mount(): void
    {
        $this->commentForm->fill();
    }

    protected function getForms(): array
    {
        return [
            'commentForm',
        ];
    }

    /**
     * Comment Form Definition - Simplified for Notion-like experience
     */
    public function commentForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('content')
                    ->placeholder('Add a comment...')
                    ->required()
                    ->maxLength(1000)
                    ->rows(3)
                    ->hiddenLabel(),
            ])
            ->statePath('commentData');
    }

    public function openModal(int $taskId): void
    {
        $this->task = DailyTask::with([
            'project', 
            'creator', 
            'assignedUsers', 
            'subtasks',
            'comments.user'
        ])->find($taskId);
        
        if ($this->task) {
            $this->dispatch('open-modal', id: 'task-detail-modal');
        }
    }

    public function closeModal(): void
    {
        $this->task = null;
        $this->dispatch('close-modal', id: 'task-detail-modal');
    }

    public function toggleTaskCompletion(): void
    {
        if (!$this->task) return;

        $newStatus = $this->task->status === 'completed' ? 'pending' : 'completed';
        $this->updateStatus($newStatus);
    }

    public function updateStatus(string $status): void
    {
        if (!$this->task) return;

        $this->task->update(['status' => $status]);
        $this->task->refresh();
        
        $this->dispatch('taskUpdated');
        
        Notification::make()
            ->title('Status Updated')
            ->body("Status changed to " . ucfirst(str_replace('_', ' ', $status)))
            ->success()
            ->duration(3000)
            ->send();
    }

    public function updatePriority(string $priority): void
    {
        if (!$this->task) return;

        $this->task->update(['priority' => $priority]);
        $this->task->refresh();
        
        $this->dispatch('taskUpdated');
        
        Notification::make()
            ->title('Priority Updated')
            ->body("Priority changed to " . ucfirst($priority))
            ->success()
            ->duration(3000)
            ->send();
    }

    public function toggleSubtask(int $subtaskId): void
    {
        $subtask = DailyTaskSubtask::find($subtaskId);
        if ($subtask) {
            $newStatus = $subtask->status === 'completed' ? 'pending' : 'completed';
            $subtask->update(['status' => $newStatus]);
            $this->task->refresh();
            $this->dispatch('taskUpdated');

            // Subtle notification for subtask updates
            Notification::make()
                ->title('Subtask updated')
                ->success()
                ->duration(2000)
                ->send();
        }
    }

    public function addComment(): void
    {
        if (!$this->task) return;

        $data = $this->commentForm->getState();

        $this->task->comments()->create([
            'user_id' => auth()->id(),
            'content' => $data['content'],
            'status' => 'approved',
        ]);

        $this->commentForm->fill();
        $this->task->refresh();
        
        Notification::make()
            ->title('Comment added')
            ->success()
            ->duration(2000)
            ->send();
    }

    public function getStatusColor(): string
    {
        if (!$this->task) return 'gray';
        
        return match ($this->task->status) {
            'completed' => 'success',
            'in_progress' => 'warning',
            'pending' => 'gray',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    public function getPriorityColor(): string
    {
        if (!$this->task) return 'gray';
        
        return match ($this->task->priority) {
            'urgent' => 'danger',
            'high' => 'warning',
            'normal' => 'primary',
            'low' => 'gray',
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

    public function getPriorityOptions(): array
    {
        return [
            'low' => 'Low',
            'normal' => 'Normal',
            'high' => 'High',
            'urgent' => 'Urgent',
        ];
    }

    public function editAction(): Actions\Action
    {
        return Actions\Action::make('edit')
            ->label('')
            ->icon('heroicon-o-pencil')
            ->color('gray')
            ->size('sm')
            ->tooltip('Edit task');
    }

    public function deleteAction(): Actions\Action
    {
        return Actions\Action::make('delete')
            ->label('')
            ->icon('heroicon-o-trash')
            ->color('gray')
            ->size('sm')
            ->tooltip('Delete task')
            ->requiresConfirmation()
            ->modalHeading('Delete Task')
            ->modalDescription('Are you sure you want to delete this task? This action cannot be undone.')
            ->action(function () {
                $this->task->delete();
                $this->closeModal();
                $this->dispatch('taskUpdated');
                
                Notification::make()
                    ->title('Task deleted')
                    ->success()
                    ->send();
            });
    }

    public function render()
    {
        return view('livewire.daily-task.daily-task-detail-modal');
    }
}