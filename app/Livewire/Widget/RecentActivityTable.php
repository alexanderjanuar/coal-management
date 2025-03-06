<?php

namespace App\Livewire\Widget;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Livewire\Component;
use App\Models\RequiredDocument;
use Filament\Support\Colors\Color;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Carbon\Carbon;

class RecentActivityTable extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    // Number of records to show
    public int $limit = 10;

    protected function getTableQuery(): Builder
    {
        // Get base query - default to today's activities
        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->whereDate('created_at', Carbon::today())
            ->whereDoesntHave('causer', function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['super-admin', 'admin']);
                });
            });

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

        return $query->latest();  // Remove the limit() call here
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\ImageColumn::make('causer_avatar')
                    ->label('User')
                    ->circular()
                    ->state(function (Activity $record) {
                        if ($record->causer && method_exists($record->causer, 'getAvatarUrl')) {
                            return $record->causer->getAvatarUrl();
                        }

                        return null;
                    })
                    ->defaultImageUrl(asset('images/default-avatar.png'))
                    ->size(40)
                    ->tooltip(function (Activity $record): string {
                        return $record->causer?->name ?? 'System';
                    }),

                Tables\Columns\TextColumn::make('description')
                    ->label('Action')
                    ->formatStateUsing(function ($state, Activity $record) {
                        // Get the subject type first
                        $type = match ($record->subject_type) {
                            'App\Models\Project' => 'Project',
                            'App\Models\Client' => 'Client information',
                            'App\Models\RequiredDocument' => 'Document',
                            'App\Models\SubmittedDocument' => 'Document',
                            default => 'Record'
                        };

                        // Then get the action in past tense
                        $action = match ($record->description) {
                            'created' => 'submitted',
                            'updated' => 'updated',
                            'deleted' => 'removed',
                            default => $record->description
                        };

                        // Format: "Document submitted" or "Project updated"
                        return "{$type} {$action}";
                    }),
                    
                // Add client name based on the subject type
                Tables\Columns\TextColumn::make('client_name')
                    ->label('Client')
                    ->state(function (Activity $record) {
                        // Try to determine client based on subject type
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
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->label('View')
                    ->visible(function (Activity $record): bool {
                        // Only show view button if we have a valid subject
                        return $record->subject !== null;
                    })
                    ->url(function (Activity $record): ?string {
                        // Determine appropriate URL based on subject type
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
                    }),
            ])
            ->emptyStateHeading('No activity found')
            ->emptyStateDescription(function () {
                $dateFilter = $this->tableFilters['date_filter'] ?? ['value' => 'today'];
                $date = $dateFilter['value'] ?? 'today';
                
                $labels = [
                    'today' => 'today',
                    'week' => 'this week',
                    'month' => 'this month',
                    'year' => 'this year',
                    'all' => '',
                ];
                
                $dateText = $labels[$date] ?? '';
                
                return "No user activity has been recorded " . ($dateText ? $dateText : "in the selected time period") . ".";
            })
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->heading('Recent User Activity')
            ->paginated(true)
            ->defaultPaginationPageOption(8)
            ->paginationPageOptions([10, 25, 50]);
    }

    public static function canView(): bool
    {
        return auth()->check();
    }

    public function render()
    {
        return view('livewire.widget.recent-activity-table');
    }
}