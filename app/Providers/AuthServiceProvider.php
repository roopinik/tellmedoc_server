<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Education;
use App\Models\Specialization;
use App\Models\Language;
use App\Models\Client;
use App\Models\HealthCareUser;
use App\Policies\AdminPolicy;
use App\Policies\HealthCareUserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Education::class => AdminPolicy::class,
        Specialization::class => AdminPolicy::class,
        Language::class => AdminPolicy::class,
        Client::class => AdminPolicy::class,
        HealthCareUser::class => HealthCareUserPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
