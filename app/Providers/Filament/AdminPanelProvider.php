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
            ->brandLogo(asset('images/acufara-header-2.svg'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('images/acufara-title.svg'))
            ->login()
            ->colors([
                'primary' => '#87A878',
                'gray' => Color::Stone,
            ])
            ->font('Inter')
            ->sidebarCollapsibleOnDesktop()
            ->navigationItems([
                \Filament\Navigation\NavigationItem::make('Log Viewer')
                    ->url('/log-viewer', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->group('Akses')
                    ->sort(100)
                    ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false),
            ])
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
                        /* ===== STRUCTURAL (Both Light & Dark) ===== */

                        /* Rounded cards, widgets, forms */
                        .fi-section,
                        .fi-wi-stats-overview-stat,
                        .fi-wi-widget,
                        .fi-fo-component-ctn,
                        .fi-modal-window {
                            border-radius: 1rem !important;
                        }

                        /* Rounded table container */
                        .fi-ta-ctn {
                            border-radius: 1rem !important;
                            overflow: hidden;
                        }

                        /* Slightly rounded buttons */
                        .fi-btn {
                            border-radius: 0.625rem !important;
                        }

                        /* Active sidebar item → pill shape with Sage Green */
                        .fi-sidebar-item-active > a {
                            background-color: #87A878 !important;
                            color: white !important;
                            border-radius: 0.625rem !important;
                            box-shadow: 0 2px 8px -2px rgba(135,168,120,0.35) !important;
                        }
                        .fi-sidebar-item-active > a span,
                        .fi-sidebar-item-active > a svg {
                            color: white !important;
                        }

                        /* Navigation group labels → uppercase small */
                        .fi-sidebar-group-label {
                            font-size: 0.625rem !important;
                            text-transform: uppercase !important;
                            letter-spacing: 0.08em !important;
                            font-weight: 600 !important;
                        }

                        /* ===== LIGHT MODE ONLY ===== */
                        html:not(.dark) body.fi-body {
                            background-color: #F7F8FA !important;
                        }

                        /* Sidebar: clean white, no transparency */
                        html:not(.dark) .fi-sidebar {
                            background-color: #FFFFFF !important;
                            border-right: 1px solid #F0F0F0 !important;
                        }
                        html:not(.dark) .fi-sidebar-header {
                            background-color: #FFFFFF !important;
                        }

                        /* Top bar: clean white */
                        html:not(.dark) .fi-topbar {
                            background-color: #FFFFFF !important;
                            border-bottom: 1px solid #F0F0F0 !important;
                        }

                        /* Cards & widgets: white with soft shadow */
                        html:not(.dark) .fi-section,
                        html:not(.dark) .fi-ta-ctn {
                            background-color: #FFFFFF !important;
                            border: 1px solid #F0F0F0 !important;
                            box-shadow: 0 1px 3px 0 rgba(0,0,0,0.04) !important;
                        }

                        /* Stat overview cards: soft pastel green tint */
                        html:not(.dark) .fi-wi-stats-overview-stat {
                            background-color: #eaf4f1 !important;
                            border: 1px solid #d5e8e0 !important;
                            box-shadow: none !important;
                        }

                        /* Sidebar hover item */
                        html:not(.dark) .fi-sidebar-item:not(.fi-sidebar-item-active) > a:hover {
                            background-color: #F7F8FA !important;
                            border-radius: 0.625rem !important;
                        }

                        /* ===== DARK MODE ONLY ===== */
                        /* Stat cards in dark mode: subtle dark green tint */
                        html.dark .fi-wi-stats-overview-stat {
                            background-color: rgba(135,168,120,0.1) !important;
                            border: 1px solid rgba(135,168,120,0.2) !important;
                        }
                    </style>
                ')
            );
    }
}
