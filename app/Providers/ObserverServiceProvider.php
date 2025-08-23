<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\User;
use App\Observers\DriverObserver;
use App\Observers\OrderObserver;
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
        Order::observe(OrderObserver::class);
    }
}
