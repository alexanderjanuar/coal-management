<?php

namespace App\Http\Responses;

use App\Filament\Client\Pages\DashboardClient;
use Filament\Pages\Dashboard;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use Filament\Http\Responses\Auth\LoginResponse as BaseLoginResponse;

class LoginResponse extends BaseLoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = auth()->user();
        
        // Check if user has any role/permission that indicates they're admin/staff
        if ($user->hasAnyRole(['client'])) {
            return redirect()->to(DashboardClient::getUrl(panel: 'klien'));
        }
           
        // Default: redirect to client dashboard
        return parent::toResponse($request);
    }
}