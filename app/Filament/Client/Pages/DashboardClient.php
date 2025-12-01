<?php

namespace App\Filament\Client\Pages;

use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class DashboardClient extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static ?string $navigationLabel = 'Dashboard';
    
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.client.pages.dashboard-client';
    
    public string $activeTab = 'overview';
    
    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
    
    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }
    
    protected function getViewData(): array
    {
        return [
            'activeTab' => $this->activeTab,
        ];
    }
}