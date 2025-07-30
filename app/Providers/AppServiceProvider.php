<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Livewire\Livewire;
use App\Livewire\SelectDoctor;
use App\Livewire\AddAppointmentDetails;
use App\Livewire\AppointmentInstructions;
use App\Livewire\AppointmentBooking;
use App\Services\WAConnectService;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use App\Models\WhatsAppAppointment;
use App\Observers\WhatsAppAppointmentObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Livewire::component('appointment-wizard', AppointmentBooking::class);
        Livewire::component('select-doctor', SelectDoctor::class);
        Livewire::component('add-details', AddAppointmentDetails::class);
        Livewire::component('appointment-instructions', AppointmentInstructions::class);

        $this->app->bind(WAConnectService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::FOOTER,
            fn(): View => view('footer-copyright'),
        );
        FilamentView::registerRenderHook(
            PanelsRenderHook::SIDEBAR_FOOTER,
            fn(): View => view('sidebar-footer'),
        );
        Model::unguard();
        WhatsAppAppointment::observe(WhatsAppAppointmentObserver::class);
    }
}
