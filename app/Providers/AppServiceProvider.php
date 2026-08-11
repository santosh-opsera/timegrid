<?php

namespace App\Providers;

use App\Models\Business;
use App\Policies\BusinessPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Business::class, BusinessPolicy::class);

        Vite::prefetch(concurrency: 3);
    }
}
