<?php

namespace App\Providers;


use App\Models\TaxReport;
use App\Observers\TaxReportObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Filament\Notifications\Livewire\DatabaseNotifications;
use App\Models\Invoice;
use App\Observers\InvoiceObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Model::unguard();
        Invoice::observe(InvoiceObserver::class);
        TaxReport::observe(TaxReportObserver::class);

        DatabaseNotifications::trigger('filament.notifications.database-notifications-trigger');

        FilamentAsset::register([
            Js::make('filament-notification-sounds', __DIR__ . '/../../resources/js/filament-notification-sounds.js'),
        ]);

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn(): string => Blade::render('@livewire(\'project-detail.document-modal-manager\')'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
            fn(): string => Blade::render('@livewire(\'notification.notification-button\')'),
        );

        


    }
}
