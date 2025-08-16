<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\DriverObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        User::observe(DriverObserver::class);
    }
}
