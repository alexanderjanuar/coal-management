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

    // Filter option for status
    public ?string $statusFilter = 'uploaded';

    public function mount(): void
    {
        $this->statusFilter = 'uploaded';
    }

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
                Tables\Columns\TextColumn::make('reviewer.name')
                    ->label('Reviewer')
                    ->placeholder('Not assigned')
                    ->visible(fn() => in_array($this->statusFilter, ['approved', 'rejected', 'pending_review'])),
            ])
            ->defaultSort('updated_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->action(function (RequiredDocument $record): void {
                        $this->dispatch('openDocumentModal', $record->id);
                    }),
            ])
            ->emptyStateHeading('No documents uploaded')
            ->emptyStateDescription(function () {
                $statusLabels = [
                    'draft' => 'in draft',
                    'uploaded' => 'uploaded',
                    'pending_review' => 'pending review',
                    'approved' => 'approved',
                    'rejected' => 'rejected',
                ];

                $statusText = $statusLabels[$this->statusFilter] ?? '';

                return "No documents " . ($statusText ? $statusText : "found") . " at this time.";
            })
            ->poll('5s')
            ->emptyStateIcon('heroicon-o-document')
            ->heading('Recently Submitted Documents');
    }

    protected function getTableQuery(): Builder
    {
        // Build the base query
        $query = RequiredDocument::query()
            ->with(['projectStep', 'projectStep.project', 'projectStep.project.client', 'reviewer']);

        // Filter by selected status
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

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

    public function render()
    {
        return view('livewire.widget.recent-submitted-documents');
    }
}
