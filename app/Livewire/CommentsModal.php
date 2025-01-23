<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Task;
use Livewire\Component;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class CommentsModal extends Component implements HasForms
{
    use InteractsWithForms;

    public $modelType;
    public $modelId;
    public $comments;
    public string $content = '';
    public $task;

    
    public function mount($modelType, $modelId)
    {
        $this->modelType = $modelType;
        $this->modelId = $modelId;
        $this->loadComments();
        $this->loadTask();
    }

    public function loadTask()
    {
        $this->task = Task::with(['projectStep'])->find($this->modelId);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                RichEditor::make('content')
                    ->label('')
                    ->placeholder('Write your comment here...')
                    ->required()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'bulletList',
                    ])
            ]);
    }

    public function loadComments()
    {
        $this->comments = Comment::where('commentable_type', $this->modelType)
            ->where('commentable_id', $this->modelId)
            ->with(['user'])
            ->latest()
            ->get();
    }

    public function addComment()
    {
        $this->validate([
            'content' => 'required|max:1000',
        ]);

        // Create the comment
        $comment = Comment::create([
            'user_id' => auth()->id(),
            'commentable_type' => $this->modelType,
            'commentable_id' => $this->modelId,
            'content' => $this->content,
        ]);

        // UI Notification
        Notification::make()
            ->title('Comment added successfully')
            ->success()
            ->send();

        // Database Notification
        $plainContent = strip_tags($this->content); // Remove HTML tags for notification
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

        // Reset form and reload comments
        $this->content = '';
        $this->loadComments();
    }

    public function render()
    {
        return view('livewire.comments-modal');
    }
}