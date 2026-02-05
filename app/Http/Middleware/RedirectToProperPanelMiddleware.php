<?php

namespace App\Http\Middleware;

use App\Filament\Client\Pages\DashboardClient;
use Closure;
use Illuminate\Http\Request;
use Filament\Pages\Dashboard;
use Filament\Facades\Filament;

class RedirectToProperPanelMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        $currentPanelId = Filament::getCurrentPanel()?->getId();
        $isClient = $user->hasAnyRole(['client']);

        // Client users trying to access admin panel → redirect to client panel
        if ($isClient && $currentPanelId === 'admin') {
            return redirect()->to(DashboardClient::getUrl(panel: 'klien'));
        }

        // Non-client users trying to access client panel → redirect to admin panel
        if (!$isClient && $currentPanelId === 'klien') {
            return redirect()->to(Dashboard::getUrl(panel: 'admin'));
        }

        return $next($request);
    }
}
