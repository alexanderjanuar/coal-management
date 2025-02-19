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
use Asmit\FilamentMention\Forms\Components\RichMentionEditor;

class ProjectDetailComments extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [
        'comment' => ''
    ];

    public ?array $replyData = [
        'reply' => ''
    ];

    public Task $task;


    public $showReplyForm = null;

    public function mount(Task $task): void
    {
        $this->task = $task;
        $this->commentForm->fill();
        $this->replyForm->fill();
    }

    public function commentForm(Form $form): Form
    {
        return $form
            ->schema([
                RichMentionEditor::make('comment')
                    ->label('Add a comment')
                    ->id('comment-editor-' . $this->task->id)
                    ->placeholder('Type your comment here...')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'link',
                        'bulletList',
                        'orderedList',
                        '|',  // This adds a separator
                        'attachFiles',
                        'uploadImage',
                    ])                    
                    ->required()
                    ->columnSpanFull()
                    ->live()
            ])
            ->statePath('data');
    }

    public function replyForm(Form $form): Form
    {
        return $form
            ->schema([
                RichEditor::make('reply')
                    ->label('Your reply')
                    ->id('comment-editor-' . $this->task->id)
                    ->placeholder('Type your reply here...')
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
            ->statePath('replyData');
    }

    protected function getForms(): array
    {
        return [
            'commentForm',
            'replyForm',
        ];
    }

    public function toggleReplyForm(?int $commentId): void
    {
        $this->showReplyForm = $this->showReplyForm === $commentId ? null : $commentId;
        if (!$this->showReplyForm) {
            $this->replyData = ['reply' => ''];
            $this->replyForm->fill();
        }
    }

    public function createComment(): void
    {
        $data = $this->commentForm->getState();

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'commentable_type' => Task::class,
            'commentable_id' => $this->task->id,
            'content' => $data['comment'],
            'status' => 'approved',
        ]);

        $this->sendNotifications($comment, 'Comment added successfully');

        // Reset form
        $this->data['comment'] = '';
        $this->commentForm->fill();
    }

    public function replyToComment(int $parentId): void
    {
        $data = $this->replyForm->getState();

        $reply = Comment::create([
            'user_id' => auth()->id(),
            'commentable_type' => Task::class,
            'commentable_id' => $this->task->id,
            'content' => $data['reply'],
            'parent_id' => $parentId,
            'status' => 'approved',
        ]);

        $this->sendNotifications($reply, 'Reply added successfully');

        // Reset form and hide reply form
        $this->replyData = ['reply' => ''];
        $this->replyForm->fill();
        $this->showReplyForm = null;
    }

    protected function sendNotifications(Comment $comment, string $successMessage): void
    {
        // UI Notification
        Notification::make()
            ->title($successMessage)
            ->success()
            ->send();

        // Database Notification
        $plainContent = strip_tags($comment->content);
        $truncatedContent = Str::limit($plainContent, 100);

        $notificationType = $comment->parent_id ? 'Reply' : 'New Comment';

        Notification::make()
            ->title($notificationType . ' on Task: ' . $this->task->title)
            ->body($truncatedContent)
            ->icon('heroicon-o-chat-bubble-left-right')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->button()
                    ->label('View ' . $notificationType)
                    ->url(route('filament.admin.pages.dashboard', ['task' => $this->task->id]))
            ])
            ->sendToDatabase(auth()->user());
    }

    public function render()
    {
        return view('livewire.project-detail.project-detail-comments', [
            'comments' => $this->task->comments()
                ->with(['user', 'replies.user'])
                ->whereNull('parent_id')
                ->latest()
                ->get()
        ]);
    }
}