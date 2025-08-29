<?php

namespace App\Livewire\DailyTask;

use App\Models\DailyTask;
use App\Models\DailyTaskSubtask;
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
    public ?array $newSubtaskData = [];
    public ?int $editingSubtaskId = null;
    public ?array $editSubtaskData = [];
    
    // For editing task details
    public bool $editingTitle = false;
    public bool $editingDescription = false;
    public ?array $taskEditData = [];

    // Initialize properties
    protected function initializeProperties()
    {
        $this->editingTitle = false;
        $this->editingDescription = false;
        $this->taskEditData = [];
    }

    protected $listeners = [
        'openTaskDetailModal' => 'openModal',
    ];

    protected function getForms(): array
    {
        return [
            'commentForm',
            'newSubtaskForm',
            'editSubtaskForm',
            'taskEditForm',
            'descriptionForm',
        ];
    }

    /**
     * Description Edit Form - Separate form for rich editing
     */
    public function descriptionForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\RichEditor::make('description')
                    ->placeholder('Tulis deskripsi task...')
                    ->maxLength(5000)
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'bulletList',
                        'orderedList',
                        'link',
                        'undo',
                        'redo',
                    ])
                    ->hiddenLabel()
                    ->required(false),
            ])
            ->statePath('taskEditData');
    }

    /**
     * Task Edit Form
     */
    public function taskEditForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->hiddenLabel(),
                Forms\Components\Textarea::make('description')
                    ->placeholder('Tulis deskripsi task...')
                    ->maxLength(1000)
                    ->rows(4)
                    ->hiddenLabel(),
            ])
            ->statePath('taskEditData');
    }

    /**
     * New Subtask Form
     */
    public function newSubtaskForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->placeholder('Tambahkan subtask baru...')
                    ->required()
                    ->maxLength(255)
                    ->hiddenLabel(),
            ])
            ->statePath('newSubtaskData');
    }

    /**
     * Edit Subtask Form
     */
    public function editSubtaskForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->hiddenLabel(),
            ])
            ->statePath('editSubtaskData');
    }

    /**
     * Comment Form Definition
     */
    public function commentForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('content')
                    ->placeholder('Tulis komentar...')
                    ->required()
                    ->maxLength(1000)
                    ->rows(3)
                    ->hiddenLabel(),
            ])
            ->statePath('commentData');
    }

    public function mount(): void
    {
        // Initialize properties
        $this->editingTitle = false;
        $this->editingDescription = false;
        $this->taskEditData = [];
        
        $this->commentForm->fill();
        $this->newSubtaskForm->fill(['title' => '']);
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
            // Initialize task edit form
            $this->taskEditForm->fill([
                'title' => $this->task->title,
                'description' => $this->task->description,
            ]);
            
            // Initialize description form separately
            $this->descriptionForm->fill([
                'description' => $this->task->description,
            ]);
            
            $this->dispatch('open-modal', id: 'task-detail-modal');
        }
    }

    public function closeModal(): void
    {
        $this->task = null;
        $this->editingTitle = false;
        $this->editingDescription = false;
        $this->dispatch('close-modal', id: 'task-detail-modal');
    }

    public function startEditTitle(): void
    {
        $this->editingTitle = true;
    }

    public function saveTitle(): void
    {
        if (!$this->task) return;

        $data = $this->taskEditForm->getState();
        $this->task->update(['title' => $data['title']]);
        $this->task->refresh();
        $this->editingTitle = false;
        
        $this->dispatch('taskUpdated');
        
        Notification::make()
            ->title('Judul berhasil diperbarui')
            ->success()
            ->duration(2000)
            ->send();
    }

    public function cancelEditTitle(): void
    {
        $this->editingTitle = false;
        $this->taskEditForm->fill([
            'title' => $this->task->title,
            'description' => $this->task->description,
        ]);
    }

    public function startEditDescription(): void
    {
        $this->editingDescription = true;
    }

    public function saveDescription(): void
    {
        if (!$this->task) return;

        $data = $this->descriptionForm->getState();
        $this->task->update(['description' => $data['description']]);
        $this->task->refresh();
        $this->editingDescription = false;
        
        $this->dispatch('taskUpdated');
        
        Notification::make()
            ->title('Deskripsi berhasil diperbarui')
            ->success()
            ->duration(2000)
            ->send();
    }

    public function cancelEditDescription(): void
    {
        $this->editingDescription = false;
        $this->descriptionForm->fill([
            'description' => $this->task->description,
        ]);
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
        
        $statusLabels = [
            'pending' => 'Tertunda',
            'in_progress' => 'Sedang Dikerjakan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan'
        ];
        
        Notification::make()
            ->title('Status Diperbarui')
            ->body("Status diubah menjadi " . ($statusLabels[$status] ?? $status))
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
        
        $priorityLabels = [
            'low' => 'Rendah',
            'normal' => 'Normal',
            'high' => 'Tinggi',
            'urgent' => 'Mendesak'
        ];
        
        Notification::make()
            ->title('Prioritas Diperbarui')
            ->body("Prioritas diubah menjadi " . ($priorityLabels[$priority] ?? $priority))
            ->success()
            ->duration(3000)
            ->send();
    }

    public function addSubtask(): void
    {
        if (!$this->task) return;

        $data = $this->newSubtaskForm->getState();
        
        $this->task->subtasks()->create([
            'title' => $data['title'],
            'status' => 'pending',
        ]);

        $this->newSubtaskForm->fill(['title' => '']);
        $this->task->refresh();
        
        Notification::make()
            ->title('Subtask berhasil ditambahkan')
            ->success()
            ->duration(2000)
            ->send();
    }

    public function startEditSubtask(int $subtaskId): void
    {
        $subtask = $this->task->subtasks->find($subtaskId);
        if ($subtask) {
            $this->editingSubtaskId = $subtaskId;
            $this->editSubtaskForm->fill(['title' => $subtask->title]);
        }
    }

    public function saveSubtaskEdit(): void
    {
        if (!$this->editingSubtaskId) return;
        
        $data = $this->editSubtaskForm->getState();
        $subtask = $this->task->subtasks->find($this->editingSubtaskId);
        
        if ($subtask) {
            $subtask->update(['title' => $data['title']]);
            $this->editingSubtaskId = null;
            $this->editSubtaskForm->fill(['title' => '']);
            $this->task->refresh();
            
            Notification::make()
                ->title('Subtask berhasil diperbarui')
                ->success()
                ->duration(2000)
                ->send();
        }
    }

    public function cancelEditSubtask(): void
    {
        $this->editingSubtaskId = null;
        $this->editSubtaskForm->fill(['title' => '']);
    }

    public function deleteSubtask(int $subtaskId): void
    {
        $subtask = $this->task->subtasks->find($subtaskId);
        if ($subtask) {
            $subtask->delete();
            $this->task->refresh();
            
            Notification::make()
                ->title('Subtask berhasil dihapus')
                ->success()
                ->duration(2000)
                ->send();
        }
    }

    public function toggleSubtask(int $subtaskId): void
    {
        $subtask = DailyTaskSubtask::find($subtaskId);
        if ($subtask) {
            $newStatus = $subtask->status === 'completed' ? 'pending' : 'completed';
            $subtask->update(['status' => $newStatus]);
            $this->task->refresh();
            $this->dispatch('taskUpdated');

            Notification::make()
                ->title('Subtask diperbarui')
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
            ->title('Komentar ditambahkan')
            ->success()
            ->duration(2000)
            ->send();
    }

    public function getStatusOptions(): array
    {
        return [
            'pending' => 'Tertunda',
            'in_progress' => 'Sedang Dikerjakan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
    }

    public function getPriorityOptions(): array
    {
        return [
            'low' => 'Rendah',
            'normal' => 'Normal',
            'high' => 'Tinggi',
            'urgent' => 'Mendesak',
        ];
    }

    public function deleteAction(): Actions\Action
    {
        return Actions\Action::make('delete')
            ->label('')
            ->icon('heroicon-o-trash')
            ->color('gray')
            ->size('sm')
            ->tooltip('Hapus task')
            ->requiresConfirmation()
            ->modalHeading('Hapus Task')
            ->modalDescription('Yakin ingin menghapus task ini? Aksi ini tidak dapat dibatalkan.')
            ->action(function () {
                $this->task->delete();
                $this->closeModal();
                $this->dispatch('taskUpdated');
                
                Notification::make()
                    ->title('Task berhasil dihapus')
                    ->success()
                    ->send();
            });
    }

    public function render()
    {
        return view('livewire.daily-task.daily-task-detail-modal');
    }
}