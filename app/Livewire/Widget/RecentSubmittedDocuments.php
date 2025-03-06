<?php

namespace App\Livewire\Widget;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use App\Models\RequiredDocument;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;

class RecentSubmittedDocuments extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    // Number of records to show
    public int $limit = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                $this->getTableQuery()
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Document Name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('projectStep.project.client.name')
                    ->label('Client')
                    ->sortable(),
                Tables\Columns\TextColumn::make('projectStep.project.priority')
                    ->label('Priority')
                    ->formatStateUsing(function (string $state): string {
                        return ucfirst($state);
                    })
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'urgent' => 'danger',
                            'normal' => 'info',
                            'low' => 'gray',
                            default => 'gray',
                        };
                    })
                    ->visible(fn() => Auth::user()->hasRole('staff')),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'rejected',
                        'warning' => 'pending_review',
                        'success' => 'approved',
                        'info' => 'uploaded',
                        'gray' => 'draft',
                    ])
                    ->formatStateUsing(fn(string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->action(function (RequiredDocument $record): void {
                        $this->dispatch('openDocumentModal', $record->id);
                    }),
            ])
            ->emptyStateHeading('No documents found')
            ->emptyStateDescription(function () {
                if (Auth::user()->hasRole('staff')) {
                    return 'No draft documents found at this time.';
                }

                return 'No uploaded or pending review documents found at this time.';
            })
            ->poll('5s')
            ->emptyStateIcon('heroicon-o-document')
            ->heading(function () {
                if (Auth::user()->hasRole('staff')) {
                    return 'Documents To Work On';
                } elseif (Auth::user()->hasRole('super-admin')) {
                    return 'Document Submission Overview';
                } else {
                    return 'Recently Submitted Documents';
                }
            });
    }

    protected function getTableQuery(): Builder
    {
        // Build the base query
        $query = RequiredDocument::query()
            ->with(['projectStep', 'projectStep.project', 'projectStep.project.client', 'reviewer']);

        // Join with project_steps table
        $query->leftJoin('project_steps', 'required_documents.project_step_id', '=', 'project_steps.id');

        // Join with projects table to access due_date and priority
        $query->leftJoin('projects', 'project_steps.project_id', '=', 'projects.id');

        // Make sure to select only the required_documents fields to avoid column ambiguity
        $query->select('required_documents.*');

        // Different query based on user role
        if (Auth::user()->hasRole('staff')) {
            // For staff: show only draft documents
            $query->where('required_documents.status', 'draft');

            // Sort by priority (urgent first, then normal, low)
            $query->orderByRaw("
                CASE 
                    WHEN projects.priority = 'urgent' THEN 1
                    WHEN projects.priority = 'normal' THEN 2
                    WHEN projects.priority = 'low' THEN 3
                    ELSE 4
                END
            ");

            // Then sort by due date (nearest first)
            $query->orderBy('projects.due_date', 'asc');
        } else {
            // For other roles: show uploaded and pending_review documents
            $query->whereIn('required_documents.status', ['uploaded', 'pending_review']);

            // First uploaded, then pending_review 
            $query->orderByRaw("
                CASE 
                    WHEN required_documents.status = 'uploaded' THEN 1 
                    WHEN required_documents.status = 'pending_review' THEN 2
                    ELSE 3
                END
            ");

            // For pending_review, sort by due date
            $query->orderByRaw("
                CASE 
                    WHEN required_documents.status = 'pending_review' THEN projects.due_date
                    ELSE NULL
                END ASC
            ");
        }

        // Secondary sort by updated_at date as final tie-breaker
        $query->orderBy('required_documents.updated_at', 'desc');

        // If the user is not a super-admin, filter by their client access
        if (!Auth::user()->hasRole('super-admin')) {
            $query->whereHas('projectStep.project', function ($q) {
                $q->whereIn('client_id', function ($subQ) {
                    $subQ->select('client_id')
                        ->from('user_clients')
                        ->where('user_id', Auth::id());
                });
            });
        }

        return $query;
    }

    // Helper function to determine if we have pending review documents
    private function hasStatus(string $status): bool
    {
        $query = clone $this->getTableQuery();
        return $query->where('required_documents.status', $status)->exists();
    }

    public function render()
    {
        return view('livewire.widget.recent-submitted-documents');
    }
}