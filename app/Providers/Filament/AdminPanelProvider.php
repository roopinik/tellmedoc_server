<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\AppoinmentInsightsFuture;
use Filament\Http\Middleware\Authenticate;
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
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Chiiya\FilamentAccessControl\FilamentAccessControlPlugin;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;
use Filament\SpatieLaravelTranslatablePlugin;
use Filament\Navigation\NavigationItem;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Sky,
            ])
            ->navigationItems([
                NavigationItem::make('Management App')
                    ->url('https://app.telmedoc.com', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-adjustments-horizontal')
            ])
            ->brandLogo(asset('logo.png'))
            ->brandLogoHeight('2rem')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AppoinmentInsightsFuture::class
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
                AddUserMenuItems::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugin(FilamentAccessControlPlugin::make())
            // ->plugin(FilamentSpatieLaravelBackupPlugin::make())
            ->plugin(SpatieLaravelTranslatablePlugin::make()->defaultLocales(["en", "kn"]));;
    }
}


class AddUserMenuItems
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth('filament')->user();
        if ($user == null)
            return $next($request);
        if ($user->hasRole('doctor')) {
            filament()->getCurrentPanel()->navigationItems([
                NavigationItem::make('User Profile')
                    ->url('/admin/doctor/' . $user->id . '/edit')
                    ->icon('heroicon-o-adjustments-horizontal')
            ]);
        }
        return $next($request);
    }
}
