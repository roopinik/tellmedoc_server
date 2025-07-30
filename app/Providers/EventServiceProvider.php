<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Observers\ClientRecordObserver;
use App\Models\LabReport;
use App\Models\Prescription;
use App\Models\Appointment;
use App\Models\Person;
use App\Models\SharedDocument;
use App\Models\DoctorLeave;
use App\Models\Hospital;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        LabReport::observe(ClientRecordObserver::class);
        Prescription::observe(ClientRecordObserver::class);
        Prescription::observe(ClientRecordObserver::class);
        Appointment::observe(ClientRecordObserver::class);
        Person::observe(ClientRecordObserver::class);
        SharedDocument::observe(ClientRecordObserver::class);
        DoctorLeave::observe(ClientRecordObserver::class);
        Hospital::observe(ClientRecordObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
