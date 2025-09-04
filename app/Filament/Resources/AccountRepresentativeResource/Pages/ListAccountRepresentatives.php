<?php

namespace App\Filament\Resources\AccountRepresentativeResource\Pages;

use App\Filament\Resources\AccountRepresentativeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAccountRepresentatives extends ListRecords
{
    protected static string $resource = AccountRepresentativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah AR Baru')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->icon('heroicon-o-queue-list')
                ->badge(fn () => $this->getModel()::count()),

            'active' => Tab::make('Aktif')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active'))
                ->badge(fn () => $this->getModel()::where('status', 'active')->count())
                ->badgeColor('success'),

            'inactive' => Tab::make('Tidak Aktif')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'inactive'))
                ->badge(fn () => $this->getModel()::where('status', 'inactive')->count())
                ->badgeColor('danger'),

        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // AccountRepresentativeResource\Widgets\ARStatsWidget::class,
        ];
    }

    public function getTitle(): string
    {
        return 'Daftar Account Representative';
    }
}