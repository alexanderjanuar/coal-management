<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Comment;
use App\Models\Task;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class ProjectDetailComments extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [
        'comment' => ''
    ];

    public Task $task;

    public function mount(Task $task): void
    {
        $this->task = $task;
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                RichEditor::make('comment')
                    ->label('Add a comment')
                    ->placeholder('Type your comment here...')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'link',
                        'bulletList',
                        'orderedList',
                    ])
                    ->required()
                    ->columnSpanFull()
                    ->live()
            ])
            ->statePath('data');
    }

    public function createComment(): void
    {
        $data = $this->form->getState();

        Comment::create([
            'user_id' => auth()->id(),
            'commentable_type' => Task::class,
            'commentable_id' => $this->task->id,
            'content' => $data['comment'],
            'status' => 'approved',
        ]);

        // UI Notification
        Notification::make()
            ->title('Comment added successfully')
            ->success()
            ->send();

        // Database Notification
        $plainContent = strip_tags($data['comment']); // Remove HTML tags for notification
        $truncatedContent = Str::limit($plainContent, 100); // Limit to 100 characters

        Notification::make()
            ->title('New Comment on Task: ' . $this->task->title)
            ->body($truncatedContent)
            ->icon('heroicon-o-chat-bubble-left-right')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->button()
                    ->label('View Comment')
                    ->url(route('filament.admin.pages.dashboard', ['task' => $this->task->id])) // Adjust route as needed
            ])
            ->sendToDatabase(auth()->user());

        // Reset form
        $this->data['comment'] = '';
        $this->form->fill();
    }

    public function render()
    {
        return view('livewire.project-detail-comments', [
            'comments' => $this->task->comments()->with('user')->latest()->get()
        ]);
    }
}