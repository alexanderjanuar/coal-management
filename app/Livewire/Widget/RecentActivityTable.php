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
            ->whereDate('created_at', Carbon::today());

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

        return $query->latest()->limit($this->limit);
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
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        return ucfirst($state);
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
                    })
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('H:i') // 24-hour format
                    ->tooltip(function (Activity $record): string {
                        // Full date and time on hover
                        return $record->created_at->format('Y-m-d H:i:s');
                    })
                    ->sortable(),
            ])
            ->filters([
                // Date range filter using proper Filter object instead of property
                Tables\Filters\SelectFilter::make('date_filter')
                    ->label('Date Range')
                    ->options([
                        'today' => 'Today',
                        'week' => 'This Week',
                        'month' => 'This Month',
                        'year' => 'This Year',
                        'all' => 'All Time',
                    ])
                    ->default('today')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function (Builder $query, string $value): Builder {
                            switch ($value) {
                                case 'today':
                                    return $query->whereDate('created_at', Carbon::today());
                                case 'week':
                                    return $query->where('created_at', '>=', Carbon::now()->startOfWeek());
                                case 'month':
                                    return $query->where('created_at', '>=', Carbon::now()->startOfMonth());
                                case 'year':
                                    return $query->where('created_at', '>=', Carbon::now()->startOfYear());
                                case 'all':
                                    return $query; // No date filtering
                                default:
                                    return $query->whereDate('created_at', Carbon::today()); // Default to today
                            }
                        });
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['value']) {
                            return null;
                        }

                        $labels = [
                            'today' => 'Today',
                            'week' => 'This Week',
                            'month' => 'This Month',
                            'year' => 'This Year',
                            'all' => 'All Time',
                        ];

                        return 'Date: ' . ($labels[$data['value']] ?? $data['value']);
                    }),

                Tables\Filters\SelectFilter::make('description')
                    ->label('Action Type')
                    ->options(function () {
                        // Get unique activity descriptions from the database
                        $descriptions = Activity::distinct()
                            ->pluck('description')
                            ->filter()
                            ->mapWithKeys(function ($item) {
                            return [
                                $item => ucfirst($item)
                            ];
                        })
                            ->toArray();

                        return $descriptions;
                    })
                    ->attribute('description'),
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
            ->paginated(false);
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