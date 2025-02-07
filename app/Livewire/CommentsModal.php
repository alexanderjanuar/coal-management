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
    public $task;
    public $showReplyForm = null;

    public ?array $data = [
        'comment' => ''
    ];

    public ?array $replyData = [
        'reply' => ''
    ];

    public function mount($modelType, $modelId)
    {
        $this->modelType = $modelType;
        $this->modelId = $modelId;
        $this->loadComments();
        $this->loadTask();
        $this->commentForm->fill();
        $this->replyForm->fill();
    }

    public function commentForm(Form $form): Form
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
                        'bulletList',
                    ])
                    ->required()
                    ->columnSpanFull()
            ])
            ->statePath('data');
    }

    public function replyForm(Form $form): Form
    {
        return $form
            ->schema([
                RichEditor::make('reply')
                    ->label('Your reply')
                    ->placeholder('Type your reply here...')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'bulletList',
                    ])
                    ->required()
                    ->columnSpanFull()
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

    public function loadTask()
    {
        $this->task = Task::with(['projectStep'])->find($this->modelId);
    }

    public function loadComments()
    {
        $this->comments = Comment::where('commentable_type', $this->modelType)
            ->where('commentable_id', $this->modelId)
            ->with(['user', 'replies.user'])
            ->whereNull('parent_id')
            ->latest()
            ->get();
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
            'commentable_type' => $this->modelType,
            'commentable_id' => $this->modelId,
            'content' => $data['comment'],
            'status' => 'approved',
        ]);

        $this->sendNotifications($comment, 'Comment added successfully');

        // Reset form
        $this->data['comment'] = '';
        $this->commentForm->fill();
        $this->loadComments();
    }

    public function replyToComment(int $parentId): void
    {
        $data = $this->replyForm->getState();

        $reply = Comment::create([
            'user_id' => auth()->id(),
            'commentable_type' => $this->modelType,
            'commentable_id' => $this->modelId,
            'content' => $data['reply'],
            'parent_id' => $parentId,
            'status' => 'approved',
        ]);

        $this->sendNotifications($reply, 'Reply added successfully');

        // Reset form and hide reply form
        $this->replyData = ['reply' => ''];
        $this->replyForm->fill();
        $this->showReplyForm = null;
        $this->loadComments();
    }

    protected function sendNotifications(Comment $comment, string $successMessage): void
    {
        // UI Notification for current user
        Notification::make()
            ->title($successMessage)
            ->success()
            ->send();

        // Get clean content for notification
        $plainContent = strip_tags($comment->content);
        $truncatedContent = Str::limit($plainContent, 100);

        // Determine notification type
        $notificationType = $comment->parent_id ? 'Reply' : 'New Comment';
        $targetType = $this->modelType === Task::class ? 'Task' : 'Item';

        // Create the notification content
        $notification = Notification::make()
            ->title($notificationType . ' on ' . $targetType . ': ' . $this->task->title)
            ->body($truncatedContent)
            ->icon('heroicon-o-chat-bubble-left-right')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->button()
                    ->label('View ' . $notificationType)
                    ->url(route('filament.admin.pages.dashboard', ['task' => $this->task->id]))
            ]);

        // Get all users related to the project
        $projectUsers = $this->task->projectStep->project->userProject()
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()  // Remove any null users
            ->unique('id')
            ->reject(function ($user) use ($comment) {
                return $user->id === $comment->user_id;
            });

        // Send notifications to all project users
        foreach ($projectUsers as $user) {
            $notification->sendToDatabase($user);
        }
    }

    public function getCommentCountProperty()
    {
        return $this->comments?->count() ?? 0;
    }

    public function getParticipantCountProperty()
    {
        $userIds = collect();

        $this->comments?->each(function ($comment) use ($userIds) {
            $userIds->push($comment->user_id);
            $comment->replies->each(fn($reply) => $userIds->push($reply->user_id));
        });

        return $userIds->unique()->count();
    }

    public function getLatestActivityProperty()
    {
        return $this->comments?->first()?->created_at?->diffForHumans() ?? 'No activity';
    }

    public function render()
    {
        return view('livewire.dashboard.comments-modal', [
            'commentCount' => $this->commentCount,
            'participantCount' => $this->participantCount,
            'latestActivity' => $this->latestActivity,
        ]);
    }
}