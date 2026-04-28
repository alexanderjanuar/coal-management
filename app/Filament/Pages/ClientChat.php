<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class ClientChat extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Live Chat';

    protected static ?string $navigationGroup = 'Client Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Live Chat';

    protected static ?string $slug = 'client-chat';

    protected static string $view = 'filament.pages.client-chat';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && !auth()->user()->hasRole('client');
    }

    public static function canAccess(): bool
    {
        return auth()->check() && !auth()->user()->hasRole('client');
    }
}
