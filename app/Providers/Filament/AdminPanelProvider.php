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
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\AdminLogin::class)
            
            // Branding - GloboKids
            ->brandName('GloboKids')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('images/logo.png'))
            
            // Brand Colors
            // D0E3F4 - light blue (backgrounds)
            // F1C49E - peach
            // 83C696 - green  
            // 4A91CD - blue (buttons)
            ->colors([
                'primary' => Color::hex('#4A91CD'),    // Main blue for buttons
                'success' => Color::hex('#83C696'),    // Green
                'warning' => Color::hex('#F1C49E'),    // Peach/Orange
                'danger' => Color::Rose,
                'info' => Color::hex('#4A91CD'),
            ])
            
            // Dark mode toggle (user choice)
            ->darkMode(true)
            
            // Navigation
            ->sidebarCollapsibleOnDesktop()
            
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\StatsOverview::class,
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
                \App\Http\Middleware\SetLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
