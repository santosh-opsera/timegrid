<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        //
    }
}
