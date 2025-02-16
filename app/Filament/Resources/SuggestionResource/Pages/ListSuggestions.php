<?php

namespace App\Filament\Resources\SuggestionResource\Pages;

use App\Filament\Resources\SuggestionResource;
use App\Filament\Resources\SuggestionResource\Widgets\CreateSuggestionWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListSuggestions extends ListRecords
{
    protected static string $resource = SuggestionResource::class;

    #[On('suggestion-created')] 
    public function refresh() {}

    protected function getHeaderWidgets(): array 
    {
        return [
            CreateSuggestionWidget::class,
        ];
    } 
}
