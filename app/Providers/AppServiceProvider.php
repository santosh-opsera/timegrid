<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\Preference;
use App\Models\Role;
use App\Models\User;
use App\Observers\AuditObserver;
use App\Policies\BusinessPolicy;
use App\Policies\ContactPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            if (class_exists(\Laracasts\Generators\GeneratorsServiceProvider::class)) {
                $this->app->register(\Laracasts\Generators\GeneratorsServiceProvider::class);
            }

            if (class_exists(\Barryvdh\Debugbar\ServiceProvider::class)) {
                $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
            }

            if (class_exists(\Potsky\LaravelLocalizationHelpers\LaravelLocalizationHelpersServiceProvider::class)) {
                $this->app->register(\Potsky\LaravelLocalizationHelpers\LaravelLocalizationHelpersServiceProvider::class);
            }
        }

        if (config('services.rollbar.access_token')) {
            if (class_exists(\Jenssegers\Rollbar\RollbarServiceProvider::class)) {
                $this->app->register(\Jenssegers\Rollbar\RollbarServiceProvider::class);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Business::class, BusinessPolicy::class);
        Gate::policy(Contact::class, ContactPolicy::class);

        $observer = AuditObserver::class;

        User::observe($observer);
        Role::observe($observer);
        Permission::observe($observer);
        Preference::observe($observer);
        Business::observe($observer);
        Contact::observe($observer);
        Appointment::observe($observer);
    }
}
