<?php

namespace App\Livewire\Widget;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class RecentActivityTable extends Component
{
    use WithPagination;

    public int $perPage = 8;
    public string $dateFilter = 'today';
    public string $search = '';

    protected $queryString = [
        'dateFilter' => ['except' => 'today'],
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateFilter()
    {
        $this->resetPage();
    }

    protected function getActivitiesQuery(): Builder
    {
        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->whereDoesntHave('causer', function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['super-admin', 'admin']);
                });
            });

        // Apply date filter
        match ($this->dateFilter) {
            'today' => $query->whereDate('created_at', Carbon::today()),
            'week' => $query->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ]),
            'month' => $query->whereMonth('created_at', Carbon::now()->month)
                           ->whereYear('created_at', Carbon::now()->year),
            'year' => $query->whereYear('created_at', Carbon::now()->year),
            default => $query, // 'all' - no date filter
        };

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('causer', function ($causerQ) {
                      $causerQ->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // User permission filtering
        if (!Auth::user()->hasRole('super-admin')) {
            $query->where(function ($q) {
                // Activities where the user is the causer
                $q->where('causer_id', Auth::id())
                    ->where('causer_type', get_class(Auth::user()));

                // Or activities related to projects/clients the user has access to
                $q->orWhereHasMorph('subject', ['App\Models\Project', 'App\Models\Client'], function ($subQ) {
                    // For projects
                    $subQ->when(
                        fn($q) => $q->getModel() instanceof \App\Models\Project,
                        fn($q) => $q->whereIn('client_id', function ($clientQ) {
                            $clientQ->select('client_id')
                                ->from('user_clients')
                                ->where('user_id', Auth::id());
                        })
                    );

                    // For clients directly
                    $subQ->when(
                        fn($q) => $q->getModel() instanceof \App\Models\Client,
                        fn($q) => $q->whereIn('id', function ($clientQ) {
                            $clientQ->select('client_id')
                                ->from('user_clients')
                                ->where('user_id', Auth::id());
                        })
                    );
                });

                // Or related to document submissions for user's clients
                $q->orWhereHasMorph('subject', ['App\Models\RequiredDocument'], function ($subQ) {
                    $subQ->whereHas('projectStep.project', function ($projectQ) {
                        $projectQ->whereIn('client_id', function ($clientQ) {
                            $clientQ->select('client_id')
                                ->from('user_clients')
                                ->where('user_id', Auth::id());
                        });
                    });
                });
            });
        }

        return $query->latest();
    }

    public function getActivitiesProperty()
    {
        return $this->getActivitiesQuery()->paginate($this->perPage);
    }

    public function formatAction($record): string
    {
        $type = match ($record->subject_type) {
            'App\Models\Project' => 'Project',
            'App\Models\Client' => 'Client information',
            'App\Models\RequiredDocument' => 'Document',
            'App\Models\SubmittedDocument' => 'Document',
            default => 'Record'
        };

        $action = match ($record->description) {
            'created' => 'submitted',
            'updated' => 'updated',
            'deleted' => 'removed',
            default => $record->description
        };

        return "{$type} {$action}";
    }

    public function getClientName($record): string
    {
        if ($record->subject) {
            // If subject is a client directly
            if (str_contains($record->subject_type, 'Client')) {
                return $record->subject->name ?? 'N/A';
            }

            // If subject is a project
            if (str_contains($record->subject_type, 'Project')) {
                return $record->subject->client->name ?? 'N/A';
            }

            // If subject is a required document
            if (
                str_contains($record->subject_type, 'RequiredDocument') &&
                $record->subject->projectStep &&
                $record->subject->projectStep->project
            ) {
                return $record->subject->projectStep->project->client->name ?? 'N/A';
            }

            // If subject is a submitted document
            if (
                str_contains($record->subject_type, 'SubmittedDocument') &&
                $record->subject->requiredDocument &&
                $record->subject->requiredDocument->projectStep &&
                $record->subject->requiredDocument->projectStep->project
            ) {
                return $record->subject->requiredDocument->projectStep->project->client->name ?? 'N/A';
            }
        }

        return 'N/A';
    }

    public function getViewUrl($record): ?string
    {
        if (!$record->subject) {
            return null;
        }

        $subjectType = $record->subject_type;

        // Handle different resource types
        if (str_contains($subjectType, 'Project')) {
            return route('filament.admin.resources.projects.view', $record->subject_id);
        } elseif (str_contains($subjectType, 'Client')) {
            return route('filament.admin.resources.clients.view', $record->subject_id);
        } elseif (str_contains($subjectType, 'RequiredDocument')) {
            // For documents, navigate to the parent project
            if ($record->subject && $record->subject->projectStep && $record->subject->projectStep->project) {
                return route('filament.admin.resources.projects.view', $record->subject->projectStep->project->id);
            }
        }

        return null;
    }

    public function getUserAvatar($record): string
    {
        if ($record->causer && method_exists($record->causer, 'getAvatarUrl')) {
            return $record->causer->getAvatarUrl();
        }

        return asset('images/default-avatar.png');
    }

    public function render()
    {
        return view('livewire.widget.recent-activity-table', [
            'activities' => $this->activities,
        ]);
    }
}