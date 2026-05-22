<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function register(): void
    {
        parent::register();

        FilamentAsset::register([
            Js::make('acuvoice', __DIR__ . '/../../../resources/js/acuvoice.js'),
        ]);
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName(config('app.name', 'Acufara AI Clinic'))
            ->login()
            ->colors([
                'primary' => '#87A878',
                'gray' => Color::Stone,
            ])
            ->font('Inter')
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->navigationGroup('Akses')
                    ->navigationLabel('Role & Permission')
                    ->navigationSort(99),
                FilamentFullCalendarPlugin::make()
                    ->selectable(true)
                    ->editable(true),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <style>
                        /* Structural Soft UI Overrides (Apply to both Light and Dark) */
                        .fi-sidebar {
                            backdrop-filter: blur(16px);
                        }
                        .fi-sidebar-header {
                            background-color: transparent !important;
                        }
                        .fi-topbar {
                            backdrop-filter: blur(12px);
                        }
                        .fi-ta-record, .fi-wi-widget, .fi-fo-component, .fi-modal-window, .fi-fieldset {
                            border-radius: 1.5rem !important;
                            box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05) !important;
                            backdrop-filter: blur(10px);
                        }
                        .fi-btn {
                            border-radius: 1rem !important;
                        }
                        .fi-sidebar-item-active > a {
                            background-color: #87A878 !important;
                            color: white !important;
                            border-radius: 1rem !important;
                            box-shadow: 0 4px 15px -3px rgba(135,168,120,0.4) !important;
                        }
                        .fi-sidebar-item-active > a span, .fi-sidebar-item-active > a svg {
                            color: white !important;
                        }

                        /* Color Overrides (Only for Light Mode) */
                        html:not(.dark) body.fi-body {
                            background-color: #F5F0E8 !important; /* Beige background */
                        }
                        html:not(.dark) .fi-sidebar {
                            background-color: rgba(255, 255, 255, 0.5) !important;
                            border-right: 1px solid rgba(255,255,255,0.6) !important;
                        }
                        html:not(.dark) .fi-topbar {
                            background-color: rgba(245, 240, 232, 0.7) !important;
                            border-bottom: 1px solid rgba(255,255,255,0.4) !important;
                        }
                        html:not(.dark) .fi-ta-record, 
                        html:not(.dark) .fi-wi-widget, 
                        html:not(.dark) .fi-fo-component, 
                        html:not(.dark) .fi-modal-window, 
                        html:not(.dark) .fi-fieldset {
                            border: 1px solid rgba(255,255,255,0.8) !important;
                            background-color: rgba(255, 255, 255, 0.8) !important;
                        }
                    </style>
                ')
            );
    }
}
