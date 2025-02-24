<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use CharrafiMed\GlobalSearchModal\Customization\Position;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use SolutionForest\FilamentAccessManagement\FilamentAccessManagementPanel;
use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use Devonab\FilamentEasyFooter\EasyFooterPlugin;
use Cmsmaxinc\FilamentErrorPages\FilamentErrorPagesPlugin;
use Illuminate\Support\HtmlString;
use Filament\Navigation\NavigationGroup;
use Filament\Support\Enums\MaxWidth;
use Kenepa\Banner\BannerPlugin;
use Njxqlus\FilamentProgressbar\FilamentProgressbarPlugin;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigationGroups([
                'Project Management',
                'Tax',
                'Master Data',
                'Admin',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->sidebarFullyCollapsibleOnDesktop()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->maxContentWidth(MaxWidth::Full)
            ->databaseNotifications(
                fn() =>
                preg_match('/(android|iphone|ipad|mobile)/i', request()->header('User-Agent'))
            )
            ->plugins([
                \TomatoPHP\FilamentPWA\FilamentPWAPlugin::make(),
                FilamentApexChartsPlugin::make(),
                EasyFooterPlugin::make()
                    ->withFooterPosition('sidebar.footer')
                    ->withSentence(new HtmlString('<img src="' . asset('images/JKB-Logo.png') . '" style="margin-right:.5rem;" alt="Laravel Logo" width="20" height="20"> JKB Management')),
                GlobalSearchModalPlugin::make(),
                BannerPlugin::make()
                    ->persistsBannersInDatabase(),
                FilamentProgressbarPlugin::make()->color('#f59e0b'),
                FilamentAccessManagementPanel::make(),
                FilamentErrorPagesPlugin::make(),
                AuthUIEnhancerPlugin::make()
                    ->showEmptyPanelOnMobile(false)
                    ->formPanelPosition('right')
                    ->formPanelWidth('40%')
                    ->emptyPanelBackgroundImageOpacity('80%')
                    ->emptyPanelBackgroundImageUrl('https://images.pexels.com/photos/466685/pexels-photo-466685.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2')
            ])

            ->viteTheme('resources/css/filament/admin/theme.css')
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
