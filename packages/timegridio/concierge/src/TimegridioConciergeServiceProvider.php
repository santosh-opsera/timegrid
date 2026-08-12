<?php

declare(strict_types=1);

namespace Timegridio\Concierge;

use Illuminate\Support\ServiceProvider;

class TimegridioConciergeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $viewsPath = __DIR__.'/resources/views';

        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'concierge');
        }

        if ($this->app->runningInConsole()) {
            $migrationsPath = __DIR__.'/../migrations';

            if (is_dir($migrationsPath)) {
                $this->publishes([
                    $migrationsPath => database_path('migrations'),
                ], 'concierge-migrations');
            }
        }
    }

    public function register(): void
    {
        $this->app->singleton(Concierge::class, static fn (): Concierge => new Concierge);
        $this->app->alias(Concierge::class, 'concierge');
    }
}
