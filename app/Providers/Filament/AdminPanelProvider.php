<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\ActiveVehiclesTable;
use App\Filament\Widgets\ParkingStatsOverview;
use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\VehicleTypeChart;
use App\Settings\GeneralSettings;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->colors([
                'primary' => Color::Green,
            ])
            ->brandName(fn () => $this->getBrandName())
            ->brandLogo(fn () => $this->getBrandLogo())
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('favicon.ico'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                ParkingStatsOverview::class,
                RevenueChart::class,
                VehicleTypeChart::class,
                ActiveVehiclesTable::class,
            ])
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
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->sidebarCollapsibleOnDesktop();
    }

    protected function getBrandName(): string
    {
        try {
            $settings = app(GeneralSettings::class);

            return $settings->business_name ?: 'Parking Management';
        } catch (\Exception $e) {
            return 'Parking Management';
        }
    }

    protected function getBrandLogo(): ?string
    {
        try {
            $settings = app(GeneralSettings::class);

            if ($settings->logo && Storage::disk('public')->exists($settings->logo)) {
                return asset('storage/'.$settings->logo);
            }
        } catch (\Exception $e) {
            // Settings not available, fall back to null
        }

        return null;
    }
}
