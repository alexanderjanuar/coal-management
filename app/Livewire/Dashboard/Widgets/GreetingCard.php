<?php

namespace App\Livewire\Dashboard\Widgets;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Project;
use App\Models\DailyTask;
use Carbon\Carbon;

class GreetingCard extends Component
{
    #[Computed]
    public function greeting(): string
    {
        $hour = now()->hour;
        
        if ($hour >= 5 && $hour < 12) {
            return 'Selamat Pagi';
        } elseif ($hour >= 12 && $hour < 15) {
            return 'Selamat Siang';
        } elseif ($hour >= 15 && $hour < 18) {
            return 'Selamat Sore';
        } else {
            return 'Selamat Malam';
        }
    }

    #[Computed]
    public function greetingEmoji(): string
    {
        $hour = now()->hour;
        
        if ($hour >= 5 && $hour < 12) {
            return '☀️';
        } elseif ($hour >= 12 && $hour < 15) {
            return '🌤️';
        } elseif ($hour >= 15 && $hour < 18) {
            return '🌅';
        } else {
            return '🌙';
        }
    }

    #[Computed]
    public function userName(): string
    {
        $user = auth()->user();
        $fullName = $user->name ?? 'User';
        
        // Get first name only
        $parts = explode(' ', $fullName);
        return $parts[0];
    }

    #[Computed]
    public function motivationalQuote(): array
    {
        $quotes = [
            ['quote' => 'The best way to predict the future is to create it.', 'author' => 'Peter Drucker'],
            ['quote' => 'Excellence is not an act, but a habit.', 'author' => 'Aristotle'],
            ['quote' => 'The secret of getting ahead is getting started.', 'author' => 'Mark Twain'],
            ['quote' => 'Success is not final, failure is not fatal: it is the courage to continue that counts.', 'author' => 'Churchill'],
            ['quote' => 'The only way to do great work is to love what you do.', 'author' => 'Steve Jobs'],
            ['quote' => 'In the middle of difficulty lies opportunity.', 'author' => 'Albert Einstein'],
            ['quote' => 'What you do today can improve all your tomorrows.', 'author' => 'Ralph Marston'],
            ['quote' => 'Success usually comes to those who are too busy to be looking for it.', 'author' => 'Henry Thoreau'],
            ['quote' => 'The harder you work for something, the greater you will feel when you achieve it.', 'author' => 'Anonymous'],
            ['quote' => 'You cant add days to your life, but you can add life your days', 'author' => 'Norman Vaughan'],
        ];
        
        // Use day of year to get a consistent quote per day
        $index = now()->dayOfYear % count($quotes);
        return $quotes[$index];
    }

    #[Computed]
    public function todayStats(): array
    {
        $user = auth()->user();
        $today = today();
        
        // Get today's tasks
        $tasksQuery = DailyTask::where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('assignedUsers', fn ($s) => $s->where('user_id', $user->id));
            })
            ->whereDate('task_date', $today);
        
        $totalTasks = (clone $tasksQuery)->count();
        $completedTasks = (clone $tasksQuery)->where('status', 'completed')->count();
        
        // Get active projects count
        $activeProjects = $this->getActiveProjectsCount();
        
        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks' => max(0, $totalTasks - $completedTasks),
            'active_projects' => $activeProjects,
            'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0,
        ];
    }

    private function getActiveProjectsCount(): int
    {
        $user = auth()->user();
        $query = Project::whereNotIn('status', ['completed', 'canceled']);

        if (!$user->hasRole('super-admin') && !$user->hasRole('director')) {
            $clientIds = $user->userClients()->pluck('client_id')->toArray();

            if (!empty($clientIds)) {
                $query->whereIn('client_id', $clientIds)
                    ->where(function ($sub) use ($user) {
                        $sub->where('pic_id', $user->id)
                            ->orWhereHas('userProject', function ($q) use ($user) {
                                $q->where('user_id', $user->id);
                            });
                    });
            }
        }

        return $query->count();
    }

    #[Computed]
    public function currentDate(): string
    {
        return now()->locale('id')->translatedFormat('l, d F Y');
    }

    #[Computed]
    public function currentTime(): string
    {
        return now()->format('H:i');
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.greeting-card');
    }
}
