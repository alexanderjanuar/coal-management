<?php

namespace App\Filament\Exports;

use App\Models\Project;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class ProjectExporter extends Exporter
{
    protected static ?string $model = Project::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('No'),
                
            ExportColumn::make('client.name')
                ->label('Nama Klien'),
                
            ExportColumn::make('name')
                ->label('Nama Proyek'),
                
            ExportColumn::make('sop.name')
                ->label('SOP'),
                
            ExportColumn::make('pic.name')
                ->label('Person In Charge (PIC)'),
                
            ExportColumn::make('status')
                ->label('Status')
                ->formatStateUsing(fn ($state) => match ($state) {
                    'draft' => 'Draft',
                    'in_progress' => 'Sedang Dikerjakan',
                    'on_hold' => 'Ditunda',
                    'completed' => 'Selesai',
                    'canceled' => 'Dibatalkan',
                    default => Str::title(str_replace('_', ' ', $state)),
                }),
                
            ExportColumn::make('priority')
                ->label('Prioritas')
                ->formatStateUsing(fn ($state) => match ($state) {
                    'urgent' => 'Mendesak',
                    'normal' => 'Normal',
                    'low' => 'Rendah',
                    default => Str::title($state),
                }),
                
            ExportColumn::make('type')
                ->label('Tipe Proyek')
                ->formatStateUsing(fn ($state) => match ($state) {
                    'single' => 'On Spot',
                    'monthly' => 'Bulanan',
                    'yearly' => 'Tahunan',
                    default => Str::title($state),
                }),
                
            ExportColumn::make('due_date')
                ->label('Tanggal Jatuh Tempo'),
                
            ExportColumn::make('progress')
                ->label('Progress (%)')
                ->state(function (Project $record): string {
                    $steps = $record->steps;
                    $totalItems = 0;
                    $completedItems = 0;

                    foreach ($steps as $step) {
                        $totalItems++;
                        if ($step->status === 'completed') {
                            $completedItems++;
                        }

                        $tasks = $step->tasks;
                        $totalItems += $tasks->count();
                        $completedItems += $tasks->where('status', 'completed')->count();

                        $documents = $step->requiredDocuments;
                        $totalItems += $documents->count();
                        $completedItems += $documents->where('status', 'approved')->count();
                    }

                    $percentage = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
                    return $percentage . '%';
                }),
                
            ExportColumn::make('steps_count')
                ->label('Jumlah Tahapan')
                ->state(fn (Project $record): int => $record->steps->count()),
                
            ExportColumn::make('tasks_count')
                ->label('Jumlah Tugas')
                ->state(fn (Project $record): int => $record->steps->sum(fn ($step) => $step->tasks->count())),
                
            ExportColumn::make('documents_count')
                ->label('Jumlah Dokumen')
                ->state(fn (Project $record): int => $record->steps->sum(fn ($step) => $step->requiredDocuments->count())),
                
            ExportColumn::make('created_at')
                ->label('Tanggal Dibuat'),
                
            ExportColumn::make('updated_at')
                ->label('Terakhir Diperbarui'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor proyek telah selesai. ' . number_format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diekspor.';
        }

        return $body;
    }
}
