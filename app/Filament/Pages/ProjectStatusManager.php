<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class ProjectStatusManager extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Project Statuses';

    protected static ?string $title = 'Project Statuses';

    protected static ?int $navigationSort = 90;

    protected static string $view = 'filament.pages.project-status-manager';

    protected static ?string $slug = 'project-statuses';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('super-admin') ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super-admin') ?? false;
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::ScreenLarge;
    }
}
