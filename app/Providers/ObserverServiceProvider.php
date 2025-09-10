<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderCarpet;
use App\Models\User;
use App\Observers\ClientObserver;
use App\Observers\DriverObserver;
use App\Observers\OrderCarpetObserver;
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
        OrderCarpet::observe(OrderCarpetObserver::class);
        Client::observe(ClientObserver::class);
    }
}
