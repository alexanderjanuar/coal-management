<?php

namespace App\Livewire\DailyTask\Dashboard;

use App\Models\DailyTask;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Support\Carbon;

class DailyTaskTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $model = DailyTask::class;

    public function table(Table $table): Table
    {
        return $table
            ->query(DailyTask::query())
            ->defaultSort('task_date', 'asc')
            ->modifyQueryUsing(fn (Builder $query) => $this->applyDefaultFilters($query))
            ->heading('Tugas Terlambat')
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Tugas')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (DailyTask $record): string {
                        return $record->title;
                    })
                    ->description(fn (DailyTask $record): ?string => 
                        $record->description ? \Str::limit($record->description, 60) : null
                    ),
                    
                    
                TextColumn::make('assignedUsers.name')
                    ->label('Ditugaskan Kepada')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->limitList(2)
                    ->expandableLimitedList(),
                                
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'pending',
                        'primary' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'pending' => 'Pending',
                        'in_progress' => 'Sedang Dikerjakan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state
                    }),
                    
                TextColumn::make('task_date')
                    ->label('Deadline')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn (DailyTask $record): string => 
                        $record->task_date->isPast() && !in_array($record->status, ['completed', 'cancelled']) 
                            ? 'danger' 
                            : 'gray'
                    )
                    ->description(function (DailyTask $record): ?string {
                        if ($record->task_date->isPast() && !in_array($record->status, ['completed', 'cancelled'])) {
                            $days = $record->task_date->diffInDays(now());
                            return "Terlambat {$days} hari";
                        } elseif ($record->task_date->isToday()) {
                            return "Hari ini";
                        } elseif ($record->task_date->isTomorrow()) {
                            return "Besok";
                        }
                        return null;
                    }),
                    
                TextColumn::make('start_task_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                    
                TextColumn::make('subtasks_count')
                    ->label('Sub-tugas')
                    ->counts('subtasks')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Tertunda',
                        'in_progress' => 'Sedang Dikerjakan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->multiple(),
                    
                SelectFilter::make('priority')
                    ->label('Prioritas')
                    ->options([
                        'low' => 'Rendah',
                        'normal' => 'Normal',
                        'high' => 'Tinggi',
                        'urgent' => 'Mendesak',
                    ])
                    ->multiple(),
                    
                Filter::make('overdue')
                    ->label('Terlambat')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('task_date', '<', now())
                        ->whereNotIn('status', ['completed', 'cancelled'])
                    )
                    ->default(),
                    
                Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('task_date', today())),
                    
                Filter::make('this_week')
                    ->label('Minggu Ini')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereBetween('task_date', [
                            now()->startOfWeek(),
                            now()->endOfWeek()
                        ])
                    ),
                    
                SelectFilter::make('assigned_user')
                    ->label('Ditugaskan Kepada')
                    ->relationship('assignedUsers', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                    
                SelectFilter::make('project')
                    ->label('Proyek')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->striped()
            ->deferLoading()
            ->actions([
                ActionGroup::make([
                    Action::make('view')
                        ->label('Lihat')
                        ->icon('heroicon-m-eye'),
                        
                    Action::make('edit')
                        ->label('Edit')
                        ->icon('heroicon-m-pencil-square')
                        ->visible(fn (DailyTask $record): bool => 
                            !in_array($record->status, ['completed', 'cancelled'])
                        ),
                        
                    Action::make('markAsCompleted')
                        ->label('Tandai Selesai')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Tandai Tugas Selesai')
                        ->modalDescription('Apakah Anda yakin ingin menandai tugas ini sebagai selesai?')
                        ->modalSubmitActionLabel('Ya, Tandai Selesai')
                        ->action(fn (DailyTask $record) => $record->markAsCompleted())
                        ->visible(fn (DailyTask $record): bool => 
                            $record->status !== 'completed' && $record->status !== 'cancelled'
                        ),
                        
                    Action::make('markAsInProgress')
                        ->label('Mulai Kerjakan')
                        ->icon('heroicon-m-play-circle')
                        ->color('primary')
                        ->action(fn (DailyTask $record) => $record->markAsInProgress())
                        ->visible(fn (DailyTask $record): bool => 
                            $record->status === 'pending'
                        ),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('markAsCompleted')
                        ->label('Tandai Selesai')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->markAsCompleted())
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\BulkAction::make('markAsInProgress')
                        ->label('Mulai Kerjakan')
                        ->icon('heroicon-m-play-circle')
                        ->color('primary')
                        ->action(fn ($records) => $records->each->markAsInProgress())
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateHeading('Tidak Ada Tugas Terlambat')
            ->emptyStateDescription('Semua tugas telah diselesaikan tepat waktu.')
            ->emptyStateIcon('heroicon-o-check-badge')
            ->poll('30s');
    }

    protected function applyDefaultFilters(Builder $query): Builder
    {
        // Default filter untuk menampilkan tugas yang terlambat atau overdue
        return $query->where(function ($q) {
            $q->where('task_date', '<', now())
              ->whereNotIn('status', ['completed', 'cancelled']);
        })->orWhere(function ($q) {
            // Atau tugas hari ini yang belum selesai
            $q->whereDate('task_date', today())
              ->whereNotIn('status', ['completed', 'cancelled']);
        });
    }

    public function render()
    {
        return view('livewire.daily-task.dashboard.daily-task-table');
    }
}