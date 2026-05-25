<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ProjectDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'Project Management';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Project Dashboard';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.project-dashboard';

    protected static ?string $slug = 'project-dashboard';

    public function getSubheading(): ?string
    {
        return 'Snapshot proyek aktif, beban kerja, dan tenggat terdekat.';
    }

    // Inherits panel-level MaxWidth::Full — no override.
}
