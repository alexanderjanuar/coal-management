<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DailyTaskDashboard extends Page
{
    protected static ?string $navigationGroup = 'Manajemen Tugas';
    
    protected static ?string $title = 'Dashboard Tugas Harian';
    
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.daily-task-dashboard';
}
